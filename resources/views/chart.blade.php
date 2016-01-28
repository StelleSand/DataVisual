<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
<div id="{{ $chartName }}"></div>
<script type="text/javascript">
    var clientWidth = document.body.clientWidth;
    var height = (Math.round(clientWidth * 0.30674847)).toString() + 'px';
    $("#{{ $chartName }}").css('height',height);
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