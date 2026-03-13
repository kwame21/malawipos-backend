<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        return response()->json(
            Sale::with(['customer', 'user', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'items.*.subtotal'   => 'required|numeric',
            'total'          => 'required|numeric',
            'amount_paid'    => 'required|numeric',
            'payment_method' => 'required|string',
            'discount'       => 'nullable|numeric',
            'customer_id'    => 'nullable|exists:customers,id',
        ]);



        DB::transaction(function () use ($request, &$sale) {
            $sale = Sale::create([
                'user_id'        => $request->user()->id,
                'customer_id'    => $request->customer_id,
                'total'          => $request->total,
                'discount'       => $request->discount ?? 0,
                'amount_paid'    => $request->amount_paid,
                'change'         => $request->amount_paid - $request->total,
                'payment_method' => $request->payment_method,
            ]);

            foreach ($request->items as $item) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal'   => $item['subtotal'],
                ]);

                // Deduct stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);

                // Record stock history
                StockHistory::create([
                    'product_id' => $item['product_id'],
                    'user_id'    => $request->user()->id,
                    'type'       => 'sale',
                    'quantity'   => $item['quantity'],
                    'note'       => 'Sale #' . $sale->id,
                ]);
            }
        });

        return response()->json(
            $sale->load(['items.product', 'customer']), 201
        );
    }

    public function show(Sale $sale)
    {
        return response()->json(
            $sale->load(['items.product', 'customer', 'user'])
        );
    }

    public function update(Request $request, Sale $sale) {}

    public function destroy(Sale $sale)
    {
        $sale->delete();
        return response()->json(['message' => 'Sale deleted']);
    }

    public function dailyReport()
    {
        $sales = Sale::with(['items.product'])
            ->whereDate('created_at', today())
            ->get();

        return response()->json([
            'total_sales'    => $sales->count(),
            'total_revenue'  => $sales->sum('total'),
            'total_discount' => $sales->sum('discount'),
            'sales'          => $sales,
        ]);
    }

    public function monthlyReport()
    {
        $sales = Sale::selectRaw('DATE(created_at) as date, COUNT(*) as total_sales, SUM(total) as total_revenue')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($sales);
    }
}