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
    public static function phpWordPressCodingStandards(Event $event, bool $fix=false): int
    {
        $config    = $event->getComposer()->getConfig();
        $vendorDir = escapeshellarg($config->get('vendor-dir'));
        $phpcs     = "{$vendorDir}/bin/phpcs";
        $phpcbf    = "{$vendorDir}/bin/phpcbf";
        $source    = "{$vendorDir}/../";
        $io        = $event->getIO();
        $process   = new ProcessExecutor($io);
        $result    = 0;
        $sniffs    = '';

        if (($event->getName() === 'production') || ($event->getName()) === 'checklist') {
            $sniffs .= "--exclude=Generic.Commenting.Todo";
        }

        // Let the user know about their composer.json wordpress standards options.
        $options = [
            'yes' => 'yes',
            'no'  => 'no',
        ];
        $composer_json_lines = [
            '"require-dev": {',
            '"bcgov/wordpress-common": "@dev",',
            '"bcgov/wordpress-scripts": "1.1.1"',
        ];
        $readme_lines        = [
            '<warning>, See the section entitled: "Why you should use the latest version of this package</warning>',
            '<warning>in the README https://apps.itsm.gov.bc.ca/bitbucket/projects/WP/repos/wordpress-scripts/browse/README.md</warning>',
        ];

        $confirm = (object) [
            'upgrade' => $io->select('Would you like to upgrade your composer.json to use the new WordPress coding standards? (Default No)', $options, 'no'),
        ];

        // Prompt the user whether they want to upgrade, then explain how & why.
        if ($confirm->upgrade === 'no') {
            $io->write('<warning>for the existing standard, the tag you want is "1.1.1". Edit your composer.json like so:');
            $io->write($composer_json_lines);
            $io->write($readme_lines);
            return $result;
        };

        if ($fix) {
            $result = $process->execute("{$phpcbf} -pn --standard=./vendor/bcgov/wordpress-scripts/wordpress.xml --colors {$source}");
            $io->write("<info>To convert all tabs to spaces run the following command:</info>");
            $io->write("{$phpcbf} -p --standard=./vendor/bcgov/wordpress-scripts/wordpress.xml --colors {$source}\n");
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
        $result   = 0;
        $io       = $event->getIO();
        $process  = new ProcessExecutor($io);
        $redirect = $silent ? '&>/dev/null' : '';
        $result   = $process->execute("npm run {$cmd} {$redirect}");
        return $result;

    }//end npm()


}//end class
