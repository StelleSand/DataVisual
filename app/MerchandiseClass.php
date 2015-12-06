<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/28
 * Time: 20:32
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Merchandise;
class MerchandiseClass extends Model {

    protected $table = 'merchandise_class';

    protected $fillable = ['name','detail','create_user','create_time'];

    public function merchandises()
    {
        return $this->hasMany('App\Merchandise', 'merchandise_class', 'id');
    }

    public function createUser()
    {
        return $this->belongsTo('App\User','create_user','id');
    }

    /*
     * 获取某一MerchandiseClass在Orders数据中指定时间间隔中的累计销量
     * 参数为(较早时间点时间戳,较晚时间点时间戳);
     * 函数会计算两个时间点之间的Orders累计值返回返回。
     * 实际用法为MerchandiseClass::find(id)->getCumulativeSaleAmount(low,high);
     * */
    public function getCumulativeSaleAmount($timeStringLow,$timeStringHigh )
    {
        //选取所有Merchandises
        $merchandises = $this->merchandises()->get();
        $saleAmount = 0;
        foreach($merchandises as $merchandise)
        {
            //获取每一merchandise在指定时段的累计销售额
            $saleAmount += $merchandise->getCumulativeSaleAmount($timeStringLow,$timeStringHigh);
        }
        return $saleAmount;
    }

    /*
     * 获取某一MerchandiseClass在Orders数据中指定时间间隔中的累计销售额
     * 参数为(较早时间点时间戳,较晚时间点时间戳);
     * 函数会计算两个时间点之间的Orders累计值返回返回。
     * 实际用法为MerchandiseClass::find(id)->getCumulativeSaleVolume(low,high);
     * */
    public function getCumulativeSaleVolume($timeStringLow,$timeStringHigh )
    {
        //选取所有Merchandises
        $merchandises = $this->merchandises()->get();
        $saleVolume = 0;
        foreach($merchandises as $merchandise)
        {
            //获取每一merchandise在指定时段的累计销售额
            $saleVolume += $merchandise->getCumulativeSaleVolume($timeStringLow,$timeStringHigh);
        }
        return $saleVolume;
    }
}