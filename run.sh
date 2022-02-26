#!/bin/bash

watch() {
    php ./system/src/watch.php --dev
}

dev() {
    #mkdir -p ./system/config/ && cp ./config/* $_
    php -S localhost:8080 -t ./system/www/ ./script/router.php
}

module() {
    if [ $# -eq 0 ] 
    then
        echo "Usage: $0 module <module-name>"
    else
        php script/module.php $1
    fi
}

package() {
    php script/package.php
}

web() {
    if [ -d "./dist/www/" ]
    then
        php -S localhost:8080 -t ./dist/www/
        nl
    else
        echo "No dist directory, execute build command first"
    fi
}

clean() {
    rm -r ./dist/
}

nl() {
    echo
}

case "$1" in
    watch)
        watch
        ;;
    dev)
        dev
        nl
        ;;
    module)
        module $2
        ;;
    package)
        package
        ;;
    web)
        web
        ;;
    start)
        package
        echo "Starting server"
        nl
        web
        ;;
    clean)
        clean
        ;;
    *)
        echo "Usage: $0 {watch|dev|module|package|web|start|clean}"
esac
