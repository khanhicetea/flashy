<?php
namespace Flashy\Http;

use Psr\Container\ContainerInterface;

abstract class Controller
{
    protected $container;
    protected $request;
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __get($key)
    {
        return $this->container->get($key);
    }

    public function __call($method, $args)
    {
        $this->request = array_shift($args);
        $this->response = array_shift($args);

        return call_user_func_array([$this, $method], $args);
    }
}
