<?php

namespace Flashy\ServiceProvider;

use function DI\object;
use function DI\get;
use function DI\factory;
use DI\Scope;
use DI\ContainerBuilder;
use Flashy\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use React\Http\Server;
use React\Socket\Server as SocketServer;
use Flashy\Http\Kernel;
use React\EventLoop\Factory;
use Flashy\Http\Middleware\KernelMiddlewareStack;
use Flashy\Http\Middleware\RoutingMiddlewareStack;
use Flashy\Http\Middleware\ErrorHandler;
use Flashy\Http\Handler\HttpErrorHandler;

class ReactHttpService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []): void
    {
        $def = array_merge([
            'http.response_class' => Response::class,
            'http.react_port' => 8080,
        ], $opts);

        $def[Server::class] = object()
            ->constructor(get('http.react_kernel'))
            ->method('listen', get('http.react_socket_server'));
        $def['http.react_loop'] = function (ContainerInterface  $container) {
            return Factory::create();
        };
        $def['http.react_socket_server'] = function (ContainerInterface $container) {
            return new SocketServer($container->get('http.react_port'), $container->get('http.react_loop'));
        };
        $def['http.react_kernel'] = function (ContainerInterface $container) {
            $kernel = $container->get(Kernel::class);

            return function (ServerRequestInterface $request) use ($container, $kernel) {
                $response = $kernel->run(
                    $request,
                    $container->get('http.response')
                );

                return $response;
            };
        };
        $def['http.response'] = factory(function (ContainerInterface $c) {
            $response_class = $c->get('http.response_class');

            return new $response_class();
        })->scope(Scope::PROTOTYPE);
        $def[HttpErrorHandler::class] = object()
            ->constructorParameter('debug', get('debug'));
        $def[KernelMiddlewareStack::class] = object()
            ->method('pushMiddleware', get(ErrorHandler::class))
            ->method('pushMiddleware', get(RoutingMiddlewareStack::class));

        $builder->addDefinitions($def);
    }
}
