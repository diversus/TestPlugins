<?php

namespace Jacoby\Intervention;

use Jacoby\Intervention\Support\Arr;
use Jacoby\Intervention\Support\Config;

/**
 * Application
 *
 * Programmatic API for application.
 *
 * @package WordPress
 * @subpackage Intervention
 * @since 2.0.0
 */
class Application
{
    protected $key;
    protected $config;

    /**
     * Set
     */
    public static function set($key = false, $config = false)
    {
        return new self($key, $config);
    }

    /**
     * Construct
     *
     * @param array $config
     */
    public function __construct($key = false, $config = false)
    {
        $this->key = $key;

        // `::set()` shorthand is used
        if ($config) {
            $this->init($config);
        }
    }

    /**
     * Init
     *
     * Direct routing to avoid action required for config file
     *
     * @param array $config
     */
    public function init($config = false)
    {
        $this->config = Arr::normalize([$this->key => $config]);

        Config::get('application/routing')->map(function ($class, $k) {
            (new Intervention())->init($this->config, $class, $k);
        });
    }
}
