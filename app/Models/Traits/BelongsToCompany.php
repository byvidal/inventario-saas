<?php

namespace App\Models\Traits;

use App\Models\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCompany
{
    /**
     * Iniciar el trait automáticamente al arrancar el modelo.
     */
    protected static function bootBelongsToCompany()
    {
        // 1. Aplicar el filtro de lectura global
        static::addGlobalScope(new CompanyScope);

        // 2. Inyectar company_id al crear registros
        static::creating(function (Model $model) {
            if (Auth::check() && !$model->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }
}