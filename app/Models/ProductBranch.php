<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // 👈 Importante

class ProductBranch extends Pivot
{
    protected $table = 'product_branch';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'branch_id',
        'quantity',
    ];

    // 👇 ESTO ERA LO QUE FALTABA
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}