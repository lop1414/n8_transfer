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



// 开放接口
$router->group([
    // 路由前缀
    'prefix' => 'open',
    // 路由中间件
    'middleware' => ['access_control_allow_origin']
], function () use ($router) {


    $router->group(['prefix' => 'yw_kyy'], function () use ($router) {
        $router->get('user', 'Open\YwKyy\UserController@distribute');

        $router->group(['prefix' => 'match'], function () use ($router) {
            $router->get('ocean', 'Open\YwKyy\MatchDataController@ocean');
            $router->get('kuaishou', 'Open\YwKyy\MatchDataController@kuaishou');
        });
    });

    $router->group(['prefix' => 'yw_h5'], function () use ($router) {
        $router->get('user', 'Open\YwH5\UserController@reg');
    });

    $router->group(['prefix' => 'match'], function () use ($router) {
        $router->get('second_version', 'Open\MatchDataController@secondVersion');
    });

    $router->get('sync_yw_channel', 'Open\SyncYwChannelController@sync');

});


// 代理
$router->group([
    'prefix' => 'proxy',
    'middleware' => ['proxy_api_auth', 'access_control_allow_origin']
], function () use ($router) {
    // 阅文
    $router->group(['prefix' => 'yw'], function () use ($router) {
        $router->get('quickapp_user/select', 'Proxy\Yw\QuickappUserController@select');
        $router->get('quickapp_order/select', 'Proxy\Yw\QuickappOrderController@select');
    });
});
