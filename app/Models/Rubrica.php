<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubrica extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'practica_id',
        'documento',
    ];

    public function practica()
    {
        return $this->belongsTo(Practica::class);
    }

    public function evaluadores()
    {
        return $this->belongsToMany(User::class, 'evaluador_rubrica', 'rubrica_id', 'user_id');
    }
}
