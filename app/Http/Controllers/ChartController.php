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
    protected $hours;//跨度时长
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
            dump(chart);
        }
        $chartLine = ChartLineMatch::where('chart_id',$chart->id)->where('line_id',$line->id);
        if(!is_null($chartLine))
            $chartLine->delete();
        return view('chartDeleteLine');
    }

    protected function init()
    {
        //初始化开始时间和结束时间
        if(Request::has('hours') && !empty(Request::input('hours')))
            $this->hours = Request::input('hours');
        else
            $this->hours = 24;
        if(Request::has('datetime') && !empty(Request::input('datetime'))) {
            $this->end_time = Carbon::createFromFormat('Y-m-d H:i', Request::input('datetime'), $this->timeZone)->timestamp;
            $this->start_time = Carbon::createFromFormat('Y-m-d H:i', Request::input('datetime'), $this->timeZone)->addMinutes( -60 * $this->hours)->timestamp;
        }
        else {
            $this->end_time = Carbon::now($this->timeZone);
            $this->end_time->second = 0;
            $this->end_time->minute = floor($this->end_time->minute / 5) * 5;
            $this->end_time->addMinutes( -15 );
            //$this->start_time = $this->end_time;
            $this->end_time = $this->end_time->timestamp;
            ///$this->start_time->addMinutes( -60 * $this->hours );
            //$this->start_time = $this->start_time->timestamp;
            $this->start_time = Carbon::today($this->timeZone)->timestamp;
            // 如果datetime没有设置，hours设置与否都失效，计算hours
            $this->hours = floor(($this->end_time - $this->start_time) / 3600);
        }
        // 如果设置了split则采用，否则直接按照5分钟一个点来设置
        if(Request::has('split') && !empty(Request::input('split')) )
            $this->split_number = Request::input('split');
        else
            //$this->split_number = 25;
            $this->split_number = ($this->end_time - $this->start_time) / 300 + 1;
        $result = $this->end_time - $this->start_time;
        $this->split_space = $result / ($this->split_number-1);
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
        return json_encode(['charts'=>$charts, 'datetime' => $this->timeStampToString($this->end_time), 'hours' => $this->hours, 'split' => $this->split_number]);
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
        return view('charts',['charts'=>$charts, 'datetime' => $this->timeStampToString($this->end_time), 'hours' => $this->hours, 'split' => $this->split_number]);
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
        $chartNames = array('总耗电(100W)','总销售额(元)','生产耗电(100W)','展示柜冰箱(100W)','存储耗电(100W)','开水炉(100W)');
        $allPowerPoints = $this->arrayMulNumber($this->getPowerAverageValue($this->const_allPowerID), 0.01);
        $allSaleAmountPoints = $this->getAllMerchandiseClassSaleAmount();
        $productPower = $this->arrayAdd($this->getPowerAverageValue($this->const_sangeluziID), $this->getPowerAverageValue($this->const_zhengbaoluID));
        $productPower = $this->arrayAdd($productPower, $this->getPowerAverageValue($this->const_baowentaiID));
        $productPower = $this->arrayAdd($productPower, $this->getPowerAverageValue($this->const_paiqishanID));
        $productPower = $this->arrayMulNumber($productPower, 0.01);

        $zhanshiguiPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_zhanshiguiID), 0.01);
        $cunchuPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_sangebingxiangID), 0.01);
        $kaishuiluPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_kaishuiluID), 0.01);

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

        return $chartPoints;
    }

    protected function makeChart2()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        $chartNames = array('面炉耗电(1000W)','保温台耗电(1000W)','面类销售量(碗)','面类销售额(10元)');
        $mianluPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_mianluID), 0.001);
        $baowentaiPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_baowentaiID),0.001);
        $mianleiSaleVolume = $this->getMerchandiseClassSalesVolume($this->const_class_mianleiID);
        $mianleiSaleAmount = $this->arrayMulNumber($this->getMerchandiseClassSalesAmount($this->const_class_mianleiID), 0.1);
        $chartPoints['chartName'] = 'Chart_2';
        $chartPoints['names'] = $chartNames;
        $chartPoints['xpoints'] = $this->timePointsString;
        $chartPoints['ypoints'] = array();
        $chartPoints['ypoints'][0] = $mianluPower;
        $chartPoints['ypoints'][1] = $baowentaiPower;
        $chartPoints['ypoints'][2] = $mianleiSaleVolume;
        $chartPoints['ypoints'][3] = $mianleiSaleAmount;
        return $chartPoints;
    }

    protected function makeChart3()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        $chartNames = array('麻辣烫耗电(100W)','开水炉耗电(100W)','麻辣烫销售金额(元)');
        $malatangPower = $this->arrayMulNumber($this->arrayMinus($this->getPowerAverageValue($this->const_sangeluziID), $this->getPowerAverageValue($this->const_mianluID)), 0.01);
        $kaishuiluPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_kaishuiluID), 0.01);
        $malatangAmount = $this->getMerchandiseClassSalesAmount($this->const_class_malatangID);
        $chartPoints['chartName'] = 'Chart_3';
        $chartPoints['names'] = $chartNames;
        $chartPoints['xpoints'] = $this->timePointsString;
        $chartPoints['ypoints'] = array();
        $chartPoints['ypoints'][0] = $malatangPower;
        $chartPoints['ypoints'][1] = $kaishuiluPower;
        $chartPoints['ypoints'][2] = $malatangAmount;
        return $chartPoints;
    }

    protected function makeChart4()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        $chartNames = array('蒸包炉耗电(1000W)','蒸点销售量(份)');
        $zhengbaoluPower = $this->arrayMulNumber($this->getPowerAverageValue($this->const_zhengbaoluID), 0.001);
        $zhengdianSaleAmount = $this->getMerchandiseClassSalesAmount($this->const_class_zhengdianID);
        $chartPoints['chartName'] = 'Chart_4';
        $chartPoints['names'] = $chartNames;
        $chartPoints['xpoints'] = $this->timePointsString;
        $chartPoints['ypoints'] = array();
        $chartPoints['ypoints'][0] = $zhengbaoluPower;
        $chartPoints['ypoints'][1] = $zhengdianSaleAmount;
        return $chartPoints;
    }

    protected function makeChart5()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        $chartNames = array('总销售额(元)','麻辣烫销售额(元)','面类销售额(元)','蒸点销售额(元)','小吃销售额(元)','饮料销售额(元)');
        $allSaleAmount = $this->getAllMerchandiseClassSaleAmount();
        $malatangSaleAmount = $this->getMerchandiseClassSalesAmount($this->const_class_malatangID);
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
        return $chartPoints;
    }

}