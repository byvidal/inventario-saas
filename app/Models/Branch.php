<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\BelongsToCompany;

class Branch extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'address',
        'phone',
        'is_main',
    ];

    public function productBranches(): HasMany
    {
        return $this->hasMany(ProductBranch::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}