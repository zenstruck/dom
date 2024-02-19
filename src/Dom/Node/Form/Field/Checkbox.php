<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Node\Form\Field;

use Zenstruck\Dom\Node\Form\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Checkbox extends Field
{
    public const SELECTOR = 'input[type="checkbox"]';

    public function isChecked(): bool
    {
        return $this->attributes()->has('checked');
    }

    /**
     * @return "on"|null
     */
    public function value(): ?string
    {
        return $this->isChecked() ? 'on' : null;
    }
}
