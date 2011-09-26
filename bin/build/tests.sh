#!/bin/sh

cd $(dirname $0)
cd ../..

project_dir=$(pwd)

$project_dir/app/console cache:clear --no-warmup --env test

$project_dir/app/console doctrine:database:drop --connection default --force --env test
$project_dir/app/console doctrine:database:create --connection default --env test
$project_dir/app/console doctrine:schema:create --em default --env test