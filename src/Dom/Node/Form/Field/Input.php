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
final class Input extends Field
{
    public const SELECTOR = 'input';

    public function type(): string
    {
        return $this->attributes()->get('type') ?? 'button';
    }

    public function value(): ?string
    {
        return parent::value();
    }
}
