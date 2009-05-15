#!/bin/bash

JAVA_HOME=/usr/java/j2sdk1.4.2_07
GAPSLITE_HOME=/usr/local/src/GS_CFG_GEN

# Do NOT modify below this line
LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/lib:$GAPSLITE_HOME/lib/`uname -m`
export LD_LIBRARY_PATH

$JAVA_HOME/bin/java -classpath $GAPSLITE_HOME/lib/gapslite.jar:$GAPSLITE_HOME/lib/bcprov-jdk14-124.jar:$GAPSLITE_HOME/config com.grandstream.cmd.TextEncoder $*

