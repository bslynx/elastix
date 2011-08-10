%define modname system

Summary: Elastix Module System 
Name:    elastix-%{modname}
Version: 2.2.0
Release: 4
License: GPL
Group:   Applications/System
#Source0: %{modname}_%{version}-2.tgz
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.2.0-1
Prereq: php-soap
Prereq: openfire, wanpipe-util >= 3.4.1-4, dahdi

%description
Elastix Module System

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT/var/www/html/
mkdir -p    $RPM_BUILD_ROOT/var/www/html/libs/
mkdir -p    $RPM_BUILD_ROOT/var/www/backup
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/privileged
mv modules/ $RPM_BUILD_ROOT/var/www/html/

mv setup/paloSantoNetwork.class.php      $RPM_BUILD_ROOT/var/www/html/libs/
mv setup/automatic_backup.php            $RPM_BUILD_ROOT/var/www/backup/
mv setup/usr/share/elastix/privileged/*  $RPM_BUILD_ROOT/usr/share/elastix/privileged

# Additional (module-specific) files that can be handled by RPM
#mkdir -p $RPM_BUILD_ROOT/opt/elastix/
#mv setup/dialer

# ** Dahdi files **#
mkdir -p $RPM_BUILD_ROOT/etc/dahdi
mkdir -p $RPM_BUILD_ROOT/usr/sbin/

# ** hardware_detector file ** #
mv setup/usr/sbin/hardware_detector           $RPM_BUILD_ROOT/usr/sbin/

# ** switch_wanpipe_media file ** #
mv setup/usr/sbin/switch_wanpipe_media        $RPM_BUILD_ROOT/usr/sbin/

# ** The following selects oslec as default echo canceller ** #
echo "echo_can oslec" > $RPM_BUILD_ROOT/etc/dahdi/genconf_parameters
echo "bri_sig_style bri" >> $RPM_BUILD_ROOT/etc/dahdi/genconf_parameters


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
    elastix-dbprocess "update"  "$pathModule/setup/db" "$preversion"
fi

# If openfire is not running probably we're in the distro installation process
# So, i configure openfire init script as stopped by default
/sbin/service openfire status | grep "not running" &>/dev/null
res=$?
# Openfire esta apagado
if [ $res -eq 0 ]; then
    # Desactivo el servicio openfire al inicio
    chkconfig --level 2345 openfire off
fi


# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php
rm -rf /tmp/new_module

%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete System menus"
  elastix-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
/var/www/backup/automatic_backup.php
%defattr(-, root, root)
/usr/sbin/hardware_detector
/usr/sbin/switch_wanpipe_media
/usr/share/elastix/privileged/*
%config(noreplace) /etc/dahdi/genconf_parameters

%changelog
* Fri Aug 05 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-4
- CHANGED: module dashboard, popup in applet telephony hardware
  was appering anywhere. Now it appears near to the applet
  SVN Rev[2876]
- FIXED: Hardware Detector: Some drivers, such as opvxg400, do not
  accept an echocanceller setting on a control channel, not even 
  'none'. Therefore, the echocanceller is instead left to its 
  default value (which is assumed to be 'none')
  SVN Rev[2875]

* Thu Aug 04 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-3
- CHANGED: In Spec file, moved file switch_wanpipe_media to /usr/sbin

* Wed Aug 03 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-2
- FIXED: Network Parameters: use smarty assign for error message
  instead of raw echo.
  SVN Rev[2870]
- FIXED: Network Parameters: relax hostname validation in order 
  to accept localhost.localdomain.
  SVN Rev[2869]
- FIXED: Network Parameters: DNS 2 can be blank, so use --dns2 
  only with nonempty parameter
  SVN Rev[2868]

* Tue Aug 02 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-1
- FIXED: module repositories, the active repos from other type 
  (main or others) were deactivated. Now these active repos remain active
  SVN Rev[2864]
- CHANGED: SQL script, userlist-profile in system, mejor definición 
  del formato.
  SVN Rev[2857]
- CHANGED: In Spec file changed prereq elastix >= 2.2.0-1

* Fri Jul 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-18
- FIXED: System - Backup Restore: Fixed bug where a backup of 
  mailbox of emails cannot be restored. SVN Rev[2850]
- CHANGED: Module dashboard, RSS URL was changed. SVN Rev[2849]
- CHANGED: module repositories, changed the word "type" to "Repo"
  SVN Rev[2830]
- CHANGED: Hardware Detector: implement modification of span 
  parameters (priority, framing, coding, LBO) for ISDN digital 
  spans. SVN Rev[2824]
- CHANGED: Hardware Detector: implement interface for switching 
  ISDN media type (E1 or T1) for Sangoma digital cards. SVN Rev[2824]
- CHANGED: Hardware Detector: fix a few incorrect English translations
  SVN Rev[2824]
- FIXED: Hardware Detector: dahdiconfig - tighten up parameter 
  validation for span parameters. SVN Rev[2824]
- CHANGED: Hardware Detector: remove unexplained initializing of
  tables with data that is always removed on hardware detection.
  SVN Rev[2823]
- CHANGED: Hardware Detector: dahdiconfig must only write crc4 
  for E1 spans. SVN Rev[2819]
- ADDED: module repositories, added some translations to other 
  languages. SVN Rev[2804]
- FIXED: module packages, the packages were not searched in repos 
  extras and epel due to a wrong database name. The problem was 
  fixed and now it is also searched in repos extras and epel.
  SVN Rev[2802]
- CHANGED: module repositories, the repositories were divided into 
  three categories "main", "others" and "all". SVN Rev[2801]
- CHANGED: module backup_restore, changed the place of the buttons 
  save and cancel. SVN Rev[2797]
- CHANGED: module userlist, now a user can have an empty extension.
  SVN Rev[2791]
- ADDED: module userlist, added a new action to edit a user extension
  from other module. SVN Rev[2787]
- CHANGED: System: fix license declaration on all helpers. SVN Rev[2781]
- FIXED: module userlist, fixed security hole when editing a user
  SVN Rev[2780]
- CHANGED: module channelusage, when there is no data to show 
  a jpgraph error was displayed. Now in this case a blank image 
  with the message "Nothing to show yet" and the title of it is 
  displayed. SVN Rev[2778]
- CHANGED: Hardware Detector: make use of dahdiconfig helper for 
  echocanceller and span configuration. As a side effect, fix 
  unnecessary repeating of dahdi restart when modifying echocanceller 
  for a span. SVN Rev[2775]
- CHANGED: Hardware Detector: fix misspelling of KB1 as KBL. SVN Rev[2775]
- ADDED: Hardware Detector: Introduce 'dahdiconfig' privileged 
  helper. This makes use of the elastix-helper framework introduced 
  in commit 2683. SVN Rev[2771]

* Wed Jun 29 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-17
- FIXED: module packages, initializa the variable $filtroGrep in
  function getPackagesInstalados
  SVN Rev[2768]
- FIXED: module packages, when the state is installed, the search
  looks for a release match also. Now the match is only by the
  package name
  SVN Rev[2761]

* Fri Jun 24 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-16
- CHANGED: IN spec file change prereq elastix >= 2.0.4-25
- FIXED: database network.db, the first 20 registers of table 
  dhcp_conf were deleted because these data were inappropriate.
  SVN Rev[2745]
- FIXED: Hardware Detector: fix database fill using wrong field 
  from configuration. SVN Rev[2744]
- CHANGED: Hardware Detector: refactored code into two methods 
  for clarity. SVN Rev[2744]
- FIXED: System - Hardware_detector: missing image images/pci.png 
  in hardware detector module. SVN Rev[2743]
- CHANGED: System - Backup_restore:   Function used to create 
  emails accounts were changes because before are in misc.lib.php  
  and now are in palosantoEmail.class.php. 
  This commit require SVN Rev[2738]. SVN Rev[2741]
- CHANGED: System - Hardware Detector: Missing images pci.png 
  this doesn't appear in the title of modules. SVN Rev[2724]
- NEW:     Security - Change Password: New module Change Password 
  allow to change the passwords of freePBX and enable or disables 
  the access per browser to the freePBX non-Embedded. SVN Rev[2724]

* Mon Jun 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-15
- CHANGED: System, changed order of update child's, now the first 
  is repositories and the second is packages. SVN Rev[2723]
- CHANGED: System/Hardware Detector: (trivial) mark some methods 
  as private. SVN Rev[2716]
- CHANGED: System/Repositories: re-implement module library using 
  repoconfig helper.  Requires commit 2683,2706. SVN Rev[2715]
- CHANGED: System/Repositories: replace uses of deprecated ereg 
  with preg_match. SVN Rev[2715]
- CHANGED: System/Repositories: (trivial) mark two methods as 
  private. SVN Rev[2715]
- ADDED: System/Repositories: Introduce 'repoconfig' privileged 
  helper. This makes use of the elastix-helper framework introduced
  in commit 2683. SVN Rev[2714]
- CHANGED: System/DHCP by MAC: rewrite module library around the 
  concept of network.db database directing changes to 
  /etc/dhcpd.conf, not the other way around as before. Also make 
  use of "dhcpconfig --refresh" helper. Requires commit 2683,2706.
  SVN Rev[2713]
- FIXED: System/DHCP by MAC: fix long-standing bug in which 
  /etc/dhcpd.conf with no host specifications resulted in inability 
  to create first host specification, as side effect of reworking 
  module library. SVN Rev[2713]
- CHANGED: System/DHCP by MAC: make use of _tr() for translations 
  instead of $arrLang. SVN Rev[2713]
- ADDED: System/DHCP by MAC: provide Spanish translation. SVN Rev[2713]
- CHANGED: System/DHCP by MAC: add --refresh option for dhcpconfig
  SVN Rev[2712]
- ADDED: System/DHCP Server: Rework dhcpconfig in preparation 
  for alternate configuration of dhcpd.conf file. SVN Rev[2711]
- CHANGED: System/DHCP by MAC: remove dead code and mark two 
  methods as private in class paloSantoDHCP_Configuration.
  SVN Rev[2710]
- CHANGED: System/Date Time: re-implement modification of date 
  parameters using dateconfig helper. Requires commit 2683,2706.
  SVN Rev[2707]
- ADDED: System/Date Time: Introduce 'dateconfig' privileged 
  helper. This makes use of the elastix-helper framework 
  introduced in commit 2683. SVN Rev[2705]
- CHANGED: System/Date Time: (trivial) Sync module to be 
  identical in 1.6 and 2.0. SVN Rev[2704]
- CHANGED: System/DHCP Server: re-implement modification of 
  network parameters using dhcpconfig helper. Requires commit 
  2683. Also, remove dead code resulting from the switch. 
  SVN Rev[2702]
- ADDED: System/DHCP Server: Introduce 'dhcpconfig' privileged 
  helper. This makes use of the elastix-helper framework 
  introduced in commit 2683. SVN Rev[2701]
- CHANGED: System/DHCP Server: No sudo required for service dhcpd 
  status. SVN Rev[2700]
- CHANGED: System/DHCP Server: Mark two template functions as 
  private. SVN Rev[2700]
- CHANGED: System/DHCP Server: Prevent access to undefined indexes 
  on DHCP IPs not in network for current interfaces. SVN Rev[2700]
- DELETED: System/DHCP Server: Remove sysmanip.lib.php. This 
  library has never worked due to requiring /sg/bin/sudo 
  which does not exist in Elastix. Additionally the result of 
  the only method called is never used as is, and the only 
  values used are assigned from different sources. SVN Rev[2698]
- CHANGED: System/DHCP Server: (trivial) Sync index.php to be 
  identical in 1.6 and 2.0. SVN Rev[2697]
- CHANGED: System/Network Parameters: re-implement modification 
  of network parameters using netconfig helper. Requires commit
  2683. Also, remove dead code resulting from the switch. 
  SVN Rev[2694]
- FIXED: System/Network Parameters: netconfig - several settings 
  should always exist even if they were previously absent. 
  SVN Rev[2693]
- CHANGED: System/Network Parameters: Invocation of netconfig 
  --hostname should also modify /etc/hosts. SVN Rev[2692]
- ADDED: System/Network Parameters: Introduce 'netconfig' 
  privileged helper. This makes use of the elastix-helper 
  framework introduced in commit 2683. SVN Rev[2689]
- CHANGED: Modules - Trunk: The ereg function was replaced by 
  the preg_match function due to that the ereg function was 
  deprecated since PHP 5.3.0. SVN Rev[2688]

* Tue May 31 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-14
- CHANGED: The split function of these modules was replaced by
  the explode function due to that the split function was
  deprecated since PHP 5.3.0. SVN Rev[2668][2650]
- CHANGED: Module Time Config, se cambio de lugar al módulo time 
  config, paso de framework a modules/core/system. SVN Rev[2666]
- FIXED: module dashboard, the applet communication activity was 
  not displaying the correct number of trunks. Now it displays 
  the number of trunks sip and iax. SVN Rev[2624]

* Tue May 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-13
- FIXED: module packages, fixed pagination. SVN Rev[2615]
- FIXED: System - dashboard: Fixed bug [#846] from bugs.elastix.org, 
  where script update from version 10 to version 11 is wrong and 
  should be from 10 to 13. SVN Rev[2613]
- FIXED: module network_parameters, text required field was 
  displayed in the view. Now its only displayed when it is edit mode
  SVN Rev[2600]

* Wed Apr 27 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-12
- FIXED: module user_list, security hole, a non administrator user
  can access to the information of other users. Now he can only
  access to his own information
  SVN Rev[2547]
- ADDED: updated sql file for database dashboard.db, added a new
  column called username to the table activated_applet_by_user
  SVN Rev[2543]
- CHANGED: module applet_admin, now the activated applets are
  showed depending on user
  SVN Rev[2542]
- CHANGED: module dashboard, now the applets are showed according
  to the applets activated by the specific login user
  SVN Rev[2541]
- CHANGED: In Spec file, changed prereq of elastix to 2.0.4-19

* Tue Apr 12 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-11
- FIXED:     System - hardware_detector:  Fixed bug where to 
  install a mISDN hardware this required to do a yum install
  mISDN but this process do not have misdnportinfo file which 
  is in the mISDNuser package and mISDN not required mISDNuser 
  but mISDNuser required mISDN. SVN Rev[2535]
- CHANGED:   System - Hardware_detector:  New icons and images
  to improve the style of hardware detector module. SVN Rev[2534]
  FIXED:     System - Hardware_detector:  Fixed bug where 
  channels of "Channelbank Xorcom" don't show correctly the 
  states of Spans(channels). For more details:  
  http://bugs.elastix.org/view.php?id=808. SVN Rev[2534]
- FIXED: fix broken wanpipe hardware detection by adding /usr/sbin
  to path in hardware_detector. Otherwise the command 
  /usr/sbin/wanrouter is not found. SVN Rev[2533]

* Tue Apr 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-10
- CHANGED: module userlist, eliminated the field Retype Webmail 
  Password. SVN Rev[2515]
- CHANGED: module hardware_detector, changed the words 
  "Channel detected and not used" to 
  "Channel detected and not in service" and 
  "Channel detected and in use" to 
  "Channel detected and in service". SVN Rev[2510]

* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- CHANGED: module shutdown, unnecessary word "Required field". 
  That word is not longer showed. SVN Rev[2462]
- FIXED:  system - network_parameters:  Fixed Bug 
  "http://bugs.elastix.org/view.php?id=723" where if an user 
  put the parameter GATEWAY in 
  /etc/sysconfig/network-scripts/ifcfg-dev 
  (ifcfg-dev === ifcfg-eth0 or ifcfg-eth1) the GATEWAY by default
  will be read in this files and not from /etc/sysconfig/network 
  because this files have a major priority that network file. 
  SVN Rev[2450]
- FIXED: Fixed Bug "http://bugs.elastix.org/view.php?id=735" 
  where appear a message "Couldn't connect to server" when a FTP
  server was not entered in the form. SVN Rev[2444]
- ADDED: module repositories, added a new button called default 
  which puts the marks checks to the default installation state.
  SVN Rev[2416]
- CHANGED: module userlist, now the field name is not required 
  to create a new user. SVN Rev[2410]

* Tue Mar 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-8
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-10
- CHANGED: update of dashboard.db, changed the code of applets 
  and added a new column called icon. SVN Rev[2379]
- CHANGED: module dashboard, it validates the case that there 
  is not internet access or the elastix web server is down.
  SVN Rev[2372]
- CHANGED: Module Dashboard, Rename style, javascript and tpls
  files, now are 1_style.css, 1_javascript.js and applets.tpl.
  SVN Rev[2369]
- CHANGED: Module Dashboard, add language word "loading", 
  delete files jquery* now action will be in jquery elastix 
  framework. SVN Rev[2368]
- CHANGED: module dashboard, the applets data is loaded using 
  the request function, the index code is now more generic, 
  also a loading image is showed while the applet data is being 
  loaded. SVN Rev[2367]
- CHANGED: module packages, the columns installed and options 
  were merged into one column called status. SVN Rev[2342]
- CHANGED: module shutdown, changed the informative message 
  to shutdown the machine by web interface. SVN Rev[2340]
- CHANGED: module dhcp_server, changed the label "Start range 
  of IPs" to "Starting IP Address", "End range of IPs" to 
  "Ending IP Address" and "IP Address Lease Time" to 
  "Lease Time". SVN Rev[2338]
- ADDED: module system, added a file .sql in the folder update 
  for changing the name from Hard Drivers to Hard Drives to the 
  id = 3. SVN Rev[2335]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- CHANGED:   In Spec add lines to support install or update
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new format 
  to sql script for administer process install, update and delete
  SVN Rev[2332]
- CHANGED:   Put lines transaccion and commit in script 
  db/install/acl/1_userlist-profile.sql. SVN Rev[2320]
- CHANGED: changed the db.info of fax to the format used in 
  elastix-dbprocess. SVN Rev[2316]
- CHANGED: created the folders install, delete and update and 
  added in install the sql scripts also the file db.info has
  the new format that is used in elastix-dbprocess. SVN Rev[2314]

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED:  In Spec file, add functionality to support create or
  update databases using script.sql with elastix-dbprocess
- CHANGED:  In Spec file, add lines to create tables of profiles
  in acl.db
- NEW:      System - setup: New file db.info, this file has 
  information about the database that is used in process to 
  install, update or delete. SVN Rev[2309]
- CHANGED:  menu.xml to support new tag "permissions" where has 
  all permissions of group per module and new attribute "desc" 
  into tag  "group" for add a description of group. 
  SVN Rev[2294][2299]
- CHANGED: module currency,added the Russian Ruble. SVN Rev[2281]
- NEW:     new tabs or folders in trunk/core to create a new 
  rpms im and extras it is for better organization. SVN Rev[2266]
- ADDED:   module currency, added currency Great British Pound
  SVN Rev[2264]

* Mon Jan 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED:  New styles to hardware_detector module, and change 
  function to update echo canceller parameters by ajax. 
  SVN Rev[2202]

* Wed Dec 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- FIXED: Fixed bug [#646] in bugs.elastix.org about FTP Backup 
  de backup_restore where do not work backup in ftp servers on 
  windows, the solution was to show the list of ftp with "-la ." 
  before was ".".

* Tue Dec 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGE: module dhcp_server, file /etc/dhcpd.conf is written 
  with value of nis-domain and domain-name equal to 
  asterisk.local . Now is written with the real host-name of 
  the server. SVN Rev[2161]

* Thu Dec 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- CHANGED: Additionals libs, move libs from additional folder 
  to each specify module. By example paloSantoNetwork.class.php
  SVN Rev[2150]
- CHANGE: currency module, changed the symbol of the salvadorian
  colon. SVN Rev[2144]

* Mon Dec 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-40
- CHANGED: In spec file add lines in post and install about openfire
  and hardware_detector from elastix.spec 
- FIXED: fix typo in null redirection. SVN Rev[2125]
- FIXED: move dahdi_genconf modules after second attempt to 
  shutdown dahdi, and fix comment. SVN Rev[2125]
- CHANGED:  Added option (-s: disable dahdi_genconf). 
  By default dahdi_genconf is true to execute but the option -s 
  with hardware_detector do not execute "dahdi_genconf modules"
  SVN Rev[2123]
- ADDED:    New file hardware_detector in setup folder of system, 
  it was move from additionals. SVN Rev[2110]
- FIXED:  Fix bug http://bugs.elastix.org/view.php?id=610 in 
  module currency where some label of languages are not exist. 
  This soluction is for both elastix. SVN Rev[2098]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-39
- CHANGED: Add new Prereq openfire, wanpipe-util, dahdi
- CHANGED: massive search and replace of HTML encodings with the 
  actual characters. SVN Rev[2002]
- FIXED: Packages: fixed syntax error in hr language. SVN Rev[2001]
- CHANGED: Backup/Restore: stop assigning template variable "url"
  directly, and remove nested <form> tag. SVN Rev[1193]
- CHANGED: Repositories: stop assigning template variable "url" 
  directly, and remove nested <form> tag. SVN Rev[1986]
- CHANGED: Dashboard: switch to use of palosantoGraphImage.lib.php 
  for graph generation. Requires commits 1964,1969 to work properly.
  SVN Rev[1973]
- CHANGED: Shutdown: remove link that cannot be accessed anyway 
  during shutdown or reboot. SVN Rev[1971]
- FIXED:  Fixed bug in dhcp server where appear html code 
  "<b>Time of client refreshment</b>" the tags are put in tpl file
  SVN Rev[1959]

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-38
- Fixed bug in dhcp server where appear html code "<b>.</b>" 
  it occur because now html is scaped in palosantoForm. SVN Rev[1957]

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-37
- CHANGED:  Harware detector module better design and functionality
  [#334] SVN Rev[1954]

* Fri Nov 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-36
- FIXED: make module aware of url-as-array in paloSantoGrid.
     Split up URL construction into an array.
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags from the filter HTML template. They are not 
      required, since the template already includes a proper <form> tag 
      enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 in 
      order to work properly.
  SVN Rev[1913]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-35
- CHANGED: Updated the Bulgarian language elastix. SVN Rev[1857]

* Tue Oct 26 2010 Eduardo Cueva <eceuva@palosanto.com> 2.0.0-34
- CHANGED: Move line elastix-menumerge at beginning the "%post" in spec file. 
  It is for the process to update.

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-33
- FIXED:   Fixed security bug with audio.php and popup.php where an user can be
  download files system without authentication. 
  See in http://bugs.elastix.org/view.php?id=552 [#522] SVN Rev[1830]
- CHANGED:  Updated fr.lang. SVN Rev[1825]
- ADDES:    New lang file, fa.lang (Persian). SVN Rev[1823]
- FIXED:  fixed bug 359 about permissions in userlist module. 
  See in http://bugs.elastix.org/view.php?id=359. SVN Rev[1816]

* Mon Sep 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-32
- FIXED: clean up any stale group membership before assigning membership for new user. Part of fix for Elastix bug #515. SVN Rev[1759]

* Tue Sep 14 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-31
- CHANGED:    Move css file to css folder in hardware_detector in the same way to js files to support framework. Rev[1737]
- FIXED: Do NOT recursively change owner of /tftpboot to root:root after a backup or restore. This causes failures to configure future endpoint provisionings because the web interface fails to write to files now owned by root (permission denied). Instead, just restore permission to /tftpboot, not to its contents. Rev[1730] 

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-30
- CHANGED: Prereq elastix-2.0.0-34

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-29
- FIXED: Fixed bug #420 from bugs.elastix.org. No Trunks was showed. Rev[1708]

* Tue Aug 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-28
- FIXED: Work around PHP bug (forget to close httpd file descriptors on PHP fork()) for the case of dhcpd restart. Requires SVN commit #1696. Rev[1702]
- FIXED: Removed spurious references to /sg/bin/ directory from StickGate. Rev[1702]

* Thu Aug 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-27
- FIXED:  Add function existSchemaDB() lib backup/restore. bugs.elastix.org [437]. Rev[1685]

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-26
- CHANGED: Change help files in Dashboard, DHCP Client List, Hardware Detector, Backup/Restore, Currency, Applet Admin. Rev[1679]
- FIXED:   Fix bug about automatic backup Rev[1679] [#400] bugs.elastix.org.

* Thu Jul 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-25
- CHANGED: Some labes and titles were improved.

* Wed Jul 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-24
- CHANGED: Change file automatic_backup in backup/restore to /var/www/backup/ (fixed security bug). Rev[1646]
-          Textfields and its names have been improved for being easier to understand. Rev[1640].
- FIXED:   Summary By extension, querys has been improved, now the data is from channel y dstchannel. Rev[1640]
-          Fixed security bug(hardware_detector). Rev[1650]
-                Script is not authenticated session
-                Script settings does not validate or Datacard past
-                Script has no protection against SQL injection (Datacard)
-                Script manages hardware parameters in /etc/dahdi/system.conf, and registration cards

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-23
- FIXED: Update RPM not changed the menu link vtigercrm.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-22
- CHANGED: Link vtigercrm was changed. The script vtigercrmWrapper.php was deleted now is obsolte.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-21
- CHANGED: String connection database root, in module backup restore.

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-20
- CHANGED: String connection database asteriskuser, asterisk in module dashboard.

* Wed Jul 21 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-19
- NEW:     Include framework jquery 1.3.2 and jquery-ui 1.7.2 in js folder to mount files css and js (dashboard).
- CHANGED: index.php from dashboard was added a new line to not include the last framework jquery because there are some problems with the last version.
-          Support jquery to the framework, now the framework load js an d css from module backup_restore. 
-          Support format themes/default/css and themes/default/js

* Thu Jul 01 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-18
- CHANGED:  Dashboard : The regular expressions were improved for show the numbers of trunks in applet COMMUNICATION ACTIVITY 
* Mon Jun 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-17
- CHANGED: Dashboard module, change how to show parameter network traffic in COMMUNICATION ACTIVITY. New word in lang.

* Mon Jun 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- CHANGED: Links for RSS in module dashboard to reference new webside elasti.org. 

* Mon Jun  7 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-15
- Change files languages, some var were corrected.

* Fri Apr 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-14
- Change the style of Dashboard module, here the burble can move with the applet content, it is not static.
- Hide the icon of configure or register card in hardware detector....

* Fri Mar 26 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-13
- Fixed bug in module network parameters, now defined localhost and localhost.localdomain to 127.0.0.1.

* Fri Mar 19 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-12
- Change info to web services (webservice.elastix.org) 

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-11
- Support save position applets.

* Mon Mar 01 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- New applet register hardware telephony in module dashboard.
- Register hardware telephony in hardware detector.

* Tue Jan 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-9
- Function getParamenter was removed in each module.
- Backup-retore validation version backup and restore.
- Hardware detector improved support for config signaling and framed.

* Wed Dec 30 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-8
- Fixed regular expresion in module dhcp by mac.
- Hide bar of admin applet in module dashboard.
- New module applet admin for manages, this in preferences.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-7
- Comment string trunk register, the value is not correct.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Improved look and perfomance module dashboard, actions drap and drop.
- Fixed bug hardware detector module, images not found.

* Fri Dec 04 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-5
- Improved module backup/restore, now have automatic backups.
- New module dhcp client list.
- New module dhcp by mac.

* Fri Oct 23 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- Improved module hardware detector.
- New action, FTP Backup in module backup/restore.

* Sat Oct 17 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-3
- Add accion uninstall rpm.
- Validation login when a user administrator, now user will see the main menu sysinfo.

* Mon Sep 07 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-2
- New structure menu.xml, add attributes link and order.

* Wed Aug 26 2009 Bruno Macias <bmacias@palosanto.com> 1.0.0-1
- Initial version.
