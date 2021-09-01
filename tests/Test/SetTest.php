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

use DateTime;
use DateTimeInterface;
use Exception;
use Jojo1981\PhpTypes\TypeInterface;
use Jojo1981\TypedSet\DifferenceResult;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Handler\GlobalHandler;
use Jojo1981\TypedSet\Set;
use Jojo1981\TypedSet\TestSuite\Fixture\StringHandler;
use Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase;
use Jojo1981\TypedSet\TestSuite\Fixture\TestHashableEntity1;
use Jojo1981\TypedSet\TestSuite\Fixture\TestHashableEntity2;
use PHPUnit\Framework\Exception as PHPUnitFrameworkException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use stdClass;
use Traversable;
use function substr;

/**
 * @package Jojo1981\TypedSet\TestSuite\Test
 */
final class SetTest extends TestCase
{
    /**
     * @runInSeparateProcess
     *
     * @return void
     * @throws Exception
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testWithDateTimeObjects(): void
    {
        $date1 = new DateTime('tomorrow');
        $date2 = clone $date1;
        $date3 = new DateTime('now');

        $set1 = (new Set(DateTimeInterface::class, [$date1, $date2, $date3, $date1]));
        self::assertEquals(3, $set1->count());
        self::assertEquals([$date1, $date2, $date3], $set1->toArray());

        GlobalHandler::getInstance()->addDefaultHandlers();

        $set2 = (new Set(DateTimeInterface::class, [$date1, $date2, $date3, $date1]));
        self::assertEquals(2, $set2->count());
        self::assertEquals([$date1, $date3], $set2->toArray());
    }

    /**
     * @runInSeparateProcess
     *
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testWithCustomHandler(): void
    {
        $set1 = new Set('string', ['text1', 'text2']);
        self::assertEquals(2, $set1->count());

        GlobalHandler::getInstance()->registerHandler(new StringHandler());

        $set2 = new Set('string', ['text1', 'text2']);
        self::assertEquals(1, $set2->count());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getElementsWithDuplicates
     *
     * @param string $type
     * @param array $elements
     * @param array $expectedArray
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testIgnoringDuplicates(string $type, array $elements, array $expectedArray): void
    {
        self::assertEquals($expectedArray, (new Set($type, $elements))->toArray());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getInvalidTypes()
     *
     * @param string $invalidType
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testConstructWithInvalidTypeShouldThrowCollectionException(string $invalidType): void
    {
        $this->expectExceptionObject(SetException::givenTypeIsNotValid($invalidType));
        new Set($invalidType);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getPrimitiveTypeWithInvalidData
     *
     * @param string $type
     * @param array $invalidData
     * @param string $message
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testConstructWithValidPrimitiveTypeButInvalidElementShouldThrowCollectionException(
        string $type,
        array $invalidData,
        string $message
    ): void {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);
        new Set($type, $invalidData);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getClassNameTypeWithInvalidData
     *
     * @param string $type
     * @param array $invalidData
     * @param string $message
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testConstructWithValidClassNameTypeButInvalidElementShouldThrowCollectionException(
        string $type,
        array $invalidData,
        string $message
    ): void {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);
        new Set($type, $invalidData);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getExceptionTestData
     *
     * @param string $type
     * @param mixed $element
     * @param string $message
     * @return void
     * @throws RuntimeException
     * @throws SetException
     * @throws HandlerException
     */
    public function testAddWithInvalidElement(string $type, $element, string $message): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);
        (new Set($type))->add($element);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testAddWithNewElement(): void
    {
        $set = new Set('string', ['text1']);
        self::assertCount(1, $set);
        $set->add('text2');
        self::assertCount(2, $set);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testAddWithExistingElement(): void
    {
        $set = new Set('string', ['text1']);
        self::assertCount(1, $set);
        $set->add('text1');
        self::assertCount(1, $set);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getExceptionTestData
     *
     * @param string $type
     * @param mixed $element
     * @param string $message
     * @return void
     * @throws RuntimeException
     * @throws SetException
     * @throws HandlerException
     */
    public function testAddAllWithInvalidElement(string $type, $element, string $message): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);
        (new Set($type))->addAll([$element]);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testAddAllWithOnlyNewElements(): void
    {
        $set = new Set('string', ['text1']);
        self::assertCount(1, $set);
        $set->addAll(['text2', 'text3']);
        self::assertCount(3, $set);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testAddAllWithSomeExistingElements(): void
    {
        $set = new Set('string', ['text1', 'text4']);
        self::assertCount(2, $set);
        $set->addAll(['text2', 'text3', 'text5', 'text4']);
        self::assertCount(5, $set);
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getExceptionTestData
     *
     * @param string $type
     * @param mixed $element
     * @param string $message
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testContainsWithInvalidElement(string $type, $element, string $message): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);
        (new Set($type))->contains($element);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testContainsWithNotExistingElement(): void
    {
        $existingValue = new stdClass();
        $notExistingValue = new TestEntityBase();
        $set = (new Set('object', [$existingValue]));
        self::assertFalse($set->contains($notExistingValue));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testContainsWithExistingElement(): void
    {
        $existingValue = new stdClass();
        $set = (new Set('object', [$existingValue]));
        self::assertTrue($set->contains($existingValue));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testRemove(): void
    {
        $callable1 = static function () {
        };
        $callable2 = static function () {
        };

        $set = (new Set('callable', [$callable1, $callable2, $callable1]));
        self::assertEquals(2, $set->count());
        self::assertTrue($set->contains($callable2));
        $set->remove($callable2);
        self::assertFalse($set->contains($callable2));
        self::assertEquals(1, $set->count());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testClear(): void
    {
        $set = (new Set('string', ['text']));
        self::assertFalse($set->isEmpty());
        $set->clear();
        self::assertTrue($set->isEmpty());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testIsEmpty(): void
    {
        self::assertTrue((new Set('string'))->isEmpty());
        self::assertFalse((new Set('string', ['text']))->isEmpty());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testIsNonEmpty(): void
    {
        self::assertTrue((new Set('string', ['text']))->isNonEmpty());
        self::assertFalse((new Set('string'))->isNonEmpty());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testToArray(): void
    {
        self::assertEquals([], (new Set('string'))->toArray());
        self::assertEquals(['text'], (new Set('string', ['text']))->toArray());
        self::assertEquals(['text1', 'text2'], (new Set('string', ['text1', 'text2']))->toArray());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getTypesTestData
     *
     * @param string $type
     * @param string $expectedType
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testGetType(string $type, string $expectedType): void
    {
        self::assertEquals($expectedType, (new Set($type))->getType());
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getIsEqualTypeTestData
     *
     * @param string $type
     * @param TypeInterface $otherType
     * @param bool $expectedResult
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testIsEqualType(string $type, TypeInterface $otherType, bool $expectedResult): void
    {
        self::assertEquals($expectedResult, (new Set($type))->isEqualType($otherType));
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getIsEqualTestData
     *
     * @param Set $setA
     * @param Set $setB
     * @param bool $expectedResult
     * @return void
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws ExpectationFailedException
     * @throws HandlerException
     */
    public function testIsEqual(Set $setA, Set $setB, bool $expectedResult): void
    {
        self::assertEquals($expectedResult, $setA->isEqual($setB));
    }

    /**
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testCompareWithInCompatible(): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage('Can not compare 2 sets of different types');
        $this->expectExceptionCode(0);
        (new Set('string'))->compare(new Set('int'));
    }

    /**
     * @dataProvider \Jojo1981\TypedSet\TestSuite\DataProvider\SetDataProvider::getCompareTestData
     *
     * @param Set $setA
     * @param Set $setB
     * @param DifferenceResult $expectedResult
     * @return void
     * @throws SetException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public function testCompare(Set $setA, Set $setB, DifferenceResult $expectedResult): void
    {
        self::assertEquals($expectedResult, $setA->compare($setB));
    }

    /**
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testMapWithInvalidTypeShouldThrowSetException(): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage('Given type: `invalidType` is not a valid type and also not an existing class');
        $this->expectExceptionCode(0);
        (new Set('string', []))->map(static function () {
        }, 'invalidType');
    }

    /**
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testMapWithoutTypeOnEmptySetShouldThrowSetException(): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage('Type can not be omitted on an empty Set');
        $this->expectExceptionCode(0);
        (new Set('string', []))->map(static function () {});
    }

    /**
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testMapWithoutTypeWithMapperReturningUnsupportedDataSetShouldThrowSetException(): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage('Determined type: `null` is not a valid type and also not an existing class');
        $this->expectExceptionCode(0);
        (new Set('string', ['text1']))->map(static function () {
            return null;
        });
    }

    /**
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testMapWithValidTypeWithMapperReturningViolatingDataDataSetShouldThrowSetException(): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage('Mapper is not returning a correct value. Data is not of type: `int`, but an instance of: `\stdClass`');
        $this->expectExceptionCode(0);
        $mapper = static function () {
            return new stdClass();
        };
        (new Set('string', ['text1']))->map($mapper, 'int');
    }

    /**
     * @return void
     * @throws SetException
     * @throws RuntimeException
     * @throws HandlerException
     */
    public function testMapWithoutTypeWithMapperReturningViolatingDataDataSetShouldThrowSetException(): void
    {
        $this->expectException(SetException::class);
        $this->expectExceptionMessage('Mapper is not returning a correct value. Data is not of type: `int`, but of type: `string`');
        $this->expectExceptionCode(0);
        $isCalled = false;
        $mapper = static function () use (&$isCalled) {
            if (!$isCalled) {
                $isCalled = true;

                return 1;
            }

            return 'text';
        };
        (new Set('string', ['text1', 'text2']))->map($mapper);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testMapWithValidTypeAndEmptySetShouldReturnNewMappedSet(): void
    {
        $set1 = new Set('text');
        self::assertEquals('string', $set1->getType());
        $set2 = $set1->map(static function () {
        }, 'integer');
        self::assertEquals('int', $set2->getType());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testMapWithValidTypeAndNonEmptySetShouldReturnNewMappedSet(): void
    {
        $originalSet = new Set('text', ['text2', 'text1', 'text3']);
        self::assertEquals('string', $originalSet->getType());
        self::assertCount(3, $originalSet);
        self::assertEquals(['text2', 'text1', 'text3'], $originalSet->toArray());

        $mapper = static function (string $text): int {
            return (int) substr($text, -1);
        };

        $newMappedSet = $originalSet->map($mapper, 'integer');
        self::assertEquals('int', $newMappedSet->getType());
        self::assertCount(3, $newMappedSet);
        self::assertEquals([2, 1, 3], $newMappedSet->toArray());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testMapWithoutTypeAndNonEmptySetShouldReturnNewMappedSet(): void
    {
        $originalSet = new Set('text', ['text2', 'text1', 'text3']);
        self::assertEquals('string', $originalSet->getType());
        self::assertCount(3, $originalSet);
        self::assertEquals(['text2', 'text1', 'text3'], $originalSet->toArray());

        $mapper = static function (string $text): int {
            return (int) substr($text, -1);
        };

        $newMappedSet = $originalSet->map($mapper);
        self::assertEquals('int', $newMappedSet->getType());
        self::assertCount(3, $newMappedSet);
        self::assertEquals([2, 1, 3], $newMappedSet->toArray());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testMapWithoutGivenTypeShouldCallMapperWithRightArguments(): void
    {
        $set = new Set(TestHashableEntity2::class, [new TestHashableEntity2('name1'), new TestHashableEntity2('name2')]);

        $expectedPosition = 0;
        $called = false;
        $mapper = static function (TestHashableEntity2 $testHashableEntity2, $actualPosition, $actualId) use (&$expectedPosition, &$called): string {
            $expectedId = $testHashableEntity2->getHash();
            if (!$called) {
                self::assertEquals(0, $actualPosition);
                $called = true;
            } else {
                self::assertEquals($expectedPosition++, $actualPosition);
            }
            self::assertEquals($expectedId, $actualId);

            return $testHashableEntity2->getHash();
        };
        $set->map($mapper);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testMapWithGivenTypeShouldCallMapperWithRightArguments(): void
    {
        $set = new Set(TestHashableEntity2::class, [new TestHashableEntity2('name1'), new TestHashableEntity2('name2')]);

        $expectedPosition = 0;
        $mapper = static function (TestHashableEntity2 $testHashableEntity2, $actualPosition, $actualId) use (&$expectedPosition): string {
            $expectedId = $testHashableEntity2->getHash();
            self::assertEquals($expectedPosition++, $actualPosition);
            self::assertEquals($expectedId, $actualId);

            return $testHashableEntity2->getHash();

        };
        $set->map($mapper, 'string');
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testFilterEmptySet(): void
    {
        $originalSet = new Set('string');
        self::assertCount(0, $originalSet);
        $filteredSet = $originalSet->filter(static function () {
        });
        self::assertCount(0, $filteredSet);
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testFilter(): void
    {
        $originalSet = new Set('string', ['text1', 'text2', 'text3']);
        self::assertEquals(['text1', 'text2', 'text3'], $originalSet->toArray());
        $filteredSet = $originalSet->filter(static function (string $item): bool {
            return 'text2' !== $item;
        });
        self::assertEquals(['text1', 'text3'], $filteredSet->toArray());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testFindEmptySet(): void
    {
        $set = new Set('string');
        self::assertNull($set->find(static function () {
        }));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testFind(): void
    {
        $set = new Set('string', ['text1', 'text2', 'text3']);
        self::assertNull($set->find(
            static function (): bool {
                return false;
            }
        ));
        self::assertEquals(
            'text2',
            $set->find(
                static function (string $item): bool {
                    return 'text2' === $item;
                }
            )
        );
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testAllEmptySet(): void
    {
        self::assertTrue((new Set('int'))->all(static function () {
        }));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testAll(): void
    {
        $set = new Set('string', ['text1', 'text2', 'text3']);
        self::assertFalse($set->all(static function (): bool {
            return false;
        }));
        self::assertFalse($set->all(static function (string $item): bool {
            return 'text2' === $item;
        }));
        self::assertTrue($set->all(static function (): bool {
            return true;
        }));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testSomeEmptySet(): void
    {
        self::assertFalse((new Set('int'))->some(static function () {
        }));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testSome(): void
    {
        $set = new Set('string', ['text1', 'text2', 'text3']);
        self::assertFalse($set->some(static function (): bool {
            return false;
        }));
        self::assertTrue($set->some(static function (string $item): bool {
            return 'text2' === $item;
        }));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testNoneEmptySet(): void
    {
        self::assertTrue((new Set('int'))->none(static function () {
        }));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testNone(): void
    {
        $set = new Set('string', ['text1', 'text2', 'text3']);
        self::assertTrue($set->none(static function (): bool {
            return false;
        }));
        self::assertFalse($set->none(static function (string $item): bool {
            return 'text2' === $item;
        }));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testCount(): void
    {
        self::assertCount(0, new Set('string'));
        self::assertCount(1, new Set('int', [1]));
        self::assertCount(1, new Set('int', [1, 1]));
        self::assertCount(2, new Set('int', [1, 2]));
        self::assertCount(3, new Set('int', [1, 2, 3]));
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testGetIterator(): void
    {
        self::assertInstanceOf(Traversable::class, (new Set('int', [1, 2, 3]))->getIterator());
        $expectedValues = [1, 2, 3];
        foreach (new Set('int', [1, 2, 3]) as $index => $item) {
            self::assertEquals($expectedValues[$index], $item);
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     * @throws SetException
     */
    public function testCreateFromElementsWithEmptyElements(): void
    {
        $this->expectExceptionObject(SetException::emptyElementsCanNotDetermineType());
        Set::createFromElements([]);
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testCreateFromElements(): void
    {
        $set = Set::createFromElements([1, 2, 3]);
        self::assertEquals('int', $set->getType());
        self::assertCount(3, $set);
        self::assertEquals([1, 2, 3], $set->toArray());
    }

    /**
     * @return void
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws SetException
     * @throws PHPUnitFrameworkException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function testWithDifferenceClassesWithAreHashable(): void
    {
        $item1 = new TestHashableEntity1('name');
        $item2 = new TestHashableEntity1('name');
        $item3 = new TestHashableEntity2('name');
        $item4 = new TestHashableEntity2('name');

        $set = new Set('object', [$item1, $item2, $item3, $item4]);
        self::assertCount(4, $set);
    }
}
