#!/bin/bash

CODEHOME=~/code/integria/
CODEHOME_ENT=~/code/artica
RPMHOME=/usr/src/packages

VERSION="3.0dev"

echo "Creating source tarballs (/usr/src/rpm/SOURCES)"
sudo rm -Rf /usr/src/rpm/SOURCES/integriaims*.tar.gz

# Opensource

cd $CODEHOME
sudo tar zcvf $RPMHOME/SOURCES/IntegriaIMS-$VERSION.tar.gz --exclude \.svn --exclude config.php --exclude enterprise trunk

# Enterprise

cd $CODEHOME_ENT/integria/trunk
sudo tar zcvf $RPMHOME/SOURCES/IntegriaIMS_enterprise-$VERSION.tar.gz --exclude \.svn enterprise/*


