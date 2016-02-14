@include('header')
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">烫烫记庄胜崇光百货</a>
        </div>

        <div class="collapse navbar-collapse" id="navbar-collapse">
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
<div class="container-fluid">
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="optionHeading">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#optionCollapse" aria-expanded="false" aria-controls="optionCollapse">
                        Options
                    </a>
                </h4>
            </div>
            <div id="optionCollapse" class="panel-collapse collapse" role="tabpanel" aria-labelledby="optionHeading">
                <div class="panel-body">
                    <form id="chartInfo" method="get" action="" class="form-horizontal" role="attachedInfo">
                        <div id="option-form-div" class="form-group">
                            <div class="form-group">
                                <label for="range" class="col-xs-offset-1 col-sm-offset-1 col-md-offset-2 col-sm-3 col-xs-3 col-md-2 control-label">Time Range</label>
                                <div class="col-sm-6 col-xs-6 col-md-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" required id="range" name="range" value="{{ $data['range'] }}" placeholder="{{ $data['range'] }}" readonly>
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Range <span class="caret"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="$('#range').val('hour');$('#timeUnit').text('hours');">Hour</a></li>
                                                <li><a onclick="$('#range').val('day');$('#timeUnit').text('days');">Day</a></li>
                                                <li><a onclick="$('#range').val('week');$('#timeUnit').text('weeks');">Week</a></li>
                                                <li><a onclick="$('#range').val('month');$('#timeUnit').text('months');">Month</a></li>
                                                <li><a onclick="$('#range').val('year');$('#timeUnit').text('year');">Year</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="datetimepicker" class="col-xs-offset-1 col-sm-offset-1 col-md-offset-2 col-sm-3 col-xs-3 col-md-2 control-label">Check Time</label>
                                <div class="col-sm-6 col-xs-6 col-md-4">
                                    <div class="input-group date" id="datetimeDiv" data-date="{{ $data['datetime'] }}" data-date-format="yyyy-mm-dd hh:ii" data-link-field="datetimepicker" data-link-format="yyyy-mm-dd hh:ii">
                                        <input class="form-control" type="text" value="{{ $data['datetime'] }}" placeholder="{{ $data['datetime'] }}" readonly>
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-remove"></i></span>
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                                    </div>
                                    <input type="hidden" name="datetime" required id="datetimepicker" /><br/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="timelength" class="col-xs-offset-1 col-sm-offset-1 col-md-offset-2 col-sm-3 col-xs-3 col-md-2 control-label">Time Length</label>
                                <div class="col-sm-6 col-xs-6 col-md-4">
                                    <div class="input-group">
                                        <input id="hours" name="timelength" type="number" min="1" step="1" class="form-control" value="{{ $data['timeLength'] }}" placeholder="{{ $data['timeLength'] }}">
                                        <span class="input-group-addon" id="timeUnit">{{ $data['range'].'s' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="timelength" class="col-xs-offset-1 col-sm-offset-1 col-md-offset-2 col-sm-3 col-xs-3 col-md-2 control-label">Interval</label>
                                <div class="col-sm-6 col-xs-6 col-md-4">
                                    <div class="input-group">
                                        <input id="interval" name="interval" type="number" min="1" step="1" class="form-control" value="{{ floor($data['space'] / 60) }}" placeholder="{{ floor($data['space'] / 60) }}">
                                        <span class="input-group-addon">Minutes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-offset-4 col-sm-offset-4 col-md-offset-4 col-sm-6 col-xs-6 col-md-4">
                                <button type="submit" class="btn btn-default">View</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="realTimeHeading">
                <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#realTimeCollapse" aria-expanded="false" aria-controls="realTimeCollapse">
                        Real-Time
                    </a>
                </h4>
            </div>
            <div id="realTimeCollapse" class="panel-collapse collapse" role="tabpanel" aria-labelledby="realTimeHeading">
                <div class="panel-body">
                    <div class="form-horizontal" role="realTimeInfo">
                        <div id="real-time-form-div" class="form-group">
                            <div class="form-group">
                                <label for="range" class="col-xs-offset-1 col-sm-offset-1 col-md-offset-2 col-sm-3 col-xs-3 col-md-2 control-label">Illustration</label>
                                <div class="col-sm-6 col-xs-6 col-md-4">
                                    <div class="input-group">
                                        <input type="text" class="form-control" required id="real_time_type" value="" placeholder="" readonly>
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Type <span class="caret"></span></button>
                                            <ul class="dropdown-menu">
                                                <li><a onclick="$('#real_time_type').val('append');$('#timeLengthDiv').addClass('hidden');">Append</a></li>
                                                <li><a onclick="$('#real_time_type').val('window');$('#timeLengthDiv').removeClass('hidden');">Window</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="real_time_split" class="col-xs-offset-1 col-sm-offset-1 col-md-offset-2 col-sm-3 col-xs-3 col-md-2 control-label">Interval</label>
                                <div class="col-sm-6 col-xs-6 col-md-4">
                                    <div class="input-group">
                                        <input id="real_time_interval" type="number" min="1" step="1" class="form-control" value="5" placeholder="5">
                                        <span class="input-group-addon">Minutes</span>
                                    </div>
                                </div>
                            </div>
                            <div id="timeLengthDiv" class="form-group">
                                <label for="real_time_hours" class="col-xs-offset-1 col-sm-offset-1 col-md-offset-2 col-sm-3 col-xs-3 col-md-2 control-label">Time Length</label>
                                <div class="col-sm-6 col-xs-6 col-md-4">
                                    <div class="input-group">
                                        <input id="real_time_hours" type="number" min="1" step="1" class="form-control" value="2" placeholder="2">
                                        <span class="input-group-addon" id="timeUnit">Hours</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-offset-4 col-sm-offset-4 col-md-offset-4 col-sm-6 col-xs-6 col-md-4">
                                <button type="button" onclick="realtimeToggle(this)" class="btn btn-default">Real Time!</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#datetimeDiv ').datetimepicker();
    $("#nav-form-div").children('br').remove();
    // 路径配置
    /*require.config({
        paths: {
            echarts: 'http://echarts.baidu.com/build/dist'
        }
    });
    */
    var globalCharts = {};
    var globalChartsOptions = {};
    var globalData = <?php unset($data['user']); echo json_encode($data); ?>;
</script>
<?php
?>
@foreach($charts as $chart)
    <?php $chart['data'] = $data;?>
    @include('chart',$chart)
@endforeach
<script>
    $(window).resize(resizeHandle);
    $(window).on('orientationchange',resizeHandle());
</script>
<div class="modal fade" id="messageModel" tabindex="-1" role="dialog" aria-labelledby="messageModelTitle">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="messageModelTitle">Info</h4>
            </div>
            <div id="messageModelBody" class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Got it.</button>
            </div>
        </div>
    </div>
</div>
@include('footer')