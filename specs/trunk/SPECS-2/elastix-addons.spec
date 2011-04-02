%define modname addons

Summary: Elastix Addons 
Name:    elastix-%{modname}
Version: 2.0.4
Release: 6
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
#Source0: %{modname}_%{version}-4.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0.4-9
Prereq: chkconfig, php-soap
Requires: yum

%description
Elastix Addons

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT/var/www/html/
mv modules/ $RPM_BUILD_ROOT/var/www/html/

# Additional (module-specific) files that can be handled by RPM
mkdir -p $RPM_BUILD_ROOT/opt/elastix/
mv setup/elastix-moduleconf $RPM_BUILD_ROOT/opt/elastix/elastix-updater
mkdir -p $RPM_BUILD_ROOT/etc/init.d/
mv $RPM_BUILD_ROOT/opt/elastix/elastix-updater/elastix-updaterd $RPM_BUILD_ROOT/etc/init.d/
chmod +x $RPM_BUILD_ROOT/etc/init.d/elastix-updaterd

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv setup/   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

%pre
mkdir -p /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
touch /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"

# Run installer script to fix up ACLs and add module to Elastix menus.
elastix-menumerge /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/menu.xml
pathSQLiteDB="/var/www/db"
mkdir -p $pathSQLiteDB
preversion=`cat $pathModule/preversion_%{modname}.info`

if [ $1 -eq 1 ]; then #install
  # The installer database
    elastix-dbprocess "install" "$pathModule/setup/db"
elif [ $1 -eq 2 ]; then #update
    elastix-dbprocess "update"  "$pathModule/setup/db" "$preversion"
fi


# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php
rm -rf /tmp/new_module

# Install elastix-updaterd as a service
chkconfig --add elastix-updaterd
chkconfig --level 2345 elastix-updaterd on

%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Addons menus"
  elastix-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
/opt/elastix/elastix-updater/*
/etc/init.d/elastix-updaterd

%changelog
* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- FIXED: module addons_availables, the input search didnt work. 
  Now the searching is working fine. SVN Rev[2429]
- CHANGED: module addons, changed the title of the menu to 
  "Available". SVN Rev[2427][2428]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED:   In Spec add lines to support install or update 
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new format 
  to sql script for administer process install, update and delete
  SVN Rev[2332]
- ADD:  addons, agenda, reports. Add folders to contain sql 
  scrips to update, install or delete. SVN Rev[2321]

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED:  menu.xml to support new tag "permissions" where has
  all permissions of group per module and new attribute "desc" 
  into tag  "group" for add a description of group. 
  SVN Rev[2294][2299]

* Wed Jan 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- FIXED:   Fixed message error in process to install where the 
  message error is "SQL error: table addons_cache already exists"
  SVN Rev[2217]

* Mon Dec 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- FIXED: Addons: Remove unnecessary ampersand from foreach 
  iteration over SimpleXMLElement->data. This item changes from a 
  simple array to an iterator in PHP 5.2+ and causes a fatal 
  error. Should fix Elastix bug #659. SVN Rev[2157]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-20
- CHANGED:  massive search and replace of HTML encodings with the
  actual characters. SVN Rev[2002]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-19
- FIXED:    unset session variable elastix_user_permission to clean
  the session variable, it allow to load the new module installed 
  through addons. SVN Rev[1859]

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-18
- CHANGED:   Updated fr.lang SVN Rev[1825]

* Tue Oct 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-17
- ADDED:      New fa.lang file (Persian). SVN Rev[1793]

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- CHANGED:    New message status was put in addons_availables to know what is the status of a download. Rev[1710]
- FIXED:      Message alert "error to install" in addons_availables was fixed. Rev[1710]

* Thu Aug 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-15
- FIXED: Also terminate an inactive yum shell after error conditions. Rev[1684]

* Sat Aug 07 2010 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-14
- FIXED: Bad form closed in file installer.php

* Sat Aug 07 2010 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-13
- FIXED: When exists a installations and the user has closed your browser, the status confirmation wasn't handler.

* Wed Jul 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-12
- CHANGED: Textfields and its names have been improved for being easier to understand. Rev[1640].  

* Wed Jul 21 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-11
- CHANGED: The Style was improved and the data information sent from web services was changed.

* Fri Jul 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- ADDED: Implemented capability to store/retrieve arbitrary string. Intended for use with web interface to store update status with urlencode(serialize($var)).
- FIXED: refresh data base cache dont work. now the bug was fixed. 

* Wed Jul 14 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-9
- ADDED: flesh out support for updating and deleting addon modules
- ADDED: Implemented ability to track down which target addon failed due to missing dependency, and report which missing dependency caused the failure.
- CHANGED: Improved interface module addons availables for support report dependency caused the failure.

* Thu Jul 01 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-8
- CHANGED: Time for refresh session cache about addons available is 2 hours. 

* Mon Jun 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-7
- NEW:     Add table for caching the response from web services. This response about addons availables can be saved and it will be update whe session time for request is out.
- CHANGED: Control time for request to web services data, it allow do not call repeat times for request data.

* Thu Jun 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-6
- Now show the process install and different errors when there is a trouble

* Mon Jun 7 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-5
- Bug about the connection with web services is fixed, before when client do not get any connection with the web services the list of addons appear empty.
- New information about YUM install.
- Improve in send of the data, better organization

* Thu Apr 15 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- Fixed bug dont install module developer and drive the session data install in database local addons.
- Improve the look module addons.

* Thu Mar 25 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-3
- solved problem where process YUM was always listening if there is a process to install. Now The daemon only turn on YUM for listening process and turn off when no process.

* Fri Mar 19 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-2
- Change url web services to webservice.elastix.org

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-1
- Initial version.
