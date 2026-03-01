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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\DomCrawler\Crawler as PantherCrawler;
use Symfony\Component\Process\Process;
use Symfony\Component\VarDumper\VarDumper;
use Zenstruck\Dom;
use Zenstruck\Dom\Exception\RuntimeException;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[CoversClass(Dom::class)]
class DomTest extends TestCase
{
    #[Test]
    public function contains_text(): void
    {
        $this->dom()->assert()
            ->contains('list 1')
            ->doesNotContain('list 4')
        ;
    }

    #[Test]
    public function contains_in_selector(): void
    {
        $this->dom()->assert()
            ->containsIn('title', 'meta title')
            ->containsIn('ul li', 'list 2')
            ->containsIn('ul li', 'list 3')
            ->doesNotContainIn('ul li', 'list 5')
        ;
    }

    #[Test]
    public function has_element(): void
    {
        $this->dom()->assert()
            ->hasElement('ul li')
            ->doesNotHaveElement('#foobar')
            ->hasElementCount('ul li', 3)
        ;
    }

    #[Test]
    public function element_visibility(): void
    {
        $this->dom()->assert()
            ->elementIsVisible('#link')
            ->elementIsNotVisible('#foobar')
        ;
    }

    #[Test]
    public function attribute_contains(): void
    {
        $this->dom()->assert()
            ->attributeContains('meta[name="description"]', 'content', 'meta description')
            ->attributeContains('a', 'href', '/page2')
            ->attributeContains('a', 'href', '/exception')
            ->attributeContains('html', 'lang', 'en')
            ->attributeContains('body', 'class', 'body-class')
            ->attributeDoesNotContain('a', 'href', '/page4')
        ;
    }

    #[Test]
    public function attribute_equals(): void
    {
        $this->dom()->assert()
            ->attributeEquals('meta[name="description"]', 'content', 'meta description')
            ->attributeEquals('html', 'lang', 'en')
            ->attributeEquals('body', 'class', 'body-class')
            ->attributeNotEquals('body', 'class', 'other-class')
            ->attributeNotEquals('a', 'href', '/page4')
            ->attributeNotEquals('#foobar', 'class', 'any-class') // passes if element doesn't exist
        ;
    }

    #[Test]
    public function class_assertions(): void
    {
        $this->dom()->assert()
            ->hasClass('body', 'body-class')
            ->doesNotHaveClass('body', 'other-class')
            ->doesNotHaveClass('#foobar', 'any-class') // passes if element doesn't exist
        ;
    }

    #[Test]
    public function field_equals(): void
    {
        $this->dom()->assert()
            ->fieldEquals('Input 1', 'input 1') // label
            ->fieldEquals('input1', 'input 1') // id
            ->fieldEquals('input_1', 'input 1') // name
            ->fieldEquals('Input 4', 'option 1') // combobox
            ->fieldEquals('Input 10', 'Some value') // combobox (text value)
        ;
    }

    #[Test]
    public function field_does_not_equal(): void
    {
        $this->dom()->assert()
            ->fieldDoesNotEqual('Input 1', 'input 2') // label
            ->fieldDoesNotEqual('input1', 'input 2') // id
            ->fieldDoesNotEqual('input_1', 'input 2') // name
        ;
    }

    #[Test]
    public function field_selected(): void
    {
        $this->dom()->assert()
            ->fieldSelected('Input 4', 'option 1') // combobox
            ->fieldNotSelected('Input 4', 'option 2') // combobox
            ->fieldSelected('Input 10', 'Some value') // combobox (text value)
            ->fieldSelected('Input 7', 'option 3') // multiselect
            ->fieldNotSelected('Input 7', 'option 2') // multiselect
            ->fieldSelected('Input 6', 'Another Option') // multiselect (text value)
            ->fieldSelected('input_8', 'option 2') // radio
            ->fieldNotSelected('input_8', 'option 1') // radio
        ;
    }

    #[Test]
    public function field_checked(): void
    {
        $this->dom()->assert()
            ->fieldChecked('input_3') // checkbox
            ->fieldNotChecked('input_2') // checkbox
            ->fieldChecked('Radio 2') // radio
            ->fieldNotChecked('Radio 1') // radio
        ;
    }

    #[Test]
    public function find_or_fail(): void
    {
        $dom = $this->dom();

        $dom->findOrFail('#link');

        $this->expectException(RuntimeException::class);

        $dom->findOrFail('#foobar');
    }

    #[Test]
    public function advanced_selector_assertions(): void
    {
        $this->dom()->assert()
            ->containsIn(static fn(Dom $dom) => $dom->findAll('ul li')->last(), 'list 3')
        ;
    }

    #[Test]
    #[DataProvider('nodeSelectorsDataProvider')]
    public function node_selectors(mixed $expected, \Closure $actual): void
    {
        $expected = self::normalizeWhitespace($expected);
        $actual = self::normalizeWhitespace($actual($this->dom()));

        $this->assertSame($expected, $actual);
    }

    public static function nodeSelectorsDataProvider(): iterable
    {
        yield [
            'a link. not a link',
            static fn(Dom $d) => $d->find('#link')->text(),
        ];

        yield [
            'not a link',
            static fn(Dom $d) => $d->find('#link')->directText(),
        ];

        yield [
            '<a href="/page2" title="click here">a link.</a> not a link',
            static fn(Dom $d) => $d->find('#link')->innerHtml(),
        ];

        yield [
            '<p id="link"><a href="/page2" title="click here">a link.</a> not a link</p>',
            static fn(Dom $d) => $d->find('#link')->outerHtml(),
        ];

        yield [
            'list 1 list 2 list 3',
            static fn(Dom $d) => $d->findAll('ul')->text(),
        ];

        yield [
            <<<HTML
                <ul>
                    <li>list 1</li>
                    <li>list 2</li>
                </ul>
                <ul>
                    <li>list 3</li>
                </ul>
                HTML,
            static fn(Dom $d) => $d->findAll('ul')->html(),
        ];

        yield [
            'list 1 list 2',
            fn(Dom $d) => $d->find('ul li')->parent()->text(),
        ];

        yield [
            'list 2',
            static fn(Dom $d) => $d->find('ul li')->next()->text(),
        ];

        yield [
            'list 1',
            static fn(Dom $d) => $d->find('ul li:last-child')->previous()->text(),
        ];

        yield [
            'list 1 list 2',
            static fn(Dom $d) => $d->find('ul li')->closest('ul')->text(),
        ];

        yield [
            'div 2 div 5 div 6 p 1',
            static fn(Dom $d) => $d->find('#div3')->siblings()->text(),
        ];
    }

    #[Test]
    public function dump_outputs_document_html(): void
    {
        if ($this->dom()->crawler() instanceof PantherCrawler) {
            $this->markTestSkipped('Dump output uses DomCrawler getNode which is not available in WebDriver mode.');
        }

        $output = $this->captureDumpOutput(fn() => $this->dom()->dump());

        $this->assertStringContainsString('meta title', $output);
    }

    #[Test]
    public function dump_with_selector_outputs_node_html(): void
    {
        if ($this->dom()->crawler() instanceof PantherCrawler) {
            $this->markTestSkipped('Dump output uses DomCrawler getNode which is not available in WebDriver mode.');
        }

        $output = $this->captureDumpOutput(fn() => $this->dom()->dump('h1'));

        $this->assertStringContainsString('h1 title', $output);
    }

    #[Test]
    public function dd_exits_with_output(): void
    {
        if ($this->dom()->crawler() instanceof PantherCrawler) {
            $this->markTestSkipped('DD test uses a subprocess and is not relevant for WebDriver mode.');
        }

        $projectRoot = \dirname(__DIR__);
        $code = 'require "vendor/autoload.php"; $dom = new \\Zenstruck\\Dom(file_get_contents("tests/Fixtures/page.html")); $dom->dd();';
        $process = new Process([\PHP_BINARY, '-r', $code], $projectRoot);

        $process->run();

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString('meta title', $process->getOutput());
    }

    protected function dom(): Dom
    {
        return new Dom(\file_get_contents(__DIR__.'/Fixtures/page.html'));
    }

    private static function normalizeWhitespace(mixed $value): mixed
    {
        return \is_string($value) ? \preg_replace('/\s+/', ' ', $value) : $value;
    }

    private function captureDumpOutput(callable $callback): string
    {
        $output = '';
        $previous = VarDumper::setHandler(static function(mixed $value) use (&$output): void {
            $output .= \is_string($value) ? $value : (string) $value;
        });

        try {
            $callback();
        } finally {
            VarDumper::setHandler($previous);
        }

        return $output;
    }
}
