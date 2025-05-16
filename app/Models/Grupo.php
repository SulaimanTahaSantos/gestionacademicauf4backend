<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Cursar;
use App\Models\Modulo;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupo';

    protected $fillable = [
        'nombre',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
        return $this->belongsToMany(User::class, 'cursar')->withTimestamps();

    }

    public function cursars()
{
    return $this->hasMany(Cursar::class);
}
}
