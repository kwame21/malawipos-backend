<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'category_id', 'buying_price',
        'selling_price', 'stock', 'low_stock_limit',
        'barcode', 'unit'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockHistory()
    {
        return $this->hasMany(StockHistory::class);
    }
}