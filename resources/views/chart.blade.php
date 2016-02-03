<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="{{ $chartName }}" class="chartDiv"></div>
<script type="text/javascript">
    var clientWidth = document.body.clientWidth;
    var height = Math.round(clientWidth * 0.30674847);
    if (height < 300)
        height = 300;
    $("#{{ $chartName }}").css('height',height.toString() + 'px');
    // 基于准备好的dom，初始化echarts图表
    var myChart = echarts.init(document.getElementById("{{  $chartName }}"));

    var option = {
        tooltip : {
            trigger: 'axis'
        },
        legend: {
            data:<?php echo json_encode($names); ?>,
            padding : [5,0]
        },
        toolbox: {
            show : true,
            feature : {
                //mark : {show: false},
                //dataView : {show: true, readOnly: false},
                magicType : {show: true, type: ['line','bar','stack', 'tiled']},
                //restore : {show: true},
                saveAsImage : {show: true}
            }
        },
        calculable : false,
        xAxis : [
            {
                type : 'category',
                boundaryGap : false,
                data : <?php echo json_encode($xpoints); ?>
            }
        ],
        yAxis : [
            {
                type : 'value',
                axisLabel : {
                    formatter: '{value} W'
                },
                name : '功率',
                nameLocation : 'end',
                min : 0
            },
            {
                type : 'value',
                axisLabel : {
                    formatter: '{value}'
                },
                name : '销售额(元)/销量(份)',
                nameLocation : 'end',
                min : 0,
                position:'<?php foreach($types as $type) if($type == 'power') {echo 'right'; goto over;} echo 'left'; over:;  ?>',
            }
        ],
        series : [
            @for($i = 0 ; $i < count($names) ; $i++)
            {
                name:'{{$names[$i] }}',
                type:'line',
                yAxisIndex : {{ $types[$i] == 'power'? 0:1 }},
                stack: '{{ $types[$i] }}',
                clipOverflow : false,
                itemStyle: {normal: {areaStyle: {type: 'default'}}},
                data:<?php echo json_encode($ypoints[$i]); ?>,
                @if($types[$i] == 'power')
                markLine : {
                    data : [
                        {type : 'average', name: '平均{{ $names[$i] }}'}
                    ]
                },
                @endif
            }
            @if($i < count($names) - 1 ) {{ ',' }}
            @endif
            @endfor
        ]
    };
    // 为echarts对象加载数据
    myChart.setOption(option);
    globalCharts['{{ $chartName }}'] = myChart;
    globalChartsOptions['{{ $chartName }}'] = option;
</script>
<div class="container">
    <div class="row">
        <div class="col-lg-12 col-md-12 ool-sm-12 col-xs-12">
            <div class="table-responsive">
                <table class="table table-striped .table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            @for($i = 0; $i < count($names); $i ++)
                            <th>
                                {{ substr($names[$i],0, strpos($names[$i], '(')) }}
                            </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>现在</td>
                            @for($i = 0; $i < count($names); $i ++)
                            <td id="{{ 'now_'.$chartName.'_'.$i }}">
                                {{ floor($ypoints[$i][count($ypoints[$i]) - 1] * ($types[$i] == 'power'? $data['space'] / 3600 : 1)) }}
                            </td>
                            @endfor
                        </tr>
                        <tr>
                            <td>累计</td>
                            @for($i = 0; $i < count($names); $i ++)
                            <td id="{{ 'accumulation_'.$chartName.'_'.$i }}">
                                <?php $result = 0;?>
                                @foreach($ypoints[$i] as $key => $ypoint)
                                    <?php if($key != 0) $result += $ypoint; ?>
                                @endforeach
                                {{ floor($result * ($types[$i] == 'power'? $data['space'] / 3600 : 1)) }}
                            </td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>