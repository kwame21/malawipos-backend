<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockHistory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(
            Product::with('category')->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string',
            'category_id'    => 'required|exists:categories,id',
            'buying_price'   => 'required|numeric',
            'selling_price'  => 'required|numeric',
            'stock'          => 'required|integer',
            'low_stock_limit'=> 'required|integer',
            'unit'           => 'required|string',
        ]);

        $product = Product::create($request->all());

        // Record stock history
        StockHistory::create([
            'product_id' => $product->id,
            'user_id'    => $request->user()->id,
            'type'       => 'add',
            'quantity'   => $product->stock,
            'note'       => 'Initial stock',
        ]);

        return response()->json($product->load('category'), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'           => 'required|string',
            'category_id'    => 'required|exists:categories,id',
            'buying_price'   => 'required|numeric',
            'selling_price'  => 'required|numeric',
            'stock'          => 'required|integer',
            'low_stock_limit'=> 'required|integer',
            'unit'           => 'required|string',
        ]);

        $product->update($request->all());

        return response()->json($product->load('category'));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    public function lowStock()
    {
        $products = Product::with('category')
            ->whereColumn('stock', '<=', 'low_stock_limit')
            ->get();

        return response()->json($products);
    }
}