Summary: Elastix is a Web based software to administrate a PBX based in open source programs
Name: elastix
Vendor: Palosanto Solutions S.A.
Version: 2.0.4
Release: 18 
License: GPL
Group: Applications/System
Source: elastix_%{version}-%{release}.tgz
#Source: elastix_%{version}-53.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: /sbin/chkconfig, /etc/sudoers, sudo
Prereq: php, php-sqlite3, php-gd, php-pear, php-pear-DB, php-xml, php-mysql, php-pdo, php-imap, php-soap
Prereq: httpd, mysql-server, ntp, nmap, mod_ssl
Prereq: perl
Prereq: elastix-firstboot >= 2.0.0-4
Obsoletes: elastix-additionals
Provides: elastix-additionals

%description
Elastix is a Web based software to administrate a PBX based in open source programs

%prep
%setup -n elastix

%install
## ** Step 1: Creation path for the installation ** ##
rm -rf   $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT

# ** /var path ** #
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/mohmp3
mkdir -p $RPM_BUILD_ROOT/var/www/db
mkdir -p $RPM_BUILD_ROOT/var/www/html
mkdir -p $RPM_BUILD_ROOT/var/www/backup

# ** /usr path ** #
mkdir -p $RPM_BUILD_ROOT/usr/local/bin
mkdir -p $RPM_BUILD_ROOT/usr/local/elastix
mkdir -p $RPM_BUILD_ROOT/usr/local/sbin
mkdir -p $RPM_BUILD_ROOT/usr/sbin
mkdir -p $RPM_BUILD_ROOT/usr/bin
mkdir -p $RPM_BUILD_ROOT/usr/share/elastix
mkdir -p $RPM_BUILD_ROOT/usr/share/pear/DB

# ** /etc path ** #
mkdir -p $RPM_BUILD_ROOT/etc/cron.d
mkdir -p $RPM_BUILD_ROOT/etc/cron.daily
mkdir -p $RPM_BUILD_ROOT/etc/httpd/conf.d
mkdir -p $RPM_BUILD_ROOT/etc/php.d
mkdir -p $RPM_BUILD_ROOT/etc/yum.repos.d
mkdir -p $RPM_BUILD_ROOT/etc/init.d

# ** /bin path ** #
mkdir -p $RPM_BUILD_ROOT/bin

## ** Step 2: Installation of files and folders ** ##
# ** Installating framework elastix webinterface ** #
mv $RPM_BUILD_DIR/elastix/framework/db/*                                $RPM_BUILD_ROOT/var/www/db/
mv $RPM_BUILD_DIR/elastix/framework/html/*                              $RPM_BUILD_ROOT/var/www/html/

# ** Installating modules elastix webinterface ** #
rm -rf $RPM_BUILD_ROOT/var/www/html/modules/userlist/  # Este modulo no es el modificado para soporte de correo, eso se encuentra en modules-core
#mv $RPM_BUILD_DIR/elastix/modules-core/*                                $RPM_BUILD_ROOT/var/www/html/modules/

# ** Installating additionals elastix webinterface ** #
mv $RPM_BUILD_DIR/elastix/additionals/db/*                              $RPM_BUILD_ROOT/var/www/db/
#mv $RPM_BUILD_DIR/elastix/additionals/html/libs/*                       $RPM_BUILD_ROOT/var/www/html/libs/
#rm -rf $RPM_BUILD_DIR/elastix/additionals/html/libs/
#mv $RPM_BUILD_DIR/elastix/additionals/html/*                            $RPM_BUILD_ROOT/var/www/html/

chmod    777 $RPM_BUILD_ROOT/var/www/db/

# ** Httpd and Php config ** #
mv $RPM_BUILD_DIR/elastix/additionals/etc/httpd/conf.d/elastix.conf        $RPM_BUILD_ROOT/etc/httpd/conf.d/
mv $RPM_BUILD_DIR/elastix/additionals/etc/php.d/elastix.ini                $RPM_BUILD_ROOT/etc/php.d/

# ** crons config ** #
mv $RPM_BUILD_DIR/elastix/additionals/etc/cron.d/elastix.cron              $RPM_BUILD_ROOT/etc/cron.d/
chmod 644 $RPM_BUILD_ROOT/etc/cron.d/*
mv $RPM_BUILD_DIR/elastix/additionals/etc/cron.daily/asterisk_cleanup      $RPM_BUILD_ROOT/etc/cron.daily/
chmod 755 $RPM_BUILD_ROOT/etc/cron.daily/*

# ** Repos config ** #
mv $RPM_BUILD_DIR/elastix/additionals/etc/yum.repos.d/CentOS-Base.repo     $RPM_BUILD_ROOT/usr/share/elastix/
mv $RPM_BUILD_DIR/elastix/additionals/etc/yum.repos.d/elastix.repo         $RPM_BUILD_ROOT/etc/yum.repos.d/

# ** sudoers config ** #
mv $RPM_BUILD_DIR/elastix/additionals/etc/sudoers                          $RPM_BUILD_ROOT/usr/share/elastix/

# ** /usr/local/ files ** #
mv $RPM_BUILD_DIR/elastix/additionals/usr/local/elastix/sampler.php        $RPM_BUILD_ROOT/usr/local/elastix/
mv $RPM_BUILD_DIR/elastix/additionals/usr/local/sbin/motd.sh               $RPM_BUILD_ROOT/usr/local/sbin/
chmod 755 $RPM_BUILD_ROOT/usr/local/sbin/motd.sh

# ** /usr/share/ files ** #
mv $RPM_BUILD_DIR/elastix/additionals/usr/share/elastix/menusAdminElx                  $RPM_BUILD_ROOT/usr/share/elastix/
mv $RPM_BUILD_DIR/elastix/additionals/usr/share/pear/DB/sqlite3.php                    $RPM_BUILD_ROOT/usr/share/pear/DB/

# ** elastix-* file ** #
mv $RPM_BUILD_DIR/elastix/additionals/usr/bin/elastix-menumerge            $RPM_BUILD_ROOT/usr/bin/
mv $RPM_BUILD_DIR/elastix/additionals/usr/bin/elastix-menuremove           $RPM_BUILD_ROOT/usr/bin/
mv $RPM_BUILD_DIR/elastix/additionals/usr/bin/elastix-dbprocess            $RPM_BUILD_ROOT/usr/bin/
mv $RPM_BUILD_DIR/elastix/additionals/usr/bin/versionPaquetes.sh           $RPM_BUILD_ROOT/usr/bin/
chmod 755 $RPM_BUILD_ROOT/usr/bin/versionPaquetes.sh

# ** asterisk.reload file ** #
mv $RPM_BUILD_DIR/elastix/additionals/bin/asterisk.reload                  $RPM_BUILD_ROOT/bin/
chmod 755 $RPM_BUILD_ROOT/bin/asterisk.reload

#copio los agi y sonidos personalizados
chown -R asterisk.asterisk $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin/
#chown -R asterisk.asterisk $RPM_BUILD_ROOT/var/lib/asterisk/sounds/

# ** files asterisk for agi-bin and mohmp3 ** #
mv $RPM_BUILD_DIR/elastix/additionals/asterisk/agi-bin/*                   $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin/
chmod 755 $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin/*
mv $RPM_BUILD_DIR/elastix/additionals/asterisk/mohmp3/*                    $RPM_BUILD_ROOT/var/lib/asterisk/mohmp3/


# Archivos generic-cloexec y close-on-exec.pl
mv $RPM_BUILD_DIR/elastix/additionals/usr/sbin/close-on-exec.pl            $RPM_BUILD_ROOT/usr/sbin/
mv $RPM_BUILD_DIR/elastix/additionals/etc/init.d/generic-cloexec           $RPM_BUILD_ROOT/etc/init.d/

#Logrotate
mkdir -p    $RPM_BUILD_ROOT/etc/logrotate.d/
mv          $RPM_BUILD_DIR/elastix/additionals/etc/logrotate.d/*           $RPM_BUILD_ROOT/etc/logrotate.d/

# File Elastix Access Audit log
mkdir -p    $RPM_BUILD_ROOT/var/log/elastix
touch       $RPM_BUILD_ROOT/var/log/elastix/access.log
 
%pre
#Para conocer la version de elastix antes de actualizar o instalar
#mkdir -p /usr/share/elastix/
#touch /usr/share/elastix/pre_elastix_version.info
#rpm -q --queryformat='%{VERSION}' elastix > /usr/share/elastix/pre_elastix_version.info

# if not exist add the asterisk group
grep -c "^asterisk:" %{_sysconfdir}/group &> /dev/null
if [ $? = 1 ]; then
    echo "   0:adding group asterisk..."
    /usr/sbin/groupadd -r -f asterisk
else
    echo "   0:group asterisk already present"
fi

# Modifico usuario asterisk para que tenga "/bin/bash" como shell
/usr/sbin/usermod -c "Asterisk VoIP PBX" -g asterisk -s /bin/bash -d /var/lib/asterisk asterisk

# TODO: TAREA DE POST-INSTALACIÓN
#useradd -d /var/ftp -M -s /sbin/nologin ftpuser
#(echo asterisk2007; sleep 2; echo asterisk2007) | passwd ftpuser


%post
######### Administration Menus and permission ###############
#. /usr/share/elastix/menusAdminElx `cat /usr/share/elastix/pre_elastix_version.info`
################## End Administration Menus and permission ##########################



# TODO: tarea de post-instalación.
# Habilito inicio automático de servicios necesarios
chkconfig --level 345 ntpd on
chkconfig --level 345 mysqld on
chkconfig --level 345 httpd on
chkconfig --del cups  &> /dev/null
chkconfig --del gpm   &> /dev/null


# ** Change content of sudoers ** #
cat   /usr/share/elastix/sudoers > /etc/sudoers
rm -f /usr/share/elastix/sudoers

# ** Change content of CentOS-Base.repo ** #
cat   /usr/share/elastix/CentOS-Base.repo > /etc/yum.repos.d/CentOS-Base.repo
rm -f /usr/share/elastix/CentOS-Base.repo

# Patch httpd.conf so that User and Group directives in elastix.conf take effect
sed --in-place "s,User\sapache,#User apache,g" /etc/httpd/conf/httpd.conf
sed --in-place "s,Group\sapache,#Group apache,g" /etc/httpd/conf/httpd.conf

# Actualizacion About Version Release
# Verificar si en la base ya existe algo
if [ "`sqlite3 /var/www/db/settings.db "select count(key) from settings where key='elastix_version_release';"`" = "0" ]; then
    `sqlite3 /var/www/db/settings.db "insert into settings (key, value) values('elastix_version_release','%{version}-%{release}');"`
else
    #Actualizar
    `sqlite3 /var/www/db/settings.db "update settings set value='%{version}-%{release}' where key='elastix_version_release';"`
fi

# Para q se actualice smarty (tpl updates)
rm -rf /var/www/html/var/templates_c/*


%preun
# Reverse the patching of httpd.conf
sed --in-place "s,#User\sapache,User apache,g" /etc/httpd/conf/httpd.conf
sed --in-place "s,#Group\sapache,Group apache,g" /etc/httpd/conf/httpd.conf

%clean
rm -rf $RPM_BUILD_ROOT

# basic contains some reasonable sane basic tiles
%files
%defattr(-, asterisk, asterisk)
/var/www/html/*
/var/lib/asterisk/*
/var/www/db
/var/www/backup
/var/log/elastix/*
%config(noreplace) /var/www/db/*
%defattr(- root, root)
/usr/share/elastix/*
/usr/share/pear/DB/sqlite3.php
/usr/local/elastix/sampler.php
/usr/local/sbin/motd.sh
/usr/sbin/close-on-exec.pl
/usr/bin/elastix-menumerge
/usr/bin/elastix-menuremove
/usr/bin/versionPaquetes.sh
/usr/bin/elastix-dbprocess
%config(noreplace) /etc/cron.d/elastix.cron
/etc/cron.daily/asterisk_cleanup
%config(noreplace) /etc/httpd/conf.d/elastix.conf
%config(noreplace) /etc/php.d/elastix.ini
%config(noreplace) /etc/yum.repos.d/elastix.repo
%config(noreplace) /etc/logrotate.d/elastixAccess.logrotate
/etc/init.d/generic-cloexec
/bin/asterisk.reload

%changelog
* Mon Apr 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-18
- CHANGED:  Framework - images: Resize the image  
  x-lite-4-lrg.jpg because this was too big compared with the 
  others. SVN Rev[2501]

* Fri Apr 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-17
- FIXED: additionals - elastix-dbprocess :  Add validation to 
  know if mysql is running or not in a process to install when 
  the event use the update scripts

* Thu Mar 31 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-16
- FIXED: elastix-dbprocess, Validation was improved if file
  /etc/elastix.conf don't exists. SVN Rev[2479] 

* Wed Mar 30 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4.15
- FIXED: module group_permission, actions view, create, update 
  and delete do not exist in the table acl_action. Those actions 
  were commented. SVN Rev[2473]

* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-14
- CHANGED: about us message was changed for a better message.
  SVN Rev[2461] 
- FIXED: fixed the problem of logout=yes in the url (bug #710).
  SVN Rev[2457]
- CHANGED: paloSantoACL, changed the functions getNumResources 
  and getListResources, now the parameter that they receive 
  could be a string or an array. SVN Rev[2452]
- CHANGED: module group_permission, changed the methodology for 
  searching a resource. SVN Rev[2451]
- CHANGED: module grouplist, changed the en.lang, the word 
  "extension user" was changed to "Extension User". SVN Rev[2449]
- CHANGED: in en.lang of Framework, translation changed "administrator" 
  to "Administrator" and "extension" to "Extension". SVN Rev[2447]
- UPDATED:  Update libs of JQuery from jquery 1.4.2 to 1.5.1 and 
  jquery-ui 1.8.2 to 1.8.10. SVN Rev[2443]
- CHANGED: Change permissions of "/etc/sasldb2" after to execute 
  "saslpasswd2 -c cyrus -u example.com" to create user cyrus admin
  SVN Rev[2442]

* Sat Mar 19 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-13
- CHANGED: changed the old logo to the new one. SVN Rev[2421]
- FIXED: wrong favicon, now the favicon is the correct logo of 
  elastix. SVN Rev[2419]
- ADDED: image x-lite-4-lrg used in static softphones. 
  SVN Rev[2404]
- FIXED:  change line: $clave = obtenerClaveCyrusAdmin()  by 
  $clave = obtenerClaveCyrusAdmin("/var/www/html/"), is 
  necessary for antispam. SVN Rev[2397]

* Wed Mar 09 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-12
- FIXED: elastix-dbprocess, undefined variable engine, the 
  correct variable name is data['engine']. SVN Rev[2394]

* Fri Mar 04 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-11
- FIXED: elastix-dbprocess, when the action is "install" the 
  process of creating database is not completed because the script 
  elastix-dbprocess was supposed to receive 2 parameters and not 4, 
  also the script needs to give asterisk group permissions to 
  the database. SVN Rev[2393]

* Tue Mar 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-10
- CHANGED: theme elastixwave, added a focus to the username field
  SVN Rev[2387]
- FIXED: additionals - elastix-firstboot: In elastix-firstboot  
  add new password in elastix.conf for cyrus admin user, this 
  fixes the bug where any user could connect remotely to the 
  console using cyrus admin user and password known. SVN Rev[2383]
- FIXED: framework - misc.lib.class  Add new function to get password 
  of cyrus admin, this fix the bug where anybody could connect to
  cyrus admin by net. SVN Rev[2381]
- FIXED:  Framework - paloSantoForm.class.php: PalosantoForm does 
  not validate forms with html element type of FILE. SVN Rev[2370]
- FIXED: Additionals elastix-menumerge, Fixed bugs where temporal 
  files of smarty cache return an error when in a upgrading there 
  are changes in designer of any module or framework where those
  changes cannot be seen in the web interface. SVN Rev[2359]
- NEW: Elastix framework, paloSantoDB.class.php. Added support
  to connections at postgreSQL. Improvement function 
  getLastInsertId to be more generic and accept an object of 
  connection. SVN Rev[2351]
- CHANGED: module time_config, replaced message to accept the
  change of time configuration. SVN Rev[2349]
- FIXED: framework, fixed the problem of not showing a border 
  line in the window displayed in "about us" using Chrome 8.0, 
  the problem was fixed for all the themes. SVN Rev[2348]
- CHANGED: module time_config, changed the message to "Changing 
  the date and time in the system can cause unexpected or 
  inconsistent values in the process whose calculations depend 
  on it". SVN Rev[2345]
- CHANGED: framework, changed the width of the left side of the 
  help window for all the themes. SVN Rev[2337]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- CHANGED: Send output of dialog to file descriptor 3 with 
  --output-fd option. This prevents error messages from dialog 
  from messing the password output. Should fix Elastix bug #702.
  SVN Rev[2331]
- CHANGED: elastix-dbprocess, validate the case that the engine 
  using is mysql but mysql is shutdown. SVN Rev[2325]
- FIXED: Elastix framework - paloSantoInstaller.class.php, 
  Scape mysql password in creation of databases, it works with
  function escapeshellcmd de php.

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-8
- CHANGED:  in Spec files remove lines about html folder in
  additionals because this folder not exist in the last source
  of files.  
- CHANGED:  elastix-dbprocess, validate the type of engine 
  using (mysql or sqlite3) and created the function to delete.
  SVN Rev[2305]
- CHANGED:  libs Framework - palosantoModuloXML,
  palosantoInstaller,palosantoACL: Support new xml from menu.xml 
  to add group permissions in a process to install. SVN Rev[2301]
- CHANGED:  Additionals - elastix-menumerge:  change file to 
  support new xml to install modules in that xml will have a 
  tag "permissions". SVN Rev[2300]
- DELETED: Elastix framework, Remove Group "Extension" in acl.db,
  Because it will be create in process to install rpms. 
  SVN Rev[2295]
- ADDED:    module endpoint_configuration, added model GXV3175
  SVN Rev[2288]
- FIXED:    framework-palosantoACL: change function 
  isUserAdministratorGroup where it return false if one user do 
  not belong to administrator group. SVN Rev[2278]
- UPDATED:  Elastix Framework, elastix-menuremove. For deleting 
  a menu if that operation is not completed the querys are done
  a rollback. SVN Rev[2277]
- DELETED:  Delete folder additionals/html because this folder 
  is empty and all files was moved modules. SVN Rev[2269]
- CHANGED:  Additionals - trunk/html/:  move xmlservices, static 
  and openfireWrapper.php to modules/trunk/core/extras and 
  modules/trunk/core/im folders. SVN Rev[2267]
- FIXED:    Problem if any account was deleted due to if there is
  an error while to delete an email account and its user on system 
  cannot be removed, the account is deleted but the user system not,
  it occur when a new account is create with the same user that was 
  deleted because this user in system exist.. [#489] SVN Rev[2248]
- ADDED:    module time_config, added the javascript that contains
  the construction of the jquery calendar. SVN Rev[2242]
- CHANGED:  module time_config, added a JQuery calendar in order 
  to set the date. SVN Rev[2241]
- FIXED:    framework paloSantoGraphImage, made global the 
  variable $_MSJ_NOTHING with this change its fixed the problem 
  of showing an error message when the outgoing or ingoing calls 
  are 0 in the module summary_by_extension. SVN Rev[2234]
- CHANGED:  Add new option in INPUT_EXTRA_PARAM to date, this 
  new option is "FIRSTDAY" and can be 1 to 7 where 1 is monday 
  and 7 is sunday. It is used to show the first day in calendar.
  SVN Rev[2229]
- FIXED: Framework index.php, bad definition word, unknown.
  SVN Rev[2227]

* Wed Jan 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- FIXED: Framework email.conf.php, Put localhost to connect with
  user cyrus. Bug http://bugs.elastix.org/view.php?id=382.
  SVN Rev[2223]

* Wed Jan 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- CHANGED: Framework index.php, Messages of audit was improved
  so show a type of message when the access is by web. 
  SVN Rev[2211]

* Wed Dec 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- FIXED: Framework elastix-dbprocess, Fix event to install or
  update a database where dbprocess ask keywork of mysqlroot if
  mysql in on or exportar SQL to elastix-firstboot if it is off.
  SVN Rev[2177]

* Wed Dec 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- FIXED: Framework Elastix, elastix-dbprocess. Fixed problem 
  with error in the process to update of SQLs. SVN Rev[2174]

* Tue Dec 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED: framework, validates that the user can maximum be a 
  string of 20 characters and the use of urlencode for the 
  variable $_POST['input_user']. SVN Rev[2166]
- CHANGED: elastix-firstboot: Bump version for release.
  SVN Rev[2158]
- CHANGED: Elastix logrotate, move because it must be in 
  framework SVN Rev[2155].
- CHANGED: Additionals libs, move libs from additional folder 
  to each specify module. SVN Rev[2152]

* Thu Dec 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGED: Additionals libs, move libs from additional folder 
  to each specify module. SVN Rev[2149]
- FIXED: paloSantoACL, name field does not support names with 
  apostrophe. bug 648 fixed now name field supports the 
  apostrophe. SVN Rev[2147]
- NEW:  Access log file and created logrotate.

* Thu Dec 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- ADDED:   Module Security - Rulers Filtering, add lines in file 
  sudoers for permit to execute commands iptables. SVN Rev[2140]
- UPDATED: Framework paloSantoConfig.class.php, Add functions 
  'recuperar_archivo' and 'respaldar_archivo', used in 
  Security - Rulers Filtering modules. SVN Rev[2139]
- NEW:     Framework elastix, support to log of access to web 
  interface. SVN Rev[2137]
- FIXED:   framework: remove unexplained and bogus check between 
  first element of current row and first element of last row. 
  Fixes Elastix bug #651. SVN Rev[2131]

* Mon Dec 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-60
- CHANGED: Modify elastix.spec move all process "post" and "install":
  - email(cyrus-imapd, postfix, spamfilter) -> elastix-email_admin.spec
  - Hardware_detector and dahdi genconf -> elastix-system.spec
  - vsftp, tftpboot and ftp -> elastix-pbx.spec
  - hylafax, iaxmoden -> elastix-fax.spec
- NEW:     Framework elastix _list.tpl, add message in header of report,
  be able to show progress message. SVN Rev[2122]
- UPDATED: Framework _list.tpl, set color as separator "#AAAAAA" in Tr
  (themes). SVN Rev[2121]
- CHANGED: menus of 2 level have by default a height:23px in style.css 
  of elastixwave (theme) SVN Rev[2112]
- DELETED: Remove all files configuration about email in additionals.
  SVN Rev[2111]
- NEW:     Files about configuration email was moved from additionals 
  to setup forlder of email_admin module, these change is for better 
  organization in elastix.spec. SVN Rev[2111]
- DELETED: Deleted file hardware_dectector in additionals for better 
  organization of elastix.spec. SVN Rev[2110]
- ADDED:   New file hardware_detector in setup folder of system, it was 
  move from additionals. SVN Rev[2110]
- DELETE: Remove files of vsftpd, xinetd.d folders and vsftpd.user_list 
  file from additionals/trunk/etc, for better organization in elastix.spec
  SVN Rev[2109]
- NEW:    New files of vsftpd, xinetd.d folders and vsftpd.user_list file 
  in setup/etc in modules/trunk/pbx/, now the spec of elastix.pbx use and 
  required these services. SVN Rev[2109]
- DELETED: Tftpboot in additionals was delete from trunk. SVN Rev[2106]
- NEW:     Tftpboot in setup of pbx was added from trunk, it is for get
  a better organization. SVN Rev[2106]
- NEW:     New libs phpmailer. These was moved from hylafax as part 
  of framework libs. SVN Rev[2105]
- CHANGED:  Change includes in files function.php (hylafax/bin/include) 
  where the include has a lib phpmailer old, now this lib was in 
  /var/www/html/libs. SVN Rev[2104]
- FIXED:   Framework: remove useless redundant download headers. 
  Fixes issue of XLS export not downloadable under IE8. SVN Rev[2097]
- FIXED:   Framework paloSantoForm.class.php, Parameter ONCHANGE for 
  type select field bad format definition. SVN Rev[2096]
- CHANGED: Module faxnew, Fixed Hard to see Bug  (H2C Bug), on 
  paloSantoFax.class.php _deleteLinesFromInittab  MUST be called using 
  $devId instead $idFax. Code Improvement, class paloSantoFax.class.php, 
  a new function called  restartFax() was created. 
  www.bugs.elastix.org [#607]. SVN Rev[2089]
- CHANGED: additional paloSantoFax.class.php, move it library to 
  modules - fax, it is for better organization in elastix.spec. 
  SVN Rev[2081]
- CHANGED: additional hylafax, Move folder hylafax to modules - fax,
  it is for better organization for spec files. SVN Rev[2073]
- FIXED:   Monitoring: the context variable MEETME_RECORDINGFILE stores 
  the name of the conference recording, if one exists, and should be 
  assigned to cdr.userfield. SVN Rev[2063]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-59
- CHANGED: Remove Prereq: freePBX, RoundCubeMail, iaxmodem, hylafax, 
  asterisk, wanpipe-util, openfire in this SPEC file
- CHANGED: Remove Prereq: elastix from spec file, since this module
  does not actually use any files from the Elastix framework, and 
  also to remove a circular dependency with elastix package.
  SVN Rev[2052]
- NEW: Additionals paloSantoCDR.class.php, New functions getParam 
  y getNumCDR, this will help changes of grids to obtain the amount 
  of registers. SVN Rev[2045]
- FIXED: Framework paloSantoGrid.class.php, fixed problem about 
  download report as SPREAD SHEET nd CSV when the name of file had 
  spaces, this fixed with concat the name of file in the header html.
  SVN Rev[2041]
- ADDED: framework: enhance getTrunkGroupsDAHDI() to attempt to 
  parse dahdi configuration files if Asterisk AMI is not available 
  or does not support "dahdi show channels group N". 
  Required for Elastix 1.6.x. SVN Rev[2038]
- FIXED: Escape ampersand in admin password since the ampersand 
  is a special character for sed. Should fix Elastix bug #598.
  SVN Rev[2013]
- CHANGED: massive search and replace of HTML encodings with the 
  actual characters. SVN Rev[2003]
- REMOVED: framework: remove images/pie_dist.php. Its only user 
  (Destination Distribution) switched to generating the graphic 
  internally in commit 1980. SVN Rev[1981]
- REMOVED: remove images/plot.php as nobody is using it and is 
  an information exposure vuln. Modules sysinfo/dashboard already 
  use different methods for displaying CPU usage. SVN Rev[1978]
- REMOVED: remove images/pie.php as nobody is using it. Modules 
  sysinfo/dashboard already use different methods for displaying 
  disk usage. SVN Rev[1977]
- REMOVED: remove libs/palosantoGraph.class.php and 
  libs/paloSantoGraphImage.php . This mechanism of generating 
  graphics is badly designed and a security bug. All users of 
  these files have already been migrated to 
  libs/paloSantoGraphImage.lib.php. SVN Rev[1976]
- REMOVED: remove images/bar.php as it is broken and nobody is 
  using it. SVN Rev[1975]
- DELETED: Módulo sysinfo, el módulo sysinfo es obsoleto para 
  elastix 2.0. Este fue quitado del framework elastix 2.0.
  SVN Rev[1972]
- CHANGED: framework: obey "menu" from $_POST as well as from 
  $_GET for module selection[1996]
- ADDED: introduce palosantoGraphImage.lib.php, a somewhat 
  compatible replacement for the palosantoGraph/palosantoGraphImage 
  method of generating graphics. SVN Rev[1964][1969]

* Fri Nov 19 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-58
- FIXED: Additionals: Fix regression from commit 1950 that 
  reenabled kernel updates unintentionally via yum. The proper 
  syntax for exclude is to list several packages in one line, 
  not to insert multiple exclude lines. Fixes Elastix bug #595.
  SVN Rev[1991] 

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-57
- FIXED: Date/Time: tweak command to set date to redirct any errors 
  to stdout. Also display lines of output from command with implode,
  as $output is an array. With this, error messages are now shown 
  properly. Part of fix for Elastix bug #584. SVN Rev[1960]
- FIXED: Date/Time: use methods load_language_module and _tr from 
  Elastix framework. Should make module more resistant to missing 
  i18n strings. Part of fix for Elastix bug #584. SVN Rev[1958]

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-56
- FIXED:   paloSantoForm: conditionally define internal functions, 
  so that method fetchForm() may be called multiple times. SVN Rev[1952]
- UPDATED: CentOS-Base.repo was updated. This changes get to update rpm of
  redhat-logos. The solution was the line exclude=redhat-logos in repo file.
  SVN Rev[1950]

* Fri Nov 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-55
- FIXED: paloSantoForm: fix copy-paste-propagated typo: $arrVals-->$arrVars
         paloSantoForm: fix typo in sprintf template $s-->%s. SVN Rev[1949]
- CHANGED: improve functions load_language() and load_language_module()
  so that they can cope with missing language other than 'en',
  and with incomplete main/module translations. It is in misc.lib
  SVN Rev[1942]
- FIXED: make module aware of url-as-array in paloSantoGrid.
     Split up URL construction into an array.
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags from the filter HTML template. They are not 
      required, since the template already includes a proper <form> 
      tag enclosing the grid.
     Run htmlspecialchars through additional template variables assigned 
      in the module.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 
      in order to work properly.
  SVN Rev[1918]
- FIXED: grouplist: return to main group listing if specified group ID 
  for viewing/editing is invalid. Part of fix for Elastix bug #572.
  SVN Rev[1917]
- FIXED: clean up the code for paloForm::fetchForm method. In the
  process, remove a number of opportunities for XSS by escaping
  form values with htmlentities(). Part of fix for Elastix bug #572.
  SVN Rev[1911]
- FIXED: framework: Add support in paloSantoGrid::fetchGridHTML() 
  for an $arrGrid['url'] of type Array with variable name as key and 
  variable value as array value. This allows the method to properly 
  escape URL variables and build an URL string with construirURL(). 
  For backwards compatibility, 'url' is still allowed to be of type 
  String. Part of fix for Elastix bug #572. SVN Rev[1902]
- FIXED:   Messages of warning and errors appear in each module 
  when had and error but the button dismiss do not work.
  SVN Rev[1900]
- FIXED: paloSantoACL: add definitions for string constants that 
  were used without being defined. SVN Rev[1899]

* Mon Nov 08 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-54
- CHANGED: In Spec File change this: [0-9a-zA-Z._-/]* by
  /usr/java/j2sdk1.4.2_07. It is a replace in
  /tftpboot/GS_CFG_GEN/bin/encode.sh

* Fri Nov 05 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-53
- FIXED:   elastix-dbprocess is more generic, the message changed
  to updating database. SVN Rev[1890]

* Fri Oct 29 2010 Edaurdo Cueva <ecueva@palosanto.com> 2.0.0-52
- FIXED:remove the line version=1.3.0-4, where this line was only
  a proof. SVN Rev[1886]

* Fri Oct 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-51
- FIXED: Syntax error in elastix-dbProccess. SVN Rev[1883]

* Fri Oct 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-50
- FIXED:   Fixed bug where variable path was passed in function 
  obtenerClaveConocidaMySQL (line 728 function generarDSNSistema). 
  SVN Rev[1882]

* Fri Oct 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-49
- CHANGED: In elastix-dbProcess before the executeFiles_SQL_update 
  function received as one of arguments a string, now this string
  has been replaced by a file. SVN Rev[1881]

* Fri Oct 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-48
- CHANGED: The change that took place in the setup_dbprocces file, 
  change the function executeFiles_SQL, and now there are 2 functions:
     1) executeFiles_SQL_install
     2) executeFiles_SQL_update
  SVN Rev[1873]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-47
- DELETE:  Remove migrationFilesMonitor.php, Now it is in elastix-pbx 
  and change the spec file for that. SVN Rev[1865]
- CHANGED: FIXED:    Output in maillog.log about SQUAT failed to open 
  index file. It was fixed in cyrus.conf with:
  squatter cmd="squatter -r *" period=15 where create index for
  mailbox for more details see in "man squatter". SVN Rev[1863] 

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-46
- NEW:     New script to update packages. 
  It is in /usr/share/elastix/migrationFilesMonitor.php 
  in additionals to 1.6 and 2.0. SVN Rev[1862]
- FIXED:   Restrict range of special characters accepted as valid in passwords. 
  Should fix Elastix bug #462. SVN Rev[1861]
- FIXED:   Updated the Bulgarian language elastix both version 1.6 as 2.0.
  SVN Rev[1856]
- UPDATED: Update content of softphone. SVN Rev[1851]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-45
- NEW:     New file /in usr/share/elastix/migrationFilesMonitor.php. This
  file is for migrating to monitoring audio files to the database
  asteriskcdrdb. SVN Rev[1862]
- CHANGED: Spec file was add new file "migrationFilesMonitor.php" in 
  additionals 

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-44
- FIXED:   Some functions were added to the file-DBPROCESS elastix, these functions 
  update the databases of packages in elastix. SVN Rev[1847].
- FIXED:   postfix configuration support in migration from 1.6 to 2.0. 
  See in http://bugs.elastix.org/view.php?id=490  SVN Rev[1837-1838-1839-1840]
- FIXED:   Removed audio.php and popup.php in libs to fixed security bug. 
  [#552]   SVN Rev[1829]
- FIXED:   Fixed security bug with audio.php and popup.php where an user can be 
  download files system without authentication by url. [#522] SVN Rev[1829]
- FIXED:   copyright were changed in all themes. SVN Rev[1827]
- CHANGE:  Updated french language. SVN Rev[1825].
- ADDED:   Added new function to paloSantoGrid.class.php for knowing if there is a 
  request with action export to PDF, CSV o SPREADSHEET. SVN Rev[1824]

* Tue Oct 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-43
- FIXED:   use generic-cloexec for network restart, as "service network restart"
  may start daemons of its own in DHCP mode. Rev SVN[1809]
- FIXED:   Change the files.lang, Corresponding to language lang Persia, they sent 
  me some files and exchange with those in the SVN. SVN Rev[1791]
- FIXED:   In function obtenerClaveConocidaMySQL in misc.lib has a parameter $ruta_base
- CHANGED: Added option text mode and html mode in action version of packages 
  in all themes and base.js bugs.elastix.org[#57] SVN Rev[1784]
- CHANGED: Added option text mode and html mode in action version of packages 
  in all themes. bugs.elastix.org[#57] SVN Rev[1783]
- DELETED: function wlog in class paloSantoPDF.class.php. SVB Rev[1782]
- CHANGED: Added new labels text mode and html mode to use in action version. 
  bugs.elastix.org [#57]. SVN Rev[1781]
- FIXED:   Renamed operator to operator in the System menu in groups.
  elastixbugs(#525) Rev SVN[1778]
- CHANGED: New information in script versionPaquetes.sh about version of postfix, 
  openfire, kernel. Bugs.elastix.org[#57]. SVN Rev[1771]

* Wed Sep 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-42
- FIXED:   Fixed some errors in the process to update menus with their order in elastix web. 
  SVN Rev[1767]

* Tue Sep 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-41
- CHANGED:     New function where update all menus including the order of menus to solve 
  the problem elastix 1.6 to 2.0. SVN Rev[1765], SVN Rev[1766]

* Tue Sep 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-40
- CHANGED:  New image loading.gif to show version of packages. Rev[1761]

* Mon Sep 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-39
- FIXED:    paloSantoTrunks: Do not reference $this outside of object context. Fixes Elastix bug #488. SVN Rev[1760]
- FIXED:    clean up stale record from table acl_membership in acl.db. Part of fix for Elastix bug #515. SVN Rev[1758]
- FIXED:    Bug fixed. Comand rpmq use CPU in 100%. bugs.elastix.org[#498] SVN Rev[1752]
- NEW       New libs was added, paloSantoJSON.class.php, JSON.php. This lib can be used to send and get message in JSON Format. SVN Rev[1751]
- CHANGED   In base.js exist a new function to response to the server, this response is in JSON format. SVN Rev[1751]
- FIXED:    Fix the auto resized columns. On this occasion the default is A3 paper. SVN Rev[1746]

* Wed Sep 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-38
- ADDED:   Added new script versionPaquetes.sh in Spec. 

* Tue Sep 14 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-37
- CHANGED: Change some definions in templates _list.tpl to support export reports in PDF Files, spread sheets and CSV. Rev[1745]
- CHANGED: New labels for version name of installed packages. Rev[1744]
- NEW:     New Script obtain the version of packages in elastix 2. Rev[1743]
- ADDED:   New function in misc lib where can obtain the version of installed packeges in elastix. Rev[1742]
- NEW:     Add images used for generate reports. Rev[1739]
- NEW:     PDF support in Framework Elastix for reports in PaloSantoGrid with new library as paloSantoPDF.class.php. Rev[1738]
- FIXED:   fix typo in Elastix password screen. Rev[1727]

* Fri Aug 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-36
- FIXED: Ensure everything in /etc/init.d/ is executable. Rev[1720]
- FIXED: Also set password on files in /etc/asterisk/ that had copies of the FreePBX database password. Rev[1715]

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-35
- ADDED: Script path in spec elastix. /etc/init.d/generic-cloexec and /usr/sbin/close-on-exec.pl

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-34
- ADDED: introduce procedure by which open file descriptors from web server are closed before starting a daemon. This prevents hylafax, iaxmodem, and other daemons from holding HTTP[S] ports open, thus preventing httpd from restarting successfully. See http://bugs.php.net/bug.php?id=38915 for explanation. Rev[1696]
- FIXED: Work around PHP bug (forget to close httpd file descriptors on PHP fork()) for the case of openfire restart. Requires SVN commit #1696. Rev[1705]
- FIXED: Work around PHP bug (forget to close httpd file descriptors on PHP fork()) for the case of hylafax/iaxmodem restart. Requires SVN commit #1696.Rev[1697]
- FIXED: fix typo in network restart. Rev[1706]

* Thu Aug 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-33
- ADDED:     set FreePBX database password along with the other passwords, and update /etc/amportal.conf accordingly. Rev[1686]
- CHANGED:   PaloSantoNavigator was improved in new Function for JQuery libs due to this lib were included when output is a modules but not when was not it. Rev[1692]
- CHANGED:    Some modifications about the style of main menu in theme elastixwave. Rev[1690]

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-32
- FIXED: use "core show channels" instead of "show channels" to sample active channels for channel usage. Required by Asterisk 1.6.2.x. Should fix Elastix bug #429.
- UPDATED: Update content about Zoiper in extra seccion of elastix.
- FIXED: handle install in active system as dependency install by writing default legacy password to /etc/elastix.conf.

* Wed Jul 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-31
- NEW:     It implements the logic for the update. This logic is a begin because need to add more algorithms to determine the current version to version you are upgrading. Rev[1645]
- CHANGED: Add explanation text for prompts and screen numbers. Rev[1639]
-          chown 600 asterisk.asterisk for /etc/elastix.conf. Rev[1639]
-          The look of theme elastixwave was improved. Rev[1641]
- REMOVED: Password setting for sugarcrm no longer necessary. Rev[1622]

* Fri Jul 23 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-30
- FIXED: Removed dependence elastix-sugarcrm.

* Fri Jul 23 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-29
- NEW:  Compatibility for updates where /etc/elastix.conf is not available for get root passwd default.
- FIXED: The error variable in class paloSantoACL.class.php was fixed.
- CHANGED: String connection database as root in lib paloSantoInstaller.class.php.

* Fri Jul 23 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-28
- NEW: Script elastix-dbprocess to administratation database install, update and delete. But the process update and delete didn't implementacion yet.
- NEW: Functions in misc.lib.php obtenerClaveConocidaMySQL and generarDSNSistema for centralized de password database with the file /etc/elastix.conf
- FIXED: Bug lib paloSantoMenu.class.php, the function deleteFather improved.

* Wed Jul 14 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-27
- FIXED: Validation XHTML in main elastix theme support(elastixwave). Improve XHTML compliance.

* Mon Jun 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-26
- FIXED: paloSantoGraphImage.php - Add validation for session and module permissions, and check that class name is a valid PHP identifier.

* Mon Jun 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-25
- UPDATE:  Upgrade jquery libs and like part of framework.
- FIXED:   bug [261] bugs.elastix.org  GrandStream provisioning Error was solved change some lines in spec file to replaces the correct paths.

* Thu Jun 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-24
- Fixed bug in configs/default.conf.php where close tab php "?>" was not there 

* Mon Jun 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-23
- Add new function in palosantoform for design of tables
- Support method BRI over OpenVox B200P

* Wed May 05 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-22
- Upload code lcdelastix to SVN elastix code.
- Fixed mayor bug, CVE-2010-1492, Directory traversal vulnerability.

* Thu Apr 15 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-21
- Fixed minor bug in framework elastix.
- Look elastix was improved.

* Mon Apr 05 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-20
- Fixed bug, include script elastix-menuremove.

* Fri Mar 26 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-19
- Fixed bug number port cyrus-imapd 2000 to 4190 file /etc/cyrus.conf. This is http://bugs.elastix.org/view.php?id=256 and 
  Bug#559923: avelsieve: Default configuration should specify Sieve port 4190.

* Fri Mar 19 2010 Eduardo Cueva D <ecueva@palosanto.com> 2.0.0-18
- Lib paloSantoGraphImage was fixed with the defaul color of pie charts pictures.
- New var language Error, that var had never been defined.
- Solution with index.php when a user into elastix and load the first module without the javascript libraries into the HEAD.
- Change in hardware detector in lib, now this module check if chan_dahdi_additional.conf exits, if not is true it is create.

* Wed Mar 17 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-17
- Support for library and style from modules, denifition HEADER_MODULES in index.php and index.tpl.

* Tue Mar 16 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-16
- Update framewok support native jquery step beta.

* Mon Mar 01 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-15
- Update look elastix version rc.

* Wed Feb 10 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-14
- Fixed bug, JAVA_PATH in endpoint configurator greandstream phone. The solution is a sed after unpackage for replace JAVA_PATH. 

* Tue Jan 19 2010 Bruno Macias V <bmacias@palosanto.com> 2.0.0-13
- Fixed bug, in freepbx 2.6 trunks now have a own table in database asterisk. (PaloSantoTrunk.class.php fixed)
- framework elastix, improved action rawmode for output only code, now use function getParameter.
- Function getParamater now is part of framework elastix, the getParameter function was removed in each module, now this function is in misc.lib.php
- Fixed bug in navigation menus en web interface, losed the url.
- New improve in look elastix.


* Wed Dec 30 2009 Bruno Macias V <bmacias@palosanto.com> 2.0.0-12
- Fixed bug in group permission, name module sysinfo appeard yet, the module sysinfo was deleted. 

* Tue Dec 29 2009 Bruno Macias V <bmacias@palosanto.com> 2.0.0-11
- New look module dasboard, this modulo replace to sysinfo module.
- Fixed minor bug in paloSantoGraphImage.php, images size

* Fri Dec 04 2009 Bruno Macias V <bmacias@palosanto.com> 2.0.0-10
- Fixed the correct url to mirrorlist for RPMs repo .
- Fixed bugs in global definitions variable $arrConf and $arrLang.
- New look elastixwave, this will be default theme.

* Fri Oct 23 2009 Bruno Macias V <bmacias@palosanto.com> 2.0.0-9
- Fixed bug, file elastix.repo url to version 2 in repos and mirrorlist.

* Fri Oct 23 2009 Bruno Macias V <bmacias@palosanto.com> 2.0.0-8
- Fixed bug, file elastix.cron  BAD FILE MODE 755 to 644
- Improved, script hardware_detector for write file chan_dahdi.conf
- Fixed bugs, module remote smtp - bad config.
- Fixed bug module hardware detector, validation ports range improved.

* Mon Oct 19 2009 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-7
- New theme elastix, elastixblue. This theme is alpha

* Sat Oct 17 2009 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-6
- Fixed definitions words and messages in same modules.
- Update framework elastix for support RPM install modules. This feature is to elastix 2.0.
- Validation login when a user administrator, now user will see the main menu sysinfo.
- Fixed minor bugs, definition languages and definition format variables php in module backup restore.
- Fixed bug, user of email not created by webinterface, error imap.
- Fixed bug, user spamfilter for execute the script antispam was created.
- Fixed bugs for support commands of root for user asterisk in PATH variable. This is for script hardware_detection

* Fri Sep 18 2009 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-5
- Script for desintall menus elastix.

* Tue Sep 07 2009 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-4
- Alpha 3 test genrated.

* Mon Sep 07 2009 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-3
- Fixed Bug in email configuration, delete @example.com and validation in email box when not exits. 
- Try new strategy for language file inclusion that tries to ensure that a string will have an English translation as a fallback if no localized string is available.
- Add more debugging information on error path. paloSantoDB.class.php

* Thu Aug 27 2009 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-2
- Script menusAdmunElx comment, this script is obsolete for elastix 2.0.0
- Fixed bug images not found in module summary by extension.

* Wed Aug 26 2009 Bruno Macias V. <bmacias@palosanto.com> 2.0.0-1
- Version 2.0.0-1 
- Script for menus and acls process elastix-menumerge.

* Thu Aug 13 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0-2test
- Require newer version of wanpipe-util for hardware_detector.
- Do not mess up with vsftpd configuration anymore (inherited from
  elastix-additionals, elastix-1.6-7).
- Try to patch vsftpd configuration to restore proper behavior which
  was broken from previous versions.

* Mon Jul 27 2009 Bruno Macias <bmacias@palosanto.com> 2.0-1test
- Prueba de genracion de modulos rpms.

* Tue Jun 23 2009 Mauro Avecilla <mavecilla@palosanto.com> 1.6-5.1
- Personalizacion para Mtech.

* Tue Jun 02 2009 Bruno Macias V <bmacias@palosanto.com> 1.6-5.1
- Fixed bug with hylafax files (configs and bin files), conflict with rpm hylafax resolved.
- In paloSantoFax.class.php now defined FaxRcvdCmd keyword to use of hylafax.
- New files faxrcvd.php and faxrcvd-elastix.php to define script after process fax recived.
- Keyword FaxRcvdCmd on file config.ttyIAX* added, for avoid replace file faxrcvd.

* Mon Jun 01 2009 Bruno Macias V <bmacias@palosanto.com> 1.6-4
- Fixed some traductions not defined in files languages elastix.
- Path static in elastix now are dynamic, elastix can be relocalitation.
- Fixed bug security, files backups of elastix now are in /var/www/backup/
- Id module Email changed to email_admin, freebpx used the same id for voicemails.
- Login in web interface now permit user with piriod.
- Changed message login in console elastix.

* Tue May 26 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 1.6-3
- Elastix package now provides elastix-additionals as well, to ease update to new package.
- Partially revert change to unpack /tftpboot at %%install, since some files are ELF and
  generate unwanted dependencies. These files are so be served to remote clients, not
  used locally.
- Properly mark several configuration files as %%config(noreplace)

* Mon May 18 2009 Bruno Macias <bmacias@palosanto.com> 1.6-2
- Files in /tftpboot, are now installed in instalation time.
- Obsoletes elastix-additionals

* Sat May 16 2009 Bruno Macias <bmacias@palosanto.com> 1.6-1
- New structure of content tar elastix. Now have three folders: additionals, framework and modules.
- Split webinterface in modules and framework foders.
- Configuration additionals be in additionals folders.
- Specs elastix was added news implementation for delete rpm elastix-additionals.


* Tue May  5 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 1.5.2-2.3
- Loosen up dependency on wanpipe-util. Now only its presence is required, 
  not a specific version.

* Sat Apr 25 2009 Bruno Macias <bmacias@palosanto.com> 1.5.2-2.2
- Fixed bug in validation call center parameter (module callcenter_config), the bug was in paloSantoValidator.class.php

* Fri Apr 24 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 1.5.2-2.1
- Do not provide a patched wancfg_zaptel.pl, since wanpipe-util-3.3.16 
  is now patched to provide the changes.

* Tue Mar 31 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 1.5.2-2
- Do not overwrite existing httpd.conf. Instead, move most configuration
  changes to elastix.conf in /etc/httpd/conf.d and comment out User
  and Group directives so that the ones on elastix.conf take effect.
  Also, reverse commenting-out at %%preun so httpd.conf is returned to
  pre-Elastix state.
- Remove unnecessary manipulations of elastix.ini at %%post, instead place
  it at its final place in /etc/php.d/ at %%install .
- Add /etc/dahdi/genconf_parameters as standard managed file instead of
  generating it at %%post .
- Add /var/spool/hylafax/etc/FaxDictionary as standard managed file instead
  of copying it over at %%post .
- Do not change ownership of /var/www/html/* to asterisk, made unnecessary
  by %%defattr in spec.
- Attempt to restore wancfg_zaptel.pl on %%preun .

* Thu Mar 26 2009 Bruno Macias <bmacias@palosanto.com> 1.5.2-1
- Better reorganization repos elastix.
- Enabled dectection sangona cards in hardware detector web interface.

* Wed Mar 18 2009 Bruno Macias <bmacias@palosanto.com> 1.5-9
- Fixed bug when choose spanish language.
- Changed currency argentinian.

* Sat Mar 14 2009 Bruno Macias <bmacias@palosanto.com> 1.5-8
- Fixed bug in adress Book reported by Saleh Madi
- New locate languages modules in themselves.
- Fixed bug integration wiht freePBX in file pbxadmin/index.php in function module_getinfo.

* Mon Mar 09 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 1.5-7
- Relax dependency on wanpipe-util

* Thu Feb 26 2009 Bruno Macias <bmacias@palosanto.com> 1.5-6
  - Delete module echo canceller in web interface Elastix.
  - Standarization, languages in each module.

* Wed Feb 25 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 1.5-5
  - Add session.save_path override to elastix.ini. Required because
    installation changes httpd process owner to asterisk, which does
    not have write permission on /var/lib/php/session

* Mon Feb 16 2009 Alex Villacis Lasso <a_villacis@palosanto.com> 1.5-4
  - Add php-pdo to dependency list
  - Do not overwrite /etc/php.ini - just create new elastix.ini with required variable changes
  - Updated wancfg_zaptel.pl patch for wanpipe-util 3.3.15

* Fri Feb 06 2009 Bruno Macias <bmacias@palosanto.com> 1.5-3
  - Release beta3 1.5-3
  - updated kernel to 2.6.18-92.1.22.el5.
  - RPMS Sangoma created.

* Tue Feb 03 2009 Bruno Macias <bmacias@palosanto.com> 1.5-2
  - Release beta2 1.5-2
 
* Fri Jan 30 2009 Bruno Macias <bmacias@palosanto.com> 1.5-1
  - Release beta 1.5-1
  - Changed module billing_report zaptel by dahdi [#148]
  - Soport DAHDI
  - Asterisk 1.4.23.1 

* Thu Jan 29 2009 Bruno Macias <bmacias@palosanto.com> 1.4-5
  - Fixed bug, names of folder faxes remane this in /var/www/html/faxes/
  - Changed module hardware_detector zaptel by dahdi [#148]
  - Changed module billing_report zaptel by dahdi [#148]
  - Changed module billing_setup zaptel by dahdi [#148]
  - Changed module graphic_report zaptel by dahdi [#148]
  - Changed module dest_distribution zaptel by dahdi [#148]
  - Changed module backup-restore zaptel by dahdi [#148]
  - paloSantoCDR.class.php and paloSantoTrunk.class.php implementation with dahdi [#148]
  - cron /usr/local/elastix/sampler.php implementation with dahdi [#148]

* Wed Jan 14 2009 Bruno Macias <bmacias@palosanto.com> 1.4-4
  - Version rc.
  - Fax Visor show faxes sent. [#138]

* Fri Nov 28 2008 Bruno Macias <bmacias@palosanto.com> 1.4-3
  - Version beta3.
  - Fixed bug extensions_batch, voicemails not work [#137].
  - Fixed bug not show images in freePBX embedded [#135].
  - Fixed bug CDRReport duplicated rows [#136].

* Fri Nov 28 2008 Bruno Macias <bmacias@palosanto.com> 1.4-2
  - Version beta2.
  - Extension Batch fixed bug with changed of meta data asterisk and hints in configs files sip [#129].
  - Endpoint Configuration better functionality and interaction in process network scan [#130].
  - Calendar fixed bug,not dialing an external number [#116].
 
* Tue Nov 10 2008 Bruno Macias <bmacias@palosanto.com> 1.4-1
  - Version beta.
  - Fixed bug with freePBX, definition GLOBAL for _guielement_tabindex, _guielement_formfields. This bug not shows the extension menu.
  - Creation new Text to Wav module, user could create your own records [#18].
  - Update help files embedded, so as the creation in missed modules [#16]. 
  - Hardware mISDN now detection in module hardware_detector [#53].
  - Update file wakeup.php, version 2.0 [#59].
  - New place for help files, now this files integrated with the own module. Creation folder images, help, 
    Files Languages split for each module, the folder lang is now in them [#95].
  - Update languages Bulgaro, French and Persa [#94].
  - Update the content in files help embedded [#104].
  - Fixed bug in paloSantoDB, conecction to othet ip host. [#118] 
  - New module graphic reports, reports by extensions, trunks and queues [#33] [#34].
  - Fixed bug in module dashboard, the resize now is constant [#86].
  - Update file config for Atcom, this is used in module endpoints_configuration [#97].
  - Changed words "Losed" by "Lost" and "segs" by "secs" in module dashboard [#98].
  - Fixed bug in paloSantoDashboars.class.php (Dashboard module), changed {localhost:143} by {localhost:143/notls} [#120].
  - Fixed bug in Extension Batch module, order the name the header and any validation [#102].
  - Fixed bug in calendar module, now update create column call_to [#100].
  - New module (extra) AvantFAX, this module not include by default [#7].
  - New interfaz, user configure your voicemails (PBX->VoiceMail) [#31].
  - Asterisk Log now have the option of search pattern words [#72].
  
* Tue Sep 24 2008 Bruno Macias <bmacias@palosanto.com> 1.3-2
  - Fixed bug in module address book (paging not work fine).

* Fri Sep 12 2008 Bruno Macias <bmacias@palosanto.com> 1.3-1
  - Add Prereq spamassassin for elastix rpm, this is used in module antispam.
  - In module hardware_detection now support sangoma cards, new scrip hardware_detector in /usr/sbin
  - Custom scrip wancfg_zaptel.pl, elastix defined files configs con *.wanpipe

* Fri Sep 05 2008 Bruno Macias <bmacias@palosanto.com> 1.2.1-4
  - Delete comment about faxvisor.
  - New module antispam, this spec add implementation for pre configuration, file spamfilter.sh in path /usr/local/bin
  - Fixed bug, whe update elastix the file /etc/postfix/network_table losed your content

* Mon Sep 01 2008 Bruno Macias <bmacias@palosanto.com> 1.2-4
  - Increase release for rc2.

* Thu Aug 28 2008 Bruno Macias <bmacias@palosanto.com> 1.2-3
  - Integration modules address book and Calendar, now you can generate calls to another phones.
  - Fixed bug in Roundcube, asosiate with the send attachment. Review spec roundcube. 
  - Module Extension Batch add field Outbound CID and fixed bug with the field Direct DID when show null.

* Fri Aug 22 2008 Bruno Macias <bmacias@palosanto.com> 1.2-2
  - Fixed error with xajax and firefox 3.
  - Integration Elastix and Roundcube better, user and password can be changed in settings of Roundcube.

* Mon Aug 11 2008 Bruno Macias <bmacias@palosanto.com> 1.1-8
  - Change rpm freePBX, version 2.4.0.0, bug fixed.

* Tue Jul 08 2008 Bruno Macias <bmacias@palosanto.com> 1.2-1
  - new module asterisk log.
  - In hylafax script (faxrcvd) and funtion, changed conexion database to fax.db. Now is with pdo.
  - Fixed bug in paloSantoTrunk.class.php, the format for customer trunk had a "AMP:" this prefix was replace for empty, this suggestion was report by Jaume Olivé.
  - Module backup/restore add file configs of FOP. 

* Wed Jun 26 2008 Adonis Figueroa <afigueroa@palosanto.com> 1.1-7
  - Module Address Book now permitt upload and download csv files.
  - All themes in elastix were changed for adaptation of modules, example call_center (agent console)
  - Help was updated to show the info of first son if it's a folder.

* Tue Jun 24 2008 Adonis Figueroa <afigueroa@palosanto.com>
  - Module Address Book was updated to report the emails in the internal directory (freepbx).
  - Fixed bug in the function _getNextAvailableDevId. This problem affected to the id of faxes
    when a fax was deleted and a new was created.
  - Module Address Book was updated to hide the column delete to internal directory. 

* Mon Jun 23 2008 Adonis Figueroa <afigueroa@palosanto.com> 
  - Module Address Book was updated to report the list of freepbx how internal directory and only to
    external directory you can add a register.

* Fri Jun 20 2008 Adonis Figueroa <afigueroa@palosanto.com> 
  - Module monitoring was updated to change the date obtained fom file OUT.*
  - Module extensions batch was updated to support the context data when you upload a batch.
  - There was a change to manage the help with the menu from session. The file menu.php was deleted.
    Moreover the users now can see only the help for their modules, not of anothers groups.

* Wed Jun 18 2008 Bruno Macias <bmacias@palosanto.com> 1.1-6
  - Version Stable 1.1

* Tue Jun 10 2008 Bruno Macias <bmacias@palosanto.com> 1.1-5
  - Add new language japanese.
  - Update language brazilian-portuguese, romanian
  - Update module pbxadmin menus.
  - new module recordings.
  - calendar better, now recordings record.
  - update validation version elastix in menuAdministrationElastix.
  - validation in menuAdministrationElastix for new tables in acl database exists (acl_profile_properties and acl_user_profile).
  - Module User Information, better validation in account webmail not defined.

* Fri Jun 06 2008 Bruno Macias <bmacias@palosanto.com>  1.1-4
  - Version 1.1 beta initial.
  - Add Prereq php-imap in this spec necessary for module user information (handler emails).
  - Add funcionality call in module address book.
  - In module calendar add context (see spec freePBX) for active gsm reproduce, module calendar agree this funcionality call extension user for avise calendar event.
  - User Information add reports of calendar event.
  - Module load module change format xml, support 3 level menus, also change implementation paloSantoModuleXML and paloSantoInstaller.

* Fri May 30 2008 Bruno Macias <bmacias@palosanto.com>  1.1-3
  - Add Prereq mod_ssl in this spec necessary for httpd port 443 where listen elastix.
  - New funcionality, webmail integrated in elastix login.

* Tue May 27 2008 Bruno Macias <bmacias@palosanto.com> 1.1-2
  - Standarization the conexion to databases, for all modules in elastix.
  - Initial changed for acopled new frameWork Elastix. 
  - Support in palosantoModuleXML for 3 level in menus.
  - PaloSantoNavigator better implementation in forms menus.
  - Version stable of module address book.
  - New FrameWork Elastix.
  - Updated language French.
  - Call Center language French updated.

* Tue May 20 2008 Bruno Macias <bmacias@palosanto.com> 1.1-1 
  - Version 1.1 alpha initial.
  - Add Prereq php-mysql in this spec.
  - Add Prereq RoundCubeMail in this spec.
  - Expresion regular in module hardware_detection better.
  - New module calendar.
  - New module user information.
  - New module address book.

* Mon Apr 28 2008 Bruno Macias <bmacias@palosanto.com>  1.0-17
  - More implementation in modules billing by developer Hetii. Add function for parse zapata.configs
  - Add fields in module New Virtual fax, area and country code. This fields are required.
  - Fixed Bug in module sysinfo, now accept more formats in partitions name.(Graphical disc image) 
  - Add validation in menuAdministrationElastix, exists columns country_code and area_code in database fax.db.
  - Add required rpm php-xml for elastix.

* Tue Apr 22 2008 Adonis Figueroa <afigueroa@palosanto.com>
  - Module Monitoring was changed to order by date.
  - Module Backup/Restore better interaction and better funcionality in make backup and restore.
  - Module Email - domain, fixed error for delete, modify and insert domain. 

* Mon Apr 21 2008 Bruno Macias <bmacias@palosanto.com> 
  - Fixed bug in billing_rates, in sqlite3 database rate.db add column trunk.
  - Fixed bug in Virtual Fax List, in palosantoFax.class.php fix validacion is_array to isset.
  - Updated language Persian.
  - In file menuAdministrationElastix add validation, alter table rate add column trunk TEXT;

* Wed Apr 19 2008 Bruno Macias <bmacias@palosanto.com> 1.0-16
  - Fixed Bug module monitoring, add new formats files.
  - New themes for web interface: al, slashdot and giox.
  - Modules billing_report and billing_rates better implementation do hetii (developer sourceforge). Before the rates are assosiated only prefix, Now the rates are assosiated with prefix and trunks.

* Wed Apr 09 2008 Bruno Macias <bmacias@palosanto.com> 1.0-15
  - This spec comment lines of create folder faxvisor, this folder is in modules elastix.
  - New language Catalan.
  - Update module Hardware Detection, now zapata.conf is more complete.

* Fri Apr 04 2008 Adonis Figueroa <afigueroa@palosanto.com> 
  - Module Extension Batch changed to support more parameters of VoiceMail.
  - Module GroupPermissions: Do not permit change the permissions of modules administratives to administrator group.

* Wed Apr 01 2008 Adonis Figueroa <afigueroa@palosanto.com> 1.0-14
  - Module Voicemail was changed to make the reports faster, and the admin can view all extensions.
  - Module CDRReport was changed, the users can view only their reports and the admin can view all reports.
  - Module monitoring was changed to make the reports faster.
  - Language Bulgarian updated.

* Wed Mar 26 2008 Bruno Macias <bmacias@palosanto.com> 1.0-13
  - Add language swedish.
  - Add words language for module Reports.
  - Help embedded updated.

* Tue Mar 25 2008 Bruno Macias <bmacias@palosanto.com> 1.0-12
  - Module cdrreport (Reports) add botton delete register.
  - Add Prereq elastix-sugarcrm

* Wed Mar 19 2008 Bruno Macias <bmacias@palosanto.com> 1.0-11
  - New dependency php-pear-DB and new rpm php-pear-DB..
  - Maintenaince of folder otherFiles/pear
  - Fixed warnning in the modules sources (maintenaince).
  - Files vtigerWrapper.php, schema.vtiger, sugarcrmWrapper.php and schema.sugarcrm move in rpms elastix-vtigercrm and elastix-sugarcrm.

* Wed Mar 19 2008 Bruno Macias <bmacias@palosanto.com> 1.0-10
  - New module extensions_batch
  - Fixed warnning and notices in source of modules. Better handler declared variables.
  - File Editor bug of seguridad file, fixed.

* Tue Mar 18 2008 Bruno Macias <bmacias@palosanto.com> 1.0-9
  - Maintenaince: /tmp/ replace for /usr/share/elastix/tmp/ suguest for zafiri
  - Also comment of funcionality old  deleted.
  - The elastix-1.0-9.tar.gz update menus ok.
  - Error and output standar handler in section Administration Menus and permission.

* Mon Mar 03 2008 Bruno Macias <bmacias@palosanto.com> 1.0-8
  - Add language Persian.
  - Finish implementation theme elastixwine

* Fri Feb 22 2008 Bruno Macias <bmacias@palosanto.com> 1.0-7
  - Add this spec Prereq nmap for module endpoints_configuration.
* Thu Feb 21 2008 Bruno Macias <bmacias@palosanto.com> 1.0-6
  - Fixed bug in telnet for atcom 320.
  - Module faxlist now show the ttyIAX number.
* Wed Feb 20 2008 Bruno Macias <bmacias@palosanto.com> 1.0-5
  - Module Conferences finish, add action kick all and invite caller, View number person in the conferences.
  - Module Endpoint Configuration, atcom provionality finish (model AT 320 and AT 530).
    Lynksys 841 ok provisional.
    Add filter for mask in subnet.
* Mon Feb 11 2008 Bruno Macias <bmacias@palosanto.com> 1.0-4
  - Add wrapper for finish instalation to module conference.
  - Module themes_system add funcionality of refresh smarty templates_c
  - Module endpoints_configuration add validation when the devices aren't created.
  - Add schema meetme in /var/www/html.
* Sat Feb 09 2008 Bruno Macias <bmacias@palosanto.com> 1.0-3
  - New Theme for elastix (elastixwine).
  - Add in spec freepbx resources (patch) for correct funcionality of modules call_center and conferences.
  - New module conferences
  - palosantoForm add field checkbox.
* Thu Feb 07 2008 Bruno Macias <bmacias@palosanto.com> 1.0-2
  - Version Alpha the elastix 1.0
  - Better funcionality module Load Module, this make for the module call center.
  - Add new words in the langs.
  - Better the frameWork paloSantoInstaler and paloSantoQueue
  - Note: In spec the freePBX 2.3.1.29 add patch for that function module call center.
  - Add validation in module Endpoint configuration. And better structure the file CFG of the endpoints.
* Wed Jan 30 2008 Bruno Macias <bmacias@palosanto.com> 1.0-1
  - Version alpha the elastix 1.0
  - New module DHCP Server
  - New module Endpoint Configuration
  - Add language hungarian.
  - Update language french.
  - New organization menu Network (Network Parameters and DHCP Server)
  - New functionality File Editor, add file and better search.  
  - Include zapata-channels.conf and zapata_additional.conf in zapata.conf
* Tue Dec 26 2007 Bruno Macias <bmacias@palosanto.com> 0.9.2-4
  - Add funcionality delete voicemails in module voicemails.
  - Add funcionality delete faxes pdf, in module fax visor.
  - Backup Restore fixed bug include palosantoFax.
  - Order desc the pdfs fax in module fax visor.
* Tue Dec 18 2007 Bruno Macias <bmacias@palosanto.com> 0.9.2-3
  - New funcionality in module file editor, now this files order by name and can be search by name file.
* Mon Dec 17 2007 Bruno Macias <bmacias@palosanto.com> 0.9.2-2
  - Fixed format valid for emails and domain in module Email. 
* Fri Dec 14 2007 Bruno Macias <bmacias@palosanto.com> 0.9.2-1
  - Add new language croata.
  - Fixed palosantoValidator, format valid regular expresion that valid emails, domain.
  - Add functionality hardware detection, now will be replaced zapata.conf file for personal file zapata elastix.
  - Fixed bug listen recordings and voicemails.
  - New order menus in the table. Tables affecct menu (menu.db) and acl_resorces (acl.db)
* Mon Dec 4 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.1-4
  - Removing elastix-vtigercrm dependency
* Mon Dec 3 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.1-3
  - The elastix-vtigercrm package was referenced in a bad way
* Mon Dec 3 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.1-2
  - Change to handle better the fact that freepbx disable some modules that need upgrade
* Fri Nov 23 2007 Bruno Macias <bmacias@palosanto.com> 0.9.1-1
  - New module Fax-TemplateEmail for configuration data remitente and mail format. New lenguage for this module.
  - New module User Management-Group List for create new group user. New lenguage for this module
  - Add funcionality for module PBX-monitoring, now be can delete recordings.
  - Changes button name Activate by Accept. In module repositories the updates.
  - Update help Elatix embedded web interface. 
  - Change link menu openfire {IP_SERVER} by {NAME_SERVER}. Add funtion for get name server in palosantoNavigator.
  - Replace old menu ports_details for hardware_detection in menu.db.
* Wed Nov 21 2007 Bruno Macias <bmacias@palosanto.com> 0.9.0-18
  - Delete menus Backup, Restore and Backup List this menus obsolete. (Add functionality of this in seccion Administration)
* Tue Nov 20 2007 Bruno Macias <bmacias@palosanto.com> 0.9.0-17
  - Fixed bug in recording, add functionality for confguration record incoming or outgoing to always.
    Users administrator can see all recordings, the others user only yours.
    Change the groupd get database.
    This change will shows a gray bar when at least one freePBX module is disabled.
* Mon Nov 19 2007 Bruno Macias <bmacias@palosanto.com> 0.9.0-16
  - Update language bulgaro an fixed bug with the "Apply Changes" bar in the PBX menu
* Tue Nov 13 2007 Bruno Macias <bmacias@palosanto.com> 0.9.0-15
  - Update menus elastix and permission, funcionality better (this spec seccion Administration Menus and permission) and update of fax.db. Prereq elastix-vtigercrm
* Tue Nov 13 2007 Bruno Macias <bmacias@palosanto.com> 0.9.0-14
  - Update menus elastix and permission, funcionality better (this spec seccion Administration Menus and permission)
* Thu Nov 8 2007 Adonis Figueroa <afigueroa@palosanto.com> 0.9.0-13
  - Framed Spark in downloads section
* Wed Nov 7 2007 Adonis Figueroa <afigueroa@palosanto.com> 0.9.0-12
  - About Update, Version and Release
* Thu Nov 6 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.0-10
  - Updated Prereq to freepbx 2.3.1-7
* Thu Nov 1 2007 Bruno Macias   <bmacias@palosanto.com> 0.9.0-9
  - Added wrapper for openfire, start service and  /sbin/chkconfig --level 2345 openfire on. elastix-0.9.0-9.tar.gz .
* Wed Oct 31 2007 Bruno Macias   <bmacias@palosanto.com> 0.9.0-8
  - Added wrapper for vtiger create database elastix-0.9.0-8.
* Tue Oct 30 2007 Bruno Macias   <bmacias@palosanto.com> 0.9.0-7
  - Added new menus in the help link package elastix-0.9.0-7.
* Mon Oct 29 2007 Bruno Macias   <bmacias@palosanto.com> 0.9.0-6
  - Changes in freePBX version 2.3 and inteface web freePBX is dual operation correction error.
* Fri Oct 26 2007 Bruno Macias   <bmacias@palosanto.com> 0.9.0-5
  - Changes in freePBX version 2.3 and inteface web freePBX is dual operation, standar format the version rpms.
* Thu Oct 25 2007 Bruno Macias   <bmacias@palosanto.com> 0.9-4
  - Add Link version Elastix, changes in the module Backup in this version elastix-0.9-4.tar.gz
* Mon Oct 22 2007 Bruno Macias   <bmacias@palosanto.com> 0.9-3
  - Add new modules and better funcionality in this version elastix-0.9-3.tar.gz
* Mon Oct 22 2007 Bruno Macias   <bmacias@palosanto.com> 0.9-2
  - Add new modules, changes order in menus in this version elastix-0.9-2.tar.gz
* Wed Oct 19 2007 Bruno Macias   <bmacias@palosanto.com> 0.9-1
  - Add new modules in this version elastix-0.9-1.tar.gz
* Tue Oct  9 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.0-1
  - Hylafax changes removed. These changes should be made in the hylafax RPM.
