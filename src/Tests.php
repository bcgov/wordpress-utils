<?php
/**
 * Composer Scripts
 *
 * PHP version 7.4
 *
 * @category Scripts
 * @package  Tests
 * @author   WordPress <govwordpress@gov.bc.ca>
 * @license  https://opensource.org/licenses/MIT MIT
 * @version  GIT: 1.0.0
 * @link     https://github.com/bcgov/wordpress-utils
 * @since    1.0.0
 */
namespace Bcgov\Script;

use Composer\Script\Event;
use Composer\Util\ProcessExecutor;

/**
 * Executes PHPUnit tests with coverage reporting.
 *
 * This class runs PHPUnit tests using the configuration file located
 * in the project root and generates coverage text output. It can
 * optionally suppress output for silent execution.
 *
 * @category Scripts
 * @package  Tests
 * @author   WordPress <govwordpress@gov.bc.ca>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/bcgov/wordpress-utils
 */
class Tests
{


    /**
     * The PHPUnit script for testing php.
     *
     * @param \Composer\Script\Event $event  The composer event.
     * @param boolean                $silent Flag to suppress output.
     *
     * @return int
     */
    public static function phpunit(Event $event, bool $silent=false): int
    {
        $config    = $event->getComposer()->getConfig();
        $vendorDir = $config->get('vendor-dir');
        $phpunit   = escapeshellarg("{$vendorDir}/bin/phpunit");
        $xml       = escapeshellarg("{$vendorDir}/../phpunit.xml");
        $io        = $event->getIO();
        $process   = new ProcessExecutor($io);
        $redirect  = $silent ? '&>/dev/null' : '';
        return $process->execute(
            "{$phpunit} --configuration {$xml} --coverage-text {$redirect}"
        );
    }//end phpunit()


}//end class
