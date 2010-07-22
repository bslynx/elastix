Summary: Elastix First Boot Setup
Name:    elastix-firstboot
Version: 0.0.0
Release: 1
License: GPL
Group:   Applications/System
Source0: %{name}-%{version}.tar.bz2
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0, 
Requires: mysql, mysql-server
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
cp /usr/share/elastix-firstboot/compat-dbscripts/07-sugarcrm-password.sql /var/spool/elastix-mysqldbscripts/

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
/usr/share/elastix-firstboot/compat-dbscripts/05-roundcubedb.sql
/usr/share/elastix-firstboot/compat-dbscripts/06-roundcube-password.sql
/usr/share/elastix-firstboot/compat-dbscripts/07-sugarcrm-password.sql


%changelog
* Wed Sep 03 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.0-1
- Initial version. Supports delayed initialization of databases.

