<?php
/**
 * Composer Scripts
 *
 * @author  WordPress <govwordpress@gov.bc.ca>
 * @license https://opensource.org/licenses/MIT MIT
 * @package wordpress-utils
 */

namespace Bcgov\Script;

use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
/**
 * Executes PHPUnit tests with coverage reporting.
 *
 * This method runs PHPUnit tests using the configuration file located in the project root
 * and generates coverage text output. It can optionally suppress output for silent execution.
 *
 * @param \Composer\Script\Event $event  The composer event containing configuration and IO.
 * @param bool                   $silent Optional. Whether to suppress output. Default false.
 *
 * @return int The exit code of the PHPUnit process (0 for success, non-zero for failure).
 */
class Tests {

    /**
     * The PHPUnit script for testing php.
     *
     * @param \Composer\Script\Event $event  The composer event.
     * @param boolean                $silent This flag can't be called by scripts, but via directly, to suppress the output.
     *
     * @return int
     */
    public static function phpunit( Event $event, bool $silent = false ): int {
        $config    = $event->getComposer()->getConfig();
        $vendorDir = $config->get( 'vendor-dir' );
        $phpunit   = escapeshellarg( "{$vendorDir}/bin/phpunit" );
        $xml       = escapeshellarg( "{$vendorDir}/../phpunit.xml" );
        $io        = $event->getIO();
        $process   = new ProcessExecutor( $io );
        $redirect  = $silent ? '&>/dev/null' : '';
        return $process->execute( "{$phpunit} --configuration {$xml} --coverage-text {$redirect}" );
    }//end phpunit()
}//end class
