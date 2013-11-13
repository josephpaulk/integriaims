#!/bin/bash
source build_vars.sh

if [ ! -d $RPMHOME/RPMS ]; then
	mkdir -p $RPMHOME/RPMS || exit 1
fi

echo "Creating RPM packages in $RPMHOME/RPMS"

# Console
rpmbuild -ba $CODEHOME/integria.centos.spec || exit 1

# Enterprise console
rpmbuild -ba $PANDHOME_ENT/enterprise/integria_enterprise.centos.spec || exit 1

exit 0

