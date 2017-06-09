<?php
namespace Flashy\Http;

use Flashy\Http\Route\UrlGenerator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

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

    public function get($key)
    {
        return $this->container->get($key);
    }

    public function __call($method, $args)
    {
        $this->request = array_shift($args);
        $this->response = array_shift($args);

        return call_user_func_array([$this, $method], $args);
    }

    protected function json(array $data, ResponseInterface $response = null) : ResponseInterface
    {
        $response = $response ?: $this->response;
        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json');
    }

    protected function render(string $template, array $data, ResponseInterface $response = null) : ResponseInterface
    {
        $response = $response ?: $this->response;
        $response->getBody()->write($this->twig->render($template, $data));

        return $response;
    }

    protected function redirectRoute(string $to,
        array $routeData = [],
        array $queryParams = [],
        $status_code = 302,
        ResponseInterface $response = null) : ResponseInterface
    {
        $url = $this->get(UrlGenerator::class)->pathFor($to, $routeData, $queryParams);
        return $this->redirect($url, $status_code, $response);
    }

    protected function redirect($to, $status_code = 302, ResponseInterface $response = null) : ResponseInterface
    {
        $response = $response ?: $this->response;
        return $response->withStatus($status_code)->withHeader('Location', $to);
    }
}
