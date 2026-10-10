<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node\Form\Field\Select;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestInspector;

#[CoversClass(Checkbox::class)]
final class CheckboxTest extends TestCase
{
    #[Test]
    public function value_is_on_only_when_checked(): void
    {
        $checked = $this->dom()->findOrFail(Selector::css('#input3'))->ensure(Checkbox::class);
        $unchecked = $this->dom()->findOrFail(Selector::css('#input2'))->ensure(Checkbox::class);

        $this->assertSame('on', $checked->value());
        $this->assertNull($unchecked->value());
    }

    #[Test]
    public function is_checked_defers_to_the_inspector(): void
    {
        $markup = '<form><input type="checkbox" name="agree"></form>';

        $this->assertFalse((new Dom($markup))->findOrFail(Selector::css('input'))->ensure(Checkbox::class)->isChecked());
        $this->assertTrue((new Dom($markup, new TestInspector(selected: true)))->findOrFail(Selector::css('input'))->ensure(Checkbox::class)->isChecked());
    }

    private function dom(): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../../../Fixtures/page.html'));
    }
}
