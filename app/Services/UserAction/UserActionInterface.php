<?php

namespace App\Services\UserAction;

interface UserActionInterface
{

    /**
     * @return string
     * 获取行为类型
     */
    public function getType(): string;

    /**
     * @return string
     * 获取平台类型
     */
    public function getCpType(): string;

    /**
     * @return string
     * 获取产品类型
     */
    public function getProductType(): string;


    /**
     * @param array $product
     * @param string $startTime
     * @param string $endTime
     * @return int|null
     * 获取总数
     */
    public function getTotal(array $product, string $startTime,string $endTime): ?int;


    /**
     * @param array $product
     * @param string $startTime
     * @param string $endTime
     * @return array
     */
    public function get(array $product, string $startTime,string $endTime): array;





}
