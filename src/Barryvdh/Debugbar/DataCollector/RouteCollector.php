<?php

namespace Barryvdh\Debugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Symfony\Component\Routing\Route;
use Illuminate\Routing\Router;

class RouteCollector extends DataCollector  implements Renderable
{

    public function __construct(){
        $this->router = new Router;
    }
    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $name = \Route::currentRouteName();
        $route = \Route::getCurrentRoute();
        return $this->getRouteInformation($name, $route);
    }

    /**
     * Get the route information for a given route.
     *
     * @param  string  $name
     * @param  \Symfony\Component\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformation($name, Route $route)
    {
        $uri = head($route->getMethods()).' '.$route->getPath();

        $action = $route->getAction() ?: 'Closure';

        return array(
            'host'   => $route->getHost() ?: ' -',
            'uri'    => $uri ?: ' -',
            'name'   => $this->getRouteName($name) ?: ' -',
            'action' => $action ?: ' -',
            'before' => $this->getBeforeFilters($route) ?: ' -',
            'after'  => $this->getAfterFilters($route) ?: ' -'
        );
    }

    /**
     * Get the route name for the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getRouteName($name)
    {
        return str_contains($name, ' ') ? '' : $name;
    }

    /**
     * Get before filters
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return string
     */
    protected function getBeforeFilters($route)
    {
        $before = $route->getBeforeFilters();

        $before = array_unique(array_merge($before, $this->getPatternFilters($route)));

        return implode(', ', $before);
    }

    /**
     * Get all of the pattern filters matching the route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getPatternFilters($route)
    {
        $patterns = array();

        foreach ($route->getMethods() as $method)
        {
            $inner = $this->router->findPatternFilters($method, $route->getPath());

            $patterns = array_merge($patterns, $inner);
        }

        return $patterns;
    }

    /**
     * Get after filters
     *
     * @param  Route  $route
     * @return string
     */
    protected function getAfterFilters($route)
    {
        return implode(', ',$route->getAfterFilters());
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'route';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return array(
            "route" => array(
                "widget" => "PhpDebugBar.Widgets.VariableListWidget",
                "map" => "route",
                "default" => "{}"
            )
        );
    }
}
