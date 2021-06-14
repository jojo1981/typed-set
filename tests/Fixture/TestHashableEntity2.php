<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet\TestSuite\Fixture;

use Jojo1981\Contracts\HashableInterface;

/**
 * @package Jojo1981\TypedSet\TestSuite\Fixture
 */
class TestHashableEntity2 implements HashableInterface
{
    /** @var string */
    private $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return \hash('sha256', $this->name);
    }
}
