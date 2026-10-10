<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Node\Form\Field\Radio;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestInspector;

#[CoversClass(Radio::class)]
final class RadioTest extends TestCase
{
    #[Test]
    public function with_value_finds_the_matching_radio_in_the_group(): void
    {
        $radio = $this->dom()->findOrFail(Selector::css('#radio1'))->ensure(Radio::class);

        $this->assertSame('option 3', $radio->withValue('option 3')?->value());
        $this->assertNull($radio->withValue('missing'));
    }

    #[Test]
    public function selected_returns_null_when_none_checked(): void
    {
        $dom = new Dom('<form><input type="radio" name="group" value="a"><input type="radio" name="group" value="b"></form>');
        $radio = $dom->findOrFail(Selector::css('input[value="a"]'))->ensure(Radio::class);

        $this->assertNull($radio->selected());
        $this->assertNull($radio->selectedValue());
    }

    #[Test]
    public function is_selected_defers_to_the_inspector(): void
    {
        $markup = '<form><input type="radio" name="choice" value="a"></form>';

        $this->assertFalse((new Dom($markup))->findOrFail(Selector::css('input'))->ensure(Radio::class)->isSelected());
        $this->assertTrue((new Dom($markup, new TestInspector(selected: true)))->findOrFail(Selector::css('input'))->ensure(Radio::class)->isSelected());
    }

    private function dom(): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html'));
    }
}
