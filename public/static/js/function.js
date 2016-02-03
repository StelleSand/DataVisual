/**
 * Created by v5 on 2016/1/27.
 */
var timeoutId;
function realtimeToggle(button)
{
    if($(button).hasClass('btn-default')) {
        $(button).removeClass('btn-default');
        $(button).addClass('btn-info');
        //5分钟一次更新
        timeoutId = setInterval('ajaxUpdateCharts()', 1000 * 60 * 5);
        baseSetCharts();
    }
    else
    {
        $(button).removeClass('btn-info');
        $(button).addClass('btn-default');
        clearInterval(timeoutId);
    }
}
function baseSetCharts()
{
    var addr = 'realtime';
    var data = {range : 'hour',timelength : 2, split : 25};
    var recallfunc = allReplaceCharts;
    ajaxData(addr, data, recallfunc);
}
function ajaxUpdateCharts()
{
    var addr = 'realtime';
    var newDate = new Date()
    var data = {range : 'minute',timelength : 5, split : 2, datetime : globalData['nextDatetime']};
    var recallfunc = partReplaceCharts;
    ajaxData(addr, data, recallfunc);
}
function allReplaceCharts(result, status)
{
    var result = JSON.parse(result);
    //更新全局变量 globalData
    globalData = result['data'];
    for(var i = 0; i < result['charts'].length; i++)
        allConfigChart(result['charts'][i]);
}
function partReplaceCharts(result, status)
{
    var result = JSON.parse(result);
    //更新全局变量 globalData
    globalData = result['data'];
    for(var i = 0; i < result['charts'].length; i++)
        partConfigChart(result['charts'][i]);
}
function allConfigChart(chartData)
{
    //获取全局的chart和对应option变量
    var myChart = globalCharts[chartData['chartName']];
    option = globalChartsOptions[chartData['chartName']];
    for(var i = 0; i < chartData['names'].length; i++)
    {
        option.series[i].data = chartData['ypoints'][i];
        var result = 0;
        for(var j = 1; j < chartData['ypoints'][i].length; j++)
        {
            result += chartData['ypoints'][i][j];
        }
        var factor = chartData['types'][i] == 'power'? parseFloat(globalData['space'].toString()) / 3600 : 1;
        var nowValue = Math.floor(chartData['ypoints'][i][chartData['ypoints'][i].length - 1] * factor);
        $('#now_' + chartData['chartName'] + '_' + i.toString()).text(nowValue.toString());
        var accumulationValue = Math.floor(result * factor);
        $('#accumulation_' + chartData['chartName'] + '_' + i.toString()).text(accumulationValue.toString());
    }

    option.xAxis[0].data = chartData['xpoints'];
    myChart.setOption(option);
}
function partConfigChart(chartData)
{
    //获取全局的chart和对应option变量
    var myChart = globalCharts[chartData['chartName']];
    option = globalChartsOptions[chartData['chartName']];
    for(var i = 0; i < chartData['names'].length; i++)
    {
        option.series[i].data.shift();
        option.series[i].data.push(chartData['ypoints'][i][1]);
        var data = option.series[i].data;
        var result = 0;
        for(var j = 1; j < data.length; j++)
        {
            result += data[j];
        }
        var factor = chartData['types'][i] == 'power'? parseFloat(globalData['space'].toString()) / 3600 : 1;
        var nowValue = Math.floor(chartData['ypoints'][i][chartData['ypoints'][i].length - 1] * factor);
        $('#now_' + chartData['chartName'] + '_' + i.toString()).text(nowValue.toString());
        var accumulationValue = Math.floor(result * factor);
        $('#accumulation_' + chartData['chartName'] + '_' + i.toString()).text(accumulationValue.toString());
    }

    option.xAxis[0].data.shift();
    option.xAxis[0].data.push(chartData['xpoints'][1]);
    myChart.setOption(option);
}
function resizeHandle()
{
    var clientWidth = document.body.clientWidth;
    var height = Math.round(clientWidth * 0.30674847);
    if (height < 300)
        height = 300;
    $(".chartDiv").css('height',height.toString() + 'px');
    for(var chartName in globalCharts) {
        globalCharts[chartName].resize();
    }
}