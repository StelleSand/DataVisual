<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/test', function () {
    return view('welcome');
});
Route::get('/','ChartController@testDiagram');
Route::post('/','ChartController@testDiagram');
Route::post('realtime', 'ChartController@ajaxRealTime');
Route::post('referenceSetting', 'ChartController@ajaxReferenceSetting');

Route::get('import', 'ExcelImportController@importRecord');
Route::get('unit', 'UnitController@getUnit');
Route::post('addUnit', 'UnitController@postAddUnit');
Route::get('deleteUnit', 'UnitController@getDeleteUnit');

Route::get('line', 'LineController@getLine');
Route::post('addLine', 'LineController@postAddLine');
Route::get('deleteLine', 'LineController@getDeleteLine');

Route::get('chart', 'ChartController@getChart');
Route::post('addChart', 'ChartController@postAddChart');
Route::post('deleteChart', 'ChartController@getDeleteChart');
Route::get('chartAddLine', function(){
    return view('chartAddLine');
});
Route::post('chartAddLine', 'ChartController@postChartAddLine');
Route::get('chartDeleteLine', function(){
    return view('chartDeleteLine');
});
Route::post('chartDeleteLine', 'ChartController@postChartDeleteLine');
