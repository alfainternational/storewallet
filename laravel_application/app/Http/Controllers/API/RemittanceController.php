<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Remittance;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RemittanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Remittance::with(['sender', 'recipient', 'currency', 'recipientCity'])
            ->where(function($q) use ($request) {
                $q->where('sender_id', $request->user()->id)
                  ->orWhere('recipient_id', $request->user()->id);
            })
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $remittances = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'remittances' => $remittances->items(),
            'total' => $remittances->total(),
        ]);
    }

    public function show($id)
    {
        $remittance = Remittance::with([
            'sender',
            'recipient',
            'currency',
            'recipientCity'
        ])->findOrFail($id);

        return response()->json(['success' => true, 'remittance' => $remittance]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_first_name' => 'required|string|max:255',
            'sender_last_name' => 'required|string|max:255',
            'sender_phone' => 'required|string',
            'sender_country' => 'required|string',
            'recipient_first_name' => 'required|string|max:255',
            'recipient_last_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string',
            'recipient_city_id' => 'required|exists:sudanese_cities,id',
            'amount' => 'required|numeric|min:1',
            'currency_id' => 'required|exists:currencies,id',
            'receiving_method' => 'required|in:bank_transfer,mobile_wallet,cash_pickup,door_delivery',
            'bank_account' => 'required_if:receiving_method,bank_transfer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = $request->user();
            $currency = Currency::findOrFail($request->currency_id);

            // Calculate SDG amount
            $amountInSDG = $request->amount * $currency->exchange_rate;
            $fees = $amountInSDG * 0.02; // 2% fee
            $totalAmount = $amountInSDG + $fees;

            // Check wallet balance
            $availableBalance = $user->wallet_balance - $user->escrowTransactions()->where('status', 'holding')->sum('amount');
            if ($availableBalance < $totalAmount) {
                return response()->json(['success' => false, 'message' => 'Insufficient wallet balance'], 422);
            }

            // Create remittance
            $remittance = Remittance::create([
                'sender_id' => $user->id,
                'remittance_number' => 'RMT-' . strtoupper(uniqid()),
                'sender_name' => $request->sender_first_name . ' ' . $request->sender_last_name,
                'recipient_name' => $request->recipient_first_name . ' ' . $request->recipient_last_name,
                'recipient_phone' => $request->recipient_phone,
                'recipient_city_id' => $request->recipient_city_id,
                'amount' => $request->amount,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $currency->exchange_rate,
                'amount_in_sdg' => $amountInSDG,
                'fees' => $fees,
                'total_amount' => $totalAmount,
                'receiving_method' => $request->receiving_method,
                'bank_account' => $request->bank_account,
                'pickup_code' => $request->receiving_method === 'cash_pickup' ? rand(100000, 999999) : null,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Deduct from wallet
            $user->decrement('wallet_balance', $totalAmount);

            // Create wallet transaction
            \App\Models\WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'remittance',
                'amount' => $totalAmount,
                'status' => 'completed',
                'description' => 'Remittance to ' . $remittance->recipient_name,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'remittance' => $remittance], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Remittance failed: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,completed,cancelled,failed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $remittance = Remittance::findOrFail($id);

        $remittance->update([
            'status' => $request->status,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        return response()->json(['success' => true, 'remittance' => $remittance]);
    }
}
