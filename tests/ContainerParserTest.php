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

namespace Mimmi20Test\NavigationHelper\ContainerParser;

use AssertionError;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Navigation as LaminasNavigation;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Mimmi20\Mezzio\Navigation\Navigation as MezzioNavigation;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParser;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function assert;
use function sprintf;

final class ContainerParserTest extends TestCase
{
    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithNull(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        $helper = new ContainerParser($serviceLocator);

        self::assertNull($helper->parseContainer(null));
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testParseContainerWithNumber(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Container must be a string alias or an instance of %s or an instance of %s',
                \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
                AbstractContainer::class,
            ),
        );
        $this->expectExceptionCode(0);

        $helper->parseContainer(1);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultNotFound(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(MezzioNavigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(MezzioNavigation::class)
            ->willThrowException(new ServiceNotFoundException('test'));

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Could not load Container "%s"', MezzioNavigation::class),
        );
        $this->expectExceptionCode(0);

        $helper->parseContainer('default');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultNotFound2(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(MezzioNavigation::class, $id),
                        default => self::assertSame(LaminasNavigation::class, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => true,
                        default => false,
                    };
                },
            );
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(LaminasNavigation::class)
            ->willThrowException(new ServiceNotFoundException('test'));

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Could not load Container "%s"', LaminasNavigation::class),
        );
        $this->expectExceptionCode(0);

        $helper->parseContainer('default');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultFound(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(MezzioNavigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(MezzioNavigation::class)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer('default'));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultFound2(): void
    {
        $container = $this->createMock(AbstractContainer::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(MezzioNavigation::class, $id),
                        default => self::assertSame(LaminasNavigation::class, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => true,
                        default => false,
                    };
                },
            );
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(LaminasNavigation::class)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer('default'));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringNavigationNotFound(): void
    {
        $name = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(3);
        $serviceLocator->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $name): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(MezzioNavigation::class, $id),
                        2 => self::assertSame(LaminasNavigation::class, $id),
                        default => self::assertSame($name, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        3 => true,
                        default => false,
                    };
                },
            );
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', $name));
        $this->expectExceptionCode(0);

        $helper->parseContainer($name);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringNavigationFound(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(3);
        $serviceLocator->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $name): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(MezzioNavigation::class, $id),
                        2 => self::assertSame(LaminasNavigation::class, $id),
                        default => self::assertSame($name, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        3 => true,
                        default => false,
                    };
                },
            );
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($name));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringNavigationFound2(): void
    {
        $container = $this->createMock(AbstractContainer::class);
        $name      = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(3);
        $serviceLocator->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $name): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(MezzioNavigation::class, $id),
                        2 => self::assertSame(LaminasNavigation::class, $id),
                        default => self::assertSame($name, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        3 => true,
                        default => false,
                    };
                },
            );
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($name));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultAndNavigationNotFound(): void
    {
        $name = 'default';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(3);
        $serviceLocator->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(MezzioNavigation::class, $id),
                        2 => self::assertSame(LaminasNavigation::class, $id),
                        default => self::assertSame('navigation', $id),
                    };

                    return false;
                },
            );
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willThrowException(new ServiceNotFoundException('test'));

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', $name));
        $this->expectExceptionCode(0);

        $helper->parseContainer($name);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringFound(): void
    {
        $container = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mimmi20\\Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($name));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringFound2(): void
    {
        $container = $this->createMock(AbstractContainer::class);
        $name      = 'Mimmi20\\Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn($container);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($name));
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testParseContainerWithContainer(): void
    {
        $container      = $this->createMock(\Mimmi20\Mezzio\Navigation\ContainerInterface::class);
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($container));
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function testParseContainerWithContainer2(): void
    {
        $container      = $this->createMock(AbstractContainer::class);
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::never())
            ->method('get');

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        self::assertSame($container, $helper->parseContainer($container));
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultNotFound3(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(MezzioNavigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(MezzioNavigation::class)
            ->willReturn(null);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            sprintf(
                '$container should be an Instance of %s, but was %s',
                \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
                'null',
            ),
        );

        $helper->parseContainer('default');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultNotFound4(): void
    {
        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(2);
        $serviceLocator->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(MezzioNavigation::class, $id),
                        default => self::assertSame(LaminasNavigation::class, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        2 => true,
                        default => false,
                    };
                },
            );
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(LaminasNavigation::class)
            ->willReturn(null);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            sprintf(
                '$container should be an Instance of %s, but was %s',
                AbstractContainer::class,
                'null',
            ),
        );

        $helper->parseContainer('default');
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringNavigationNotFound2(): void
    {
        $name = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher        = self::exactly(3);
        $serviceLocator->expects($matcher)
            ->method('has')
            ->willReturnCallback(
                static function (string $id) use ($matcher, $name): bool {
                    match ($matcher->numberOfInvocations()) {
                        1 => self::assertSame(MezzioNavigation::class, $id),
                        2 => self::assertSame(LaminasNavigation::class, $id),
                        default => self::assertSame($name, $id),
                    };

                    return match ($matcher->numberOfInvocations()) {
                        3 => true,
                        default => false,
                    };
                },
            );
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn(null);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            sprintf(
                '$container should be an Instance of %s or %s, but was %s',
                \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
                AbstractContainer::class,
                'null',
            ),
        );

        $helper->parseContainer($name);
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringNotFound(): void
    {
        $name = 'Mimmi20\\Mezzio\\Navigation\\Top';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::never())
            ->method('has');
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with($name)
            ->willReturn(null);

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(AssertionError::class);
        $this->expectExceptionCode(1);
        $this->expectExceptionMessage(
            sprintf(
                '$container should be an Instance of %s or %s, but was %s',
                \Mimmi20\Mezzio\Navigation\ContainerInterface::class,
                AbstractContainer::class,
                'null',
            ),
        );

        $helper->parseContainer($name);
    }
}
