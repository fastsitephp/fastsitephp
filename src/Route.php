<?php
/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP;

use \Closure;

/**
 * When [$app->get()], [$app->post()] and other methods are 
 * called a new Route Object is created.
 */
class Route
{
    /**
     * URL Pattern to match for the route to get called
     * @var string
     */
    public $pattern = null;

    /**
     * Controller Closure function or string that refers
     * to a class or class and method.
     * @var string|\Closure
     */
    public $controller = null;

    /**
     * Request Method to match ['GET', 'POST', etc]
     * @var string
     */
    public $method = null;

    /**
     * Array of filter functions for the route
     * @var array
     */
    public $filter_callbacks = array();
    
    /**
     * Add a filter function to the route. If the route is matched
     * then all filter functions for it are called. If one or more
     * of the filter functions returns [false] then the route is skipped.
     * Filter functions are not required to return anything.
     * If a filter function returns a Response Object then it will be
     * sent to the client and the controller for the route will not be called.
     * 
     * @param \Closure|string $callback
     * @return $this
     */
    public function filter($callback) 
    {
        $this->filter_callbacks[] = $callback;
        return $this;
    }
}