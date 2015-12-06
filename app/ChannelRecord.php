<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/29
 * Time: 0:26
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChannelRecord extends Model {

    protected $table = 'channel_record';

    protected $fillable = ['channel_id','record_type','date','value'];

    public $timestamps = false;

    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

}