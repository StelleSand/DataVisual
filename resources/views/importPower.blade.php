<form action="importPower" method="post">
    Select a PowerRecord File to Import.<br>
    <input type="file" name="PowerRecordFile">
    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>"><br>
    <button type="submit" name="Submit" value="Submit">Submit</button>
</form>