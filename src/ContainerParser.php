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
use Mezzio\Navigation;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function get_debug_type;
use function in_array;
use function is_string;
use function sprintf;

final class ContainerParser implements ContainerParserInterface
{
    /** @throws void */
    public function __construct(private readonly ContainerInterface $serviceLocator)
    {
        // nothing to do
    }

    /**
     * Verifies container and eventually fetches it from service locator if it is a string
     *
     * @param AbstractContainer<AbstractPage>|int|Navigation\ContainerInterface|string|null $container
     *
     * @return AbstractContainer<AbstractPage>|Navigation\ContainerInterface|null
     *
     * @throws InvalidArgumentException
     */
    public function parseContainer(
        AbstractContainer | int | Navigation\ContainerInterface | string | null $container = null,
    ): AbstractContainer | Navigation\ContainerInterface | null {
        if (
            $container === null
            || $container instanceof Navigation\ContainerInterface
            || $container instanceof AbstractContainer
        ) {
            return $container;
        }

        if (is_string($container)) {
            // Fallback
            if (in_array($container, ['default', 'navigation'], true)) {
                // Uses class name
                if ($this->serviceLocator->has(Navigation\Navigation::class)) {
                    try {
                        $container = $this->serviceLocator->get(Navigation\Navigation::class);
                    } catch (ContainerExceptionInterface $e) {
                        throw new InvalidArgumentException(
                            sprintf('Could not load Container "%s"', Navigation\Navigation::class),
                            0,
                            $e,
                        );
                    }

                    assert($container instanceof Navigation\ContainerInterface);

                    return $container;
                }

                // Uses old service name
                if ($this->serviceLocator->has('navigation')) {
                    try {
                        $container = $this->serviceLocator->get('navigation');
                    } catch (ContainerExceptionInterface $e) {
                        throw new InvalidArgumentException(
                            'Could not load Container "navigation"',
                            0,
                            $e,
                        );
                    }

                    assert(
                        $container instanceof Navigation\ContainerInterface || $container instanceof AbstractContainer,
                        sprintf(
                            '$container should be an Instance of %s or %s, but was %s',
                            Navigation\ContainerInterface::class,
                            AbstractContainer::class,
                            get_debug_type($container),
                        ),
                    );

                    return $container;
                }
            }

            /*
             * Load the navigation container from the root service locator
             */
            try {
                $container = $this->serviceLocator->get($container);
            } catch (ContainerExceptionInterface $e) {
                throw new InvalidArgumentException(
                    sprintf('Could not load Container "%s"', $container),
                    0,
                    $e,
                );
            }

            assert(
                $container instanceof Navigation\ContainerInterface || $container instanceof AbstractContainer,
                sprintf(
                    '$container should be an Instance of %s or %s, but was %s',
                    Navigation\ContainerInterface::class,
                    AbstractContainer::class,
                    get_debug_type($container),
                ),
            );

            return $container;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Container must be a string alias or an instance of %s or an instance of %s',
                Navigation\ContainerInterface::class,
                AbstractContainer::class,
            ),
        );
    }
}
