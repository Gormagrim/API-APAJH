<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Message extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    protected $table = 'message';
    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fromUser', 'toUser', 'id', 'time', 'content', 'id_users', 'isRead', 'isReadTime'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function User()
    {
        return $this->belongsToMany(User::class, 'message', 'id', 'id_users');
    }
    
    public function DestiUser()
    {
        return $this->belongsToMany(User::class, 'message', 'id', 'toUser');
    }

    public function UserDescription()
    {
        return $this->hasOneThrough(UserDescription::class, User::class, 'id', 'id_users', 'id_users', 'id');
    }
    
    public function DestiUserDescription()
    {
        return $this->hasOneThrough(UserDescription::class, User::class, 'id', 'id_users', 'toUser', 'id');
    }
    
    public function Media(){
        return $this->hasOne(Media::class, 'id_message');
    }
    
}
