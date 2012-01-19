#!/bin/bash

CODEHOME=~/code/integria
CODEHOME_ENT=~/code/artica
RPMHOME=/usr/src/packages

echo "Creating RPMs at $RPMHOME/RPMS"

REDHAT=`ls -la /etc/redhat-release | wc -l| awk '{ print $1}'`

cd $CODEHOME

if [ $REDHAT == 1 ]
then
	sudo rpmbuild -bb trunk/integria.fedora.spec
        sudo rpmbuild -bb $CODEHOME_ENT/integria/trunk/enterprise/integria_enterprise.fedora.spec
else
	sudo rpmbuild -bb trunk/integria.spec
	sudo rpmbuild -bb $CODEHOME_ENT/integria/trunk/enterprise/integria_enterprise.spec
fi

