<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Centro extends Model
{
    use HasFactory;

    protected $table = 'centros';

    // Campos que se pueden llenar mediante formularios
    protected $fillable = [
        'clave',
        'nombre',
        'descripcion'
    ];

    /**
     * Relación: Un centro tiene muchos grupos asignados.
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'centro_id');
    }
}