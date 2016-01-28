#!/bin/bash

MODULES=( ebooks-en hesperian_health iicba infonet kaos-en math_expression \
	  medline_plus musictheory olpc powertyping practical_action scratch \
	  understanding_algebra wikipedia_for_schools rpi_guide windows_apps asst_medical )


DEST_PATH=/var/www/modules
RSYNC_SOURCE=rsync://dev.worldpossible.org/rachelmods/

CMDNAME=$0
Usage () {
    echo "$CMDNAME [options]"
    echo "  Options:"
    echo "    --source       RACHEL content server. (default '$RSYNC_SOURCE')"
    echo "    --help         Display help message"
    echo ""
}

while [ true ]; do
    case $1 in 
        -h | --help | -\?)
            Usage
            exit 0
            ;;
        --source) 
            RSYNC_SOURCE="$2"
            shift 2
            ;;
	--*)
	    echo "$CMDNAME: invalid option '$1'"
	    exit 1
	    ;;
        *) 
            break
            ;;
    esac
done

if [ $# -gt 0 ]; then
    echo "$CMDNAME: too many arguments"
    exit 1
fi

if [ `id -u` != "0" ]; then
    echo "$CMDNAME: This script must be run as root"
    exit
fi

if [ "${RSYNC_SOURCE: -1}" != ":" ] && [ "${RSYNC_SOURCE: -1}" != "/" ]; then
    RSYNC_SOURCE="${RSYNC_SOURCE}/"
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
