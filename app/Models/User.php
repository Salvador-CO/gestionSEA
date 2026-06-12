<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    
    // Agregamos 'centro' y 'moodle_user_id' a fillable
    protected $fillable = [
        'username', 
        'password', 
        'email', 
        'nombre', 
        'apellido', 
        'centro', 
        'rol_id', 
        'activo', 
        'moodle_user_id'
    ];

    public function rol() {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function tienePermiso($permisoNombre) {
        if (!$this->rol) return false;
        return $this->rol->permisos()->where('nombre', $permisoNombre)->exists();
    }
}