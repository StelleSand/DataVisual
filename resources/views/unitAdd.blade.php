<form action="addUnit" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <br>
    Unit Name:<input type="text" name="name" placeholder="Name">
    <br>
    Unit 公式:<input type="text" name="formula" placeholder="FORMAT:c1+(c2-c3)*c4+c5/c6">
    <br>
    Unit 描述:<textarea name="detail" ></textarea>
    <br>
    <button type="submit">新建unit</button>
</form>