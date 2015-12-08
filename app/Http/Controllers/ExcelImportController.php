<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/11/19
 * Time: 16:33
 */
namespace App\Http\Controllers;
use App\Jobs\InsertExcel;
use App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
set_time_limit(0);
class ExcelImportController extends Controller {
    public function importRecord()
    {
        //将insertExcel任务加入队列中.
        $insert = new InsertExcel();
        $insert->handle();
    }
}
