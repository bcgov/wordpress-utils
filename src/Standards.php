<?php
/**
 * Composer Scripts
 *
 * PHP version 7.4
 *
 * @category Scripts
 * @package  Standards
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
 * Class Standards
 *
 * Provides utility methods for running WordPress coding standards checks and fixes,
 * as well as npm command execution for development workflows. This class is designed
 * to be used with Composer scripts to automate code quality checks in WordPress
 * theme and plugin development.
 *
 * The class integrates with PHP_CodeSniffer (phpcs)
 * and PHP Code Beautifier and Fixer (phpcbf)
 * to enforce WordPress coding standards, and provides
 * npm command execution capabilities for frontend build processes.
 *
 * @category Scripts
 * @package  Standards
 * @author   WordPress <govwordpress@gov.bc.ca>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/bcgov/wordpress-utils
 */
class Standards
{


    /**
     * Performs WordPress theme and plugin coding standards based on
     * WordPress's coding standards.
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
     * Performs WordPress theme and plugin coding standards fixed
     * based on WordPress's coding standards.
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
     * WordPress coding standards helper function to setup installed paths
     * for WordPress coding standards, plus does check or fix.
     *
     * @param \Composer\Script\Event $event The composer event.
     * @param boolean                $fix   Fix issues flag.
     *
     * @return int
     */
    public static function phpWordPressCodingStandards(
        Event $event,
        bool $fix = false
    ): int {
        $config    = $event->getComposer()->getConfig();
        $vendorDir = ($config->get('vendor-dir'));
        $phpcs     = escapeshellarg("{$vendorDir}/bin/phpcs");
        $phpcbf    = escapeshellarg("{$vendorDir}/bin/phpcbf");
        $source    = escapeshellarg("{$vendorDir}/../");
        $io        = $event->getIO();
        $process   = new ProcessExecutor($io);
        $result    = 0;
        $sniffs    = '';
        $args      = '--standard=./vendor/bcgov/wordpress-utils/wordpress.xml';

        if (($event->getName() === 'production')
            || (($event->getName()) === 'checklist')
        ) {
            $sniffs .= "--exclude=Generic.Commenting.Todo";
        }

        if ($fix) {
            $result = $process->execute("{$phpcbf} {$args} {$source}");
        } else {
            $result = $process->execute(
                "{$phpcs} {$args} {$sniffs} {$source}"
            );
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
        $io       = $event->getIO();
        $process  = new ProcessExecutor($io);
        $redirect = $silent ? '&>/dev/null' : '';
        return $process->execute("npm run {$cmd} {$redirect}");
    }//end npm()

}//end class
