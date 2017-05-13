<?php

namespace Starch;

use DI\ContainerBuilder;
use function DI\object;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use InvalidArgumentException;
use mindplay\middleman\Dispatcher;
use mindplay\readable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\Router;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

class App
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var (callable|MiddlewareInterface)[]
     */
    private $middlewares = [];

    public function __construct()
    {
        $this->buildContainer();
    }

    /**
     * Override this method to add extra definitions to your app
     *
     * @return void
     */
    public function configureContainer(ContainerBuilder $builder) : void
    {
    }

    /**
     * Add middleware
     *
     * This method will add the given callable to the middleware stack
     *
     * @param callable|MiddlewareInterface $middleware
     *
     * @return void
     */
    public function add($middleware) : void
    {
        if (
            $middleware instanceof MiddlewareInterface
            || is_callable($middleware)
        ) {
            $this->middlewares[] = $middleware;

            return;
        }

        throw new InvalidArgumentException(sprintf(
            "Middleware must be callable or MiddlewareInterface, %s given.",
            readable::value($middleware)
        ));
    }

    /********************************************************************************
     * Router proxy methods
     *******************************************************************************/

    /**
     * Add a GET route
     *
     * @param  string   $route
     * @param  callable $handler
     *
     * @return void
     */
    public function get(string $route, callable $handler) : void
    {
        $this->container->get(Router::class)->map(['GET'], $route, $handler);
    }

    /********************************************************************************
     * Running the actual app
     *******************************************************************************/

    /**
     * Run the app
     *
     * Will build a request from PHP globals, process that request and then emit it
     *
     * @return void
     */
    public function run() : void
    {
        $request = $request = ServerRequestFactory::fromGlobals();

        $response = $this->process($request);

        $this->container->get(EmitterInterface::class)->emit($response);
    }

    /**
     * Dispatch the request to the router
     * Add the returned handler as the last Middleware
     * Send the Request through the stack
     *
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request) : ResponseInterface
    {
        $this->middlewares[] = $this->container->get(Router::class)->dispatch($request);

        $dispatcher = new Dispatcher($this->middlewares);

        return $dispatcher->dispatch($request);
    }

    /********************************************************************************
     * Private methods
     *******************************************************************************/

    /**
     * Build the container with a couple base service
     *
     * These base services can be overridden in self::configureContainer
     *
     * @return void
     */
    private function buildContainer() : void
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            Router::class => object(),

            EmitterInterface::class => object(SapiEmitter::class),
        ]);

        $this->configureContainer($builder);

        $this->container = $builder->build();
    }
}
