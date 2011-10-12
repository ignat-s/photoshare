#!/bin/sh

# Initializing dirs
source_dir=$(dirname $(dirname $0))

target_dir=$1

assert_dir()
{
	if [ ! -d "$@" ]; then
		log_msg "$@ is not a directory"
		exit
	fi
}

log_msg() { echo $@; }
log_usage_msg() { log_msg "Usage: deploy.sh SOURCE_DIR TARGET_DIR"; }


if [ ! $source_dir -o ! $target_dir ]; then
	log_usage_msg
	exit
fi

assert_dir $source_dir
rsync -avz --exclude=".git" --exclude="app/cache/*" --exclude="app/logs/*"  --exclude="web/bundles/*" --exclude=".idea" --exclude="photostorage/*"  $source_dir/ $target_dir/