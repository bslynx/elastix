Summary: Implementation of the Primary Rate ISDN specification
Name: libpri
Version: 1.4.1
Release: 1%{?lptver}
License: GPL
Group: System Environment/Libraries
URL: http://www.asterisk.org/
Source: http://ftp.digium.com/pub/libpri/libpri-%{version}.tar.gz

Patch1: libpri_bristuff-0.4.0-test4.patch
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root

%description
C implementation of the Primary Rate ISDN specification.
It was based on the Bellcore specification SR-NWT-002343 for National ISDN.
As of May 12, 2001, it has been tested work with NI-2, Nortel DMS-100, and
Lucent 5E Custom protocols on switches from Nortel and Lucent.

%package devel
Summary: Header files and development libraries for libpri
Group: Development/Libraries
Requires: %{name} = %{version}

%description devel
This package contains the header files needed to compile applications that
will use libpri.

%prep
%setup
%patch1 -p1 
%{__perl} -pi -e 's|(\$\(INSTALL_BASE\)/)lib|$1%{_lib}|g' Makefile

%build
export CFLAGS="%{optflags}"
%{__make} %{?_smp_mflags}

%install
%{__rm} -rf %{buildroot}
%{__make} install INSTALL_PREFIX=%{buildroot}

%clean
%{__rm} -rf %{buildroot}

%post -p /sbin/ldconfig

%postun -p /sbin/ldconfig

%files
%defattr(-, root, root, 0755)
%doc ChangeLog LICENSE README TODO
%{_libdir}/*.so.*

%files devel
%defattr(-, root, root, 0755)
%{_includedir}/*
%exclude %{_libdir}/*.a
%{_libdir}/*.so

%changelog
* Sun Oct  7 2007 Edgar Landivar <e_landivar@palosanto.com>
- Update to 1.4.1.

* Sat Mar 17 2007 Joel Barrios <http://joel-barrios.blogspot.com/>
- Update to 1.4.0.

* Fri Nov 24 2006 Matthias Saou <http://freshrpms.net/> 1.2.4-1 #4932
- Update to 1.2.4.

* Thu Sep  7 2006 Matthias Saou <http://freshrpms.net/> 1.2.3-1
- Update to 1.2.3.

* Fri Jan 27 2006 Matthias Saou <http://freshrpms.net/> 1.2.2-1
- Update to 1.2.2.

* Fri Nov 25 2005 Matthias Saou <http://freshrpms.net/> 1.2.0-1
- Update to 1.2.0.
- Split off devel sub-package.

* Tue Aug 23 2005 Matthias Saou <http://freshrpms.net/> 1.0.9-1
- Initial RPM release.

