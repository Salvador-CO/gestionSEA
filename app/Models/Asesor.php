<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asesor extends Model
{
    use HasFactory;

    protected $table = 'asesores';

    protected $fillable = [
        'matricula',
        'nombre',
        'apellidos',
        'correo',
        'contacto',
        'cargo_id',
        'centro_id' // Asegúrate de haber agregado esta columna en la DB
    ];

    /**
     * Relación: El asesor pertenece a un centro de adscripción.
     * ESTA ES LA QUE FALTABA Y CAUSABA EL ERROR
     */
    public function centro()
    {
        return $this->belongsTo(Centro::class, 'centro_id');
    }

    /**
     * Relación: El asesor pertenece a un cargo específico.
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    /**
     * Relación: Un asesor puede tener múltiples grupos asignados.
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'asesor_id');
    }

    /**
     * Accesor opcional para obtener el nombre completo fácilmente.
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} {$this->apellidos}";
    }
}