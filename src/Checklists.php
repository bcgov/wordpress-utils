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
use Bcgov\Script\Standards;
use Bcgov\Script\Tests;
/**
 * Checklists class for managing post-production checks and validations.
 *
 * This class provides functionality to run automated checks for code quality,
 * testing, and linting before production deployment. It creates interactive
 * checklists and generates checklist.md files for tracking completion status.
 *
 * The class supports various types of checks including:
 * - PHP coding standards (phpcs)
 * - PHP unit tests
 * - JavaScript linting
 * - CSS linting
 * - JavaScript tests
 *
 * It also provides interactive prompts for manual verification items like
 * version updates, changelog updates, and documentation requirements.
 *
 * @package wordpress-utils
 * @author  WordPress <govwordpress@gov.bc.ca>
 * @since   1.0.0
 */
class Checklists {

    /**
     * Checklist for post production skipping phpunit execution.
     *
     * @param Event $event Gets triggered when run from a composer script.
     * @return void
     */
    public static function postProductionChecksSkipPhpunit( Event $event ) {
        self::postProductionChecks( $event, true );
    }

    /**
     * Checklist for post production.
     *
     * @param Event $event Gets triggered when run from a composer script.
     * @param bool  $skip_phpunit Whether to skip phpunit execution.
     *
     * @return void
     */
    public static function postProductionChecks( Event $event, bool $skip_phpunit = false ): void {
        $io             = $event->getIO();
        $checklist_file = ( $event->getComposer()->getConfig()->get( 'vendor-dir' ) . '/../checklist.md' );
        $success        = true;

        // This is the default checklist, which is not automatically checked.
        $checklist = array();
        // These are the automatic checks, eventually other items like version might be incorporated.
        $checks = array(
            'phpcs'   => 'PHP Coding standards failed, no errors or warnings allowed, exceptions can be removed by using this comment //phpcs:ignore',
            'phpUnit' => 'PHP Unit tests failed.',
            'lintJs'  => 'Javascript linting failed.',
            'lintCss' => 'Style linting failed.',
            'testJs'  => 'Javascript tests failed.',
        );
        // If checklist is not created via production script, give warning to ensure composer production.
        if ( $event->getName() !== 'production' ) {
            $io->write( self::console( 'Ensure that you run `composer production`', 'warning' ) );
        }

        // Creates a checklist.md file in the theme or plugin root.
        $io->write( self::console( "\nCreating checklist.md...\n" ) );
        // Loops through all the items, and will break if any of them fail, in order not to waste the devs time.
        foreach ( $checks as $check => $error_msg ) {
            $result = 0;
            $item   = '';
            if ( 'phpcs' === $check ) {
                $result = Standards::phpWordPressCodingStandards( $event, false );
                $item   = '* [%s] Verified coding standards (phpcs)';
            } elseif ( 'phpUnit' === $check ) {
                if ( $skip_phpunit ) {
                    $result = 0;
                    $item   = '* [N/A] Skipped PHP tests';
                } else {
                    $result = Tests::phpunit( $event, true );
                    $item   = '* [%s] Run PHP tests';
                }
            } elseif ( 'lintJs' === $check ) {
                $result = Standards::npm( $event, 'lint:js', true );
                $item   = '* [%s] Lint javascript';
            } elseif ( 'lintCss' === $check ) {
                $result = Standards::npm( $event, 'lint:css', true );
                $item   = '* [%s] Lint CSS';
            } elseif ( 'testJs' === $check ) {
                $result = Standards::npm( $event, 'test', true );
                $item   = '* [%s] Javascript Tests';
            }

            // This ensures that one of the conditions are meet, in order to add the checklist item.
            if ( ! empty( $item ) ) {
                $check_type     = self::get_checkmark_type( $result );
                $checklist_item = sprintf( $item, $check_type );
                $checklist[]    = $checklist_item;
                $io->write( self::console( $checklist_item ) );
            }

            // A 0 result means passed, > 0 failed.
            if ( $result > 0 ) {
                $io->write( self::console( "*** FAIL ***\nTests Failed on {$check} please fix issues and re-run\n{$error_msg}\n", 'error' ) );
                $success = false;
                break;
            }
        }//end foreach

        if ( $success ) {
            $select_choices = array(
                'yes' => 'yes',
                'no'  => 'no',
            );
            $confirm        = (object) array(
                'style'         => $io->select( 'Is your version in your style.css or plugin file the correct version? (Default Yes)', $select_choices, 'yes' ),
                'changelog'     => $io->select( 'Did you update the CHANGELOG.md to include jira tickets? (Default Yes)', $select_choices, 'yes' ),
                'readme'        => $io->select( 'Update README.md if applicable? (Default No)', $select_choices, 'no' ),
                'assets'        => $io->select( 'Built assets if applicable? (Default Yes)', $select_choices, 'yes' ),
                'documentation' => $io->select( 'Does/did documentation need to be updated? (Default No)', $select_choices, 'no' ),
            );
            // If documentation needs to be updated, is a new JIRA ticket needed?
            if ( 'yes' === $confirm->documentation ) {
                $confirm->new_ticket_needed = $io->select( 'Is a separate ticket required for the documentation? (Default No)', $select_choices, 'no' );
            }
            // If a new ticket is needed, we need to get the ticket ID.
            if ( isset( $confirm->new_ticket_needed ) && 'yes' === $confirm->new_ticket_needed ) {
                $confirm->new_ticket_id = $io->ask( 'Please enter the ticket ID: ' );
            }
            // Now we can determine the value of $confirm->documentation (either 'N/A', 'Updated', or a ticket ID).
            if ( 'no' === $confirm->documentation ) {
                $confirm->documentation = 'N/A';
            } elseif ( 'yes' === $confirm->documentation && isset( $confirm->new_ticket_needed ) && 'no' === $confirm->new_ticket_needed ) {
                $confirm->documentation = 'Updated';
            } else {
                $confirm->documentation = $confirm->new_ticket_id;
            }
            $checklist = array_merge(
                array(
                    "* [{$confirm->style}] Updated version in style.css or plugin file",
                    "* [{$confirm->changelog}] Updated CHANGELOG.md to include jira ticket",
                    "* [{$confirm->readme}] Updated README.md for new functionality",
                    "* [{$confirm->assets}] Built assets for production (npm run build:production)",
                    "* [{$confirm->documentation}] Updated the documentation (N/A, Updated, or a ticket ID)",
                ),
                $checklist
            );
            $io->write( self::console( "\nChecklist created successfully!!!" ) );
        }//end if

        // Creates the checklist string that will be outputted to the checklist.md.
        // This is meant as a temporary solution (checklist.md), towards CI/CD.
        $dt                = new \DateTime( 'now', new \DateTimeZone( 'America/Vancouver' ) );
        $checklist_string  = sprintf( 'Created at %s', $dt->format( 'Y-m-d g:i a' ) );
        $checklist_string .= "\n\n" . implode( "\n", $checklist );

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        file_put_contents( $checklist_file, $checklist_string );
    }//end postProductionChecks()


    /**
     * Helper function to return checkmark or Fail for checklist.
     *
     * @param integer $status The status ( 0, or number greater than 0 ).
     *
     * @return string
     */
    private static function get_checkmark_type( int $status ): string {
        $output = 'Fail';
        if ( 0 === $status ) {
            $output = '✓';
        }

        return $output;
    }//end get_checkmark_type()


    /**
     * Checklist for post production for Common.
     *
     * @return void
     */
    public static function postProductionChecksForCommon(): void {
        $checklist = array(
            '[] Updated CHANGELOG.md to include jira ticket',
            '[] Updated README.md for new functionality',
            '[] Verified coding standards (phpcs)',
            '[] Run PHP tests',
        );

        echo "****** CHECKLIST ******\n\n";
        echo implode( "\n", $checklist ) . "\n\n";
    }//end postProductionChecksForCommon()


    /**
     * Checklist for post production for Scripts.
     *
     * @return void
     */
    public static function postProductionChecksForScripts(): void {
        self::postProductionChecksForCommon();
    }//end postProductionChecksForScripts()


    /**
     * Helper console function that returns formatted messages for the Composer\IO\IOInterface.
     *
     * @param string $msg     The message.
     * @param string $msg_type The message type, in order to wrap text, see $log_levels for allowed.
     *
     * @return string
     */
    public static function console( string $msg, string $msg_type = 'info' ): string {
        $log_levels = array(
            'error',
            'warning',
            'info',
        );
        if ( in_array( $msg_type, $log_levels, true ) ) {
            $msg = "<{$msg_type}>{$msg}</{$msg_type}>";
        }

        return $msg;
    }//end console()
}//end class
