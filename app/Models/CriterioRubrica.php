<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Rubrica;

class CriterioRubrica extends Model
{
    use HasFactory;

    protected $table = 'criterios_rubrica';

    protected $fillable = [
        'rubrica_id',
        'nombre',
        'puntuacion_maxima',
        'descripcion'
    ];

    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class);
    }
}
