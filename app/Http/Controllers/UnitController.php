<?php
/**
 * Created by PhpStorm.
 * User: v5
 * Date: 2015/12/16
 * Time: 12:45
 */

namespace App\Http\Controllers;


use App\Unit;
use App\User;
use Request;

class UnitController extends Controller {

    protected $user ;

    public function __construct()
    {
        $this->user = User::find(1);
    }

    public function getUnit()
    {
        return view('unitAdd');
    }

    public function postAddUnit()
    {
        $inputs = Request::all();
        $unitInfo = array('name' => $inputs['name'], 'formula' => $inputs['formula'], 'create_user' => $this->user->id,'detail'=>$inputs['detail']);
        Unit::create($unitInfo);
        return ;
    }

    public function getDeleteUnit()
    {
        $unit = Unit::find(Request::input('id'));
        if($unit->create_user == $this->user->id)
        $unit->delete();
        return view('unitAdd');
    }

}