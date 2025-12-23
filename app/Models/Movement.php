<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movement extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'product_id',
        'supplier_id', // 👈 ¡Nuevo!
        'type',        // purchase, sale, adjustment...
        'quantity',
        'cost_at_movement',
        'price_at_movement',
        'notes',
    ];

    // Relación con el Producto
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con el Proveedor
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relación con la Sucursal
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Relación con el Usuario que hizo el movimiento
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}