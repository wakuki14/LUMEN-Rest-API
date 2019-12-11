<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class City extends Model
{
    
    protected $table = 'cities';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'short_name', 'post_code', 'state', 'province', 'country_id'
    ];
    
    public $i18n = ['name', 'short_name'];
    
    public function getCreatedAtAttribute($date)
    {
        return strtotime($date);
    }
    
    public function getUpdatedAtAttribute($date)
    {
        return strtotime($date);
    }
}