<?php

namespace App\Jobs;

use App\Jobs\Job;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Merchandise;
use App\MerchandiseClass;
use App\Orders;
use App\Channel;
use App\Order;
use Illuminate\Support\Facades\DB;
use Excel;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
class InsertExcel extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $channelRecordRepeatCheck = true;
    protected $orderRecordRepeatCheck = true;
    protected $orderFileNames;
    protected $excelRoot;
    protected $orderStoragePath;
    protected $userId;
    protected $deleteSourceFile;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //测试userId 设为1
        $this->userId = 1;
        //设置运行后删除源文件为true
        $this->deleteSourceFile = true;
        //设置excelRoot指向文件系统root，用于Excel加载
        $this->excelRoot = 'storage/app/';
        //设置storagePath,用于Storage加载
        $this->orderStoragePath = 'order/'.$this->userId;
        //获取orderDisk;
        $this->orderFileNames = Storage::allFiles($this->orderStoragePath);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $this->importOrderRecord();
        //$this->importPowerRecord();
    }

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
        //对获取到的每个orderFile名字进行处理
        foreach($this->orderFileNames as $orderFileName)
        {
            //拼接获取orderFile的全名，用于Excel加载
            $orderFileFullName = $this->excelRoot.$orderFileName;
            Excel::load($orderFileFullName, function($reader) {
                //set the timestamp format
                $reader->setDateColumns(array('sendprint_date'));
                $reader->setDateColumns(array('create_date'));
                // Loop through all sheets
                $reader->each(function($sheet) {
                    // Loop through all rows
                    $sheet->each(function($row) {
                        $row = $row->toArray();
                        $this->orderInsert($row);
                    });
                });
            });
            //如果设置为不保留源文件，运行完成后删除源文件
            if($this->deleteSourceFile)
                Storage::delete($orderFileName);
        }
    }
    //插入一条Order
    public function orderInsert($row)
    {
        //查询商品是否存在
        //$merchandise = DB::table('merchandise')->where('name','=',$row['item_name'])->first();
        $merchandise = Merchandise::where('name','=',$row['item_name'])->first();
        //如果商品查询失败，则商品不存在，检验创建新商品条目
        if(is_null($merchandise))
        {
            //查找merchandise_class中的种类的id。
            $merchandise_class = MerchandiseClass::where('name','=',$row['cls_name'])->first();
            $merchandise_class_id = $merchandise_class->id;
            //添加此商品到merchandise表中并获取商品id
            $merchandise = Merchandise::create(
                array('name'=>$row['item_name'],'merchandise_class'=>$merchandise_class_id)
            );
            $merchandise_id = $merchandise->id;
        }
        else
        {
            $merchandise_id = $merchandise->id;
            //检测订单是否已存在，防止重复插入
            //如果要求不查重,则直接跳过查重函数。
            if($this->orderRecordRepeatCheck && $this->orderExist($merchandise_id, $row['account_no'], $row['sendprint_date']->toDateTimeString()))
                return ;
        }
        //根据商品id插入order
        Orders::create(
            array('order_no' => $row['account_no'],'quantity'=>$row['sub_qty'],'price'=>$row['sale_price'],'merchandise_id'=>$merchandise_id,'create_date'=>$row['create_date']->toDateTimeString(),'print_date'=>$row['sendprint_date']->toDateTimeString())
        );
        //添加商品购买记录
    }
    //检测指定商品id和时间的订单是否存在——基于同一时间同一商品同一订单号只能存在一个
    public function orderExist($merchandise_id,$order_no,$print_date)
    {
        $order = Orders::where('merchandise_id','=',$merchandise_id)->where('order_no','=',$order_no)->where('print_date','=',$print_date)->first();
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
