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

use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\TypedSet\TestSuite\Fixture\TestEntity;

/**
 * @package Jojo1981\TypedSet\TestSuite\DataProvider
 */
class HandlerExceptionDataProvider
{
    /**
     * @throws TypeException
     * @return array[]
     */
    public function getTestData(): array
    {
        return [
            [
                AbstractType::createFromTypeName('string'),
                AbstractType::createFromTypeName('integer'),
                'Can not handle element, element not of type: `string`, but of type: `int`'
            ],
            [
                AbstractType::createFromTypeName(\stdClass::class),
                AbstractType::createFromTypeName('number'),
                'Can not handle element, element not an instance of: `\stdClass`, but of type: `float`'
            ],
            [
                AbstractType::createFromTypeName('bool'),
                AbstractType::createFromTypeName(\stdClass::class),
                'Can not handle element, element not of type: `bool`, but an instance of: `\stdClass`'
            ],
            [
                AbstractType::createFromTypeName(TestEntity::class),
                AbstractType::createFromTypeName(\stdClass::class),
                'Can not handle element, element not an instance of: `\Jojo1981\TypedSet\TestSuite\Fixture\TestEntity`, but an instance of: `\stdClass`'
            ]
        ];
    }
}