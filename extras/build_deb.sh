#!/bin/bash

CODEHOME=~/code/integria/trunk
CODEHOME_ENT=~/code/artica/code
RPMHOME=/usr/src/packages

mkdir -p $RPMHOME/DEB 2> /dev/null

echo "Creating DEB packages in $RPMHOME/DEB"
cd $CODEHOME/DEBIAN
bash ./make_deb_package.sh 
mv /tmp/Integria*.deb $RPMHOME/DEB

cd $CODEHOME_ENT/integria/trunk/enterprise/DEBIAN
bash ./make_deb_package.sh
mv /tmp/Inte*.deb $RPMHOME/DEB

