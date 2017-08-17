<?php

namespace Flashy\Runnable;

use Psr\Container\ContainerInterface;
use Silly\Edition\PhpDi\Application;

class CliApplication
{
    public function run(ContainerInterface $container): void
    {
        $app = $container->get(Application::class);
        $container->call('console.register_func');
        $app->run();
    }
}
