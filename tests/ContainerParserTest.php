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

namespace Mimmi20Test\NavigationHelper\ContainerParser;

use Interop\Container\ContainerInterface;
use Laminas\Navigation\AbstractContainer;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use Mezzio\Navigation\Navigation;
use Mimmi20\NavigationHelper\ContainerParser\ContainerParser;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

use function assert;
use function sprintf;

final class ContainerParserTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
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

        assert($serviceLocator instanceof ContainerInterface);
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
        $this->expectExceptionMessage('Container must be a string alias or an instance of Mezzio\Navigation\ContainerInterface');
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
            ->with(Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(Navigation::class)
            ->willThrowException(new ServiceNotFoundException('test'));

        assert($serviceLocator instanceof ContainerInterface);
        $helper = new ContainerParser($serviceLocator);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Could not load Container "%s"', Navigation::class));
        $this->expectExceptionCode(0);

        $helper->parseContainer('default');
    }

    /**
     * @throws Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringDefaultFound(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::once())
            ->method('has')
            ->with(Navigation::class)
            ->willReturn(true);
        $serviceLocator->expects(self::once())
            ->method('get')
            ->with(Navigation::class)
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
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringNavigationFound(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'navigation';

        $serviceLocator = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], [$name])
            ->willReturnOnConsecutiveCalls(false, true);
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
        $serviceLocator->expects(self::exactly(2))
            ->method('has')
            ->withConsecutive([Navigation::class], ['navigation'])
            ->willReturnOnConsecutiveCalls(false, false);
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function testParseContainerWithStringFound(): void
    {
        $container = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
        $name      = 'Mezzio\\Navigation\\Top';

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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws Exception
     */
    public function testParseContainerWithContainer(): void
    {
        $container      = $this->createMock(\Mezzio\Navigation\ContainerInterface::class);
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
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
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
}
