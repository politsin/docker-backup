#!/usr/bin/perl

use strict;
use warnings;
chdir("/opt/docker-backup");
system("docker build -t docker-backup:1.2 .");
system("docker tag docker-backup:1.2 docker-backup:latest");
