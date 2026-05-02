<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToCompany;

class Tax extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'rate'];
}
