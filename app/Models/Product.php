<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /**
     * Los atributos que son asignables en masa.
     * Esto soluciona el error "Add [field] to fillable property".
     */
    protected $fillable = [
        'company_id',
        'name',
        'sku',
        'description',
        'price',
        'weight',      # <--- El nuevo campo que agregamos
        'category_id',
        'unit_id',
    ];

    # Relaciones: Permiten acceder a la categoría así: $product->category->name
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}