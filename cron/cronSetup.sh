#!/bin/sh

# SETTINGS
FILE=./cronScript.sh
OUTPUTDIR=/var/www/therealgd/logs
OUTPUTFILE=import_reddit.json
IP=$(/sbin/ifconfig eth0 | grep 'inet addr' | cut -d: -f2 | awk '{print $1}')
PORT=8000
CONTROLLERPATH=/var/www/therealgd/src/Controller/SubmissionController.php

# WRITE OUT CRONFILE
cat<<EOF  > $FILE
* * * * * sed -i 's/127.0.0.1/$IP/g' $CONTROLLERPATH
* * * * * wget -q http://$IP:$PORT/import_reddit --output-document=$OUTPUTDIR/$$
EOF

# Create Directory and Perms
mkdir $OUTPUTDIR
chmod 644 -R $OUTPUTDIR

# CRONTAB AND START
crontab $FILE
service cron start
