<?php
/**
 * PHPUnit bootstrap file.
 *
 * PHP version 7.4
 *
 * @category Tests
 * @package  Bootstrap
 * @author   WordPress <govwordpress@gov.bc.ca>
 * @license  https://opensource.org/licenses/MIT MIT
 * @version  GIT: 1.0.0
 * @link     https://github.com/bcgov/wordpress-utils
 */

$_tests_dir = getenv('WP_TESTS_DIR');

if (! $_tests_dir ) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit
// bootstrap file.
$_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
if (false !== $_phpunit_polyfills_path ) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
}

if (! file_exists("{$_tests_dir}/includes/functions.php") ) {
    echo "Could not find {$_tests_dir}/includes/functions.php, "
        . "have you run bin/install-wp-tests.sh ?" . PHP_EOL;
    exit(1);
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin or theme being tested.
 *
 * @return void
 *
 * @throws Exception If the plugin or theme entrypoint cannot be found.
 */
function manuallyLoadPluginOrTheme()
{
    $entrypoint = wordpressutilsFindEntrypointFile();
    if (true === $entrypoint) {
        registerTheme();
    } elseif (is_string($entrypoint)) {
        include $entrypoint;
    } else {
        throw new Exception('Could not load plugin or theme entrypoint.');
    }
}

/**
 * Attempts to find the entrypoint of the theme or plugin being tested.
 * Themes should have a functions.php file entrypoint.
 * Plugins should have a *.php file entrypoint with certain headers.
 *
 * @return string|bool Return the path to the *.php entrypoint for
 *                     plugins. Return true for themes. Return false if
 *                     no entrypoint could be found.
 */
function wordpressutilsFindEntrypointFile()
{
    if (function_exists('error_log')) {
        error_log('wordpressutilsFindEntrypointFile getcwd: ' . getcwd());
    }
    // Start at the vendor package dir and walk up directories until we find the
    // plugin or theme entrypoint. This makes the tests robust when the
    // vendor package is installed as a dependency or referenced as a path
    // repository located outside the plugin directory.
    $path = dirname(dirname(__DIR__));
    while ($path !== dirname($path)) {
        if (function_exists('error_log')) {
            error_log('wordpressutilsFindEntrypointFile path: ' . $path);
        }

        // If functions.php exists this is a theme, return true.
        if (file_exists($path . '/functions.php')) {
            return true;
        }

        // Get all php files in this directory and check for the Plugin Name
        // header.
        $files = glob($path . '/*.php');
        $default_headers = [
            'Plugin Name' => 'Plugin Name',
        ];

        foreach ($files as $file) {
            if (function_exists('error_log')) {
                error_log('wordpressutilsFindEntrypointFile checking: ' . $file);
            }
            $file_data = get_file_data($file, $default_headers);
            if (!empty($file_data['Plugin Name'])) {
                return $file;
            }
        }

        // Move up a directory and try again.
        $path = dirname($path);
    }

    if (function_exists('error_log')) {
        error_log('wordpressutilsFindEntrypointFile: no entrypoint found');
    }

    // No theme or plugin entrypoint was found.
    return false;
}

/**
 * Registers theme.
 *
 * @return void
 */
function registerTheme()
{

    $theme_dir     = dirname(__DIR__, 4);
    $current_theme = basename($theme_dir);
    $theme_root    = dirname($theme_dir);

    add_filter(
        'theme_root',
        function () use ( $theme_root ) {
            return $theme_root;
        }
    );

    register_theme_directory($theme_root);

    add_filter(
        'pre_option_template',
        function () use ( $current_theme ) {
            return $current_theme;
        }
    );

    add_filter(
        'pre_option_stylesheet',
        function () use ( $current_theme ) {
            return $current_theme;
        }
    );
}

tests_add_filter('muplugins_loaded', 'manuallyLoadPluginOrTheme');

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
