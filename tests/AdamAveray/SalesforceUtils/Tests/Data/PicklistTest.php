<?php
declare(strict_types=1);

namespace AdamAveray\SalesforceUtils\Tests\Data;

use AdamAveray\SalesforceUtils\Data\Picklist;

/**
 * @coversDefaultClass \AdamAveray\SalesforceUtils\Data\Picklist
 */
class PicklistTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getValues
     * @covers ::<!public>
     */
    public function testStoreValues(): void
    {
        $values = ['a', 'b', 'c'];
        $object = new Picklist($values);
        self::assertEquals(
            $values,
            $object->getValues(),
            'Constructor values should be provided by ->getValues()',
        );
    }

    /**
     * @depends testStoreValues
     * @covers ::contains
     * @covers ::<!public>
     */
    public function testContains(): void
    {
        $values = ['a', 'b', 'c'];
        $object = new Picklist($values);
        foreach ($values as $value) {
            self::assertTrue(
                $object->contains($value),
                'Items in picklist should be marked as contained',
            );
        }

        self::assertFalse(
            $object->contains('d'),
            'Items not in picklist should be marked as not contained',
        );
    }

    /**
     * @depends testStoreValues
     * @covers ::add
     * @covers ::remove
     * @covers ::<!public>
     */
    public function testManipulateValues(): void
    {
        $values = ['a', 'b', 'c'];
        $object = new Picklist($values);

        $object->add('d');
        self::assertEquals(
            ['a', 'b', 'c', 'd'],
            $object->getValues(),
            'Added values should be stored',
        );

        $object->remove('d');
        self::assertEquals(
            ['a', 'b', 'c'],
            $object->getValues(),
            'Removed values should no longer be stored',
        );

        $object->add('b');
        $this->assertEquals(
            ['a', 'b', 'c'],
            $object->getValues(),
            'Duplicate values should be ignored',
        );

        $object->remove('d');
        self::assertEquals(
            ['a', 'b', 'c'],
            $object->getValues(),
            'Missing values should be ignored if attempted to be removed',
        );
    }

    /**
     * @covers ::add
     * @covers ::remove
     */
    public function testChainable()
    {
        $object = new Picklist();

        $self = $object->add('value');
        $this->assertSame($object, $self, 'Add should be chainable');

        $self = $object->remove('value');
        $this->assertSame($object, $self, 'Remove should be chainable');
    }

    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        $input = ['a', 'b', 'c'];
        $expected = 'a' . Picklist::SEPARATOR . 'b' . Picklist::SEPARATOR . 'c';

        $object = new Picklist($input);
        self::assertEquals(
            $expected,
            (string) $object,
            'Picklists should be serialised to the correct format',
        );
    }

    /**
     * @covers ::fromString
     * @dataProvider fromStringDataProvider
     */
    public function testFromString($expected, $input): void
    {
        $object = Picklist::fromString($input);
        self::assertEquals(
            $expected,
            $object->getValues(),
            'Picklist strings should be deserialised to values',
        );
    }

    public function fromStringDataProvider(): iterable
    {
        yield 'No whitespace' => [
            ['a', 'b', 'c'],
            'a' . Picklist::SEPARATOR . 'b' . Picklist::SEPARATOR . 'c',
        ];

        yield 'With whitespace' => [
            ['a', 'b', 'c'],
            'a    ' .
            "\n\n" .
            Picklist::SEPARATOR .
            '    b    ' .
            Picklist::SEPARATOR .
            '    ' .
            "\n" .
            'c',
        ];
    }
}
