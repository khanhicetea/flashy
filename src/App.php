<?php

namespace Flashy;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class App
{
    protected $container = null;
    protected $container_builder = null;
    protected $configure_function = null;
    protected $services = [];

    public function __construct(ContainerBuilder $builder = null)
    {
        if (null === $builder) {
            $builder = new ContainerBuilder();
            $builder->useAutowiring(true)->useAnnotations(false);
        }

        $this->container_builder = $builder;
    }

    public function buildContainer(): Container
    {
        if ($this->configure_function) {
            call_user_func($this->configure_function, $this->container_builder);
        }

        return $this->container = $this->container_builder->build();
    }

    public function configureContainerBuilder(callable $func)
    {
        $this->configure_function = $func;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function register(ServiceProviderInterface $service, array $opts = []): void
    {
        $service->register($this->container_builder, $opts);
    }

    public function run($runner)
    {
        $this->buildContainer();

        $this->container->set(ContainerInterface::class, $this->container);
        $this->container->set(Container::class, $this->container);

        return $this->container->call([$runner, 'run']);
    }
}
