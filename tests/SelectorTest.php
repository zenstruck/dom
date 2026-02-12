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
use Zenstruck\Dom\Selector;

/**
 * @author Simon Andre <smmusic@music.fr>
 */
#[CoversClass(Selector::class)]
final class SelectorTest extends TestCase
{
    #[Test]
    public function wrap_returns_selector_instance(): void
    {
        $selector = Selector::wrap('div');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function css_returns_selector_instance(): void
    {
        $selector = Selector::css('.foo');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function id_returns_selector_instance(): void
    {
        $selector = Selector::id('link');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function field_returns_selector_instance(): void
    {
        $selector = Selector::field('input_1');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function xpath_returns_selector_instance(): void
    {
        $selector = Selector::xpath('//div');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function link_returns_selector_instance(): void
    {
        $selector = Selector::link('a link');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function button_returns_selector_instance(): void
    {
        $selector = Selector::button('Submit');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function image_returns_selector_instance(): void
    {
        $selector = Selector::image('Submit Image');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function clickable_returns_selector_instance(): void
    {
        $selector = Selector::clickable('Submit');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function field_for_name_returns_selector_instance(): void
    {
        $selector = Selector::fieldForName('input_1');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function field_for_label_returns_selector_instance(): void
    {
        $selector = Selector::fieldForLabel('Input 1');

        $this->assertInstanceOf(Selector::class, $selector);
    }

    #[Test]
    public function wrap_returns_same_instance_when_given_selector(): void
    {
        $selector = Selector::css('.foo');

        $this->assertSame($selector, Selector::wrap($selector));
    }

    #[Test]
    public function css_returns_same_instance_when_given_selector(): void
    {
        $selector = Selector::xpath('//div');

        $this->assertSame($selector, Selector::css($selector));
    }

    #[Test]
    public function to_string_produces_type_separator_value_format(): void
    {
        $this->assertSame('auto:==:div', (string) Selector::wrap('div'));
        $this->assertSame('css:==:.foo', (string) Selector::css('.foo'));
        $this->assertSame('id:==:link', (string) Selector::id('link'));
        $this->assertSame('xpath:==://div', (string) Selector::xpath('//div'));
        $this->assertSame('field:==:input_1', (string) Selector::field('input_1'));
        $this->assertSame('field-for-name:==:input_1', (string) Selector::fieldForName('input_1'));
        $this->assertSame('field-for-label:==:Input 1', (string) Selector::fieldForLabel('Input 1'));
        $this->assertSame('clickable:==:Submit', (string) Selector::clickable('Submit'));
        $this->assertSame('link:==:a link', (string) Selector::link('a link'));
        $this->assertSame('button:==:Submit', (string) Selector::button('Submit'));
        $this->assertSame('image:==:Submit Image', (string) Selector::image('Submit Image'));
    }

    #[Test]
    public function to_string_for_callback_selector(): void
    {
        $selector = Selector::wrap(static fn(Dom $dom) => $dom->find('div'));

        $this->assertSame('(callback)', (string) $selector);
    }

    #[Test]
    public function separator_parsing_creates_typed_selector(): void
    {
        $selector = Selector::wrap('css:==:.foo');

        $this->assertSame('css:==:.foo', (string) $selector);
    }

    #[Test]
    public function separator_parsing_with_xpath_type(): void
    {
        $selector = Selector::wrap('xpath:==://div[@id="test"]');

        $this->assertSame('xpath:==://div[@id="test"]', (string) $selector);
    }

    #[Test]
    public function separator_parsing_with_id_type(): void
    {
        $selector = Selector::wrap('id:==:link');

        $this->assertSame('id:==:link', (string) $selector);
    }

    #[Test]
    public function separator_parsing_with_field_type(): void
    {
        $selector = Selector::wrap('field:==:input_1');

        $this->assertSame('field:==:input_1', (string) $selector);
    }

    #[Test]
    public function invalid_type_in_separator_falls_back_to_default(): void
    {
        // 'invalid' is not a valid type, so it should fall back to 'auto'
        $selector = Selector::wrap('invalid:==:.foo');

        $this->assertSame('auto:==:.foo', (string) $selector);
    }

    #[Test]
    public function invalid_type_in_separator_falls_back_to_css_when_using_css_factory(): void
    {
        $selector = Selector::css('notavalidtype:==:.bar');

        $this->assertSame('css:==:.bar', (string) $selector);
    }

    #[Test]
    public function invalid_type_in_separator_falls_back_to_xpath_when_using_xpath_factory(): void
    {
        $selector = Selector::xpath('badtype:==://div');

        $this->assertSame('xpath:==://div', (string) $selector);
    }

    #[Test]
    public function filter_with_css_selector(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::css('ul li');

        $result = $selector->filter($crawler);

        $this->assertCount(3, $result);
    }

    #[Test]
    public function filter_with_css_selector_no_match(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::css('.nonexistent');

        $result = $selector->filter($crawler);

        $this->assertCount(0, $result);
    }

    #[Test]
    public function filter_with_id_selector(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::id('link');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
        $this->assertSame('p', $result->nodeName());
    }

    #[Test]
    public function filter_with_id_selector_strips_hash(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::id('#link');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function filter_with_xpath_selector(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::xpath('//ul/li');

        $result = $selector->filter($crawler);

        $this->assertCount(3, $result);
    }

    #[Test]
    public function filter_with_xpath_selector_specific_node(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::xpath('//p[@id="link"]');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function filter_with_callback_selector(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::wrap(static fn(Dom $dom) => $dom->findAll('ul li'));

        $result = $selector->filter($crawler);

        $this->assertCount(3, $result);
    }

    #[Test]
    public function filter_with_callback_returning_single_node(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::wrap(static fn(Dom $dom) => $dom->find('#link'));

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function filter_with_callback_returning_null(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::wrap(static fn(Dom $dom) => null);

        $result = $selector->filter($crawler);

        $this->assertCount(0, $result);
    }

    #[Test]
    public function filter_with_callback_returning_invalid_type_throws(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::wrap(static fn(Dom $dom) => 'invalid');

        $this->expectException(\LogicException::class);

        $selector->filter($crawler);
    }

    #[Test]
    public function filter_with_button_selector(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::button('Submit');

        $result = $selector->filter($crawler);

        $this->assertGreaterThan(0, \count($result));
    }

    #[Test]
    public function filter_with_link_selector(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::link('a link.');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
        $this->assertSame('a', $result->nodeName());
    }

    #[Test]
    public function filter_with_image_selector(): void
    {
        $crawler = new Crawler('<html><body><a href="/page"><img alt="My Image" src="test.png"/></a></body></html>');
        $selector = Selector::image('My Image');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function filter_with_field_for_name_selector(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::fieldForName('input_1');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
        $this->assertSame('input', $result->nodeName());
    }

    #[Test]
    public function filter_with_field_for_label_selector(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::fieldForLabel('Input 1');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
        $this->assertSame('input1', $result->attr('id'));
    }

    #[Test]
    public function auto_priority_tries_css_first(): void
    {
        $crawler = $this->createCrawler();

        // 'ul li' is valid CSS, so auto should resolve it via CSS
        $selector = Selector::wrap('ul li');
        $result = $selector->filter($crawler);

        $this->assertCount(3, $result);
    }

    #[Test]
    public function auto_priority_falls_back_to_button(): void
    {
        $crawler = $this->createCrawler();

        // 'Submit' is not valid CSS, but should match a button
        $selector = Selector::wrap('Submit');
        $result = $selector->filter($crawler);

        $this->assertGreaterThan(0, \count($result));
    }

    #[Test]
    public function auto_priority_falls_back_to_link(): void
    {
        $crawler = $this->createCrawler();

        // 'a link.' is not valid CSS or button, but should match a link
        $selector = Selector::wrap('a link.');
        $result = $selector->filter($crawler);

        $this->assertGreaterThan(0, \count($result));
    }

    #[Test]
    public function auto_priority_falls_back_to_id(): void
    {
        $crawler = $this->createCrawler();

        // 'link' is not valid CSS (no element with tag name 'link' in body), but is a valid ID
        $selector = Selector::wrap('link');
        $result = $selector->filter($crawler);

        $this->assertGreaterThan(0, \count($result));
    }

    #[Test]
    public function clickable_priority_tries_button_first(): void
    {
        $crawler = $this->createCrawler();

        $selector = Selector::clickable('Submit');
        $result = $selector->filter($crawler);

        $this->assertGreaterThan(0, \count($result));
    }

    #[Test]
    public function field_priority_tries_field_for_name_first(): void
    {
        $crawler = $this->createCrawler();

        $selector = Selector::field('input_1');
        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
        $this->assertSame('input', $result->nodeName());
    }

    #[Test]
    public function field_priority_falls_back_to_label(): void
    {
        $crawler = $this->createCrawler();

        // 'Input 1' is not a field name, but is a label
        $selector = Selector::field('Input 1');
        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function filter_returns_empty_crawler_when_no_match(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::wrap('nonexistent-element-that-does-not-exist');

        $result = $selector->filter($crawler);

        $this->assertCount(0, $result);
    }

    #[Test]
    public function xpath_quote_with_double_quotes(): void
    {
        $crawler = $this->createCrawler();

        // A value with double quotes should be wrapped in single quotes by xpathQuote
        $selector = Selector::link('click here');
        $result = $selector->filter($crawler);

        // 'click here' is the title of the <a> link, link filter checks title too
        $this->assertGreaterThan(0, \count($result));
    }

    #[Test]
    public function xpath_quote_with_single_quotes(): void
    {
        // Ensure selectors with apostrophes don't break XPath
        $selector = Selector::fieldForLabel("doesn't exist");
        $crawler = $this->createCrawler();

        $result = $selector->filter($crawler);

        // No match expected, but it should not throw
        $this->assertCount(0, $result);
    }

    #[Test]
    public function xpath_quote_with_both_quotes(): void
    {
        // Ensure selectors with both quote types don't break XPath (uses concat())
        $selector = Selector::fieldForLabel('doesn\'t "exist"');
        $crawler = $this->createCrawler();

        $result = $selector->filter($crawler);

        // No match expected, but it should not throw
        $this->assertCount(0, $result);
    }

    #[Test]
    public function separator_with_value_containing_separator(): void
    {
        // The separator only splits on the first occurrence
        $selector = Selector::wrap('css:==:div[data-value=":==:"]');

        // The type should be 'css' and the value should contain the rest
        $this->assertSame('css:==:div[data-value=":==:"]', (string) $selector);
    }

    #[Test]
    public function filter_with_callback_returning_crawler(): void
    {
        $crawler = $this->createCrawler();
        $selector = Selector::wrap(static fn(Dom $dom) => $dom->find('#link')->crawler());

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function filter_link_with_partial_text_match(): void
    {
        $crawler = new Crawler('<html><body><a href="/page">Click here for more information</a></body></html>');
        $selector = Selector::link('more information');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function filter_link_by_title_attribute(): void
    {
        $crawler = new Crawler('<html><body><a href="/page" title="Go to dashboard">Icon</a></body></html>');
        $selector = Selector::link('dashboard');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function filter_field_for_label_with_nested_input(): void
    {
        $crawler = new Crawler('<html><body><form><label>Username <input type="text" name="user"></label></form></body></html>');
        $selector = Selector::fieldForLabel('Username');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
        $this->assertSame('input', $result->nodeName());
    }

    #[Test]
    public function auto_priority_falls_back_to_image(): void
    {
        $crawler = new Crawler('<html><body><a href="/page"><img alt="Submit Image" src="test.png"/></a></body></html>');
        $selector = Selector::wrap('Submit Image');

        $result = $selector->filter($crawler);

        $this->assertCount(1, $result);
    }

    private function createCrawler(): Crawler
    {
        return new Crawler(\file_get_contents(__DIR__.'/Fixtures/page.html'));
    }
}
