<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LowStockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'inventory_before',
        'inventory_after',
        'notified_to',
        'notified_at'
    ];
    
    protected $casts = [
        'notified_to' => 'array',
        'notified_at' => 'datetime'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
