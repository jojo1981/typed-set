<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/typed-set package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\TypedSet;

/**
 * @package Jojo1981\TypedSet
 */
class Difference
{
    /** @var array */
    private array $missingElements;

    /** @var array */
    private array $extraElements;

    /**
     * @param array $missingElements
     * @param array $extraElements
     */
    public function __construct(array $missingElements, array $extraElements)
    {
        $this->missingElements = $missingElements;
        $this->extraElements = $extraElements;
    }

    /**
     * @return array
     */
    public function getMissingElements(): array
    {
        return $this->missingElements;
    }

    /**
     * @return array
     */
    public function getExtraElements(): array
    {
        return $this->extraElements;
    }
}
