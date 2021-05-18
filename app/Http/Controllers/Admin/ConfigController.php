<?php


namespace App\Http\Controllers\Admin;



use App\Models\ConfigModel;
use Illuminate\Http\Request;


class ConfigController extends BaseController
{




    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new ConfigModel();


        parent::__construct();
    }



    public function getPrepare()
    {
        $this->curdService->getQueryAfter(function(){
            $data = [];
            foreach ($this->curdService->responseData as $item){
                $data[$item['group']][$item['k']] = $item['v'];
            }
            $this->curdService->responseData = $data;
        });
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     */
    public function save(Request $request){
        $requestData = $request->all();

        foreach ($requestData['data'] as $group => $item){
            foreach ($item as $k => $v){
                $config = (new ConfigModel())
                    ->where('group',$group)
                    ->where('k',$k)
                    ->first();
                if(empty($config)){
                    $config = new ConfigModel();
                    $config->group = $group;
                    $config->k = $k;
                }
                $config->v = $v;
                $config->save();
            }

        }
        return $this->success([]);
    }




}
