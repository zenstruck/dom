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
}
