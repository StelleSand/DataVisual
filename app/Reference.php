<?php
/**
 * Created by PhpStorm.
 * User: AILance
 * Date: 2016/4/24
 * Time: 16:13
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    protected $table = 'reference';

    protected $fillable = ['start', 'end', 'attribute','value','powercost'];

    public $timestamps = false;

    public static function deleteAllData()
    {
        $references = Reference::all();
        foreach($references as $reference)
            $reference->delete();
    }

}