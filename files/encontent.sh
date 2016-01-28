#!/bin/bash

MODULES=( ebooks-en hesperian_health iicba infonet kaos-en math_expression \
	  medline_plus musictheory olpc powertyping practical_action scratch \
	  understanding_algebra wikipedia_for_schools rpi_guide windows_apps asst_medical )


DEST_PATH=/var/www/modules
RSYNC_SOURCE=rsync://dev.worldpossible.org/rachelmods/

CMDNAME=$0

if [ `id -u` != "0" ]; then
    echo "$CMDNAME: This script must be run as root"
    exit
fi

for module in ${MODULES[@]}; do
    echo
    echo "Syncing $module ..."
    sudo -u www-data rsync -rlptvz "${RSYNC_SOURCE}${module}" "$DEST_PATH"
    if [ $? -ne 0 ]; then
	sleep 1
	echo "Copying of $module failed or was aborted.  Exiting."
	exit
    fi
done
