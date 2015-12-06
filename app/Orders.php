<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/29
 * Time: 0:28
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model {

    protected $table = 'orders';

    protected $fillable = ['order_no','quantity','price','create_date','print_date','merchandise_id'];

    public $timestamps = false;

    public function merchandise()
    {
        return $this->belongsTo('App\Merchandise');
    }

}