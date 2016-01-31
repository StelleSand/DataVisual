@include('header')
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">烫烫记庄胜崇光百货</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-left">
                <form id="chartInfo" method="get" action="/" class="navbar-form navbar-left" role="attachedInfo">
                    <!--<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">-->
                    <div id="nav-form-div" class="form-group">
                        <!--<input name="datetime" id="datetimepicker" type="datetime" class="form-control" placeholder="{{ $datetime }}" data-date-format="yyyy-mm-dd hh:ii">-->
                        <div class="input-group date" id="datetimeDiv" data-date="{{ $datetime }}" data-date-format="yyyy-mm-dd hh:ii" data-link-field="datetimepicker" data-link-format="yyyy-mm-dd hh:ii">
                            <input class="form-control" type="text" placeholder="{{ $datetime }}" readonly>
                            <span class="input-group-addon"><i class="glyphicon glyphicon-remove"></i></span>
                            <span class="input-group-addon"><i class="glyphicon glyphicon-time"></i></span>
                        </div>
                        <input type="hidden" name="datetime" id="datetimepicker" value="" /><br/>
                        <div class="input-group">
                            <input id="hours" name="hours" type="number" min="0.5" step="0.5" class="form-control" value="{{ $hours }}" placeholder="{{ $hours }}">
                            <span class="input-group-addon">Hours</span>
                        </div>
                        <div class="input-group">
                            <input id="split" name="split" type="number" min="5" step="1" class="form-control" value="{{ $split }}" placeholder="{{ $split }}">
                            <span class="input-group-addon">Points</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-default navbar-btn">View</button>
                </form>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><button class="btn btn-default navbar-btn" onclick="realtimeToggle(this)">Real-Time</button></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
<script>
    $('#datetimeDiv ').datetimepicker();
    $("#nav-form-div").children('br').remove();
    // 路径配置
    require.config({
        paths: {
            echarts: 'http://echarts.baidu.com/build/dist'
        }
    });
    var globalCharts = {};
</script>
@foreach($charts as $chart)
    @include('chart',$chart)
@endforeach
<script>
    $(window).resize(resizeHandle);
</script>
@include('footer')