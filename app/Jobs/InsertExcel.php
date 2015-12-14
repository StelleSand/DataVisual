<?php

namespace App\Jobs;

use App\ChannelRecord;
use App\Jobs\Job;
use App\User;
use Carbon\Carbon;
use Faker\Provider\cs_CZ\DateTime;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Merchandise;
use App\MerchandiseClass;
use App\Orders;
use App\Channel;
use App\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Excel;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
class InsertExcel extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $timeZone;
    protected $channelRecordRepeatCheck = true;
    protected $orderRecordRepeatCheck = true;
    protected $orderFileNames;
    protected $powerFileName;
    protected $excelRoot;
    protected $orderStoragePath;
    protected $powerStoragePath;
    protected $userId;
    protected $userChannels;
    protected $deleteSourceFile;
    protected $powerCurlSiteLogin;
    protected $powerCurlSiteDownload;
    protected $powerCurlUsername;
    protected $powerCurlPassword;
    protected $powerCurlHandler;
    protected $powerCurlTime;
    protected $cookieJar;
    //下面设置power相关的缓存和插值信息
    protected $cachePowerLastInsertRowName;
    protected $cachePowerLastInsertRow;
    protected $powerLastInsertRow;
    protected $powerAdditionalInsertBoundMinute;
    protected $powerAdditionalInsertInvalidMinute;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId = 1, $deleteSourceFile = true)
    {
        //测试userId 设为1
        $this->userId = $userId;
        //设置运行后删除源文件为true
        $this->deleteSourceFile = $deleteSourceFile;
        //设置excelRoot指向文件系统root，用于Excel加载
        $this->excelRoot = 'storage/app/';
        //设置storagePath,用于Storage加载
        $this->orderStoragePath = 'order/'.$this->userId.'/';
        $this->powerStoragePath = 'power/'.$this->userId.'/';
        //设置要处理的order相关文件
        $this->orderFileNames = Storage::allFiles($this->orderStoragePath);
        //设置抓取power数据相关信息
        $this->powerCurlSiteLogin = "http://en.mywatt.kr/account/proc.php";
        //注意这个链接末端必须有'?',用于添加get参数的附加字符串
        $this->powerCurlSiteDownload = 'http://en.mywatt.kr/sem3000/download.php?';
        $this->powerCurlUsername = '13801232605';
        $this->powerCurlPassword = 'hlp87725835';
        //初始化cookie保存文件
        $this->cookieJar = tempnam('/tmp','cookie');
        //设置curl的时间;包括时区
        $this->timeZone = 'Asia/Shanghai';
        $this->powerCurlTime = Carbon::now($this->timeZone);
        //设置user的所有channels数据
        $this->userChannels = User::find($this->userId)->channels()->get();
        //获取cachePowerLastInsertTime
        $this->cachePowerLastInsertRowName = $this->userId.'powerLastInsertRow';
        if(Cache::has($this->cachePowerLastInsertRowName))
            $this->cachePowerLastInsertRow = Cache::get($this->cachePowerLastInsertRowName);
        else
            $this->cachePowerLastInsertRow = array('date'=>'1980-1-1 05:20');
        //将缓存的最近插入行设置为最近插入行
        $this->powerLastInsertRow = $this->cachePowerLastInsertRow;
        //设置插值最大边界间隔时间,默认为10分钟
        $this->powerAdditionalInsertBoundMinute = 10;
        //设置使插值操作失效的分钟数，默认为3小时
        //即，若两次插值相差为3小时以上，则对中间缺失数据不进行插值处理
        $this->powerAdditionalInsertInvalidMinute = 3 * 60;
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
        $this->importPowerRecord();
    }

    //导入电量数据—此处同时兼任导入温度数据
    public function importPowerRecord()
    {
        $this->getPowerFile();
        //拼接获取powerFile的全名，用于Excel加载
        $powerFileFullName = $this->excelRoot.$this->powerFileName;
        Excel::load($powerFileFullName, function($reader) {
            // Loop through all sheets
            $reader->each(function($row) {
                //将每一行数据插入
                $this->powerRecordInsert($row);
            });
        });
        //完成文件扫描后，用最近插入时间更新缓存中最近插入行
        $this->cachePowerLastInsertRow = $this->powerLastInsertRow;
        $expiresAt = Carbon::now()->addDay();
        Cache::put($this->cachePowerLastInsertRowName, $this->cachePowerLastInsertRow, $expiresAt);
    }

    public function importOrderRecord()
    {
        //对获取到的每个orderFile名字进行处理
        foreach($this->orderFileNames as $orderFileName)
        {
            //如果其中有文件是gitignore文件，直接跳过
            if($orderFileName == $this->orderStoragePath.'.gitignore')
                continue;
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
            //查找merchandise_class中的种类的id,如果没有则创建一个。
            $merchandise_class = MerchandiseClass::firstOrCreate(array('name' => $row['cls_name'], 'create_user' => $this->userId));
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
    public function powerRecordInsert($row)
    {
        $row = $row->toArray();
        //获取当前此次插入时间戳和最近插入时间戳
        $rowDateTime = Carbon::createFromFormat('Y-m-d H:i',$row['date'], $this->timeZone);
        $lastInsertTime = Carbon::createFromFormat('Y-m-d H:i',$this->powerLastInsertRow['date'], $this->timeZone);
        //与最近插入时间比较,如果时间小于最近缓存的插入时间,则直接返回
        //获取两次row相差分钟数
        //注意diffInMinutes函数会用rowDateTime时间减去最近时间得到返回值
        $minuteDiffer = $lastInsertTime->diffInMinutes($rowDateTime, false);
        //如果为新文件第一次插入，则lastInsertTime为cacheLastInsertTime,
        //如果时间差为非正数，直接返回，无插入操作
        if($minuteDiffer <= 0) return;
        //初始化时间点变量,从1分钟开始
        //但是如果现在时间与上次插入时间相差太大（大于插值无效时间），则中间不执行插入操作，
        if($minuteDiffer <= $this->powerAdditionalInsertInvalidMinute)
            $timePointer = $lastInsertTime->addMinute();
        else $timePointer = $lastInsertTime->addMinutes($minuteDiffer);
        //初始化zeroValue,用来判断中间插入值是否应为零值
        $zeroValue = $minuteDiffer > $this->powerAdditionalInsertBoundMinute;
        //设置每次累加的power数据单元;
        $valueUnits = array();
        foreach($this->userChannels as $channel)
        {
            $channelOffset = 'ch'.$channel->channel_number;
            //计算得出每次累加的power数据单元
            if(!isset($this->powerLastInsertRow[$channelOffset]))
                $this->powerLastInsertRow[$channelOffset] = 0;
            $valueUnits[$channelOffset] = round(($row[$channelOffset] - $this->powerLastInsertRow[$channelOffset]) / $minuteDiffer, 1);
        }
        while($timePointer->diffInMinutes($rowDateTime,false) >= 0)
        {
            //获取这次插值距离lastInsertRow的分钟数
            $minutesAdded = $timePointer->diffInMinutes($rowDateTime,false);
            foreach($this->userChannels as $channel)
            {
                $channelRecordArray = array();
                //设置record的channel_id
                $channelRecordArray['channel_id'] = $channel->id;
                //设置插值的时间点
                $channelRecordArray['date'] = $timePointer;
                $channelOffset = 'ch'.$channel->channel_number;
                //如果zeroValue为真且这次插值不是真实值，而是额外值,则直接赋值为0，否则按照比例计算插入值
                if($zeroValue && $minutesAdded < $minuteDiffer)
                    $channelRecordArray['value'] = 0;
                else
                    $channelRecordArray['value'] =  $minutesAdded * $valueUnits[$channelOffset] + $this->powerLastInsertRow[$channelOffset];
                //根据Model插入值
                ChannelRecord::firstOrCreate($channelRecordArray);
            }
            //每次插入完成后时间点增加一分钟
            $timePointer->addMinute();
        }
        //每插入完一组数据且完成插值后,则更新上次插入行
        $this->powerLastInsertRow = $row;
    }

    /*
     * 用账户密码初始化cookie并抓取power文件放置到指定位置
     * */
    public function getPowerFile()
    {
        $this->powerCurlInit();
        $this->powerCurlGrab();
    }

    /*
     * 本类中抓取power数据的curl句柄初始化工作函数
     * 主要用于初始化cookie
     * */
    public function powerCurlInit()
    {
        //初始化power的curl设置
        $this->powerCurlHandler = curl_init();
        //设置提交登录网站使用的用户信息数组
        $postUserInfo = array(
            "mode" => 'login',
            "a_id" => $this->powerCurlUsername,
            "a_pw" => $this->powerCurlPassword
        );
        //设置curl参数数组
        $curlOptions = array(CURLOPT_URL => $this->powerCurlSiteLogin,
            CURLOPT_HEADER => true,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $postUserInfo,
            CURLOPT_COOKIEFILE => $this->cookieJar,
            CURLOPT_COOKIEJAR => $this->cookieJar
        );
        //应用curl参数数组
        curl_setopt_array($this->powerCurlHandler, $curlOptions);
        curl_exec($this->powerCurlHandler);
        curl_close($this->powerCurlHandler);
    }

    /*
     * 抓取power数据的csv文件并放置
     * */
    public function powerCurlGrab()
    {
        //初始化power的curl设置
        $this->powerCurlHandler = curl_init();
        //时间信息由本类构造函数中的时间确定,其余信息为默认值(测试值)
        $postFileInfo = array(
            "t_no" => '242',
            "use_period" => 'D',
            "ch_view" => '0',
            "use_year" => $this->powerCurlTime->year,
            "use_month" => $this->powerCurlTime->month,
            "use_day" => $this->powerCurlTime->day
        );
        //设置curl参数数组,注意设置url时需要叠加get参数字符串
        $curlOptions = array(CURLOPT_URL => $this->powerCurlSiteDownload.$this->arrayToGetParams($postFileInfo),
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieJar
        );
        //应用curl参数数组
        curl_setopt_array($this->powerCurlHandler, $curlOptions);
        $result = curl_exec($this->powerCurlHandler);
        //拆分result获取header和body
        list($headerString, $bodyString) = explode("\r\n\r\n", $result, 2);
        //拆分headerString，获取每一个header项
        $header_array = explode("\n", $headerString);
        //初始化headers数组
        $headers = array();
        foreach($header_array as $header_value) {
            //用:分隔开键值对
            $header_pieces = explode(':', $header_value);
            if(count($header_pieces) == 2) {
                //设置headers数组
                $headers[$header_pieces[0]] = trim($header_pieces[1]);
            }
        }
        //对于Content-Disposition，用filename分隔开获取filename
        $contentDisposition = explode('filename=',$headers['Content-Disposition']);
        //filename为分割后的第二个元素
        $file_name = $contentDisposition[1];
        //文件内容则直接为bodyString
        $file_content = $bodyString;
        //设置powerFileName
        $this->powerFileName = $this->powerStoragePath.$file_name;
        //将获取结果写入本地文件
        Storage::put($this->powerFileName, $file_content);
        curl_close($this->powerCurlHandler);
    }

    /*
     *将数组转换为curl可用的get参数字符串
     * 注意！此函数不会在前添加'?'，必须在url末端添加'?'
     * */
    public function arrayToGetParams($params)
    {
        //初始化getString为空字符串
        $getString = '';
        foreach($params as $key => $value)
        {
            //对每一个值，叠加到getString后面
            $getString .= $key.'='.$value.'&';
        }
        return $getString;
    }
}
