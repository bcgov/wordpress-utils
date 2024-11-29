<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Wordpress_Utils
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin or theme being tested.
 */
function _manually_load_plugin_or_theme() {
	$entrypoint = _wordpressutils_find_entrypoint_file();
    if ($entrypoint === true) {
        _register_theme();
    } elseif (is_string($entrypoint)) {
        require $entrypoint;
    }
}

/**
 * Attempts to find the entrypoint of the theme or plugin being tested.
 * Themes should have a functions.php file entrypoint.
 * Plugins should have a <plugin-name>.php file entrypoint.
 *
 * @return string|bool Return the path to the <plugin-name>.php entrypoint for
 *                     plugins. Return true for themes.
 * @throws Exception If no single entrypoint php file can be found in the root directory.
 */
function _wordpressutils_find_entrypoint_file() {
    $path = dirname( dirname( __FILE__ ), 4 );
    $file_to_require = null;

    // Get all php files in the plugin/theme root.
    $files = glob($path . '/*.php');
    $filenames = [];

    // Get just the filename for each file found.
    foreach($files as $file) {
        $filenames[] = pathinfo($file, PATHINFO_FILENAME);
    }

    // If functions.php exists (theme), require it.
    $key = array_search('functions', $filenames);
    if ($key !== false) {
        return true;
    } else {
        // Plugins are named <plugin-name>.php, attempt to filter php files to find this file.
        $IGNORED_FILENAMES = ['index', 'uninstall'];
        $filtered_filenames = array_diff($filenames, $IGNORED_FILENAMES);
        $files_found = count($filtered_filenames);
        if ($files_found === 1) {
            $file_to_require = $files[array_key_first($filtered_filenames)];
        } elseif ($files_found < 1) {
            throw new Exception('No entrypoint php file found. Plugins should have a <plugin-name>.php and themes should have a functions.php.');
        } else {
            throw new Exception('Multiple potential entrypoint php files found. List of allowed *.php files in project root: <plugin-name>, ' . join($IGNORED_FILENAMES, ', '));
        }
    }

    return $file_to_require;
}

/**
 * Registers theme.
 */
function _register_theme() {

	$theme_dir     = dirname( __DIR__, 4 );
	$current_theme = basename( $theme_dir );
	$theme_root    = dirname( $theme_dir );

	add_filter( 'theme_root', function () use ( $theme_root ) {
		return $theme_root;
	} );

	register_theme_directory( $theme_root );

	add_filter( 'pre_option_template', function () use ( $current_theme ) {
		return $current_theme;
	} );

	add_filter( 'pre_option_stylesheet', function () use ( $current_theme ) {
		return $current_theme;
	} );
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin_or_theme' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
