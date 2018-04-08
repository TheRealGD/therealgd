# Setting up the CRON job

Provided in /cron directory is a cronSetup.sh shell script.
This script does a few things based off of the environment on the dev box.

Feel free to adapt as necessary for production.

1) The settings at the beginning of the setup script idenfity the following:

$FILE - The output file that gets created by the setup script.
$OUTPUTDIR - When the CRON runs where the JSON output file from the website will be written to.
$OUTPUTFILE - Name of the file in the $OUTPUTFILE directory.
$IP - This command should be left as is, works with ubuntu to get the external IP of the server so that the CRON can hit it.
$PORT - The port that the $IP site is working off off, production would be 80, dev 8000 or other.
$CONTROLLERPATH - Absolute path to the SubmissionController, on dev it has 127.0.0.1 as the ip from the REPO, so I replace 127.0.0.1 with the $IP gotten in the above variable, this is incase Jenkins overwrites the SubmissionController, the CRON still resolves as a success.

# Running the CRON

Set the cron setup script to executable, and run it.
$> chmod +x ./cronSetup.sh
$> ./cronSetup

This will output a file of the name $FILE along side cronSetup.sh

The cron should be all set, run the following to verify there are two lines in CRONTAB matching the two lines in the $FILE
$>crontab -e
