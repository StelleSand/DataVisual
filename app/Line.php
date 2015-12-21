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

    /*
     * line的formula计算函数，非静态
     * 参数为(较早时间点时间戳,较晚时间点时间戳);
     * 支持的解析标识符包括：m——merchandise;c——merchandise_class;u——unit
     * */
    public function formulaCalculate($timeStringLow,$timeStringHigh)
    {
        //获取要解析的字符串
        $formula = $this->formula;
        //初始化指针指向0
        $pointer = 0;
        //解析直到完成
        while(strlen($formula)>0)
        {
            if($formula[$pointer] == 'm')
            {
                return ;
            }
        }
    }
}