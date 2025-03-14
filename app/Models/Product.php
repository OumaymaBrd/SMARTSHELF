<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'alert_threshold',
        'category_id',
        'promotion_price',
        'promotion_start_date',
        'promotion_end_date',
        'promotion_history'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'promotion_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'alert_threshold' => 'integer',
        'promotion_start_date' => 'date',
        'promotion_end_date' => 'date',
        'promotion_history' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['current_price', 'is_on_promotion', 'in_stock'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function isLowStock()
    {
        return $this->stock_quantity <= $this->alert_threshold;
    }

    public function getIsOnPromotionAttribute()
    {
        if (!$this->promotion_price) {
            return false;
        }
        
        $today = Carbon::today();
        
        return $this->promotion_price &&
               $this->promotion_start_date &&
               $this->promotion_end_date &&
               $today->between($this->promotion_start_date, $this->promotion_end_date);
    }

    public function getCurrentPriceAttribute()
    {
        return $this->is_on_promotion ? $this->promotion_price : $this->price;
    }

    public function getInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }

    public function endPromotion()
    {
        if (!$this->is_on_promotion) {
            return false;
        }

        // Sauvegarder l'historique de promotion
        $history = $this->promotion_history ?? [];
        $history[] = [
            'original_price' => $this->price,
            'promotion_price' => $this->promotion_price,
            'start_date' => $this->promotion_start_date->format('Y-m-d'),
            'end_date' => $this->promotion_end_date->format('Y-m-d'),
            'ended_at' => Carbon::now()->format('Y-m-d H:i:s')
        ];

        $this->update([
            'promotion_history' => $history,
            'promotion_price' => null,
            'promotion_start_date' => null,
            'promotion_end_date' => null
        ]);

        return true;
    }
}