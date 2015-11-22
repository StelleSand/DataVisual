<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/20
 * Time: 20:29
 */

namespace App\Http\Controllers;

use App\Http\Controllers;
use Illuminate\Support\Facades\DB;

class TestController extends Controller{
    protected $start_time;//起始时间
    protected $end_time;//结束时间
    protected $split_number;//图表取点个数
    protected $timePoints;//计算得出的时间点数组
    protected $split_space;//计算得出的每两个时间点间隔

    //定义各个表字段名
    protected $merchandise_class_table_name ='merchandise_class';
    protected $merchandise_table_name = 'merchandise';
    protected $merchandise_table_foreign_key_name = 'merchandise_class';
    protected $order_table_name = 'orders';
    protected $order_table_foreign_key_name = 'merchandise_id';
    protected $order_table_quantityColumn_name = 'quantity';
    protected $order_table_priceColumn_name = 'price';

    protected $channel_record_table_name = 'channel_record';
    protected $channel_record_table_foreign_key_name = 'channel_id';
    protected $channel_record_table_valueColumn_name = 'value';

    //设置sale_amount和sale_volume数组，用以减少重复计算
    protected $merchandise_class_sale_amount = array();
    protected $merchandise_class_sale_volume = array();


    public function __construct()
    {
        //初始化开始时间和结束时间
        $this->start_time = mktime(0, 0, 0, 11, 8, 2015);
        $this->end_time = mktime(0, 0, 0, 11, 9, 2015);
        $this->split_number = 25;
        $this->timePoints = array();
        $result = $this->end_time - $this->start_time;
        $this->split_space = $result / ($this->split_number-1);
        //构建等间隔时间点数组
        //第一个点就是开始点
        $time_point = $this->start_time;
        array_push($this->timePoints,$time_point);
        for($i = 1; $i <= $this->split_number - 1; $i++)
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
     *获取指定表的时序均值数组:即求瞬时值，而非累计值
     *参数为（表名称，外键字段名字，外键字段值,需要获取值的字段名,时间戳字段名[默认为'date']）
     *时间参数从类中的属性获取，默认时间比较采用date字段。
     *
     * */
    protected function getTablePointsAverageValue($table_name,$foreign_key_name,$foreign_key_id,$value_column_name,$datetime_column_name = 'date')
    {
        $Points = array();
        for($i = 0;$i < $this->split_number; $i++) {
            //选取对应时间段数据
            $timePoint_points = DB::table($table_name)->where($foreign_key_name,'=',$foreign_key_id)->whereBetween($datetime_column_name,array($this->timeStampToString($this->timePoints[$i] - $this->split_space / 2),$this->timeStampToString($this->timePoints[$i] + $this->split_space / 2)))->get();
            $timePoint_value_sum = 0;
            //获取对应时间点附近数据和
            foreach($timePoint_points as $timePoint_point)
            {
                $timePoint_value_sum += $timePoint_point->$value_column_name;
            }
            //求对应时间点附近最终数据值
            //防止除0
            if(count($timePoint_points) == 0)
                $timePoint_value = 0;
            else
                $timePoint_value = $timePoint_value_sum / count($timePoint_points);
            //将获得的最终结果加入到channelPoints中
            array_push($Points,$timePoint_value);
        }
        return $Points;
    }

    /*
     * 获取指定channel_id的power时序均值数组
     * */
    protected function getPowerAverageValue($channel_id)
    {
        return $this->getTablePointsAverageValue($this->channel_record_table_name,$this->channel_record_table_foreign_key_name,$channel_id,$this->channel_record_table_valueColumn_name);
    }

    /*
     *获取指定表的时序和值数组:即求每个点周围的累计值，而非均值或总累计值
     *参数为（表名称，外键字段名字，外键字段值,需要获取值的字段名,取值时需要在表内做乘法运算的字段名[默认为null,null则直接取，不做运算],时间戳字段名[默认为'date']）
     *时间参数从类中的属性获取，默认时间比较采用date字段。
     *获取销量则直接取value_column_name = quantity即可，获取销售额则同时应加上value_plus_column_name = price
     * */

    protected function getTablePointsSumValue($table_name,$foreign_key_name,$foreign_key_id,$value_column_name,$value_plus_column_name = null,$datetime_column_name = 'date')
    {
        $Points = array();
        for($i = 0;$i < $this->split_number; $i++) {
            //选取对应时间段数据
            $timePoint_points = DB::table($table_name)->where($foreign_key_name,'=',$foreign_key_id)->whereBetween($datetime_column_name,array($this->timeStampToString($this->timePoints[$i] - $this->split_space / 2),$this->timeStampToString($this->timePoints[$i] + $this->split_space / 2)))->get();
            $timePoint_value_sum = 0;
            //获取对应时间点附近数据和
            foreach($timePoint_points as $timePoint_point)
            {
                if(is_null($value_plus_column_name))
                    $timePoint_value_sum += $timePoint_point->$value_column_name;
                else
                    $timePoint_value_sum += $timePoint_point->$value_column_name * $timePoint_point->$value_plus_column_name;
            }
            //将获得的最终结果加入到channelPoints中
            array_push($Points,$timePoint_value_sum);
        }
        return $Points;
    }


    /*
     * 获取指定种类商品销售额数组
     * 参数为(商品种类id,商品种类表名字[],商品表名字[],商品表外键名[],订单表名[],订单表外键名[])
     * */
    protected function getMerchandiseClassSalesAmount($merchandise_class_id)
    {
        //如果商品种类的销售额数组已经被设置，直接返回
        if(isset($this->merchandise_class_sale_amount[$merchandise_class_id]))
            return $this->merchandise_class_sale_amount[$merchandise_class_id];
        $points = array();
        //初始化points数组
        for($i = 0;$i < $this->split_number; $i++)
            $points[$i] = 0;
        //获取所有属于此商品种类的商品列表
        $merchandises = DB::table($this->merchandise_table_name)->where($this->merchandise_table_foreign_key_name,'=',$merchandise_class_id)->get();
        //分别统计每种商品在特定时间点的销售额，并累加到商品种类的销售额上
        foreach($merchandises as $merchandise)
        {
            //获取每种商品在所有时间点的销售额数组
            $merchandiseValuePoints = $this->getTablePointsSumValue($this->order_table_name, $this->order_table_foreign_key_name, $merchandise->id,$this->order_table_quantityColumn_name, $this->order_table_priceColumn_name );
            //在每个时间点进行累加
            for($i = 0;$i < $this->split_number; $i++)
                //将商品销售额累加到商品种类销售额上
                $points[$i] += $merchandiseValuePoints[$i];
        }
        //设置商品种类的销售额数组
        $this->merchandise_class_sale_amount[$merchandise_class_id] = $points;
        //最后获得累加后的商品种类销售额，返回
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
        //初始化points数组
        for($i = 0;$i < $this->split_number; $i++)
            $points[$i] = 0;
        //获取所有属于此商品种类的商品列表
        $merchandises = DB::table($this->merchandise_table_name)->where($this->merchandise_table_foreign_key_name,'=',$merchandise_class_id)->get();
        //分别统计每种商品在特定时间点的销量，并累加到商品种类的销量上
        foreach($merchandises as $merchandise)
        {
            //获取每种商品在所有时间点的销量数组
            $merchandiseValuePoints = $this->getTablePointsSumValue($this->order_table_name, $this->order_table_foreign_key_name, $merchandise->id,$this->order_table_quantityColumn_name );
            //在每个时间点进行累加
            for($i = 0;$i < $this->split_number; $i++)
                //将商品销售额累加到商品种类销量上
                $points[$i] += $merchandiseValuePoints[$i];
        }
        //设置商品种类的销售量数组
        $this->merchandise_class_sale_volume[$merchandise_class_id] = $points;
        //最后获得累加后的商品种类销量，返回
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
        $merchandise_classes = DB::table($this->merchandise_class_table_name)->where('create_user','=','1')->get();
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
        $merchandise_classes = DB::table($this->merchandise_class_table_name)->where('create_user','=','1')->get();
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
        $allPowerPoints = $this->getTablePointsAverageValue('channel_record','channel_id',$allPowerChannelID,'value');
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