<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/29
 * Time: 0:26
 */

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ChannelRecord extends Model {

    protected $table = 'channel_record';

    protected $fillable = ['channel_id','record_type','date','value'];

    public $timestamps = false;

    public $timeZone = 'Asia/Shanghai';

    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    //计算当前记录值与指定记录（应该是相邻的下一条记录）之间的用电量，单位为焦耳J
    public function getCalculativePower($anotherPowerRecord)
    {
        $carbonA = Carbon::createFromFormat('Y-m-d H:i:s',$this->date, $this->timeZone);
        $carbonB = Carbon::createFromFormat('Y-m-d H:i:s',$anotherPowerRecord->date, $this->timeZone);
        $timeInterval = $carbonA->diffInSeconds($carbonB, true);
        return $this->value * $timeInterval;
    }

}