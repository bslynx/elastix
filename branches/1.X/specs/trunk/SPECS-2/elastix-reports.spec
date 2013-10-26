%define modname reports

Summary: Elastix Module Reports 
Name:    elastix-reports
Version: 2.2.0
Release: 7
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
#Source0: %{modname}_%{version}-5.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.2.0-15
Prereq: asterisk

%description
Elastix Module Reports

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT/var/www/html/
mv modules/ $RPM_BUILD_ROOT/var/www/html/

# Additional (module-specific) files that can be handled by RPM
#mkdir -p $RPM_BUILD_ROOT/opt/elastix/
#mv setup/dialer

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p                             $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mkdir -p                             $RPM_BUILD_ROOT/var/www/html/libs/
mv setup/paloSantoCDR.class.php      $RPM_BUILD_ROOT/var/www/html/libs/
mv setup/paloSantoTrunk.class.php    $RPM_BUILD_ROOT/var/www/html/libs/
mv setup/paloSantoRate.class.php     $RPM_BUILD_ROOT/var/www/html/libs/
mv setup/paloSantoQueue.class.php    $RPM_BUILD_ROOT/var/www/html/libs/
mv setup/                            $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml                          $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

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
  echo "Delete Reports menus"
  elastix-menuremove "%{modname}"

  echo "Dump and delete %{name} databases"
  elastix-dbprocess "delete" "$pathModule/setup/db"
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*

%changelog
* Tue Nov 22 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-7
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-15
- FIXED: Billing Setup: remove nested <form> tag
  SVN Rev[3276]

* Sat Oct 29 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-6
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-13

* Sat Oct 29 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-5
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-12
- CHANGED: Modules - Summary By Extension: changed size of popup in 
  Summary By Extension. SVN Rev[3227]
- FIXED: Modules - Summary by extension: Changed the image flecha.png 
  with background blank with transparent as background.
  CHANGED: Modules - menu.xml: Changed label of summary by extension
  by summary and User Management with users.
  SVN Rev[3204]
- CHANGED: module dest_distribution, changed the style of the module
  to adapt it to the new theme elastixneo
  SVN Rev[3198]
- CHANGED: module graphic_report, changed the way to present a graphic
  in case there is no records for queues
  SVN Rev[3191]
- UPDATED: reports modules  templates files support new elastixneo theme
  SVN Rev[3154]
- UPDATED: reports modules  templates files support new elastixneo theme
  SVN Rev[3152]

* Fri Oct 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.2.0-4
- FIXED: Modules - Billing Reports: Fixed bug where trunk SIP 
  or IAX were not showed in billing reports. SVN Rev[3065]
- CHANGED: module billing_rates, the word "required field" was 
  deleted for view mode. SVN Rev[3027]
- CHANGED: module billing_setup, the asterisks in labels and word 
  "required field" were removed for view mode. SVN Rev[3020]

* Thu Sep 22 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-3
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-4
- CHANGED: module graphic_report, deleted unnecessary asterisks
  SVN Rev[2966]
- CHANGED: module dest_distribution, deleted unnecessary asterisks
  SVN Rev[2965]
- CHANGED: module billing_report, deleted unnecessary asterisks
  SVN Rev[2964]
- CHANGED: module cdrreport, deleted unnecessary asterisks
  SVN Rev[2963]

* Fri Sep 09 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-2
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-3
- CHANGED: module cdrreport, only administrators can delete CDRs
  SVN Rev[2941]

* Fri Aug 26 2011 Alberto Santos <asantos@palosanto.com> 2.2.0-1
- CHANGED: In spec file, changed prereq elastix >= 2.2.0-2
- CHANGED: module cdrreport, if user does not have an extension
  assigned, a message is showed
  SVN Rev[2881]

* Tue Jul 19 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-15
- CHANGED: In spec file change prereq elastix >= 2.0.4-29
- FIXED: Modules - cdrreport: Fixed bug where any user without 
  an extension assigned could see all cdr in cdrreports.
  SVN Rev[2835]
- CHANGED: module graphic_report, in case of error no graphic 
  is showed. SVN Rev[2809]
- CHANGED: module graphic_report, when there is no data to show 
  the design was changed like used in module email_stats.
  SVN Rev[2800]
- CHANGED: module dest_distribution, when there is no data to 
  show the design is changed like used in module email_stats. 
  SVN Rev[2799]
- CHANGED: module channelusage, when there is no data to show a 
  jpgraph error was displayed. Now in this case a blank image 
  with the message "Nothing to show yet" and the title of it 
  is displayed. SVN Rev[2777]

* Wed Jun 29 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-14
- FIXED: module billing_report, if user press next or last without
  pressing show button first, the user gets a blank page. Now the
  user can press next or last without pressing show button first
  to see the data on next page
  SVN Rev[2767]
- FIXED: module summary_by_extension, instead of the accent on the
  wordsm a special character was displayed. Now the accent is showed
  SVN Rev[2760]

* Fri Jun 24 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-13
- CHANGED: IN spec file change prereq elastix >= 2.0.4-25
- FIXED: module graphic_report, changed action onclick to onchange 
  on combo box. SVN Rev[2756].
- FIXED: Bug in module Dest distribution, Error to create image.
  PieGraph class do not exists, To solve the library 
  paloSantoGraphImage.lib.php had to be included. SVN Rev[2754]
- CHANGED: Module CDR Reports, change soap name 
 class SOAP_Cdr.class.php to SOAP_CDR.class.php. SVN Rev[2750]

* Mon Jun 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-12
- CHANGED: Modules - Trunk: The ereg function was replaced by the 
  preg_match function due to that the ereg function was deprecated 
  since PHP 5.3.0. SVN Rev[2688]

* Thu May 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-11
- ADDED: CDR Report: Add support for filter by ringgroup. SVN Rev[2617].
- FIXED: CDR Report: fix typo in Spanish translation. SVN Rev[2612]
- FIXED: CDR Report: fix date comparison bug on delete scenario - 
  dates were being compared with "d M Y" format, should be compared 
  with ISO format. SVN Rev[2611]
- CHANGED: CDR Report: report now uses CDR API with parameter arrays.
  SVN Rev[2611]
- CHANGED: paloSantoCDR: add checks for invalid parameter arrays.
  SVN Rev[2610]
- FIXED: module summary_by_extension, for numbers too big the 
  leyend could overlaps the percentages. SVN Rev[2606]
- FIXED: cdrreport: Fixed bug [#821] filters don't work if the 
  action is "delete cdr". SVN Rev[2605]
- FIXED: paloSantoCDR: refuse to process any query or delete that 
  specifies a non-null date in an invalid format. SVN Rev[2604]
- ADDED: paloSantoCDR: add support for ringgroup filtering. SVN Rev[2603]
- CHANGED: CDR Report: create new method contarCDRs that receives 
  a parameter array, and modify getNumCDR to forward to contarCDRs
  SVN Rev[2599]
- CHANGED: CDR Report: make Delete_All_CDRs() use getParam() helper.
  SVN Rev[2599]

* Tue Apr 26 2011 Alberto Santos <asantos@palosanto.com> 2.0.4-10
- CHANGED: module cdrreport, changed class name to core_CDR
  SVN Rev[2577]
- CHANGED: module cdrreport, changed name from puntosF_CDR.class.php
  to core.class.php
  SVN Rev[2569]
- NEW: new scenarios for SOAP in cdrreport
  SVN Rev[2560]
- FIXED: module cdrreport, character '}' misplaced in confirming
  message to delete cdrs
  SVN Rev[2525]
- FIXED: Reports - billing reports: Fixed style.css, a class of
  css was written bad
  SVN Rev[2508]

* Tue Apr 05 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-9
- CHANGED:  Reports - Billing reports: Changes in styles and 
  tpl. SVN Rev[2506]

* Wed Mar 30 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-8
- FIXED:  Reports - Email: Button cancel don't work in action 
  edit due to on URL put parameter "edit" where that parameter 
  has the name of rate and it is wrong because this parameter 
  is only for the action EDIT. SVN Rev[2474]

* Tue Mar 29 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-7
- FIXED: Reports - Cdrreports:  
  Fix bug "http://bugs.elastix.org/view.php?id=753" this 
  require commit 2445, and change function "borrarCDRs" 
  where query to execute never receive the array of values 
  for parametrization method. SVN Rev[2446]
- FIXED: reports - cdrreports:  Fixed bug 
  "http://bugs.elastix.org/view.php?id=753" where repors of 
  call from cdr reports cannot be deleted. SVN Rev[2445]
- CHANGED: module summary_by_extension, changed the column 
  names according to the bug #756. SVN Rev[2432]
- CHANGED: module billing_rates, changed the message where it 
  is one suggestion to keep or create a new rate according to the
  bug #755. SVN Rev[2431]
- CHANGED: module cdrreport, changed the popup message to "Are 
  you sure you wish to delete the displayed CDR(s)?" and the 
  delete button to "Delete the displayed CDR(s)". SVN Rev[2426]
- CHANGED: module cdrreport, changed the title from "CDRReport"
  to "CDR Report". SVN Rev[2422]

* Tue Mar 01 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-6
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-10
- FIXED:  Reports - billing_rates/billing_report:  Fixed bug 
  where module billing rate does not work, the problem was the 
  actions are bad and the comparison was wrong. SVN Rev[2388]

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-5
- CHANGED:  In Spec file add prerequiste elastix 2.0.4-9

* Mon Feb 07 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-4
- CHANGED:   In Spec add lines to support install or update
  proccess by script.sql.
- DELETED:   Databases sqlite were removed to use the new format 
  to sql script for administer process install, update and delete
  SVN Rev[2332]
- FIXED:  Reports - Billing_report: Fixed some bug report in 
  bugs.elastix.org [694][709] and field time of duration per 
  call are in format number(s) (number(h) number(m) number(s)). 
  Example: 145s (2m 25s). SVN Rev[2327]
- ADD:  addons, agenda, reports. Add folders to contain sql 
  scrips to update, install or delete. SVN Rev[2321]

* Thu Feb 03 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-3
- CHANGED:  menu.xml to support new tag "permissions" where has 
  all permissions of group per module and new attribute "desc" 
  into tag  "group" for add a description of group. 
  SVN Rev[2294][2299]
- CHANGED:  Put rate by default in billing rates, when the modules 
  is used for first time appear Default rate as unique rate created.
  SVN Rev[2261]
- CHANGED:  paloSantoCDR, changed to order DESC in the function 
  listarCDRs. SVN Rev[2258]

* Mon Jan 17 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-2
- CHANGED:   Reports: Billing Module: Change billing reports 
  and billing rates for better performance, creation of new 
  rates and edit the same rates without affect the reports 
  with rates older.....[#205] SVN Rev[2244]

* Thu Dec 23 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.4-1
- CHANGED: Additionals libs, move libs from additional folder 
  to each specify module by example paloSantoCDR.class.php
   SVN Rev[2150]

* Mon Dec 06 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-21
- CHANGE: add new prereq asterisk in spec file.
- CHANGE: cdrreport module, change format to export and get data
  New function getNumCDR in PaloSantoCDR to obtain total of 
  regiters. SVN Rev[2046]
- FIXED: Graphic Report: add rawmode=yes to all graphic URLs.
  SVN Rev[2037]
- CHANGED: Graphic Report: make use of new functionality to 
  implement expansion of trunk groups. Requires 2022 
  (getTrunkGroupsDAHDI() function), 2032 (rewrite of loadTrunks()) 
  to work properly. Fixes Elastix bug #468 for Elastix 2.0.
  SVN Rev[2035]
- CHANGED: Graphic Report: rewrite the method used to query total 
  duration/callcount by trunk:
           Removes opportunities for SQL injection
           Uses a single SELECT instead of two nested SELECTs, 
             more efficient search
           Removes unnecessary use of TO_DAYS function, enabling 
             speedup by applying indexes on cdr.calldate
           Adds capability to query for multiple trunks, required
             for trunk groups
           Fixes potential bug in which statistics for DAHDI/1 
             trunk would include DAHDI/10, DAHDI/11...
  SVN Rev[2032]
- CHANGED:  remove trunk.db in setup of reports (svn) and new 
  trunk.db in setup folder of pbx. It changes is for VOIP Provider 
  module.
  install.php of pbx was changed to support new trunk.db
  install.php of reports was changed, because the support of 
  trunk.db is in install.php of pbx by VOIP Provider. SVN Rev[2026]
- CHANGED: CDR Report: rewrite of report code. This achieves 
 the following:
           Fix (potential) vuln of non-admin user deleting CDRs 
             belonging to other users
           Improve readability of code
           Making use of _tr and load_language_module() for 
             better i18n support
           Depend on newer support for integrated CSV/XLS/PDF 
             export of full report
  Requires SVN commit 2020 to work properly. SVN Rev[2021]
- CHANGED: CDR Report: improve XHTML compatibility. SVN Rev[2019]
- FIXED:   Graphic Report: fix regression due to picking 'menu' 
  variable from $_POST for module selection - Graphic. 
  SVN Rev[2004]
- CHANGED: Graphic Report: detect availability of getParameter() 
  at runtime. SVN Rev[2004]
- CHANGED: Graphic Report: remove invalid <BODY> tag from filter 
  template. SVN Rev[2004]
- CHANGED: massive search and replace of HTML encodings with the 
  actual characters. SVN Rev[2002]
- CHANGED: Billing Report: 
           stop assigning template variable "url" directly, and 
  remove nested <form> tag. SVN Rev[1985]
           add "menu" URL variable to list of variables for grid
  SVN Rev[1989]
- CHANGED: Destination Distribution: rework functionality from 
  images/pie_dist.php into main module. This integrates graphic 
  generation into its only user so images/pie_dist.php is no 
  longer needed. SVN Rev[1980]
- CHANGED: Destination Distribution: separate querying code into its
  own function, in preparation for graphic rework. SVN Rev[1979]
- CHANGED: Channel Usage: switch to use of palosantoGraphImage.lib.php 
  for graph generation. Requires commits 1964,1969 to work properly.
  SVN Rev[1970]
- CHANGED: Summary by Extension: remove reference to 
  paloSantoGraph.class.php. SVN Rev[1968]

* Fri Nov 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-20
- FIXED: make module aware of url-as-array in paloSantoGrid.
     Split up URL construction into an array.
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags from the filter HTML template fetch. They are 
      not required, since the template already includes a proper <form> 
      tag enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 
      in order to work properly.
  SVN Rev[1916]
- FIXED: make module aware of url-as-array in paloSantoGrid. 
     Delegate URL construction to class paloSantoGrid instead of calling 
      construirURL directly
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags from the filter HTML template. They are not 
      required, since the template already includes a proper <form> tag 
      enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 
      in order to work properly.
  SVN Rev[1914]
- FIXED: make module aware of url-as-array in paloSantoGrid. This commit 
  shows the basic transformations required on each module to escape URL 
  variables:
     Split up URL construction into an array.
     Assign the URL array as a member of the $arrGrid structure.
     Remove <form> tags around the returned value of fetchGrid method. 
      They are not required, since the template already includes a 
      proper <form> tag enclosing the grid.
     Part of fix for Elastix bug #572. Requires commits 1901 and 1902 
      in order to work properly.
  SVN Rev[1903]

* Thu Oct 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-19
- FIXED:   Fixed bug by update elastix-report replace rate.db it 
  remove all data in rate.db. Problem was installer.php. SVN Rev[1864]

* Wed Oct 27 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-18
- CHANGED: Updated the Bulgarian language elastix. SVN Rev[1857]

* Mon Oct 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-17
- FIXED:   variable decimalTotal undefined. This variable show time in 
  seconds > 3600 in format ( h m s )[#353] SVN Rev[1845]
- CHANGED: Updated fr.lang. SVN Rev[1825]
- ADDED:   New lang file fa.lang (Persian) SVN Rev[1823]

* Wed Aug 18 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-16
- FIXED: work around empty DSN by using Elastix 2 support for fetching DSN from /etc/amportal.conf. Rev[1711]
- FIXED: Do not treat an empty recordset as an error when filling data arrays for graph. Rev[1711]

* Thu Aug 12 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-15
- DELETED: Remove definition to connect asteriskcdrdb, it is not necessary.

* Sat Aug 07 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-14
- CHANGED: Change help files in Summary by Extension.
- FIXED:   Remove images/graphReport.php and fold its functionality back into index.php for module. This bring graph details under ACL control.

* Wed Jul 28 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-13
- FIXED:   Summary By extension, querys has been improved, now the data is from channel y dstchannel. Rev[1640]

* Mon Jun 29 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-12
- FIXED:   Graphic Report: Fold functionality for graphic reports into main index.php, delete libs/grafic*.php, and adjust template accordingly. This places graphic reports under ACL control. In addition, fix leftover reference to phone_numbers.php used by Graphic Report.

* Thu Jun 17 2010 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-11
- New fr lang was added.

* Fri Mar 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-10
- Changed order menu.

* Tue Mar 16 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-9
- Defined number order menu.

* Mon Mar 01 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-8
- Update relase module.

* Tue Jan 19 2010 Bruno Macias <bmacias@palosanto.com> 2.0.0-7
- Function getParamater was removed in each module.

* Wed Dec 30 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-6
- Rename trunk in voip provider, database trunk, new rename.

* Tue Dec 29 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-5
- Rename trunk in voip provider, database trunk.db

* Fri Dec 04 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-4
- Incremental released.

* Mon Oct 19 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-3
- Add accion uninstall rpm.
- Fixed minor bugs in definition words languages and messages.

* Mon Sep 07 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-2
- New structure menu.xml, add attributes link and order.

* Wed Aug 26 2009 Bruno Macias <bmacias@palosanto.com> 1.0.0-1
- Initial version.