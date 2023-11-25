#!/bin/bash
set -eux
echo "Running $0"

PIWIGO_PATH=${1-"../docker-piwigo"}
VERSION_PHP=${2-8.1}
VERSION_PIWIGO=${3-13.7}
declare -x VERSION_PHP
declare -x VERSION_PIWIGO

if [ "$(basename $PWD)" == .tests ]; then 
    cd ..
fi
ROOTPATH="$PWD"
LDAP_PATH=$(realpath "$PWD" )
BASE=$(basename "$LDAP_PATH" )

###
### ldap_login
###

#check for path
if [ -d "$PIWIGO_PATH" ]; then
    # local path of piwigo found
    PIWIGO_PATH=$(realpath "$PIWIGO_PATH" )
    cd "$PIWIGO_PATH" || exit 1
else
    # no local path of piwigo found
    echo "unable to find local repo of docker-piwigo, check your arguments"
    exit 1
fi


cd "$PIWIGO_PATH" || exit 1
echo -e "\nCheck if ${BASE} is copied to piwigo container"
if [ "$(docker-compose run --rm --entrypoint "bash -c" piwigo.php "ls /app/piwigo/plugins/${BASE} 2>/dev/null"  | wc -l)" == 0 ];then
    echo "Copy ldap_logon to container:/app/piwigo/plugins"
    docker cp "$LDAP_PATH" piwigo.php:/app/piwigo/plugins/
else 
    read -i Y -t 5 -p "Replace previous ${BASE} (Y/n)" answer
    EXITVALUE=$?
    if [ "$answer" == 'Y' ] || [ $EXITVALUE -gt 128 ];then
        echo -e "\nRemoving ${BASE} from container:/app/piwigo/plugins"
        docker-compose run --rm --entrypoint "bash -c" piwigo.php "/bin/rm -rf /app/piwigo/plugins/${BASE}/"
        echo -e "\nAdding ${BASE} to container:/app/piwigo/plugins"
        docker cp "$LDAP_PATH" piwigo.php:/app/piwigo/plugins/
    fi
fi

echo -e "\nCheck if phpunit is installed"
docker compose run --entrypoint 'bash -c' -it --rm  piwigo.phpunit "test -f /app/piwigo/plugins/${BASE}/vendor/bin/phpunit"  2> /dev/null;
exist=$?;  # 0=succes, 1=missing
if [ $exist -eq 1 ]; then
    cd "$PIWIGO_PATH" || return
    echo "Install phpunit using composer"
    docker-compose run --rm piwigo.composer composer require --dev phpunit/phpunit # composer.json  composer.lock  vendor
    cd "$ROOTPATH" || return
else
   echo "/app/piwigo/plugins/${BASE}/vendor/bin/phpunit exists"
fi

echo -e "\nRun tests"
cd "$PIWIGO_PATH" || return
docker-compose run --rm piwigo.phpunit  \
    --bootstrap /app/piwigo/plugins/${BASE}/vendor/autoload.php \
    --configuration /app/piwigo/plugins/${BASE}/.tests/phpunit.xml \
    /app/piwigo/plugins/${BASE}/.tests/LdapLoginTest.php
cd "$ROOTPATH" || return

echo -e "\nshutdown containers"
cd "$PIWIGO_PATH" || return
#docker-compose down

echo -e "\nExiting $0\n"
