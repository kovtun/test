<?php

namespace App\Tests\Action\Traits;

use App\Tests\Action\ParamWrapper;

/**
 * Class DataParamWrapperProcessorTrait
 */
trait DataParamWrapperProcessorTrait
{
    /**
     * @param array $data
     */
    protected function processDataParamWrapper(array &$data)
    {
        foreach ($data as &$dataItem) {
            if ($dataItem instanceof ParamWrapper) {
                $this->processParamWrapper($dataItem);
            } else if (is_array($dataItem)) {
                $this->processDataParamWrapper($dataItem);
            }
        }
    }
}
