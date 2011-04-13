%define _unpackaged_files_terminate_build 0
%define _missing_doc_files_terminate_build 0
%define modname fop2

Summary: FOP2
Vendor: asternic.biz
Name: %{modname}
Version: 2
Release: 2.21
License: GPL
Group: Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Packager: Nicolas Gudino <nicolas.gudino@asternic.biz>
URL: http://www.fop2.com
Prereq: elastix-pbx >= 2.0.4-8

%description
This package contains the Flash Operator Panel 2 for Asterisk based PBX

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p                     $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mkdir -p                     $RPM_BUILD_ROOT/var/www/html/admin/modules
mkdir -p                     $RPM_BUILD_ROOT/etc/asterisk
mkdir -p                     $RPM_BUILD_ROOT/etc/rc.d/init.d
mkdir -p                     $RPM_BUILD_ROOT/usr/local
mkdir -p                     $RPM_BUILD_ROOT/usr/share/doc

mv modules/fop2admin         $RPM_BUILD_ROOT/var/www/html/admin/modules
mv modules/fop2              $RPM_BUILD_ROOT/var/www/html

mv setup/etc/asterisk/*      $RPM_BUILD_ROOT/etc/asterisk
mv setup/etc/rc.d/init.d/*   $RPM_BUILD_ROOT/etc/rc.d/init.d

mv setup/usr/local/fop2      $RPM_BUILD_ROOT/usr/local
mv setup/usr/share/doc/fop2  $RPM_BUILD_ROOT/usr/share/doc

mv setup/   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menufop2.xml $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menufop.xml $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

%pre
mkdir -p /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
touch /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
if [ $1 -eq 2 ]; then
    rpm -q --queryformat='%{VERSION}-%{RELEASE}' %{name} > /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/preversion_%{modname}.info
fi

%post
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"

# Run installer script to fix up ACLs and add module to Elastix menus.
elastix-menumerge /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/menufop2.xml

preversion=`cat $pathModule/preversion_%{modname}.info`

if [ $1 -eq 1 ]; then #install
    sed 's/\;listen_port=4445/listen_port=4446/g' /var/www/html/panel/op_server.cfg > /tmp/op_server.cfg && mv -f /tmp/op_server.cfg /var/www/html/panel/op_server.cfg
    killall op_server.pl
    chkconfig --add fop2
    service fop2 start
    grep -q extensions_override_fop2 /etc/asterisk/extensions_override_freepbx.conf || echo "#include extensions_override_fop2.conf" >> /etc/asterisk/extensions_override_freepbx.conf
  # Removing fop menu
    echo "Delete fop menu"
    elastix-menuremove "fop"
  # The installer database
    elastix-dbprocess "install" "$pathModule/setup/db"
  # installing fop2 on freePBX
    php -q /var/www/html/admin/modules/fop2admin/rpminst.php install
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
  sed 's/listen_port=4446/\;listen_port=4445/g' /var/www/html/panel/op_server.cfg > /tmp/op_server.cfg &&  mv -f /tmp/op_server.cfg /var/www/html/panel/op_server.cfg
  service fop2 stop
  chkconfig --del fop2
  grep -v extensions_override_fop2 /etc/asterisk/extensions_override_freepbx.conf > /tmp/ext_override.temp && mv -f /tmp/ext_override.temp /etc/asterisk/extensions_override_freepbx.conf
# removing fop2 over freePBX
  echo "Removing FOP2 from freePBX"
  php -q /var/www/html/admin/modules/fop2admin/rpminst.php uninstall

  echo "Delete FOP2 menus"
  elastix-menuremove "%{modname}"

  echo "Restoring FOP"
  elastix-menumerge /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/menufop.xml

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-,asterisk,asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
/var/www/html/fop2
/var/www/html/admin/modules/fop2admin

%defattr(644,root,root)
/usr/local/fop2/buttons.cfg.sample
/usr/local/fop2/autobuttons.cfg
/usr/local/fop2/FOP2Callbacks.pm 

%attr(751, root, root) /usr/local/fop2/autoconfig-buttons-freepbx.sh
%attr(751, root, root) /usr/local/fop2/autoconfig-users-freepbx.sh
%attr(751, root, root) /usr/local/fop2/fop2_server
%attr(751, root, root) /usr/local/fop2/fop2recording.pl
%attr(751, root, root) /usr/local/fop2/tovoicemail.pl

%defattr(644,root,root)
/usr/share/doc/fop2/README
/usr/share/doc/fop2/LICENSE

%config
/usr/local/fop2/fop2.cfg

%defattr(-,root,root)
/etc/rc.d/init.d/fop2
/etc/asterisk/extensions_override_fop2.conf

%changelog
* Tue Apr 12 2011 Eduardo Cueva <ecueva@palosanto.com> 2-2.21
- Initial Version
