%define modname im

Summary: Elastix IM 
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
Elastix IM

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p                          $RPM_BUILD_ROOT/var/www/html/
mv modules/ $RPM_BUILD_ROOT/var/www/html/

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p                          $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv -f setup/openfireWrapper.php   $RPM_BUILD_ROOT/var/www/html/
mv setup/                         $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml                       $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

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
  echo "Delete IM menus"
  elastix-menuremove "%{modname}"
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*

%changelog
* Mon Jun 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGED: Modules - Trunk: The ereg function was replaced by the 
  preg_match function due to that the ereg function was deprecated 
  since PHP 5.3.0. SVN Rev[2688]

* Fri Jan 28 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- Initial version.

