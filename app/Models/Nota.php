<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Entrega;
use App\Models\User;
use App\Models\Rubrica;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = [
        'entrega_id',
        'user_id',
        'rubrica_id',
        'nota_final',
        'comentario'
    ];

    public function entrega()
    {
        return $this->belongsTo(Entrega::class);
    }

    public function evaluador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class);
    }
}
