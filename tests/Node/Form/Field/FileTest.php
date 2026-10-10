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
use Zenstruck\Dom\Node\Form\Field\File;
use Zenstruck\Dom\Selector;

#[CoversClass(File::class)]
final class FileTest extends TestCase
{
    #[Test]
    public function is_multiple_reflects_attribute(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html'));

        $single = $dom->findOrFail(Selector::css('#input5'))->ensure(File::class);
        $multiple = $dom->findOrFail(Selector::css('#input9'))->ensure(File::class);

        $this->assertFalse($single->isMultiple());
        $this->assertTrue($multiple->isMultiple());
    }
}
