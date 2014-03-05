Summary: Elastix First Boot Setup
Name:    elastix-firstboot
Version: 3.0.0
Release: 2
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
cp change-passwords elastix-admin-passwords $RPM_BUILD_ROOT/usr/bin/
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
/usr/bin/elastix-admin-passwords

%changelog
* Wed Mar 05 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: elastix-firstboot: Fix missing parameter to file_put_contents() 
  resulting from previous update.
  SVN Rev[6496]

* Wed Feb 05 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: elastix-firstboot: Fixed error message for insufficiently strong 
  password. Fixes Elastix bug #1836. Additionally, WSS setup was rewritten to
  not use sed, and menu initialization has been shortened with the use of glob
  instead of handcoded directory listing. Also, WSS setup and menu 
  initialization were moved inside the first-boot check to prevent running them
  on every system startup.
  SVN Rev[6460]

* Thu Jul 18 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: elastix-firstboot: fix script to update password in mysql database
  instead of sqlite.
  SVN Rev[5345]

* Tue Apr 09 2013 Luis Abarca <labarca@palosanto.com> 3.0.0-2
- CHANGED: firstboot - Build/elastix-firstboot.spec: Update specfile with latest
  SVN history. Changed release in specfile.

* Tue Apr 09 2013 Rocio Mera <rmera@palosanto.com>
- CHANGED: Apps - elastix-firstboot: Was edited file elastix-admin-passwords in
  order to set the password to database elxpbx in file
  /etc/asterisk/res_odbc.conf and /etc/odbc.ini. This file was added to add
  support asterisk to use odbc to connect with mysql databases
  SVN Rev[4804]

* Thu Jan 31 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: elastix-firstboot: make update of password in manager.conf more 
  robust in the case it falls out of sync with /etc/elastix.conf.
  SVN Rev[4658]

* Wed Jan 09 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: A minor correction in the commit 4564 where the Cancel option
  appears only when command change-password is used.
  SVN Rev[4565]

* Tue Jan 08 2013 Luis Abarca <labarca@palosanto.com>
- CHANGED: The Cancel option that used to appear in the dialog_password was
  removed, because if someone pressed, it no allows to continue configuring
  passwords.
  SVN Rev[4564]

* Thu Dec 20 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: elastix-firstboot: Revert SVN commit 4161 and fix the proper way.
  Original bug was caused by forgotten blanking of password after regexp failed.
  SVN Rev[4526]

* Thu Nov 29 2012 Luis Abarca <labarca@palosanto.com>
- FIXED: firstboot - elastix-admin-passwords: Now the password in the file
  cdr_mysql.conf will be set correctly.
  SVN Rev[4467]

* Thu Nov 29 2012 Luis Abarca <labarca@palosanto.com>
- CHANGED: Now the RHGB feature it will be shown after the first boot in the
  machine
  SVN Rev[4463]

* Wed Sep 26 2012 Luis Abarca <labarca@palosanto.com>
- CHANGED: The message for updates of passwords now are diferent.
  SVN Rev[4309]

* Tue Sep 25 2012 Luis Abarca <labarca@palosanto.com>
- CHANGED: The message for the elastix web admin is now elastix web superadmin.
  SVN Rev[4278]

* Tue Sep 25 2012 Luis Abarca <labarca@palosanto.com>
- FIXED: A symbol was wrong now its corrected.
  SVN Rev[4275]

* Mon Sep 24 2012 Luis Abarca <labarca@palosanto.com>
- FIXED: Not needed anymore restart amportal.
  SVN Rev[4262]

* Mon Sep 24 2012 Luis Abarca <labarca@palosanto.com>
- FIXED: Now the elastix admin password is updated.
  SVN Rev[4261]

* Thu Sep 20 2012 Luis Abarca <labarca@palosanto.com> 3.0.0-1
- CHANGED: firstboot - Build/elastix-firstboot.spec: Update specfile with latest
  SVN history. Changed release in specfile.
  SVN Rev[4223]

* Thu Aug 30 2012 German Macas <gmacas@palosanto.com>
- FIXED: elastix-admin-passwords: Fixed bug when enter a not allowed character
  in password
  SVN Rev[4161]

* Fri Jun 15 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Optimization: do not attempt to start mysql unconditionally. It 
  should be started only when a database configuration is required. Also, since
  elastix-admin-passwords starts mysql if required, there is no need to start
  it on the init script too.
- FIXED: elastix-firstboot: do not attempt to start mysql on system shutdown.
  SVN Rev[4006]

* Mon May 07 2012 Rocio Mera <rmera@palosanto.com> 2.3.0-7
- CHANGED: Changed in specfile, updated release to 7
  SVN Rev[3934]

* Fri May 04 2012 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Rewrite the password assignment as a PHP script. This allows the use
  of native preg_match() and proper string escaping instead of potentially
  flawed shell escaping. Both initial password assignment and subsequent 
  password changing are now handled by the PHP script. May fix Elastix 
  bug #1260.
  SVN Rev[3928]

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

