<?php

namespace AdamAveray\SalesforceUtils\Tests\DummyClasses;

use Phpforce\SoapClient\Result\RecordIterator;

class DummyRecordIterator extends RecordIterator
{
    private $i = 0;
    private $values;

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function current()
    {
        return $this->values[$this->i] ?? null;
    }

    public function next()
    {
        $this->i++;
    }

    public function key()
    {
        return $this->i;
    }

    public function valid()
    {
        return $this->current() !== null;
    }

    public function rewind()
    {
        $this->i = 0;
    }

    public function count()
    {
        return count($this->values);
    }

    protected function getObjectAt($key)
    {
        return $this->values[$key] ?? null;
    }
}
