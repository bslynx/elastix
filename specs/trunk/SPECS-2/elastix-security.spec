%define modname security

Summary: Elastix Addons 
Name:    elastix-%{modname}
Version: 2.0.4
Release: 11
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0.4-10
Prereq: freePBX >= 2.7.0-10
Prereq: iptables

%description
Elastix Security

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT/var/www/html/
mv modules/ $RPM_BUILD_ROOT/var/www/html/

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
elastix-menumerge $pathModule/menu.xml
pathSQLiteDB="/var/www/db"
mkdir -p $pathSQLiteDB
preversion=`cat $pathModule/preversion_%{modname}.info`

if [ $1 -eq 1 ]; then #install
  # The installer database
    elastix-dbprocess "install" "$pathModule/setup/db"
elif [ $1 -eq 2 ]; then #update
   # The update database
      elastix-dbprocess "update"  "$pathModule/setup/db" "$preversion"
fi

#chown asterisk.asterisk $pathSQLiteDB/iptables.db

# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r $pathModule/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php
rm -rf /tmp/new_module


%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"

if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Security menus"
  elastix-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*

%changelog
* Tue Mar 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-11
- CHANGED: In spec file changed prereq elastix >= 2.0.4-10
- FIXED: module sec_rules, changed the event from onClick to 
  onChange. SVN Rev[2364]
- CHANGED: module sec_rules, changed the translate or spelling 
  of some labels. SVN Rev[2363]
- CHANGED:  Change the way to organize the script.sql of databases
  SVN Rev[2334]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-10
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9
- CHANGED:  Change the way to organize the script.sql of databases
  SVN Rev[2334]

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- CHANGED:  menu.xml to support new tag "permissions" where has 
  all permissions of group per module and new attribute "desc" 
  into tag  "group" for add a description of group. 
  SVN Rev[2294][2299]
- CHANGED:  changed name and path of file.info to db.info. This
  file contains the database name of all db used for this rpm.
  SVN Rev[2296]

* Wed Feb 02 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-8
- CHANGED: Add new column "state" in table filter of iptable.db.
  SVN Rev[2292]
- ADD:     new field in the table filter and new rule was inserted 
  in the table filter. SVN Rev[2263]
- FIXED:   module sec_rules, new rule that allows yum and ssh.
  SVN Rev[2262]
- FIXED:   module sec_weak_keys, pagination did not work. Now the 
  pagination is working. SVN Rev[2260]

* Thu Jan 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- CHANGED: module sec_rules, added new button desactivate Firewall
  SVN Rev[2239]
- CHANGED: module sec_rules, new images for the help and new icon 
  for the module. SVN Rev[2238]
- CHANGED: module sec_rules, the first time state interaction was
  improved. SVN Rev[2236]
- FIXED: module sec_rules, the problem of the last row was fixed
  SVN Rev[2231]

* Wed Jan 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- CHANGED: module sec_weak_keys, changed the word key for secret 
  and new validations for security. SVN Rev[2221]

* Wed Jan 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- ADDED: schema of database, new column in the table tmp_execute
  SVN Rev[2214]
- CHANGED: module sec_weak_keys, validation if the user is admin 
  or not for privileges. SVN Rev[2213]
- UPDATED: Module Rule Firewall, New Rule was added to accept
  IMAP traffic. SVN Rev[2210]
- FIXED: Module Access Audit, Fixed bug where do not showing 
  the correct amount of pages. SVN Rev[2208]

* Thu Dec 30 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED: module sec_rules, new method for validation of ip.
  SVN Rev[2194]
- FIXED: Module Security, New rule of firewall to accept 
  resolve DNS. SVN Rev[2193]

* Wed Dec 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED: Module Security, Change name of modules and join up 
  the modules "details port" and "rulers". SVN Rev[2178]

* Tue Dec 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGE: module sec_rules, add a line to create the file 
  /etc/sysconfig/iptabl. SVN Rev[2172]

* Wed Dec 22 2010 Bruno Macias V. <bmacias@palosanto.com> 2.0.4-1
- Initial version.
