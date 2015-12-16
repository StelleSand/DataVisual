<form action="addChart" method="post">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <br>
    Chart Name:<input type="text" name="name" placeholder="Name">
    <br>
    Chart 描述:<textarea name="detail" ></textarea>
    <br>
    <button type="submit">新建Chart</button>
</form>