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

use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Exception\RuntimeException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class DomTest extends TestCase
{
    /**
     * @test
     */
    public function auto_select_assertions(): void
    {
        $this->dom()->assert()
            ->contains('list 1')
            ->doesNotContain('list 4')
            ->containsIn('title', 'meta title')
            ->containsIn('ul li', 'list 2')
            ->containsIn('ul li', 'list 3')
            ->doesNotContainIn('ul li', 'list 5')
            ->hasElement('ul li')
            ->doesNotHaveElement('#foobar')
            ->hasElementCount('ul li', 3)
            ->elementIsVisible('#link')
            ->elementIsNotVisible('#foobar')
            ->attributeContains('meta[name="description"]', 'content', 'meta description')
            ->attributeContains('a', 'href', '/page2')
            ->attributeContains('a', 'href', '/exception')
            ->attributeContains('html', 'lang', 'en')
            ->attributeContains('body', 'class', 'body-class')
            ->attributeDoesNotContain('a', 'href', '/page4')
            ->fieldEquals('Input 1', 'input 1') // label
            ->fieldEquals('input1', 'input 1') // id
            ->fieldEquals('input_1', 'input 1') // name
            ->fieldEquals('Input 4', 'option 1') // combobox
            ->fieldEquals('Input 10', 'Some value') // combobox (text value)
            ->fieldDoesNotEqual('Input 1', 'input 2') // label
            ->fieldDoesNotEqual('input1', 'input 2') // id
            ->fieldDoesNotEqual('input_1', 'input 2') // name
            ->fieldSelected('Input 4', 'option 1') // combobox
            ->fieldNotSelected('Input 4', 'option 2') // combobox
            ->fieldSelected('Input 10', 'Some value') // combobox (text value)
            ->fieldSelected('Input 7', 'option 3') // multiselect
            ->fieldNotSelected('Input 7', 'option 2') // multiselect
            ->fieldSelected('Input 6', 'Another Option') // multiselect (text value)
            ->fieldSelected('input_8', 'option 2') // radio
            ->fieldNotSelected('input_8', 'option 1') // radio
            ->fieldChecked('input_3') // checkbox
            ->fieldNotChecked('input_2') // checkbox
            ->fieldChecked('Radio 2') // radio
            ->fieldNotChecked('Radio 1') // radio
        ;
    }

    /**
     * @test
     */
    public function find_or_fail(): void
    {
        $dom = $this->dom();

        $dom->findOrFail('#link');

        $this->expectException(RuntimeException::class);

        $dom->findOrFail('#foobar');
    }

    /**
     * @test
     */
    public function advanced_selector_assertions(): void
    {
        $this->dom()->assert()
            ->containsIn(fn(Dom $dom) => $dom->findAll('ul li')->last(), 'list 3')
        ;
    }

    /**
     * @test
     * @dataProvider nodeSelectorsDataProvider
     */
    public function node_selectors(mixed $expected, \Closure $actual): void
    {
        $dom = $this->dom();

        $this->assertSame($expected, $actual($dom));
    }

    public static function nodeSelectorsDataProvider(): iterable
    {
        yield [
            'a link. not a link',
            fn(Dom $d) => $d->find('#link')->text(),
        ];

        yield [
            'not a link',
            fn(Dom $d) => $d->find('#link')->directText(),
        ];

        yield [
            '<a href="/page2" title="click here">a link.</a> not a link',
            fn(Dom $d) => $d->find('#link')->innerHtml(),
        ];

        yield [
            '<p id="link"><a href="/page2" title="click here">a link.</a> not a link</p>',
            fn(Dom $d) => $d->find('#link')->outerHtml(),
        ];
    }

    protected function dom(): Dom
    {
        return new Dom(\file_get_contents(__DIR__.'/Fixtures/page.html'));
    }
}
