#!/bin/bash

git clone https://github.com/Kipjr/docker-piwigo docker-piwigo

function GetRandom(){
    cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 24 | head -n 1
}

__TEMPLATE__DB_ROOT_PASSWORD=$(GetRandom)
__TEMPLATE__PWG_ADMIN_PASSWORD=$(GetRandom)
__TEMPLATE_LDAP_ADMIN_PASSWORD=$(GetRandom)
__TEMPLATE__LDAP_CONFIG_PASSWORD=$(GetRandom)
declare -x __TEMPLATE__DB_ROOT_PASSWORD
declare -x __TEMPLATE__PWG_ADMIN_PASSWORD
declare -x __TEMPLATE_LDAP_ADMIN_PASSWORD
declare -x __TEMPLATE__LDAP_CONFIG_PASSWORD

envsubst < docker-piwigo/docker-compose.template > docker-compose.yml
