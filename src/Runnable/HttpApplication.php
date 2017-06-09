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

    public function run(ContainerInterface $container) : void
    {
        $response = $container->call(Kernel::class, [
            get('http.request'),
            get('http.response'),
            get('http.last_next'),
        ]);

        $container->get(EmitterInterface::class)->emit($response);
    }
}
