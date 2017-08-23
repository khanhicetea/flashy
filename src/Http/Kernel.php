<?php

namespace Flashy\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Flashy\Http\Middleware\KernelMiddlewareStack;
use Flashy\Http\Middleware\DoNothing;

class Kernel
{
    private $kernelStack;
    private $doNothing;

    public function __construct(KernelMiddlewareStack $kernelStack)
    {
        $this->kernelStack = $kernelStack;
        $this->doNothing = function (ServerRequestInterface $request, ResponseInterface $response) {
            return $response;
        };
    }

    public function run(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return call_user_func($this->kernelStack, $request, $response, $this->doNothing);
    }
}
