<?php

namespace App\Http\Controllers\Proxy\Yw;

use Illuminate\Http\Request;

class QuickappOrderController extends YwController
{
    /**
     * @param Request $request
     * @return mixed
     * 获取充值记录
     */
    public function select(Request $request){
        $req = $request->all();
        $res = $this->sdk->getOrders($req);
        return $this->success($res);
    }
}
