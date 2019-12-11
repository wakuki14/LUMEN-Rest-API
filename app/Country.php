<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Country extends Model
{
    
    protected $table = 'countries';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'iso_code', 'phone_code'
    ];
    
    public $i18n = ['name'];
    
    public function getCreatedAtAttribute($date)
    {
        return strtotime($date);
    }
    
    public function getUpdatedAtAttribute($date)
    {
        return strtotime($date);
    }
}