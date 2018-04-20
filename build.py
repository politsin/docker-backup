#!/usr/bin/python

import os

ver = "1.4"

if os.path.exists('/opt/docker-backup'):
  os.chdir('/opt/docker-backup')
  os.system('docker build -t docker-backup:1.4 .')
  os.system('docker tag docker-backup:1.4 docker-backup:latest')
# get travis repo
if os.environ['REPO']:
    repo = os.environ['REPO']
    bild = os.environ['TRAVIS_BUILD_NUMBER']
    os.system("docker build -f Dockerfile -t %s ." % (repo))
    os.system("docker tag %s %s:latest" % (repo, repo))
    os.system("docker tag %s %s:%s" % (repo, repo, ver))
    os.system("docker tag %s %s:%s.%s" % (repo, repo, ver, bild))
