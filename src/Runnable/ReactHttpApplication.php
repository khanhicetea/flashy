<?php
namespace Flashy\Runnable;

use Exception;
use Psr\Container\ContainerInterface;
use React\Http\Server;

class ReactHttpApplication
{
    private $kernel;

    public function run(ContainerInterface $container) : void
    {
        $server = $container->get(Server::class);
        $server->on('error', function(Exception $e) {
            var_dump($e->getMessage());die;
        });
        $loop = $container->get('http.react_loop');
        $loop->run();
    }
}
