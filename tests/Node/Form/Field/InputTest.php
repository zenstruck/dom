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
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestSession;

#[CoversClass(Input::class)]
final class InputTest extends TestCase
{
    #[Test]
    public function type_defaults_to_button(): void
    {
        $dom = new Dom('<form><input name="no_type" value="v"></form>');
        $input = $dom->findOrFail(Selector::css('input'))->ensure(Input::class);

        $this->assertSame('button', $input->type());
    }

    #[Test]
    public function fill_calls_session(): void
    {
        $session = new TestSession();
        $input = $this->dom($session)->findOrFail(Selector::css('#input1'))->ensure(Input::class);

        $input->fill('updated');

        $this->assertCount(1, $session->fills);
        $this->assertSame('updated', $session->fills[0][1]);
    }

    private function dom(?TestSession $session = null): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html'), $session);
    }
}
