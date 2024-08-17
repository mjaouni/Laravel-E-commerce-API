#!/bin/bash

# Run PHP Code Sniffer
vendor/bin/phpcs --standard=phpcs.xml --ignore=vendor/,storage/,bootstrap/,config/ -n .

# Check the exit code of phpcs
if [ $? -ne 0 ]; then
    echo "PHP Code Sniffer detected issues. Running PHP Code Beautifier and Fixer..."
    vendor/bin/phpcbf --standard=phpcs.xml --ignore=vendor/,storage/,bootstrap/,config/ -n .
    
    # Re-run PHP Code Sniffer to ensure all issues are fixed
    vendor/bin/phpcs --standard=phpcs.xml --ignore=vendor/,storage/,bootstrap/,config/ -n .
    
    # Check if there are still issues after running phpcbf
    if [ $? -ne 0 ]; then
        echo "There are still coding standard issues after running PHP Code Beautifier and Fixer."
        exit 1
    fi
fi