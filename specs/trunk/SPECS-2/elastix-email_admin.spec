%define modname email_admin

Summary: Elastix Module Email 
Name:    elastix-%{modname}
Version: 2.0.4
Release: 10
License: GPL
Group:   Applications/System
#Source0: %{modname}_%{version}-4.tgz
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0.4-13
Prereq: RoundCubeMail
Prereq: php-imap
Prereq: postfix, spamassassin, cyrus-imapd

%description
Elastix Module Email

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# ** /etc path ** #
mkdir -p    $RPM_BUILD_ROOT/etc/postfix
mkdir -p    $RPM_BUILD_ROOT/usr/local/bin/
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mkdir -p    $RPM_BUILD_ROOT/var/www/html/libs/

# ** libs ** #
mv setup/paloSantoEmail.class.php     $RPM_BUILD_ROOT/var/www/html/libs/
mv setup/cyradm.php                   $RPM_BUILD_ROOT/var/www/html/libs/
mv setup/checkSpamFolder.php          $RPM_BUILD_ROOT/var/www/
mv setup/deleteSpam.php               $RPM_BUILD_ROOT/var/www/

# ** dando permisos de ejecucion ** #
chmod +x $RPM_BUILD_ROOT/var/www/checkSpamFolder.php
chmod +x $RPM_BUILD_ROOT/var/www/deleteSpam.php


# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT/var/www/html/
mv modules/ $RPM_BUILD_ROOT/var/www/html/

# Additional (module-specific) files that can be handled by RPM
#mkdir -p $RPM_BUILD_ROOT/opt/elastix/
#mv setup/dialer

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.

# ** postfix config ** #
mv setup/etc/postfix/virtual.db               $RPM_BUILD_ROOT/usr/share/elastix/

# Remplazo archivos de Postfix y Cyrus
mv setup/etc/imapd.conf.elastix               $RPM_BUILD_ROOT/etc/
mv setup/etc/postfix/main.cf.elastix          $RPM_BUILD_ROOT/etc/postfix/
mv setup/etc/cyrus.conf.elastix               $RPM_BUILD_ROOT/etc/

# ** /usr/local/ files ** #
mv setup/usr/local/bin/spamfilter.sh          $RPM_BUILD_ROOT/usr/local/bin/

mv setup/   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

%pre
# ****Agregar el usuario cyrus con el comando saslpasswd2:
#echo "palosanto" | /usr/sbin/saslpasswd2 -c cyrus -u example.com

mkdir -p /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
touch /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
# Habilito inicio automático de servicios necesarios
chkconfig --level 345 saslauthd on
chkconfig --level 345 cyrus-imapd on
chkconfig --level 345 postfix on

# Cambiar permisos del archivo /etc/sasldb2 a 644
#chmod 644 /etc/sasldb2


# Creo el archivo /etc/postfix/network_table if not exixts
if [ ! -f "/etc/postfix/network_table" ]; then
    touch /etc/postfix/network_table
    echo "127.0.0.1/32" >  /etc/postfix/network_table
fi

# Verifo si existe virtual.db para previa installation
if [ ! -f /etc/postfix/virtual.db ]; then
   mv /usr/share/elastix/virtual.db /etc/postfix/virtual.db
   chown root:root /etc/postfix/virtual.db
else
   rm -f /usr/share/elastix/virtual.db
fi

# TODO: TAREA DE POST-INSTALACIÓN
# Cambio archivos de Postfix e Imapd con los de Elastix
# Only replace main.cf on install  and user spamfilter create
if [ $1 -eq 1 ]; then
    mv /etc/imapd.conf /etc/imapd.conf.orig
    cp /etc/imapd.conf.elastix /etc/imapd.conf

    mv /etc/postfix/main.cf  /etc/postfix/main.cf.orig
    cp /etc/postfix/main.cf.elastix /etc/postfix/main.cf

    mv /etc/cyrus.conf /etc/cyrus.conf.orig
    cp /etc/cyrus.conf.elastix /etc/cyrus.conf

    # Create the user spamfilter
    /usr/sbin/useradd spamfilter
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

%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete Email menus"
  elastix-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
%defattr(-, root, root)
/usr/local/bin/spamfilter.sh
/etc/imapd.conf.elastix
/etc/postfix/main.cf.elastix
/etc/cyrus.conf.elastix
/usr/share/elastix/virtual.db
/var/www/checkSpamFolder.php
/var/www/deleteSpam.php


%changelog
* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-10
- CHANGED: Fixed usability bug:
  "http://bugs.elastix.org/view.php?id=799" where password fields 
  (password and re-type password) must be join. SVN Rev[2468]
- CHANGED: Email - Remote SMTP:  Add label as example: 
  username@domain.com and change the image of help. 
  Change labels user (Email Account) password (Email Account) 
  to Username and Password. SVN Rev[2467]

* Mon Mar 28 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- CHANGED: Email - Antispam: Change el name of button "Update" 
  to "Save". SVN Rev[2466]
- CHANGED: New look and styles in Remote SMTP. Add functionality 
  to show the commons Mail servers like GMAIL, HOTMAIL, YAHOO. 
  SVN Rev[2465]
- FIXED:   Fix bug "http://bugs.elastix.org/view.php?id=800". 
  Form don't have any required values. However appear a 
  legend "* Required field", this legend must be removed.
  SVN Rev[2464]
- CHANGED: Change the styles of remote smtp module and the way 
  to create certificates was changed by executing a 
  command "/etc/pki/tls/certs/make-dummy-cert" to create a 
  new certicate. SVN Rev[2460]

* Thu Mar 24 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-8
- FIXED:  modules - email_admin: Fixed bug where Spam folder 
  per user was never "subscribe". 
  For more information "http://bugs.elastix.org/view.php?id=792"
  SVN Rev[2455]

* Sat Mar 19 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- CHANGED: In spec file move files to execute the action to
  to remove Spam and create Spam folders per email accounts.
- CHANGED: In spec file change prereq elastix >= 2.0.4-13
- CHANGED: email_admin - antispam:  Add Help in module antispam
  and  functionality of delete emails of Spam per email account.
  SVN Rev[2441]
- CHANGED: misspell the word mailman, changed from mailmam to 
  mailman.  SVN Rev[2409]
- CHANGED:  New functionality of Antispam.
        - Create of Spam folder by users
        - Better filter of Spam by Sieve Service
        - Better performance
  SVN Rev[2396] 

* Tue Mar 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- CHANGED: In spec file change prereq elastix >= 2.0.4-10
- CHANGED: In spec file removed lines to change password of 
  cyrus user, now firstboot has the job to do it.

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED:   In Spec add lines to support install or update
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new 
  format to sql script for administer process install, update 
  and delete. SVN Rev[2332]
- ADDED: module antispam, added a new field to change the 
  rewrite header in the file local.cf. SVN Rev[2330]
- FIXED: module remote_smtp, fixed spelling mistake, the 
  word autentification was replaced by aunthentication.
  SVN Rev[2326]
- CHANGED: module antispam, the configuration files are 
  created only in the action activate spam filter, also 
  changed the error messages. SVN Rev[2323]
- ADDED: Module antispam, added the exec command service 
  spamassassin start and stop for the activation or 
  desactivation of the antispam service. SVN Rev[2319]
- CHANGED: changed the db.info of fax to the format used in 
  elastix-dbprocess. SVN Rev[2316]
- ADDED: added the folders update, delete and install, and the 
  sql script for the installation, also db.info has the 
  correct format used in elastix-dbprocess. SVN Rev[2315]

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED:  menu.xml to support new tag "permissions" where has 
  all permissions of group per module and new attribute "desc" 
  into tag  "group" for add a description of group. 
  SVN Rev[2294][2299]
- FIXED:    Email_admin - Remote SMTP: fixed bug #643 and #687 
  in elastix.org.
       #643: config file main.cf with respect add a relay host
       #687: error in validation username as email
  SVN Rev[2255]
- FIXED:    Problem if any account was deleted due to if there 
  is an error while to delete an email account and its user on 
  system cannot be removed the account is deleted but the user not, 
  it occur when a new account is created with the same user that 
  was deleted because this user in system exist.. [#489] 
  SVN Rev[2246][2247][2249]
- FIXED:    Email - Email Account:  password of email account 
  cannot be replaced using module email account, the error was 
  in function edit_email_account where use 
  "crear_usuario_correo_sistema" where send var $email="". 
  SVN Rev[2240]
  

* Thu Dec 30 2010 Eduardo Cueva <ecueva@palsoanto.com> 2.0.4-2
- CHANGED: In Spec file put process to move cyradm.php to 
  /var/www/html/libs

* Thu Dec 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- CHANGED: Additionals libs, move libs from additional folder 
  to each specify module. By example paloSantoEmail.class.php
  SVN Rev[2150]

* Mon Dec 20 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-25
- CHANGED: In spec file add instructions post and install about
  email from elastix.spec.
- NEW:     Files about configuration email was moved from 
  additionals to setup forlder of email_admin module, these 
  change is for better organization in elastix.spec. SVN Rev[2111]
* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-24
- ADD:     Add new prereq to roundcube in spec file
- CHANGED: massive search and replace of HTML encodings with the 
  actual characters. SVN Rev[2002]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-23
- CHANGED: Updated the Bulgarian language elastix. SVN Rev[1857]

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-22
- FIXED:  postfix configuration support in migration from 1.6 to 2.0.
  Some changes appear in email account 
  See in http://bugs.elastix.org/view.php?id=490 [#490] SVN Rev[1840]
- CHANGED: Updated fr.lang. SVN Rev[1825]

* Tue Oct 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-21
- ADDED:      New fa.lang file (Persian). SVN Rev[1793]

* Tue Sep 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-20
- CHANGED: Apply all changes in before realease.

* Mon Sep 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-19
- NEW:     Added the certificate option to allow the autentication for remote smtp. SVN Rev[1748]

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-18
- CHANGED: Prereq elastix-2.0.0-34

* Tue Aug 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-17
- FIXED: Work around PHP bug (forget to close httpd file descriptors on PHP fork()) for the case of mailman restart. Requires SVN commit #1696. Rev[1703].

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- CHANGED:   Change the help files of email_list and remote SMTP modules.

* Wed Jul 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-15
- CHANGED:   Maintenance coding(Menu WebMail from menu.xml). Rev[1638] 

* Fri Jul 23 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-14
- FIXED: Name actions button was changed to "Enable" and "Disable".

* Wed Jul 21 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-13
- CHANGED: Module remote SMTP was improved. 
-          Fixed bug the radio button enable or disable remote smtp [#237].

* Thu Jul 01 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-12
- CHANGED: Change the style in Remote SMTP module. 

* Mon Jun  7 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-11
- Fixed bug where a domain cannot have a character "_"

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- Defined number order menu.

* Mon Mar 01 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-9
- Update release module.

* Tue Jan 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-8
- Function getParameter removed in each module.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-7
- Fixed bug, validation email format in module remote_smtp.

* Fri Dec 04 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Module Remote SMTP, more testing.
- Fixed minor bugs in emails.

* Tue Oct 20 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-5
- Fixed bug in module Remote SMTP, improved definition in the hostname and domain.

* Tue Oct 20 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- Fixed bug name of id module, remote_smpt to remote_smtp.

* Mon Oct 19 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-3
- Add accion uninstall rpm.
- Fixed minor bugs in definition words languages and messages.

* Mon Sep 07 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-2
- Fixed Bug in email configuration, delete @example.com and validation in email box when not exits.
- New module smart host.
- New structure menu.xml, add attributes link and order.

* Wed Aug 26 2009 Bruno Macias <bmacias@palosanto.com> 1.0.0-1
- Initial version.
