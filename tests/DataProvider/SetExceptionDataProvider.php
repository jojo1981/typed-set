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

use Jojo1981\PhpTypes\ClassType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\PhpTypes\FloatType;
use Jojo1981\PhpTypes\IntegerType;
use Jojo1981\PhpTypes\ObjectType;
use Jojo1981\PhpTypes\StringType;
use Jojo1981\PhpTypes\Value\ClassName;
use Jojo1981\PhpTypes\Value\Exception\ValueException;

/**
 * @package Jojo1981\TypedSet\TestSuite\DataProvider
 */
class SetExceptionDataProvider
{
    /**
     * @throws ValueException
     * @throws TypeException
     * @return array[]
     */
    public function getTestDataForDataIsNotOfExpectedType(): array
    {
        return [
            [
                new StringType(),
                new IntegerType(),
                'Data is not of type: `string`, but of type: `int`'
            ],
            [
                new StringType(),
                new IntegerType(),
                'Pref! Data is not of type: `string`, but of type: `int`',
                'Pref! '
            ],
            [
                new ObjectType(),
                new FloatType(),
                'Data is not of type: `object`, but of type: `float`'
            ],
            [
                new ClassType(new ClassName(__CLASS__)),
                new FloatType(),
                'Data is not an instance of: `\Jojo1981\TypedSet\TestSuite\DataProvider\SetExceptionDataProvider`, but of type: `float`',
                null
            ],
            [
                new FloatType(),
                new ClassType(new ClassName(__CLASS__)),
                'Prefix. Data is not of type: `float`, but an instance of: `\Jojo1981\TypedSet\TestSuite\DataProvider\SetExceptionDataProvider`',
                'Prefix. '
            ]
        ];
    }
}