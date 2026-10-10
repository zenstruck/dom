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
use Zenstruck\Dom\Node\Form\Field\Select\Combobox;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestInspector;

#[CoversClass(Option::class)]
final class OptionTest extends TestCase
{
    #[Test]
    public function value_and_selected_state(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../../../Fixtures/page.html'));

        $withValue = $dom->findOrFail(Selector::css('#input10 option[selected]'))->ensure(Option::class);
        $this->assertSame('option 3', $withValue->value());
        $this->assertTrue($withValue->isSelected());

        $noValue = $dom->findOrFail(Selector::css('#input4 option[value="option 2"]'))->ensure(Option::class);
        $this->assertSame('option 2', $noValue->value());
        $this->assertFalse($noValue->isSelected());
    }

    #[Test]
    public function selector_and_collection_resolve_parent_select(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../../../Fixtures/page.html'));
        $option = $dom->findOrFail(Selector::css('#input4 option:first-child'))->ensure(Option::class);

        $this->assertInstanceOf(Combobox::class, $option->selector());
        $this->assertCount(2, $option->collection());
    }

    #[Test]
    public function collection_is_empty_when_orphaned(): void
    {
        $dom = new Dom('<option>lonely</option>');
        $option = $dom->findOrFail(Selector::css('option'))->ensure(Option::class);

        $this->assertNull($option->selector());
        $this->assertCount(0, $option->collection());
    }

    #[Test]
    public function is_selected_defers_to_the_inspector(): void
    {
        $markup = '<form><select name="s" multiple><option value="a">A</option></select></form>';

        $this->assertFalse((new Dom($markup))->findOrFail(Selector::css('option'))->ensure(Option::class)->isSelected());
        $this->assertTrue((new Dom($markup, new TestInspector(selected: true)))->findOrFail(Selector::css('option'))->ensure(Option::class)->isSelected());
    }
}
