# WordPress Utils.

## Description

This should be included as dev dependency for all themes and plugins, which will then give coding standards, testing and checklists.

## The bin folder
###  Scan WP Patterns Script

The `scan-wp-patterns.php` script gets installed into the `vendor/bin` folder, and is used to do basic scanning of WordPress patterns.
Here are some of the things it scans
- checks image tags <img > to ensure the source is a valid url, which is defined by a php tag
- checks link tags <a> to ensure localhost is not part of link
- ... more to come as required
- It requires the `patterns` folder to be at the root of the theme or plugin.

#### Usage 
```json
...
"scripts": {
        "scan-wp-patterns": "@php vendor/bin/scan-wp-patterns.php",
}
...
```
## Classes

### \Bcgov\Script

#### \Bcgov\Script\Standards::phpcs

This script can be used in a theme or plugin for WordPress coding standards checks.

#### \Bcgov\Script\Standards::phpcbf

This script can be used in a theme or plugin for WordPress coding standard fixes.

#### \Bcgov\Script\Tests::phpunit

This script can be used for php unit testing.

#### \Bcgov\Script\Checklists::postProductionChecks

This is used to create a checklist, which creates a checklist.md in your root of your theme or plugin.

- Creates a file checklist.md in theme or plugin.
- Creates a static checklist of items that need to be completed.
- Creates a dynamic checklist, and automatically checks passed or failed, depending on item.
- ignores the @todo warning if the event comes from the checklist or production script.

## Composer.json
## Composer.json

Typical composer.json for theme / plugin

```
"require-dev": {
    "bcgov/wordpress-utils": "@dev"
},
"scripts" : {
    "setup": [
        "npm i",
        "@build"
    ],
    "build" : [
        "npm run build"
    ],
    "production" : [
        "npm run format:js",
        "npm run build:production",
        "@checklist"
    ],
    "scan-wp-patterns": "@php vendor/bin/scan-wp-patterns.php",
    "checklist" : [
        "Bcgov\\Script\\Checklists::postProductionChecks"
    ],
    "phpcs": [
        "Bcgov\\Script\\Standards::phpcs"
    ],
    "phpcbf": [
        "Bcgov\\Script\\Standards::phpcbf"
    ],
    "test": [
        "Bcgov\\Script\\Tests::phpunit"
    ],
    "coverage": [
        "./vendor/bcgov/scripts/vendor/bin/phpunit --coverage-html ./coverage/php/"
    ]
}
```

## Why you should use the latest version of this package:

> (_currently using the ^3.0.1 of wordpress-coding-standards_)

1. the latest version of the package has the latest version of the coding standards
2. this set of standards is compliant with the latest version of php (8.2.x)
3. the warnings/errors are more accurate and up to date

### How to upgrade to the latest version of this package:

Change the version of wordpress-utils in composer.json

        ```JSON
            '"require-dev": {',
            '...',
            '"bcgov/wordpress-utils": "2.0"',
            '...',
            '}',
        ```

> The default is: "@dev" which will use the latest version, but you should specify a version number (currently 2.0) to avoid unexpected changes.

### How to downgrade this package to the old version:

_If you would like to suppress the new errors and warnings, (NOT RECOMMENDED), you can downgrade to the 1.1.1 version of this package._

Change the version of wordpress-utils in composer.json

        ```JSON
            '"require-dev": {',
            '...',
            '"bcgov/wordpress-utils": "1.1.1"',
            '...',
            '}',
        ```

> DOWNGRADING IS NOT RECOMMENDED: this will prevent the new errors and warnings, and lower the quality of the code.

### See this link for more info: https://make.wordpress.org/core/2023/08/21/wordpresscs-3-0-0-is-now-available/
