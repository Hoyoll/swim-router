<?php
namespace Swim\Router;

class Path 
{
    private array $method;
    private ?string $controller;
    private array $middleware;
    /** @type Callable */
    private $onError;

    /**
    * Depends on wether you registered classname or not.
    * If you do the string must corresponds to it's designated method,
    * you can register callable instead, it will get called regardless if classname is registered or not
    */

    public function on(array $method, string | callable $process): self 
    {
        foreach ($method as $req_method) {
            $this->method[strtoupper($req_method)] = $process;
        }
        return $this;
    }

    /**
    * A bunch of method for convenience
    */

    public function get(string | callable $process): self 
    {
        return $this->on(['get'], $process);
    }

    public function post(string | callable $process): self 
    {
        return $this->on(['post'], $process);
    }

    public function patch(string | callable $process): self 
    {
        return $this->on(['patch'], $process);
    }

    public function delete(string | callable $process): self 
    {
        return $this->on(['delete'], $process);
    }

    /**
    * You can register callable or classname. But only if that classname has __invoke() magic method
    */

    public function middleware(callable | string $middleware, array $on = ['ALL']): self 
    {
        foreach ($on as $method) {
            $this->middleware[strtoupper($method)] = $middleware;
        }
        return $this;
    }

    /**
    * How you define your error if the request fail is up to you
    * we use callable here
    */

    public function onError(callable $process) 
    {
        $this->onError = $process;
    }


    

    public function __construct(?string $controller = null) 
    {
        $this->controller = $controller;                   
    }

    public function dispatch(string $method, mixed &$packets = null, array &$params = [])
    {
        $this->call_middleware($method, $packets);
        try {
            $this->invoke($packets, $this->method[$method], $params);
        } catch (\Throwable $e) {
            ($this->onError)($e);
        }
    }

    private function invoke(mixed $packets, string | callable $process, array $params = []) 
    {
        if (is_callable($process)) {
            $send = [$packets, $params];
            $process(...$send);
        } else {
            $controller = new $this->controller($packets); 
            $controller->{$process}(...$params);
        }
    }

    private function call_middleware(string $method, mixed $packets) 
    {
        $this->dispatch_middleware($this->middleware[$method] ?? '', $packets);
        $this->dispatch_middleware($this->middleware['ALL'] ?? '', $packets);
    }

    private function dispatch_middleware(string | callable $middleware, mixed $packets) 
    {
        if ($middleware) {
            if (is_callable($middleware)) {
                $middleware($packets);
            } else {
                (new $middleware($packets))();    
            }
        }
    }
}
