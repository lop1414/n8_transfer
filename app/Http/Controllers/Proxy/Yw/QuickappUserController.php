<?php

namespace App\Http\Controllers\Proxy\Yw;

use Illuminate\Http\Request;

class QuickappUserController extends YwController
{
    /**
     * @param Request $request
     * @return mixed
     * 获取用户信息
     */
    public function select(Request $request){
        $req = $request->all();
        $res = $this->sdk->getUser($req);
        return $this->success($res);
    }
}
