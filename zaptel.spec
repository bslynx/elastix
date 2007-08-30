# "uname -r" output of the kernel to build for, the running one
# if none was specified with "--define 'kernel <uname -r>'"
%{!?kernel: %{expand: %%define kernel %(uname -r)}}

%define kversion %(echo %{kernel} | sed -e s/smp// -)
%define krelver  %(echo %{kversion} | tr -s '-' '_')
%if %(echo %{kernel} | grep -c smp)
        %{expand:%%define ksmp -smp}
%endif

Summary: Telephony interface support
Name: zaptel
Version: 1.4.5.1
Release: 1%{?lptver}
License: GPL
Group: System Environment/Libraries
URL: http://www.asterisk.org/
Source0: http://ftp.digium.com/pub/zaptel/zaptel-%{version}.tar.gz
Source1: zaptel-makedev.d.txt
Patch0: ystdm8xx-zaptel-1.4.5.patch
#Patch1: zaptel-sysconfig-yeastar.patch
Patch2: zaptel-init.patch
#Patch3: zaptel-Makefile.patch
Prereq: kernel-module-zaptel, kernel-module-zaptel-xen
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root
BuildRequires: kernel%{?ksmp}-devel = %{kversion}
BuildRequires: newt-devel, libusb-devel, MAKEDEV

%description
This package contains the libraries, device entries, startup scripts and tools
needed to use Digium telephony hardware. This includes the pseudo TDM
interfaces.

You will also need to install a kernel modules package matching your current
kernel for everything to work, and edit /etc/modprobe.conf.

%package devel
Summary: Header files and development libraries for Zaptel
Group: Development/Libraries
Requires: %{name} = %{version}

%description devel
This package contains the header files needed to compile applications that
will use Zaptel, such as Asterisk.

%package -n kernel%{?ksmp}-module-zaptel
Summary: Kernel modules required for some hardware to operate with Zaptel
Release: %{krelver}_%{release}
Group: System Environment/Kernel
Requires: kernel%{?ksmp} = %{kversion}, /sbin/depmod
Provides: kernel-modules
%{?ksmp:Provides: kernel-module-zaptel = %{version}-%{release}_%{krelver}}
#%kmdl_dependencies

%description -n kernel%{?ksmp}-module-zaptel
This package contains the zaptel kernel modules for the Linux kernel package :
%{kversion} (%{_target_cpu}%{?ksmp:, SMP}).

%prep
%setup
%patch0 -p1
#%patch1 -p0 
%patch2 -p0 
#%patch3 -p1
# Fix lib vs. lib64
%{__perl} -pi -e 's|/usr/lib|%{_libdir}|g' Makefile
# Force mknod calls to never happen
%{__perl} -pi -e 's|mknod |true |g' Makefile


%build
export CFLAGS="%{optflags}"
%configure
%{__make} %{?_smp_mflags} KVERS="%{kernel}"

%install
%{__rm} -rf %{buildroot}
# Install checks the presence of this file to decide which to modify
%{__mkdir_p} %{buildroot}%{_sysconfdir}
touch %{buildroot}%{_sysconfdir}/modprobe.conf
# Required in 1.2.0
%{__mkdir_p} %{buildroot}%{_mandir}/man8
# Main install
%{__make} install \
    KVERS="%{kernel}" \
    DESTDIR="%{buildroot}" \

# Install and generate all the device stuff
%{__install} -D -p -m 0644 %{SOURCE1} \
    %{buildroot}%{_sysconfdir}/makedev.d/zaptel

# Create entry list
[ -x /sbin/MAKEDEV ] && MAKEDEV=/sbin/MAKEDEV || MAKEDEV=/dev/MAKEDEV
${MAKEDEV} \
    -c %{buildroot}%{_sysconfdir}/makedev.d \
    -d %{buildroot}/dev -M zaptel | sed 's|%{buildroot}||g' | \
    grep -v 'dir /dev$' > device.list

# Install the init script and sysconfig file
%{__install} -Dp -m0644 zaptel.sysconfig \
    %{buildroot}%{_sysconfdir}/sysconfig/zaptel
%{__install} -Dp -m0755 zaptel.init \
    %{buildroot}%{_sysconfdir}/rc.d/init.d/zaptel

# Move kernel modules in the "kernel" subdirectory
#%{__mkdir_p} %{buildroot}/lib/modules/%{kernel}/kernel
#%{__mv} %{buildroot}/lib/modules/%{kernel}/extra \
#        %{buildroot}/lib/modules/%{kernel}/kernel/extra

# Move the modules config file back in order to put it in docs instead
%{__mv} %{buildroot}%{_sysconfdir}/modprobe.conf . || :

# Move the binaries from /sbin back to /usr/sbin
#%{__mkdir_p} %{buildroot}%{_sbindir}
#%{__mv} %{buildroot}/sbin/* %{buildroot}%{_sbindir}/

# Remove the backup of the empty file we created earlier
%{__rm} -f %{buildroot}%{_sysconfdir}/modprobe.conf.bak || :

# Required now to install the config files
%{__make} config \
    KVERS="%{kernel}" \
    INSTALL_PREFIX="%{buildroot}" \
    DESTDIR="%{buildroot}" \
    ROOT_PREFIX="%{buildroot}"

# Install zaptel.conf
%{__install} -Dp -m0755 zaptel.conf.sample \
    %{buildroot}%{_sysconfdir}/zaptel.conf

%clean
%{__rm} -rf %{buildroot}

%post 
/sbin/ldconfig
/sbin/chkconfig --add zaptel
/sbin/chkconfig --level 2345 zaptel on
# deactivate the original zaptel udev rules in /etc/udev/rules.d/50-udev.rules
# perl -pi -e's,KERNEL\=\=\"zap,#KERNEL\=\=\"zap,g' /etc/udev/rules.d/50-udev.rules
mkdir -p /var/lib/digium/licenses

%postun 
/sbin/ldconfig

# activate the original zaptel udev rules in /etc/udev/rules.d/50-udev.rules
# perl -pi -e's,#KERNEL\=\=\"zap,KERNEL\=\=\"zap,g' /etc/udev/rules.d/50-udev.rules


%post -n kernel%{?ksmp}-module-zaptel
/sbin/depmod -a -F /boot/System.map-%{kernel} %{kernel} &>/dev/null || :

%postun -n kernel%{?ksmp}-module-zaptel
/sbiin/depmod -a -F /boot/System.map-%{kernel} %{kernel} &>/dev/null || :

%files -f device.list
%defattr(-, root, root, 0755)
%doc ChangeLog README.fxsusb mod*.conf
%doc ifcfg-hdlc0 ifup-hdlc zaptel.conf.sample
%config(noreplace) %{_sysconfdir}/sysconfig/zaptel
%config(noreplace) %{_sysconfdir}/zaptel.conf
%{_sysconfdir}/makedev.d/*
%{_sysconfdir}/rc.d/init.d/*
%{_sysconfdir}/default/zaptel
%{_sysconfdir}/hotplug/*
%{_sysconfdir}/udev/rules.d/xpp.rules
/sbin/*
/usr/sbin/*
/usr/include/*
/usr/share/zaptel/*
/usr/lib/perl5/*
%{_libdir}/*.so.*
%{_libdir}/*.a
%{_mandir}/man8/*

%files devel
%defattr(-, root, root, 0755)
%{_includedir}/zaptel/*.h
%{_libdir}/*.so

%files -n kernel%{?ksmp}-module-zaptel
%defattr(-, root, root, 0755)
/lib
#/lib/modules/%{kernel}/kernel/extra/

%changelog
* Fri Aug 24 2007 Tzafrir Cohen <tzafrir.cohen@xorcom.com> 1.4.5.1-1
- New upstrem release - fixes Makefile typo.
- Removing makefile typo.
- Remove b410p mISDN drivers: they belong in an misdn package.

* Mon Jun 11 2007 Edwin Boza <www.palosanto.com>
- Add yeastar module to /etc/sysconfig/zaptel
- Initialize service zaptel on level 2345

* Sat Mar 17 2007 Joel Barrios <http://joel-barrios.blogspot.com/>
- Update to 1.4.0.

* Sun Mar 04 2007 Joel Barrios <http://joel-barrios.blogspot.com/>
- Update to 1.2.15

* Sun Dec 17 2006 Joel Barrios <http://linuxparatodos.net/>
- Update to 1.2.12

* Fri Nov 24 2006 Matthias Saou <http://freshrpms.net/> 1.2.11-1
- Update to 1.2.11.

* Thu Sep  7 2006 Matthias Saou <http://freshrpms.net/> 1.2.8-1
- Update to 1.2.8.

* Thu May  4 2006 Matthias Saou <http://freshrpms.net/> 1.2.5-1
- Update to 1.2.5.

* Wed Mar 15 2006 Matthias Saou <http://freshrpms.net/> 1.2.4-1
- Rebuild fails on RHEL4 up U3 (included), because of a typo :
  https://bugzilla.redhat.com/180568

* Tue Mar  7 2006 Matthias Saou <http://freshrpms.net/> 1.2.4-1
- Update to 1.2.4.

* Tue Jan 31 2006 Matthias Saou <http://freshrpms.net/> 1.2.3-1
- Update to 1.2.3.

* Fri Jan 27 2006 Matthias Saou <http://freshrpms.net/> 1.2.2-1
- Update to 1.2.2.

* Fri Nov 25 2005 Matthias Saou <http://freshrpms.net/> 1.2.0-1
- Update to 1.2.0.
- No longer patch the Makefile, horray!
- Kernel modules are now in "extra" and no longer in "misc".
- Split off devel sub-package.

* Thu Sep 15 2005 Matthias Saou <http://freshrpms.net/> 1.0.9.2-1
- Update to 1.0.9.2.
- Update makefile patch to add ztdummy to the modules.
- Fix kernel-smp-devel requirement for smp modules rebuild.

* Tue Aug 23 2005 Matthias Saou <http://freshrpms.net/> 1.0.9.1-0
- Update to 1.0.9.1.
- Remove "devices" from install with the Makefile patch.
- Replace /usr/lib in Makefile with %%{_libdir} to fix 64bit lib location.

* Tue Apr  5 2005 Matthias Saou <http://freshrpms.net/> 1.0.7-0
- Update to 1.0.7.
- This spec still doesn't build with mach (sub-package release tag bug).

* Tue Mar  8 2005 Matthias Saou <http://freshrpms.net/> 1.0.6-0
- Update to 1.0.6.
- Change /dev/MAKEDEV calls to /sbin/MAKEDEV for FC3.
- Rework and re-enable the kernel modules, only through kernel-devel, though.

* Wed Feb  2 2005 Matthias Saou <http://freshrpms.net/> 1.0.4-0
- Update to 1.0.4.
- Updated makefile patch.
- Keep "/dev" from being owned by the package.

* Mon Oct 18 2004 Matthias Saou <http://freshrpms.net/> 1.0.1-0
- Update to 1.0.1.

* Mon Aug 30 2004 Matthias Saou <http://freshrpms.net/> 1.0-0.RC2.0
- Update to 1.0-RC2.
- Disable kernel module building for now, we don't use any.

* Mon Jul 26 2004 Matthias Saou <http://freshrpms.net/> 1.0-0.RC1.1
- Update to 1.0-RC1.
- Major Makefile patch updates, spec updates to match.

* Mon Nov 17 2003 Matthias Saou <http://freshrpms.net/>
- Uncomment the ztdummy module to have it built.

* Wed Nov  5 2003 Matthias Saou <http://freshrpms.net/>
- Initial RPM release.

