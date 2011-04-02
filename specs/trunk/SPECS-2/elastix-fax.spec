%define modname fax

Summary: Elastix Module Fax
Name:    elastix-%{modname}
Version: 2.0.4
Release: 5
License: GPL
Group:   Applications/System
#Source0: %{modname}_%{version}-3.tgz
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0.4-9
Prereq: iaxmodem, hylafax

%description
Elastix Module Fax

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT/var/www/html/
mv modules/ $RPM_BUILD_ROOT/var/www/html/

# Files personalities for hylafax
mkdir -p $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mkdir -p $RPM_BUILD_ROOT/var/spool/hylafax/etc/
mv setup/hylafax/bin/includes                 $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/faxrcvd-elastix.php      $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/faxrcvd.php              $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/notify-elastix.php       $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/bin/notify.php               $RPM_BUILD_ROOT/var/spool/hylafax/bin/
mv setup/hylafax/etc/FaxDictionary            $RPM_BUILD_ROOT/var/spool/hylafax/etc/
mv setup/hylafax/etc/config                   $RPM_BUILD_ROOT/var/spool/hylafax/etc/
mv setup/hylafax/etc/setup.cache              $RPM_BUILD_ROOT/var/spool/hylafax/etc/
rm -rf setup/hylafax

chmod -R 755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/includes
chmod    755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/faxrcvd.php
chmod    755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/faxrcvd-elastix.php
chmod    755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/notify.php
chmod    755 $RPM_BUILD_ROOT/var/spool/hylafax/bin/notify-elastix.php

# move main library of FAX. 
mkdir -p    $RPM_BUILD_ROOT/var/www/html/libs
mv setup/paloSantoFax.class.php               $RPM_BUILD_ROOT/var/www/html/libs/

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv setup/   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

# new for fax
mkdir -p $RPM_BUILD_ROOT/var/log/iaxmodem
mkdir -p $RPM_BUILD_ROOT/var/spool/hylafax/bin
mkdir -p $RPM_BUILD_ROOT/var/spool/hylafax/etc
mkdir -p $RPM_BUILD_ROOT/var/www/faxes
mkdir -p $RPM_BUILD_ROOT/var/www/faxes/recvd
mkdir -p $RPM_BUILD_ROOT/var/www/faxes/sent

# ** Fax Visor additional config ** #
chmod -R 755 $RPM_BUILD_ROOT/var/www/faxes

%pre
mkdir -p /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
touch /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
# Habilito inicio automático de servicios necesarios
chkconfig --level 2345 hylafax on
chkconfig --level 2345 iaxmodem on

# Agrego Enlaces para Hylafax, ESTO AL PARECER LO HACE EL RPM DE HYLAFAX
ln -f -s pdf2fax.gs /var/spool/hylafax/bin/pdf2fax
ln -f -s ps2fax.gs  /var/spool/hylafax/bin/ps2fax

# Elimino archivos de fax que sobran
rm -f /etc/iaxmodem/iaxmodem-cfg.ttyIAX
rm -f /var/spool/hylafax/etc/config.ttyIAX

for i in `ls /var/spool/hylafax/etc/config.* 2>/dev/null`; do
  if [ "$i" != "/var/spool/hylafax/etc/config.sav" ]; then
    if [ "$i" != "/var/spool/hylafax/etc/config.devid" ]; then
      tilde=`echo $i | grep '~'`
      if [ "$?" -eq "1" ]; then
        if [ ! -L "$i" ]; then
          line="FaxRcvdCmd:              bin/faxrcvd.php"
          grep $line "$i" &>/dev/null
          res=$?
          if [ ! $res -eq 0 ]; then # no exists line
            echo "$line" >> $i
          fi
        fi
      fi
    fi
  fi
done

# Cambio de nombre de carpetas de faxes, esto es desde elastix 1.4
if [ -d "/var/www/html/faxes/recvq" ]; then
        mv /var/www/html/faxes/recvq/* /var/www/faxes/recvd
        rm -rf /var/www/html/faxes/recvq
fi

if [ -d "/var/www/html/faxes/sendq" ]; then
        mv /var/www/html/faxes/sendq/* /var/www/faxes/sent
        rm -rf /var/www/html/faxes/sendq
fi

if [ -d "/var/www/html/faxes" ]; then
        mv /var/www/html/faxes/* /var/www/faxes
fi

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

chmod 666 /var/www/db/fax.db

%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Fax menus"
  elastix-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
/var/www/faxes/*
%defattr(- root, root)
/var/spool/hylafax/bin/*
/var/spool/hylafax/etc/setup.cache

%dir
/var/log/iaxmodem
%defattr(-, uucp, uucp)
%config(noreplace) /var/spool/hylafax/etc/FaxDictionary
%config(noreplace) /var/spool/hylafax/etc/config

%changelog
* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED: Fax - setup hylafax:  Change the text of email 
  notification from sending a Fax. SVN Rev[2459]
- CHANGED: module email_template, changed some information in 
  the view according to the bug #744. SVN Rev[2430]
- CHANGED: module faxlist and faxnew, changed the word 
  "destination email" to "associated email". SVN Rev[2412][2413]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED:   In Spec add lines to support install or update
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new format 
  to sql script for administer process install, update and delete
  SVN Rev[2332]
- CHANGED: changed the db.info of fax to the format used in 
  elastix-dbprocess. SVN Rev[2316]

* Thu Dec 30 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- FIXED: Framework/Fax: Commits 2088/2089 accidentally reverted 
  commit 1697, thus reintroducing the 
  unable-to-restart-webserver-after-configuring-fax bug. 
  Add the fix again. SVN Rev[2192]

* Mon Dec 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- CHANGED:  Spec File has the actions post and install from elastix.spec
  about hylafax.
- CHANGED:  Change includes in files function.php 
  (hylafax/bin/include) where the include has a lib phpmailer old, 
  now this lib was in /var/www/html/libs. SVN Rev[2104]
- CHANGED: Module faxnew, Fixed Hard to see Bug  (H2C Bug), on 
  paloSantoFax.class.php _deleteLinesFromInittab  MUST be called 
  using $devId instead $idFax. Code Improvement, 
  class paloSantoFax.class.php, a new function called  restartFax() 
  was created. www.bugs.elastix.org [#607]. SVN Rev[2088]
- NEW:       additional paloSantoFax.class.php, better organization
  in Spec. SVN Rev[2082]
- CHANGED:   Change path to read or find pdf file sended or 
  received in faxviewer, this cahneg was done due to path 
  where fax files could be seen by url "http://IPSERVER/faxes"
  SVN Rev[2077]
- NEW:       New folder hylafax. SVN Rev[2074]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-19
- CHANGED:   Add new Prereq in spec file about iaxmodem, hylafax  
- CHANGED:   massive search and replace of HTML encodings with the 
  actual characters. SVN Rev[2002]

* Mon Nov 15 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-18
- FIXED:     SendFax Module, label "Notification Sucessfull" does not
  exist in lang files. SVN Rev[1951]

* Fri Nov 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-17
- REMOVED: removed stray debug code that wrote to /tmp. SVN Rev[1909]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- CHANGED:   Updated the Bulgarian language elastix. SVN Rev[1857]

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palsoanto.com> 2.0.0-15
- CHANGED:   Updated fr.lang. SVN Rev[1825]
- NEW:       New lang file fa.lang (Persian). SVN Rev[1823]

* Mon Sep 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-14
- FIXED:     Corrected the message that say: Fax has been sended correctly, now says: Fax has been sent correctly. SVN Rev[1753], Bug[#518]

* Tue Sep 14 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-13
- CHANGED:   Valid types of extensions to upload files and show message for incorrect files or files sended. Rev[1735]

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-12
- CHANGED:   Change help files in send fax, fax viewer and email template. Rev[1679]
-            Show label (types of files supported) in send fax module

* Thu Jun 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-11
- Fixed bug where cannot edit a fax information. Link incorrect to faxvisor and not faxviewer

* Mon Mar 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- Fixed bug, permission 666 to database fax.db

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-9
- Defined number order menu.

* Mon Mar 01 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-8
- Update release module.

* Tue Jan 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-7
- function getParameter removed in each module.

* Wed Dec 30 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Fixed bug name module, the name is sendfax and not send_fax.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-5
- New module send fax.

* Fri Dec 04 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- Increment released.

* Sat Oct 17 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-3
- Add accion uninstall rpm.
- Rename module faxvisor by faxview.
- Changed of words for a better definition of menus and messages.

* Mon Sep 07 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-2
- New structure menu.xml, add attributes link and order.

* Wed Aug 26 2009 Bruno Macias <bmacias@palosanto.com> 1.0.0-1
- Initial version.
