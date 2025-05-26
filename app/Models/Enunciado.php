<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enunciado extends Model
{
    protected $table = 'enunciado';
    
    protected $fillable = [
        'descripcion',
        'practica_id',
        'modulo_id',
        'user_id',
        'fecha_limite',
        'rubrica_id',
        'grupo_id'
    ];

    protected $casts = [
        'fecha_limite' => 'datetime',
    ];

    public function practica()
    {
        return $this->belongsTo(Practica::class);
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class);
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}
