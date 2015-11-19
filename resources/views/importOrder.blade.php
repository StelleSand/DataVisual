<form action="importOrder" method="post">
    Select a OrderRecord File to Import.<br>
    <input type="file" name="OrderRecordFile">
    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"><br>
    <button type="submit" name="Submit" value="Submit">Submit</button>
</form>
</form>