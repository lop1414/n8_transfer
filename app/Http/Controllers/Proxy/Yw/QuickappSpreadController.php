<?php

namespace App\Http\Controllers\Proxy\Yw;

use Illuminate\Http\Request;

class QuickappSpreadController extends YwController
{
    /**
     * @param Request $request
     * @return mixed
     * 获取渠道列表
     */
    public function select(Request $request){
        $req = $request->all();
        $res = $this->sdk->getSpreads($req);
        return $this->success($res);
    }
}
