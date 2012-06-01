#!/bin/bash

CODEHOME=~/code/
CODEHOME_ENT=~/code/artica
RPMHOME=/usr/src/packages

VERSION=`cat $CODEHOME/integria/integria.spec | grep "define version" | awk '{ print $3 }'`


echo "Creating source tarballs (/usr/src/rpm/SOURCES)"
mkdir -p /usr/src/rpm/SOURCES/
sudo rm -Rf /usr/src/rpm/SOURCES/integriaims*.tar.gz

# Opensource

cd $CODEHOME
sudo tar zcvf $RPMHOME/SOURCES/IntegriaIMS-$VERSION.tar.gz --exclude \.svn --exclude include/config.php --exclude enterprise --exclude *~ integria

# Enterprise

cd $CODEHOME_ENT/integria
sudo tar zcvf $RPMHOME/SOURCES/IntegriaIMS_enterprise-$VERSION.tar.gz --exclude \.svn --exclude *~ enterprise/*


