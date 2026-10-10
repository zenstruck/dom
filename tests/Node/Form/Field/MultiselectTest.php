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
use Zenstruck\Dom\Node\Form\Field\Select\Multiselect;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestInspector;

#[CoversClass(Multiselect::class)]
final class MultiselectTest extends TestCase
{
    #[Test]
    public function selected_options_values_and_texts(): void
    {
        $multi = $this->dom()->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $selected = $multi->selectedOptions();
        $this->assertCount(2, $selected);
        $this->assertInstanceOf(Option::class, $selected->first());
        $this->assertSame(['option 1', 'option 3'], $multi->selectedValues());
        $this->assertSame(['option 1', 'option 3'], $multi->selectedTexts());
    }

    #[Test]
    public function value_returns_selected_values(): void
    {
        $multi = $this->dom()->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $this->assertSame(['option 1', 'option 3'], $multi->value());
    }

    #[Test]
    public function selected_options_defer_to_the_inspector(): void
    {
        $markup = (string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html');

        $none = (new Dom($markup, new TestInspector(selected: false)))->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);
        $all = (new Dom($markup, new TestInspector(selected: true)))->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $this->assertSame([], $none->selectedValues());
        $this->assertSame(['option 1', 'option 2', 'option 3'], $all->selectedValues());
    }

    #[Test]
    public function selected_values_keep_zero(): void
    {
        $dom = new Dom('<select id="s" multiple><option value="0" selected>Zero</option><option value="1" selected>One</option></select>');
        $multi = $dom->findOrFail(Selector::css('#s'))->ensure(Multiselect::class);

        $this->assertSame(['0', '1'], $multi->selectedValues());
    }

    private function dom(): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html'));
    }
}
