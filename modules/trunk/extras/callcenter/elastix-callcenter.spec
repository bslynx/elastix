%define modname callcenter

Summary: Elastix Call Center 
Name:    elastix-callcenter
Version: 2.0.0
Release: 5
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0

%description
Elastix Call Center

%prep
%setup -n %{modname}

%install
rm -rf $RPM_BUILD_ROOT

# Files provided by all Elastix modules
mkdir -p    $RPM_BUILD_ROOT/var/www/html/
mv modules/ $RPM_BUILD_ROOT/var/www/html/

# Additional (module-specific) files that can be handled by RPM
mkdir -p $RPM_BUILD_ROOT/opt/elastix/
mv setup/dialer_process/dialer/ $RPM_BUILD_ROOT/opt/elastix/
chmod +x $RPM_BUILD_ROOT/opt/elastix/dialer/dialerd
mkdir -p $RPM_BUILD_ROOT/etc/rc.d/init.d/
mv setup/dialer_process/elastixdialer $RPM_BUILD_ROOT/etc/rc.d/init.d/
chmod +x $RPM_BUILD_ROOT/etc/rc.d/init.d/elastixdialer
rmdir setup/dialer_process
mkdir -p $RPM_BUILD_ROOT/etc/logrotate.d/
mv setup/elastixdialer.logrotate $RPM_BUILD_ROOT/etc/logrotate.d/elastixdialer

# The following folder should contain all the data that is required by the installer,
# that cannot be handled by RPM.
mkdir -p    $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv setup/   $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv menu.xml $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/
mv CHANGELOG $RPM_BUILD_ROOT/usr/share/elastix/module_installer/%{name}-%{version}-%{release}/

%post

# Run installer script to fix up ACLs and add module to Elastix menus.
elastix-menumerge /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/menu.xml

# The installer script expects to be in /tmp/new_module
mkdir -p /tmp/new_module/%{modname}
cp -r /usr/share/elastix/module_installer/%{name}-%{version}-%{release}/* /tmp/new_module/%{modname}/
chown -R asterisk.asterisk /tmp/new_module/%{modname}

php /tmp/new_module/%{modname}/setup/installer.php
rm -rf /tmp/new_module

# Add dialer to startup scripts, but disable it by default
chkconfig --add elastixdialer
chkconfig --level 2345 elastixdialer off

# Fix incorrect permissions left by earlier versions of RPM
chown -R asterisk.asterisk /opt/elastix/dialer

%clean
rm -rf $RPM_BUILD_ROOT

%preun
if [ $1 -eq 0 ] ; then # Check to tell apart update and uninstall
  # Workaround for missing elastix-menuremove in old Elastix versions (before 2.0.0-20)
  if [ -e /usr/bin/elastix-menuremove ] ; then
    echo "Removing CallCenter menus..."
    elastix-menuremove "call_center"
  else
    echo "No elastix-menuremove found, might have stale menu in web interface."
  fi
  chkconfig --del elastixdialer
fi

%files
%defattr(-, asterisk, asterisk)
%{_localstatedir}/www/html/*
/usr/share/elastix/module_installer/*
/opt/elastix/dialer/*
/etc/rc.d/init.d/elastixdialer
/etc/logrotate.d/elastixdialer

%changelog
* Wed May 05 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-5
- Updated version, synchronized with CallCenter 1.5-3.2
- From CHANGELOG:
	1.5-3.2
	- Form Designer: fix missing string translation
	- Agent Console: fix reference for nonexistent icon image. 
	- Configuration: allow to save settings that include blank login, and
	  blank out the password in that case, to use settings from manager.conf.
	- Dialer: store Asterisk 1.6.x Bridge event as Link in current_calls.
	- Dialer: newer FreePBX versions store trunk information in table 
	  'asterisk.trunks' instead of 'asterisk.globals' as previous versions did.
	  The dialer daemon must look now on 'asterisk.trunks' if it exists. 
	- Dialer: seems newer FreePBX versions store DAHDI trunk information as DAHDI
	  not ZAP as previous versions. The dialer now needs to check under both names
	  when supplied a DAHDI trunk.
	- Dialer: use queue show instead of show queue for free agent report. 
	- Campaigns Out: modify CSV report of completed calls to add Uniqueid and all
	  attributes defined for each call.
	- Campaigns Out: previous fix for new campaign selection broke on old 
	  Elastix versions. Fix it properly for all Elastix versions.
	- Dialer: Handle Bridge event fired by Asterisk 1.6.2.x instead of Link

* Mon Apr 26 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-4
- Updated version, synchronized with CallCenter 1.5-3.1
- From CHANGELOG:
	1.5-3.1
	- Agents: Fix regression that prevented new agents from being created. Fixes
  	  Elastix bug #299.
	- Dialer: Join event reports caller-id as CallerID in Asterisk 1.4.x and
  	  CallerIDNum in Asterisk 1.6.2.x. Account for the difference.
	- Campaigns Out: greatly reduce time spent uploading a CSV phone file for
  	  a new campaign, by fixing an inefficient database operation. Also, set
      max_execution_time to 1 hour for the duration of the operation to prevent
      it from being aborted.
    - Campaigns Out: fix inability to select a form for a new campaign due to
      mismatch in control name in javascript code. Fixes Elastix bug #296.


* Mon Apr 05 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-3
- Fix issue of /opt/elastix/dialer not being tracked by RPM and having wrong owner.

* Mon Apr 05 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-2
- Updated version, synchronized with CallCenter 1.5-3
- Use elastix-menuremove when available to remove menus.
- Copy CHANGELOG into installer directory for reference.
- From CHANGELOG:
	1.5-3
    - Fix behavior of agent reporting to prevent mistaking no-agent case with DB error
    - Remove declarations of getParameter() that conflict with existing declaration
      included in Elastix 2.0
	- Merge new CallCenter reports into SVN:
	  - Agent Information: show summary of agent, first/last disconnection, and 
		received incoming calls.
	  - Agents Monitoring: real-time status of agents per queue, with total login
		time and number of calls
	  - Trunks used per hour: Displays calls placed/answered/abandoned per trunk
		over a specified time period.
	  - Agents connection type: displays summary or detail of agent sessions, with
		percentage of agent session actually spent handling calls, per queue, over
		a specified time period.
	  - Incoming calls monitoring: real-time summary of incoming calls, grouped
		by queue and status. 
	- Dialer: Always save start_time when marking a call as ShortCall. Should fix
	  Elastix bug #262.
	- Dialer: Remove per-queue counter of pending calls. This code is prone to get
	  out of sync with the actual count of pending calls. Instead, store queue
	  in call structure and count pending calls that match a given queue.
	- Dialer: Fix assumption that Link and Join events will always occur after
	  the OriginateResponse event. This does not always hold for calls made through
	  the dialplan (Local/XXX@from-internal). Should fix issue of some calls not
	  being detected when using dialplan for campaigns. 
	- Dialer: An incoming call that is transferred should result in the agent being
	  marked as idle in the database, instead of incorrectly keep displaying the
	  information for the transferred call. Fixes Elastix bug #213.
	- Dialer: rename a method to reflect context in which it is used.
	- Agent Console: only build VTiger link if contact information for incoming call
	  actually exists.
	- Agent Console: fix case typo for reference to translated string
	- Agent Console: tweak loading of language files to have English strings as 
	  fallback if no localized string is available
	- Break Administrator: fix reference to nonexistent translation string. Spotted
	  and fixed by Jorge Gutierrez.
	- Agents: detect and fail operation to add an agent if the agent already exists.
	  Should fix Elastix bug #209
	- Agents: remove obsolete parameter from method call that references an 
	  undefined variable.
	- Merge improvements to templates for Campaign Out by Franck Danard
	- Display callerid as incoming number for incoming calls
	- Add more missing strings and synchronize French translations
	- Check that session variable is set before testing if not null.

	1.5-2.1
	- Dialer - do not fill log with notifications about origin of AMI credentials

	- Agents: Merge changes from http://elajonjoli.org/node/25 to provide defined
	  ordering of agent report and reloading just chan_agent, not entire Asterisk,
	  when agent configuration changes. Tracked at Elastix bug #191. 

	- Dialer - fix check for wrong column name that led to assuming nonexistent
	  support for scheduled agents on systems that lack the required column 
	  'calls.agent'.

	- Agents: Major rewrite:
	 - Remove empty directory libs/js
	 - Add missing language strings
	 - Translate Spanish language strings correctly
	 - Rework interface code into separate procedures for each screen
	 - Merge form preparation for new agent and agent modification
	 - Remove dead code from interface and module library
	 - Store database connection in library object as done in other modules
	 - Store message string in library object as done in other modules
	 - Fix use of a session variable instead of input data for agent logoff
	 - Centralize logging into Asterisk AMI into a single procedure
	 - Rework library code to merge parameter validation and actual work code
	   into single procedures.
	 - Replace pattern of copying configuration file into temporary file
	 - Improve interface to place agent removal functionality in main screen
	 - Simplify loading and parsing of agent configuration file
	 - Move filter HTML into separate template 

	1.5-2
	- Agents, Calls Detail, Calls per Agent, Calls per Hour, Campaign Out, 
	  Form Designer, Hold Time, Incoming Calls Success, Login/Logout, Reports Break,
	  : Tweak loading of language files to have English strings as fallback if no 
	  localized string is available
	- Agents: Add missing English language strings
	- Agents: Look for phpagi-asmanager.php in libs/ in addition to /var/lib/asterisk/agi-bin
	- Agents: Make re-loading of Asterisk more robust in case of failure
	- Report - Calls Detail: Initialize a variable
	- Report - Calls Detail: Add missing language string
	- Report - Calls per Agent: Add missing language string
	- Report - Calls per Hour: Fix incorrect index for internationalized strings
	- Report - Hold Time: Actually define internationalized strings that are being 
	  used.
	- Report - Incoming Calls Success: Fix use of undefined variables when no calls
	  are present
	- Report - Login/Logout: Actually define internationalized strings that are 
	  being used.
	- Break Administrator: Actually define internationalized strings that are being 
	  used.
	- Form Designer: Actually define internationalized strings that are being used.
	- Report - Calls Details: Actually define internationalized strings that are 
	  being used.
	- Dialer - Fix bug in which a scheduled agent in pause would receive calls even 
	  when paused.
	- Dialer - Try harder to work around a bug in some Asterisk versions where 
	  agents are reported as busy when they are really free, by manually modifying 
	  the Asterisk database and restarting Asterisk.
	- Dialer - Fix use of undefined variable in some code paths.
	- Dialer - Fix bug in which an agent that belongs to both an incoming and 
	  outgoing campaign will cause outgoing calls to be handled as incoming.
	- Outgoing Campaigns: Major rewrite:
	 - Code cleanup/refactoring to remove duplicated functionality between creation 
	   and modification of a campaign.
	 - Use rawmode for display of CSV data instead of a separate callable PHP script.
	 - Improve usability of New Campaign/Edit Campaign with links to relevant 
	   resource managers.
	 - Display error message instead of form when trying to create an outgoing 
	   campaign without   defining forms or queues, or when all available queues are
	   used by incoming campaigns.
	 - Move out embedded HTML markup for report filter into a separate Smarty 
	   template.
	 - Rework query for campaign CSV data to be more readable.
	 - Rework campaign report to make accessible more of the available 
	   functionality. Now the operations for Deactivate/Delete campaign are show in
	   the report instead of having to use the View link.
	 - Fixed use of undefined localized strings.
	- Graphic calls: Major rewrite:
	 - Remove vim swapfile unwittingly committed into repository
	 - Remove unused template new.tpl and unused richedit library
	 - Remove copy of jpgraph library and redirect references to Elastix embedded
	   jpgraph instead
	 - Rewrite code to remove write of query data to a temporary PHP file, replaced
	   by rawmode and proper query
	 - Refactor code to eliminate repeating code for hour processing
	 - Use SQL with GROUP BY and IF conditionals to replace PHP code that built 
	   histogram from a direct query of calls
	 - Move out HTML code for report filter into a proper template
	 - Fix use of undefined localization strings
	- Dialer - Fixed improper handling of multiple Link events for monitored 
	  incoming calls that lead to temporary incoming call information not being 
	  removed from the database
	- At long last, actually include a CHANGELOG in the tarball ;-)
	- Ingoing Calls Success - remove vim swapfile unwittingly committed into repository

	1.5-svn-branch-1.6
	- Agent Console: Fix up conformance to XHTML in several templates.
	- Add missing translations for strings "Name" and "Retype Password" (Elastix bug
	  #167)
	- Dialer Configuration: Add support for setting Service Percent (97 percent by 
	  default)
	- Report - Calls Detail: Actually use internationalized string for "End Time"
	- Report - Calls per Hour: Actually use internationalized strings for call 
	  states
	- Report - Calls per Hour: Expand French localization strings
	- Outgoing Campaigns: Add library support (not yet exposed in interface) to 
	  leave trunk blank in order to use default Asterisk dialplan through 
	  Local/$OUTNUM$@from-internal
	- Report - Graphic Calls per hour: fix French localization for "Total Calls"
	- Report - Hold Time: Internationalize strings for average wait time
	- Report - Hold Time: Add French localization strings
	- Report - Ingoing Calls Success: Fix French localization strings
	- Report - Login/Logout: Use internationalized strings for report type
	- Report - Login/Logout: Add French localization strings
	- Report - Reports Break: Make code more robust when no break types are defined
	- Report - Reports Break: comment out unused code in library files
	- Dialer - Add support for scheduled calls to specific agents. This code also
	  automatically detects whether the database tables support the functionality.
	- Dialer - Add support for setting the probability used when calculating the
	  odds that a currently-active call will hang up after a certain set time.
	  Previously this value was hardcoded to 97 percent. This parameter is exposed
	  on the web configuration as Service Percent.
	- Dialer - Add code to verify whether the database connection was lost and 
	  attempt to reconnect to the database periodically.
	- Dialer - Add code to figure out the time between originating a call and 
	  linking the call to an agent, and use it for predictive calculations. 
	  Previously this value was hardcoded to 8 seconds.
	- Dialer - Add enter/exit trace code for debugging events
	- Dialer - Record incoming trunk for incoming calls, if database column is 
	  available
	- Dialer - Add code to attempt to eliminate reentrancy on handling of Asterisk 
	  events.
	- Dialer - Remove dead code for normal distribution for call prediction.

	1.5-1
	- Callcenter for Elastix 1.5.x released.

* Tue Aug 25 2009 Bruno Macias <bmacias@palosanto.com> 2.0.0-1
- Initial version.
