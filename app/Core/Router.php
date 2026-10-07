<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Router
{
    protected static array $routes = [];
    protected static array $groupMiddlewares = [];

    public static function get(string $path, array|string|callable $handler, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, array|string|callable $handler, array $middlewares = []): void
    {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    public static function put(string $path, array|string|callable $handler, array $middlewares = []): void
    {
        self::addRoute('PUT', $path, $handler, $middlewares);
    }

    public static function delete(string $path, array|string|callable $handler, array $middlewares = []): void
    {
        self::addRoute('DELETE', $path, $handler, $middlewares);
    }

    public static function group(array $attributes, callable $callback): void
    {
        $previousMiddlewares = self::$groupMiddlewares;
        if (isset($attributes['middleware'])) {
            $middlewares = (array)$attributes['middleware'];
            self::$groupMiddlewares = array_merge(self::$groupMiddlewares, $middlewares);
        }

        $callback();

        self::$groupMiddlewares = $previousMiddlewares;
    }

    protected static function addRoute(string $method, string $path, array|string|callable $handler, array $middlewares): void
    {
        $allMiddlewares = array_merge(self::$groupMiddlewares, $middlewares);
        $cleanPath = '/' . trim($path, '/');

        self::$routes[] = [
            'method'      => strtoupper($method),
            'path'        => $cleanPath,
            'handler'     => $handler,
            'middlewares' => $allMiddlewares,
        ];
    }

    public static function dispatch(Request $request): Response
    {
        $requestMethod = $request->getMethod();
        $requestPath   = $request->getPath();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = self::buildRegexPattern($route['path']);
            if (preg_match($pattern, $requestPath, $matches)) {
                // Filter named parameters
                $params = array_filter($matches, '\is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware pipeline
                foreach ($route['middlewares'] as $middlewareDef) {
                    $middlewareResponse = self::executeMiddleware($middlewareDef, $request);
                    if ($middlewareResponse instanceof Response) {
                        return $middlewareResponse;
                    }
                }

                // Execute handler
                return self::executeHandler($route['handler'], $request, $params);
            }
        }

        // 404 Handler
        return View::render('partials.404', ['title' => 'Page Not Found'], 'customer')
            ->setStatusCode(404);
    }

    protected static function buildRegexPattern(string $routePath): string
    {
        // Convert route parameters like {category}-services-in-{city} or {id} to named regex capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        return '#^' . $pattern . '$#u';
    }

    protected static function executeMiddleware(string $middlewareDef, Request $request): ?Response
    {
        [$name, $arg] = array_pad(explode(':', $middlewareDef, 2), 2, null);
        $class = "App\\Middleware\\{$name}";

        if (!class_exists($class)) {
            throw new RuntimeException("Middleware class [{$class}] not found.");
        }

        $instance = new $class();
        return $instance->handle($request, $arg);
    }

    protected static function executeHandler(array|string|callable $handler, Request $request, array $params): Response
    {
        if (is_callable($handler)) {
            $result = call_user_func_array($handler, array_merge([$request], $params));
            return self::wrapResponse($result);
        }

        if (is_string($handler)) {
            [$controllerName, $action] = explode('@', $handler);
            $fullClass = str_starts_with($controllerName, 'App\\Controllers\\')
                ? $controllerName
                : "App\\Controllers\\{$controllerName}";

            if (!class_exists($fullClass)) {
                throw new RuntimeException("Controller class [{$fullClass}] not found.");
            }

            $controller = new $fullClass();
            if (!method_exists($controller, $action)) {
                throw new RuntimeException("Action method [{$action}] not found on controller [{$fullClass}].");
            }

            $result = call_user_func_array([$controller, $action], array_merge([$request], $params));
            return self::wrapResponse($result);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = is_string($class) ? new $class() : $class;
            $result = call_user_func_array([$controller, $method], array_merge([$request], $params));
            return self::wrapResponse($result);
        }

        throw new RuntimeException('Invalid route handler configuration.');
    }

    protected static function wrapResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        if (is_string($result)) {
            return Response::html($result);
        }

        return Response::html('');
    }
}
