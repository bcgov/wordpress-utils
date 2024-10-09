#!/usr/bin/env bash 
ROOT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/../"
# Accomodate zsh
if [ ! -f "$ROOT_PATH/.env" ]; then 
    ROOT_PATH="$(dirname $(readlink -f $0))/../"
fi
# Load in the env file
source "$ROOT_PATH.env"

# Set up WordPress unit testing environment
wp_setup_tests() {
    docker exec -it dev-wordpress-php-fpm-1 /bin/sh -c "/usr/bin/setup-tests.sh wordpress_test root ${MYSQL_ROOT_PASSWORD} ${MYSQL_HOST}"
}
  # Perform WordPress PHP unit tests on the current directory
# Should be used from a theme or plugin project's root
wp_test() {
    docker exec \
    -w /var/www/html/wp-content/${PWD//$CONTENT_DIR/} \
    -it dev-wordpress-php-fpm-1 \
    vendor/bin/phpunit --configuration vendor/bcgov/wordpress-utils/phpunit.xml.dist
}