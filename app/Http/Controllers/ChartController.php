<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/20
 * Time: 20:29
 */

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers;
use App\Merchandise;
use Illuminate\Support\Facades\DB;
use App\MerchandiseClass;
use App\Channel;
class ChartController extends Controller{
    protected $start_time;//起始时间
    protected $end_time;//结束时间
    protected $split_number;//图表取点个数
    protected $timePoints;//计算得出的时间点数组
    protected $split_space;//计算得出的每两个时间点间隔
    protected $user_id;//预设使用制表的用户
    protected $user;//预设用户Model
    protected $roundNumber;//本类计算结果保留位数


    //设置sale_amount和sale_volume数组，用以减少重复计算
    protected $merchandise_class_sale_amount = array();
    protected $merchandise_class_sale_volume = array();


    //设置channel_average_power数组，用以减少重复计算
    protected $channel_average_power = array();


    public function __construct()
    {
        //初始化开始时间和结束时间
        $this->start_time = mktime(0, 0, 0, 11, 8, 2015);
        $this->end_time = mktime(0, 0, 0, 11, 9, 2015);
        $this->split_number = 25;
        $this->timePoints = array();
        $result = $this->end_time - $this->start_time;
        $this->split_space = $result / ($this->split_number-1);
        //初始化制表用户
        $this->user_id = 1;
        $this->user = User::find($this->user_id);
        //设置所有计算保留为小数点后两位
        $this->roundNumber = 2;
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
            array_push($this->timePoints,$time_point);
        }
    }

    protected function timeStampToString($time)
    {
        return date('Y-m-d H:i:s',$time);
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

    public function testDiagram()
    {
        $charts = array();
        array_push($charts,$this->makeChart1());
        array_push($charts,$this->makeChart2());
        array_push($charts,$this->makeChart3());
        array_push($charts,$this->makeChart4());
        foreach($charts as $chart_index => $chart)
        {
            foreach($chart as $point_index => $point)
            {
                foreach($point as $value_index => $value)
                {
                    if(is_numeric($value))
                        $charts[$chart_index][$point_index][$value_index] = round($value ,2);
                }
            }
        }
        return view('test',['date'=>$charts]);
    }
    protected function makeChart1()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        //设置allPower的channel_id为2
        $allPowerChannelID = 2;
        $allPowerPoints = $this->getPowerAverageValue($allPowerChannelID);
        $allSaleAmount = $this->getAllMerchandiseClassSaleAmount();
        $allSaleVolume = $this->getAllMerchandiseClassSaleVolume();
        for($i = 0;$i < $this->split_number; $i++) {
            //设置period字段
            $chartPoint = array();
            $chartPoint['period'] = $this->timeStampToString($this->timePoints[$i]);
            $chartPoint['allPower'] = $allPowerPoints[$i];
            $chartPoint['allSaleAmount'] = $allSaleAmount[$i];
            $chartPoint['allSaleVolume'] = $allSaleVolume[$i];
            $chartPoint['allPowerDivideByAllSaleAmount'] = $chartPoint['allSaleAmount'] == 0 ? 0 : $chartPoint['allPower'] / $chartPoint['allSaleAmount'];
            //将计算后的点加入到chartPoints中;
            array_push($chartPoints,$chartPoint);
        }
        return $chartPoints;
    }

    protected function makeChart2()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        //设置面炉的channel_id为3,保温台channel_id为10
        $mianluChannelID = 3;
        $mianluPowerPoints = $this->getPowerAverageValue($mianluChannelID);
        $baowentaiChannelID = 10;
        $baowentaiPowerPoints = $this->getPowerAverageValue($baowentaiChannelID);
        //面类merchandise_class_id为4
        $mianleiMerchandiseClassID = 4;
        $mianleiSaleVolumePoints = $this->getMerchandiseClassSalesVolume($mianleiMerchandiseClassID);
        for($i = 0;$i < $this->split_number; $i++) {
            //设置period字段
            $chartPoint = array();
            $chartPoint['period'] = $this->timeStampToString($this->timePoints[$i]);
            $chartPoint['mianluPower'] = $mianluPowerPoints[$i];
            $chartPoint['baowentaiPower'] = $baowentaiPowerPoints[$i];
            $chartPoint['mianleiSaleVolume'] = $mianleiSaleVolumePoints[$i];
            $chartPoint['mianleiPowerDivideBySaleAmount'] = $chartPoint['mianleiSaleVolume'] == 0 ? 0 : $chartPoint['mianluPower'] / $chartPoint['mianleiSaleVolume'];
            //将计算后的点加入到chartPoints中;
            array_push($chartPoints,$chartPoint);
        }
        return $chartPoints;
    }


    protected function makeChart3()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        //麻辣烫cladd_id为3,面类为4，小吃为5，饮料为6，蒸点merchandise_class_id为7
        $malatangMerchandiseClassID = 3;
        $malatangSaleAmountPoints = $this->getMerchandiseClassSalesAmount($malatangMerchandiseClassID);
        $mianleiMerchandiseClassID = 4;
        $mianleiSaleAmountPoints = $this->getMerchandiseClassSalesAmount($mianleiMerchandiseClassID);
        $xiaochiMerchandiseClassID = 5;
        $xiaochiSaleAmountPoints = $this->getMerchandiseClassSalesAmount($xiaochiMerchandiseClassID);
        $yinliaoMerchandiseClassID = 6;
        $yinliaoSaleAmountPoints = $this->getMerchandiseClassSalesAmount($yinliaoMerchandiseClassID);
        $zhengdianMerchandiseClassID = 7;
        $zhengdianSaleAmountPoints = $this->getMerchandiseClassSalesAmount($zhengdianMerchandiseClassID);
        $allSaleAmountPoints = $this->getAllMerchandiseClassSaleAmount();
        for($i = 0;$i < $this->split_number; $i++) {
            //设置period字段
            $chartPoint = array();
            $chartPoint['period'] = $this->timeStampToString($this->timePoints[$i]);
            $chartPoint['malatangSaleAmount'] = $malatangSaleAmountPoints[$i];
            $chartPoint['mianleiSaleAmount'] = $mianleiSaleAmountPoints[$i];
            $chartPoint['xiaochiSaleAmount'] = $xiaochiSaleAmountPoints[$i];
            $chartPoint['yinliaoSaleAmount'] = $yinliaoSaleAmountPoints[$i];
            $chartPoint['zhengdianSaleAmount'] = $zhengdianSaleAmountPoints[$i];
            $chartPoint['allSaleAmount'] = $allSaleAmountPoints[$i];
            //将计算后的点加入到chartPoints中;
            array_push($chartPoints,$chartPoint);
        }
        return $chartPoints;
    }

    protected function makeChart4()
    {
        //初始化表chartPoints数组
        $chartPoints = array();
        //设置各个channel_id
        $allPowerChannelID = 2;
        $allPowerPoints = $this->getPowerAverageValue($allPowerChannelID);
        $mianluChannelID = 3;
        $mianluPowerPoints = $this->getPowerAverageValue($mianluChannelID);
        $sangeluziChannelID = 4;
        $sangeluziPowerPoints = $this->getPowerAverageValue($sangeluziChannelID);
        $kaishuluChannelID = 5;
        $kaishuiluPowerPoints = $this->getPowerAverageValue($kaishuluChannelID);
        $paifengshanChannelID = 6;
        $paifengshanPowerPoints = $this->getPowerAverageValue($paifengshanChannelID);
        $sangebingxiangChannelID = 7;
        $sangebingxiangPowerPoints = $this->getPowerAverageValue($sangebingxiangChannelID);
        $zhanguibingxiangChannelID = 8;
        $zhanguibingxiangPowerPoints = $this->getPowerAverageValue($zhanguibingxiangChannelID);
        $zhengbaoluChannelID = 9;
        $zhengbaoluPowerPoints = $this->getPowerAverageValue($zhengbaoluChannelID);
        $baowentaiChannelID = 10;
        $baowentaiPowerPoints = $this->getPowerAverageValue($baowentaiChannelID);
        $kelechazuoChannelID =11;
        $kelechazuoPowerPoints = $this->getPowerAverageValue($kelechazuoChannelID);
        for($i = 0;$i < $this->split_number; $i++) {
            //设置period字段
            $chartPoint = array();
            $chartPoint['period'] = $this->timeStampToString($this->timePoints[$i]);
            $chartPoint['allPower'] = $allPowerPoints[$i];
            $chartPoint['producePower'] = $sangeluziPowerPoints[$i] + $kaishuiluPowerPoints[$i] + $zhengbaoluPowerPoints[$i] + $baowentaiPowerPoints[$i] + $paifengshanPowerPoints[$i];
            $chartPoint['frontPower'] = $zhanguibingxiangPowerPoints[$i] + $kelechazuoPowerPoints[$i];
            $chartPoint['storagePower'] = $sangebingxiangPowerPoints[$i];
           //将计算后的点加入到chartPoints中;
            array_push($chartPoints,$chartPoint);
        }
        return $chartPoints;
    }
}