<?php

namespace Flashy\ServiceProvider;

use function DI\object;
use function DI\factory;
use function DI\get;
use DI\Scope;
use DI\ContainerBuilder;
use Flashy\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiStreamEmitter;
use Flashy\Http\Middleware\KernelMiddlewareStack;
use Flashy\Http\Middleware\RoutingMiddlewareStack;
use Flashy\Http\Middleware\ErrorHandler;
use Flashy\Http\Handler\HttpErrorHandler;

class HttpService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []): void
    {
        $def = array_merge([
            'http.response_class' => Response::class,
        ], $opts);

        $def['http.request'] = factory(function (ContainerInterface $c) {
            return ServerRequestFactory::fromGlobals();
        });
        $def['http.response'] = factory(function (ContainerInterface $c) {
            $responseClass = $c->get('http.response_class');

            return new $responseClass();
        })->scope(Scope::PROTOTYPE);
        $def[HttpErrorHandler::class] = object()
            ->constructorParameter('debug', get('debug'));
        $def[KernelMiddlewareStack::class] = object()
            ->method('pushMiddleware', get(ErrorHandler::class))
            ->method('pushMiddleware', get(RoutingMiddlewareStack::class));

        $def[EmitterInterface::class] = object(SapiStreamEmitter::class);

        $builder->addDefinitions($def);
    }
}
