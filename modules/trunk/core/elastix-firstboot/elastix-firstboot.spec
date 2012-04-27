Summary: Elastix First Boot Setup
Name:    elastix-firstboot
Version: 2.3.0
Release: 6
License: GPL
Group:   Applications/System
Source0: %{name}-%{version}.tar.bz2
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
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
%setup -n %{name}

%install
rm -rf $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT/etc/init.d/
mkdir -p $RPM_BUILD_ROOT/var/spool/elastix-mysqldbscripts/
mkdir -p $RPM_BUILD_ROOT/usr/share/elastix-firstboot/
mkdir -p $RPM_BUILD_ROOT/usr/bin/
mkdir -p $RPM_BUILD_ROOT/usr/sbin/
cp elastix-firstboot $RPM_BUILD_ROOT/etc/init.d/
cp change-passwords  $RPM_BUILD_ROOT/usr/bin/
mv compat-dbscripts/ $RPM_BUILD_ROOT/usr/share/elastix-firstboot/

%post

chkconfig --del elastix-firstboot
chkconfig --add elastix-firstboot
chkconfig --level 2345 elastix-firstboot on

# The following scripts are placed in the spool directory if the corresponding
# database does not exist. This is only temporary and should be removed when the
# corresponding package does this by itself.
if [ ! -d /var/lib/mysql/asteriskcdrdb ] ; then
	cp /usr/share/elastix-firstboot/compat-dbscripts/01-asteriskcdrdb.sql /usr/share/elastix-firstboot/compat-dbscripts/02-asteriskuser-password.sql /var/spool/elastix-mysqldbscripts/
fi

# If installing, the system might have mysql running (upgrading from a RC). 
# The default password is written to the configuration file. 
if [ $1 -eq 1 ] ; then
	if [ -e /var/lib/mysql/mysql ] ; then
		if [ ! -e /etc/elastix.conf ] ; then
			echo "Installing in active system - legacy password written to /etc/elastix.conf"
			echo "mysqlrootpwd=eLaStIx.2oo7" >> /etc/elastix.conf
		fi
                if [ -f /etc/elastix.conf  ] ; then
                        grep 'cyrususerpwd' /etc/elastix.conf &> /dev/null
                        res=$?
                        if [ $res != 0 ] ; then
                            echo "cyrususerpwd=palosanto" >> /etc/elastix.conf
                        fi
                fi

	fi
fi

# If updating, and there is no /etc/elastix.conf , a default file is generated with
# legacy password so new modules continue to work.
if [ $1 -eq 2 ] ; then
	if [ ! -e /etc/elastix.conf ] ; then
		echo "Updating in active system - legacy password written to /etc/elastix.conf"
		echo "mysqlrootpwd=eLaStIx.2oo7" >> /etc/elastix.conf
	fi
	if [ -f /etc/elastix.conf  ] ; then
		grep 'cyrususerpwd' /etc/elastix.conf &> /dev/null
		res=$?
		if [ $res != 0 ] ; then
		    echo "cyrususerpwd=palosanto" >> /etc/elastix.conf
		fi
	fi
fi

# If updating, ensure elastix-firstboot now runs at shutdown
if [ $1 -eq 2 ] ; then
    touch /var/lock/subsys/elastix-firstboot
fi

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root)
%attr(755, root, root) /etc/init.d/*
%dir %{_localstatedir}/spool/elastix-mysqldbscripts/
/usr/share/elastix-firstboot/compat-dbscripts/01-asteriskcdrdb.sql
/usr/share/elastix-firstboot/compat-dbscripts/02-asteriskuser-password.sql
/usr/bin/change-passwords

%changelog
* Fri Apr 27 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-6
- CHANGED: Addons - Build/elastix-addons.spec: update specfile with latest
  SVN history. Changed release in specfile
- CHANGED: elastix-firstboot: Remove greater-than and less-than characters
  from accepted characters in passwords, since amportal/FOP choke on these.
  SVN Rev[3888]

* Mon Apr 02 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-5
- CHANGED: Additionals - Elastix_Firstboot: Changed in elastix-firstboot and
  elastix-chance-password for change manager asterisk config username and
  password for a2billing
  SVN Rev[3817]-[3815]

* Fri Mar 30 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-4
- CHANGED: elastix-firstboot: comment-out /etc/init.d/functions inclusion. This
  inclusion is useless in CentOS and actually harmful in Fedora, since (in
  Fedora) it sends dialog output to /dev/console instead of controlling console
  which might be a SSH session.
  SVN Rev[3800]
- CHANGED: elastix-firsboot, se revierte los cambios del firewall activado por
  omisión hasta mejorar el diseño y conjunto de reglas activas.
  SVN Rev[3798]
- FIXED: Additional - Elastix-FistBoot/elastix-firstboot: problem with restart
  firewall
  SVN Rev[3794]

* Wed Mar 28 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-3
- FIXED: Additional - Elastix-FistBoot/elastix-firstboot: problem with
  restart firewall
  SVN Rev[3791]
- FIXED: Additional - Elastix-FistBoot/elastix-firstboot: Solved the problem
  that firewall be activated each time restart elastix
  SVN Rev[3783]

* Tue Mar 27 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-2
- CHANGED: Elastix-Firstboot - elastix-firstboot: Changed the message that
  appear when the firewall is activated
  SVN Rev[3783]
- FIXED: elastix-firstboot: the character sequence &-@ unexpectedly created a
  character range, instead of the intended three literal characters. This
  allowed more characters to be accepted as valid passwords than intended. Now
  only the three intended characters are accepted.
  SVN Rev[3770]
- CHANGED: Additionals - elastix-fistboot/elastix-firstboot: Now the Firewall
  will be activated in the installations process
  SVN Rev[3766]

* Fri Mar 09 2012 Alex Villacis Lasso <a_villacis@palosanto.com> 2.3.0-1
- CHANGED: Remove fix for Elastix bug 595. This workaround is rendered obsolete
  with the use of kmod-dahdi. 
  SVN Rev[3726]

* Wed Dec 22 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-9
- CHANGED: In spec file remove actions over vtiger database because the
  package vtiger do that task.
- FIXED: Elastix-firstboot: Changes in elastix-firstboot script to fix 
  the bug with elastix.conf where is created that file by elastix-framework 
  for adding "amiadminpwd" to ami password.
  SVN Rev[3480]
- FIXED: Fixed bug in  "elastix-firstboot" after intallation of an iso 
  where all passwords are never changed after the first reboot. SVN Rev[3478]
- CHANGED: Elastix-Firstboot: Support update change password to 
  vtigercrm 510 and 521. This changes was applied in elastix-firstboot 
  and change-passwords scripts. SVN Rev[3476]

* Mon Dec 05 2011 Alex Villacis Lasso <a_villacis@palosanto.com> 2.2.0-8
- CHANGED: Elastix-firstboot: Reverted some changes of commit 3415 on 3414
- FIXED: fix elastix-firstboot so that it will actually run at shutdown
- FIXED: fix-elastix-bug-595 will now run yum to install the required kernel

* Fri Dec 02 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-7
- FIXED: Additional - elastix-firstboot: Changes scripts elastix-firstboot
  and change-passwords to change the user root to admin in a2billing database.
  SVN Rev[3410]

* Tue Nov 29 2011 Alex Villacis Lasso <a_villacis@palosanto.com> 2.2.0-6
- ADDED: new script fix-elastix-bug-595 to fix breakage in kernel update.

* Fri Oct 07 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-5
- CHANGED: elastix-firstboot and change-passwords, changed the
  query to database mya2billing, changed "where userid=1" to
  "where login='admin'", in case the id of user admin is not 1
  SVN Rev[3018]

* Tue Sep 27 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-4
- FIXED: change-passwords, new validation in case the word amiadminpwd
  is not present in file /etc/elastix.conf
  SVN Rev[3000]
- CHANGED: elastix-firstboot and change-passwords, change the AMI password
  SVN Rev[2993]

* Fri Sep 09 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-3
- CHANGED: elastix-firstboot and change-passwords, the 
  ARI_ADMIN_PASSWORD is also changed with the password for freePBX admin
  SVN Rev[2942]

* Thu Sep 01 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-2
- CHANGED: change-passwords, when user press button cancel the
  script does an exit
  SVN Rev[2926]

* Wed Aug 24 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-1
- NEW: new script that change the passwords of mysql, freePBX, 
  user admin, fop, cyrus
  SVN Rev[2894]
- CHANGED: elastix-firstboot, if mysql is not running, elastix-firstboot
  tries to start the service, also the fop password in /etc/amportal.conf
  is set with the password entered for elastix admin
  SVN Rev[2892]

* Wed Aug 10 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- FIXED: in script elastix firstboot the step to add word
  "localhost" after "127.0.0.1" from /etc/hosts was improved
  due to possibles problems during of updating. SVN Rev[2887]
- FIXED: elastix-firstboot, an error occurred when the update or
  install operation is done on a elastix 2.0.3 where the password
  of cyrus was not rewrited by firstboot(older versions) in
  /etc/elastix.conf. SVN Rev[2886]

* Tue May 17 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-8
- FIXED: elastix-firstboot, an error occurred when the password
  of root or mysql have spaces. Now the password can have spaces
  also.
  SVN Rev[2641]

* Mon Apr 04 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- FIXED: elastix-firstboot, Defined a temporal solution to add
  localhost first in /etc/hosts, That solution is for cyrus admin
  authenticatication. SVN Rev[2497]

* Thu Mar 31 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- ADD:     elastix-firsboot, Add comment to show the possible 
  bug in the future when the process to execute scripts throw 
  an error of sql this error don't permit to execute the next
  step and ask the admin web passwords. SVN Rev[2476]
- DELETED: Additional - elastix-firstboot, script mya2billing 
  was deleted because is not necessary, elastixdbprocess 
  administration databases. SVN Rev[2475]

* Sat Mar 19 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED: Change permissions of "/etc/sasldb2" after to execute 
  "saslpasswd2 -c cyrus -u example.com" to create user cyrus admin

* Thu Mar 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED: File elastix-firstboot was modified because the logic 
  changed due to a2billing password

* Wed Mar 02 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED: In elastix-firstboot add new password in elastix.conf for 
  cyrus admin user, this fixes the bug where any user could connect remotely 
  to the console using cyrus admin user and password known

* Mon Jan  7 2011 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.4-2
- CHANGED: Send output of dialog to file descriptor 3 with --output-fd option.
  This prevents error messages from dialog from messing the password output.
  Should fix Elastix bug #702.

* Mon Dec 27 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.4-1
- CHANGED: Bump version for release.

* Fri Dec  3 2010 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Remove Prereq: elastix from spec file, since this module does not
  actually use any files from the Elastix framework, and also to remove a 
  circular dependency with elastix package. 

- FIXED: Escape ampersand in admin password since the ampersand is a special
  character for sed. Should fix Elastix bug #598.

* Tue Oct 26 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-14
- FIXED: Restrict range of special characters accepted as valid in passwords.
  Should fix Elastix bug #462.

* Tue Aug 23 2010 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: fix typo in Elastix password screen.

* Fri Aug 20 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-13
- FIXED: Ensure everything in /etc/init.d/ is executable.

* Thu Aug 19 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-12
- FIXED: Also set password on files in /etc/asterisk/ that had copies of
  the FreePBX database password.

* Wed Aug 11 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-11
- ADDED: set FreePBX database password along with the other passwords, and 
  update /etc/amportal.conf accordingly.

* Wed Aug 04 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-10
- FIXED: handle install in active system as dependency install by writing
  default legacy password to /etc/elastix.conf.

* Thu Jul 29 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-9
- CHANGED: Remove references to non-existent RoundCube scripts in postinstall.

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

