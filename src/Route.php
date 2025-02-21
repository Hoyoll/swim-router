<?php
namespace Swim\Router;

class Route extends Request
{   
    private static string $head = '';

    /**
    * You can also add a classnamae in this and the behaviour will change
    */
    public static function add(string $name, ?string $class = null): Path 
    {
        $path = new Path($class);
        self::$path_collections[$name] = $path;
        if (strpos($name, self::HOOK) !== false) {
            self::$path_alias[self::method_cleaner($name)] = $name;
        }
        return $path;
    }

    /**
    * I want you to handle your own 404, because depending on your server setup i might break it in some way
    */

    public static function error(callable $process): void
    {
        self::$path_collections['error'] = $process;
    }

    /**
    * example if you use 
    * Route::head('api')
    * all the subsequent route registered using Route::append() will start with /api
    * And you can reset by calling another Route::head()
    */

    public static function head(string $head): void 
    {
        self::$head = self::path_cleaner($head);
    }

    public static function append(string $name, ?string $class = null): Path 
    {
        if (self::DELIMITER === $name ) {
            return self::add(self::$head, $class);
        }
        $path = self::$head . self::path_cleaner($name);
        return self::add($path, $class);
        
    }

    private static function path_cleaner(string $path): string 
    {
        return self::DELIMITER . trim($path, self::DELIMITER);
    }

    private static function method_cleaner(string $name) 
    {
        $subject = explode(self::DELIMITER, trim($name, self::DELIMITER));
        $raw = array_map(
            function ($item) {
                return match ($item) {
                    ':num' => '([0-9]+)',
                    ':any' => '([a-zA-Z0-9\-]+)',
                    default => $item
                };
            }, $subject
        );
        $result = implode('\/', $raw);
        return "/^\/$result$/";
    }
}
