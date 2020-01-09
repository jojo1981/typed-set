<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Test;

use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\DifferenceResult;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Handler\GlobalHandler;
use Jojo1981\TypedSet\Set;
use Jojo1981\TypedSet\TestSuite\Fixture\StringHandler;
use Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @package Jojo1981\TypedSet\TestSuite\Test
 */
class SetTest extends TestCase
{
    /**
     * @runInSeparateProcess
     *
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testWithDateTimeObjects(): void
    {
        $date1 = new \DateTime('tomorrow');
        $date2 = clone $date1;
        $date3 = new \DateTime('now');

        $set1 = (new Set(\DateTimeInterface::class, [$date1, $date2, $date3, $date1]));
        $this->assertEquals(3, $set1->count());
        $this->assertEquals([$date1, $date2, $date3], $set1->toArray());

        GlobalHandler::getInstance()->addDefaultHandlers();

        $set2 = (new Set(\DateTimeInterface::class, [$date1, $date2, $date3, $date1]));
        $this->assertEquals(2, $set2->count());
        $this->assertEquals([$date1, $date3], $set2->toArray());
    }

    /**
     * @runInSeparateProcess
     *
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testWithCustomHandler(): void
    {
        $set1 = new Set('string', ['text1', 'text2']);
        $this->assertEquals(2, $set1->count());

        GlobalHandler::getInstance()->registerHandler(new StringHandler());

        $set2 = new Set('string', ['text1', 'text2']);
        $this->assertEquals(1, $set2->count());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getElementsWithDuplicates
     *
     * @param string $type
     * @param array $elements
     * @param array $expectedArray
     * @return void
     *@throws SetException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testIgnoringDuplicates(string $type, array $elements, array $expectedArray): void
    {
        $this->assertEquals($expectedArray, (new Set($type, $elements))->toArray());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getInvalidTypes()
     *
     * @param string $invalidType
     * @throws SetException
     * @return void
     */
    public function testConstructWithInvalidTypeShouldThrowCollectionException(string $invalidType): void
    {
        $this->expectExceptionObject(new SetException(
            'Given type: `' . $invalidType . '` is not a valid primitive type and also not an existing class'
        ));
        new Set($invalidType);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getPrimitiveTypeWithInvalidData
     *
     * @param string $type
     * @param mixed[] $invalidData
     * @param string $message
     * @throws SetException
     * @return void
     */
    public function testConstructWithValidPrimitiveTypeButInvalidElementShouldThrowCollectionException(
        string $type,
        array $invalidData,
        string $message
    ): void
    {
        $this->expectExceptionObject(new SetException($message));
        new Set($type, $invalidData);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getClassNameTypeWithInvalidData
     *
     * @param string $type
     * @param mixed[] $invalidData
     * @param string $message
     * @throws SetException
     * @return void
     */
    public function testConstructWithValidClassNameTypeButInvalidElementShouldThrowCollectionException(
        string $type,
        array $invalidData,
        string $message
    ): void
    {
        $this->expectExceptionObject(new SetException($message));
        new Set($type, $invalidData);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getExceptionTestData
     *
     * @param string $type
     * @param mixed $element
     * @param \Exception $expectedException
     * @throws SetException
     * @throws HandlerException
     * @return void
     */
    public function testAddWithInvalidElement(string $type, $element, \Exception $expectedException): void
    {
        $this->expectExceptionObject($expectedException);
        (new Set($type))->add($element);
    }

    /**
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testAddWithNewElement(): void
    {
        $set = new Set('string', ['text1']);
        $this->assertCount(1, $set);
        $set->add('text2');
        $this->assertCount(2, $set);
    }

    /**
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testAddWithExistingElement(): void
    {
        $set = new Set('string', ['text1']);
        $this->assertCount(1, $set);
        $set->add('text1');
        $this->assertCount(1, $set);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getExceptionTestData
     *
     * @param string $type
     * @param mixed $element
     * @param \Exception $expectedException
     * @throws HandlerException
     * @throws SetException
     * @return void
     */
    public function testAddAllWithInvalidElement(string $type, $element, \Exception $expectedException): void
    {
        $this->expectExceptionObject($expectedException);
        (new Set($type))->addAll([$element]);
    }

    /**
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testAddAllWithOnlyNewElements(): void
    {
        $set = new Set('string', ['text1']);
        $this->assertCount(1, $set);
        $set->addAll(['text2', 'text3']);
        $this->assertCount(3, $set);
    }

    /**
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testAddAllWithSomeExistingElements(): void
    {
        $set = new Set('string', ['text1', 'text4']);
        $this->assertCount(2, $set);
        $set->addAll(['text2', 'text3', 'text5', 'text4']);
        $this->assertCount(5, $set);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getExceptionTestData
     *
     * @param string $type
     * @param mixed $element
     * @param \Exception $expectedException
     * @throws SetException
     * @throws HandlerException
     * @return void
     */
    public function testContainsWithInvalidElement(string $type, $element, \Exception $expectedException): void
    {
        $this->expectExceptionObject($expectedException);
        (new Set($type))->contains($element);
    }

    /**
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testContainsWithNotExistingElement(): void
    {
        $existingValue = new \stdClass();
        $notExistingValue = new TestEntityBase();
        $set = (new Set('object', [$existingValue]));
        $this->assertFalse($set->contains($notExistingValue));
    }

    /**
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws HandlerException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testContainsWithExistingElement(): void
    {
        $existingValue = new \stdClass();
        $set = (new Set('object', [$existingValue]));
        $this->assertTrue($set->contains($existingValue));
    }

    /**
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testRemove(): void
    {
        $callable1 = static function () {};
        $callable2 = static function () {};

        $set = (new Set('callable', [$callable1, $callable2, $callable1]));
        $this->assertEquals(2, $set->count());
        $this->assertTrue($set->contains($callable2));
        $set->remove($callable2);
        $this->assertFalse($set->contains($callable2));
        $this->assertEquals(1, $set->count());
    }

    /**
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testClear(): void
    {
        $set = (new Set('string', ['text']));
        $this->assertFalse($set->isEmpty());
        $set->clear();
        $this->assertTrue($set->isEmpty());
    }

    /**
     * @throws SetException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @return void
     */
    public function testIsEmpty(): void
    {
        $this->assertTrue((new Set('string'))->isEmpty());
        $this->assertFalse((new Set('string', ['text']))->isEmpty());
    }

    /**
     * @throws SetException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @return void
     */
    public function testIsNonEmpty(): void
    {
        $this->assertTrue((new Set('string', ['text']))->isNonEmpty());
        $this->assertFalse((new Set('string'))->isNonEmpty());
    }

    /**
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testToArray(): void
    {
        $this->assertEquals([], (new Set('string'))->toArray());
        $this->assertEquals(['text'], (new Set('string', ['text']))->toArray());
        $this->assertEquals(['text1', 'text2'], (new Set('string', ['text1', 'text2']))->toArray());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getTypesTestData
     *
     * @param string $type
     * @param string $expectedType
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testGetType(string $type, string $expectedType): void
    {
        $this->assertEquals($expectedType, (new Set($type))->getType());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getIsEqualTypeTestData
     *
     * @param string $type
     * @param TypeInterface $otherType
     * @param bool $expectedResult
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testIsEqualType(string $type, TypeInterface $otherType, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, (new Set($type))->isEqualType($otherType));
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getIsEqualTestData
     *
     * @param Set $setA
     * @param Set $setB
     * @param bool $expectedResult
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testIsEqual(Set $setA, Set $setB, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, $setA->isEqual($setB));
    }

    /**
     * @throws SetException
     * @return void
     */
    public function testCompareWithInCompatible(): void
    {
        $this->expectExceptionObject(new SetException('Can not compare 2 sets of different types'));
        (new Set('string'))->compare(new Set('int'));
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getCompareTestData
     *
     * @param Set $setA
     * @param Set $setB
     * @param DifferenceResult $expectedResult
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testCompare(Set $setA, Set $setB, DifferenceResult $expectedResult): void
    {
        $this->assertEquals($expectedResult, $setA->compare($setB));
    }

    /**
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testCount(): void
    {
        $this->assertCount(0, new Set('string'));
        $this->assertCount(1, new Set('int', [1]));
        $this->assertCount(1, new Set('int', [1, 1]));
        $this->assertCount(2, new Set('int', [1, 2]));
        $this->assertCount(3, new Set('int', [1, 2, 3]));
    }

    /**
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testGetIterator(): void
    {
        $this->assertInstanceOf(\Traversable::class, (new Set('int', [1, 2, 3]))->getIterator());
        $expectedValues = [1, 2, 3];
        foreach (new Set('int', [1, 2, 3]) as $index => $item) {
            $this->assertEquals($expectedValues[$index], $item);
        }
    }

    /**
     * @throws SetException
     * @return void
     */
    public function testCreateFromElementsWithEmptyElements(): void
    {
        $this->expectExceptionObject(SetException::emptyElementsCanNotDetermineType());
        Set::createFromElements([]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @return void
     */
    public function testCreateFromElements(): void
    {
        $set = Set::createFromElements([1, 2, 3]);
        $this->assertEquals('int', $set->getType());
        $this->assertCount(3, $set);
        $this->assertEquals([1,2,3], $set->toArray());
    }
}
