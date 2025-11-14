#!/bin/bash

TAG="latest"

while getopts t: flag
do
    case "${flag}" in
        t) TAG=${OPTARG};;
        *) echo "Usage: $0 -t <tag>"; exit 1;;
    esac
done

docker build -t my_app:$TAG .

