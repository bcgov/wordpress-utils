<?php
/**
 * Composer Scripts
 *
 * @author  WordPress <govwordpress@gov.bc.ca>
 * @license MIT
 */
namespace Bcgov\Script;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\Util\ProcessExecutor;
use Composer\IO\IOInterface;

class Standards
{
    /**
     * Performs WordPress theme and plugin coding standards based on WordPress's coding standards.
     *
     * @param \Composer\Script\Event $event The composer event.
     *
     * @return int
     */
    public static function phpcs(Event $event): int
    {
        return self::phpWordPressCodingStandards($event);

    }//end phpcs()


    /**
     * Performs WordPress theme and plugin coding standards fixed based on WordPress's coding standards.
     *
     * @param \Composer\Script\Event $event The composer event.
     *
     * @return int
     */
    public static function phpcbf(Event $event): int
    {
        return self::phpWordPressCodingStandards($event, true);

    }//end phpcbf()


    /**
     * WordPress coding standards helper function to setup installed paths for WordPress coding standards, plus does check or fix.
     *
     * @param \Composer\Script\Event $event The composer event.
     * @param boolean                $fix   Fix issues flag.
     *
     * @return int
     */
    private static function phpWordPressCodingStandards(Event $event, bool $fix=false): int
    {
        $config    = $event->getComposer()->getConfig();
        $vendorDir = $config->get('vendor-dir');
        $phpcs     = "{$vendorDir}/bcgov/wordpress-scripts/vendor/bin/phpcs";
        $phpcbf    = "{$vendorDir}/bcgov/wordpress-scripts/vendor/bin/phpcbf";
        $source    = "{$vendorDir}/../";
        $io        = $event->getIO();
        $process   = new ProcessExecutor($io);
        $result    = false;
        $sniffs    = '';

        if (('production' === $event->getName()) || ('checklist' === $event->getName())) {
            $sniffs .= "--exclude=Generic.Commenting.Todo";
        }

        $process->execute("{$phpcs} --config-set installed_paths vendor/bcgov/wordpress-scripts/vendor/wp-coding-standards/wpcs/");
        if ($fix) {
            $result = $process->execute("{$phpcbf} -ps --standard=./vendor/bcgov/wordpress-scripts/wordpress.xml --colors {$source}");
        } else {
            $result = $process->execute("{$phpcs} -ps --standard=./vendor/bcgov/wordpress-scripts/wordpress.xml  --colors {$sniffs} {$source}");
        }

        return $result;

    }//end phpWordPressCodingStandards()

    /**
     * Npm functions used as part of the checklist system
     *
     * @param \Composer\Script\Event $event  The composer event.
     * @param string                 $cmd    The npm cmd to run.
     * @param boolean                $silent The flag that suppresses the output.
     *
     * @return integer
     */
    public static function npm(Event $event, string $cmd, bool $silent=false): int
    {
        $result    = 0;
        $config    = $event->getComposer()->getConfig();
        $vendorDir = $config->get('vendor-dir');
        $phpunit   = "{$vendorDir}/bcgov/wordpress-scripts/vendor/bin/phpunit";
        $source    = "{$vendorDir}/../";
        $io        = $event->getIO();
        $process   = new ProcessExecutor($io);
        $redirect  = $silent ? '&>/dev/null' : '';
        $result    = $process->execute("npm run {$cmd} {$redirect}");
        return $result;

    }//end npm()

}//end class
