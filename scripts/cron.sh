#!/bin/sh

# SETTINGS
FILE=./cronScript.sh
OUTPUTDIR=/var/www/therealgd/logs
OUTPUTFILE=import_reddit.json
BINPATH=/var/www/therealgd/bin/console
IMPORTCOMMAND=app:import-reddit

# WRITE OUT CRONFILE
cat<<EOF  > $FILE
* * * * * $BINPATH $IMPORTCOMMAND > $OUTPUTDIR/$OUTPUTFILE
EOF

# Create Directory and Perms
mkdir $OUTPUTDIR
chmod 644 -R $OUTPUTDIR

# CRONTAB AND START
crontab $FILE
service cron start
