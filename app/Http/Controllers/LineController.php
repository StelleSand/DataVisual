<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/12/16
 * Time: 14:25
 */

namespace App\Http\Controllers;


use Request;
use App\User;
use App\Line;

class LineController extends Controller {

    protected $user;

    public function __construct()
    {
        $this->user = User::find(1);
    }

    public function getLine()
    {
        return view('lineAdd');
    }

    public function postAddLine()
    {
        $inputs = Request::all();
        $lineInfo = array('name' => $inputs['name'], 'formula' => $inputs['formula'], 'create_user' => $this->user->id,'detail'=>$inputs['detail']);
        Line::create($lineInfo);
        return view('lineAdd') ;
    }

    public function getDeleteLine()
    {
        $line = Line::find(Request::input('id'));
        if($line->create_user == $this->user->id)
            $line->delete();
        return view('lineAdd');
    }

}