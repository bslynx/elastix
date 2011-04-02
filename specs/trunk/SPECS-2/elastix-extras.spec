%define modname extras

Summary: Elastix Extras 
Name:    elastix-%{modname}
Version: 2.0.4
Release: 2
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0.4-9
Requires: yum

%description
Elastix EXTRA 

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p                   $RPM_BUILD_ROOT/var/www/html/
mv modules/                $RPM_BUILD_ROOT/var/www/html/

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p                   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv -f setup/static/        $RPM_BUILD_ROOT/var/www/html/
mv -f setup/xmlservices/   $RPM_BUILD_ROOT/var/www/html/
mv setup/                  $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml                $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

%post

# Run installer script to fix up ACLs and add module to Elastix menus.
elastix-menumerge /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/menu.xml

# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php

rm -rf /tmp/new_module

%clean
rm -rf $RPM_BUILD_ROOT

%preun
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Extras menus"
  elastix-menuremove "%{modname}"
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*

%changelog
* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGED: the information showed ih those static files had 
  some changes according to the bug #779. SVN Rev[2406]
- CHANGED:  menu.xml: all modules; new attribute "desc" into 
  tag "group" for add a description of group. SVN Rev[2299]
- CHANGED:  menu.xml in all modules was changed to support new
  tag "permissions" where it has all permissions of group per
  module. SVN Rev[2294]

* Fri Jan 28 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- Initial version.

