#!/usr/bin/python

import os
if os.path.exists('/d/temp')
  os.chdir('/opt/docker-backup')
else
  os.chdir('politsin/docker-backup')
os.system('docker build -t docker-backup:1.3 .')
os.system('docker tag docker-backup:1.3 docker-backup:latest')
