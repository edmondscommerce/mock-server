#!/usr/bin/env bash
readonly DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
cd $DIR;
set -e
set -u
set -o pipefail
standardIFS="$IFS"
IFS=$'\n\t'
echo "
===========================================
$(hostname) $0 $@
===========================================
"

echo "Ensuring any child processes are killed"
pkill -f 'router[.]php'

echo "
===========================================
$(hostname) $0 $@ COMPLETED
===========================================
"
