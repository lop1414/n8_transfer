<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
// 后台
$router->group([
    'prefix' => 'admin',
    'middleware' => ['center_menu_auth', 'admin_request_log', 'access_control_allow_origin']
], function () use ($router) {
    // config
    $router->group(['prefix' => 'config'], function () use ($router) {
        $router->post('get', 'Admin\ConfigController@get');
        $router->post('save', 'Admin\ConfigController@save');
    });
});
