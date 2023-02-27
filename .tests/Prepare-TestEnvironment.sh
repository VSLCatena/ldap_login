#!/bin/bash

git clone https://github.com/Kipjr/docker-piwigo docker-piwigo

function GetRandom(){
    cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 24 | head -n 1
}
__TEMPLATE__PASSWORD1=$(GetRandom)
__TEMPLATE__PASSWORD2=$(GetRandom)
__TEMPLATE__PASSWORD3=$(GetRandom)
__TEMPLATE__PASSWORD4=$(GetRandom)
declare -x __TEMPLATE__PASSWORD1
declare -x __TEMPLATE__PASSWORD2
declare -x __TEMPLATE__PASSWORD3
declare -x __TEMPLATE__PASSWORD4

envsubst < docker-piwigo/docker-compose.template > docker-compose.yml
