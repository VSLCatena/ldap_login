#!/bin/bash
echo "Running $(realpath $0)"
VERSION_PHP=${1-8.1}
VERSION_PIWIGO=${2-13.6}
PIWIGO_PATH=${3}

declare -x VERSION_PHP
declare -x VERSION_PIWIGO

LDAP_PATH=$(realpath "$( dirname $0)/..")
echo LDAP_PATH: $LDAP_PATH

function GetRandom(){
    head -n 10 /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1
}

docker-compose down --volume

###
### Get Piwigo
###

#check for path
if [ -d "$PIWIGO_PATH" ]; then
    # path of Piwigo given
    PIWIGO_PATH=$(realpath "$PIWIGO_PATH" )
elif [ -d "../docker-piwigo" ]; then
    #path of ldap / piwigo share common parent
    PIWIGO_PATH=$(realpath ../docker-piwigo)
elif [ $(basename "$(realpath $LDAP_PATH/..)") == "docker-piwigo" ]; then
    #this repo is in subfolder of docker-piwigo
    PIWIGO_PATH=$(realpath "$LDAP_PATH/..")
else
    # no local path of piwigo found
    echo "unable to find local repo of docker-piwigo, cloning to parent directory"
    git clone https://github.com/Kipjr/docker-piwigo "$LDAP_PATH/../docker-piwigo"
    PIWIGO_PATH=$(realpath $LDAP_PATH/../docker-piwigo)
fi
# PIWIGO_PATH: /home/adminuser/docker/docker-piwigo/ldap_login/docker-piwigo
echo PIWIGO_PATH: $PIWIGO_PATH

###
### Prepare LDAP
###

/bin/rm $PIWIGO_PATH/docker-compose.override.yml
/bin/rm $PIWIGO_PATH/docker-compose.yml

if [ ! -f "$PIWIGO_PATH/docker-compose.override.yml" ]; then
    PASSWORD_LDAP_ADMIN=$(GetRandom)
    PASSWORD_LDAP_CONFIG=$(GetRandom)

    declare -x PASSWORD_LDAP_ADMIN
    declare -x PASSWORD_LDAP_CONFIG
    declare -x PIWIGO_PATH
    
    envsubst < $LDAP_PATH/.tests/docker-compose.override.template > $PIWIGO_PATH/docker-compose.override.yml
fi


echo -e  "\nCreate ldap bootstrap"
    envsubst < $LDAP_PATH/.tests/ldap_bootstrap.ldif.template > $PIWIGO_PATH/ldap_bootstrap.ldif


#docker cp /tmp/ldap_bootstrap.ldif piwigo.ldap:/container/service/slapd/assets/config/bootstrap/ldif/50-bootstrap.ldif #overwrite
#docker cp /tmp/ldap_bootstrap.ldif piwigo.ldap:/container/service/slapd/assets/config/bootstrap/ldif/custom/50-bootstrap.ldif
# docker-compose run --rm --name piwigo.ldap -d piwigo.ldap --copy-service


###
### Start
###

echo "Preparing Piwigo test environment"
# function phpversion piwigoversion DO_NOT_AUTOSTART_CONTAINERS_AFTER_SCRIPT
$PIWIGO_PATH/bin/start_piwigo.sh $VERSION_PHP $VERSION_PIWIGO "TRUE"

echo "Continuing $(realpath $0)"


echo -e "\nCreate container + volumes"
docker-compose up --no-start

echo -e "\nStart containers"
docker-compose --profile debug up  -d --no-recreate

cd "$PIWIGO_PATH" || exit 1
echo -e "\nCheck if ldap_login is copied to piwigo container"
if [ "$(docker-compose run --rm --entrypoint "bash -c" piwigo.php "ls /app/piwigo/plugins/ldap_login 2>/dev/null"  | wc -l)" == 0 ];then
    echo "Copy ldap_logon to container:/app/piwigo/plugins"
    docker cp "$LDAP_PATH" piwigo.php:/app/piwigo/plugins/
else 
    read -i Y -t 5 -p "Replace previous ldap_login (Y/n)" answer
    EXITVALUE=$?
    if [ "$answer" == 'Y' ] || [ $EXITVALUE -gt 128 ];then
        echo -e "\nRemoving ldap_login from container:/app/piwigo/plugins"
        docker-compose run --rm --entrypoint "bash -c" piwigo.php "/bin/rm -rf /app/piwigo/plugins/ldap_login/"
        echo -e "\nAdding ldap_login to container:/app/piwigo/plugins"
        docker cp "$LDAP_PATH" piwigo.php:/app/piwigo/plugins/
    fi
fi

# docker exec -i <container_name> mysql -u root -ppassword <mydb> < /path/to/script.sql

echo -e "\nCheck if phpunit is installed"
if [ "$( docker-compose run --rm --entrypoint "bash -c" piwigo.phpunit 'ls /app/vendor/bin/phpunit 2>/dev/null' | wc -l)" == 0 ];then
    cd "$PIWIGO_PATH" || return
    echo "Install phpunit using composer"
    docker-compose run --rm piwigo.composer composer require --dev phpunit/phpunit
    cd "$LDAP_PATH" || return
fi

echo -e "\nRun tests"
cd "$PIWIGO_PATH" || return
docker-compose run --rm piwigo.phpunit  --bootstrap vendor/autoload.php --configuration /piwigo/piwigo/plugins/ldap_login/.tests/phpunit.xml \
/piwigo/piwigo/plugins/ldap_login/.tests/LdapLoginTest.php
cd "$LDAP_PATH" || return

echo -e "\nCurrent credentials"
docker-compose config  | grep -i 'password'

echo -e "\nExiting $0\n"

