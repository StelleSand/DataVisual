<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/12/16
 * Time: 16:05
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ChartLineMatch extends Model {

    protected $table = 'chart_line_match';

    protected $fillable = ['chart_id','line_id'];

}