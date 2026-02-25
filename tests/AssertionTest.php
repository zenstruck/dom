<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Dom;
use Zenstruck\Dom\Assertion;

#[CoversClass(Assertion::class)]
final class AssertionTest extends TestCase
{
    #[Test]
    public function can_create_from_string(): void
    {
        $assertion = new Assertion('<div>content</div>');
        $assertion->contains('content');
    }

    #[Test]
    public function can_create_from_dom(): void
    {
        $dom = new Dom('<div>content</div>');
        $assertion = new Assertion($dom);
        $assertion->contains('content');
    }

    #[Test]
    public function can_create_from_crawler(): void
    {
        $crawler = new Crawler('<div>content</div>');
        $assertion = new Assertion($crawler);
        $assertion->contains('content');
    }

    #[Test]
    public function contains(): void
    {
        $this->assertion()->contains('list 1');
    }

    #[Test]
    public function does_not_contain(): void
    {
        $this->assertion()->doesNotContain('nonexistent text');
    }

    #[Test]
    public function contains_in(): void
    {
        $this->assertion()->containsIn('ul', 'list 1');
    }

    #[Test]
    public function does_not_contain_in(): void
    {
        $this->assertion()->doesNotContainIn('ul', 'nonexistent');
    }

    #[Test]
    public function has_element(): void
    {
        $this->assertion()->hasElement('ul');
    }

    #[Test]
    public function does_not_have_element(): void
    {
        $this->assertion()->doesNotHaveElement('table');
    }

    #[Test]
    public function has_element_count(): void
    {
        $this->assertion()->hasElementCount('li', 3);
    }

    #[Test]
    public function element_is_visible(): void
    {
        $this->assertion()->elementIsVisible('ul');
    }

    #[Test]
    public function element_is_not_visible_for_hidden(): void
    {
        $assertion = new Assertion('<div hidden>hidden</div>');
        $assertion->elementIsNotVisible('div');
    }

    #[Test]
    public function element_is_not_visible_when_element_missing(): void
    {
        $this->assertion()->elementIsNotVisible('table');
    }

    #[Test]
    public function attribute_contains(): void
    {
        $assertion = new Assertion('<div id="test" class="foo bar">content</div>');
        $assertion->attributeContains('#test', 'class', 'foo');
    }

    #[Test]
    public function attribute_does_not_contain(): void
    {
        $assertion = new Assertion('<div id="test" class="foo bar">content</div>');
        $assertion->attributeDoesNotContain('#test', 'class', 'baz');
    }

    #[Test]
    public function field_equals_for_input(): void
    {
        $this->assertion()->fieldEquals('#input1', 'input 1');
    }

    #[Test]
    public function field_equals_for_empty_string(): void
    {
        $this->assertion()->fieldEquals('#empty-input', '');
    }

    #[Test]
    public function field_equals_for_combobox_by_text(): void
    {
        $this->assertion()->fieldEquals('#input4', 'option 1');
    }

    #[Test]
    public function field_does_not_equal(): void
    {
        $this->assertion()->fieldDoesNotEqual('#input1', 'wrong value');
    }

    #[Test]
    public function field_selected_for_radio(): void
    {
        $this->assertion()->fieldSelected('#radio2', 'option 2');
    }

    #[Test]
    public function field_selected_for_combobox(): void
    {
        $this->assertion()->fieldSelected('#input4', 'option 1');
    }

    #[Test]
    public function field_placeholder_selected_for_combobox(): void
    {
        $this->assertion()->fieldSelected('#select-with-empty-value', '');
    }

    #[Test]
    public function field_selected_for_multiselect_by_value(): void
    {
        $this->assertion()->fieldSelected('#input7', 'option 1');
    }

    #[Test]
    public function field_selected_for_multiselect_by_text(): void
    {
        $this->assertion()->fieldSelected('#input7', 'option 3');
    }

    #[Test]
    public function field_not_selected_for_radio(): void
    {
        $this->assertion()->fieldNotSelected('#radio1', 'option 1');
    }

    #[Test]
    public function field_not_selected_for_combobox(): void
    {
        $this->assertion()->fieldNotSelected('#input4', 'option 2');
    }

    #[Test]
    public function field_not_selected_for_multiselect(): void
    {
        $this->assertion()->fieldNotSelected('#input7', 'option 2');
    }

    #[Test]
    public function field_checked_for_checkbox(): void
    {
        $this->assertion()->fieldChecked('#input3');
    }

    #[Test]
    public function field_checked_for_radio(): void
    {
        $this->assertion()->fieldChecked('#radio2');
    }

    #[Test]
    public function field_not_checked_for_checkbox(): void
    {
        $this->assertion()->fieldNotChecked('#input2');
    }

    #[Test]
    public function field_not_checked_for_radio(): void
    {
        $this->assertion()->fieldNotChecked('#radio1');
    }

    #[Test]
    public function methods_are_chainable(): void
    {
        $this->assertion()
            ->contains('list 1')
            ->doesNotContain('nonexistent')
            ->hasElement('ul')
            ->doesNotHaveElement('table')
            ->hasElementCount('li', 3)
            ->elementIsVisible('ul')
        ;
    }

    private function assertion(): Assertion
    {
        return new Assertion(\file_get_contents(__DIR__.'/Fixtures/page.html'));
    }
}
