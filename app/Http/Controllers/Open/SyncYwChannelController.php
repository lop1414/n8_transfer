<?php


namespace App\Http\Controllers\Open;


use App\Common\Enums\ProductTypeEnums;


use App\Common\Helpers\Functions;
use App\Services\ProductService;
use Illuminate\Http\Request;

class SyncYwChannelController extends BaseController
{


    public function sync(Request $request){
        $reqData = $request->all();
        $productId = $reqData['product_id'];
        $startDate = $reqData['start_date'];
        $endDate = $reqData['end_date'];
        Functions::checkDateRange($startDate,$endDate);
        $productList = (new ProductService())->get(['id' => $productId]);
        $service = null;
        foreach ($productList as $product){

            if($product['type'] == ProductTypeEnums::KYY){
                $service = new \App\Services\YwKyy\PushChannelService();
            }elseif($product['type'] == ProductTypeEnums::H5){
                $service = new \App\Services\YwH5\PushChannelService();
            }


            $date = $startDate;
            while($date <= $endDate){
                $tmpEndDate = date('Y-m-d',  strtotime('+1 day',strtotime($date)));


                $service->setDateRange($date,$tmpEndDate)->run($product['id']);

                $date = $tmpEndDate;
            }

        }
        return $this->success([]);
    }

}
