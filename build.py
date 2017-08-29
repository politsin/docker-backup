#!/usr/bin/python

import os
if os.path.exists('/opt/docker-backup'):
  os.chdir('/opt/docker-backup')
os.system('docker build -t docker-backup:1.3 .')
os.system('docker tag docker-backup:1.3 docker-backup:latest')
