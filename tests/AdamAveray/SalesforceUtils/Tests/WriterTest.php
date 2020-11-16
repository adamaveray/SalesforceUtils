<?php

namespace AdamAveray\SalesforceUtils\Tests\Queries;

use AdamAveray\SalesforceUtils\Tests\DummyClasses\DummySaveResult;
use AdamAveray\SalesforceUtils\Writer;
use AdamAveray\SalesforceUtils\Client\ClientInterface;
use Phpforce\SoapClient\Result\SObject;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \AdamAveray\SalesforceUtils\Writer
 */
class WriterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param ClientInterface|null $client
     * @return Writer
     */
    private function getWriter(ClientInterface $client = null): Writer
    {
        return new Writer($client ?? $this->getClient());
    }

    /**
     * @param array|null $methods
     * @return MockObject|ClientInterface
     */
    private function getClient(array $methods = null)
    {
        $builder = $this->getMockBuilder(ClientInterface::class);
        if ($methods !== null) {
            $builder->setMethods($methods);
        }
        return $builder->getMockForAbstractClass();
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @covers ::<!public>
     */
    public function testCreate()
    {
        $type = 'Test';
        $id = '12345';

        $values = [
            'testValue' => 'one',
            'otherValue' => 'two',
        ];
        $object = new \stdClass();
        $object->testValue = $values['testValue'];
        $object->otherValue = $values['otherValue'];

        $saveResult = new DummySaveResult($id);

        $client = $this->getClient(['createOne']);
        $client
            ->expects($this->exactly(3))
            ->method('createOne')
            ->with($object, $type)
            ->willReturn($saveResult);

        $writer = $this->getWriter($client);

        // Values
        $result = $writer->create($type, $values);
        $this->assertEquals(
            $saveResult,
            $result,
            'The created object ID should be returned in the SaveResult',
        );

        // Pre-made object
        $result = $writer->create($type, $object);
        $this->assertEquals(
            $saveResult,
            $result,
            'The created object ID should be returned in the SaveResult',
        );

        // Set record type
        $recordTypeId = '98765';
        $originalObject = clone $object;
        $object->{Writer::FIELD_RECORD_TYPE_ID} = $recordTypeId;
        $result = $writer->create($type, $originalObject, $recordTypeId);
        $this->assertEquals(
            $saveResult,
            $result,
            'The provided record type should be assigned on the create object',
        );
    }

    /**
     * @covers ::__construct
     * @covers ::create
     * @covers ::<!public>
     * @expectedException \AdamAveray\SalesforceUtils\Exceptions\SaveFailureException
     */
    public function testCreateFailure()
    {
        $saveResult = new DummySaveResult(null, false);

        $client = $this->getClient(['createOne']);
        $client
            ->expects($this->once())
            ->method('createOne')
            ->willReturn($saveResult);

        $writer = $this->getWriter($client);
        $writer->create('test', []);
    }

    /**
     * @covers ::__construct
     * @covers ::update
     * @covers ::<!public>
     */
    public function testUpdate()
    {
        $type = 'Test';
        $id = '12345';
        $base = new SObject();
        $base->Id = $id;

        $values = [
            'testValue' => 'one',
            'otherValue' => 'two',
        ];
        $object = new SObject();
        $object->Id = $id;
        $object->testValue = $values['testValue'];
        $object->otherValue = $values['otherValue'];

        $saveResult = new DummySaveResult($id);

        $client = $this->getClient(['updateOne']);
        $client
            ->expects($this->exactly(2))
            ->method('updateOne')
            ->with($object, $type)
            ->willReturn($saveResult);

        $writer = $this->getWriter($client);

        // Update from objecrt
        $result = $writer->update($type, $base, $values);
        $this->assertEquals(
            $saveResult,
            $result,
            'The updated object ID should be returned in the SaveResult',
        );

        // Update from ID
        $result = $writer->update($type, $id, $values);
        $this->assertEquals(
            $saveResult,
            $result,
            'The updated object ID should be returned in the SaveResult',
        );
    }

    /**
     * @covers ::__construct
     * @covers ::update
     * @covers ::<!public>
     * @expectedException \AdamAveray\SalesforceUtils\Exceptions\SaveFailureException
     */
    public function testUpdateFailure()
    {
        $saveResult = new DummySaveResult(null, false);

        $client = $this->getClient(['updateOne']);
        $client
            ->expects($this->once())
            ->method('updateOne')
            ->willReturn($saveResult);

        $writer = $this->getWriter($client);
        $writer->update('test', 'id', []);
    }

    /**
     * @covers ::buildSObject
     * @covers ::<!public>
     * @dataProvider buildSObjectDataProvider
     */
    public function testBuildSObject($expected, ?string $id, array $values)
    {
        $writer = $this->getWriter();
        $result = $writer->buildSObject($id, $values);
        $this->assertEquals(
            $expected,
            $result,
            'An SObject with values assigned should be returned',
        );
    }

    public function buildSObjectDataProvider(): array
    {
        $invoke = function (callable $fn) {
            return $fn();
        };
        return [
            'Simple' => [
                $invoke(function () {
                    $obj = new SObject();
                    $obj->Id = '12345';
                    $obj->testOne = 'value one';
                    $obj->testTwo = 'value two';
                    return $obj;
                }),
                '12345',
                [
                    'testOne' => 'value one',
                    'testTwo' => 'value two',
                ],
            ],
            'String Cast' => [
                $invoke(function () {
                    $obj = new SObject();
                    $obj->Id = '12345';
                    $obj->test = 'value';
                    return $obj;
                }),
                '12345',
                [
                    'test' => new class {
                        public function __toString()
                        {
                            return 'value';
                        }
                    },
                ],
            ],
            'No ID' => [
                $invoke(function () {
                    $obj = new SObject();
                    $obj->testOne = 'value one';
                    $obj->testTwo = 'value two';
                    return $obj;
                }),
                null,
                [
                    'testOne' => 'value one',
                    'testTwo' => 'value two',
                ],
            ],
        ];
    }
}
