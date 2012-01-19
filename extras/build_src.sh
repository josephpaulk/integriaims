#!/bin/bash

CODEHOME=~/code/integria/
CODEHOME_ENT=~/code/artica
RPMHOME=/usr/src/packages

VERSION=`cat $CODEHOME/trunk/integria.spec | grep "define version" | awk '{ print $3 }'`


echo "Creating source tarballs (/usr/src/rpm/SOURCES)"
sudo rm -Rf /usr/src/rpm/SOURCES/integriaims*.tar.gz

# Opensource

cd $CODEHOME
sudo tar zcvf $RPMHOME/SOURCES/IntegriaIMS-$VERSION.tar.gz --exclude \.svn --exclude trunk/include/config.php --exclude enterprise --exclude *~ trunk

# Enterprise

cd $CODEHOME_ENT/integria/trunk
sudo tar zcvf $RPMHOME/SOURCES/IntegriaIMS_enterprise-$VERSION.tar.gz --exclude \.svn --exclude *~ enterprise/*


