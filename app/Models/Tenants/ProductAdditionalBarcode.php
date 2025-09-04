<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAdditionalBarcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'additional_barcode',
        'is_deleted',
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get only active (non-deleted) barcodes
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }
}