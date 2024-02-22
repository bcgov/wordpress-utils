<?php
/**
 * Composer Scripts
 *
 * @author  WordPress <govwordpress@gov.bc.ca>
 * @license https://opensource.org/licenses/MIT MIT
 */
namespace Bcgov\Script;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\Util\ProcessExecutor;
use Composer\IO\IOInterface;

class Tests
{


    /**
     * The PHPUnit script for testing php.
     *
     * @param \Composer\Script\Event $event  The composer event.
     * @param boolean                $silent This flag can't be called by scripts, but via directly, to suppress the output.
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
        $result    = $process->execute("{$phpunit} --configuration {$xml} --coverage-text {$redirect}");
        return $result;

    }//end phpunit()


}//end class
