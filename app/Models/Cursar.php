<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cursar extends Model
{
    use HasFactory;
    protected $table = [
        'user_id',
        'grupo_id',
        'fecha_inicio',
        'fecha_fin'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }
}
