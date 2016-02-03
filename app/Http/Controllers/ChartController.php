<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/20
 * Time: 20:29
 */

namespace App\Http\Controllers;

use App\Chart;
use App\ChartLineMatch;
use App\User;
use App\Http\Controllers;
use App\Merchandise;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\MerchandiseClass;
use App\Channel;
use Request;
class ChartController extends Controller{
    protected $start_time;//起始时间
    protected $end_time;//结束时间
    protected $timeLength = null;//跨度时长
    protected $range = null;//跨度时间单位
    protected $split_number;//图表取点个数
    protected $timePoints = array();//计算得出的时间点数组
    protected $timePointsString = array();
    protected $split_space;//计算得出的每两个时间点间隔
    protected $user_id;//预设使用制表的用户
    protected $user;//预设用户Model
    protected $roundNumber;//本类计算结果保留位数
    protected $timeZone = 'Asia/Shanghai';


    //设置sale_amount和sale_volume数组，用以减少重复计算
    protected $merchandise_class_sale_amount = array();
    protected $merchandise_class_sale_volume = array();


    //设置channel_average_power数组，用以减少重复计算
    protected $channel_average_power = array();

    //返回给前端的数据
    protected $data = array();

    //设置id常数
    protected $const_allPowerID = 1;
    protected $const_mianluID = 2;
    protected $const_sangeluziID = 3;
    protected $const_kaishuiluID = 4;
    protected $const_paiqishanID = 5;
    protected $const_sangebingxiangID = 6;
    protected $const_zhanshiguiID = 7;
    protected $const_zhengbaoluID = 8;
    protected $const_baowentaiID = 9;
    protected $const_kelechazuoID = 10;

    protected $const_class_mianleiID = 1;
    protected $const_class_chengzhongID = 2;
    protected $const_class_malatangID = 3;
    protected $const_class_yinliaoID = 4;
    protected $const_class_xiaochiID = 5;
    protected $const_class_zhengdianID = 6;

    public function __construct()
    {
        //初始化制表用户
        $this->user_id = 1;
        $this->user = User::find($this->user_id);
        //设置所有计算保留为小数点后两位
        $this->roundNumber = 2;
        $this->data['user'] = $this->user;
    }

    public function getChart()
    {
        $charts = $this->user->charts()->get();
        $this->data['charts'] = $charts;
        return view('chartAdd',$this->data);
    }

    /*
     * 添加一个chart
     * */
    public function postAddChart()
    {
        $inputs = Request::all();
        $chartInfo = array('name' => $inputs['name'], 'create_user' => $this->user->id,'detail'=>$inputs['detail']);
        Chart::create($chartInfo);
        return view('chartAdd');
    }

    /*
     * 删除指定chart
     * */
    public function getDeleteChart()
    {
        $chart = Chart::find(Request::input('id'));
        if($chart->create_user == $this->user->id)
            $chart->delete();
        return view('chartAdd');
    }


    /*
     * 为指定chart添加一条Line
     * */
    public function postChartAddLine()
    {
        $line = $this->user->lines()->find(Request::input('line_id'));
        $chart = $this->user->charts()->find(Request::input('chart_id'));
        if(is_null($line) || is_null($chart))
        {
            dump($line);
            dump(chart);
        }
        $chartLineInfo = array('line_id' => $line->id, 'chart_id' => $chart->id);
        ChartLineMatch::create($chartLineInfo);
        return view('chartAddLine');
    }

    /*
     * 为指定chart删除一条Line
     * */
    public function postChartDeleteLine()
    {
        $line = $this->user->lines()->find(Request::input('line_id'));
        $chart = $this->user->charts()->find(Request::input('chart_id'));
        if(is_null($line) || is_null($chart))
        {
            dump($line);
            dump($chart);
        }
        $chartLine = ChartLineMatch::where('chart_id',$chart->id)->where('line_id',$line->id);
        if(!is_null($chartLine))
            $chartLine->delete();
        return view('chartDeleteLine');
    }

    protected function init()
    {
        $this->range = Request::has('range') && !empty(Request::input('range'))? Request::input('range') : null;
        //初始化开始时间和结束时间
        $this->timeLength = Request::has('timelength') && !empty(Request::input('timelength'))? Request::input('timelength') : null;
        $datetime = Request::has('datetime') && !empty(Request::input('datetime'))? Request::input('datetime') : null;
        if(!is_null($datetime)) {
            $this->end_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->timestamp;
            switch($this->range){
                case null:
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addDays( - 1 )->timestamp;
                    break;
                case 'minute':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addMinutes( 0 - $this->timeLength)->timestamp;
                    break;
                case 'hour':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addHours( 0 - $this->timeLength)->timestamp;
                    break;
                case 'day':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addDays( 0 - $this->timeLength)->timestamp;
                    break;
                case 'week':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addWeeks( 0 - $this->timeLength)->timestamp;
                    break;
                case 'month':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addMonths( 0 - $this->timeLength)->timestamp;
                    break;
                case 'year':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addYears( 0 - $this->timeLength)->timestamp;
                    break;
            }
            //$this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addMinutes( -60 * $this->hours)->timestamp;
        }
        else {
            // 如果datetime没有设置，计算其他数据
            $this->end_time = Carbon::now($this->timeZone);
            $this->end_time->second = 0;
            // 结束时间设置为当前时间往前15分钟，且必须为5的倍数
            $this->end_time->minute = floor($this->end_time->minute / 5) * 5;
            $this->end_time->addMinutes( -15 );
            $datetime = $this->timeStampToString($this->end_time->timestamp);
            switch($this->range){
                case null:
                    // 对于凌晨的时候特殊处理，否则会出现错误
                    if(!($this->end_time->hour == 0 && $this->end_time->minute < 30))
                        $this->start_time = Carbon::today($this->timeZone)->timestamp;
                    else
                        $this->start_time = Carbon::today($this->timeZone)->addHours( - 2 )->timestamp;
                    $this->timeLength = floor(($this->end_time->timestamp - $this->start_time) / 3600);
                    $this->range = 'hour';
                    break;
                case 'minute':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addMinutes( 0 - $this->timeLength)->timestamp;
                    break;
                case 'hour':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addHours( 0 - $this->timeLength)->timestamp;
                    break;
                case 'day':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addDays( 0 - $this->timeLength)->timestamp;
                    break;
                case 'week':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addWeeks( 0 - $this->timeLength)->timestamp;
                    break;
                case 'month':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addMonths( 0 - $this->timeLength)->timestamp;
                    break;
                case 'year':
                    $this->start_time = Carbon::createFromFormat('Y-m-d H:i', $datetime, $this->timeZone)->addYears( 0 - $this->timeLength)->timestamp;
                    break;
            }
            $this->end_time = $this->end_time->timestamp;
        }
        // 如果设置了split则采用，否则统一25个点
        if(Request::has('split') && !empty(Request::input('split')) )
            $this->split_number = Request::input('split');
        else
            $this->split_number = 25;
            //$this->split_number = ($this->end_time - $this->start_time) / 300 + 1;
        //设置相关全局返回数据
        $this->data['datetime'] = $this->timeStampToString($this->end_time);
        $this->data['split'] = $this->split_number;
        $this->data['range'] = $this->range;
        $this->data['timeLength'] = $this->timeLength;

        $result = $this->end_time - $this->start_time;
        //从这里可以知道split至少为2
        $this->split_space = $result / ($this->split_number-1);
        $this->data['space'] = $this->split_space;
        $this->data['nextDatetime'] = $this->timeStampToString($this->end_time + $this->split_space);
        //构建等间隔时间点数组
        //第一个点为开始点往前推算split_space时间间隔的点，用于计算第一个时间点的值,offset 为 -1
        //实际上timePoints数组有split_number + 1个点但是由于第一个点下标为-1,所以前端并不会使用，而会忽略这个下标为-1的点
        //此处需要严重注意：尽管这个类中timePoints数组有split_number + 1个点，
        //但是返回给前端的points数组下标从0开始，且只有split_number个点！！！
        $time_point = $this->start_time - $this->split_space;
        //array_push($this->timePoints,$time_point);
        $this->timePoints[-1] = $time_point;
        for($i = 0; $i < $this->split_number; $i++)
        {
            $time_point += $this->split_space;
            //将中间点压入数组
            array_push($this->timePoints, $time_point);
            array_push($this->timePointsString, $this->timeStampToString($time_point));
        }
    }

    protected function timeStampToString($time)
    {
        //return date('Y-m-d H:i:s',$time);
        return Carbon::createFromTimestamp($time, $this->timeZone)->format('Y-m-d H:i');
    }

    /*
     * 本类保留小数点后位数函数
     * 默认保留两位小数，可根据需求调整返回前端的实数保留位数
     * */
    protected function valueRound($value)
    {
        return round($value ,$this->roundNumber);
    }

    /*
     *获取channel表的时序均值数组:即求瞬时值，而非累计值
     * 参数为(channel_id)
     * */
    protected function getPowerAverageValue($channel_id)
    {
        //如果channel的瞬时值数组已经被设置，直接返回
        if(isset($this->channel_average_power[$channel_id]))
            return $this->channel_average_power[$channel_id];
        $points = array();
        //获取Channel的Model
        $channel = Channel::find($channel_id);
        for($i = 0;$i < $this->split_number; $i++) {
            //计算每个时间点对应channelAverageValue
            $points[$i] = $this->valueRound($channel->getAveragePower($this->timeStampToString($this->timePoints[$i - 1]), $this->timeStampToString($this->timePoints[$i])));
        }
        $this->channel_average_power[$channel_id] = $points;
        return $points;
    }

    /*
     * 获取指定种类商品销售额数组
     * 参数为(商品种类id)
     * */
    protected function getMerchandiseClassSalesAmount($merchandise_class_id)
    {
        //如果商品种类的销售额数组已经被设置，直接返回
        if(isset($this->merchandise_class_sale_amount[$merchandise_class_id]))
            return $this->merchandise_class_sale_amount[$merchandise_class_id];
        $points = array();
        //获取商品种类Model
        $merchandise_class = MerchandiseClass::find($merchandise_class_id);
        for($i = 0;$i < $this->split_number; $i++) {
            //计算每个时间点对应销售额数据
            $points[$i] = $merchandise_class->getCumulativeSaleAmount($this->timeStampToString($this->timePoints[$i - 1]), $this->timeStampToString($this->timePoints[$i]));
        }
        $this->merchandise_class_sale_amount[$merchandise_class_id] = $points;
        return $points;
    }

    /*
     * 获取指定种类商品销量数组
     * 参数为(商品种类id)
     * */
    protected function getMerchandiseClassSalesVolume($merchandise_class_id)
    {
        //如果商品种类的销售量数组已经被设置，直接返回
        if(isset($this->merchandise_class_sale_volume[$merchandise_class_id]))
            return $this->merchandise_class_sale_volume[$merchandise_class_id];
        $points = array();
        //获取商品种类Model
        $merchandise_class = MerchandiseClass::find($merchandise_class_id);
        for($i = 0;$i < $this->split_number; $i++) {
            //计算每个时间点对应销量数据
            $points[$i] = $merchandise_class->getCumulativeSaleVolume($this->timeStampToString($this->timePoints[$i - 1]), $this->timeStampToString($this->timePoints[$i]));
        }
        $this->merchandise_class_sale_volume[$merchandise_class_id] = $points;
        return $points;
    }

    /*
     * 获取所有种类商品的销售额数组,即商店总销售额
     * */
    protected function getAllMerchandiseClassSaleAmount()
    {
        $points = array();
        for($i = 0;$i < $this->split_number; $i++)
            $points[$i] = 0;
        $merchandise_classes = $this->user->merchandiseClasses()->get();
        foreach($merchandise_classes as $merchandise_class)
        {
            $merchandise_points = $this->getMerchandiseClassSalesAmount($merchandise_class->id);
            for($i = 0;$i < $this->split_number; $i++)
                $points[$i] += $merchandise_points[$i];
        }
        return $points;
    }
    /*
     * 获取所有种类商品的销量数组,即商店总销量
     * */
    protected function getAllMerchandiseClassSaleVolume()
    {
        $points = array();
        for($i = 0;$i < $this->split_number; $i++)
            $points[$i] = 0;
        $merchandise_classes = $this->user->merchandiseClasses()->get();
        foreach($merchandise_classes as $merchandise_class)
        {
            $merchandise_points = $this->getMerchandiseClassSalesVolume($merchandise_class->id);
            for($i = 0;$i < $this->split_number; $i++)
                $points[$i] += $merchandise_points[$i];
        }
        return $points;
    }

    protected function arrayAdd($array_x, $array_y)
    {
        $result = array();
        for ($i = 0; $i < $this->split_number; $i++)
        {
            $result[$i] = $array_x[$i] + $array_y[$i];
        }
        return $result;
    }

    protected function arrayMinus($array_x, $array_y)
    {
        $result = array();
        for ($i = 0; $i < $this->split_number; $i++)
        {
            $result[$i] = $array_x[$i] - $array_y[$i];
        }
        return $result;
    }

    // 返回数组都乘以一个常数后的结果
    protected function arrayMulNumber($array, $number)
    {
        if($number == 1) return $array;
        $result = array();
        for ($i = 0; $i < $this->split_number; $i++)
        {
            $result[$i] = $array[$i] * $number;
        }
        return $result;
    }

    public function ajaxTestDiagram()
    {
        $this->init();
        $charts = array();
        array_push($charts,$this->makeChart1());
        array_push($charts,$this->makeChart2());
        array_push($charts,$this->makeChart3());
        array_push($charts,$this->makeChart4());
        array_push($charts,$this->makeChart5());
        return json_encode(['charts'=>$charts,'data'=>$this->data]);
    }

    public function testDiagram()
    {
        $this->init();
        $charts = array();
        array_push($charts,$this->makeChart1());
        array_push($charts,$this->makeChart2());
        array_push($charts,$this->makeChart3());
        array_push($charts,$this->makeChart4());
        array_push($charts,$this->makeChart5());
        return view('charts',['charts'=>$charts,'data'=>$this->data]);
    }

    /*
     * chart制作函数，用以返回chart解析后的数据数组
     * 参数为chart对象
     * */
    protected function makeChart(Chart $chart)
    {
        $lines = $chart->lines()->get();
    }

    protected function makeChart1()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        //$chartNames = array('总耗电(100W)','总销售额(元)','生产耗电(100W)','展示柜冰箱(100W)','存储耗电(100W)','开水炉(100W)');
        $chartNames = array('总耗电(W)','总销售额(元)','生产耗电(W)','展示柜冰箱耗电(W)','存储耗电(W)','开水炉耗电(W)');

        //$allPowerPoints = $this->arrayMulNumber($this->getPowerAverageValue($this->const_allPowerID), 0.01);
        $allPowerPoints = $this->getPowerAverageValue($this->const_allPowerID);

        $allSaleAmountPoints = $this->getAllMerchandiseClassSaleAmount();
        $productPower = $this->arrayAdd($this->getPowerAverageValue($this->const_sangeluziID), $this->getPowerAverageValue($this->const_zhengbaoluID));
        $productPower = $this->arrayAdd($productPower, $this->getPowerAverageValue($this->const_baowentaiID));
        $productPower = $this->arrayAdd($productPower, $this->getPowerAverageValue($this->const_paiqishanID));

        //$productPower = $this->arrayMulNumber($productPower, 0.01);
        $productPower = $productPower;

        //$zhanshiguiPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_zhanshiguiID), 0.01);
        $zhanshiguiPower = $this->getPowerAverageValue($this->const_zhanshiguiID);

        //$cunchuPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_sangebingxiangID), 0.01);
        $cunchuPower = $this->getPowerAverageValue($this->const_sangebingxiangID);

        //$kaishuiluPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_kaishuiluID), 0.01);
        $kaishuiluPower = $this->getPowerAverageValue($this->const_kaishuiluID);

        $chartPoints['chartName'] = 'Chart_1';
        $chartPoints['names'] = $chartNames;
        $chartPoints['xpoints'] = $this->timePointsString;
        $chartPoints['ypoints'] = array();
        $chartPoints['ypoints'][0] = $allPowerPoints;
        $chartPoints['ypoints'][1] = $allSaleAmountPoints;
        $chartPoints['ypoints'][2] = $productPower;
        $chartPoints['ypoints'][3] = $zhanshiguiPower;
        $chartPoints['ypoints'][4] = $cunchuPower;
        $chartPoints['ypoints'][5] = $kaishuiluPower;
        $chartPoints['types'] = ['power','amount','power','power','power','power'];

        return $chartPoints;
    }

    protected function makeChart2()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        //$chartNames = array('面炉耗电(1000W)','保温台耗电(1000W)','面类销售量(碗)','面类销售额(10元)');
        $chartNames = array('面炉耗电(W)','保温台耗电(W)','面类销售量(碗)','面类销售额(元)');

        /* old version
        $mianluPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_mianluID), 0.001);
        $baowentaiPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_baowentaiID),0.001);
        $mianleiSaleVolume = $this->getMerchandiseClassSalesVolume($this->const_class_mianleiID);
        $mianleiSaleAmount = $this->arrayMulNumber($this->getMerchandiseClassSalesAmount($this->const_class_mianleiID), 0.1);
        */
        $mianluPower = $this->getPowerAverageValue($this->const_mianluID);
        $baowentaiPower = $this->getPowerAverageValue($this->const_baowentaiID);
        $mianleiSaleVolume = $this->getMerchandiseClassSalesVolume($this->const_class_mianleiID);
        $mianleiSaleAmount = $this->getMerchandiseClassSalesAmount($this->const_class_mianleiID);

        $chartPoints['chartName'] = 'Chart_2';
        $chartPoints['names'] = $chartNames;
        $chartPoints['xpoints'] = $this->timePointsString;
        $chartPoints['ypoints'] = array();
        $chartPoints['ypoints'][0] = $mianluPower;
        $chartPoints['ypoints'][1] = $baowentaiPower;
        $chartPoints['ypoints'][2] = $mianleiSaleVolume;
        $chartPoints['ypoints'][3] = $mianleiSaleAmount;
        $chartPoints['types'] = ['power','power','volume','amount'];
        return $chartPoints;
    }

    protected function makeChart3()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        //$chartNames = array('麻辣烫耗电(100W)','开水炉耗电(100W)','麻辣烫销售金额(元)');
        $chartNames = array('麻辣烫耗电(W)','开水炉耗电(W)','麻辣烫销售金额(元)');
        /*
         * old version
        $malatangPower = $this->arrayMulNumber($this->arrayMinus($this->getPowerAverageValue($this->const_sangeluziID), $this->getPowerAverageValue($this->const_mianluID)), 0.01);
        $kaishuiluPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_kaishuiluID), 0.01);
        $malatangAmount = $this->arrayAdd($this->getMerchandiseClassSalesAmount($this->const_class_malatangID),$this->getMerchandiseClassSalesAmount($this->const_class_chengzhongID)) ;
        */
        $malatangPower =$this->arrayMinus($this->getPowerAverageValue($this->const_sangeluziID), $this->getPowerAverageValue($this->const_mianluID));
        $kaishuiluPower = $this->getPowerAverageValue($this->const_kaishuiluID);
        $malatangAmount = $this->arrayAdd($this->getMerchandiseClassSalesAmount($this->const_class_malatangID),$this->getMerchandiseClassSalesAmount($this->const_class_chengzhongID)) ;

        $chartPoints['chartName'] = 'Chart_3';
        $chartPoints['names'] = $chartNames;
        $chartPoints['xpoints'] = $this->timePointsString;
        $chartPoints['ypoints'] = array();
        $chartPoints['ypoints'][0] = $malatangPower;
        $chartPoints['ypoints'][1] = $kaishuiluPower;
        $chartPoints['ypoints'][2] = $malatangAmount;
        $chartPoints['types'] = ['power','power','amount'];
        return $chartPoints;
    }

    protected function makeChart4()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        //$chartNames = array('蒸包炉耗电(1000W)','蒸点销售量(份)');
        $chartNames = array('蒸包炉耗电(W)','蒸点销售量(份)');
        /*
        $zhengbaoluPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_zhengbaoluID), 0.001);
        $zhengdianSaleAmount = $this->getMerchandiseClassSalesAmount($this->const_class_zhengdianID);
        */
        $zhengbaoluPower = $this->getPowerAverageValue($this->const_zhengbaoluID);
        $zhengdianSaleVolume = $this->getMerchandiseClassSalesVolume($this->const_class_zhengdianID);

        $chartPoints['chartName'] = 'Chart_4';
        $chartPoints['names'] = $chartNames;
        $chartPoints['xpoints'] = $this->timePointsString;
        $chartPoints['ypoints'] = array();
        $chartPoints['ypoints'][0] = $zhengbaoluPower;
        $chartPoints['ypoints'][1] = $zhengdianSaleVolume;
        $chartPoints['types'] = ['power','volume'];
        return $chartPoints;
    }

    protected function makeChart5()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        $chartNames = array('总销售额(元)','麻辣烫销售额(元)','面类销售额(元)','蒸点销售额(元)','小吃销售额(元)','饮料销售额(元)');
        $allSaleAmount = $this->getAllMerchandiseClassSaleAmount();
        $malatangSaleAmount = $this->arrayAdd($this->getMerchandiseClassSalesAmount($this->const_class_malatangID),$this->getMerchandiseClassSalesAmount($this->const_class_chengzhongID)) ;
        $mianleiSaleAmount = $this->getMerchandiseClassSalesAmount($this->const_class_mianleiID);
        $zhengdianSaleAmount = $this->getMerchandiseClassSalesAmount($this->const_class_zhengdianID);
        $xiaochiSaleAmount = $this->getMerchandiseClassSalesAmount($this->const_class_xiaochiID);
        $yinliaoSaleAmount = $this->getMerchandiseClassSalesAmount($this->const_class_yinliaoID);
        $chartPoints['chartName'] = 'Chart_5';
        $chartPoints['names'] = $chartNames;
        $chartPoints['xpoints'] = $this->timePointsString;
        $chartPoints['ypoints'] = array();
        $chartPoints['ypoints'][0] = $allSaleAmount;
        $chartPoints['ypoints'][1] = $malatangSaleAmount;
        $chartPoints['ypoints'][2] = $mianleiSaleAmount;
        $chartPoints['ypoints'][3] = $zhengdianSaleAmount;
        $chartPoints['ypoints'][4] = $xiaochiSaleAmount;
        $chartPoints['ypoints'][5] = $yinliaoSaleAmount;
        $chartPoints['types'] = ['amount','amount','amount','amount','amount','amount'];
        return $chartPoints;
    }

}