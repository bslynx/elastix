Summary: Additional packages and and third party software for the Elastix PBX software appliance
Name: elastix-additionals
Version: 0.9.0
Release: 6
License: GPL
Group: Applications/System
Source: elastix-additionals-%{version}.tgz
BuildRoot: %{_tmppath}/%{name}-%{version}-root
BuildArch: noarch
Prereq: elastix, tftp-server, vsftpd, perl, freePBX, postfix, cyrus-imapd, zaptel, /sbin/chkconfig, openfire

%description
Additional packages and and third party software for the Elastix PBX software appliance

%prep
%setup -n elastix-additionals

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT

mkdir -p $RPM_BUILD_ROOT/tftpboot
#mkdir -p $RPM_BUILD_ROOT/etc
mkdir -p $RPM_BUILD_ROOT/etc/cron.daily/
mkdir -p $RPM_BUILD_ROOT/etc/postfix
mkdir -p $RPM_BUILD_ROOT/bin
mkdir -p $RPM_BUILD_ROOT/usr/src
mkdir -p $RPM_BUILD_ROOT/usr/local/sbin
mkdir -p $RPM_BUILD_ROOT/var/ftp/config
mkdir -p $RPM_BUILD_ROOT/var/www/html
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin
#mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/sounds
#mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/sounds/tts
mkdir -p $RPM_BUILD_ROOT/var/lib/asterisk/mohmp3
mkdir -p $RPM_BUILD_ROOT/tmp

mv  $RPM_BUILD_DIR/elastix-additionals/asterisk_cleanup $RPM_BUILD_ROOT/etc/cron.daily/

tar xvf $RPM_BUILD_DIR/elastix-additionals/tftpboot.tar -C $RPM_BUILD_ROOT/tftpboot
mv -f $RPM_BUILD_DIR/elastix-additionals/tftp $RPM_BUILD_ROOT/tmp

cp -f $RPM_BUILD_DIR/elastix-additionals/vsftpd.conf $RPM_BUILD_ROOT/tmp
cp -f $RPM_BUILD_DIR/elastix-additionals/vsftpd.user_list $RPM_BUILD_ROOT/etc

# FIXME: Este paquete debe ser eliminado. Lo mas importante aqui es la carpeta crm pero
#        esta carpeta podria pertenecer a un futuro paquete de sugarcrm
mv $RPM_BUILD_DIR/elastix-additionals/webContentAdditional/* $RPM_BUILD_ROOT/var/www/html/

mv $RPM_BUILD_DIR/elastix-additionals/asterisk.reload $RPM_BUILD_ROOT/bin

mv $RPM_BUILD_DIR/elastix-additionals/festival-weather-script.pl $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin
mv $RPM_BUILD_DIR/elastix-additionals/festival-script.pl $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin
mv $RPM_BUILD_DIR/elastix-additionals/*.agi $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin
mv $RPM_BUILD_DIR/elastix-additionals/nv-weather.php $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin
mv $RPM_BUILD_DIR/elastix-additionals/wakeup.php $RPM_BUILD_ROOT/var/lib/asterisk/agi-bin

# Installing sounds
# FIXME: This files should be under the freepbx RPM
#mv $RPM_BUILD_DIR/elastix-additionals/extra_sounds.tar.gz $RPM_BUILD_ROOT/tmp

# Instalando Native Music on Hold files
unzip $RPM_BUILD_DIR/elastix-additionals/moh-native.zip -d $RPM_BUILD_ROOT/var/lib/asterisk/mohmp3

# Copio Script motd.sh
mv $RPM_BUILD_DIR/elastix-additionals/motd.sh $RPM_BUILD_ROOT/usr/local/sbin/motd.sh

# Remplazo archivos de Postfix y Cyrus
mv $RPM_BUILD_DIR/elastix-additionals/imapd.conf.elastix $RPM_BUILD_ROOT/etc
mv $RPM_BUILD_DIR/elastix-additionals/main.cf.elastix $RPM_BUILD_ROOT/etc/postfix


%post
# Installing sounds
# FIXME: This files should be under the freepbx RPM
#tar zxf $RPM_BUILD_ROOT/tmp/extra_sounds.tar.gz -C /var/lib/asterisk/sounds

# Reemplazo archivos de otros paquetes: tftp, vsftp, zaptel
cat $RPM_BUILD_ROOT/tmp/tftp > /etc/xinetd.d/tftp
cat $RPM_BUILD_ROOT/tmp/vsftpd.conf > /etc/vsftpd/vsftpd.conf

chmod 777 /etc/cron.daily/asterisk_cleanup
chmod 666 /etc/ntp.conf

# Fixing CentOS Alt-F9 terminal bug
#cp /etc/sysconfig/i18n /tmp/i18n.tmp
#sed s/"latarcyrheb-sun16"/"lat0-sun16"/g < /tmp/i18n.tmp> /etc/sysconfig/i18n
#rm -f /tmp/i18n.tmp

# Tareas de TFTP
chmod 777 /tftpboot
chmod 777 /tftpboot/* -R

# Tareas de VSFTPD
chkconfig --level 2345 vsftpd on
chmod 777 /var/ftp/config

# Permisos de Ejecucion a "asterisk.reload"
chmod 777 /bin/asterisk.reload

# Permisos para Scripts AGI
chmod +x /var/lib/asterisk/agi-bin/*
#chmod -R 755 /var/lib/asterisk/sounds
#chmod 777 /var/lib/asterisk/sounds/tts

# Permisos para el Script motd.sh
chmod 777 /usr/local/sbin/motd.sh

# Configuro las tarjetas Zaptel
# FIXME: Esto se deberia hacer siempre y cuando no sea una instalacion nueva?
/usr/local/sbin/genzaptelconf

# Cambio archivos de Postfix e Imapd con los de Elastix
mv /etc/imapd.conf /etc/imapd.conf.orig
mv /etc/postfix/main.cf  /etc/postfix/main.cf.orig
cp /etc/imapd.conf.elastix /etc/imapd.conf
cp /etc/postfix/main.cf.elastix /etc/postfix/main.cf

# If openfire is not running probably we're in the distro installation process
# So, i configure openfire init script as stopped by default
/sbin/service openfire status | grep "not running" &>/dev/null
res=$?
# Openfire esta apagado
if [ $res -eq 0 ]; then
    # Desactivo el servicio openfire al inicio
    /sbin/chkconfig --level 2345 openfire off
fi     



%pre

useradd -d /var/ftp -M -s /sbin/nologin ftpuser
(echo asterisk2007; sleep 2; echo asterisk2007) | passwd ftpuser

%clean
rm -rf $RPM_BUILD_ROOT

# basic contains some reasonable sane basic tiles
%files
%defattr(-, asterisk, asterisk)
/var/www/html/*
/var/lib/asterisk/*
%defattr(-, root, root)
/tftpboot/*
/etc/imapd.conf.elastix
/etc/postfix/main.cf.elastix
/etc/cron.daily/*
/etc/vsftpd.user_list
/bin/*
/tmp/*
%dir
/usr/local/sbin
/var/ftp/config

%changelog
* Tue Oct 30 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.0-6
  - Disabling Openfire service by default.

* Tue Oct 26 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.0-5
  - Removing freepbx extra sounds. These files will belong to the freepbx rpm.

* Tue Oct 23 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.0-4
  - Removing the asterisk logrotate script. This file has been included in the asterisk RPM 
  - Removing the asterisk native sounds in ulaw format. I'm not sure why they were needed.
  - Removing touch to /etc/fxotune file.

* Tue Oct 22 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.0-2
  - /etc/sysconfig/zaptel was populated here but this has been removed because 
    this file is generated by genzaptelconf

* Tue Oct  9 2007 Edgar Landivar <elandivar@palosanto.com> 0.9.0-1
  - Removed some old scripts.
  - genzaptelconf removed. This script will be included into the zaptel package.