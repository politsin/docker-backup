#!/bin/bash

# cd /opt/build/docker-backup
docker build . -t synstd/s3-dockup
docker tag synstd/s3-dockup synstd/s3-dockup:8.1.0
# docker push synstd/s3-dockup
# docker push synstd/s3-dockup:8.1.0
