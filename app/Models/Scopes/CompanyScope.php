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
        if (Auth::check()) {
            // 👇 MAGIA: Si eres el dueño del SaaS, salimos de la función sin aplicar filtros.
            // Esto te permite ver absolutamente todo en la base de datos.
            if (Auth::user()->role === 'super_admin') {
                return;
            }

            // Para los clientes (company_admin o user), aplicamos el filtro estricto de su empresa.
            if (Auth::user()->company_id) {
                // Usamos $model->getTable() para evitar errores de ambigüedad en Joins
                $builder->where($model->getTable() . '.company_id', Auth::user()->company_id);
            }
        }
    }
}