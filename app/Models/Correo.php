<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correo extends Model
{
    use HasFactory;

    protected $table = 'estudiante_correos';

    protected $fillable = [
        'plantel',
        'matricula',
        'nombre',
        'fecha_ingreso',
        'correo_personal',
        'correo_institucional',
        'clave_correo',
        'matricula_asesor',
        'nombre_asesor',
        'estatus',
        'fecha_entrega',
        'subido_por'
    ];

    protected $casts = [
        'fecha_entrega' => 'datetime',
    ];
}