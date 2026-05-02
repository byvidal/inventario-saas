<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToCompany;

class Client extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'email', 'phone', 'tax_id'];
}
