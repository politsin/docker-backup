#!/usr/bin/python


import os
import sys
import time
import string
import random
import subprocess

# Vars
siteroot = "/var/www/html"
settings = siteroot + "/sites/default/settings.php"
dbskip = "--structure-tables-list=cache,cache_*,cachetags,search_*,watchdog,history,sessions"
tar_opts = "--exclude=/var/www/html/cmd-* --exclude=/var/www/html/adminer.php --exclude=.git"
pass8 = ''.join(random.choice(string.ascii_uppercase + string.digits) for _ in range(8));
tar_opts_restore = ""

# Backup
restore = os.getenv('RESTORE', 0) #['', 'restore']
backup_name = os.getenv('BACKUP_NAME', '')
backup_path = os.getenv('BACKUP_PATHS', '/var/www/html')
tar_options = os.getenv('BACKUP_TAR_OPTION', tar_opts)

# DB Settings:
dbfile = os.getenv('DBFILE', siteroot + "/.db.sql")
dbdump = os.getenv('DBDUMP', 'drush') # opts: ['', 'drush', 'mysql', 'postgre']
dbname = os.getenv('DBNAME', 'drupal')
dbuser = os.getenv('DBUSER', 'drupal')
dbpass = os.getenv('DBPASS', pass8)
dbhost = os.getenv('DBHOST', 'localhost')
dbskip = os.getenv('DBSKIP', '')
dbrestore = os.getenv('DBRESTORE', 'mysql') #['', 'mysql', 'postgre']

# AWS Settings
aws_region = os.getenv('AWS_DEFAULT_REGION', 'eu-west-1')
bucket = os.getenv('S3_BUCKET_NAME', '') 

def logMsg(msg):
    print (msg)
    return;

def sendLog(status, msg):
    print (msg)
    return;

def execLog(result, ok, fail):
    if result == 0:
        logMsg (ok)
    else:
        logMsg (fail)
        sendLog ('FAIL', '@all ' + fail)
        sys.exit()
    return;

def backup():
    print ("Backup")

    if len(dbdump) > 4:
        # Create drupal-DBdump
        if dbdump == 'drush':
            drushdump = "/usr/local/bin/drush sql-dump --root=%s" % (siteroot)
            backup = os.system("%s --result-file=%s %s" % (drushdump, dbfile, dbskip))
            execLog(backup, 'OK: Dump DB', 'ERROR: Failed to create DB dump')
        # TODO: Create mysqml-DBdump
        if dbdump == 'mysqml':
            sendLog ('FAIL', 'Function is not ready yet, TODO')
            sys.exit()
        # TODO: Create postgre-DBdump
        if dbdump == 'postgre':
            sendLog ('FAIL', 'Function is not ready yet, TODO')
            sys.exit()
        # Fix dump owner
        os.system("chown www-data:www-data %s" % (dbfile))

    # Create tarball
    backup_suffix = time.strftime(".%Y-%m-%d-%H-%M-%S.tar.gz", time.gmtime())
    tarball = backup_name + backup_suffix
    logMsg ('start tar with opts %s' % (tar_options))
    tar = os.system("tar czf %s %s %s" % (tarball, backup_path, tar_options))
    execLog(tar, 'OK: tar files', 'ERROR: Failed to create tar files')

    # Clean db file.
    if dbdump == 'drupal' and dbfile == "/var/www/html/.db.sql":
        rmdump = os.system("rm -f '/var/www/html/.db.sql'") # this file, not %s dbfile
        execLog(rmdump, 'OK: rm %s' % (dbfile), 'ERROR: Failed to rm %s' % (dbfile))

    # Upload the backup to S3 with timestamp
    s3 = os.system("aws s3 --region %s cp %s s3://%s/%s" % (aws_region, tarball, bucket, tarball))
    execLog(s3, 'OK: s3 upload', 'ERROR: Failed to upload backup to AWS')

    # Remove old files.
    rmtar = os.system("rm -f %s" % tarball)
    execLog(rmtar, 'Removed old backup file', 'ERROR: Failed to remove old backup file')

    # Say 'OK'
    sendLog ('OK', 'Backup Completed')
    return;

def restore():
    print ("Restore")

    # Find Last Backup Name (Name + exec->trim->explode->arr[1])
    cmd = "aws s3 ls s3://%s | grep %s | sort -r | head -n1" % (bucket, backup_name)
    backup = backup_name + os.popen(cmd).read().strip("\n").split(backup_name)[1]
    bcpfail = 0;
    if len(backup) < 10:
        bcpfail = 'fail';
    execLog(bcpfail, 'Last Backup Name: %s' % (backup), 'ERROR: Failed to get Last Backup Name')

    # Download from AWS S3
    s3 = os.system("aws s3 cp --region %s s3://%s/%s %s" % (aws_region, bucket, backup, backup))
    execLog(s3, 'OK: Download Last Backup from AWS', 'ERROR: Failed to download last backup from AWS')

    # Un TAR
    untar = os.system("tar xzf %s %s" % (backup, tar_opts_restore))
    execLog(untar, 'OK: Unpack backup', 'ERROR: Failed to unpack Last Backup')

    # Write settings php
    if len(dbpass) >=6 and os.path.isfile(settings):
        os.system("chmod 755 %s/sites/default" % (siteroot))
        os.system("chmod 644 %s" % (settings))
        with open(settings, "a") as myfile:
            myfile.write("\n$databases['default']['default']['password'] = '%s';\n" % (dbpass))

    # Restore db
    if len(dbrestore) > 4:
        # Mysql db-restore.
        if dbrestore == 'mysql' and len(dbpass) >=3:
            mysql = os.system("mysql -u %s -p%s %s < %s" % (dbuser, dbpass, dbname, dbfile))
            execLog(mysql, 'OK: Restore DB dump', 'ERROR: Failed to restore DB dump')
        # TODO: Postgre db-restore.
        if dbrestore == 'postgre':
            sendLog ('FAIL', 'Function is not ready yet, TODO')
            sys.exit()

    # Say 'OK'
    sendLog ('OK', 'Restore Completed')
    return;

if restore == 'restore':
    restore()
else:
    backup()
