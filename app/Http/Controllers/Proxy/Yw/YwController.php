<?php
namespace App\Http\Controllers\Proxy\Yw;

use App\Common\Controllers\Front\FrontController;
use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\UnionApiService;
use App\Sdks\Yw\YwSdk;
use Illuminate\Http\Request;

class YwController extends FrontController
{
    /**
     * @var
     */
    protected $sdk;

    /**
     * constructor.
     */
    public function __construct(){
        parent::__construct();

        $product = Functions::getGlobalData('product');
        $unionApiService = new UnionApiService();
        $cpAccount = $unionApiService->apiReadCpAccount($product['cp_account_id']);

        $this->sdk = new YwSdk($product['cp_product_alias'], $cpAccount['account'],$cpAccount['cp_secret']);
    }
}
