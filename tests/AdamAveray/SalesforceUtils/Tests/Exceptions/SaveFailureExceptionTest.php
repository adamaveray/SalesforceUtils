<?php
declare(strict_types=1);

namespace AdamAveray\SalesforceUtils\Tests\Data;

use AdamAveray\SalesforceUtils\Exceptions\SaveFailureException;
use Phpforce\SoapClient\Result\SaveResult;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \AdamAveray\SalesforceUtils\Exceptions\SaveFailureException
 */
class SaveFailureExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getResult
     */
    public function testException(): void
    {
        $recordId = '12345';
        $message = 'Save failure: ' . $recordId;
        $previous = new \Exception('Test');

        // Mock SaveResult
        /** @var MockObject|SaveResult $mock */
        $mock = $this->getMockBuilder(SaveResult::class)
            ->setMethods(['getId'])
            ->getMock();
        $mock
            ->expects(self::once())
            ->method('getId')
            ->willReturn($recordId);

        $exception = new SaveFailureException($mock, $previous);
        self::assertEquals(
            $message,
            $exception->getMessage(),
            'The correct message should be generated',
        );
        self::assertSame(
            $mock,
            $exception->getResult(),
            'The save result should be stored',
        );
        self::assertSame(
            $previous,
            $exception->getPrevious(),
            'Previous exceptions should be stored',
        );
    }
}
