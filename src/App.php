<?php

namespace Flashy;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class App
{
    protected $container = null;
    protected $containerBuilder = null;
    protected $configureFunction = null;
    protected $services = [];

    public function __construct(ContainerBuilder $builder = null)
    {
        if (null === $builder) {
            $builder = new ContainerBuilder();
            $builder->useAutowiring(true)->useAnnotations(false);
        }

        $this->containerBuilder = $builder;
    }

    public function buildContainer(): Container
    {
        if ($this->configureFunction) {
            call_user_func($this->configureFunction, $this->containerBuilder);
        }

        return $this->container = $this->containerBuilder->build();
    }

    public function configureContainerBuilder(callable $func)
    {
        $this->configureFunction = $func;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function register(ServiceProviderInterface $service, array $opts = []): void
    {
        $service->register($this->containerBuilder, $opts);
    }

    public function run($runner)
    {
        $this->buildContainer();

        $this->container->set(ContainerInterface::class, $this->container);
        $this->container->set(Container::class, $this->container);
        $this->container->set(static::class, $this);

        return $this->container->call([$runner, 'run']);
    }
}
