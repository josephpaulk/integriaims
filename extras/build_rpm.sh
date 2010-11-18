#!/bin/bash

CODEHOME=~/code/integria
CODEHOME_ENT=~/code/artica
RPMHOME=/usr/src/packages

echo "Creating RPMs  at $RPMHOME/RPMS"

cd $CODEHOME
sudo rpmbuild -bb trunk/integria.spec
sudo rpmbuild -bb $CODEHOME_ENT/integria/trunk/enterprise/integria_enterprise.spec


