### 2.3.1 October 16, 2024
- Fix scripts for PHPUnit test to make them work locally([DESCW-2680](https://citz-gdx.atlassian.net/browse/DESCW-2680))

### 2.3.0 October 7, 2024
- rename wordpress-scripts to wordpres-utils in the README ([DESCW-2671](https://citz-gdx.atlassian.net/browse/DESCW-2671))

### 2.1.3 May 2, 2024
### 2.2.0 May 2, 2024
- Integrate reusable unit tests by adding reusable scaffolding. ([DESCW-2664](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-2664))

### 2.1.3 May 2, 2024
- phpcs updates fixes camelCase issue ([DESCW-2280](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-2280))

### 2.1.2 March 8, 2024
- Changed how the paths used by the phpcs/phpcbf/phpunit commands where run to properly escape the whole path and not just the vendor dir. Fixes issue with running phpcs/phpcbf on windows.
- Moved to using the php built in file_put_contents() for checklist writing instead of shell/echo commands. ([DESCW-2152](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-2152))

### 2.1.1 February 7, 2024

- Added a directory exclusion for additional dist|-named| directories, specifically to allow use of the Vite builder tooling and create an alternate dist directory such 'dist-vue' to build to in order to not conflict with wp-scripts output ([ENG-109](https://apps.itsm.gov.bc.ca/jira/browse/ENG-109))

### 2.1.0 January 11, 2024

- Added PHPCompatibilityWP rule to check for PHP 7.4-8.3 issues ([DESWC-1895](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-1895))
- Removed composer.json from the .gitattributes export ignore.

### 2.0.2 January 04, 2024

- Changed Standards::phpcbf to fix all warnings and errors by default ([DESCW-1744](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-1744))

### 2.0.1 October 25, 2023

- Fixed bug in Checklists logic causing error when certain options are selected ([DESCW-1665](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-1665))

### 2.0.0 October 12, 2023

- Updated wordpress coding standards to 3.0.1 ([DESCW-1503](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-1503))
- Added scripting to prompt user whether to accept upgrade, and why to do so.
- Added user message to instruct user how to decline an upgrade in coding standards for their repo.

### 1.1.1 March 27, 2023

- Added PHPCS rule to enforce space indentation. ([DESCW-977](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-977))

### 1.1.0 January 17, 2023

- Added documentation questions to checklist. ([DESENG-244](https://apps.itsm.gov.bc.ca/jira/browse/DESENG-244))

### 1.0.1 April 06, 2022

- Fix for scripts failing if there are spaces in the directory path ([DESCW-266](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-266))

### 1.0.0 February 25, 2022

- Moved from WordPress Common([DESCW-168](https://apps.itsm.gov.bc.ca/jira/browse/DESCW-168))
