<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
   
    use Authenticatable, Authorizable;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_LOCK = 3;
    const DELETE_YES = 1;
    const DELETE_NO = 0;
    
    const DEFAULT_PASS_SOCIAL_LOGIN = '@Admin.123456';
    const LOGIN_FROM_FACEBOOK = 1;
    const LOGIN_FROM_GOOGLE = 2;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phone','avatar', 'country_id', 'city_id', 'gender', 'birthday'
    ];

    public $i18n = ['address', 'bio'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
    
    public function getCreatedAtAttribute($date)
    {
        return strtotime($date);
    }
    
    public function getUpdatedAtAttribute($date)
    {
        return strtotime($date);
    }
    
    public function getBirthdayAttribute($date)
    {
        return strtotime($date);
    }
    
    public function getLastLoginAttribute($date)
    {
        return strtotime($date);
    }
}
