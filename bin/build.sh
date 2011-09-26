#!/bin/sh

# Initializing dirs
bin_dir=$(dirname $0)

COMMAND="$1"
if [ -n "$COMMAND" ]; then
    case $COMMAND in
    reinstall-vendors)
        $bin_dir/build/vendors.php install --reinstall
        ;;
    install-vendors)
        $bin_dir/build/vendors.php install
        ;;
    update-vendors)
        $bin_dir/build/vendors.php update
        ;;
    *)
        echo "Unknown command $COMMAND"
        ;;
    esac
fi

$bin_dir/build/app.sh

echo "Build completed"