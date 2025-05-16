<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'codigo', 'descripcion', 'curso_id'];
    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'impartido_modulos')->withTimestamps();
    }

    public function practicas()
    {
        return $this->hasMany(Practica::class);
    }
}
