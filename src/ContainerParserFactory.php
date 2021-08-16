<?php
/**
 * This file is part of the mimmi20/navigation-helper-containerparser package.
 *
 * Copyright (c) 2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\NavigationHelper\ContainerParser;

use Interop\Container\ContainerInterface;

final class ContainerParserFactory
{
    /**
     * Create and return a navigation view helper instance.
     */
    public function __invoke(ContainerInterface $container): ContainerParser
    {
        return new ContainerParser($container);
    }
}
