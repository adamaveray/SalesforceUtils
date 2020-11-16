<?php
declare(strict_types=1);

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

    public function next(): void
    {
        $this->i++;
    }

    public function key(): int
    {
        return $this->i;
    }

    public function valid(): bool
    {
        return $this->current() !== null;
    }

    public function rewind(): void
    {
        $this->i = 0;
    }

    public function count(): int
    {
        return count($this->values);
    }

    protected function getObjectAt($key)
    {
        return $this->values[$key] ?? null;
    }
}
