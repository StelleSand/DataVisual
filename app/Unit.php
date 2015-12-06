<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/28
 * Time: 20:21
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model {

    protected $table = 'unit';

    protected $fillable = ['name','detail','formula','create_user'];

    public function createUser()
    {
        return $this->belongsTo('App\User','create_user','id');
    }
}