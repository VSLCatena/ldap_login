#!/bin/bash
echo "Running $0"

PIWIGO_PATH=${1-"../docker-piwigo"}
VERSION_PHP=${2-8.1}
VERSION_PIWIGO=${3-13.8}
declare -x VERSION_PHP
declare -x VERSION_PIWIGO

if [ "$(basename $PWD)" == .tests ]; then 
    cd ..
fi
ROOTPATH="$PWD"
LDAP_PATH=$(realpath "$PWD" )
PLUGIN_NAME=$(basename "$LDAP_PATH" )

###
### ldap_login
###

#check for path
if [ -d "$PIWIGO_PATH" ]; then
    # local path of piwigo found
    PIWIGO_PATH=$(realpath "$PIWIGO_PATH" )
    cd "$PIWIGO_PATH" || exit 1
    ./bin/start_piwigo.sh
    cd "$ROOTPATH"  || exit 1
else
    # no local path of piwigo found
    echo "unable to find local repo of docker-piwigo, check your arguments"
    exit 1
fi


cd "$PIWIGO_PATH" || exit 1
echo -e "\nCheck if $PLUGIN_NAME is copied to piwigo container"
if [ "$(docker-compose run --rm --entrypoint "bash -c" piwigo.php "ls /app/piwigo/plugins/$PLUGIN_NAME 2>/dev/null"  | wc -l)" == 0 ];then
    echo "Copy $PLUGIN_NAME to container:/app/piwigo/plugins"
    docker cp "$LDAP_PATH" piwigo.php:/app/piwigo/plugins/
else 
    read -i Y -t 5 -p "Replace previous $PLUGIN_NAME (Y/n)" answer
    EXITVALUE=$?
    if [ "$answer" == 'Y' ] || [ $EXITVALUE -gt 128 ];then
        echo -e "\nRemoving $PLUGIN_NAME from container:/app/piwigo/plugins"
        docker-compose run --rm --entrypoint "bash -c" piwigo.php "/bin/rm -rf /app/piwigo/plugins/$PLUGIN_NAME/"
        echo -e "\nAdding $PLUGIN_NAME to container:/app/piwigo/plugins"
        docker cp "$LDAP_PATH" piwigo.php:/app/piwigo/plugins/
    fi
fi

echo -e "\nCheck if phpunit is installed"
if [ "$( docker-compose run --rm --entrypoint "bash -c" piwigo.phpunit "ls /app/vendor/bin/phpunit 2>/dev/null" | wc -l)" == 0 ];then
    cd "$PIWIGO_PATH" || return
    echo "Install phpunit using composer"
    docker-compose run --rm  --workdir="/app/piwigo/plugins/$PLUGIN_NAME"  piwigo.composer /usr/bin/composer require --dev phpunit/phpunit # composer.json  composer.lock  vendor
    cd "$ROOTPATH" || return
fi

echo -e "\nRun tests"
cd "$PIWIGO_PATH" || return
docker-compose run --rm piwigo.phpunit  --bootstrap vendor/autoload.php --configuration /piwigo/plugins/$PLUGIN_NAME/.tests/phpunit.xml /piwigo/piwigo/plugins/$PLUGIN_NAME/.tests/LdapLoginTest.php
cd "$ROOTPATH" || return

echo -e "\nshutdown containers"
cd "$PIWIGO_PATH" || return
#docker-compose down

echo -e "\nExiting $0\n"
