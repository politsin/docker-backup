#!/bin/bash

docker stop dockup
docker rm dockup
docker-compose up dockup
