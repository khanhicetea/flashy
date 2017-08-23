<?php

namespace Flashy\Http\Middleware;

use Exception;
use Flashy\Http\Route\Router;
use Flashy\ContainerResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class RoutingMiddlewareStack extends MiddlewareStack
{
    private $routing;
    private $routing_middleware;

    public function __construct(ContainerResolver $resolver, Router $routing)
    {
        parent::__construct($resolver);
        $this->routing = $routing;
        $this->routing_middleware = function (ServerRequestInterface $request, ResponseInterface $response, $next) {
            return $this->getRouting()->dispatch($request, $response);
        };
    }

    abstract public function loadMiddlewares(): void;

    public function getRouting() : Router
    {
        return $this->routing;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next): ResponseInterface
    {
        $bottom = $this->resolve(0);

        $this->pushMiddleware($this->routing_middleware);
        $response = $bottom($request, $response);
        $this->popMiddleware();

        $resolver = $this->getResolver();
        $next_callable = $resolver($next);
        
        return $next_callable($request, $response);
    }
}
