<?php


namespace App\Http\Controllers\Admin;



use App\Models\ConfigModel;
use Illuminate\Http\Request;


class ConfigController extends BaseController
{


    /**
     * @var string
     * 默认排序字段
     */
    protected $defaultOrderBy = 'group';


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

        $data = [];
        foreach ($requestData['data'] as $group => $item){
            foreach ($item as $k => $v){
                $data[] = [
                    'group' => $group,
                    'k'     => $k,
                    'v'     => json_encode($v)
                ];
            }

        }
        (new ConfigModel())->batchInsertOrUpdate($data);
        return $this->success([]);
    }




}
