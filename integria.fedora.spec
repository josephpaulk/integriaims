#
# Integria IMS	
#
%define name        IntegriaIMS
%define version     3.0
%define httpd_name      httpd
# User and Group under which Apache is running
%define httpd_name  httpd
%define httpd_user  apache
%define httpd_group apache

# Evaluate PHP version

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
Prefix:             /var/www/html
BuildRoot:          %{_tmppath}/%{name}
BuildArchitectures: noarch
AutoReq:            0
Requires:           httpd
Requires:           php >= 5.3.0
Requires:           php-gd, php-gettext, php-imap, curl, php-ldap, php5-imap
Requires:           php-mysql, php-ldap, php-mbstring, php, php-zlib
Requires:           graphviz
Provides:           %{name}-%{version}

%description
Integria IMS is a enterprise software for project management, CRM, incident ticketing system inventory, file distribution and time tracking management. Integria is multiuser, with an integrated email reporting and notification system

%prep
rm -rf $RPM_BUILD_ROOT

%setup -q -n trunk

%build

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT%{prefix}/integria
cp -aRf * $RPM_BUILD_ROOT%{prefix}/integria
if [ -f $RPM_BUILD_ROOT%{prefix}/integria/integria.fedora.spec ] ; then
   rm $RPM_BUILD_ROOT%{prefix}/integria/integria.fedora.spec
fi

%clean
rm -rf $RPM_BUILD_ROOT

%preun

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

