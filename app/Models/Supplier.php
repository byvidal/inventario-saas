<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\BelongsToCompany;

class Supplier extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'tax_id',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}