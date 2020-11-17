<?php

namespace Sayeed\LumenRouteList;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class RouteListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered routes';

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The table headers for the command.
     *
     * @var string[]
     */
    protected $headers = ['Domain', 'Method', 'URI', 'Name', 'Action', 'Middleware'];

    /**
     * The columns to display when using the "compact" flag.
     *
     * @var string[]
     */
    protected $compactColumns = ['method', 'uri', 'action'];

    protected $routes = [];

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->displayRoutes();
        return;
    }

    /**
     * Display the routes in console
     * @return bool
     */
    public function displayRoutes()
    {
        $headers = ['Method', 'URI', 'Name', 'Action', 'Middleware', 'Map To'];
        $this->generateRoutes();
        $this->applyFilters();
        if (!$this->routes) {
            $this->warn('No routes found!');
            return false;
        }
        //change the reverse order if command contains reverse command
        $str = '';
        if ($this->option('reverse')) {
            rsort($this->routes);
            $str = '. Displayed in reverse order';
        }
        //$this->info("Route found: " . count($this->routes) . $str);
        $this->table($headers, $this->routes);
    }

    /**
     * Generate the formatted routes array
     * @return bool
     */
    public function generateRoutes()
    {
        $routes = property_exists(app(), 'router')? app()->router->getRoutes() : app()->getRoutes();
        foreach ($routes as $route) {
            array_push($this->routes, [
                'method' => $route['method'],
                'uri' => $route['uri'],
                'name' => $this->getRouteName($route),
                'action' => $this->getRouteAction($route),
                'middleware' => $this->getRouteMiddleware($route),
                'map' => $this->getRouteMapTo($route)
            ]);
        }
    }

    /**
     * Apply filters on routes if user provide
     */
    private function applyFilters()
    {
        $availableOptions = ['name', 'method', 'uri', 'action', 'middleware'];
        foreach ($this->options() as $key => $option) {
            if (in_array($key, $availableOptions) && null != $option) {
                foreach ($this->routes as $index => $route) {
                    if (!str_contains(strtolower($route[$key]), strtolower($option)))
                        unset($this->routes[$index]);
                }
            }
        }
    }

    /**
     * Get the route name
     * @param $route
     * @return null
     */
    private function getRouteName($route)
    {
        return (isset($route['action']['as'])) ? $route['action']['as'] : '';
    }

    /**
     * Get the route action type
     * @param $route
     * @return string
     */
    private function getRouteAction($route)
    {
        return ($this->isClosureRoute($route)) ? 'Closure' : 'Controller';
    }

    /**
     *  Get where the route map to
     * @param $route
     * @return string
     */
    private function getRouteMapTo($route)
    {
        return (!$this->isClosureRoute($route)) ? $route['action']['uses'] : '';
    }

    /**
     * Get route middleware
     * @param $route
     * @return string
     */
    private function getRouteMiddleware($route)
    {
        if (isset($route['action']['middleware'])) {
            return join(',', $route['action']['middleware']);
        }
        return '';
    }

    /**
     *  Check if the route is closure or controller route
     * @param $route
     * @return bool
     */
    private function isClosureRoute($route)
    {
        return !isset($route['action']['uses']);
    }

    /**
     * Get console input options
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['method', 'm', InputOption::VALUE_OPTIONAL, 'Method'],
            ['uri', 'u', InputOption::VALUE_OPTIONAL, 'Uri'],
            ['name', 'f', InputOption::VALUE_OPTIONAL, 'Name'],
            ['action', 'a', InputOption::VALUE_OPTIONAL, 'Action'],
            ['middleware', 'd', InputOption::VALUE_OPTIONAL, 'Middleware'],
            ['map', 'p', InputOption::VALUE_OPTIONAL, 'Map to'],
            ['reverse', 'r', InputOption::VALUE_NONE, 'Reverse route list']
        ];
    }

}
