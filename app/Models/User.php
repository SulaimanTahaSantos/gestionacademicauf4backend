<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Grupo;
use App\Models\Clase;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Perfil;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'dni',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function grupo()
    {
        return $this->hasOne(Grupo::class);
        return $this->belongsToMany(Grupo::class, 'cursar')->withTimestamps();

    }

    public function clase()
    {
        return $this->hasOne(Clase::class);
    }

    public function perfil()
    {
    return $this->belongsTo(Perfil::class);
    
    }

    public function cursars()
    {
    return $this->hasMany(Cursar::class);
    }

    public function gruposActuales()
    {
    return $this->belongsToMany(Grupo::class, 'cursars')->withTimestamps();
    }

      public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
