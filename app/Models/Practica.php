<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Practica extends Model
{
    use HasFactory;

    protected $fillable = [
        'identificador',
        'titulo',
        'descripcion',
        'modulo_id',
        'nombre_practica',
        'profesor_id',
        'fecha_entrega',
        'rubrica_id',
        'grupo_id'
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}
