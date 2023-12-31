<?php
/*
Plugin Name: Intervention
Plugin URI: https://github.com/darrenjacoby/intervention
Description: Easily customize wp-admin and configure application options.
Text Domain: intervention
Version: 2.0.0
Author: Darren Jacoby
Author URI: https://github.com/darrenjacoby
License: MIT License
License URI: https://opensource.org/licenses/MIT
 */
namespace Jacoby\Intervention;

use Jacoby\Intervention\Intervention;

/**
 * Restrict direct access
 */
if (!defined('ABSPATH')) {
    die;
}

define('INTERVENTION_DIR', dirname(__FILE__));
define('THEME_TEXT_DOMAIN', wp_get_theme()->get('TextDomain'));
define('INTERVENTION_TEXT_DOMAIN', 'intervention');

/**
 * Support for Bedrock/Composer
 */
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// include file_exists($composer = __DIR__ . '/vendor/autoload.php') ? $composer : __DIR__ . '/build/vendor/autoload.php';

/**
 * WordPress/Laravel mix function
 */
include __DIR__ . '/mix.php';

/**
 * Return user config for Intervention
 *
 * @return array
 */
function getConfigFile()
{
    $theme = get_stylesheet_directory();

    $default = file_exists($theme . '/config/') ?
    $theme . '/config/intervention.php' :
    $theme . '/intervention.php';

    $config = has_filter('sober/intervention/return') ?
    apply_filters('sober/intervention/return', rtrim($default)) :
    $default;

    if (!file_exists($config)) {
        return;
    }

    $read = include $config;

    return $read === 1 ? false : $read;
}

function getDatabase()
{
    $option = get_option('intervention_admin', []);
    $read = [];
    if ($option) {
        foreach ($option as $role => $array) {
            $read['wp-admin.' . $role] = $array;
        }
    }
    return $read;
}

/**
 * Initialize
 */
new Intervention(getConfigFile(), true);
new Intervention(getDatabase());
new UserInterface();
