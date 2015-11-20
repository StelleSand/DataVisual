<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/19
 * Time: 16:33
 */
namespace App\Http\Controllers;

use Excel;
use App\Http\Controllers;
use Illuminate\Support\Facades\DB;

set_time_limit(0);
class ExcelImportController extends Controller {
    /*
     * 根据前端导入的Excel文件向数据库中插入原始数据
     * */

   /* //初始化数据表,仅为此项目静态演示使用
    //目前此函数已弃用，被seed填充替代
    public function initializeTables()
    {
        // 插入用户
        DB::insert('insert into users ( name, email,password,descreption ) values (?, ?, ?, ?)', ['BUAA', 'buaasoft@163.com','buaasoft','TEST!']);
        //插入10个频道的信息
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['所有',1,'100313',0,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['面炉',2,'100313',8,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['三个炉子',3,'100313',0,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['开水炉',4,'100313',5.94,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['排风扇/烟机',5,'100313',1.5,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['三个冰箱',6,'100313',0,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['展示柜冰箱',7,'100313',2,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['蒸包炉',8,'100313',3,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['保温台',9,'100313',2,1,1]);
        DB::insert('insert into channel (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['可乐插座',10,'100313',0,1,1]);
        //插入unit单元
        //DB::insert('insert into unit (name,channel_number,receiver_id,norminal_power,power_factor,create_user) values (?, ?, ?, ?, ?, ?)', ['所有',1,'100313',0,1,1]);
    }*/

    protected $channelRecordRepeatCheck = true;
    protected $orderRecordRepeatCheck = true;

    //导入电量数据—此处同时兼任导入温度数据
    public function importPowerRecord()
    {
        $directory = 'storage\app\power\power.csv';

        Excel::load($directory, function($reader) {
            // Loop through all sheets
            $this->channelRecordRepeatCheck = false;
            $reader->each(function($row) {
                $create_user = 1;
                if(empty($channels)) {//此处查询指定create_user的channel号等信息
                    $channels = DB::table('channel')->select('id', 'channel_number', 'receiver_id')->where('create_user', '=', $create_user)->get();
                }
                $this->powerRecordInsert($row,$channels);
                //暂时所有record都插入到create_user = 1的user名下，日后量化再修改此处.
            });
        });
    }

    public function importOrderRecord()
    {
        $directory = 'storage\app\order\order.xlsx';
        Excel::load($directory, function($reader) {
            //set the timestamp format
            $reader->setDateColumns(array('sendprint_date'));
            // Loop through all sheets
            $reader->each(function($sheet) {
                // Loop through all rows
                $sheet->each(function($row) {
                    $row = $row->toArray();
                    $this->orderInsert($row);
                });
            });
        });
    }
    //插入一条Order
    public function orderInsert($row)
    {
        //查询商品是否存在
        $merchandise = DB::table('merchandise')->where('name','=',$row['item_name'])->first();
        //如果商品查询失败，则商品不存在，检验创建新商品条目
        if(is_null($merchandise))
        {
            //查找merchandise_class中的种类的id。
            $merchandise_class = DB::table('merchandise_class')->select('id')->where('name','=',$row['cls_name'])->first();
            $merchandise_class_id = $merchandise_class->id;
            //添加此商品到merchandise表中并获取商品id
            $merchandise_id = DB::table('merchandise')->insertGetId(
                array('name'=>$row['item_name'],'merchandise_class'=>$merchandise_class_id)
            );
        }
        else
        {
            $merchandise_id = $merchandise->id;
            //检测订单是否已存在，防止重复插入
            //如果要求不查重,则直接跳过查重函数。
            if($this->orderRecordRepeatCheck && $this->orderExist($merchandise_id, $row['sendprint_date']->toDateTimeString()))
                return ;
        }
        //根据商品id插入order
        DB::table('orders')->insert(
            array('quantity'=>$row['sub_qty'],'price'=>$row['sale_price'],'merchandise_id'=>$merchandise_id,'date'=>$row['sendprint_date']->toDateTimeString())
        );
        //添加商品购买记录
    }
    //检测指定商品id和时间的订单是否存在——基于同一时间同一商品只能有一个订单
    public function orderExist($merchandise_id,$date)
    {
        $order = DB::table('orders')->where('merchandise_id','=',$merchandise_id)->where('date','=',$date)->first();
        return !is_null($order);
    }

    //插入一条Power_record记录
    public function powerRecordInsert($row,$channels)
    {
        $row = $row->toArray();
        //根据每个channel的信息来插入数据——如果channel没有定义，则无法插入数据，之后可以考虑如果channel不存在，则在此处直接新建channel
        foreach($channels as $channel)
        {
            //首先检测记录是否已存在，如果存在，则不再插入。
            //如果设置为不查重，则直接进入插入
            if(!$this->channelRecordRepeatCheck || !$this->powerRecordExist($row['date'],$channel->id, $row['ch'.$channel->channel_number])) {
                //每次插入完全取决于已经定义的channel的id
                DB::table('channel_record')->insert(
                    array('channel_id' => $channel->id, 'date' => $row['date'], 'value' => $row['ch' . $channel->channel_number])
                );
            }
        }
    }
    public function powerRecordExist($date,$channelID,$value)
    {
        $record = DB::table('channel_record')->where('date','=',$date)->where('channel_id','=',$channelID)->where('value','=',$value)->first();
        return !is_null($record);
    }
}
