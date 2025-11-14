#!/bin/bash

TAG="latest"

while getopts t: flag
do
    case "${flag}" in
        t) TAG=${OPTARG};;
        *) echo "Usage: $0 -t <tag>"; exit 1;;
    esac
done

docker run -d -p 8000:8000 --name my_app_container my_app:$TAG