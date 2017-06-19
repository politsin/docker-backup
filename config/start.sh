#!/bin/bash

echo "Europe/Moscow" > /etc/timezone                     
cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime 

# www-data user
usermod -d /var/www/ www-data
chsh -s /bin/bash www-data

echo "start dump"

/usr/local/bin/drush sql-dump --root=/var/www/html --result-file=/var/www/html/.adminer.sql --skip-tables-list=cache,cache_*,search_*,watchdog,history,sessions
chown www-data:www-data /var/www/html/.adminer.sql

if [[ "$RESTORE" == "true" ]]; then
  # Find last backup file
  : ${LAST_BACKUP:=$(aws s3 ls s3://$BUCKET | awk -F " " '{print $4}' | grep ^$BACKUP_NAME | sort -r | head -n1)}
  
  # Download backup from S3
  aws s3 cp s3://$BUCKET/$LAST_BACKUP $LAST_BACKUP
  
  # Extract backup
  tar xzf $LAST_BACKUP $RESTORE_TAR_OPTION
else
  # Get timestamp
  : ${BACKUP_SUFFIX:=.$(date +"%Y-%m-%d-%H-%M-%S")}
  readonly tarball=$BACKUP_NAME$BACKUP_SUFFIX.tar.gz
  
  # Create a gzip compressed tarball with the volume(s)
  echo "start tar"
  echo "opts $BACKUP_TAR_OPTION"
  tar czf $tarball $BACKUP_PATHS $BACKUP_TAR_OPTION
  echo "end tar"

  # Upload the backup to S3 with timestamp
  aws s3 --region $REGION cp $tarball s3://$BUCKET/$tarball
  echo "end aws cp"

  # Clean up
  rm $tarball
  
fi
