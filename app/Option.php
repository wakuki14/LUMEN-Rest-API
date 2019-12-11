<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Option extends Model
{
    const TYPE_STRING = 1;
    const TYPE_TEXT = 2;
    const TYPE_INT = 3;
    const TYPE_TINYINT = 4;
    const TYPE_FLOAT = 5;
    const TYPE_ENUM = 6;
    const TYPE_BOOL = 7;
    
    protected $table = 'options';
    
    public function getCreatedAtAttribute($date)
    {
        return strtotime($date);
    }
    
    public function getUpdatedAtAttribute($date)
    {
        return strtotime($date);
    }
}