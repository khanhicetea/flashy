<?php

namespace Flashy\Runnable;

use Flashy\Http\Kernel;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response\EmitterInterface;
use function DI\get;

class HttpApplication
{
    private $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function run(ContainerInterface $container): void
    {
        $response = $container->call([Kernel::class, 'run'], [
            get('http.request'),
            get('http.response')
        ]);

        $container->get(EmitterInterface::class)->emit($response);
    }
}
