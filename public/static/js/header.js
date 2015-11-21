$(function(){
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    //ajax全局设置，加上这个才能在laravel框架下ajax成功
    $( document ).ajaxError(function( event, request, settings ) {
        showAjaxError(event['type'],request['status'],request['statusText'],request['responseText']);
    });
    //为全局ajaxError事件注册消息提示框
});
//生成一个表单提交按钮，参数分别为 按钮文字、按钮blockclass、SizeClass、目标表单名字、按钮点击回调函数、ajax提交成功处理回调函数
function getFormBtn(btnText,btnColor,btnBlock,btnSize,targetFormName,btnClickFunc,dataProcessFunc)
{
    var btn = $('<button></button>').append(btnText).addClass('btn').addClass(btnColor).addClass(btnBlock).addClass(btnSize).attr('data-form',targetFormName).attr('data-recall',dataProcessFunc).on('click',btnClickFunc);
    return btn;
}
//通过表单提交按钮提交表单数据函数，对表单表头信息、提交按钮data-信息有要求
function postFormByFormbtn()
{
    var formID = $(this).attr('data-form');
    var postAddress = $('#' + formID).attr('data-target');
    var recallFunc = $(this).attr('data-recall');
    ajaxOneFormByID(formID,postAddress,recallFunc);
}
//通过表单ID ajax提交数据,并执行成功时的回调函数
function ajaxOneFormByID(formID,postAddress,recallFunc,clearFormData)
{
    var data = getFormData($('#' + formID),clearFormData);
    ajaxData(postAddress,data,recallFunc);
}
//根据表单元素获取表单内所有数据（input和textarea）,参数为表单元素，不是ID
function getFormData(formElement,clearFormData){
    var data = {};
    var clear = !isNull(clearFormData) && clearFormData ? true : false;
    $(formElement).find('input').each(function() {
        var name = $(this).attr('name');
        if(name) {
            var val = $(this).val();
            if(!val)
                val = $(this).attr('placeholder');
            data[name] = val;
        }
        if(clear) $(this).val('');
        if(isNull(data[name])) {
            throw new Error(name + '表单输入框为空或者输入不合法！',1);
        }
    });
    $(formElement).find('textarea').each(function() {
        var name = $(this).attr('name');
        if(name) {
            var val = $(this).val();
            if(!val)
                val = $(this).attr('placeholder');
            data[name] = val;
        }
        if(clear) $(this).val('');
        if(isNull(data[name])) {
            console.log(formElement);
            throw new Error( name + '文本输入框不能为空！',1);
        }
    });
    return data;
}
//判断一个变量是否为空，不存在或者空字符串
function isNull(data)
{
    return (data == null || data == '' || data == undefined) ? true : false;
}
//ajax提交数据并执行成功后的回调函数
function ajaxData(postAddress,data,recallFunc)
{
    $.post(postAddress,data,function(result,status){
       eval(recallFunc)(result,status);
    });
}
//用消息提示框显示ajaxError信息的函数
function showAjaxError(ErrorType,status,message,responseText){
    var messages = Array();
    messages[0] = Array();
    messages[0]['class'] = 'alert-warning';
    messages[0]['message'] = ErrorType + "(" + status.toString() + ")  :  " + message;
    messages[0]['message'] += responseText;
    showAlertMessages(messages,null);
}
//用消息提示框展示ajax返回的消息的函数，参数为ajax的结果，
function showAlertMessages(messages,status){
    var messagesContent;
    for(var i = 0 ; i < messages.length ; i++)
    {
        if(i == 0) messagesContent = getMessageAlert(messages[i]['class'],null,messages[i]['message']);
        else $(messagesContent).after(getMessageAlert(messages[i]['class'],null,messages[i]['message']));
    }
    showMessage(messagesContent);
}
//根据警告CLass，前置label、信息文字生成一个警告框的函数
function getMessageAlert(alertClass,label,message)
{
    var alertDiv = $('<div></div>').addClass('alert').addClass(alertClass);
    $(alertDiv).append(label);
    $(alertDiv).append(message);
    return alertDiv
}
//用消息提示框显示消息
function showMessage(messageContent){
    $('#message-content').empty();
    $('#message-content').append(messageContent);
    $('#messageModel').modal('show');
}
//按照Bootstrap标准格式——Div+label+input组合生成一个表单输入框
function getFormGroup(labelText,inputName,inputType,inputPlaceholder,inputSize)
{
    var formgroup = $('<div></div>').addClass('form-group');
    var label = $('<label></label>').append(labelText);
    if(inputType != 'textarea') {
        var input = $('<input>', {
            type: inputType,
            name: inputName,
            placeholder: inputPlaceholder
        }).addClass(inputSize).addClass('form-control');
    }
    else
    {
        var input = $('<textarea></textarea>',{
            name:inputName,
            placeholder:inputPlaceholder,
            rows:8,
        }).addClass('unresize').addClass('form-control');
    }
    $(formgroup).append(label).append(input);
    return formgroup;
}
//抓取web前端错误,主要用于前端差错
function catchClientError(e){
    var messages = Array();
    var message = Array();
    message['type'] = 'error';
    message['class'] = 'alert-warning';
    message['message'] = 'Client Error : ' + e.name + "<hr/>&nbsp;&nbsp;&nbsp;&nbsp;" + e.message;
    messages[0] = message;
    showAlertMessages(messages,'');
}
//此函数判断用户是否已经登录，主要用于一些必须要登陆才能进行的操作
function isLogin()
{
    return $('meta[name="islogin"]').attr('content') == '1';
}