#!/bin/bash

echo "Europe/Moscow" > /etc/timezone                     
cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime 

# www-data user
usermod -d /var/www/ www-data
chsh -s /bin/bash www-data
if [[ "$RESTORE" == "true" ]]; then
  # Find last backup file
  : ${LAST_BACKUP:=$(aws s3 ls s3://$S3_BUCKET_NAME | awk -F " " '{print $4}' | grep ^$BACKUP_NAME | sort -r | head -n1)}
  
  # Download backup from S3
  aws s3 cp s3://$S3_BUCKET_NAME/$LAST_BACKUP $LAST_BACKUP
  
  # Extract backup
  tar xzf $LAST_BACKUP $RESTORE_TAR_OPTION
  echo "\$databases['default']['default']['password'] = '$DBPASS';" >> /var/www/html/sites/default/settings.php

  if [[ "$DBRESTORE" == "true" ]]; then
    mysql -u drupal -p$DBPASS drupal < /var/www/html/.db.sql
  fi

else
  if [[ "$DBDUMP" == "true" ]]; then
  
    /usr/local/bin/drush sql-dump --root=/var/www/html --result-file=$DBFILE $DBSKIP
    chown www-data:www-data /var/www/html/.db.sql
  fi
  # Get timestamp
  : ${BACKUP_SUFFIX:=.$(date +"%Y-%m-%d-%H-%M-%S")}
  readonly tarball=$BACKUP_NAME$BACKUP_SUFFIX.tar.gz
  
  # Create a gzip compressed tarball with the volume(s)
  echo "start tar with opts $BACKUP_TAR_OPTION"
  tar czf $tarball $BACKUP_PATHS $BACKUP_TAR_OPTION
  # Clean db file
  if [[ "$DBDUMP" == "true" and "$DBFILE" == "/var/www/html/.db.sql" ]]; then
    rm '/var/www/html/.db.sql'
  fi

  # Upload the backup to S3 with timestamp
  echo "start aws s3 upload"
  aws s3 --region $AWS_DEFAULT_REGION cp $tarball s3://$S3_BUCKET_NAME/$tarball
  echo "done"

  # Clean up
  rm $tarball
  
fi


