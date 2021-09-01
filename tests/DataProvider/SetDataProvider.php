<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\DataProvider;

use ArrayIterator;
use Jojo1981\PhpTypes\ArrayType;
use Jojo1981\PhpTypes\CallableType;
use Jojo1981\PhpTypes\ClassType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\PhpTypes\FloatType;
use Jojo1981\PhpTypes\IntegerType;
use Jojo1981\PhpTypes\IterableType;
use Jojo1981\PhpTypes\ObjectType;
use Jojo1981\PhpTypes\StringType;
use Jojo1981\PhpTypes\Value\ClassName;
use Jojo1981\PhpTypes\Value\Exception\ValueException;
use Jojo1981\TypedSet\Difference;
use Jojo1981\TypedSet\DifferenceResult;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Set;
use Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity;
use Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity;
use Jojo1981\TypedSet\TestSuite\Fixture\TestEntity;
use Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase;
use Jojo1981\TypedSet\TestSuite\Fixture\TestHashableEntity1;
use RuntimeException;
use stdClass;

/**
 * @package Jojo1981\TypedSet\TestSuite\DataProvider
 */
final class SetDataProvider
{
    /**
     * @return array[]
     */
    public function getElementsWithDuplicates(): array
    {
        return [
            [
                'string',
                ['text1', 'text3', 'text1', 'text2'],
                ['text1', 'text3', 'text2']
            ],
            [
                'int',
                [1, 2, 3, 2, 4, 1, 5, 5, 5, 6],
                [1, 2, 3, 4, 5, 6]
            ],
            [
                'float',
                [1.0, 2.0, 3.0, 2.0, 4.0, 1.0, 5.0, 5.0, 5.0, 6.0],
                [1.0, 2.0, 3.0, 4.0, 5.0, 6.0]
            ],
            [
                'array',
                [[1, 2, 3], [4, 5, 6], [1, 2, 3]],
                [[1, 2, 3], [4, 5, 6]]
            ],
            [
                'array',
                [['1', '2', '3'], [4, 5, 6], [1, 2, 3]],
                [['1', '2', '3'], [4, 5, 6], [1, 2, 3]]
            ],
            [
                'array',
                [['1', '2', '3'], ['4', '5', '6'], ['1', '2', '3']],
                [['1', '2', '3'], ['4', '5', '6']]
            ],
            [
                'callable',
                [($callback1 = static function () { }), ($callback2 = static function () { }), $callback1],
                [$callback1, $callback2]
            ],
            [
                'iterable',
                [($iterable1 = new ArrayIterator()), ($iterable2 = new ArrayIterator()), $iterable1],
                [$iterable1, $iterable2]
            ],
            [
                TestHashableEntity1::class,
                [new TestHashableEntity1('car'), new TestHashableEntity1('house'), new TestHashableEntity1('car'), new TestHashableEntity1('boat')],
                [new TestHashableEntity1('car'), new TestHashableEntity1('house'), new TestHashableEntity1('boat')]
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function getInvalidTypes(): array
    {
        return [
            ['invalid-type'],
            ['mixed'],
            ['null'],
            ['void'],
            ['bool'],
            ['boolean'],
            ['resource']
        ];
    }

    /**
     * @return array[]
     */
    public function getPrimitiveTypeWithInvalidData(): array
    {
        return [

            ['int', [-1, 0, 1, 5, 'text'], 'Data is not of type: `int`, but of type: `string`'],
            ['int', [-1, 0, 1, 5, -1.0], 'Data is not of type: `int`, but of type: `float`'],
            ['int', [-1, 0, 1, 5, 0.0], 'Data is not of type: `int`, but of type: `float`'],
            ['int', [-1, 0, 1, 5, 1.0], 'Data is not of type: `int`, but of type: `float`'],
            ['int', [-1, 0, 1, 5, true], 'Data is not of type: `int`, but of type: `bool`'],
            ['int', [-1, 0, 1, 5, false], 'Data is not of type: `int`, but of type: `bool`'],
            ['int', [-1, 0, 1, 5, []], 'Data is not of type: `int`, but of type: `array`'],
            ['int', [-1, 0, 1, 5, ['item1', 'item2']], 'Data is not of type: `int`, but of type: `array`'],
            ['int', [-1, 0, 1, 5, ['key' => 'value']], 'Data is not of type: `int`, but of type: `array`'],
            ['int', [-1, 0, 1, 5, new stdClass()], 'Data is not of type: `int`, but an instance of: `\stdClass`'],
            ['int', [-1, 0, 1, 5, new TestEntity()], 'Data is not of type: `int`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`'],
            ['int', [-1, 0, 1, 5, new TestEntityBase()], 'Data is not of type: `int`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`'],

            ['integer', [-1, 0, 1, 5, 'text'], 'Data is not of type: `int`, but of type: `string`'],
            ['integer', [-1, 0, 1, 5, -1.0], 'Data is not of type: `int`, but of type: `float`'],
            ['integer', [-1, 0, 1, 5, 0.0], 'Data is not of type: `int`, but of type: `float`'],
            ['integer', [-1, 0, 1, 5, 1.0], 'Data is not of type: `int`, but of type: `float`'],
            ['integer', [-1, 0, 1, 5, true], 'Data is not of type: `int`, but of type: `bool`'],
            ['integer', [-1, 0, 1, 5, false], 'Data is not of type: `int`, but of type: `bool`'],
            ['integer', [-1, 0, 1, 5, []], 'Data is not of type: `int`, but of type: `array`'],
            ['integer', [-1, 0, 1, 5, ['item1', 'item2']], 'Data is not of type: `int`, but of type: `array`'],
            ['integer', [-1, 0, 1, 5, ['key' => 'value']], 'Data is not of type: `int`, but of type: `array`'],
            ['integer', [-1, 0, 1, 5, new stdClass()], 'Data is not of type: `int`, but an instance of: `\stdClass`'],
            ['integer', [-1, 0, 1, 5, new TestEntity()], 'Data is not of type: `int`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`'],
            ['integer', [-1, 0, 1, 5, new TestEntityBase()], 'Data is not of type: `int`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`'],

            ['float', [-1.0, 0.0, 1.0, 3.25, 'text'], 'Data is not of type: `float`, but of type: `string`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, -1], 'Data is not of type: `float`, but of type: `int`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, 0], 'Data is not of type: `float`, but of type: `int`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, 1], 'Data is not of type: `float`, but of type: `int`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, true], 'Data is not of type: `float`, but of type: `bool`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, false], 'Data is not of type: `float`, but of type: `bool`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, []], 'Data is not of type: `float`, but of type: `array`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, ['item1', 'item2']], 'Data is not of type: `float`, but of type: `array`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, ['key' => 'value']], 'Data is not of type: `float`, but of type: `array`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, new stdClass()], 'Data is not of type: `float`, but an instance of: `\stdClass`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, new TestEntity()], 'Data is not of type: `float`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`'],
            ['float', [-1.0, 0.0, 1.0, 3.25, new TestEntityBase()], 'Data is not of type: `float`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`'],

            ['double', [-1.0, 0.0, 1.0, 3.25, 'text'], 'Data is not of type: `float`, but of type: `string`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, -1], 'Data is not of type: `float`, but of type: `int`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, 0], 'Data is not of type: `float`, but of type: `int`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, 1], 'Data is not of type: `float`, but of type: `int`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, true], 'Data is not of type: `float`, but of type: `bool`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, false], 'Data is not of type: `float`, but of type: `bool`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, []], 'Data is not of type: `float`, but of type: `array`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, ['item1', 'item2']], 'Data is not of type: `float`, but of type: `array`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, ['key' => 'value']], 'Data is not of type: `float`, but of type: `array`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, new stdClass()], 'Data is not of type: `float`, but an instance of: `\stdClass`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, new TestEntity()], 'Data is not of type: `float`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`'],
            ['double', [-1.0, 0.0, 1.0, 3.25, new TestEntityBase()], 'Data is not of type: `float`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`'],

            ['number', [-1.0, 0.0, 1.0, 3.25, 'text'], 'Data is not of type: `float`, but of type: `string`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, -1], 'Data is not of type: `float`, but of type: `int`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, 0], 'Data is not of type: `float`, but of type: `int`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, 1], 'Data is not of type: `float`, but of type: `int`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, true], 'Data is not of type: `float`, but of type: `bool`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, false], 'Data is not of type: `float`, but of type: `bool`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, []], 'Data is not of type: `float`, but of type: `array`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, ['item1', 'item2']], 'Data is not of type: `float`, but of type: `array`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, ['key' => 'value']], 'Data is not of type: `float`, but of type: `array`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, new stdClass()], 'Data is not of type: `float`, but an instance of: `\stdClass`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, new TestEntity()], 'Data is not of type: `float`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`'],
            ['number', [-1.0, 0.0, 1.0, 3.25, new TestEntityBase()], 'Data is not of type: `float`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`'],

            ['string', ['text', -1], 'Data is not of type: `string`, but of type: `int`'],
            ['string', ['text', 0], 'Data is not of type: `string`, but of type: `int`'],
            ['string', ['text', 1], 'Data is not of type: `string`, but of type: `int`'],
            ['string', ['text', -1.0], 'Data is not of type: `string`, but of type: `float`'],
            ['string', ['text', 0.0], 'Data is not of type: `string`, but of type: `float`'],
            ['string', ['text', 1.0], 'Data is not of type: `string`, but of type: `float`'],
            ['string', ['text', true], 'Data is not of type: `string`, but of type: `bool`'],
            ['string', ['text', false], 'Data is not of type: `string`, but of type: `bool`'],
            ['string', ['text', []], 'Data is not of type: `string`, but of type: `array`'],
            ['string', ['text', ['item1', 'item2']], 'Data is not of type: `string`, but of type: `array`'],
            ['string', ['text', ['key' => 'value']], 'Data is not of type: `string`, but of type: `array`'],
            ['string', ['text', new stdClass()], 'Data is not of type: `string`, but an instance of: `\stdClass`'],
            ['string', ['text', new TestEntity()], 'Data is not of type: `string`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`'],
            ['string', ['text', new TestEntityBase()], 'Data is not of type: `string`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`'],

            ['array', [[], ['item1', 'item2'], ['key' => 'value'], 'text'], 'Data is not of type: `array`, but of type: `string`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], -1], 'Data is not of type: `array`, but of type: `int`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], 0], 'Data is not of type: `array`, but of type: `int`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], 1], 'Data is not of type: `array`, but of type: `int`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], -1.0], 'Data is not of type: `array`, but of type: `float`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], 0.0], 'Data is not of type: `array`, but of type: `float`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], 1.0], 'Data is not of type: `array`, but of type: `float`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], true], 'Data is not of type: `array`, but of type: `bool`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], false], 'Data is not of type: `array`, but of type: `bool`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], new stdClass()], 'Data is not of type: `array`, but an instance of: `\stdClass`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], new TestEntity()], 'Data is not of type: `array`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`'],
            ['array', [[], ['item1', 'item2'], ['key' => 'value'], new TestEntityBase()], 'Data is not of type: `array`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`'],

            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), 'text'], 'Data is not of type: `object`, but of type: `string`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), -1], 'Data is not of type: `object`, but of type: `int`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), 0], 'Data is not of type: `object`, but of type: `int`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), 1], 'Data is not of type: `object`, but of type: `int`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), -1.0], 'Data is not of type: `object`, but of type: `float`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), 0.0], 'Data is not of type: `object`, but of type: `float`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), 1.0], 'Data is not of type: `object`, but of type: `float`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), true], 'Data is not of type: `object`, but of type: `bool`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), false], 'Data is not of type: `object`, but of type: `bool`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), []], 'Data is not of type: `object`, but of type: `array`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), ['item1', 'item2']], 'Data is not of type: `object`, but of type: `array`'],
            ['object', [new stdClass(), new TestEntity(), new TestEntityBase(), ['key' => 'value']], 'Data is not of type: `object`, but of type: `array`'],
        ];
    }

    /**
     * @return array[]
     */
    public function getClassNameTypeWithInvalidData(): array
    {
        return [
            [TestEntity::class, [new TestEntity(), -1], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `int`'],
            [TestEntity::class, [new TestEntity(), 0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `int`'],
            [TestEntity::class, [new TestEntity(), 1], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `int`'],
            [TestEntity::class, [new TestEntity(), true], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `bool`'],
            [TestEntity::class, [new TestEntity(), false], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `bool`'],
            [TestEntity::class, [new TestEntity(), -1.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `float`'],
            [TestEntity::class, [new TestEntity(), 0.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `float`'],
            [TestEntity::class, [new TestEntity(), 1.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `float`'],
            [TestEntity::class, [new TestEntity(), 'text'], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `string`'],
            [TestEntity::class, [new TestEntity(), []], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `array`'],
            [TestEntity::class, [new TestEntity(), ['item1', 'item2']], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `array`'],
            [TestEntity::class, [new TestEntity(), ['key' => 'value']], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but of type: `array`'],
            [TestEntity::class, [new TestEntity(), new stdClass()], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but an instance of: `\stdClass`'],
            [TestEntity::class, [new TestEntityBase(), new stdClass()], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`'],

            [TestEntityBase::class, [new TestEntity(), -1], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `int`'],
            [TestEntityBase::class, [new TestEntity(), 0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `int`'],
            [TestEntityBase::class, [new TestEntity(), 1], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `int`'],
            [TestEntityBase::class, [new TestEntity(), true], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `bool`'],
            [TestEntityBase::class, [new TestEntity(), false], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `bool`'],
            [TestEntityBase::class, [new TestEntity(), -1.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `float`'],
            [TestEntityBase::class, [new TestEntity(), 0.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `float`'],
            [TestEntityBase::class, [new TestEntity(), 1.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `float`'],
            [TestEntityBase::class, [new TestEntity(), 'text'], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `string`'],
            [TestEntityBase::class, [new TestEntity(), []], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `array`'],
            [TestEntityBase::class, [new TestEntity(), ['item1', 'item2']], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `array`'],
            [TestEntityBase::class, [new TestEntity(), ['key' => 'value']], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but of type: `array`'],
            [TestEntityBase::class, [new TestEntity(), new stdClass()], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntityBase`, but an instance of: `\stdClass`'],

            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), -1], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `int`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), 0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `int`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), 1], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `int`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), true], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `bool`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), false], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `bool`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), -1.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `float`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), 0.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `float`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), 1.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `float`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), 'text'], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `string`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), []], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `array`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), ['item1', 'item2']], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `array`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), ['key' => 'value']], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but of type: `array`'],
            [AbstractTestEntity::class, [new TestEntityBase(), new TestEntity(), new stdClass()], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\AbstractTestEntity`, but an instance of: `\stdClass`'],

            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), -1], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `int`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), 0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `int`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), 1], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `int`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), true], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `bool`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), false], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `bool`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), -1.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `float`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), 0.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `float`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), 1.0], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `float`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), 'text'], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `string`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), []], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `array`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), ['item1', 'item2']], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `array`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), ['key' => 'value']], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but of type: `array`'],
            [InterfaceTestEntity::class, [new TestEntityBase(), new TestEntity(), new stdClass()], 'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\InterfaceTestEntity`, but an instance of: `\stdClass`']
        ];
    }

    /**
     * @return array[]
     */
    public function getTypesTestData(): array
    {
        return [
            ['string', 'string'],
            ['text', 'string'],
            ['int', 'int'],
            ['integer', 'int'],
            ['float', 'float'],
            ['real', 'float'],
            ['double', 'float'],
            ['number', 'float'],
            ['array', 'array'],
            ['object', 'object'],
            ['callable', 'callable'],
            ['callback', 'callable'],
            ['iterable', 'iterable'],
            [stdClass::class, '\\' . stdClass::class],
            [InterfaceTestEntity::class, '\\' . InterfaceTestEntity::class],
            [AbstractTestEntity::class, '\\' . AbstractTestEntity::class],
            [TestEntityBase::class, '\\' . TestEntityBase::class],
            [TestEntity::class, '\\' . TestEntity::class]
        ];
    }

    /**
     * @throws ValueException
     * @throws TypeException
     * @return array[]
     */
    public function getIsEqualTypeTestData(): array
    {
        return [
            ['string', new StringType(), true],
            ['string', new IntegerType(), false],
            ['string', new FloatType(), false],
            ['string', new CallableType(), false],
            ['string', new IterableType(), false],
            ['string', new ArrayType(), false],
            ['string', new ObjectType(), false],
            ['string', new ClassType(new ClassName(__CLASS__)), false],

            ['int', new IntegerType(), true],
            ['int', new StringType(), false],
            ['int', new FloatType(), false],
            ['int', new CallableType(), false],
            ['int', new IterableType(), false],
            ['int', new ArrayType(), false],
            ['int', new ObjectType(), false],
            ['int', new ClassType(new ClassName(__CLASS__)), false],

            ['float', new FloatType(), true],
            ['float', new StringType(), false],
            ['float', new IntegerType(), false],
            ['float', new CallableType(), false],
            ['float', new IterableType(), false],
            ['float', new ArrayType(), false],
            ['float', new ObjectType(), false],
            ['float', new ClassType(new ClassName(__CLASS__)), false],

            ['callable', new CallableType(), true],
            ['callable', new StringType(), false],
            ['callable', new IntegerType(), false],
            ['callable', new FloatType(), false],
            ['callable', new IterableType(), false],
            ['callable', new ArrayType(), false],
            ['callable', new ObjectType(), false],
            ['callable', new ClassType(new ClassName(__CLASS__)), false],

            ['iterable', new IterableType(), true],
            ['iterable', new StringType(), false],
            ['iterable', new IntegerType(), false],
            ['iterable', new FloatType(), false],
            ['iterable', new CallableType(), false],
            ['iterable', new ArrayType(), false],
            ['iterable', new ObjectType(), false],
            ['iterable', new ClassType(new ClassName(__CLASS__)), false],

            ['array', new ArrayType(), true],
            ['array', new StringType(), false],
            ['array', new IntegerType(), false],
            ['array', new FloatType(), false],
            ['array', new CallableType(), false],
            ['array', new IterableType(), false],
            ['array', new ObjectType(), false],
            ['array', new ClassType(new ClassName(__CLASS__)), false],

            ['object', new ObjectType(), true],
            ['object', new ClassType(new ClassName(__CLASS__)), false],
            ['object', new StringType(), false],
            ['object', new IntegerType(), false],
            ['object', new FloatType(), false],
            ['object', new CallableType(), false],
            ['object', new IterableType(), false],
            ['object', new ArrayType(), false],

            [InterfaceTestEntity::class, new ClassType(new ClassName(InterfaceTestEntity::class)), true],
            [InterfaceTestEntity::class, new ClassType(new ClassName(AbstractTestEntity::class)), false],
            [InterfaceTestEntity::class, new ClassType(new ClassName(TestEntityBase::class)), false],
            [InterfaceTestEntity::class, new ClassType(new ClassName(TestEntity::class)), false],
            [InterfaceTestEntity::class, new StringType(), false],
            [InterfaceTestEntity::class, new IntegerType(), false],
            [InterfaceTestEntity::class, new FloatType(), false],
            [InterfaceTestEntity::class, new CallableType(), false],
            [InterfaceTestEntity::class, new IterableType(), false],
            [InterfaceTestEntity::class, new ArrayType(), false],
            [InterfaceTestEntity::class, new ObjectType(), false],

            [AbstractTestEntity::class, new ClassType(new ClassName(AbstractTestEntity::class)), true],
            [AbstractTestEntity::class, new ClassType(new ClassName(InterfaceTestEntity::class)), false],
            [AbstractTestEntity::class, new ClassType(new ClassName(TestEntityBase::class)), false],
            [AbstractTestEntity::class, new ClassType(new ClassName(TestEntity::class)), false],
            [AbstractTestEntity::class, new StringType(), false],
            [AbstractTestEntity::class, new IntegerType(), false],
            [AbstractTestEntity::class, new FloatType(), false],
            [AbstractTestEntity::class, new CallableType(), false],
            [AbstractTestEntity::class, new IterableType(), false],
            [AbstractTestEntity::class, new ArrayType(), false],
            [AbstractTestEntity::class, new ObjectType(), false],

            [TestEntityBase::class, new ClassType(new ClassName(TestEntityBase::class)), true],
            [TestEntityBase::class, new ClassType(new ClassName(InterfaceTestEntity::class)), false],
            [TestEntityBase::class, new ClassType(new ClassName(AbstractTestEntity::class)), false],
            [TestEntityBase::class, new ClassType(new ClassName(TestEntity::class)), false],
            [TestEntityBase::class, new StringType(), false],
            [TestEntityBase::class, new IntegerType(), false],
            [TestEntityBase::class, new FloatType(), false],
            [TestEntityBase::class, new CallableType(), false],
            [TestEntityBase::class, new IterableType(), false],
            [TestEntityBase::class, new ArrayType(), false],
            [TestEntityBase::class, new ObjectType(), false],

            [TestEntity::class, new ClassType(new ClassName(TestEntity::class)), true],
            [TestEntity::class, new ClassType(new ClassName(InterfaceTestEntity::class)), false],
            [TestEntity::class, new ClassType(new ClassName(AbstractTestEntity::class)), false],
            [TestEntity::class, new ClassType(new ClassName(TestEntityBase::class)), false],
            [TestEntity::class, new StringType(), false],
            [TestEntity::class, new IntegerType(), false],
            [TestEntity::class, new FloatType(), false],
            [TestEntity::class, new CallableType(), false],
            [TestEntity::class, new IterableType(), false],
            [TestEntity::class, new ArrayType(), false],
            [TestEntity::class, new ObjectType(), false],
        ];
    }

    /**
     * @return array[]
     * @throws HandlerException
     * @throws RuntimeException
     * @throws SetException
     */
    public function getIsEqualTestData(): array
    {
        return [
            [
                new Set('string'),
                new Set('string'),
                true
            ],
            [
                new Set('string'),
                new Set('object'),
                false
            ],
            [
                new Set('string', ['text1']),
                new Set('string', ['text1']),
                true
            ],
            [
                new Set('int', [2]),
                new Set('string', ['text1']),
                false
            ],
            [
                new Set('string', ['text1', 'text3', 'text2']),
                new Set('string', ['text1', 'text2', 'text3']),
                true
            ],
            [
                new Set(InterfaceTestEntity::class),
                new Set(AbstractTestEntity::class),
                false
            ],
            [
                new Set(TestHashableEntity1::class, []),
                new Set(TestHashableEntity1::class, []),
                true
            ],
            [
                new Set(TestHashableEntity1::class, [new TestHashableEntity1('test')]),
                new Set(TestHashableEntity1::class, []),
                false
            ],
            [
                new Set(TestHashableEntity1::class, [new TestHashableEntity1('test')]),
                new Set(TestHashableEntity1::class, [new TestHashableEntity1('test')]),
                true
            ],
            [
                new Set(TestHashableEntity1::class, [new TestHashableEntity1('test1'), new TestHashableEntity1('test2')]),
                new Set(TestHashableEntity1::class, [new TestHashableEntity1('test2')]),
                false
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function getExceptionTestData(): array
    {
        return [
            ['string', 4,'Data is not of type: `string`, but of type: `int`'],
            ['integer', new stdClass(), 'Data is not of type: `int`, but an instance of: `\stdClass`'],
            [stdClass::class, 'text', 'Data is not an instance of: `\stdClass`, but of type: `string`'],
            [
                stdClass::class,
                new TestEntity(),
                'Data is not an instance of: `\stdClass`, but an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`'
            ],
        ];
    }

    /**
     * @return array[]
     * @throws HandlerException
     * @throws RuntimeException
     * @throws SetException
     */
    public function getCompareTestData(): array
    {
        return [
            [new Set('int'), new Set('int'), $this->createEmptyIsEqualDifferenceResult()],
            [new Set('float'), new Set('float'), $this->createEmptyIsEqualDifferenceResult()],
            [new Set('object'), new Set('object'), $this->createEmptyIsEqualDifferenceResult()],
            [new Set('int'), new Set('int'), $this->createEmptyIsEqualDifferenceResult()],
            [new Set('int'), new Set('int'), $this->createEmptyIsEqualDifferenceResult()],
            [
                new Set('int', [1, 2]),
                new Set('int', [3, 4]),
                new DifferenceResult(new Difference([3, 4], [1, 2]), new Difference([1, 2], [3, 4]), [], false)
            ],
            [new Set('int', [1, 2]), new Set('int', []), new DifferenceResult(new Difference([], [1, 2]), new Difference([1, 2], []), [], false)],
            [new Set('int', []), new Set('int', [1, 2]), new DifferenceResult(new Difference([1, 2], []), new Difference([], [1, 2]), [], false)],
            [new Set('int', [1, 2]), new Set('int', [2, 3]), new DifferenceResult(new Difference([3], [1]), new Difference([1], [3]), [2], false)]
        ];
    }

    /**
     * @return DifferenceResult
     */
    private function createEmptyIsEqualDifferenceResult(): DifferenceResult
    {
        return new DifferenceResult(new Difference([], []), new Difference([], []), [], true);
    }
}
