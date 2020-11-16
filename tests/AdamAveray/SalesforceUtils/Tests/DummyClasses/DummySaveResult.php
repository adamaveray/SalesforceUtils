<?php

namespace AdamAveray\SalesforceUtils\Tests\DummyClasses;

use Phpforce\SoapClient\Result\SaveResult;

class DummySaveResult extends SaveResult
{
    public function __construct(
        $id,
        $success = true,
        $errors = false,
        $param = null
    ) {
        $this->id = $id;
        $this->success = $success;
        $this->errors = $errors;
        $this->param = $param;
    }
}
