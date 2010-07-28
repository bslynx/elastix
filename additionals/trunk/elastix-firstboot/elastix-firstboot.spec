Summary: Elastix First Boot Setup
Name:    elastix-firstboot
Version: 2.0.0
Release: 8
License: GPL
Group:   Applications/System
Source0: %{name}-%{version}.tar.bz2
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0, 
Requires: mysql, mysql-server, dialog
Requires: sed, grep
Requires: coreutils
Conflicts: elastix-mysqldbdata
Requires(post): chkconfig, /bin/cp

%description
This module contains (or should contain) utilities and configurations that
cannot be prepared at install time from the ISO image, and are therefore
delayed until the first boot of the newly installed system. The main aim of
this script is to replace elastix-mysqldbdata until all RPMS are able to
either prepare their databases on their own, or delegate this task to this
package.

%prep
%setup 

%install
rm -rf $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT/etc/init.d/
mkdir -p $RPM_BUILD_ROOT/var/spool/elastix-mysqldbscripts/
mkdir -p $RPM_BUILD_ROOT/usr/share/elastix-firstboot/
cp elastix-firstboot $RPM_BUILD_ROOT/etc/init.d/
mv compat-dbscripts/ $RPM_BUILD_ROOT/usr/share/elastix-firstboot/

%post

chkconfig --add elastix-firstboot
chkconfig --level 2345 elastix-firstboot on

# The following scripts are placed in the spool directory if the corresponding
# database does not exist. This is only temporary and should be removed when the
# corresponding package does this by itself.
if [ ! -d /var/lib/mysql/asteriskcdrdb ] ; then
	cp /usr/share/elastix-firstboot/compat-dbscripts/01-asteriskcdrdb.sql /usr/share/elastix-firstboot/compat-dbscripts/02-asteriskuser-password.sql /var/spool/elastix-mysqldbscripts/
fi
if [ ! -d /var/lib/mysql/mya2billing ] ; then
	cp /usr/share/elastix-firstboot/compat-dbscripts/03-mya2billing.sql /usr/share/elastix-firstboot/compat-dbscripts/04-a2billinguser-password.sql /var/spool/elastix-mysqldbscripts/
fi
if [ ! -d /var/lib/mysql/roundcubedb ] ; then
	cp /usr/share/elastix-firstboot/compat-dbscripts/05-roundcubedb.sql /usr/share/elastix-firstboot/compat-dbscripts/06-roundcube-password.sql /var/spool/elastix-mysqldbscripts/
fi
if [ ! -d /var/lib/mysql/vtigercrm510 ] ; then
	cp /usr/share/elastix-firstboot/compat-dbscripts/08-schema-vtiger.sql /var/spool/elastix-mysqldbscripts/
fi

# If updating, and there is no /etc/elastix.conf , a default file is generated with
# legacy password so new modules continue to work.
if [ $1 -eq 2 ] ; then
	if [ ! -e /etc/elastix.conf ] ; then
		echo "mysqlrootpwd=eLaStIx.2oo7" >> /etc/elastix.conf
	fi
fi

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
/etc/init.d/*
%dir %{_localstatedir}/spool/elastix-mysqldbscripts/
/usr/share/elastix-firstboot/compat-dbscripts/01-asteriskcdrdb.sql
/usr/share/elastix-firstboot/compat-dbscripts/02-asteriskuser-password.sql
/usr/share/elastix-firstboot/compat-dbscripts/03-mya2billing.sql
/usr/share/elastix-firstboot/compat-dbscripts/04-a2billinguser-password.sql
/usr/share/elastix-firstboot/compat-dbscripts/08-schema-vtiger.sql

%changelog
* Wed Jul 28 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-8
- REMOVED: Removed SQL scripts for RoundCube - newer RoundCube installs them
  on its own.

* Tue Jul 27 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-7
- CHANGED: Add explanation text for prompts and screen numbers.
- CHANGED: chown 600 asterisk.asterisk for /etc/elastix.conf

* Mon Jul 26 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-6
- CHANGED: Reduced number of screens used to prompt for passwords at first boot.

* Fri Jul 23 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-5
- FIXED: generate default /etc/elastix.conf when upgrading from previous
  RPM version that did not have password prompting functionality.

* Thu Jul 22 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-4
- FIXED: salt for crypt for VTiger generated wrongly. Should be 'admin', not entered password.
- REMOVED: Password setting for sugarcrm no longer necessary

* Thu Jul 22 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-3
- FIXED: fix incorrect reference to shell variable

* Thu Jul 22 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-2
- Bump to version 2.0.0 for consistency with other Elastix-2 packages
- Add VTigerCRM schema to compatibility database files
- Add the new task of reading the MySQL root password for the newly installed
  system, and storing it in /etc/mysql.conf , and requesting a password for
  the 'admin' login in Elastix, FreePBX, A2Billing, VTiger. This requires
  dialog to be installed in the system.

* Wed Sep 03 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.0-1
- Initial version. Supports delayed initialization of databases.

