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
final class Textarea extends Field
{
    public const SELECTOR = 'textarea';

    public function value(): string
    {
        return $this->directText();
    }

    public function fill(string $value): void
    {
        $this->ensureSession()->fill($this, $value);
    }
}
