/**
 * Created by v5 on 2016/1/27.
 */
var timeoutId;
var appendPoint = false;
function realtimeToggle(button)
{
    // 获取检查过的数据
    var data = getRealtimeData();
    // 如果获取数据过程出错，结束函数
    if(data == 0)
        return;
    if($(button).hasClass('btn-default')) {
        $(button).removeClass('btn-default');
        $(button).addClass('btn-info');
        $(button).text('Real Time On!');
        var timeSpace = 0;
        if(data['type'] == 'window')
            appendPoint = true;
        else if(data['type'] == 'append')
            appendPoint = false;
        // 5分钟一次更新
        timeoutId = setInterval('ajaxUpdateCharts()', 1000 * 60 * globalData['space']);
        baseSetCharts(data);
    }
    else
    {
        $(button).removeClass('btn-info');
        $(button).addClass('btn-default');
        $(button).text('Real Time!');
        clearInterval(timeoutId);
    }
}
// 检查并获取realtime相关数据
function getRealtimeData(){
    var data = {};
    data['type'] = $('#real_time_type').val();
    if(isNull(data['type']))
    {
        var messages = [{class:'alert-warning', message:'Undefined Parameter in Real-Time Illustration!'}];
        showAlertMessages(messages);
        return 0;
    }
    data['interval'] = $('#real_time_interval').val();
    if(isNaN(data['interval']) || isNull(data['interval']))
    {
        var messages = [{class:'alert-warning', message:'Illegal Input in Real-Time Split Number!'}];
        showAlertMessages(messages);
        return 0;
    }
    data['hours'] = $('#real_time_hours').val();
    if(data['type'] == 'window' && (isNaN(data['hours']) || isNull(data['hours'])))
    {
        var messages = [{class:'alert-warning', message:'Illegal Input in Real-Time Time Length!'}];
        showAlertMessages(messages);
        return 0;
    }
    return data;
}
//ajax方式获取数据并完全替换数据
function baseSetCharts(data)
{
    var addr = 'realtime';
    var sendData = {};
    if(data['type'] == 'window')
        sendData = {range : 'hour',timelength : data['hours'], interval : data['interval']};
    else if(data['type'] == 'append')
        sendData = { interval : data['interval'] };
    var recallfunc = allReplaceCharts;
    ajaxData(addr, sendData, recallfunc);
}
//ajax方式获取数据并更新数据
function ajaxUpdateCharts()
{
    var addr = 'realtime';
    var data = {range : 'minute',timelength : Math.floor(globalData['space'] / 60), split : 2, datetime : globalData['nextDatetime']};
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
//部分替换图表数据函数，ajax回调使用,用于展示windows类型窗口
function partReplaceCharts(result, status)
{
    var result = JSON.parse(result);
    //更新全局变量 globalData
    globalData = result['data'];
    for(var i = 0; i < result['charts'].length; i++)
        partConfigChart(result['charts'][i]);
}
// 图表数据更新函数,用于进行全部替换更新
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
// 图表数据更新函数,用于进行window类型或append类型更新
function partConfigChart(chartData)
{
    //获取全局的chart和对应option变量
    var myChart = globalCharts[chartData['chartName']];
    option = globalChartsOptions[chartData['chartName']];
    for(var i = 0; i < chartData['names'].length; i++)
    {
        // 如果不是叠加模式，则移除最开始的点
        if(!appendPoint)
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
    // 如果不是叠加模式，则移除最开始的点
    if(!appendPoint)
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