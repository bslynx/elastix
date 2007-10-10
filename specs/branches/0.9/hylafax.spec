%define name        hylafax
#%define version     4.4.2
%define version     4.3.3
#
## OS version detection
##
%define is_rh %(test -e /etc/redhat-release && echo 1 || echo 0)
%define is_fc %(test -e /etc/fedora-release && echo 1 || echo 0)
%define is_suse %(test -e /etc/SuSE-release && echo 1 || echo 0)

# Some RPM builders will try to expand defines in block they won't execute
# or fail on nested blocks. That's why we define *_version to 0 for other
# distributions and didn't use nested %ifs.

%if %{is_rh}
%define rh_version %(rpm -q --queryformat='%{VERSION}' -f /etc/redhat-release | sed - -e 's/\\([0-9]*\\).*/\\1/')
%else
%define rh_version 0
%endif

%if %{rh_version} > 0 && %{rh_version} < 7
%define ostag rhel%{rh_version}
%endif
%if %{rh_version} >= 7
%define ostag rh%{rh_version}
%endif

%if %{is_fc}
%define ostag fc%(rpm -q --queryformat='%{VERSION}' fedora-release)
%endif


%if %{is_suse}
%define initdir     /etc/init.d
%else
%define sles_version 0
%define suse_version 0
%define initdir     /etc/rc.d/init.d
%endif

%if %{suse_version} > 0
%define ostag suse%(echo %{suse_version} | sed - -e 's/\\([0-9]*\\)[0-9].*/\\1/')
%endif
%if %{sles_version} > 0
%define ostag sles%{sles_version}
%endif

%define release 2%{ostag}
%define htmldoc_rpm 0
%define serial      %(echo `date +%Y%m%d`)

%define faxspool    %{_var}/spool/hylafax

Summary:   HylaFAX(tm) is a sophisticated enterprise strength fax package
Name:	   %{name}
Version:   %{version}
Release:   %{release}
Epoch:     %{serial}
License:   better than LGPL
Group:     Applications/Communications
Packager:  Darren Nickerson <darren.nickerson@ifax.com>
URL:       http://www.ifax.com/

Source:    ftp://ftp.hylafax.org/source/%{name}-%{version}.tar.gz
Source1:   hylafax_init
Source2:   hylafax_config
Source3:   hylafax_config.modem
Source4:   hylafax_setup.cache
Source5:   hylafax_setup.modem
Source6:   hylafax_daily.cron
Source7:   hylafax_hourly.cron
Source8:   hylafax_logrotate
Source9:   hylafax_README.rpm
Source10:  hylafax_hyla.conf
Source11:  hylafax_FaxDispatch
Source12:  hylafax_jobcontrol.sh
Source13:  hylafax_sysconfig
Source14:  hylafax-4.3.3-elastix.tar.gz

BuildPrereq: libjpeg-devel, libtiff-devel, zlib-devel
Requires:    ghostscript >= 5.5
Requires:    libtiff >= 3.5.5
%if %{is_suse}
Requires:    tiff >= 3.5.5
%endif
Requires:    gawk
Requires:    rpm >= 3.0.5
Requires:    sharutils
Conflicts:   mgetty-sendfax

BuildRoot: %{_tmppath}/%{name}-root

%description
HylaFAX(tm) is a sophisticated enterprise-strength fax package for 
class 1 and 2 fax modems on unix systems. It provides spooling
services and numerous supporting fax management tools. 
The fax clients may reside on machines different from the server
and client implementations exist for a number of platforms including 
windows.

%if %{htmldoc_rpm}
%package htmldoc
Summary: Documentation in HTML format for the HylaFAX fax server package.
Group:   Applications/Communications

%description htmldoc
This package contains documentation in HTML format for the HylaFAX
fax server package. It explains in detail how to build, configure 
and run the HylaFAX server.
%endif

%prep
%setup -q

%build
# - Can't use the configure macro because HylaFAX configure script does
#   not understand the config options used by that macro
# - --with-HTML is 'no' because the html dir is taken by the doc macro
./configure \
	--with-DIR_BIN=%{_bindir} \
	--with-DIR_SBIN=%{_sbindir} \
	--with-DIR_LIB=%{_libdir} \
	--with-DIR_LIBEXEC=%{_sbindir} \
	--with-DIR_LIBDATA=%{_sysconfdir}/hylafax \
	--with-DIR_LOCKS=%{_var}/lock \
	--with-LIBDIR=%{_libdir} \
	--with-TIFFBIN=%{_bindir} \
	--with-DIR_MAN=%{_mandir} \
	--with-PATH_GSRIP=%{_bindir}/gs \
	--with-DBLIBINC=%{_includedir} \
	--with-LIBTIFF="-ltiff -ljpeg -lz" \
	--with-DIR_SPOOL=%{faxspool} \
	--with-AFM=no \
	--with-AWK=/usr/bin/gawk \
	--with-PATH_VGETTY=/sbin/vgetty \
	--with-PATH_GETTY=/sbin/mgetty \
	--with-HTML=no \
	--with-PAGESIZE=A4 \
	--with-PATH_DPSRIP=%{faxspool}/bin/ps2fax \
	--with-PATH_IMPRIP="" \
	--with-SYSVINIT=%{initdir}/hylafax \
	--with-INTERACTIVE=no

# CFLAGS is set up by the HylaFAX configure script; setting it up here the
# standard way would break things. Since OPTIMIZER is included in CFLAGS
# by the HylaFAX configure system, it's used here in place of CFLAGS
#make CFLAGS="$RPM_OPT_FLAGS"
make OPTIMIZER="$RPM_OPT_FLAGS"

%install
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

# install: make some dirs...
mkdir -p -m 755 $RPM_BUILD_ROOT%{_sysconfdir}/{cron.daily,cron.hourly,logrotate.d} 
mkdir -p -m 755 $RPM_BUILD_ROOT%{_sysconfdir}/sysconfig
mkdir -p -m 755 $RPM_BUILD_ROOT%{_sysconfdir}/hylafax
mkdir -p -m 755 $RPM_BUILD_ROOT%{initdir}
mkdir -p -m 755 $RPM_BUILD_ROOT%{_bindir}
mkdir -p -m 755 $RPM_BUILD_ROOT%{_sbindir}
mkdir -p -m 755 $RPM_BUILD_ROOT%{_libdir}
mkdir -p -m 755 $RPM_BUILD_ROOT%{_mandir}
mkdir -p -m 755 $RPM_BUILD_ROOT%{faxspool}/config/defaults

# install: binaries and man pages 
# FAXUSER, FAXGROUP, SYSUSER and SYSGROUP are set to the current user to
# avoid warnings about chown/chgrp if the user building the SRPM is not root; 
# they are set to the correct values with the RPM attr macro
%makeinstall -e \
	FAXUSER=`id -u` \
	FAXGROUP=`id -g` \
	SYSUSER=`id -u` \
	SYSGROUP=`id -g` \
	BIN=$RPM_BUILD_ROOT%{_bindir} \
	SBIN=$RPM_BUILD_ROOT%{_sbindir} \
	LIBDIR=$RPM_BUILD_ROOT%{_libdir} \
	LIBDATA=$RPM_BUILD_ROOT%{_sysconfdir}/hylafax \
	LIBEXEC=$RPM_BUILD_ROOT%{_sbindir} \
	SPOOL=$RPM_BUILD_ROOT%{faxspool} \
	MAN=$RPM_BUILD_ROOT%{_mandir} \
	INSTALL_ROOT=$RPM_BUILD_ROOT

# Starting from 4.1.6, port/install.sh won't chown/chmod anymore if the current
# user is not root; instead a file root.sh is created with chown/chmod inside.
# 
# If you build the rpm as normal user (not root) you get an rpm with all the
# permissions messed up and hylafax will give various weird errors.
#
# The following line fixes that.
#
[ -f root.sh ] && sh root.sh

# install: remaining files
install -m 755 %{SOURCE1} $RPM_BUILD_ROOT%{initdir}/hylafax
install -m 644 %{SOURCE2} $RPM_BUILD_ROOT%{faxspool}/config/defaults/config
install -m 644 %{SOURCE3} $RPM_BUILD_ROOT%{faxspool}/config/defaults/config.modem
install -m 644 %{SOURCE4} $RPM_BUILD_ROOT%{faxspool}/config/defaults/setup.cache
install -m 644 %{SOURCE5} $RPM_BUILD_ROOT%{faxspool}/config/defaults/setup.modem
install -m 755 %{SOURCE6} $RPM_BUILD_ROOT%{_sysconfdir}/cron.daily/hylafax
install -m 755 %{SOURCE7} $RPM_BUILD_ROOT%{_sysconfdir}/cron.hourly/hylafax
install -m 644 %{SOURCE8} $RPM_BUILD_ROOT%{_sysconfdir}/logrotate.d/hylafax
install -m 644 %{SOURCE9} ./README.rpm
install -m 644 %{SOURCE10} $RPM_BUILD_ROOT%{_sysconfdir}/hylafax/hyla.conf
install -m 644 %{SOURCE11} $RPM_BUILD_ROOT%{faxspool}/etc/FaxDispatch
install -m 644 %{SOURCE12} $RPM_BUILD_ROOT%{faxspool}/bin/jobcontrol.sh
install -m 644 %{SOURCE13} $RPM_BUILD_ROOT%{_sysconfdir}/sysconfig/hylafax

# some symlinks
ln -s ../..%{faxspool}/etc $RPM_BUILD_ROOT%{_sysconfdir}/hylafax/etc
ln -s ../..%{faxspool}/log $RPM_BUILD_ROOT%{_sysconfdir}/hylafax/log
ln -s ps2fax.gs  $RPM_BUILD_ROOT%{faxspool}/bin/ps2fax
ln -s pdf2fax.gs $RPM_BUILD_ROOT%{faxspool}/bin/pdf2fax

# Prepare docdir by removing non-doc files
find ./html -type d -name CVS | xargs rm -rf
rm -f ./html/{.cvsignore,Makefile.in}
rm -rf ./html/tools
# Remove files that are not needed on Linux
rm -f $RPM_BUILD_ROOT%{_sbindir}/{faxsetup.irix,faxsetup.bsdi}
rm -f $RPM_BUILD_ROOT%{faxspool}/bin/{ps2fax.imp,ps2fax.dps}
# avoid rpm 4.x errors about files in buildroot but not in file list
rm -f $RPM_BUILD_ROOT%{faxspool}/etc/xferfaxlog 
rm -f $RPM_BUILD_ROOT%{faxspool}/COPYRIGHT

# Some specific changes for Elastix
tar -xvzf %{SOURCE14} 
cd %{name}-%{version}-elastix
mv includes/ $RPM_BUILD_ROOT%{faxspool}/bin/
mv $RPM_BUILD_ROOT%{faxspool}/bin/faxrcvd $RPM_BUILD_ROOT%{faxspool}/bin/faxrcvd.old
mv faxrcvd $RPM_BUILD_ROOT%{faxspool}/bin/faxrcvd
chmod -R 755 $RPM_BUILD_ROOT%{faxspool}/bin/includes
chmod -R 755 $RPM_BUILD_ROOT%{faxspool}/bin/faxrcvd
chown root:root $RPM_BUILD_ROOT%{faxspool}/bin/includes
chown root:root  $RPM_BUILD_ROOT%{faxspool}/bin/faxrcvd


%clean
[ "$RPM_BUILD_ROOT" != "/" ] && rm -rf $RPM_BUILD_ROOT

%pre
# if necessary (upgrading from < 4.1rc1) move spool dir to the new location
# and live with rpm errors :)
if [ -f /var/spool/fax/etc/setup.cache -a ! -d %{faxspool} ]; then
    if [ -f /var/lock/subsys/hylafax ]; then
        /sbin/service hylafax stop 1>/dev/null 2>&1 || :
    fi
    mv /var/spool/fax /var/spool/hylafax
fi

%post
if [ -x /usr/lib/lsb/install_initd ]; then
    /usr/lib/lsb/install_initd %{initdir}/hylafax
elif [ -x /sbin/chkconfig ]; then
    /sbin/chkconfig --add hylafax
else
   for i in 3 4 5; do
        ln -sf %{initdir}/hylafax /etc/rc.d/rc${i}.d/S95hylafax
   done
   for i in 0 1 2 6; do
        ln -sf %{initdir}/hylafax /etc/rc.d/rc${i}.d/K05hylafax
   done
fi
/sbin/ldconfig

echo "#########################################################"
echo "#            HylaFAX installation complete!             #"
echo "#                                                       #"
echo "#      You should now run /usr/sbin/faxsetup to         #"
echo "#       create or update HylaFAX configuration          #"
echo "#      before you can begin using the software.         #"
echo "#                                                       #"
echo "#########################################################"

%preun
if [ $1 = 0 ] ; then
    if [ -x /usr/lib/lsb/remove_initd ]; then
        /usr/lib/lsb/install_initd %{initdir}/hylafax
    elif [ -x /sbin/chkconfig ]; then
        /sbin/chkconfig --del hylafax
    else
        rm -f /etc/rc.d/rc?.d/???hylafax
    fi
    /sbin/service hylafax stop >/dev/null 2>&1 || :
fi

%postun
/sbin/ldconfig
if [ "$1" -ge "1" ]; then
	/sbin/service hylafax condrestart >/dev/null 2>&1 || :
fi


%if %{htmldoc_rpm}
%files htmldoc
%defattr(-,root,root)
%doc html
%endif

%files
%defattr(-,root,root)
%if %{htmldoc_rpm}
%doc CHANGES CONTRIBUTORS COPYRIGHT INSTALL README TODO VERSION README.rpm
%else
%doc CHANGES CONTRIBUTORS COPYRIGHT INSTALL README TODO VERSION README.rpm html
%endif
%attr(755,root,root) %config(noreplace) %{initdir}/hylafax
%attr(644,root,root) %config(noreplace) %{_sysconfdir}/sysconfig/hylafax
%attr(644,root,root) %config(noreplace) %{_sysconfdir}/logrotate.d/hylafax
%attr(755,root,root) %config(noreplace) %{_sysconfdir}/cron.daily/hylafax
%attr(755,root,root) %config(noreplace) %{_sysconfdir}/cron.hourly/hylafax
%{_bindir}/*
%{_sbindir}/*
%{_libdir}/*
%attr(644,root,root) %{_mandir}/*/*
%attr(755,root,root) %dir %{_sysconfdir}/hylafax
%attr(644,root,root) %{_sysconfdir}/hylafax/faxcover_example_sgi.ps
%attr(644,root,root) %config(noreplace) %{_sysconfdir}/hylafax/faxcover.ps
%attr(644,root,root) %config(noreplace) %{_sysconfdir}/hylafax/faxmail.ps
%attr(644,root,root) %config(noreplace) %{_sysconfdir}/hylafax/hfaxd.conf
%attr(644,root,root) %config(noreplace) %{_sysconfdir}/hylafax/hyla.conf
%attr(644,root,root) %config(noreplace) %{_sysconfdir}/hylafax/pagesizes
%attr(644,root,root) %config(noreplace) %{_sysconfdir}/hylafax/typerules
%attr(-,root,root) %{_sysconfdir}/hylafax/etc
%attr(-,root,root) %{_sysconfdir}/hylafax/log
%attr(-,uucp,uucp) %dir %{faxspool}
%attr(-,uucp,uucp) %dir %{faxspool}/archive
%attr(-,uucp,uucp) %dir %{faxspool}/bin
%attr(-,uucp,uucp) %dir %{faxspool}/client
%attr(-,uucp,uucp) %dir %{faxspool}/config
#%attr(-,uucp,uucp) %dir %{faxspool}/config/defaults
%attr(-,uucp,uucp) %dir %{faxspool}/dev
%attr(-,uucp,uucp) %dir %{faxspool}/docq
%attr(-,uucp,uucp) %dir %{faxspool}/doneq
%attr(-,uucp,uucp) %dir %{faxspool}/etc
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates
%attr(644,root,root) %{faxspool}/etc/templates/README
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/en
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/es
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/de
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/pt_BR
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/pl
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/pt
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/it
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/ro
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/fr
%attr(-,uucp,uucp) %dir %{faxspool}/etc/templates/html-sample1
%attr(644,root,root) %config(noreplace) %{faxspool}/etc/templates/*/*
%attr(-,uucp,uucp) %dir %{faxspool}/info
%attr(-,uucp,uucp) %dir %{faxspool}/log
%attr(-,uucp,uucp) %dir %{faxspool}/pollq
%attr(-,uucp,uucp) %dir %{faxspool}/recvq
%attr(-,uucp,uucp) %dir %{faxspool}/sendq
%attr(-,uucp,uucp) %dir %{faxspool}/status
%attr(-,uucp,uucp) %dir %{faxspool}/tmp
%attr(-,root,root) %{faxspool}/bin/*
%attr(-,root,root) %{faxspool}/config/*
%attr(-,root,root) %{faxspool}/etc/dpsprinter.ps
%attr(-,root,root) %{faxspool}/etc/cover.templ
%attr(-,root,root) %config(noreplace) %{faxspool}/etc/dialrules*
%attr(-,uucp,uucp) %{faxspool}/etc/lutRS18.pcf
%attr(-,uucp,uucp) %config(noreplace) %{faxspool}/etc/hosts.hfaxd
%attr(-,uucp,uucp) %config(noreplace) %{faxspool}/etc/FaxDispatch
%attr(-,uucp,uucp) %{faxspool}/FIFO


%changelog
* Tue Oct  9 2007 Edgar Landivar <elandivar@palosanto.com> 4.3.3-2
  - changes for Elastix distro. 

* Fri Mar 02 2007 Patrice Fournier <patrice.fournier@ifax.com> 4.3.3-1
  - update to official 4.3.3 release

* Fri Feb 23 2007 Patrice Fournier <patrice.fournier@ifax.com> 4.3.2-3
  - Add requirement for tiff package under SuSE

* Fri Feb 23 2007 Patrice Fournier <patrice.fournier@ifax.com> 4.3.2-2
  - [bug 849] notify "rejected" templates are wrongly called notify-reject.txt
  - [bug 850] adds jobtag to notification mails

* Fri Feb 16 2007 Patrice Fournier <patrice.fournier@ifax.com> 4.3.2-1
  - update to official 4.3.2 release

* Mon Jan 29 2007 Darren Nickerson <darren.nickerson@ifax.com> 4.3.2rc1-1
  - update to first release candidate of 4.3.2

* Mon Jan 29 2007 Darren Nickerson <darren.nickerson@ifax.com> 4.3.2beta2-1
  - update to second beta of 4.3.2

* Mon Jan 29 2007 Darren Nickerson <darren.nickerson@ifax.com> 4.3.2beta1-1
  - update to first beta of 4.3.2

* Mon Dec 04 2006 Darren Nickerson <darren.nickerson@ifax.com> 4.3.1-1
  - update to offical 4.3.1 release

* Wed Nov 22 2006 Darren Nickerson <darren.nickerson@ifax.com> 4.3.1rc3-1
  - update to third release candidate of 4.3.1

* Tue Nov 14 2006 Darren Nickerson <darren.nickerson@ifax.com> 4.3.1rc2-1
  - update to second release candidate of 4.3.1

* Fri Oct 27 2006 Patrice Fournier <patrice.fournier@ifax.com> 4.3.1rc1-1
  - update to first release candidate of 4.3.1

* Tue Oct 12 2006 Darren Nickerson <darren.nickerson@ifax.com> 4.3.1beta4-1
  - update to 4.3.1beta4

* Fri May 26 2006 Patrice Fournier <patrice.fournier@ifax.com> 4.3.0-2
  - [Bug 775] Don't try to start two instances of hfaxd (Simon Matter)
  - [Bug 776] sysconfig file doesn't need exec permissions (Simon Matter)

* Mon May 22 2006 Darren Nickerson <darren.nickerson@ifax.com> 4.3.0-1
  - update to official 4.3.0 release

* Fri May 12 2006 Patrice Fournier <patrice.fournier@ifax.com> 4.3.0rc3-1
  - update to third release candidate of 4.3.0

* Fri Apr 28 2006 Patrice Fournier <patrice.fournier@ifax.com> 4.3.0rc2-1
  - update to second release candidate of 4.3.0

* Fri Apr 21 2006 Patrice Fournier <patrice.fournier@ifax.com> 4.3.0rc1-1
  - update to first release candidate of 4.3.0
  - Added Sample jobcontrol script
  - Stop logrotate from returning an error when ran before HylaFAX first
    start (Simon Matter)
  - [Bug 766] Replaced obsolete Serial tag with Epoch (Dimitris)
  - HylaFAX init script can now be configured in /etc/sysconfig/hylafax
    (fixes bug 652)

* Thu Jan 12 2006 Darren Nickerson <darren.nickerson@ifax.com> 4.2.5-1
  - update to official 4.2.5 release
  - updated urls in README.rpm

* Fri Jan 6 2006 Patrice Fournier <patrice.fournier@ifax.com> 4.2.4-1
  - update to official 4.2.4 release

* Tue Nov 15 2005 Darren Nickerson <darren.nickerson@ifax.com> 4.2.3-1
  - update to official 4.2.3 release

* Fri Nov 11 2005 Patrice Fournier <patrice.fournier@ifax.com> 4.2.3rc1-2
  - only run faxcron when HylaFAX has been setup (Simon Matter)
  - fixed RPM file naming on RedHat 7 (broken since 4.2.2rc1-2)
  - put hylafax init script in the right place on SuSE
  - fixed init script for SuSE
  - Correctly differentiate between SLES and regular SuSE

* Fri Nov 9 2005 Darren Nickerson <darren.nickerson@ifax.com> 4.2.3rc1-1
  - update to first release candidate of 4.2.3

* Fri Sep 23 2005 Patrice Fournier <patrice.fournier@ifax.com> 4.2.2-1
  - update to official 4.2.2 release

* Fri Sep 16 2005 Patrice Fournier <patrice.fournier@ifax.com> 4.2.2rc1-2
  - added SuSE support to SPECS file
  - now distinguish between RH and RHEL versions (using version number)

* Fri Sep 2 2005 Patrice Fournier <patrice.fournier@ifax.com> 4.2.2rc1-1
  - update to first release candidate of 4.2.2
  - updated installation complete message

* Tue Jan 11 2005 Darren Nickerson <darren.nickerson@ifax.com> 4.2.1-1
  - update to official 4.2.1 release
  - [Bug 617] remove unnecessary debug logging (iFAX Solutions)
  - [Bug 574] faxsetup cleanups (Lee Howard)
  - [Bug 118] Improve EOM handling in batched faxes (Lee Howard)
  - improve previously broken digi config (Lee Howard) 
  - improve redhat release detection and RPM file naming

* Thu Jul 15 2004 Darren Nickerson <darren.nickerson@ifax.com> 4.2.0-1
  - update to official 4.2.0 release

* Thu Jul 15 2004 Darren Nickerson <darren.nickerson@ifax.com> 4.2.0rc2-1
  - update to second release candidate of 4.2.0

* Mon May 10 2004 Darren Nickerson <darren.nickerson@ifax.com> 4.2.0beta2-1
  - update to second beta release of 4.2.0

* Fri Apr 16 2004 Darren Nickerson <darren.nickerson@ifax.com> 4.2.0beta1-1
  - update to first beta release of 4.2.0

* Sun Dec 14 2003 Darren Nickerson <darren.nickerson@ifax.com> 4.1.8-2
  - [Bug 435] tiffcheck does not properly implement "-3" option
    (Kevin Fleming)
  - [Bug 436] tiffcheck does not suppress libtiff warnings
    (Kevin Fleming)

* Fri Oct 10 2003 Darren Nickerson <darren.nickerson@ifax.com> 4.1.8-1
  - update to official 4.1.8 release
  - [Bug 468] Fix remotely executable format string vulnerability in hfaxd
    (Sebastian Krahmer and the SuSE Security Team)

* Fri Oct 10 2003 Darren Nickerson <darren.nickerson@ifax.com> 4.1.7-2
  - [Bug 443] Expand & unify sequence namespace (iFAX Solutions)
  - [Bug 445] Corrected a long-standing problem that would leave old image
     files in docq/ causing them to be sent again many months later in
     place of the new image file (iFAX Solutions)
  - [Bug 454] Faxgetty could hear more than 1 ring at once, doubling
     things like CIDNAME and CIDNUMBER, and breaking inbound fax
     routing (iFAX Solutions)
  - [Bug 424] Updated, slightly cleaner patch. Functionally identical.

* Sun Jun 15 2003 Darren Nickerson <darren.nickerson@ifax.com> 4.1.7-1
  - update to official 4.1.7 release
  - add metamail dependency
  - [Bug 420] Add SaveUnconfirmedPages config option
    (Lee Howard)
  - [Bug 427] Fix problem with port/install.sh on non-root builds
    (Giulio Orsero)
  - [Bug 424] Add support for tracking CIDName and CIDNumber in
    xferfaxlog, tiff files, and client/server protocol via hfaxd
    (iFAX Solutions)

* Sun Jun 15 2003 Darren Nickerson <darren.nickerson@ifax.com> 4.1.6-1
  - update to official 4.1.6 release
  - [Bug 410] Added sharutils dependency, and default FaxDispatch
  - [Bug 407] Remove COPYRIGHT (packaged in %doc) and empty xferfaxlog
    files from source tree before packaging.

* Mon Oct 21 2002 Darren Nickerson <darren@hylafax.org> 4.1.5-1
  - update to official 4.1.5 release

* Wed Oct 16 2002 Darren Nickerson <darren@hylafax.org> 4.1.4-1
  - update to official 4.1.4 release

* Sun Jul 28 2002 Darren Nickerson <darren@hylafax.org> 4.1.3-1
  - update to official 4.1.3 release
  - added --with-LIBDIR=%{_libdir} to configure invocation (Lee Howard)

* Sun Mar 17 2002 Darren Nickerson <darren@dazza.org> 4.1.1-2
  - [Bug 160] fix segfault in faxqclean under heavy load

* Sun Feb 17 2002 Darren Nickerson <darren@dazza.org> 4.1.1-1
  - update to official 4.1.1 release

* Sun Feb 10 2002 Darren Nickerson <darren@dazza.org>
  - [Bug 156] Faxquit may not work after an upgrade - init script
    now uses killall. (Giulio Orsero)
  - [Bug 188] Roll-up of various fixes (Giulio Orsero)
	1. removed 'sed' from hylafax_logrotate and similar files, which
	   caused confusion and some missed /var/spool/fax ->
           /var/spool/hylafax corrections.
	2. removed 2 unnecessary files on linux: ps2fax.dps and ps2fax.imp
	3. set IMPRIP to blank since it does not exist on linux
	4. marked all %config as noreplace
	5. Changed "Copyright:" to "License:"
	6. Changed serial to date +%Y%m%d
	7. Linux DSO support has been merged into CVS. Removed patch
 	   and modified .spec accordingly
	8. Restore CFLAGS to RPM default
  - [Bug 189] FHS compliance - changed /usr/share/fax -> /etc/hylafax
    (see http://www.pathname.com/fhs/2.0/fhs-4.8.html) (Giulio Orsero)
  - [Bug 196] Updated stale source files, commented hylafax_hyla.conf
    (Giulio Orsero - spotting a trend here?)
  - [Bug 206] Init script activates SNPP support if pagermap file
    exists (Matthew Rice)


* Sun Jul 01 2001 Darren Nickerson <darren@dazza.org>
  - [Bug 132] Added Conflicts: to avoid confusion with mgetty-sendfax
  - [Bug 145] Added BuildPrereq: zlib-devel, since it's necessary
  - [Bug 156] Init script now restarts faxgetty also
  - [Bug 133] Set symlink for pdf2fax, added new docs to the %doc macro,
    add intelligence to set spooldir accordingly, clarify comment for
    %build macro, migrate /var/spool/fax to /var/spool/hylafax if this
    is an upgrade, and add serial number to make versioning know 4.1 is
    newer than 4.1beta3 and 4.1rc2. Thanks Giulio!!
  - added --with-DIR_LIB=%{_libdir} to configure invocation

* Sun Apr 15 2001 Darren Nickerson <darren@dazza.org>
  - [Bug 89] Modify OPTIMIZER to be less aggressive for binary compatibility
    on older systems. Tweaked .spec file so that one file produces both the
    rh6 and rh7 RPMs (requires rpm-3.0.5 or better), remove gawk version
    requirement. Removed --with-TIFFINC and --with-libdb from configure
    invocation (Giulio Orsero).

* Thu Feb 22 2001 Darren Nickerson <darren@dazza.org>
  - update to hylafax-4.1beta3

* Sun Dec 03 2000 Darren Nickerson <darren@dazza.org>
  - update to cvs-20001203
  - break out VRes tweak into hyla.conf for clarity. Makes a nice
    example of how to use hyla.conf as well.

* Fri Sep 01 2000 Darren Nickerson <darren@dazza.org>
  - remove SysVinit patch due to clash with Tim Rice's work
  - hard-wire gawk dependency to prevent it defaulting to mawk, which
    is not installed by default on some systems
  - use system zlib rather than HylaFAX's bundled one
  - remove typerules and ps2fax patches, after committing them to CVS
  - Update README.RPM

* Wed Aug 23 2000 Darren Nickerson <darren@dazza.org>
  - remove libgr dependencies for greater RPM portability

* Mon Aug 21 2000 Giulio Orsero <giulioo@pobox.com>
  - new 'rh7/rpm4 features': uses FHS macros, binaries stripping and man
    pages gzipping handled by rpm policies, uses /sbin/service.
  - Red Hat 6.x/7.x style init script (colors, condrestart)
  - does not use caldera's lisa because I cannot test it.
  - xferfaxlog and lutRS18.pcf no more tagged as config files
  - RPM does not own /etc, /usr, ... anymore, just the files in them
  - no more AFM dir and links (textfmt does not need them since 4.1b1)
  - page size set to A4
  - does not use HTML/CGI configure directive, takes html directly from source
  - configure patched to run 'unattended' 
  - no more in RPM: manpage, man2html, unquote, faxsetup.irix, faxsetup.bsdi
  - uses the OPTIMIZER variable to pass RPM_OPT_FLAGS to 'make'

* Sun May 14 2000 Darren Nickerson <darren@dazza.org>
  - standardize on libtiff >= 3.5 to resolve run length (16->32 bits)
  - fixed modes of .dso files in dso.patch to silence ldd warning

* Sun Mar 18 2000 Darren Nickerson <darren@dazza.org>
  - instead of beta2 + patches, begin using CVS snapshot
  - changed LIBTIFF linker line to include -ljpeg -lz, suggested by Matthew
	Rice <matt@starnix.com>, and Erik Ratcliffe <erik@calderasystems.com>

* Fri Nov 5 1999 Matthew Rice <matt@starnix.com>
  - hylafax-4.1beta2.tar.gz
  - added lisa support
  - fix for installing into a build root

* Wed Jun 16 1999 Darren Nickerson <darren@info.tpc.int>
  - hylafax-4.1beta1.tar.gz
  - added chkconfig support
  - removed libjpeg linking and dependency

* Tue Sep 29 1998 Darren Nickerson <darren@info.tpc.int>
  - added security fix proposed by Carsten Hoeger <choeger@suse.de> for
	potential race condition  reported by Tobias Richter
	<tsr@cave.isdn.cs.tu-berlin.de>
* Wed Sep 9 1998 Darren Nickerson <darren@info.tpc.int>
  - built the RPM on Redhat-5.0 to avoid dependency problems with libjpeg
	and libstdc++. 
* Tue May 26 1998 Darren Nickerson <darren@info.tpc.int>
  - removed .orig files from patch - they were 90 percent of it
  - removed oversimplified /dev/modem assumptions
  - faxcron was invoking xferstats, instead of new xferfaxstats - fixed
  - revised faxcron's manpage
  - HylaFAX was still writing etc/xferlog. Changed to etc/xferfaxlog as 
	advertised by all supporting docs and scripts.
  - added hourly faxqclean and daily faxcron cron jobs, and xferlog rotation
  - hfaxd no longer hard-wired as running from inetd, faxsetup will handle this
  - no longer assumes /dev/modem and blindly inserts inittab entry
  - change naming scheme to differentiate rh4/rh5
  - move documentation back into main rpm, instead of sub-packages
  - added Robert Colquhoun's textfmt-mailer patch
  - increased margin on LHS, was too close and getting clipped
  - make faxsetup warn that modem class = modem pool, not Class1/2/2.0
  - use HylaFAX's init script, startup with new protocol only and no snpp
  - added -DFIXEDMEDIA to last command in ps2fax.gs, as posted
	by "Alan Sparks" <asparks@nss.harris.com>
  - added fixhtml patch, removed release from the doc dir, now just version
  - added Nico's skel patch, for class1/2/2.0 modem prototype files
  - added Robert Colquhoun's patch to hfaxd's tagline generation
  - fixes to build on 5.1, contributed by Richard Sharpe <sharpe@ns.aus.com>
  - faxrcvd now treated as a config file, preserved as .rpmsave
  - fixed ghostscript dependency to require fonts-std, not fonts.
  - remove requirement for mawk - use gawk instead.
  - faxsetup now detects is hfaxd is not driven from inetd, and starts it
	when restarting faxq using SysV init script (Robert Colquhoun)
  

* Wed Mar 04 1998 Markus Pilzecker <mp@rhein-neckar.netsurf.de>
  - took ldconfig call out of install section
  - minimized and compressed patch
  - arch rpm buildable as ordinary user
  - diverted subpackages for [un]compressed man pages
  - diverted subpackage for html documentation

* Thu Jan 22 1998 Bernd Johannes Wuebben <wuebben@kde.org>
  - hylafax-4.0-8
  - A previous version of this spec file was handed to me by 
    Ramana Juvvadi (juvvadi@lekha.org)  
    who unfortunately can no longer provide rpms of hylafax. 
    Thanks so much for you work Ramana!
    Bernd

* Fri Oct 24 1997 Ramana Juvvadi (juvvadi@lekha.org)
  - hylafax-4.0-6
  

