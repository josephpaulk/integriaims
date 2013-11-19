#!/bin/bash
source build_vars.sh

if [ ! -d $RPMHOME/RPMS ]; then
	mkdir -p $RPMHOME/RPMS || exit 1
fi

echo "Creating RPM packages in $RPMHOME/RPMS"

rpmbuild -ba $CODEHOME/integria.spec || exit 1

# Enterprise 
rpmbuild -ba $INTHOME_ENT/enterprise/integria_enterprise.spec || exit 1

exit 0

