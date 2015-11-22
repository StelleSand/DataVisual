<!doctype html>
<head xmlns="http://www.w3.org/1999/html">
    <link href="static/css/bootstrap.min.css" rel="stylesheet">
    <link href="static/css/buttons.css" rel="stylesheet">
    <script src="static/js/jquery.min.js"></script>
    <script src="static/js/header.js"></script>
    <script src="static/js/headroom.js"></script>
    <script src="static/js/jquery.bootstrap-autohidingnavbar.js"></script>

    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.2/raphael-min.js"></script>
    <script src="static/lib/morris.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/prettify/r224/prettify.min.js"></script>
    <script src="static/lib/example.js"></script>
    <link rel="stylesheet" href="static/lib/example.css">
    <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/prettify/r224/prettify.min.css">
    <link rel="stylesheet" href="static/lib/morris.css">
</head>
<body>
<h1>第一张图表</h1>
<div id="graph"></div>
<div id="code" name="codepre" style="" class="prettyprint linenums">
<srript>
    var day_data = [
    <?php $PointNumber = count($date);?>
    @for($i = 0;$i < $PointNumber ; $i++ )
        <?php $PointNumber2 = count($date[$i]);?>
        @for($j = 0;$j < $PointNumber2 ; $j++ )
            @if($i==1)
                {"period": "{{$date[$i][$j]['period']}}", "licensed": "{{$date[$i][$j]['mianluPower']}}", "sorned": "{{$date[$i][$j]['baowentaiPower']}}", "sorned1": "{{$date[$i][$j]['mianleiPowerDivideBySaleAmount']}}"},
            @endif

            @endfor
    @endfor
    ];
    Morris.Line({
    element: 'graph',
    data: day_data,
    xkey: 'period',
    ykeys: ['licensed', 'sorned','sorned1'],
    labels: ['总用电量', '保温台用电','面类用电／销售量']
    });
</srript>
</div>


<h1>第二张图表</h1>
<div id="graph_2"></div>
<div id="code_2" name="codepre" style="" class="prettyprint linenums">
    <script>
    var day_data = [
    <?php $PointNumber = count($date);?>
    @for($i = 0;$i < $PointNumber ; $i++ )
        <?php $PointNumber2 = count($date[$i]);?>
        @for($j = 0;$j < $PointNumber2 ; $j++ )
            @if($i==0)
                {"period": "{{$date[$i][$j]['period']}}", "licensed": "{{$date[$i][$j]['allPower']}}", "sorned": "{{$date[$i][$j]['allSaleAmount']}}", "sorned1": "{{$date[$i][$j]['allSaleVolume']}}","sorned2": "{{$date[$i][$j]['allPowerDivideByAllSaleAmount']}}"},
            @endif

            @endfor
    @endfor
    ];
    Morris.Line({
    element: 'graph_2',
    data: day_data,
    xkey: 'period',
    ykeys: ['licensed', 'sorned','sorned1','sorned2'],
    labels: ['总耗电', '总销售额','总销量','总耗电/总销售额']
    });
    </script>
</div>

<h1>第三张图表</h1>
<div id="graph_3"></div>
<div id="code_3" name="codepre" style="" class="prettyprint linenums">
    <script>
    var day_data = [
    <?php $PointNumber = count($date);?>
    @for($i = 0;$i < $PointNumber ; $i++ )
        <?php $PointNumber2 = count($date[$i]);?>
        @for($j = 0;$j < $PointNumber2 ; $j++ )
            @if($i == 2)
                {"period": "{{$date[$i][$j]['period']}}", "licensed": "{{$date[$i][$j]['allSaleAmount']}}", "sorned": "{{$date[$i][$j]['malatangSaleAmount']}}", "sorned1": "{{$date[$i][$j]['mianleiSaleAmount']}}","sorned2": "{{$date[$i][$j]['xiaochiSaleAmount']}}","sorned3": "{{$date[$i][$j]['yinliaoSaleAmount']}}","sorned4": "{{$date[$i][$j]['zhengdianSaleAmount']}}"},
            @endif

            @endfor
    @endfor
    ];
    Morris.Line({
    element: 'graph_3',
    data: day_data,
    xkey: 'period',
    ykeys: ['licensed', 'sorned','sorned1','sorned2','sorned3','sorned4'],
    labels: ['总销售额', '麻辣烫销售额','面类销售额','小吃销售额','饮料销售额','蒸点销售额']
    });
    </script>
</div>
<h1>第四张图表</h1>
<div id="graph_4"></div>
<div id="code_4" name="codepre" style="" class="prettyprint linenums">
    <script>
    var day_data = [
    <?php $PointNumber = count($date);?>
    @for($i = 0;$i < $PointNumber ; $i++ )
        <?php $PointNumber2 = count($date[$i]);?>
        @for($j = 0;$j < $PointNumber2 ; $j++ )
            @if($i == 3)
                {"period": "{{$date[$i][$j]['period']}}", "licensed": "{{$date[$i][$j]['allPower']}}", "sorned": "{{$date[$i][$j]['producePower']}}", "sorned1": "{{$date[$i][$j]['frontPower']}}","sorned2": "{{$date[$i][$j]['storagePower']}}"},
            @endif

            @endfor
    @endfor
    ];
    Morris.Line({
    element: 'graph_4',
    data: day_data,
    xkey: 'period',
    ykeys: ['licensed', 'sorned','sorned1','sorned2'],
    labels: ['总耗电', '生产耗电','前厅耗电','存储耗电']
    });
    </script>
</div>
</body>
<script>
    $(function () {
        var html="<table class='table'><thead><tr><th style='width:100px;display: block;'> #</th>"
        <?php $PointNumber2 = count($date[1]);?>
        @for($j = 11;$j < 21 ; $j++ )
        html+="<th style='width:50%;'>{{$date[1][$j]['period']}}</th>";
        @endfor

        html+=" </tr></thead><tbody><tr class='active'><th scope='row'>总用电量</th>";
        @for($j = 11;$j <21 ; $j++ )
        html+="<td>{{$date[1][$j]['mianluPower']}}</td>";
        @endfor
       html+=" </tr><<tr class=''><th scope='row'>保温台用电</th>";
        @for($j = 11;$j < 21 ; $j++ )
        html+="<td>{{$date[1][$j]['baowentaiPower']}}</td>";
        @endfor
         html+=" </tr><<tr class='active'><th scope='row'>面类用电／销售量</th>";
        @for($j = 11;$j < 21 ; $j++ )
        html+="<td>{{$date[1][$j]['mianleiPowerDivideBySaleAmount']}}</td>";
        @endfor
        html+="</tr></tbody></table>";
        document.getElementById('code').innerHTML=html;
    })
</script>
