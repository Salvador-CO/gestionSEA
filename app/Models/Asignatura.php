<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignatura extends Model
{
    use HasFactory;

    protected $table = 'asignaturas';

    protected $fillable = [
        'clave',
        'nombre',
        'semestre'
    ];

    /**
     * Relación: Una asignatura puede estar en muchos grupos.
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'asignatura_id');
    }
}