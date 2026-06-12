<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $fillable = ['codigo_moodle', 'centro_id', 'asignatura_id', 'asesor_id', 'p_numero'];

    public function centro() { return $this->belongsTo(Centro::class); }
    public function asignatura() { return $this->belongsTo(Asignatura::class); }
    public function asesor() { return $this->belongsTo(Asesor::class); }
}