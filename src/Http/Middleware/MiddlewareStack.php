<?php

namespace Flashy\Http\Middleware;

use Exception;
use Flashy\ContainerResolver;
use Flashy\Http\Route\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class MiddlewareStack implements MiddlewareStackInterface
{
    private $resolver;
    private $stack = [];

    public function __construct(ContainerResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function getMiddlewares() : array
    {
        return $this->stack;
    }

    public function pushMiddleware($middleware): MiddlewareStackInterface
    {
        $this->stack[] = $middleware;
        return $this;
    }

    public function popMiddleware()
    {
        return array_pop($this->stack);
    }

    public function getResolver(): ContainerResolver
    {
        return $this->resolver;
    }

    public function getRouting(): Router
    {
        return $this->routing;
    }

    public function resolve($index) : callable
    {
        return function (ServerRequestInterface $request, ResponseInterface $response) use ($index) : ResponseInterface {
            if (empty($this->stack[$index])) {
                return $response;
            }
            
            $resolver = $this->getResolver();
            $callable = $resolver($this->stack[$index]);

            return $callable($request, $response, $this->resolve($index + 1));
        };
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next): ResponseInterface
    {
        $bottom = $this->resolve(0);
        $response = $bottom($request, $response);

        $resolver = $this->getResolver();
        $next_callable = $resolver($next);
        
        return $next_callable($request, $response);
    }
}
