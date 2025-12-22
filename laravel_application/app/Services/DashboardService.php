<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Auction;
use App\Models\Shipment;
use App\Models\Remittance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * خدمة لوحة التحكم والتقارير
 */
class DashboardService
{
    /**
     * إحصائيات لوحة التحكم الرئيسية
     */
    public function getMainDashboardStats()
    {
        return Cache::remember('dashboard_main_stats', 300, function () {
            return [
                'users' => $this->getUserStats(),
                'orders' => $this->getOrderStats(),
                'revenue' => $this->getRevenueStats(),
                'products' => $this->getProductStats(),
                'auctions' => $this->getAuctionStats(),
                'shipments' => $this->getShipmentStats(),
                'remittances' => $this->getRemittanceStats(),
            ];
        });
    }

    /**
     * إحصائيات المستخدمين
     */
    public function getUserStats()
    {
        return [
            'total' => User::count(),
            'active_today' => User::where('last_seen_at', '>=', Carbon::today())->count(),
            'new_this_month' => User::whereMonth('created_at', Carbon::now()->month)->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'merchants' => User::where('user_type', 'merchant')->count(),
            'customers' => User::where('user_type', 'customer')->count(),
            'expatriates' => User::where('is_expatriate', true)->count(),
        ];
    }

    /**
     * إحصائيات الطلبات
     */
    public function getOrderStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total' => Order::count(),
            'today' => Order::whereDate('created_at', $today)->count(),
            'this_month' => Order::where('created_at', '>=', $thisMonth)->count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'average_value' => Order::avg('total_amount'),
        ];
    }

    /**
     * إحصائيات الإيرادات
     */
    public function getRevenueStats()
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentMonth = Order::where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)
            ->sum('total_amount');

        $previousMonth = Order::where('status', 'completed')
            ->whereBetween('created_at', [$lastMonth, $thisMonth])
            ->sum('total_amount');

        $platformCommission = Order::where('status', 'completed')
            ->sum(DB::raw('total_amount * 0.05')); // 5% commission

        return [
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'this_month' => $currentMonth,
            'last_month' => $previousMonth,
            'growth_percentage' => $previousMonth > 0 ? (($currentMonth - $previousMonth) / $previousMonth) * 100 : 0,
            'platform_commission' => $platformCommission,
            'currency' => 'SDG',
        ];
    }

    /**
     * إحصائيات المنتجات
     */
    public function getProductStats()
    {
        return [
            'total' => Product::count(),
            'active' => Product::where('active', true)->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
            'low_stock' => Product::whereBetween('stock_quantity', [1, 10])->count(),
            'top_selling' => $this->getTopSellingProducts(5),
            'latest' => Product::latest()->take(5)->get(),
        ];
    }

    /**
     * إحصائيات المزادات
     */
    public function getAuctionStats()
    {
        return [
            'total' => Auction::count(),
            'active' => Auction::where('status', 'active')->count(),
            'completed' => Auction::where('status', 'completed')->count(),
            'product_auctions' => Auction::where('type', 'product')->count(),
            'delivery_auctions' => Auction::where('type', 'delivery')->count(),
            'international_auctions' => Auction::where('type', 'international')->count(),
            'total_bids' => DB::table('auction_bids')->count(),
        ];
    }

    /**
     * إحصائيات الشحنات
     */
    public function getShipmentStats()
    {
        return [
            'total' => Shipment::count(),
            'pending' => Shipment::where('status', 'pending')->count(),
            'in_transit' => Shipment::where('status', 'in_transit')->count(),
            'delivered' => Shipment::where('status', 'delivered')->count(),
            'failed' => Shipment::where('status', 'failed')->count(),
            'on_time_rate' => $this->calculateOnTimeDeliveryRate(),
        ];
    }

    /**
     * إحصائيات التحويلات المالية
     */
    public function getRemittanceStats()
    {
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total' => Remittance::count(),
            'this_month' => Remittance::where('created_at', '>=', $thisMonth)->count(),
            'pending' => Remittance::where('status', 'pending')->count(),
            'completed' => Remittance::where('status', 'completed')->count(),
            'total_amount' => Remittance::where('status', 'completed')->sum('receive_amount'),
            'by_delivery_method' => Remittance::select('delivery_method', DB::raw('count(*) as count'))
                ->groupBy('delivery_method')
                ->get(),
        ];
    }

    /**
     * الأكثر مبيعاً
     */
    protected function getTopSellingProducts($limit = 10)
    {
        return Product::select('products.*', DB::raw('COUNT(order_items.id) as sales_count'))
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy('products.id')
            ->orderByDesc('sales_count')
            ->limit($limit)
            ->get();
    }

    /**
     * حساب معدل التوصيل في الوقت المحدد
     */
    protected function calculateOnTimeDeliveryRate()
    {
        $totalDelivered = Shipment::where('status', 'delivered')->count();

        if ($totalDelivered === 0) {
            return 0;
        }

        $onTime = Shipment::where('status', 'delivered')
            ->whereRaw('delivered_at <= estimated_delivery_date')
            ->count();

        return ($onTime / $totalDelivered) * 100;
    }

    /**
     * تقرير المبيعات حسب الفترة
     */
    public function getSalesReport($startDate, $endDate, $groupBy = 'day')
    {
        $query = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $format = $groupBy === 'day' ? '%Y-%m-%d' : '%Y-%m';

        return $query->select(
            DB::raw("DATE_FORMAT(created_at, '{$format}') as period"),
            DB::raw('COUNT(*) as order_count'),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('AVG(total_amount) as average_order_value')
        )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * تقرير حسب المدينة
     */
    public function getReportByCity()
    {
        return Order::select('city', DB::raw('COUNT(*) as order_count'), DB::raw('SUM(total_amount) as revenue'))
            ->where('status', 'completed')
            ->groupBy('city')
            ->orderByDesc('order_count')
            ->get();
    }

    /**
     * تقرير التجار الأكثر نشاطاً
     */
    public function getTopMerchants($limit = 10)
    {
        return User::where('user_type', 'merchant')
            ->select('users.*',
                DB::raw('(SELECT COUNT(*) FROM orders WHERE orders.merchant_id = users.id) as order_count'),
                DB::raw('(SELECT SUM(total_amount) FROM orders WHERE orders.merchant_id = users.id AND status = "completed") as total_revenue')
            )
            ->orderByDesc('order_count')
            ->limit($limit)
            ->get();
    }

    /**
     * تقرير شركات الشحن
     */
    public function getShippingCompaniesReport()
    {
        return DB::table('shipping_companies')
            ->select(
                'shipping_companies.*',
                DB::raw('COUNT(shipments.id) as total_shipments'),
                DB::raw('AVG(shipping_companies.rating) as average_rating')
            )
            ->leftJoin('shipments', 'shipping_companies.id', '=', 'shipments.company_id')
            ->groupBy('shipping_companies.id')
            ->orderByDesc('total_shipments')
            ->get();
    }

    /**
     * تقرير المزادات
     */
    public function getAuctionsReport($startDate = null, $endDate = null)
    {
        $query = Auction::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return [
            'total_auctions' => $query->count(),
            'by_type' => $query->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get(),
            'by_status' => $query->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get(),
            'total_bids' => DB::table('auction_bids')
                ->whereIn('auction_id', $query->pluck('id'))
                ->count(),
            'average_bids_per_auction' => DB::table('auction_bids')
                ->whereIn('auction_id', $query->pluck('id'))
                ->count() / max($query->count(), 1),
        ];
    }

    /**
     * تقرير المغتربين
     */
    public function getExpatriateReport()
    {
        $expatriates = User::where('is_expatriate', true)->get();

        return [
            'total_expatriates' => $expatriates->count(),
            'by_location' => $expatriates->groupBy('expatriate_location')->map->count(),
            'total_remittances' => Remittance::whereIn('sender_user_id', $expatriates->pluck('id'))->count(),
            'total_remittance_amount' => Remittance::whereIn('sender_user_id', $expatriates->pluck('id'))
                ->where('status', 'completed')
                ->sum('send_amount'),
            'popular_destinations' => User::where('is_expatriate', true)
                ->select('expatriate_location', DB::raw('COUNT(*) as count'))
                ->groupBy('expatriate_location')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * ملخص الأداء اليومي
     */
    public function getDailyPerformance()
    {
        $today = Carbon::today();

        return [
            'date' => $today->format('Y-m-d'),
            'orders' => Order::whereDate('created_at', $today)->count(),
            'revenue' => Order::whereDate('created_at', $today)->where('status', 'completed')->sum('total_amount'),
            'new_users' => User::whereDate('created_at', $today)->count(),
            'active_auctions' => Auction::where('status', 'active')->count(),
            'deliveries' => Shipment::whereDate('delivered_at', $today)->count(),
            'remittances' => Remittance::whereDate('created_at', $today)->count(),
        ];
    }

    /**
     * تقرير شامل للتصدير
     */
    public function generateFullReport($startDate, $endDate)
    {
        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'sales' => $this->getSalesReport($startDate, $endDate),
            'orders' => $this->getOrderStats(),
            'revenue' => $this->getRevenueStats(),
            'users' => $this->getUserStats(),
            'products' => $this->getProductStats(),
            'auctions' => $this->getAuctionsReport($startDate, $endDate),
            'shipments' => $this->getShipmentStats(),
            'remittances' => $this->getRemittanceStats(),
            'top_merchants' => $this->getTopMerchants(),
            'by_city' => $this->getReportByCity(),
            'expatriates' => $this->getExpatriateReport(),
            'generated_at' => Carbon::now()->toDateTimeString(),
        ];
    }
}
