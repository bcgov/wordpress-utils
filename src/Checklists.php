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
use Bcgov\Script\Standards;
use Bcgov\Script\Tests;

class Checklists
{
    /**
     * Checklist for post production.
     *
     * @param \Composer\Script\Event $event Gets triggered when run from a composer script.
     *
     * @return void
     */
    public static function postProductionChecks(Event $event): void
    {
        $io            = $event->getIO();
        $checklistFile = $event->getComposer()->getConfig()->get('vendor-dir').'/../checklist.md';
        $success       = true;
        $process       = new ProcessExecutor($io);

        // This is the default checklist, which is not automatically checked.
        $checklist = [];
        // These are the automatic checks, eventually other items like version might be incorporated.
        $checks = [
            'phpcs'   => 'PHP Coding standards failed, no errors or warnings allowed, exceptions can be removed by using this comment //phpcs:ignore',
            'phpUnit' => 'PHP Unit tests failed.',
            'lintJs'  => 'Javascript linting failed.',
            'lintCss' => 'Style linting failed.',
            'testJs'  => 'Javascript tests failed.',
        ];
        // If checklist is not created via production script, give warning to ensure composer production.
        if ('production' !== $event->getName()) {
            $io->write(self::console('Ensure that you run `composer production`', 'warning'));
        }

        // Creates a checklist.md file in the theme or plugin root.
        $io->write(self::console("\nCreating checklist.md...\n"));
        // Loops through all the items, and will break if any of them fail, in order not to waste the devs time.
        foreach ($checks as $check => $errorMsg) {
            $result = 0;
            $item   = '';
            if ('phpcs' === $check) {
                $result = Standards::phpWordPressCodingStandards($event, false);
                $item   = '* [%s] Verified coding standards (phpcs)';
            } else if ('phpUnit' === $check) {
                $result = Tests::phpunit($event, true);
                $item   = '* [%s] Run PHP tests';
            } else if ('lintJs' === $check) {
                $result = Standards::npm($event, 'lint:js', true);
                $item   = '* [%s] Lint javascript';
            } else if ('lintCss' === $check) {
                $result = Standards::npm($event, 'lint:css', true);
                $item   = '* [%s] Lint CSS';
            } else if ('testJs' === $check) {
                $result = Standards::npm($event, 'test', true);
                $item   = '* [%s] Javascript Tests';
            }

            // This ensures that one of the conditions are meet, in order to add the checklist item.
            if (! empty($item)) {
                $checkType     = self::getCheckmarkType($result);
                $checklistItem = sprintf($item, $checkType);
                $checklist[]   = $checklistItem;
                $io->write(self::console($checklistItem));
            }

            // A 0 result means passed, > 0 failed.
            if ($result > 0) {
                $io->write(self::console("*** FAIL ***\nTests Failed on {$check} please fix issues and re-run\n{$errorMsg}\n", 'error'));
                $success = false;
                break;
            }
        }//end foreach

        if ($success) {
            $selectChoices = [
                'yes' => 'yes',
                'no'  => 'no',
            ];
            $confirm       = (object) [
                'composer'  => $io->select('Is your version in composer.json the correct version? (Default Yes)', $selectChoices, 'yes'),
                'style'     => $io->select('Is your version in your style.css or plugin file the correct version? (Default Yes)', $selectChoices, 'yes'),
                'changelog' => $io->select('Did you update the CHANGELOG.md to include jira tickets? (Default Yes)', $selectChoices, 'yes'),
                'readme'    => $io->select('Update README.md if applicable? (Default Yes)', $selectChoices, 'yes'),
                'assets'    => $io->select('Built assets if applicable? (Default Yes)', $selectChoices, 'yes'),
            ];
            $checklist     = array_merge(
                [
                    "* [{$confirm->composer}] Updated version in composer.json",
                    "* [{$confirm->style}] Updated version in style.css or plugin file",
                    "* [{$confirm->changelog}] Updated CHANGELOG.md to include jira ticket",
                    "* [{$confirm->readme}] Updated README.md for new functionality",
                    "* [{$confirm->assets}] Built assets for production (npm run build:production)",
                ],
                $checklist
            );
            $io->write(self::console("\nChecklist created successfully!!!"));
        }//end if

        // Creates the checklist string that will be outputted to the checklist.md.
        // This is meant as a temporary solution (checklist.md), towards CI/CD.
        date_default_timezone_set('America/Vancouver');
        $checklistString  = sprintf('Created at %s', date('Y-m-d g:i a'));
        $checklistString .= $process->escape("\n\n".implode("\n", $checklist));
        $process->execute("echo {$checklistString} > {$checklistFile}");

    }//end postProductionChecks()


    /**
     * Helper function to return checkmark or Fail for checklist.
     *
     * @param integer $status The status ( 0, or number greater than 0 ).
     *
     * @return string
     */
    private static function getCheckmarkType(int $status): string
    {
        $output = 'Fail';
        if (0 === $status) {
            $output = '✓';
        }

        return $output;

    }//end getCheckmarkType()


    /**
     * Checklist for post production for Common.
     *
     * @return void
     */
    public static function postProductionChecksForCommon(): void
    {
        $checklist = [
            '[] Updated version in composer.json',
            '[] Updated CHANGELOG.md to include jira ticket',
            '[] Updated README.md for new functionality',
            '[] Verified coding standards (phpcs)',
            '[] Run PHP tests',
        ];

        echo "****** CHECKLIST ******\n\n";
        echo implode("\n", $checklist)."\n\n";

    }//end postProductionChecksForCommon()


    /**
     * Helper console function that returns formatted messages for the Composer\IO\IOInterface.
     *
     * @param string $msg     The message.
     * @param string $msgType The message type, in order to wrap text, see $logLevels for allowed.
     *
     * @return string
     */
    public static function console(string $msg, string $msgType='info'): string
    {
        $logLevels = [
            'error',
            'warning',
            'info',
        ];
        if (in_array($msgType, $logLevels)) {
            $msg = "<{$msgType}>{$msg}</{$msgType}>";
        }

        return $msg;

    }//end console()


}//end class
