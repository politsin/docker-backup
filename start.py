#!/usr/bin/python


import os
import sys
import time
import string
import random
import subprocess
import urllib2
import json
import subprocess

# Vars
siteroot = "/var/www/html"
settings = siteroot + "/sites/default/settings.php"
pass8x = ''.join(random.choice(string.ascii_uppercase + string.digits) for _ in range(8));

# Backup
tz = os.getenv('TIMEZONE', '') # 'Europe/Moscow'
backup_name = os.getenv('BACKUP_NAME', '')
backup_path = os.getenv('BACKUP_PATHS', '/var/www/html')
tar_options = os.getenv('BACKUP_TAR_OPTION', '')
restoreflag = os.getenv('RESTORE', 0) #['', 'restore']
tar_opts_restore = os.getenv('BACKUP_TAR_OPTION_RESTORE', '')

# DB Settings:
dbfile = os.getenv('DBFILE', siteroot + "/.db.sql")
dbdump = os.getenv('DBDUMP', '') # opts: ['', 'drush', 'mysql', 'postgre']
dbname = os.getenv('DBNAME', 'drupal')
dbuser = os.getenv('DBUSER', 'drupal')
dbpass = os.getenv('DBPASS', pass8x)
dbhost = os.getenv('DBHOST', 'localhost')
dbskip = os.getenv('DBSKIP', '')
dbrestore = os.getenv('DBRESTORE', '') #['', 'mysql', 'postgre']

# AWS Settings
aws_region = os.getenv('AWS_DEFAULT_REGION', 'eu-west-1')
bucket = os.getenv('S3_BUCKET_NAME', '')

# Message
mattermost = os.getenv('MATTERMOST', '')


# Send Message
def sendLog(status, msg):
    # mattermost
    if len(mattermost) > 10:
        post_to_mattermost(mattermost, status, backup_name, msg)
    print (msg)
    return;

# Exec Status & Log
def execLog(result, ok, fail):
    if result == 0:
        print (ok)
    else:
        print (fail)
        sendLog ('FAIL', '@all ' + backup_name + ' ' + fail)
        sys.exit()
    return;

def emoji(notificationtype):
    return {
        "FAIL": ":fire:",
        "OK": ":four_leaf_clover:",
        "START BCP": ":rocket:",
        "START RST": ":cyclone:",
    }.get(notificationtype, "")

def encode_special_characters(text):
    text = text.replace("%", "%25")
    text = text.replace("&", "%26")
    return text

def post_to_mattermost(webhook, status, backup_name, msg):
    data = {}
    data['text'] = emoji(status) + " [" + backup_name + "] " + encode_special_characters(msg)
    req = urllib2.Request(webhook)
    req.add_header('Content-Type','application/json')
    payload = json.dumps(data)
    response = urllib2.urlopen(req, payload)
    if response.getcode() is not 200:
        print 'Posting to mattermost failed'

def backup():
    # Say 'Start'
    sendLog ('START BCP', 'Backup start [%s]' % (backup_path))
    print ("Backup")

    if len(tz) > 4:
        os.system('echo "%s" > /etc/timezone' % (tz))
        os.system('cp /usr/share/zoneinfo/%s /etc/localtime' % (tz))

    if len(dbdump) > 4:
        # Create drush drupal-DBdump
        if dbdump == 'drush':
            drush = "/usr/local/bin/drush --root=%s" % (siteroot)
            drushdump = os.system("%s sql-dump --result-file=%s %s" % (drush, dbfile, dbskip))
            execLog(drushdump, 'OK: DRUSH db-dump', 'ERROR: Failed to create DRUSH db-dump')
        # Create mysqml-DBdump
        if dbdump == 'mysql':
            mysqldump = os.system("mysqldump -u%s -p%s -h%s %s > %s" % (dbuser, dbpass, dbhost, dbname, dbfile))
            execLog(mysqldump, 'OK: MySQL db-dump', 'ERROR: Failed to create MySQL db-dump')
        # Create postgre-DBdump
        if dbdump == 'postgre':
            print "pg_dump -U=%s -h=%s %s > %s" % (dbuser, dbhost, dbname, dbfile)
            host = ''
            if len(dbhost) > 4 and dbhost != 'localhost':
                host = "-h %s" % (dbhost)
            pgdump = os.system("pg_dump -U %s %s %s > %s" % (dbuser, host, dbname, dbfile))
            execLog(pgdump, 'OK: PostgreSQL db-dump', 'ERROR: Failed to create PostgreSQL db-dump')
        # Fix dump owner
        os.system("chown www-data:www-data %s" % (dbfile))

    # Create tarball
    backup_suffix = time.strftime(".%Y-%m-%d-%H-%M-%S.tar.gz", time.localtime())
    tarball = backup_name + backup_suffix
    tar = os.system("tar czf %s %s %s" % (tarball, backup_path, tar_options))
    if tar == 256:
        tar = 0 # Tar exit code=1 (high byte of a 16-bit value) IS OK `Some files differ'
    execLog(tar, 'OK: tar files', 'ERROR: Failed to create tar files')

    # Clean db file.
    if len(dbdump) > 4 and dbfile == "/var/www/html/.db.sql":
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
    # Say 'Start'
    sendLog ('START RST', 'Restore start')
    print ("Restore")

    # Find Last Backup Name (Name + exec->trim->explode->arr[1])
    cmd = "aws s3 ls s3://%s | grep %s | sort -r | head -n1" % (bucket, backup_name)
    backup = backup_name + os.popen(cmd).read().strip("\n").split(backup_name)[1]
    bcpfail = 0;
    if len(backup) < 10:
        bcpfail = 'fail';
    execLog(bcpfail, 'Last Backup Name: %s' % (backup), 'ERROR: Failed to get Last Backup Name')

    # Download from AWS S3
    s3 = os.system("aws s3 cp s3://%s/%s %s" % (bucket, backup, backup))
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

if restoreflag == 'restore':
    restore()
else:
    backup()
