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