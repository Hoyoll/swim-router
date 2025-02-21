<?php
namespace Swim\Router;

class Request
{
    protected const DELIMITER = '/';
    protected const HOOK = ':';

    protected static array $path_collections = [];
    protected static array $path_alias = [];

    /**
     * You want to give it uripath, and request method
     */
    
    public static function dispatch(string $uri, string $method, mixed $packets = null): void
    {
        $route = self::search_path($uri);
        if (!empty($route)) {
            $route['path']->dispatch($method, $packets, $route['params']);        
        } else {
            $error_handler = self::$path_collections['error'] ?? null;
            if ($error_handler === null) {
                throw new Exception("You did not specify error 404 handler!");
            }
            $send = [$method, $packets];
            $error_handler(...$send);
        }
    }

    private static function search_path(string $uri): array 
    {
        $path = self::$path_collections[$uri] ?? null;
        if ($path === null) {
            return self::search_alias(rtrim($uri, self::DELIMITER));
        }
        return [
            'path' => $path,
            'params' => []
        ];
    }

    private static function search_alias(string $uri): array 
    {
        foreach (self::$path_alias as $key => $value) {
            $result = preg_match($key, $uri, $match);
            if (!empty($match)) {
                array_shift($match);
                return [
                    'path' => self::$path_collections[$value],
                    'params' => $match
                ];
            }
        }
        return [];
    }
}