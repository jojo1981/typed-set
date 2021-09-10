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
final class DifferenceResult
{
    /** @var Difference */
    private Difference $lhs;

    /** @var Difference */
    private Difference $rhs;

    /** @var array */
    private array $same;

    /** @var bool */
    private bool $equal;

    /**
     * @param Difference $lhs
     * @param Difference $rhs
     * @param array $same
     * @param bool $equal
     */
    public function __construct(Difference $lhs, Difference $rhs, array $same, bool $equal)
    {
        $this->lhs = $lhs;
        $this->rhs = $rhs;
        $this->same = $same;
        $this->equal = $equal;
    }

    /**
     * Owning side
     *
     * @return Difference
     */
    public function getLhs(): Difference
    {
        return $this->lhs;
    }

    /**
     * Other side
     *
     * @return Difference
     */
    public function getRhs(): Difference
    {
        return $this->rhs;
    }

    /**
     * @return array
     */
    public function getSame(): array
    {
        return $this->same;
    }

    /**
     * @return bool
     */
    public function areEqual(): bool
    {
        return $this->equal;
    }
}
