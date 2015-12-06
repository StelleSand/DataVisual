<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/28
 * Time: 20:23
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chart extends Model{

    protected $table = 'chart';

    protected $fillable = ['name','detail','create_user','create_time'];

    public function createUser()
    {
        return $this->belongsTo('App\User','create_user','id');
    }
}