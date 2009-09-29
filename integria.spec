#
# Integria IMS	
#
%define name        integriaIMS
%define version     2.0.0
%define httpd_name      httpd
# User and Group under which Apache is running
%define httpd_name  apache2
%define httpd_user  wwwrun
%define httpd_group www

# Evaluate PHP version
%define phpver_lt_430 %(out=`rpm -q --queryformat='%{VERSION}' php` 2>&1 >/dev/null || out=0 ; out=`echo $out | tr . : | sed s/://g` ; if [ $out -lt 430 ] ; then out=1 ; else out=0; fi ; echo $out)

Summary:            Integria IMS	
Name:               %{name}
Version:            %{version}
Release:            1 
License:            GPL
Vendor:             Artica ST <info@artica.es>
Source0:            %{name}-%{version}.tar.gz
URL:                http://www.integriaims.com
Group:              Productivity/Web/Utilities
Packager:           Sancho Lerena <slerena@artica.es> 
Prefix:             /srv/www/htdocs
BuildRoot:          %{_tmppath}/%{name}
BuildArchitectures: noarch
AutoReq:            0
Requires:           apache2
Requires:           php >= 4.3.0
Requires:           php5-gd, php5-pear, php5-json, php5-gettext
Requires:           php5-mysql, php5-ldap, php5-mbstring, php5
Requires:           graphviz, xorg-x11-fonts-core
Requires:           php5-pear-db php-pear-xml_rpc
Provides:           %{name}-%{version}

%description
Integria IMS is a project manager and a full tracking system to manage incidents, user workunit reports, multiuser, and with an excepent reporting and notification system. Includes company contracts management and a full inventory system integrated with the tracking system.

%prep
rm -rf $RPM_BUILD_ROOT

%setup -q -n trunk

%build

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT%{prefix}/integria
cp -aRf * $RPM_BUILD_ROOT%{prefix}/integria
if [ -f $RPM_BUILD_ROOT%{prefix}/integria/integria.spec ] ; then
   rm $RPM_BUILD_ROOT%{prefix}/integria/integria.spec
fi

%clean
rm -rf $RPM_BUILD_ROOT

%preun

if [ -f %{prefix}/integria/include/config.php ]
then
	export DBPASS=`cat %{prefix}/integria/include/config.php | grep "dbpass" | cut -f 2 -d "=" | cut -f 2 -d "\""  | head -1`
        export DBUSER=`cat %{prefix}/integria/include/config.php | grep "dbuser" | cut -f 2 -d "=" | cut -f 2 -d "\""  | head -1`
        export DBHOST=`cat %{prefix}/integria/include/config.php | grep "dbhost" | cut -f 2 -d "=" | cut -f 2 -d "\""  | head -1`
        export DBNAME=`cat %{prefix}/integria/include/config.php | grep "dbname" | cut -f 2 -d "=" | cut -f 2 -d "\""  | head -1`

	echo "drop database $DBNAME" | mysql -u $DBUSER -h $DBHOST -p$DBPASS 
fi

rm -Rf %{prefix}/integria/ 

%post

# Has an install already been done, if so we only want to update the files
# push install.php aside so that the console works immediately using existing
# configuration.
#

if [ -f %{prefix}/integria/include/config.php ] ; then
   echo "Seems you have already an existing config.php file. Installer has been renamed to install.done"
   mv %{prefix}/integria/install.php %{prefix}/integria/install.done
else
   echo "Please, now, point your browser to http://your_IP_address/integria/install.php and follow all the steps described on it to complete Integria IMS installation"
fi

%files
%defattr(0644,%{httpd_user},%{httpd_group},0755)
%{prefix}/integria

