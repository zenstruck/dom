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

use PHPUnit\Framework\TestCase;
use Zenstruck\Dom\Node\Attributes;

/**
 * @covers \Zenstruck\Dom\Node\Attributes
 */
final class AttributesTest extends TestCase
{
    /**
     * @param string[] $expected
     *
     * @dataProvider provideClassCases
     *
     * @test
     */
    public function classes(string $classes, array $expected): void
    {
        $element = new \DOMElement('test');
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

    /**
     * @test
     */
    public function all(): void
    {
        $element = new \DOMElement('test');
        $element->setAttribute('foo', 'bar');
        $element->setAttribute('baz', 'qux');

        $attributes = new Attributes($element);
        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $attributes->all());
    }

    /**
     * @test
     */
    public function countable(): void
    {
        $element = new \DOMElement('test');
        $element->setAttribute('foo', 'bar');
        $element->setAttribute('baz', 'qux');

        $attributes = new Attributes($element);
        $this->assertCount(2, $attributes);
        $this->assertEquals(2, $attributes->count());
    }
}
