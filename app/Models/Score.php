<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Score extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    protected $table = 'score';
    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_game', 'id_users', 'id', 'time', 'difficulty', 'score', 'trials', 'victory'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function User()
    {
        return $this->belongsToMany(User::class, 'score', 'id', 'id_users');
    }

    public function game()
    {
        // return $this->belongsToMany(Game::class, 'score', 'id', 'id_game');
        return $this->hasOneThrough(Game::class, Score::class, 'id', 'id', 'id_game', 'id');
    }

    public function UserDescription()
    {
        return $this->hasOneThrough(UserDescription::class, User::class, 'id', 'id_users', 'id_users', 'id');
    }
}
