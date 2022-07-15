<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class ContentLike extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    protected $table = 'contentlike';
    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_users',  'id_content'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function content()
    {
        return $this->hasMany(Content::class, 'id', 'id_content');
    }

    public function users()
    {
        return $this->hasMany(Users::class, 'id');
    }

    public function video()
    {
        return $this->hasOneThrough(Videos::class, Content::class, 'id', 'id_content', 'id_content', 'id');
    }

    public function category()
    {
        return $this->hasOneThrough(Category::class, Videos::class, 'id', 'id', 'id_content', 'id_category');
    }

}
