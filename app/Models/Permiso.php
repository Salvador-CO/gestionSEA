<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    public $timestamps = false;

    // ESTO ES LO QUE FALTA: Los campos que permites guardar masivamente
    protected $fillable = ['nombre', 'descripcion'];

    public function roles() {
        return $this->belongsToMany(Rol::class, 'rol_permisos', 'permiso_id', 'rol_id');
    }
}