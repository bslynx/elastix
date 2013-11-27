%define modname callcenter

Summary: Elastix Call Center 
Name:    elastix-callcenter
Version: 2.1.1
Release: 0
License: GPL
Group:   Applications/System
Source0: %{modname}_%{version}-%{release}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix >= 2.0.0-35
Requires: asterisk
Requires: freePBX
Requires: php-mbstring

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

# Add dialer to startup scripts, and enable it by default
chkconfig --add elastixdialer
chkconfig --level 2345 elastixdialer on

# Fix incorrect permissions left by earlier versions of RPM
chown -R asterisk.asterisk /opt/elastix/dialer

# To update smarty (tpl updates)
rm -rf /var/www/html/var/templates_c/*

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
* Fri Oct 21 2011 Alex Villacis Lasso <a_villacis@palosanto.com> 2.1.1-0
- Updated version, synchronized with CallCenter 1.6.1 (SVN revision 3102)
- From CHANGELOG:
    - Agent Console: perform translation of legacy contact labels for incoming 
      calls into user-friendly values subject to i18n. Fixes Elastix bug #1039. 
    - Dialer (ECCP): extend "getcampaignlist" request to add extra filtering options
    - Dialer (ECCP): fix behavior when an incoming call is put on hold, and then
      is hung up by the remote side.
    - Agent Console: specify explicit font size in CSS to prevent font size from
      varying with the selected theme. Helps fix Elastix bug #1035.
    - Agent Console: rewrite script and header inclusion so that javascript.js 
      from module is included exactly once. Fixes Elastix bug #1036.
    - Dialer (ECCP): allow the "agentlogout" request to cancel a login that is 
      still in progress and asking for the agent numeric password.

* Mon Oct 17 2011 Alex Villacis Lasso <a_villacis@palosanto.com> 2.1.0-0
- Updated version, synchronized with CallCenter 1.6 (SVN revision 3087)
- From CHANGELOG:
    - Agent Console: complete rewrite.
      This version of the Call Center module has a completely rewritten Agent 
      Console based on the Elastix CallCenter Protocol (ECCP). This rewrite is 
      intended to showcase the capabilities of ECCP and become the reference 
      implementation for an ECCP client. Some highlights of the rewrite:
      - The previous Agent Console polled the webserver every 4 seconds for updates
        on the agent state. This polling gets multiplied by the number of simultaneous
        agents and becomes a heavy burden on the server CPU with more than a few
        tens of agents. The new Agent Console switches to Long Polling 
        (http://en.wikipedia.org/wiki/Push_technology#Long_polling) in which the
        browser is left waiting for a response for up to 2 minutes at a time while
        the server listens to state change events with very little CPU usage. This 
        is made possible thanks to ECCP events. Fixes Elastix bug #114, probably 
        fixes Elastix bugs #412, #489, #637.
      - Agent Console now works correctly in Internet Explorer. Tested with IE6, IE8
        and IE9. Fixes Elastix bug #30.
      - The previous console visual layout is now reimplemented using jQueryUI. This 
        introduces niceties such as decent tabs, dialogs with shading, and themed
        buttons, as well as greatly simplifying and reorganizing the JavaScript
        implementation.
      - The previous Agent Console depends on the agent being willing and able to
        close the agent session correctly from within the interface. Failure to do
        this results in corrupted (stale) session and break audit records. This 
        corruption is the probable root cause of Elastix bug #494. The new Agent 
        Console is immune to this failure scenario, since the audit record update
        is now done by the dialer daemon process.
      - The agent audit now properly handles the case where an agent is deactivated
        and reactivated multiple times while keeping the same agent number. Fixes
        Elastix bug #990.
      - As a result of improved handling of the interface state, it is now possible
        to switch to other Elastix modules while the agent is logged in, then switch
        back to the Agent Console, which will display the correct interface state.
        It is even possible to close the browser while handling a call, then log 
        back into Elastix, and choose the agent number and extension previously used,
        and "log-in" back into a correct console session, as long as the agent   
        telephone call is kept open all the time.
      - The Transfer capability is expanded to any arbitrary extension/queue. Partial
        fix for Elastix bug #419.
      As a side effect of the rewrite, may also fix Elastix bugs #879, #796, #414.
    - Dialer (ECCP): implement "getcampaignlist" request
    - Dialer (ECCP): log out an agent immediately if login succeeded but audit 
      record cannot be inserted.
    - Agent Information: fix division by zero on no connection time.
    - Incoming calls monitoring: fix use of undefined array index.
    - Campaigns In: new module to define campaigns for incoming calls 
    - Campaigns Out: include NoAnswer and Abandoned calls in CSV report too.
    - Agents: Do not leave newly created or modified agent without an ECCP password.
      The ECCP password is autogenerated if required. Also, assign an ECCP password
      at install/update time.
    - ECCP Users: new module for administration of ECCP authorized users
    - Dialer (ECCP): fix requests for agents that are not assigned to any queue.
    - Dialer: fix bug in which a request for outgoing calls would repeatedly request
      already retried calls until the retry limit even if calls with lower retry
      numbers were available.
    - Dialer (ECCP): fix bug in which an agent that has just been called Hangup on
      would show as offline instead of online in getagentstatus request.
    - Dialer (ECCP): implement new events "pausestart" and "pauseend".
    - Dialer (ECCP): fix incorrect parameter verification in "getcallinfo" request
    - Dialer (ECCP): implement "getqueuescript" request
    - Dialer (ECCP): the "getcampaigninfo" request has been extended to return 
      additional form attributes "name" and "description" in the <form> tag.
    - CHANGED: module agent_console, verify if function "obtenerClaveAMIAdmin" 
      exists, if not the password is set with "elastix456"
    - CHANGED: changed the password "elastix456" of AMI to the password set in 
      /etc/elastix.conf
    - Dialer (ECCP): implement "filterbyagent" request
    - Dialer (ECCP): Added the following fields to response for "getagentstatus" 
      request: onhold pauseinfo remote_channel callinfo . 
    - Login Logout: fix time format representation for time in calls. Fixes Elastix
      bug #705.
    - Dialer (ECCP): fixed bug that prevented the hold/schedulecall/transfercall
      requirements from working after agent entered a pause while still connected
      to a call.

* Mon Oct 10 2011 Alex Villacis Lasso <a_villacis@palosanto.com>
- Enable elastixdialer service by default.

* Tue Sep 13 2011 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-16
- Updated version, synchronized with CallCenter 1.5-4.2 (SVN revision 2972)
- From CHANGELOG:
1.5-4.2 (SVN revision 2972)
    - Dialer (ECCP): extended "transfercall" request to work with calls connected
      to an agent that were not tracked by the dialer.
    - Dialer (ECCP): fixed bug in which a transfer request of an incoming campaign
      call into an outgoing campaign queue results in a new incoming agentlinked 
      message.
    - Dialer (ECCP): fixed bug in which a transfer request of an incoming campaign
      call into a incoming campaign queue (the same one the call came from or a 
      different one) results in an agentunlinked event with an incomplete agent
      number. 
    - Installer: add section in extensions_custom.conf that will hold required
      callcenter extensions for extra functionality.
    - Dialer (ECCP): implement "schedulecall" request
    - Dialer (ECCP): allow call to be hung up when agent is both busy and paused.
    - Dialer (ECCP): implement "getrequestlist" request
    - Dialer (ECCP): fixed a 200-byte-per-request memory leak on XML response ID 
      assignment.
    - Dialer (ECCP): implement "getcampaignstatus" request
    - Dialer (ECCP): implement "transfercall" request
    - Dialer: write true outgoing channel, not Local@from-internal, to current_calls
      table. Also reuse stored channel at hold time instead of querying the channel
      again from Asterisk. May fix Elastix bug #796.
    - Dialer: remove use of deprecated ereg* functions from Predictivo.class.php
    - Dialer: fixed regular expression for parsing of 'agent show' report that 
      missed DAHDI channels (and possibly other custom channels).
    - Dialer: fix accounting of parked calls (for Hold) in incoming-calls case

* Fri Jul 22 2011 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-15
- Updated version, synchronized with CallCenter 1.5-4.1 (SVN revision 2847)
- From CHANGELOG:
    1.5-4.1 (SVN revision 2847)
    - Dialer (ECCP): Added "extension" and "channel" fields to response for 
      "getagentstatus" request.
    - Agent Console: display error diagnostic messages when pause/unpause fails. 
      Also display attempted hangup channel when Hangup operation failed.
    - Agents, Break Administrator, Campaigns Out: undo use of <button> inside of <a>
      as this combination does not work as intended in Firefox 4.0.1. Fixes Elastix
      bug #864.
    - Agents: implement setting and changing optional ECCP password for agent
    - Agents: remove hardcoded HTML escapings in Spanish translation
    - Agent Console: When attempting to hang an incoming call, do not expose SQL on
      empty recordset. Fixes Elastix bug #841.
    - Agent Console: Remove reference to nonexistent javascript ManejadorCierre()

* Mon May  9 2011 Alex Villacis Lasso <a_villacis@palosanto.com>
- Update trunk version of specfile
- Clear smarty cache on install/update.

* Thu Apr 14 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-14
- Changed the documentation of ECCP.

* Wed Apr 13 2011 Eduardo Cueva <ecueva@palosanto.com> 2.0.0-13
- Agent Console: When programming calls, do not send campaign ID, phone number,
  and client name through the URL. Instead, just send the call ID and read the
  other values from the database. This sidesteps character escaping issues.
- Hold Time: fix warning on old code path for paging
- Break Administrator: fix warning on array_fill() when no breaks are defined.
- Compatibility fix: check if generated HTML for report includes a <form> tag,
  and add it if not present. Required for compatibility with very old Elastix
  versions. Should fix Elastix bug #662
- Agents: use <button> tag to make link appear as a button.
- Campaigns Out: check that multibyte functions exist before trying to use them.
  Fixes Elastix bug #650.
- Calls Detail: complete rewrite. Reorganized library methods now use SQL query
  parameters, have more readable code and a cleaner API, allows simultaneous
  filtering by more criteria, no longer requires fetching of all records to find
  the total number, and fixes several design flaws. Reorganized interface 
  replaces the filtering by up to two criteria with a four-criteria filter with
  drop-down lists for Call Type, Agent, Queues. Should also contain fix for 
  Elastix bug #638.
- Reports Break, Calls Detail, Calls per Agent, Hold Time, Login Logout: do not
  add HTML tags on non-HTML exports when using production versions of Elastix
  framework 2.0.
- Break Administrator: use parametrized SQL queries instead of string 
  concatenation. Remove uses of construirInsert and construirUpdate. Make code
  smaller and add additional parameter validation. Remove unneeded AJAX calls.
  Expose functionality to activate/deactivate breaks in break listing. Use POST
  actions exclusively for modifications.

* Wed Dec  8 2010  Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-12
- Updated version, synchronized with CallCenter 1.5-3.9 (SVN revision 2086)
- From CHANGELOG:
1.5-3.9 (SVN revision 2086)
    - Reports: fix regression introduced by testing existence of incorrect method in
      Elastix 2.

* Tue Dec  7 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-11
- Updated version, synchronized with CallCenter 1.5-3.8 (SVN revision 2084)
- From CHANGELOG:
1.5-3.8 (SVN Revision 2084)
    - Dialer (phpagi-asmanager): fix broken database_get method
    - Hold Time, Login Logout: remove unused HTML templates 
    - Break Administrator, Hold Time: remove dead code.
    - CallCenter Config, Agent Console, Agents, Break Administrator, Calls Detail,
      Calls per Agent, Calls per Hour, Campaign Out, Ingoing Calls Success,
      Login Logout, Agent Information, Agents Connection Time, Trunks Used per Hour,
      Hold Time: Use methods load_language_module and _tr from Elastix framework.
      Define them if they do not exist.
    - Campaigns: improve speed and memory usage of CSV download of completed 
      campaign data by replacing single big SQL query by multiple smaller ones.
      Fixes Elastix bug #600. 
    - Agents, Break Administrator, Calls Detail, Calls per Agent, Calls per Hour,
      Campaign Out, Ingoing Calls Success, Login Logout, Agents Connection Time,
      Trunks Used per Hour: make module aware of url parameter in
      paloSantoGrid::fetchGrid().
    - Agents, Break Administrator, Calls Detail, Calls per Agent, Calls per Hour,
      Campaign Out, Ingoing Calls Success, Login Logout, Agent Information, 
      Hold Time: remove <form> tags from the filter HTML template. They are not
      required, since the template already includes a proper <form> tag enclosing 
      the grid.
    - Agent Console (programar_llamadas): restrict call reprogramming interface to
      requests with a valid Elastix session and authorization to the Agent Console.
      Also, clean up form code to use the default Elastix support for date controls.  
    - Form List: fix breakage from i18n unification and remove useless code
    - Agent Console: move around conditional declaration of getParameter so that
      it is available at the moment it is required.
    - Dialer: Found probable cause of unexpected delays in outgoing calls. Asterisk
      AMI may emit events at any arbitrary frequency. If AMI manages to continuosly 
      emit events with less than 1 second delay, the event loop in the dialer will
      get stuck processing events (even if not interesting) and never dispatch any
      new calls. Fixed by forcing end of event receipt inside wait_response() method 
      in phpagi-asmanager-elastix.php. Might fix Elastix bugs #543, #577.
    - Web interface: cleanup of i18n files to bring them in line with the rest of 
      the Elastix code.

* Fri Dec  3 2010 Alex Villacis Lasso <a_villacis@palosanto.com>
- FIXED: Add Requires for asterisk and freePBX for more modular installation

* Mon Oct 18 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-10
- Requires: elastix-2.0.0-35 or later, for generic-cloexec
- Updated version, synchronized with CallCenter 1.5-3.7 (SVN revision 1843)
- From CHANGELOG:
1.5-3.7 (SVN revision 1843)
    - Dialer: Add debug notification when record has just been inserted in 
      current_calls. Should help debug unexplained delays of call notifications in
      agent console.
    - Dialer: Asterisk 1.6.x expects context variables separated by commas, not 
      pipes. Should fix Elastix bug #558.
    - Dialer: asterisk 1.6.2.x emits Dial event with changed property name. Account
      for the difference in order to prevent access to undefined property.
    - Reports (Agent Information): behave properly when no queues have been defined.
    - Agent Console: protect private declaration of getParameter() in call 
      programming so that it does not conflict with Elastix 2 framework. Fixes
      Elastix bug #478.
    - Trunks Used per Hour: fix over-complicated query for active trunks from 
      FreePBX database that resulted in misnamed trunk sources. Should fix Elastix
      bug #499.
    - Campaigns Out: set array variables holding recordsets to NULL as soon as 
      possible in the CSV download codepath. This reduces memory usage and allows
      processing of a larger number of call records without exceeding the PHP
      memory limit. Also, for the same purpose, replace superfluous array 
      assignment via loop, with direct recordset assignment.

* Mon Aug 23 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-9
- Updated version, synchronized with CallCenter 1.5-3.6 (SVN revision 1713)
- From CHANGELOG:
1.5-3.6 (SVN revision 1713)
    - Configuration: detect and use generic-cloexec if available. Should fix issue
      of httpd failing to restart due to dialer process grabbing HTTP[S] ports
      as in Elastix bug #425. If generic-cloexec is not available, this has no 
      effect.
    - Dialer: remove broken "reload" verb support from init script. Should fix 
      Elastix bug #434.
    - Reports (Agent Connection Time, Agent Information, Trunks Used per Hour):
      remove hardcoded freePBX database credentials, and instead parse them from
      /etc/amportal.conf .
    - Dialer: with low-quality phone number databases with lots of repeated 
      numbers, the generation of a call with the same number as one already
      being originated or monitored will confuse the dialer and mix up events.
      So skip over calls that duplicate calls already originated/in progress.  
    - Dialer: if the AMI reports an OriginateResponse with a Success status, but
      the channel (or an auxiliary channel) has seen a Hangup, treat the call as
      a failure instead. 
    - Dialer: when receiving a Link event before an OriginateResponse, do not wait
      until the OriginateResponse if the Uniqueid is known. Instead, fake an 
      OriginateResponse event to handle the call as soon as possible. 
    - Dialer: prevent event re-entrancy when originating outgoing calls and pausing
      queue agents.
    - Dialer: document more possible re-entrancy points where nested event handling
      could happen.
    - Dialer: add debugging function to dump list of current_calls to log
    - Dialer: phpagi-asmanager-elastix.php: factor out handling of queued events 
      into a separate function, and add events to the queue, not only on reentrancy,
      but also when the queue is non-empty. This ensures that any events already in the
      queue when entering wait_response() will be dispatched before any new events
      that were picked up on the event loop.  
    - Calls Detail: fix broken chronological ordering of call records. Spotted while
      fixing Elastix bug #373.
    - Calls Detail: when filtering by phone number, the SELECT statement for incoming
      calls failed to take into account that there might be no contact available for
      a given incoming call, but the Caller ID was available anyway. This resulted in
      missing incoming calls when filtering by telephone. Fixed. Should fix Elastix 
      bug #373.

* Mon Jun 21 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-8
- Updated version, synchronized with CallCenter 1.5-3.5 (SVN revision 1563)
- From CHANGELOG:
1.5-3.5 (SVN revision 1563)
    - Dialer: add and use new column to record timestamp of Originate execution for
      call. This is needed for upcoming support for outgoing monitoring 
      information. 
    - Reports Break: rewrite introduced a regression in date select that prevented
      data from being fetched if the start and end date are the same. Fixed. Should
      fix Elastix bug #360.
    - Dialer: fix accessing of undefined Uniqueid variable in OnDial event.
    - Agent Console: recognize call attributes that are HTTP URLs, and display them
      as hyperlinks.
    - Dialer: merge fix to prevent stale pidfile that happens to match an active 
      process from making the daemon hang on startup. See Elastix bug #356. 
    - Agent Console: add interface for scheduling of calls to particular agents. 
      This support requires a specific context to work properly.

* Fri Jun 11 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-7
- Updated version, synchronized with CallCenter 1.5-3.4
- From CHANGELOG:
    1.5-3.4
    - Installer: The ALTER TABLE embedded in the middle of the SQL script to create
      the database will not work for creating the complete database in the clean
      install case. The script stops processing on the first error, so the remainder
      of the tables are not created. Embedding the creation in a stored procedure
      works in Elastix 2.0 but not in Elastix 1.6 due to Elastix bug #71. This makes
      it necessary to duplicate the schema check and update in PHP code.

* Tue Jun 08 2010 Alex Villacis Lasso <a_villacis@palosanto.com> 2.0.0-6
- Updated version, synchronized with CallCenter 1.5-3.3
- From CHANGELOG:
    1.5-3.3
    - Clients: implement download of existing contact information.
    - Clients: rewrite file upload support to resemble campaign upload code, and 
      detect charset encoding. Intended to fix Elastix bug #334.
    - Agent Console,Campaign Out: remove HTML codes from French translations, as 
      they seep into CSV export output. Fix #2 for Elastix bug #325.
    - Forms Designer: fix default selection of field type. Should prevent instances
      of Elastix bug #206.
    - Calls per Hour: replaced implementation with newest implementation of Graphic
      Calls. Originally (deduced via diff) Graphic Calls was a fork of Calls per
      Hour, and it inherited major flaws. The fixed Graphics Calls implementation
      is now folded back into Calls per Hour. Should fix Elastix bug #336.
    - Campaigns Out: attempt to detect character encoding of uploaded CSV file in
      order to always store UTF-8 data in the database. Currently detects UTF-8
      and ISO-8859-15. Should fix Elastix bug #325.
    - Agent Information: (1/2) Replace questionable UNION statement that tries to
      fetch everything at once with three distinct SELECTs. The UNION mixed 
      different datatypes in several columns, resulting in a mangling of UTF-8 
      encoding for break names. This mangling required a workaround in the view 
      via utf8_decode(), which did not work at all with characters outside latin-1.
      The mangling and the utf8_decode() workaround are no longer necessary.
      (2/2) Rewrite report to use the new data structure returned by (1/2). Remove
      dead code from (1/2). Fix CSV export to not insert HTML tags in download. 
    - Agent Console: fix issue in which a session or break is incorrectly marked as
      belonging to an inactive agent with the same agent number as the current 
      active agent. Should fix Elastix bug #329.
    - Dialer: monitor additional instances of Uniqueid that can be associated with
      a given call and might hold additional call failure information in their
      Hangup events.
    - Reports Break: complete rewrite. Replace inefficient time lookup, and fix
      no-data issue on CSV export, as well as HTML tags in CSV export. Should fix
      Elastix bug #324. 
    - Dialer: add support for reopening of logs when receiving SIGHUP, and 
      implement a corresponding logrotate directive to make use of this.
    - Dialer: record hangup cause code and description for a failed outgoing call
      Currently implemented only for calls sent through default dialplan.
    - Dialer: verify that enterqueue_timestamp is set
    - Campaigns Out: fix warning on line that start with a comma
    - Agent Console: verify that $_SESSION['elastix_agent_audit'] is set
    - Agent Console: report most causes of "spontaneous" agent disconnection
    - Agent Console: implement ability to save form information after call is
      disconnected. To use properly, the Wrapuptime parameter in the queue must
      be set to an appropriate value.
    - Agent Console: fix reference to string without translation
    - Agent Console: fix incorrect javascript in time counter reset
    - Agent Console: fix function call with insufficient parameters
    - Calls Details: remove reference to not-used status variable, including use
      of undefined $_POST index.

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