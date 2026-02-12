<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom\Node\Attributes;

#[CoversClass(Attributes::class)]
final class AttributesTest extends TestCase
{
    /**
     * @param string[] $expected
     */
    #[Test]
    #[DataProvider('provideClassCases')]
    public function classes(string $classes, array $expected): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $element->setAttribute('class', $classes);

        $attributes = new Attributes($element);
        $this->assertSame($expected, $attributes->classes());
    }

    /**
     * @return iterable<array{0: string, 1: string[]}>
     */
    public static function provideClassCases(): iterable
    {
        yield 'no class' => [
            '',
            [],
        ];
        yield 'single class' => [
            'foo',
            ['foo'],
        ];
        yield 'multiple classes' => [
            'foo bar baz',
            ['foo', 'bar', 'baz'],
        ];
        yield 'multiple classes with extra spaces' => [
            '  foo   bar  baz  ',
            ['foo', 'bar', 'baz'],
        ];
    }

    #[Test]
    public function all(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $element->setAttribute('foo', 'bar');
        $element->setAttribute('baz', 'qux');

        $attributes = new Attributes($element);
        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $attributes->all());
    }

    #[Test]
    public function countable(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $element->setAttribute('foo', 'bar');
        $element->setAttribute('baz', 'qux');

        $attributes = new Attributes($element);
        $this->assertCount(2, $attributes);
        $this->assertEquals(2, $attributes->count());
    }

    #[Test]
    public function has_returns_true_when_attribute_exists(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $element->setAttribute('foo', 'bar');

        $attributes = new Attributes($element);
        $this->assertTrue($attributes->has('foo'));
        $this->assertFalse($attributes->has('baz'));
    }

    #[Test]
    public function get_returns_null_when_attribute_missing(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $attributes = new Attributes($element);

        $this->assertNull($attributes->get('nonexistent'));
    }

    #[Test]
    public function get_returns_value_when_attribute_exists(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $element->setAttribute('foo', 'bar');

        $attributes = new Attributes($element);
        $this->assertSame('bar', $attributes->get('foo'));
    }

    #[Test]
    public function is_returns_false_when_attribute_missing(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $attributes = new Attributes($element);

        $this->assertFalse($attributes->is('type', 'text'));
    }

    #[Test]
    public function is_matches_single_value(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $element->setAttribute('type', 'text');

        $attributes = new Attributes($element);
        $this->assertTrue($attributes->is('type', 'text'));
        $this->assertFalse($attributes->is('type', 'checkbox'));
    }

    #[Test]
    public function is_matches_one_of_multiple_values(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $element->setAttribute('type', 'submit');

        $attributes = new Attributes($element);
        $this->assertTrue($attributes->is('type', 'button', 'submit', 'reset'));
        $this->assertFalse($attributes->is('type', 'text', 'checkbox', 'radio'));
    }

    #[Test]
    public function is_comparison_is_case_insensitive(): void
    {
        $element = (new \DOMDocument())->createElement('test');
        $element->setAttribute('type', 'TEXT');

        $attributes = new Attributes($element);
        $this->assertTrue($attributes->is('type', 'text'));
        $this->assertTrue($attributes->is('type', 'Text'));
    }
}
