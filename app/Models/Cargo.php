<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargos';

    protected $fillable = [
        'nombre'
    ];

    /**
     * Relación: Un cargo (ej. Psicopedagogo) puede pertenecer a muchos asesores.
     */
    public function asesores()
    {
        return $this->hasMany(Asesor::class, 'cargo_id');
    }
}