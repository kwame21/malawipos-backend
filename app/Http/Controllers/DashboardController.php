<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Expense;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $todaySales = Sale::whereDate('created_at', today())->sum('total');
        $monthSales = Sale::whereMonth('created_at', now()->month)->sum('total');
        $totalProducts = Product::count();
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_limit')->count();
        $totalCustomers = Customer::count();
        $todayExpenses = Expense::whereDate('date', today())->sum('amount');
        $recentSales = Sale::with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        $topProducts = Product::withCount('saleItems')
            ->orderBy('sale_items_count', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'today_sales'       => $todaySales,
            'month_sales'       => $monthSales,
            'total_products'    => $totalProducts,
            'low_stock_products'=> $lowStockProducts,
            'total_customers'   => $totalCustomers,
            'today_expenses'    => $todayExpenses,
            'recent_sales'      => $recentSales,
            'top_products'      => $topProducts,
        ]);
    }
}