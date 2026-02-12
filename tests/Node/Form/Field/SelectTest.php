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
use Zenstruck\Dom\Node\Form\Field\Select;
use Zenstruck\Dom\Node\Form\Field\Select\Combobox;
use Zenstruck\Dom\Node\Form\Field\Select\Multiselect;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Selector;

#[CoversClass(Select::class)]
final class SelectTest extends TestCase
{
    #[Test]
    public function available_options_and_values(): void
    {
        $dom = new Dom('<select id="s"><option value=""></option><option value="value-1">One</option></select>');
        $select = $dom->findOrFail(Selector::css('#s'))->ensure(Combobox::class);

        $this->assertCount(2, $select->availableOptions());
        $this->assertSame(['value-1'], \array_values($select->availableValues()));
    }

    #[Test]
    public function option_matching_prefers_exact_then_contains_case_insensitive(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html'));
        $select = $dom->findOrFail(Selector::css('#input4'))->ensure(Combobox::class);

        $exact = $select->optionMatching('OPTION 2');
        $this->assertInstanceOf(Option::class, $exact);
        $this->assertSame('option 2', $exact->value());

        $contains = $select->optionMatching('another');
        $this->assertInstanceOf(Option::class, $contains);
        $this->assertSame('option 2', $contains->value());
    }

    #[Test]
    public function is_multiple_reflects_attribute(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html'));

        $single = $dom->findOrFail(Selector::css('#input4'))->ensure(Combobox::class);
        $multi = $dom->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $this->assertFalse($single->isMultiple());
        $this->assertTrue($multi->isMultiple());
    }
}
