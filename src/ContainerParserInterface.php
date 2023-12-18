<?php
/**
 * This file is part of the mimmi20/navigation-helper-containerparser package.
 *
 * Copyright (c) 2021-2023, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Mimmi20\NavigationHelper\ContainerParser;

use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation;
use Mimmi20\Mezzio\Navigation\Page\PageInterface;

interface ContainerParserInterface
{
    /**
     * Verifies container and eventually fetches it from service locator if it is a string
     *
     * @param AbstractContainer<AbstractPage>|int|Navigation\ContainerInterface<PageInterface>|string|null $container
     *
     * @return AbstractContainer<AbstractPage>|Navigation\ContainerInterface<PageInterface>|null
     *
     * @throws InvalidArgumentException
     */
    public function parseContainer(
        AbstractContainer | int | Navigation\ContainerInterface | string | null $container = null,
    ): AbstractContainer | Navigation\ContainerInterface | null;
}
