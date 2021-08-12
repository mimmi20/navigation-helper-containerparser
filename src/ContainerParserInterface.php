<?php
/**
 * This file is part of the mimmi20/navigation-helper-containerparser package.
 *
 * Copyright (c) 2020-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\NavigationHelper\ContainerParser;

use Laminas\Navigation\AbstractContainer;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Mezzio\Navigation;

interface ContainerParserInterface
{
    /**
     * Verifies container and eventually fetches it from service locator if it is a string
     *
     * @param AbstractContainer|int|Navigation\ContainerInterface|string|null $container
     *
     * @return AbstractContainer|Navigation\ContainerInterface|null
     *
     * @throws InvalidArgumentException
     */
    public function parseContainer($container = null);
}
