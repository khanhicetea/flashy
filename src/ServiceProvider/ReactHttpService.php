<?php

namespace Flashy\ServiceProvider;

use function DI\object;
use function DI\get;
use function DI\factory;
use DI\Scope;
use DI\ContainerBuilder;
use Flashy\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use React\Http\Server;
use React\Socket\Server as SocketServer;
use Flashy\Http\Kernel;
use React\EventLoop\Factory;

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
                $response = call_user_func(
                    $kernel,
                    $request,
                    $container->get('http.response'),
                    $container->get('http.last_next')
                );

                return $response;
            };
        };
        $def['http.last_next'] = function (ContainerInterface $c) {
            return function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
                return $response;
            };
        };
        $def['http.response'] = factory(function (ContainerInterface $c) {
            $response_class = $c->get('http.response_class');

            return new $response_class();
        })->scope(Scope::PROTOTYPE);

        $builder->addDefinitions($def);
    }
}
