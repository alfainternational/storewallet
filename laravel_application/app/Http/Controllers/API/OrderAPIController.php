<?php
/**
 * File name: OrderAPIController.php
 * Last modified: 2020.05.31 at 19:34:40
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;


use App\Criteria\Orders\OrdersOfStatusesCriteria;
use App\Criteria\Orders\OrdersOfUserCriteria;
use App\Events\OrderChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\AssignedOrder;
use App\Notifications\NewOrder;
use App\Notifications\StatusChangedOrder;
use App\Repositories\CartRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProductOrderRepository;
use App\Repositories\UserRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Stripe\Token;
use App\Services\WalletService;
use function GuzzleHttp\Promise\iter_for;
use function MongoDB\BSON\toJSON;

/**
 * Class OrderController
 * @package App\Http\Controllers\API
 */
class OrderAPIController extends Controller
{
    /** @var  OrderRepository */
    private $orderRepository;
    /** @var  ProductOrderRepository */
    private $productOrderRepository;
    /** @var  CartRepository */
    private $cartRepository;
    /** @var  UserRepository */
    private $userRepository;
    /** @var  PaymentRepository */
    private $paymentRepository;
    /** @var  NotificationRepository */
    private $notificationRepository;
    /** @var WalletService */
    private $walletService;

    /**
     * OrderAPIController constructor.
     * @param OrderRepository $orderRepo
     * @param ProductOrderRepository $productOrderRepository
     * @param CartRepository $cartRepo
     * @param PaymentRepository $paymentRepo
     * @param NotificationRepository $notificationRepo
     * @param UserRepository $userRepository
     */
    public function __construct(OrderRepository $orderRepo, ProductOrderRepository $productOrderRepository, CartRepository $cartRepo, PaymentRepository $paymentRepo, NotificationRepository $notificationRepo, UserRepository $userRepository, WalletService $walletService)
    {
        $this->orderRepository = $orderRepo;
        $this->productOrderRepository = $productOrderRepository;
        $this->cartRepository = $cartRepo;
        $this->userRepository = $userRepository;
        $this->paymentRepository = $paymentRepo;
        $this->notificationRepository = $notificationRepo;
        $this->walletService = $walletService;
    }

    /**
     * Display a listing of the Order.
     * GET|HEAD /orders
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $this->orderRepository->pushCriteria(new RequestCriteria($request));
            $this->orderRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->orderRepository->pushCriteria(new OrdersOfStatusesCriteria($request));
            $this->orderRepository->pushCriteria(new OrdersOfUserCriteria(auth()->id()));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $orders = $this->orderRepository->all();

        return $this->sendResponse($orders->toArray(), 'Orders retrieved successfully');
    }

    /**
     * Display the specified Order.
     * GET|HEAD /orders/{id}
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        /** @var Order $order */
        if (!empty($this->orderRepository)) {
            try {
                $this->orderRepository->pushCriteria(new RequestCriteria($request));
                $this->orderRepository->pushCriteria(new LimitOffsetCriteria($request));
            } catch (RepositoryException $e) {
                return $this->sendError($e->getMessage());
            }
            $order = $this->orderRepository->findWithoutFail($id);
        }

        if (empty($order)) {
            return $this->sendError('Order not found');
        }

        return $this->sendResponse($order->toArray(), 'Order retrieved successfully');


    }

    /**
     * Store a newly created Order in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $payment = $request->only('payment');
        if (isset($payment['payment']) && $payment['payment']['method']) {
            if ($payment['payment']['method'] == "Wallet") {
                return $this->walletPayment($request);
            } else {
                // Fallback or force wallet? 
                // For unified system, we might want to default to wallet or allow "Cash" as "Wallet Pay on Delivery"
                return $this->walletPayment($request); 
            }
        }
        return $this->walletPayment($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    private function stripPayment(Request $request)
    {
        $input = $request->all();
        $amount = 0;
        try {
            $user = $this->userRepository->findWithoutFail($input['user_id']);
            if (empty($user)) {
                return $this->sendError('User not found');
            }
            $stripeToken = Token::create(array(
                "card" => array(
                    "number" => $input['stripe_number'],
                    "exp_month" => $input['stripe_exp_month'],
                    "exp_year" => $input['stripe_exp_year'],
                    "cvc" => $input['stripe_cvc'],
                    "name" => $user->name,
                )
            ));
            if ($stripeToken->created > 0) {
                if (empty($input['delivery_address_id'])) {
                    $order = $this->orderRepository->create(
                        $request->only('user_id', 'order_status_id', 'tax', 'hint')
                    );
                } else {
                    $order = $this->orderRepository->create(
                        $request->only('user_id', 'order_status_id', 'tax','options', 'delivery_address_id', 'delivery_fee', 'hint')
                    );
                }
                foreach ($input['products'] as $productOrder) {
                    $productOrder['order_id'] = $order->id;
                    $amount += $productOrder['price'] * $productOrder['quantity'];

                    $this->productOrderRepository->create($productOrder);
                }
                $amount += $order->delivery_fee;
                $amountWithTax = $amount + ($amount * $order->tax / 100);
                $charge = $user->charge((int)($amountWithTax * 100), ['source' => $stripeToken]);
                $payment = $this->paymentRepository->create([
                    "user_id" => $input['user_id'],
                    "description" => trans("lang.payment_order_done"),
                    "price" => $amountWithTax,
                    "status" => $charge->status, // $charge->status
                    "method" => $input['payment']['method'],
                ]);
                $this->orderRepository->update(['payment_id' => $payment->id], $order->id);

                $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);

                Notification::send($order->productOrders[0]->product->market->users, new NewOrder($order));
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    private function walletPayment(Request $request)
    {
        $input = $request->all();
        $amount = 0;
        try {
            $user = $this->userRepository->findWithoutFail($input['user_id']);
            if (empty($user)) {
                return $this->sendError('User not found');
            }

            // Calculate Amount
            // Create temporary order structure in memory or calculate from products
            // logic reused from stripPayment
             if (empty($input['delivery_address_id'])) {
                $order = $this->orderRepository->create(
                    $request->only('user_id', 'order_status_id', 'tax', 'hint')
                );
            } else {
                $order = $this->orderRepository->create(
                    $request->only('user_id', 'order_status_id', 'tax','options', 'delivery_address_id', 'delivery_fee', 'hint')
                );
            }
            foreach ($input['products'] as $productOrder) {
                $productOrder['order_id'] = $order->id;
                $amount += $productOrder['price'] * $productOrder['quantity'];
                $this->productOrderRepository->create($productOrder);
            }
            $amount += $order->delivery_fee;
            $amountWithTax = $amount + ($amount * $order->tax / 100);

            // Process Wallet Payment
            if (!$user->wallet_user_id) {
                 // Try to find wallet user by email if ID missing (migration lag)
                 $walletUser = $this->walletService->getWalletUser($user->email);
                 if ($walletUser) {
                     $user->wallet_user_id = $walletUser->id;
                     $user->save();
                 } else {
                      return $this->sendError('Wallet account not found. Please contact support.');
                 }
            }

            $result = $this->walletService->processPayment($user->wallet_user_id, $amountWithTax);

            if ($result['success']) {
                $payment = $this->paymentRepository->create([
                    "user_id" => $input['user_id'],
                    "description" => "Paid via Wallet (TRX: " . $result['trx'] . ")",
                    "price" => $amountWithTax,
                    "status" => 'Paid',
                    "method" => 'Wallet',
                ]);
                $this->orderRepository->update(['payment_id' => $payment->id], $order->id);
                $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);
                
                // Notify Market
                if(isset($order->productOrders[0])) {
                     Notification::send($order->productOrders[0]->product->market->users, new NewOrder($order));
                }

                return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
            } else {
                // Payment Failure
                // Should we delete the order? Or keep it as Unpaid?
                // Usually better to fail the request or return error
                return $this->sendError('Payment Failed: ' . $result['message']);
            }

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    private function cashPayment(Request $request){
        $input = $request->all();
        $amount = 0;
        try {
            $order = $this->orderRepository->create(
                $request->only('user_id', 'order_status_id', 'tax', 'delivery_address_id', 'delivery_fee', 'hint')
            );
            Log::info($order);

            $user = $this->userRepository->find($order->user_id);


            foreach ($input['products'] as $productOrder) {
                $productOrder['order_id'] = $order->id;
                $amount += $productOrder['price'] * $productOrder['quantity'];
                $this->productOrderRepository->create($productOrder);
            }
            $amount += $order->delivery_fee;
            $amountWithTax = $amount + ($amount * $order->tax / 100);
            $payment = $this->paymentRepository->create([
                "user_id" => $input['user_id'],
                "description" => trans("lang.payment_order_waiting"),
                "price" => $amountWithTax,
                "status" => 'Waiting for Client',
                "method" => $input['payment']['method'],
            ]);

            $this->orderRepository->update(['payment_id' => $payment->id], $order->id);

            $this->cartRepository->deleteWhere(['user_id' => $order->user_id]);

            Notification::send($order->productOrders[0]->product->market->users, new NewOrder($order));

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

    /**
     * Update the specified Order in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $oldOrder = $this->orderRepository->findWithoutFail($id);
        if (empty($oldOrder)) {
            return $this->sendError('Order not found');
        }
        $oldStatus = $oldOrder->payment->status;
        $input = $request->all();

        try {
            $order = $this->orderRepository->update($input, $id);
            if (isset($input['order_status_id']) && $input['order_status_id'] == 5 && !empty($order)) {
                $this->paymentRepository->update(['status' => 'Paid'], $order['payment_id']);
            }
            event(new OrderChangedEvent($oldStatus, $order));

            if (setting('enable_notifications', false)) {
                if (isset($input['order_status_id']) && $input['order_status_id'] != $oldOrder->order_status_id) {
                    Notification::send([$order->user], new StatusChangedOrder($order));
                }

                if (isset($input['driver_id']) && ($input['driver_id'] != $oldOrder['driver_id'])) {
                    $driver = $this->userRepository->findWithoutFail($input['driver_id']);
                    if (!empty($driver)) {
                        Notification::send([$driver], new AssignedOrder($order));
                    }
                }
            }

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($order->toArray(), __('lang.saved_successfully', ['operator' => __('lang.order')]));
    }

}
