<?php

declare(strict_types=1);

namespace HttpSoft\Basis;

use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\Router\Route;
use HttpSoft\Router\RouteCollector;
use HttpSoft\Runner\MiddlewarePipelineInterface;
use HttpSoft\Runner\MiddlewareResolverInterface;
use HttpSoft\Runner\ServerRequestRunner;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Application
{
    /**
     * @var RouteCollector
     */
    private RouteCollector $router;

    /**
     * @var EmitterInterface
     */
    private EmitterInterface $emitter;

    /**
     * @var MiddlewarePipelineInterface
     */
    private MiddlewarePipelineInterface $pipeline;

    /**
     * @var MiddlewareResolverInterface
     */
    private MiddlewareResolverInterface $resolver;

    /**
     * @var RequestHandlerInterface|null
     */
    private ?RequestHandlerInterface $default;

    /**
     * @param RouteCollector $router
     * @param EmitterInterface $emitter
     * @param MiddlewarePipelineInterface $pipeline
     * @param MiddlewareResolverInterface $resolver
     * @param RequestHandlerInterface|null $default
     */
    public function __construct(
        RouteCollector $router,
        EmitterInterface $emitter,
        MiddlewarePipelineInterface $pipeline,
        MiddlewareResolverInterface $resolver,
        RequestHandlerInterface $default = null
    ) {
        $this->router = $router;
        $this->emitter = $emitter;
        $this->pipeline = $pipeline;
        $this->resolver = $resolver;
        $this->default = $default;
    }

    /**
     * Run the application.
     *
     * Proxies to the `ServerRequestRunner::run()` method.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface|null $defaultHandler
     */
    public function run(ServerRequestInterface $request, RequestHandlerInterface $defaultHandler = null): void
    {
        (new ServerRequestRunner($this->pipeline, $this->emitter))->run($request, $defaultHandler ?? $this->default);
    }

    /**
     * Adds a middleware to the pipeline.
     *
     * Wrapper over the `MiddlewarePipelineInterface::pipe()` method.
     *
     * @param mixed $middleware any valid value for converting it to `Psr\Http\Server\MiddlewareInterface` instance.
     * @param string|null $path path prefix from the root to which the middleware is attached.
     */
    public function pipe($middleware, string $path = null): void
    {
        $this->pipeline->pipe($this->resolver->resolve($middleware), $path);
    }

    /**
     * Creates a route group with a common prefix.
     *
     * Proxies to the `RouteCollector::group()` method.
     *
     * The callback can take a `HttpSoft\Router\RouteCollector` instance as a parameter.
     * All routes created in the passed callback will have the given group prefix prepended
     *
     * @param string $prefix common path prefix for the route group.
     * @param callable $callback callback that will add routes with a common path prefix.
     */
    public function group(string $prefix, callable $callback): void
    {
        $this->router->group($prefix, $callback);
    }

    /**
     * Adds a route and returns it.
     *
     * Proxies to the `RouteCollector::add()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @param array $methods allowed request methods of the route.
     * @return Route
     */
    public function add(string $name, string $pattern, $handler, array $methods = []): Route
    {
        return $this->router->add($name, $pattern, $handler, $methods);
    }

    /**
     * Adds a generic route for any request methods and returns it.
     *
     * Proxies to the `RouteCollector::any()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @return Route
     */
    public function any(string $name, string $pattern, $handler): Route
    {
        return $this->router->any($name, $pattern, $handler);
    }

    /**
     * Adds a GET route and returns it.
     *
     * Proxies to the `RouteCollector::get()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @return Route
     */
    public function get(string $name, string $pattern, $handler): Route
    {
        return $this->router->get($name, $pattern, $handler);
    }

    /**
     * Adds a POST route and returns it.
     *
     * Proxies to the `RouteCollector::post()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @return Route
     */
    public function post(string $name, string $pattern, $handler): Route
    {
        return $this->router->post($name, $pattern, $handler);
    }

    /**
     * Adds a PUT route and returns it.
     *
     * Proxies to the `RouteCollector::put()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @return Route
     */
    public function put(string $name, string $pattern, $handler): Route
    {
        return $this->router->put($name, $pattern, $handler);
    }

    /**
     * Adds a PATCH route and returns it.
     *
     * Proxies to the `RouteCollector::patch()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @return Route
     */
    public function patch(string $name, string $pattern, $handler): Route
    {
        return $this->router->patch($name, $pattern, $handler);
    }

    /**
     * Adds a DELETE route and returns it.
     *
     * Proxies to the `RouteCollector::delete()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @return Route
     */
    public function delete(string $name, string $pattern, $handler): Route
    {
        return $this->router->delete($name, $pattern, $handler);
    }

    /**
     * Adds a HEAD route and returns it.
     *
     * Proxies to the `RouteCollector::head()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @return Route
     */
    public function head(string $name, string $pattern, $handler): Route
    {
        return $this->router->head($name, $pattern, $handler);
    }

    /**
     * Adds a OPTIONS route and returns it.
     *
     * Proxies to the `RouteCollector::options()` method.
     *
     * @param string $name route name.
     * @param string $pattern path pattern with parameters.
     * @param mixed $handler action, controller, callable, closure, etc.
     * @return Route
     */
    public function options(string $name, string $pattern, $handler): Route
    {
        return $this->router->options($name, $pattern, $handler);
    }
}
