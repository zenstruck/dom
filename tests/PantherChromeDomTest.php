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

use Symfony\Component\Panther\PantherTestCaseTrait;
use Zenstruck\Dom;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @group panther
 */
final class PantherChromeDomTest extends DomTest
{
    use PantherTestCaseTrait;

    protected function dom(): Dom
    {
        $client = self::createPantherClient([
            'browser' => 'chrome',
            'webServerDir' => __DIR__.'/Fixtures',
        ]);
        $client->get('/page.html');

        return new Dom($client->getCrawler());
    }
}
