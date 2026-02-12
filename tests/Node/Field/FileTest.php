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
use Zenstruck\Dom\Node\Form\Field\File;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestSession;

#[CoversClass(File::class)]
final class FileTest extends TestCase
{
    #[Test]
    public function is_multiple_reflects_attribute(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'));

        $single = $dom->findOrFail(Selector::css('#input5'))->ensure(File::class);
        $multiple = $dom->findOrFail(Selector::css('#input9'))->ensure(File::class);

        $this->assertFalse($single->isMultiple());
        $this->assertTrue($multiple->isMultiple());
    }

    #[Test]
    public function attach_throws_when_multiple_files_on_single(): void
    {
        $session = new TestSession();
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'), $session);
        $file = $dom->findOrFail(Selector::css('#input5'))->ensure(File::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot attach multiple files to a non-multiple file input.');

        $file->attach(__FILE__, __FILE__);
    }

    #[Test]
    public function attach_throws_when_file_missing(): void
    {
        $session = new TestSession();
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'), $session);
        $file = $dom->findOrFail(Selector::css('#input5'))->ensure(File::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File "/path/does/not/exist" does not exist.');

        $file->attach('/path/does/not/exist');
    }

    #[Test]
    public function attach_calls_session_for_valid_files(): void
    {
        $session = new TestSession();
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'), $session);
        $file = $dom->findOrFail(Selector::css('#input9'))->ensure(File::class);

        $tmp = \tempnam(\sys_get_temp_dir(), 'dom-file-');
        $this->assertNotFalse($tmp);

        $file->attach($tmp);

        $this->assertCount(1, $session->attachments);
        $this->assertSame($tmp, $session->attachments[0][1][0]);

        @\unlink($tmp);
    }
}
