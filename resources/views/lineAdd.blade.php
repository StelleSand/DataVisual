<form action="addLine" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <br>
    Line Name:<input type="text" name="name" placeholder="Name">
    <br>
    Line 公式:<input type="text" name="formula" placeholder="FORMAT:mca1/u1 | ma2/u2 | mv3 | u3 | ma4">
    <br>
    Line 描述:<textarea name="detail" ></textarea>
    <br>
    <button type="submit">新建Line</button>
</form>