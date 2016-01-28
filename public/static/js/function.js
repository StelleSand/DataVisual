/**
 * Created by v5 on 2016/1/27.
 */
var timeoutId;
function realtimeToggle(button)
{
    if($(button).hasClass('btn-default')) {
        $(button).removeClass('btn-default');
        $(button).addClass('btn-info');
        //2分钟一次更新
        timeoutId = setInterval('ajaxCharts()', 1000 * 120);
        ajaxCharts();
    }
    else
    {
        $(button).removeClass('btn-info');
        $(button).addClass('btn-default');
        clearInterval(timeoutId);
    }
}
function ajaxCharts()
{
    var addr = 'realtime';
    var data = {'hours':$('#hours').attr('placeholder')};
    var recallfunc = updateCharts;
    ajaxData(addr, data, recallfunc);
}
function updateCharts(result, status)
{
    var result = JSON.parse(result);
    //console.log(result);
    for(var i = 0; i < result['charts'].length; i++)
        configChart(result['charts'][i]);
}
function configChart(chartData)
{
    var lines = [];
    for(var i = 0; i < chartData['names'].length; i++)
    {
        var line = {};
        line['name'] = chartData['names'][i];
        //line['type'] = 'line';
        //line['stack'] = '总量';
        //line['itemStyle'] = {normal: {areaStyle: {type: 'default'}}};
        line['data'] = chartData['ypoints'][i];
        lines.push(line);
    }
    option = {
        xAxis: {
            data : chartData['xpoints']
        },
        series : lines
    };

    console.log(globalCharts);
    var myChart = globalCharts[chartData['chartName']];
    myChart.setOption(option);
    // 使用
    /*require(
        [
            'echarts',
            'echarts/chart/line',
            'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载
        ],
        function (ec) {
            // 基于准备好的dom，初始化echarts图表
            var myChart = ec.init(document.getElementById(chartData['chartName']));

            var option = {
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:chartData['names']
                } ,
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
                        data : chartData['xpoints']
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : lines
            };
            // 为echarts对象加载数据
            myChart.setOption(option);
        }
    );*/
}