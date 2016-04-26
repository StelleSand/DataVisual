<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/28
 * Time: 20:19
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\ChannelRecord;

class Channel extends Model {

    protected $table = 'channel';

    protected $fillable = ['name','detail','channel_number','receiver_id','nominal_power','power_factor','create_user'];

    public function records()
    {
        return $this->hasMany('App\ChannelRecord');
    }

    public function createUser()
    {
        return $this->belongsTo('App\User','create_user','id');
    }

    /*
     * 获取ChannelRecord数据的平均值
     * 参数为(较早时间点时间戳,较晚时间点时间戳);
     * 函数会计算两个时间点之间的Channel_Power加权平均值作为平均返回。
     * 此函数适用于实际查询，要求两个时间点之间间隔较小，否则误差较大
     * 实际用法为Channel::find(channel_id)->getAverageValue(low,high);
     * */
    public function getAveragePower($timeStringLow,$timeStringHigh )
    {
        return  $this->records()->whereBetween('date',array($timeStringLow,$timeStringHigh))->where('record_type','=','power')->avg('value');
    }

    //获得指定channel在指定时间段之内的累计用电量，单位为焦耳J
    public function getCumulativePower($timeStringLow,$timeStringHigh)
    {
        $records = $this->records()->whereBetween('date',array($timeStringLow,$timeStringHigh))->where('record_type','=','power')->orderBy('date')->get();
        $sum = 0;
        for($i = 0; $i < count($records); $i++) {
            if (isset($records[$i + 1])) {//对于最后一个元素不进行累加
                $sum += $records[$i]->getCalculativePower($records[$i + 1]);
            }
        }
        return $sum;
    }

}