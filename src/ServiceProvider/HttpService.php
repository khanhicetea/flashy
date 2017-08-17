<?php

namespace Flashy\ServiceProvider;

use function DI\object;
use function DI\factory;
use DI\Scope;
use DI\ContainerBuilder;
use Flashy\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiStreamEmitter;

class HttpService implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder, array $opts = []): void
    {
        $def = array_merge([
            'http.response_class' => Response::class,
        ], $opts);

        $def['http.last_next'] = function (ContainerInterface $c) {
            return function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
                return $response;
            };
        };
        $def['http.request'] = factory(function (ContainerInterface $c) {
            return ServerRequestFactory::fromGlobals();
        });
        $def['http.response'] = factory(function (ContainerInterface $c) {
            $response_class = $c->get('http.response_class');

            return new $response_class();
        })->scope(Scope::PROTOTYPE);
        $def[EmitterInterface::class] = object(SapiStreamEmitter::class);

        $builder->addDefinitions($def);
    }
}
