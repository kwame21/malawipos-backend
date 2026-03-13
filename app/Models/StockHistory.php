<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $table = 'stock_history'; // 👈 add this line

    protected $fillable = [
        'product_id', 'user_id', 'type', 'quantity', 'note'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}