%define modname endpointconfig2

Summary: Elastix Module Distributed Dial Plan
Name:    elastix-%{modname}
Version: 2.4.0
Release: 0
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Requires: freePBX >= 2.8.1-12
Requires: elastix-framework >= 2.4.0-0
Requires: elastix-agenda >= 2.4.0-5
Requires: py-Asterisk
Requires: python-eventlet
Requires: python-tempita
Requires: pyOpenSSL
Requires: python-daemon
Requires: MySQL-python
Requires: python-cjson
Requires: pytz
Requires: php-magpierss
Requires: python-paramiko >= 1.7.6-2
Prereq: tftp-server
Conflicts: elastix-pbx <= 2.4.0-15

%description
The Elastix Endpoint Configurator is a complete rewrite and reimplementation of
the elastix-pbx module known as the Endpoint Configurator. This rewrite 
addresses several known design flaws in the old Endpoint Configurator and should
eventually be integrated as the new standard configurator in elastix-pbx.

User-visible features:
- Supports assignment of multiple accounts to a single endpoint.
- Automatic model detection implemented for most supported manufacturers.
- Improved user interface written with Ember.js.
- Network parameters can be updated onscreen in addition to being uploaded.
- Endpoint network scan is cancellable.
- The configuration of every endpoint is executed in parallel, considerably
  shortening the potential wait until all endpoints are configured.
- A log of the actual endpoint configuration can be displayed for diagnostics.
- Supports two additional download formats in addition to the download format of
  the old endpoint configurator - required for multiple account support.
- Custom properties can be assigned to the endpoint and per account, until GUI
  support is properly added.
- Can be installed alongside the old endpoint configurator.
- For supported phones, the module provides an HTTP resource to serve remote
  services, such as a phonebook browser, for better integration with Elastix.

For developers:
- The architecture of the module is plugin-friendly. Each vendor implementation
  (written in Python) has been completely encapsulated and no vendor-specific
  logic remains in the module core itself. To add a new vendor, it is enough to
  write a new implementation class in Python, add new templates if necessary,
  and add database records for MACs. Patching of the core is no longer required.
- Foundation for replacing the standard configurator dialog with a 
  vendor-specific one (not yet used).

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p                        $RPM_BUILD_ROOT/var/www/html/
mv modules/                     $RPM_BUILD_ROOT/var/www/html/

# Additional (module-specific) files that can be handled by RPM
mkdir -p $RPM_BUILD_ROOT/etc/
mv setup/etc/httpd/ $RPM_BUILD_ROOT/etc/

mv setup/usr/ $RPM_BUILD_ROOT/
mkdir -p $RPM_BUILD_ROOT/usr/local/share/elastix/endpoint-classes/tpl

rm -rf setup/build/

# ** /tftpboot path ** #
# ** files tftpboot for endpoints configurator ** #
mkdir -p $RPM_BUILD_ROOT/tftpboot
unzip setup/tftpboot/P0S3-08-8-00.zip  -d     $RPM_BUILD_ROOT/tftpboot/
rm setup/tftpboot/P0S3-08-8-00.zip
mv setup/tftpboot/*                           $RPM_BUILD_ROOT/tftpboot/
rmdir setup/tftpboot

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
elastix-menumerge /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/menu.xml

pathSQLiteDB="/var/www/db"
mkdir -p $pathSQLiteDB
preversion=`cat $pathModule/preversion_%{modname}.info`

if [ $1 -eq 1 ]; then #install
  # The installer database
    elastix-dbprocess "install" "$pathModule/setup/db"

  # Restart apache to disable HTTPS redirect on phonesrv script
  /sbin/service httpd restart
elif [ $1 -eq 2 ]; then #update
    elastix-dbprocess "update"  "$pathModule/setup/db" "$preversion"
fi
rm -f $pathModule/preversion_%{modname}.info

# Remove old endpointconfig2 menu item
elastix-menuremove endpointconfig2

# Prepare tftpboot for use by module
chmod 777 /tftpboot/
cat /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/setup/etc/xinetd.d/tftp > /etc/xinetd.d/tftp


%clean
rm -rf $RPM_BUILD_ROOT

%preun
pathModule="/usr/share/elastix/module_installer/%{name}-%{version}-%{release}"
if [ $1 -eq 0 ] ; then # Validation for desinstall this rpm
  echo "Delete distributed dial plan menus"
  elastix-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi


%files
%defattr(-, root, root)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
/usr/share/elastix/endpoint-classes
/usr/local/share/elastix/endpoint-classes
/tftpboot/*
%defattr(644, root, root)
/etc/httpd/conf.d/elastix-endpointconfig.conf
%defattr(755, root, root)
/usr/bin/*
/usr/share/elastix/privileged/*

%changelog
* Mon May 19 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Endpoint Configurator: introduce a Grandstream HTTP reboot hook and
  leave it unimplemented. This is in preparation to the integration of Hanlong 
  support.
  SVN Rev[6630]
- CHANGED: Endpoint Configurator: factor out Grandstream per-model variable
  tweaking to a separate function. This allows subclasses to override this
  tweaking for their own implemented models. This is in preparation to the
  integration of Hanlong support.
  SVN Rev[6629]
- CHANGED: Endpoint Configurator: factor out mapping of Grandstream P-vars to 
  account settings to a separate function. In addition to improving readability, 
  this allows subclasses to override the mapping. This is in preparation to the 
  integration of Hanlong support.
  SVN Rev[6628]

* Mon May 05 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- ADDED: Endpoint Configurator: add support for Grandstream GXP2160, based on
  work by Lenin Loaza.
  SVN Rev[6623]

* Thu May 01 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Endpoint Configurator: update Ember.js to 1.5.1, Handlebars to 1.3.0
  SVN Rev[6618]
- CHANGED: Endpoint Configurator: refactor Grandstream static provisioning
  dispatcher as a loop to select a method based on the existence of an URL.
  SVN Rev[6617]
- CHANGED: Endpoint Configurator: remove useless checks on setModel 
  implementations. The source value is the database, which is assumed to be
  trusted.
  SVN Rev[6616]
- CHANGED: Endpoint Configurator: remove copy of jQuery 1.8. This module will
  always use the framework copy of jQuery.
  SVN Rev[6615]

* Tue Feb 11 2014 Alex Villacis Lasso <a_villacis@palosanto.com> 2.4.0-0
- CHANGED: Endpoint Configurator: add support for new RCA model IP160s
  SVN Rev[6472]

* Sat Feb 08 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Endpoint Configurator: update Ember.js to 1.3.2

* Mon Feb 03 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Endpoint Configurator: add support for new RCA models IP115, IP125
  SVN Rev[6456]

* Thu Jan 30 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: Endpoint Configurator: update Ember.js to 1.3.1, Handlebars to
  1.2.1.
- CHANGED: Endpoint Configurator: promotion to main configurator. Transfer of
  ownership of tftpboot files and configuration to this module from elastix-pbx.
  Dropped zipped Cisco firmware files, since package will provide unpacked files.
  Dropped Java-based Grandstream configurator, since package provices PHP
  implementation.
  SVN Rev[6450]

* Fri Jan 10 2014 Jose Briones <jbriones@elastix.com>
- CHANGED: New Endpoint Configurator: The english help file was renamed to 
  en.hlp and a spanish help file called es.hlp was ADDED.
  SVN Rev[6370]

* Thu Jan 09 2014 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: fix regression introduced by moving models
  inside App.EndpointController that left a newly inserted App.Endpoint without
  a reference to the models array.
  SVN Rev[6352]

* Sat Dec 14 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: provide a "loading" template for the main
  endpoint listing using the loading view support of Ember.js 1.2.0.
  SVN Rev[6291]
- CHANGED: New Endpoint Configurator: update Ember.js to 1.2.0, Handlebars to
  1.1.2.
  SVN Rev[6290]
- CHANGED: New Endpoint Configurator: reimplement loading of model details and
  unassigned accounts using an Ember promise.
  SVN Rev[6289]
- CHANGED: New Endpoint Configurator: reimplement loading of known models and 
  current endpoints using an Ember promise.
  SVN Rev[6288]
- CHANGED: New Endpoint Configurator: internalize App.modelos structure into
  App.EndpointsController.
  SVN Rev[6287]
- CHANGED: New Endpoint Configurator: reimplement loading of configuration log
  using an Ember promise.
  SVN Rev[6286]

* Thu Dec 05 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: add timeouts to telnet read interactions.
  SVN Rev[6258]

* Tue Dec 03 2013 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.7-0
- Bump version for release
- FIXED: New Endpoint Configurator: move SQL update file to correct directory.
  SVN Rev[6240]
- CHANGED: New Endpoint Configurator: add support for new Snom models 710, 720, 
  760, 870.
- FIXED: New Endpoint Configurator: fix network configuration sequence for new
  Snom models.
- CHANGED: New Endpoint Configurator: add language support for several Snom 
  models.
  SVN Rev[6235]

* Mon Dec 02 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: fix static IP assignment of RCA IP150 by 
  starting a SSH session into the phone and running a network command directly.
  The package now requires python-paramiko.
  SVN Rev[6129]

* Tue Nov 26 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: allow removal of the configuration for
  endpoint records that have no model assigned.
  SVN Rev[6159]
- FIXED: New Endpoint Configurator: legacy CSV file upload must synthetize the
  dhcp parameter and set it to 0 when the file specifies a static IP 
  configuration. Additionally, parameter validation was relaxed to avoid 
  dropping the string '0' as an unset value.
  SVN Rev[6158]

* Mon Nov 25 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: rename Polycom phone models to better fit
  the values found via Cisco Discovery Protocol. Add some comments to the SQL
  updates.
  SVN Rev[6154]

* Fri Nov 22 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: fix support for static provisioning for
  Aastra phones by working around a firmware bug that hangs an HTTP POST if
  headers and body end up in different IP packets.
  SVN Rev[6145]
- FIXED: New Endpoint Configurator: make Aastra XML services handle missing RSS
  links and forward the 404 to the phone.
  SVN Rev[6144]

* Thu Nov 21 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: display spinner when removing accounts
  information.
  SVN Rev[6143]

* Tue Nov 19 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: update RSS URLs with working versions
  SVN Rev[6127] 
- CHANGED: New Endpoint Configurator: abstract Cisco XML objects into classes
  for readability.
  SVN Rev[6126]
- CHANGED: New Endpoint Configurator: convert Cisco feature code directory to
  dynamic table read from FreePBX.
  SVN Rev[6124]

* Mon Nov 18 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: add missing import errno to Digium.py
  SVN Rev[6119]
- CHANGED: New Endpoint Configurator: centralize RSS channel list inside the
  parent class BaseVendorResource.
  SVN Rev[6118]
- CHANGED: New Endpoint Configurator: implement basic XML phonebook and RSS 
  support for Aastra phones. Tested with Aastra 6739i. In the process, implement
  reading of current feature codes from FreePBX, instead of hardcoding them.
  SVN Rev[6117]  
- CHANGED: New Endpoint Configurator: add static provisioning support for Aastra
  phones. Define softkeys to access configured accounts past the third line.
  Define URL for (not yet implemented) XML services for Aastra.
  SVN Rev[6114]

* Sun Nov 17 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add support for highlighting accounts that
  are being registered by hosts not in the current set of scanned hosts, to help
  prevent assignment of an already-used account to an endpoint.
  SVN Rev[6111]

* Fri Nov 15 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add support for Polycom remote phonebook
  via the microbrowser.
  SVN Rev[6109]

* Wed Nov 13 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: do not crash when configuring the RCA IP150 
  with static IP and just one DNS instead of two.
  SVN Rev[6085]
- FIXED: New Endpoint Configurator: tweak the Atlinks Temporis IP800 template to
  blank out unused accounts on the phone display.
  SVN Rev[6083] 
- FIXED: New Endpoint Configurator: enhance Atlinks Temporis IP800: add support
  for setting language (default Spanish), and link to remote phonebooks with the
  exact same format as Yealink. Reprogram line keys to display assigned accounts
  correctly. Bind to Elastix as primary NTP server.
  SVN Rev[6082]

* Tue Nov 12 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: make DPMA optional when removing phone 
  configuration for Digium phones.
  SVN Rev[6081]
- CHANGED: New Endpoint Configurator: when removing configuration files, also
  unregister previously detected accounts.
  SVN Rev[6080]
- FIXED: New Endpoint Configurator: the AudioCodes 310HD/320HD require a dummy
  HTTP request from the same IP that will later POST the autoconfiguration data,
  in order for the phone to accept the changes. Also set default language to 
  Spanish, and inherit timezone from the Elastix server.
  SVN Rev[6079]

* Fri Nov 08 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: tweak detection method for Elastix LXP200
  with updated firmware.
  SVN Rev[6074]
- CHANGED: New Endpoint Configurator: increase default socket timeout to 10
  seconds to cope with several endpoints that take a long time to apply changes.
  SVN Rev[6073]

* Thu Nov 07 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add support for RCA IP150
  SVN Rev[6072]

* Mon Nov 04 2013 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.6-0
- Bump version for release
- FIXED: New Endpoint Configurator: fix incorrect variable reference that caused
  an uncaught exception when configuring a static IP on a Grandstream/Elastix
  phone.
  SVN Rev[6053]
- FIXED: New Endpoint Configurator: do not silently lose uncaught exceptions when
  logging to a progress file. Required for more precise debugging.
  SVN Rev[6050]
- FIXED: New Endpoint Configurator: fix incorrect variable references in
  configuration status monitoring.
  SVN Rev[6049]
- CHANGED: New Endpoint Configurator: allow configuration of default language
  for Grandstream and Elastix endpoints by setting the custom endpoint property
  'language'. The default if unset is 'es' for Spanish.
  SVN Rev[6048]

* Sat Oct 26 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: updated internal copy of Ember.js to 
  version 1.1.2.
  SVN Rev[6041]

* Wed Oct 23 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: updated internal copy of Ember.js to
  version 1.1.0
  SVN Rev[6031]

* Tue Oct 22 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: enhance the Grandstream/Elastix 
  configurator to add the following useful parameters: NTP Server, set to the
  Elastix server; disable override of NTP server by DHCP option 42; enable 
  automatic attended transfer; set default display language to Spanish; enable
  auto-answer on Call-Info.
  SVN Rev[6026]

* Thu Oct 17 2013 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.5-0
- Bump version for release

* Wed Oct 16 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- ADDED: New Endpoint Configurator: add help files.
  SVN Rev[6013]

* Mon Oct 07 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add support for offset-based timezone
  configuration using the pytz library. The phones by the following manufacturers
  will now inherit the telephony server timezone: Atlinks, Yealink. Based on a 
  patch by Israel Santana Alemán.
  SVN Rev[5995]

* Sat Oct 05 2013 Alex Villacís Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: require and use system-installed magpierss
  instead of bundled magpierss.
  SVN Rev[5991]

* Sun Sep 29 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add status message at the end of the 
  configuration process indicating whether there were warnings or errors.
  SVN Rev[5953]

* Thu Sep 26 2013 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.4-0
- Bump version for release

* Tue Sep 17 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: New Endpoint Configurator: do not allow any code path to exit the 
  configurator without printing the end banner. This is required for the GUI to
  know that the configurator stopped running.
  SVN Rev[5894]

* Fri Sep 13 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: rework the remote phonebook support to
  allow authenticated endpoints that do not have extensions associated with an
  Elastix user to still access the internal contact list and the public contacts
  of the external contact list.
  SVN Rev[5879]

* Thu Sep 12 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add the ability to store custom templates
  that will override the default ones if required, at the directory 
  /usr/local/share/elastix/endpoint-classes/tpl . Based on a patch by Israel 
  Santana Alemán.
  SVN Rev[5875]

* Fri Sep  6 2013 Alex Villacis Lasso <a_villacis@palosanto.com> 
- CHANGED: New Endpoint Configurator: add sip notify call to Digium 
  implementation in order to reboot the phone when not (yet) configured for
  DPMA.
  SVN Rev[5841]

* Wed Sep  4 2013 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.3-0
- Bump version for release.
- CHANGED: New Endpoint Configurator: fix attempt to assign to immutable tuple
  that only raises an error in python 2.7
  SVN Rev[5835]
- CHANGED: New Endpoint Configurator: switch from python-json to python-cjson
  since the latter module exists in both CentOS and Fedora 17.
  SVN Rev[5834]
- CHANGED: New Endpoint Configurator: merge the functionality of the program
  elastix-endpointclear into elastix-endpointconfig, and remove the large code
  duplication.
- CHANGED: New Endpoint Configurator: implement clearing of endpoint 
  configuration for Digium phones. This required a change in the python API.
  SVN Rev[5832]
- FIXED: New Endpoint Configurator: request dhcp property in view init so change
  to defined value will be observed, as noted in Ember.js rc8 changelog.
  SVN Rev[5829]

* Sun Sep  1 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: use GET for read requests whenever 
  possible.
  SVN Rev[5825]
- CHANGED: New Endpoint Configurator: update Ember.js to 1.0.0
  SVN Rev[5824]

* Fri Aug 30 2013 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.2-0
- Bump version for release
- CHANGED: New Endpoint Configurator: add basic Digium phones support.
  SVN Rev[5822] 
- CHANGED: New Endpoint Configurator: update Ember.js to 1.0.0-rc8
  SVN Rev[5821]

* Thu Aug 29 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add phonebook search capability to Cisco 
  XML services.
  SVN Rev[5820]

* Wed Aug 28 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add phonebook support for Yealink.
  SVN Rev[5818]
- CHANGED: New Endpoint Configurator: add static provisioning support for 
  Grandstream GXP1450.
  SVN Rev[5817]

* Tue Aug 20 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add support for Elastix LXP100
  SVN Rev[5777]
- CHANGED: New Endpoint Configurator: add proper phonebook support for GXV3140.
  SVN Rev[5776]
- CHANGED: New Endpoint Configurator: write authentication hash as soon as it
  is available, to allow reference from HTTP requests. Required for phonebook
  in Grandstream GXV3140.
  SVN Rev[5775]

* Mon Aug 19 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add static provisioning support for GXV
  series. Tested to work with GXV3140.
  SVN Rev[5774]
- CHANGED: New Endpoint Configurator: set Elastix LXP200 and Grandstream GXP280
  as capable of static provisioning. Add default HTTP password for LXP200.
  SVN Rev[5773] 
- CHANGED: New Endpoint Configurator: serve gs_phonebook.xml as an alias to
  phonebook.xml, as requested by GXP280 phones.
  SVN Rev[5772]
- ADDED: New Endpoint Configurator: add phonebook support for Elastix phones. As
  these are rebranded GXP140x phones, it is enough to subclass the Grandstream
  phonebook class and do nothing else.
  SVN Rev[5771]

* Sun Aug 18 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add flag to inform support for static
  provisioning. Modify GUI to display the information.
  SVN Rev[5770]
- CHANGED: New Endpoint Configurator: update Ember.js to 1.0.0-rc7 and 
  Handlebars to 1.0.0.
  SVN Rev[5769]

* Fri Aug 16 2013 Alex Villacis Lasso <a_villacis@palosanto.com>
- CHANGED: New Endpoint Configurator: add some static provisioning support for
  a few supported GrandStream models. This also attempts to point the phonebook
  on GrandStream to the HTTP resource.
  SVN Rev[5768]
- ADDED: New Endpoint Configurator: add phonebook.xml resource for GrandStream.
  Fix invalid SQL for fetching the endpoint data.
  SVN Rev[5767]
- ADDED: New Endpoint Configurator: Add data for the GrandStream GXP1400
  SVN Rev[5766]

* Thu Aug 15 2013 Alex Villacis Lasso <a_villacis@palosanto.com> 0.0.1-0
- ADDED: New Endpoint Configurator: Initial release 
