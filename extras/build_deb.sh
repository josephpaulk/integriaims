#!/bin/bash
source build_vars.sh

if [ ! -d $RPMHOME/DEB ]; then
	mkdir -p $RPMHOME/DEB || exit 1
fi

echo "Creating DEB packages in $RPMHOME/DEB"

# Console
cd $CODEHOME/DEBIAN && bash ./make_deb_package.sh && mv ../*.deb $RPMHOME/DEB || exit 1

# Enterprise console
cd $PANDHOME_ENT/enterprise/DEBIAN && bash ./make_deb_package.sh && mv ../*.deb $RPMHOME/DEB || exit 1

exit 0

