<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class ActivationCode extends Model
{
    const STATUS_VERIFIED = 1;
    const STATUS_UNVERIFIED = 0;
    const DETAULT_DURATION = 60;
    
    protected $table = 'activation_codes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'code', 'status','duration'
    ];
}
