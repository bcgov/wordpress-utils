# WordPress Scripts.

## Description
This should be included as dev dependency for all themes and plugins, which will then give coding standards, testing and checklists.




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

* Creates a file checklist.md in theme or plugin.
* Creates a static checklist of items that need to be completed.
* Creates a dynamic checklist, and automatically checks passed or failed, depending on item.
* ignores the @todo warning if the event comes from the checklist or production script.


## Composer.json 

Typical composer.json for theme / plugin

```
"require-dev": {
    "bcgov/wordpress-scripts": "@dev"
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
    "checklist" : [
        "Bcgov\Script\Checklists::postProductionChecks"
    ],
    "phpcs": [
        "Bcgov\Script\Standards::phpcs"
    ],
    "phpcbf": [
        "Bcgov\Script\Standards::phpcbf"
    ],
    "test": [
        "Bcgov\Script\Tests::phpunit"
    ],
    "coverage": [
        "./vendor/bcgov/scripts/vendor/bin/phpunit --coverage-html ./coverage/php/"
    ]
}
```