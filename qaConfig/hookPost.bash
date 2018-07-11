#!/usr/bin/env bash
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
