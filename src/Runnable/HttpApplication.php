<?php
namespace Flashy\Runnable;
use DI\Container;
use Flashy\Http\Kernel;
use Zend\Diactoros\Response\EmitterInterface;
use function DI\get;

class HttpApplication {
    private $kernel;

    public function __construct(Kernel $kernel) {
        $this->kernel = $kernel;
    }

    public function run(Container $container) : void {
        $response = $container->call(Kernel::class, [
            get('http.request'),
            get('http.response'),
            get('http.last_next'),
        ]);

        $container->get(EmitterInterface::class)->emit($response);
    }
}