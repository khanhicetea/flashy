<?php
namespace Flashy\Http;

use Exception;
use Flashy\Http\Route\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class MiddlewareStack implements MiddlewareStackInterface
{
    private $routing;
    private $stacks = [];

    public function __construct(Router $routing)
    {
        $this->routing = $routing;
        $this->stacks[] = function (ServerRequestInterface $request, ResponseInterface $response) use ($routing) : ResponseInterface {
            return $routing->dispatch($request, $response);
        };
    }

    abstract public function loadMiddlewares() : void;

    public function addMiddleware(callable $middleware) : MiddlewareStackInterface
    {
        $next = $this->stacks[count($this->stacks) - 1];

        $this->stacks[] = function (ServerRequestInterface $request, ResponseInterface $response) use ($middleware, $next) {
            $result = call_user_func($middleware, $request, $response, $next);
            if ($result instanceof ResponseInterface === false) {
                throw new Exception(
                    'Middleware must return instance of \Psr\Http\Message\ResponseInterface'
                );
            }
            return $result;
        };

        return $this;
    }

    public function getRouting() : Router
    {
        return $this->routing;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) : ResponseInterface
    {
        $middleware = $this->stacks[count($this->stacks) - 1];

        $result = call_user_func($middleware, $request, $response, $next);
        if ($result instanceof ResponseInterface === false) {
            throw new Exception(
                'Middleware must return instance of \Psr\Http\Message\ResponseInterface'
            );
        }
        return $result;
    }
}
