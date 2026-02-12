<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node\Field;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Exception\RuntimeException;
use Zenstruck\Dom\Node\Form\Field\Select\Multiselect;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestSession;

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
    public function select_calls_session_for_each_value(): void
    {
        $session = new TestSession();
        $multi = $this->dom($session)->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $multi->select(['option 2', 'option 3']);

        $this->assertCount(2, $session->selected);
        $this->assertSame('option 2', $session->selected[0]->value());
        $this->assertSame('option 3', $session->selected[1]->value());
    }

    #[Test]
    public function select_throws_when_missing(): void
    {
        $multi = $this->dom(new TestSession())->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find option with value/text "missing".');

        $multi->select(['missing']);
    }

    #[Test]
    public function deselect_all_calls_session(): void
    {
        $session = new TestSession();
        $multi = $this->dom($session)->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $multi->deselectAll();

        $this->assertCount(1, $session->unselected);
    }

    #[Test]
    public function value_returns_selected_values(): void
    {
        $multi = $this->dom()->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $this->assertSame(['option 1', 'option 3'], $multi->value());
    }

    private function dom(?TestSession $session = null): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'), $session);
    }
}
