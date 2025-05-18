<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Practica;
use App\Models\Nota;



class Entrega extends Model
{
    use HasFactory;

    protected $fillable = [
        'practica_id',
        'user_id',
        'fecha_entrega',
        'archivo'
    ];

    public function practica()
    {
        return $this->belongsTo(Practica::class);
    }

    public function alumno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function nota()
    {
        return $this->hasOne(Nota::class);
    }
}
