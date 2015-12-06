<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/28
 * Time: 20:37
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Order;
class Merchandise extends Model {

    protected $table = 'merchandise';

    protected $fillable = ['name','detail','pricing','merchandise_class','create_time'];

    public function orders()
    {
        return $this->hasMany('App\Orders');
    }

    public function merchandiseClass()
    {
        return $this->belongsTo('App\MerchandiseClass', 'merchandise_class', 'id');
    }

    /*
     * 获取Orders数据在指定时间间隔中的累计销量
     * 参数为(较早时间点时间戳,较晚时间点时间戳);
     * 函数会计算两个时间点之间的Orders累计值返回返回。
     * 实际用法为Merchandise::find(id)->getCumulativeSaleAmount(low,high);
     * */
    public function getCumulativeSaleAmount($timeStringLow,$timeStringHigh )
    {
        //选取对应时间段数据,目前设置为选取print_date,此字段可调整
        $orders = $this->orders()->whereBetween('print_date',array($timeStringLow,$timeStringHigh))->get();
        $SaleAmount = 0;
        //获取对应时间点附近数据和
        foreach($orders as $order)
        {
            $SaleAmount += $order->price * $order->quantity;
        }
        return $SaleAmount;
    }

    /*
     * 获取Orders数据在指定时间间隔中的累计销售额
     * 参数为(较早时间点时间戳,较晚时间点时间戳);
     * 函数会计算两个时间点之间的Orders累计值返回返回。
     * 实际用法为Merchandise::find(id)->getCumulativeSaleVolume(low,high);
     * */
    public function getCumulativeSaleVolume($timeStringLow,$timeStringHigh )
    {
        //选取对应时间段数据
        $orders = $this->orders()->whereBetween('print_date',array($timeStringLow,$timeStringHigh))->get();
        $SaleVolume = 0;
        //获取对应时间点附近数据和
        foreach($orders as $order)
        {
            $SaleVolume += $order->quantity;
        }
        return $SaleVolume;
    }
}