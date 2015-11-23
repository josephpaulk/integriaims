#!/bin/bash
source build_vars.sh

# Add build string for nightly builds
if [ "$1" == "nightly" ]; then
	LOCAL_VERSION="$VERSION-$BUILD"
else
	LOCAL_VERSION=$VERSION
fi

if [ ! -d $RPMHOME/SOURCES ]; then
	mkdir -p $RPMHOME/SOURCES || exit 1
fi

echo "Creating source tarballs in $RPMHOME/SOURCES"

# Console
cd $CODEHOME; cd .. && tar zcvf $RPMHOME/SOURCES/IntegriaIMS-$LOCAL_VERSION.tar.gz --exclude \.git --exclude \.gitignore --exclude \.svn --exclude include/config.php --exclude enterprise trunk || exit 1
cd $CODEHOME; cd .. && zip -r $RPMHOME/SOURCES/IntegriaIMS-$LOCAL_VERSION.zip trunk -x *.git* -x *.gitignore* -x *.svn* -x include/config.php -x *enterprise* || exit 1

# Enterprise 
cd $PANDHOME_ENT && tar zcvf $RPMHOME/SOURCES/IntegriaIMS_enterprise-$LOCAL_VERSION.tar.gz --exclude \.svn enterprise/* || exit 1
cd $PANDHOME_ENT && zip -r $RPMHOME/SOURCES/IntegriaIMS_enterprise-$LOCAL_VERSION.zip enterprise/* -x *.svn* || exit 1

# Create symlinks needed to build RPM packages
if [ "$1" == "nightly" ]; then
	ln -s $RPMHOME/SOURCES/IntegriaIMS-$LOCAL_VERSION.tar.gz $RPMHOME/SOURCES/IntegriaIMS-$VERSION.tar.gz || exit 1
	ln -s $RPMHOME/SOURCES/IntegriaIMS_enterprise-$LOCAL_VERSION.tar.gz $RPMHOME/SOURCES/IntegriaIMS_enterprise-$VERSION.tar.gz || exit 1

	ln -s $RPMHOME/SOURCES/IntegriaIMS-$LOCAL_VERSION.zip $RPMHOME/SOURCES/IntegriaIMS-$VERSION.zip || exit 1
        ln -s $RPMHOME/SOURCES/IntegriaIMS_enterprise-$LOCAL_VERSION.zip $RPMHOME/SOURCES/IntegriaIMS_enterprise-$VERSION.zip || exit 1

fi

exit 0

