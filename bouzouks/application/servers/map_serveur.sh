#!/bin/bash

pid_file="application/servers/map_serveur.pid"
log_file="application/logs/node-$(date +%Y-%m-%d).log"

# Si l'arrêt est demandé, on arrête le serveur Node.js
if [ "$1" == "stop" ]
then
	if [ -f $pid_file ]
	then
		kill $(cat $pid_file)
		rm $pid_file
	fi
fi

# Si le lancement est demandé, on lance le serveur Node.js
if [ "$1" == "start" ]
then
	#node application/servers/map_server.js 2>>application/logs/node-$(date +%Y-%m-%d).log 
	node application/servers/map_server.js > /dev/null &
	echo $! > $pid_file
fi
