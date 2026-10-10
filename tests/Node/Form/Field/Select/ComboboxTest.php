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

#[CoversClass(Combobox::class)]
final class ComboboxTest extends TestCase
{
    #[Test]
    public function selected_option_and_text_value(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../../../Fixtures/page.html'));

        $selected = $dom->findOrFail(Selector::css('#input10'))->ensure(Combobox::class);
        $this->assertInstanceOf(Option::class, $selected->selectedOption());
        $this->assertSame('option 3', $selected->selectedValue());
        $this->assertSame('Some value', $selected->selectedText());

        $fallback = $dom->findOrFail(Selector::css('#input11'))->ensure(Combobox::class);
        $this->assertSame('option 1', $fallback->selectedValue());
        $this->assertSame('Some value', $fallback->selectedText());
    }

    private function dom(): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../../../Fixtures/page.html'));
    }
}
