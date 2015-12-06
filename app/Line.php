<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/29
 * Time: 0:08
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Line extends Model {

    protected $table = 'line';

    protected $fillable = ['name','detail','formula','create_user','create_time'];

    public function createUser()
    {
        return $this->belongsTo('App\User','create_user','id');
    }
}