<?php

namespace Flashy\Http\Middleware;

interface MiddlewareStackInterface extends MiddlewareInterface
{
    public function getMiddlewares() : array;

    public function pushMiddleware($middleware): MiddlewareStackInterface;

    public function popMiddleware();
}
