<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false; // Solo usamos created_at manual

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'username',
        'accion',
        'modulo',
        'descripcion',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relación con el usuario del sistema (puede ser null si fue eliminado)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
