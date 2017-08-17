<?php

namespace Flashy\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel
{
    private $middlewareStack;

    public function __construct(MiddlewareStackInterface $middlewareStack)
    {
        $this->middlewareStack = $middlewareStack;
    }

    public function getMiddlewareStack(): MiddlewareStackInterface
    {
        return $this->middlewareStack;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $response = call_user_func($this->middlewareStack, $request, $response, $next);

        return $next($request, $response);
    }
}
