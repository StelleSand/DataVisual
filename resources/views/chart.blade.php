<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="{{ $chartName }}"></div>
<script type="text/javascript">
    var clientWidth = document.body.clientWidth;
    var height = Math.round(clientWidth * 0.30674847);
    if (height < 250)
        height = 250;
    $("#{{ $chartName }}").css('height',height.toString() + 'px');
    // 使用
    require(
            [
                'echarts',
                'echarts/chart/line',
                'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                var myChart = ec.init(document.getElementById("{{  $chartName }}"));

                var option = {
                    tooltip : {
                        trigger: 'axis'
                    },
                    legend: {
                        data:<?php echo json_encode($names); ?>
                    },
                    toolbox: {
                        show : true,
                        feature : {
                            mark : {show: false},
                            dataView : {show: true, readOnly: false},
                            magicType : {show: true, type: ['stack', 'tiled']},
                            restore : {show: true},
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
                            type : 'value'
                        }
                    ],
                    series : [
                        @for($i = 0 ; $i < count($names) ; $i++)
                        {
                            name:'{{$names[$i] }}',
                            type:'line',
                            stack: '总量',
                            itemStyle: {normal: {areaStyle: {type: 'default'}}},
                            data:<?php echo json_encode($ypoints[$i]); ?>
                        }
                        @if($i < count($names) - 1 ) {{ ',' }}
                        @endif
                        @endfor
                    ]
                };

                // 为echarts对象加载数据
                myChart.setOption(option);
                globalCharts['{{ $chartName }}'] = myChart;
            }
    );
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
                            <td>
                                {{ $ypoints[$i][count($ypoints[$i]) - 1] }}
                            </td>
                            @endfor
                        </tr>
                        <tr>
                            <td>累计</td>
                            @for($i = 0; $i < count($names); $i ++)
                            <td>
                                <?php $result = 0;?>
                                @foreach($ypoints[$i] as $ypoint)
                                    <?php $result += $ypoint; ?>
                                @endforeach
                                {{ $result }}
                            </td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>