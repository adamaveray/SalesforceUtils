<?php
declare(strict_types=1);

namespace AdamAveray\SalesforceUtils\Tests\Queries;

use AdamAveray\SalesforceUtils\Queries\SafeString;

/**
 * @coversDefaultClass \AdamAveray\SalesforceUtils\Queries\SafeString
 */
class SafeStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__toString
     * @covers ::<!public>
     */
    public function testValuePreservation(): void
    {
        $original = 'Test Value';
        $object = new SafeString($original);

        self::assertEquals(
            $original,
            (string) $object,
            'The original string should be returned when casting to string',
        );
    }

    /**
     * @covers ::escape
     * @covers ::<!public>
     * @depends      testValuePreservation
     * @dataProvider escapeDataProvider
     */
    public function testEscape(
        string $expected,
        $value,
        $isLike = null,
        $quote = null
    ): void {
        $object = SafeString::escape($value, $isLike ?? false, $quote ?? true);
        $output = (string) $object;

        self::assertEquals(
            $expected,
            $output,
            'Values should be escaped correctly',
        );
    }

    public function escapeDataProvider(): iterable
    {
        yield 'Null' => [SafeString::VALUE_NULL, null];
        yield 'True' => [SafeString::VALUE_TRUE, true];
        yield 'False' => [SafeString::VALUE_FALSE, false];
        yield 'Integers' => ['12345', 12345];
        yield 'Floats' => ['12345.6789', 12345.6789];

        yield 'Dates' => [
            '2000-01-01T12:00:00+00:00',
            new \DateTimeImmutable(
                '2000-01-01 12:00:00',
                new \DateTimeZone('UTC'),
            ),
        ];

        yield 'Regular Strings' => [
            SafeString::QUOTE_OPEN . 'test string' . SafeString::QUOTE_CLOSE,
            'test string',
        ];

        yield 'Escaped Strings' => [
            SafeString::QUOTE_OPEN .
            't\\\'e\\nst%_ st\\tring' .
            SafeString::QUOTE_CLOSE,
            't\'e' . "\n" . 'st%_ st' . "\t" . 'ring',
        ];

        yield 'Like Escaped Strings' => [
            SafeString::QUOTE_OPEN .
            't\\\'e\\nst\\%\\_ st\\tring' .
            SafeString::QUOTE_CLOSE,
            't\'e' . "\n" . 'st%_ st' . "\t" . 'ring',
            true,
            true,
        ];

        yield 'Non-Quoted Regular Strings' => [
            'test string',
            'test string',
            false,
            false,
        ];

        yield 'Non-Quoted Escaped Strings' => [
            't\\\'e\\nst st\\tring',
            't\'e' . "\n" . 'st st' . "\t" . 'ring',
            false,
            false,
        ];

        yield 'Non-Quoted Like Escaped Strings' => [
            't\\\'e\\nst\\%\\_ st\\tring',
            't\'e' . "\n" . 'st%_ st' . "\t" . 'ring',
            true,
            false,
        ];
    }

    /**
     * @covers ::escape
     * @covers ::<!public>
     * @depends      testValuePreservation
     * @dataProvider escapeArrayDataProvider
     */
    public function testEscapeArray(
        string $expected,
        $value,
        $isLike = null,
        $quote = null
    ): void {
        $object = SafeString::escapeArray(
            $value,
            $isLike ?? false,
            $quote ?? true,
        );
        $output = (string) $object;

        self::assertEquals(
            $expected,
            $output,
            'Values should be escaped correctly',
        );
    }

    public function escapeArrayDataProvider(): iterable
    {
        yield 'Empty' => ['', []];

        yield 'Single string' => ['\'item\'', ['item']];

        yield 'Multiple strings' => [
            '\'item one\', \'item two\', \'item three\'',
            ['item one', 'item two', 'item three'],
        ];

        yield 'Mixed values' => [
            '\'string\', ' .
            SafeString::VALUE_TRUE .
            ', ' .
            SafeString::VALUE_FALSE .
            ', ' .
            SafeString::VALUE_NULL .
            ', 123',
            ['string', true, false, null, 123],
        ];
    }
}
