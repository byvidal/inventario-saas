<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CompanyScope implements Scope
{
    /**
     * Aplicar el scope a una consulta dada.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Solo aplicamos el filtro si hay un usuario logueado y tiene company_id asignado
        if (Auth::check() && Auth::user()->company_id) {
            // Usamos $model->getTable() para evitar errores de ambigüedad en Joins
            $builder->where($model->getTable() . '.company_id', Auth::user()->company_id);
        }
    }
}