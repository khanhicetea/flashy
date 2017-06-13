<?php
namespace Flashy\Http\Route;

use Exception;

class UrlGenerator
{
    private $router;
    private $parsedCache;

    public function __construct(Router $router)
    {
        $this->router = $router;
        $this->parsedCache = [];
    }

    public function pathFor($namedRoute, array $parameters = [], array $queryParams = []) : string
    {
        $url = isset($parameters[0]) ? $this->generateDataList($namedRoute, $parameters) : $this->generateDataAssoc($namedRoute, $parameters);

        if ($queryParams) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    private function parseRoutePattern(string $namedRoute) : array
    {
        if (!array_key_exists($namedRoute, $this->parsedCache)) {
            $route = $this->router->getNamedRoute($namedRoute);
            $pattern = $route->getPath();
            $this->parsedCache[$namedRoute] = $this->router->getRouteParser()->parse($pattern);
        }

        return $this->parsedCache[$namedRoute];
    }

    // Ref : Router from Slim3 framework
    protected function generateDataAssoc($namedRoute, array $data = []) : string
    {
        $routeDatas = array_reverse($this->parseRoutePattern($namedRoute));
        $segments = [];

        foreach ($routeDatas as $routeData) {
            foreach ($routeData as $item) {
                if (is_string($item)) {
                    $segments[] = $item;
                    continue;
                }
                if (!array_key_exists($item[0], $data)) {
                    $segments = [];
                    $segmentName = $item[0];
                    break;
                }
                $segments[] = $data[$item[0]];
            }
            if (!empty($segments)) {
                break;
            }
        }
        if (empty($segments)) {
            throw new Exception('Missing data for URL segment: ' . $segmentName);
        }
        return implode('', $segments);
    }

    // Ref : https://github.com/nikic/FastRoute/issues/66#issuecomment-130395124
    protected function generateDataList($namedRoute, array $list = []) : string
    {
        $routes = $this->parseRoutePattern($namedRoute);

        foreach ($routes as $route) {
            $url = '';
            $paramIdx = 0;
            foreach ($route as $part) {
                if (is_string($part)) {
                    $url .= $part;
                    continue;
                }

                if ($paramIdx === count($list)) {
                    throw new Exception('Not enough parameters given');
                }
                $url .= $list[$paramIdx++];
            }

            if ($paramIdx === count($list)) {
                return $url;
            }
        }

        throw new Exception('Too many parameters given');
    }
}
