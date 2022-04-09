<?php

namespace App\Services\UserAction\Reg;


use App\Services\UserAction\AddShortcut\TwKyyAddShortcutService;
use App\Services\UserAction\UserActionAbstract;
use App\Traits\Cp\TwApp;
use App\Traits\ProductType\App;
use App\Traits\UserAction\Reg;


class TwAppRegService extends UserActionAbstract
{

    use App;
    use TwApp;
    use Reg;

    protected $twKyyAddShortcutService;

    public function __construct()
    {
        $this->twKyyAddShortcutService = new TwKyyAddShortcutService();
    }


    public function get(array $product, string $startTime,string $endTime): array
    {

        $sdk = $this->getSdk($product);

        $tmp =  $sdk->getUsers([
            'start_date'  => $startTime,
            'end_date'  => $endTime,
        ]);
        $data = [];
        foreach ($tmp['data'] as $item){
            $data[] = [
                'open_id'       => $item['user_id'],
                'action_time'   => $item['reg_time'],
                'type'          => $this->getType(),
                'cp_channel_id' => $item['channel_id'],
                'request_id'    => '',
                'ip'            => $item['ip'],
                'action_id'     => $item['user_id'],
                'extend'        => $this->filterExtendInfo($item),
                'data'          => $item
            ];
        }


        return $data;
    }


}
