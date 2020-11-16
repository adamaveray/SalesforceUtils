<?php

namespace AdamAveray\SalesforceUtils\Tests\Client;

use AdamAveray\SalesforceUtils\Client\SoapClientFactory;
use Phpforce\SoapClient\Soap\SoapClient;

/**
 * @coversDefaultClass \AdamAveray\SalesforceUtils\Client\SoapClientFactory
 */
class SoapClientFactoryTest extends \PHPUnit\Framework\TestCase
{
    private const DUMMY_WSDL_PATH =
        __DIR__ .
        '/../../../../../vendor/phpforce/soap-client/tests/Phpforce/SoapClient/Tests/Fixtures/sandbox.enterprise.wsdl.xml';

    /**
     * @covers ::factory
     */
    public function testFactory(): void
    {
        $factory = new SoapClientFactory();
        $result = $factory->factory(self::DUMMY_WSDL_PATH, []);

        self::assertInstanceOf(
            SoapClient::class,
            $result,
            'A SoapClient instance should be generated',
        );

        self::assertEquals(
            1,
            $result->trace,
            'The trace property should be set',
        );
    }
}
