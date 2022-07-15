<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    protected $table = 'content';
    use SoftDeletes;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contentTitle', 'contentDate', 'contentIsOnline', 'id', 'contentIsShare', 'update_at', 'id_users', 'id_contentType'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
          
    ];

    public function paragraph(){
        return $this->hasMany(Paragraph::class, 'id_content');
    }

    public function Photo(){
        return $this->hasMany(Photos::class, 'id_content');
    }

    public function Video(){
        return $this->hasMany(Videos::class, 'id_content');
    }
    
    public function LongVideo(){
        return $this->hasMany(LongVideos::class, 'id_content');
    }

    public function Like(){
        return $this->hasMany(ContentLike::class, 'id_content');
    }

    public function Category(){
        return $this->belongsToMany(Category::class, 'videos', 'id_content', 'id_category');
    }

    public function View(){
        return $this->hasMany(Views::class, 'id_content');
    }
    
    public function User(){
        return $this->belongsToMany(User::class, 'content', 'id', 'id_users');
    }

    public function UserDescription(){
        return $this->hasOneThrough(UserDescription::class, User::class, 'id', 'id_users', 'id_users', 'id');
    }
    
    public function UserPhoto(){
        return $this->hasOneThrough(UserPhoto::class, User::class, 'id', 'id_users', 'id_users', 'id');
    }

    public function ParagraphPhotos(){
        return $this->hasManyThrough(ParagraphPhotos::class, Paragraph::class, 'id_content', 'id_paragraph', 'id', 'id');
    }
    
    public function Questions(){
        return $this->hasMany(Questions::class, 'id_content');
    }

    public function Answers(){
        return $this->hasManyThrough(Answers::class, Questions::class, 'id_content', 'id_questions', 'id', 'id');
    }
    
    public function Devoirs(){
        return $this->hasMany(Devoirs::class, 'id_content');
    }
}
