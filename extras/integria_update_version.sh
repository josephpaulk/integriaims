#!/bin/bash
# Automatically update Integria IMS version and build where necessary.
source build_vars.sh

# Check command line arguments
if [ $# -lt 2 ] || [ $# -gt 3 ]; then
	echo "Usage: $0 <final|nightly> <version string> [build string]"
	exit 1
fi

# Set some global vars
if [ "$1" == "nightly" ]; then
	NB=1
else
	NB=0
fi
VERSION=$2
if [ $# == 2 ]; then
	BUILD=`date +%g%m%d`
else
	BUILD=$3
fi
TEMP_FILE="/tmp/integria_update_version.tmp"

SPEC_FILES="$CODEHOME/integria.spec \
$CODEHOME/integria.centos.spec \
$PANDHOME_ENT/enterprise/integria_enterprise.spec \
$PANDHOME_ENT/enterprise/integria_enterprise.centos.spec "

DEBIAN_FILES="$CODEHOME/DEBIAN \
$PANDHOME_ENT/enterprise/DEBIAN "

CONSOLE_DB_FILE="$CODEHOME/integria_dbdata.sql"
CONSOLE_FILE="$CODEHOME/include/config_process.php"
CONSOLE_INSTALL_FILE="$CODEHOME/install.php"

# Update version in spec files
function update_spec_version {
        FILE=$1

        if [ $NB == 1 ]; then
                sed -e "s/^\s*%define\s\s*release\s\s*.*/%define release     $BUILD/" "$FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$FILE"
        else
                sed -e "s/^\s*%define\s\s*release\s\s*.*/%define release     1/" "$FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$FILE"
        fi
        sed -e "s/^\s*%define\s\s*version\s\s*.*/%define version     $VERSION/" "$FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$FILE"
}


# Update version in debian dirs
function update_deb_version {
	DEBIAN_DIR=$1
	
	if [ $NB == 1 ]; then
		LOCAL_VERSION="$VERSION-$BUILD"
	else
		LOCAL_VERSION="$VERSION"
	fi

	sed -e "s/^pandora_version\s*=.*/pandora_version=\"$LOCAL_VERSION\"/" "$DEBIAN_DIR/make_deb_package.sh" > "$TEMP_FILE" && mv "$TEMP_FILE" "$DEBIAN_DIR/make_deb_package.sh" && sed -e "s/^Version:\s*.*/Version: $LOCAL_VERSION/" "$DEBIAN_DIR/control" > "$TEMP_FILE" && mv "$TEMP_FILE" "$DEBIAN_DIR/control"
}

# Spec files
for file in $SPEC_FILES; do
	echo "Updating spec file $file..."
	update_spec_version $file
done

# Debian dirs
for dir in $DEBIAN_FILES; do
	echo "Updating DEBIAN dir $dir..."
	update_deb_version $dir
done

# PHP Code
echo "Updating DB version..."
sed -e "s/\s*[(]\s*'db_scheme_version'\s*\,.*/('db_scheme_version'\,'$VERSION'),/" "$CONSOLE_DB_FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$CONSOLE_DB_FILE"
sed -e "s/\s*[(]\s*'db_scheme_build'\s*\,.*/('db_scheme_build'\,'$BUILD'),/" "$CONSOLE_DB_FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$CONSOLE_DB_FILE"

echo "Updating version..."
sed -e "s/^\$config\[\"version\"\]=\".*\"/\$config\[\"version\"\]=\"$VERSION\"/" "$CONSOLE_FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$CONSOLE_FILE"

sed -e "s/^\$config\[\"build\"\]=\".*\"/\$config\[\"build\"\]=\"$BUILD\"/" "$CONSOLE_FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$CONSOLE_FILE"

echo $TEMP_FILE

#echo "Updating Installer version..."
#sed -e "s/\s*\$version\s*=.*/\$version = '$VERSION';/" "$CONSOLE_INSTALL_FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$CONSOLE_INSTALL_FILE"

#sed -e "s/\s*\$build\s*=.*/\$build = '$BUILD';/" "$CONSOLE_INSTALL_FILE" > "$TEMP_FILE" && mv "$TEMP_FILE" "$CONSOLE_INSTALL_FILE"

rm -f "$TEMP_FILE"

