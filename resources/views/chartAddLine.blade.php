<form action="chartAddLine" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <br>
    Chart id:<input type="text" name="chart_id" placeholder="Chart ID">
    <br>
    Line id <input type="text" name="line_id" placeholder="Line ID">
    <br>
    <button type="submit">给Chart添加Line</button>
</form>