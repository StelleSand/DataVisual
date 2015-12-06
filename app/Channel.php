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
     * 实际用法为Channel::find(channel_id)->getAverageValue(low,high);
     * */
    public function getAveragePower($timeStringLow,$timeStringHigh )
    {
        //选取对应时间段数据
        $records = $this->records()->whereBetween('date',array($timeStringLow,$timeStringHigh))->where('record_type','=','power')->get();
        $records_sum = 0;
        //获取对应时间点附近数据和
        foreach($records as $record)
        {
            $records_sum += $record->value;
        }
        //求对应时间点附近最终数据值
        //防止除0
        if(count($records) == 0)
            $average_value = 0;
        else
            $average_value = $records_sum / count($records);
        return $average_value;
    }

}