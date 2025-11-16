<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'available',
        'reserved',
        'low_stock_threshold'
    ];
    
    protected $casts = [
        'available' => 'integer',
        'reserved' => 'integer',
        'low_stock_threshold' => 'integer'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
