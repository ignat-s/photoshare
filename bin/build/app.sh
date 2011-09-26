#!/bin/sh

cd $(dirname $0)
cd ../..

project_dir=$(pwd)

# Build bootstrap
$project_dir/vendor/bundles/Sensio/Bundle/DistributionBundle/Resources/bin/build_bootstrap.php

# Install assets
$project_dir/app/console assets:install --symlink $project_dir/web

# Clear cache
$project_dir/app/console cache:clear --no-warmup --env prod
$project_dir/app/console cache:clear --no-warmup --env dev

# Initializing dirs
documents_upload_dir="$project_dir/app/uploads"

create_dir_if_not_exists()
{
    DIR=$1

    if [ ! -d "$DIR" ]; then
        mkdir $DIR
        echo "Created $DIR directory"
    fi
}

create_dir_if_not_exists $documents_upload_dir