#!/usr/bin/perl

use strict;
use warnings;
chdir("/opt/docker-backup");
system("docker build -t docker-backup:1.0 .");
