# vim: set fileencoding=utf-8 :
# vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
# Codificación: UTF-8
# +----------------------------------------------------------------------+
# | Elastix version 2.0.4                                                |
# | http://www.elastix.org                                               |
# +----------------------------------------------------------------------+
# | Copyright (c) 2006 Palosanto Solutions S. A.                         |
# +----------------------------------------------------------------------+
# | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
# | Telfs. 2283-268, 2294-440, 2284-356                                  |
# | Guayaquil - Ecuador                                                  |
# | http://www.palosanto.com                                             |
# +----------------------------------------------------------------------+
# | The contents of this file are subject to the General Public License  |
# | (GPL) Version 2 (the "License"); you may not use this file except in |
# | compliance with the License. You may obtain a copy of the License at |
# | http://www.opensource.org/licenses/gpl-license.php                   |
# |                                                                      |
# | Software distributed under the License is distributed on an "AS IS"  |
# | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
# | the License for the specific language governing rights and           |
# | limitations under the License.                                       |
# +----------------------------------------------------------------------+
# | The Original Code is: Elastix Open Source.                           |
# | The Initial Developer of the Original Code is PaloSanto Solutions    |
# +----------------------------------------------------------------------+
# $Id: dialerd,v 1.2 2008/09/08 18:29:36 alex Exp $
import logging
import re
import struct
import eventlet
from eventlet.green import socket, urllib2, urllib, os
import errno
import json
from elastix.BaseEndpoint import BaseEndpoint
telnetlib = eventlet.import_patched('telnetlib')
import cookielib

class Endpoint(BaseEndpoint):
    def __init__(self, amipool, dbpool, sServerIP, sIP, mac):
        BaseEndpoint.__init__(self, 'Grandstream', amipool, dbpool, sServerIP, sIP, mac)
        self._timeZone = 'auto'

    def setExtraParameters(self, param):
        if not BaseEndpoint.setExtraParameters(self, param): return False
        if 'timezone' in param: self._timeZone = param['timezone']
        return True

    def setModel(self, sModel):
        if sModel in (
            # Tested models
            'GXP280', 'GXV3140', 'GXV3175', 'GXP2120', 'BT200', 'GXP1400',
            # Tested by Sergio
            'GXP2100', 'GXP1405',
            # These expose admin console in ssh, not telnet
            'GXP1450', 'GXP2200',            
            # Untested models 
            'GXP2000', 'GXP2020','HT386'):
            self._model = sModel
            return True
        return False
    
    def probeModel(self):
        '''Probe specific model of the Grandstream phone
        
        To probe for the specific model, a telnet session is tried first. The 
        login banner exposes the phone model. If the telnet session is refused,
        an attempt is made to invoke the manager URL through HTTP.
        '''
        bTelnetFailed = False
        try:
            telnet = telnetlib.Telnet()
            telnet.open(self._ip)
            telnet.get_socket().settimeout(5)
        except socket.timeout, e:
            logging.error('Endpoint %s@%s failed to telnet - timeout (%s)' %
                (self._vendorname, self._ip, str(e)))
            return
        except socket.error, e:
            logging.info('Endpoint %s@%s failed to telnet - %s. Trying HTTP...' %
                (self._vendorname, self._ip, str(e)))
            bTelnetFailed = True

        
        sModel = None
        # If telnet failed to connect, the model might still be exposed through HTTP
        if bTelnetFailed:
            try:
                response = urllib2.urlopen('http://' + self._ip + '/manager?action=product&time=0')
                htmlbody = response.read()
                if response.code == 200:
                    # Response=Success\r\nProduct=GXP2200\r\n
                    m = re.search(r'Product=(\w+)', htmlbody)
                    if m != None: sModel = m.group(1)
            except Exception, e:
                pass
        else:
            try:
                idx, m, text = telnet.expect([r'Password:'])
                telnet.close()
                
                # This is known to detect GXV3140, GXP2120
                m = re.search(r'Grandstream (\S+)\s', text)
                if m != None:
                    sModel = m.group(1)
                
                # If this matches, this is an Elastix phone (rebranded Grandstream)
                m = re.search(r'Elastix (\S+)\s', text)
                if m != None:
                    self._saveVendor('Elastix')
                    sModel = m.group(1)
                
                #if sModel == None:
                #    print text
            except socket.error, e:
                logging.error('Endpoint %s@%s connection failure - %s' %
                    (self._vendorname, self._ip, str(e)))
                return False
        
        if sModel != None: self._saveModel(sModel)

    def updateLocalConfig(self):
        '''Configuration for Grandstream endpoints (local):
        
        The file cfgXXXXXXXXXXXX contains the SIP configuration. Here 
        XXXXXXXXXXXX is replaced by the lowercase MAC address of the phone. 
        Grandstream is special in that the file is not text but a binary 
        encoding, which is generated by _encodeGrandstreamConfig().
        
        To reboot the phone, it is necessary to issue the AMI command:
        For GXP280,GXV3140,GXV3175: "sip notify cisco-check-cfg {$EXTENSION}"
        '''
        # Check that there is at least one account to configure
        if len(self._accounts) <= 0:
            logging.error('Endpoint %s@%s has no accounts to configure' %
                (self._vendorname, self._ip))
            return False

        # Need to calculate lowercase version of MAC address without colons
        sConfigFile = 'cfg' + (self._mac.replace(':', '').lower())
        sConfigPath = self._tftpdir + '/' + sConfigFile
        
        vars = self._hashTableGrandstreamConfig()

        try:
            self._writeContent(sConfigPath, self._encodeGrandstreamConfig(vars))
        except IOError, e:
            logging.error('Endpoint %s@%s failed to write configuration file - %s' %
                (self._vendorname, self._ip, str(e)))
            return False
        
        # Attempt to send configuration via HTTP to phone. This is required for
        # static provisioning
        if not self._enableStaticProvisioning(vars):
            return False
        
        # Check if there is at least one registered extension. This is required
        # for sip notify to work
        if self._hasRegisteredExtension():
            # GXV3175 wants check-sync, not sys-control
            #self._amireboot('grandstream-check-cfg')
            self._amireboot('cisco-check-cfg')
        elif self._telnet_password != None and not self._rebootbytelnet():
            return False            
        elif self._ssh_password != None and not self._rebootbyssh():
            return False
        
        self._unregister()
        self._setConfigured()
        return True

    def _enableStaticProvisioning(self, vars):
        # Detect what kind of HTTP interface is required
        try:
            # Interface for newer GXP140x firmware - JSON based
            response = urllib2.urlopen('http://' + self._ip + '/cgi-bin/api.values.post')
            body = response.read()
            logging.info('Endpoint %s@%s appears to have GXP140x JSON interface...' %
                        (self._vendorname, self._ip))
            return self._enableStaticProvisioning_GXP140x(vars)
        except urllib2.HTTPError, e:
            if e.code != 404:
                logging.error('Endpoint %s@%s failed to detect GXP140x - %s' %
                    (self._vendorname, self._ip, str(e)))
                return False
        except socket.error, e:
            logging.error('Endpoint %s@%s failed to connect - %s' %
                (self._vendorname, self._ip, str(e)))
            return False
        try:
            # Interface for old BT200 firmware or similar
            response = urllib2.urlopen('http://' + self._ip + '/update.htm')
            body = response.read()
            logging.info('Endpoint %s@%s appears to have BT200 interface...' %
                        (self._vendorname, self._ip))
            return self._enableStaticProvisioning_BT200(vars)
        except urllib2.HTTPError, e:
            if e.code != 404:
                logging.error('Endpoint %s@%s failed to detect BT200 - %s' %
                    (self._vendorname, self._ip, str(e)))
                return False
        except socket.error, e:
            logging.error('Endpoint %s@%s failed to connect - %s' %
                (self._vendorname, self._ip, str(e)))
            return False
        
        logging.warning('Endpoint %s@%s cannot identify HTTP interface, static provisioning might not work.' %
                    (self._vendorname, self._ip))
        return True

    def _enableStaticProvisioning_GXP140x(self, vars):
        try:
            # Login into interface and get SID. Check proper Content-Type
            response = urllib2.urlopen('http://' + self._ip + '/cgi-bin/dologin',
                urllib.urlencode({'password' : self._http_password}))
            body = response.read()
            if response.info()['Content-Type'] <> 'application/json':
                logging.error('Endpoint %s@%s GXP140x - dologin answered not application/json but %s' %
                    (self._vendorname, self._ip, response.info()['Content-Type']))
                return False
            
            # Check successful login and get sid
            jsonvars = json.read(body)
            if not ('body' in jsonvars and 'sid' in jsonvars['body']):
                logging.error('Endpoint %s@%s GXP140x - dologin failed login' %
                    (self._vendorname, self._ip))
                return False
            sid = jsonvars['body']['sid']
            
            # Post vars with sid
            vars.update({'sid' : sid})
            response = urllib2.urlopen('http://' + self._ip + '/cgi-bin/api.values.post',
                urllib.urlencode(vars))
            body = response.read()
            if response.info():
                if response.info()['Content-Type'] <> 'application/json':
                    logging.error('Endpoint %s@%s GXP140x - api.values.post answered not application/json but %s' %
                        (self._vendorname, self._ip, response.info()['Content-Type']))
                    return False
                jsonvars = json.read(body)
            else:
                # The GXP1400 has been discovered to violate the HTTP protocol.
                # The response for /cgi-bin/api.values.post sticks a shebang
                # header before the HTTP headers of the response. This causes
                # the header parsing to end early and the body gets prepended
                # with the headers. We now have to undo this mess.
                expectbody = False 
                for s in body.splitlines():
                    if not expectbody:
                        m = re.search(r'Content-Type: (\S+)', s)
                        if m != None:
                            if m.group(1) <> 'application/json':
                                logging.error('Endpoint %s@%s GXP140x - api.values.post answered not application/json but %s' %
                                    (self._vendorname, self._ip, m.group(1)))
                                return False
                        if s == '':
                            expectbody = True
                    else:
                        # This expects the body to be a single JSON string in one line
                        jsonvars = json.read(s)
                        break
            if not ('response' in jsonvars and jsonvars['response'] == 'success' \
                    and 'body' in jsonvars and 'status' in jsonvars['body'] and jsonvars['body']['status'] == 'right' ):
                logging.error('Endpoint %s@%s GXP140x - vars rejected by interface - %s' %
                    (self._vendorname, self._ip, body))
                return False
            
            return True
        except json.ReadException, e:
            logging.error('Endpoint %s@%s GXP140x received invalid JSON - %s' %
                (self._vendorname, self._ip, str(e)))
            return False
        except urllib2.HTTPError, e:
            logging.error('Endpoint %s@%s GXP140x failed to send vars to interface - %s' %
                (self._vendorname, self._ip, str(e)))
            return False
        except socket.error, e:
            logging.error('Endpoint %s@%s GXP140x failed to connect - %s' %
                (self._vendorname, self._ip, str(e)))
            return False

    def _enableStaticProvisioning_BT200(self, vars):
        try:
            # Login into interface
            cookiejar = cookielib.CookieJar(cookielib.DefaultCookiePolicy(rfc2965=True))
            opener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cookiejar))
            response = opener.open('http://' + self._ip + '/dologin.htm',
                urllib.urlencode({'Login' : 'Login', 'P2' : self._http_password, 'gnkey' : '0b82'}))
            body = response.read()
            if 'dologin.htm' in body:
                logging.error('Endpoint %s@%s BT200 - dologin failed login' %
                    (self._vendorname, self._ip))
                return False

            # Force cookie version to 0
            for cookie in cookiejar:
                cookie.version = 0
            
            response = opener.open('http://' + self._ip + '/update.htm',
                urllib.urlencode(vars) + '&gnkey=0b82')
            body = response.read()
            if 'dologin.htm' in body:
                logging.error('Endpoint %s@%s BT200 - dologin failed to keep session' %
                    (self._vendorname, self._ip))
                return False

            return True
        except urllib2.HTTPError, e:
            logging.error('Endpoint %s@%s BT200 failed to send vars to interface - %s' %
                (self._vendorname, self._ip, str(e)))
            return False
        except socket.error, e:
            logging.error('Endpoint %s@%s BT200 failed to connect - %s' %
                (self._vendorname, self._ip, str(e)))
            return False

    def _rebootbytelnet(self):
        '''Start reboot of Grandstream phone by telnet'''
        try:
            telnet = telnetlib.Telnet()
            telnet.open(self._ip)
            telnet.get_socket().settimeout(10)
        except socket.timeout, e:
            logging.error('Endpoint %s@%s failed to telnet - timeout (%s)' %
                (self._vendorname, self._ip, str(e)))
            return False
        except socket.error, e:
            logging.error('Endpoint %s@%s failed to telnet - %s' %
                (self._vendorname, self._ip, str(e)))
            return False

        # The Grandstream GXV3175 needs to have a wait of at least 1 second with
        # the stream open after the reboot command before the reboot command 
        # will actually take effect. We let the timeout close the telnet stream.
        telnetwaitmodels = ('GXV3140', 'GXV3175', 'GXP2120', 'GXP1400', 'GXP1405', 'GXP1450')
        deliberatetimeout = False
        
        # Attempt to login into admin telnet
        try:
            #telnet.read_until('Login:')
            if self._telnet_username != None: telnet.write(self._telnet_username.encode() + '\r\n')
            telnet.read_until('Password:')
            if self._telnet_password != None: telnet.write(self._telnet_password.encode() + '\r\n')

            # Wait for either prompt or login prompt
            idx, m, text = telnet.expect([r'Password:', r'>\s?'])
            if idx == 0:
                telnet.close()
                logging.error('Endpoint %s@%s detected ACCESS DENIED on telnet connect' %
                              (self._vendorname, self._ip))
                return False
            else:
                if self._model in ('GXV3140', 'GXV3175'):
                    rebootcommand = 'reboot'
                else:
                    # GXP280 accepts just a 'r'
                    rebootcommand = 'reboot'
                telnet.write(rebootcommand + '\r\n')
                idx, m, text = telnet.expect([r'Rebooting', r'reboot'])
                if self._model in telnetwaitmodels:
                    telnet.get_socket().settimeout(1)
                    deliberatetimeout = True
                    logging.info('Endpoint %s@%s waiting 1 second for reboot to take effect' %
                        (self._vendorname, self._ip))
                    telnet.read_all()
                else:
                    # For other models, reboot takes effect immediately
                    telnet.close()
        except socket.timeout, e:
            telnet.close()
            if not deliberatetimeout:
                logging.error('Endpoint %s@%s connection failure - %s' %
                    (self._vendorname, self._ip, str(e)))
                return False
        except socket.error, e:
            logging.error('Endpoint %s@%s connection failure - %s' %
                (self._vendorname, self._ip, str(e)))
            return False
        return True        

    def _rebootbyssh(self):
        logging.error('Endpoint %s@%s unimplemented ssh reboot - %s' %
            (self._vendorname, self._ip, str(e)))
        return False

    def _hashTableGrandstreamConfig(self):
        stdvars = self._prepareVarList()
        
        # Remove 'http://' from begingging of string
        stdvars['phonesrv'] = stdvars['phonesrv'][7:]
        
        o = stdvars['server_ip'].split('.')
        vars = {
            'P192'  :   stdvars['server_ip'], # Firmware Server Path
            'P237'  :   stdvars['server_ip'], # Config Server Path
            'P212'  :   '0',            # Firmware Upgrade. 0 - TFTP Upgrade,  1 - HTTP Upgrade.
            'P290'  :   '{ x+ | *x+ | *xx*x+ }', # (GXV3175 specific) Dialplan string
            'P64'   :   self._timeZone, # Time Zone

            'P8'    :   '0',            # DHCP=0 o static=1
            'P41': o[0], 'P42': o[1], 'P43': o[2], 'P44': o[3], # TFTP Server
            
            'P330'  :   1,    # 0-Disable phonebook download 1-HTTP 2-TFTP 3-HTTPS
            'P331'  :   stdvars['phonesrv'],
            'P332'  :   20,   # Minutes between XML phonebook fetches, or 0 to disable
        }
        if self._model in ('GXP280',):
            vars.update({'P73' : '1'})  # Send DTMF. 8 - in audio, 1 - via RTP, 2 - via SIP INFO
        if not self._dhcp:
            vars.update({
                'P8'     :  '1',    # DHCP=0 o static=1
            })
            if stdvars['static_ip'] != None:
                # IP Address
                o = stdvars['static_ip'].split('.')
                vars.update({'P9':  o[0], 'P10': o[1], 'P11': o[2], 'P12': o[3],})
            if stdvars['static_mask'] != None:
                # Subnet Mask
                o = stdvars['static_mask'].split('.')
                vars.update({'P13': o[0], 'P14': o[1], 'P15': o[2], 'P16': o[3],})
            if stdvars['static_gw'] != None:
                # Gateway
                o = stdvars['static_gw'].split('.')
                vars.update({'P17': o[0], 'P18': o[1], 'P19': o[2], 'P20': o[3],})
            if stdvars['static_dns1'] != None:
                # DNS Server 1
                o = stdvars['static_dns1'].split('.')
                vars.update({'P21': o[0], 'P22': o[1], 'P23': o[2], 'P24': o[3],})
            if stdvars['static_dns2'] != None:
                # IP Address
                o = stdvars['static_dns2'].split('.')
                vars.update({'P25': o[0], 'P26': o[1], 'P27': o[2], 'P28': o[3],})

        varmap = [
            {'enable'       :   'P271', # Enable account
             'accountname'  :   'P270', # Account Name
             'sipserver'    :   'P47',  # SIP Server
             'sipid'        :   'P35',  # SIP User ID
             'authid'       :   'P36',  # Authenticate ID
             'secret'       :   'P34',  # Authenticate password
             'displayname'  :   'P3',   # Display Name (John Doe)
             'outboundproxy':   'P48',  # Outbound Proxy
            },
            {'enable'       :   'P401', # Enable account
             'accountname'  :   'P417', # Account Name
             'sipserver'    :   'P402', # SIP Server
             'sipid'        :   'P404', # SIP User ID
             'authid'       :   'P405', # Authenticate ID
             'secret'       :   'P406', # Authenticate password
             'displayname'  :   'P407', # Display Name (John Doe)
             'outboundproxy':   'P403', # Outbound Proxy
            },
            {'enable'       :   'P501', # Enable account
             'accountname'  :   'P517', # Account Name
             'sipserver'    :   'P502', # SIP Server
             'sipid'        :   'P504', # SIP User ID
             'authid'       :   'P505', # Authenticate ID
             'secret'       :   'P506', # Authenticate password
             'displayname'  :   'P507', # Display Name (John Doe)
             'outboundproxy':   'P503',  # Outbound Proxy
            },
            {'enable'       :   'P601', # Enable account
             'accountname'  :   'P617', # Account Name
             'sipserver'    :   'P602', # SIP Server
             'sipid'        :   'P604', # SIP User ID
             'authid'       :   'P605', # Authenticate ID
             'secret'       :   'P606', # Authenticate password
             'displayname'  :   'P607', # Display Name (John Doe)
             'outboundproxy':   'P603', # Outbound Proxy
            },
            {'enable'       :   'P1701',# Enable account
             'accountname'  :   'P1717',# Account Name
             'sipserver'    :   'P1702',# SIP Server
             'sipid'        :   'P1704',# SIP User ID
             'authid'       :   'P1705',# Authenticate ID
             'secret'       :   'P1706',# Authenticate password
             'displayname'  :   'P1707',# Display Name (John Doe)
             'outboundproxy':   'P1703',# Outbound Proxy
            },
            {'enable'       :   'P1801',# Enable account
             'accountname'  :   'P1817',# Account Name
             'sipserver'    :   'P1802',# SIP Server
             'sipid'        :   'P1804',# SIP User ID
             'authid'       :   'P1805',# Authenticate ID
             'secret'       :   'P1806',# Authenticate password
             'displayname'  :   'P1807',# Display Name (John Doe)
             'outboundproxy':   'P1803',# Outbound Proxy
            },
        ]

        # Blank out all variables prior to assignment
        for i in range(0, min(len(varmap), stdvars['max_sip_accounts'])):
            vars[varmap[i]['enable']] = 0
            vars[varmap[i]['sipserver']] = stdvars['server_ip']
            vars[varmap[i]['outboundproxy']] = stdvars['server_ip']
            vars[varmap[i]['accountname']] = ''
            vars[varmap[i]['displayname']] = ''
            vars[varmap[i]['sipid']] = ''
            vars[varmap[i]['authid']] = ''
            vars[varmap[i]['secret']] = ''
        
        for i in range(0, min(len(varmap), len(stdvars['sip']))):
            vars[varmap[i]['enable']] = 1
            vars[varmap[i]['accountname']] = stdvars['sip'][i].description
            vars[varmap[i]['displayname']] = stdvars['sip'][i].description
            vars[varmap[i]['sipid']] = stdvars['sip'][i].extension
            vars[varmap[i]['authid']] = stdvars['sip'][i].account
            vars[varmap[i]['secret']] = stdvars['sip'][i].secret
        return vars
            
    def _encodeGrandstreamConfig(self, vars):
        # Encode configuration variables. The gnkey must be the last item in
        # order to prevent other variables from being followed by a null byte.
        payload = urllib.urlencode(vars) + '&gnkey=0b82'
        if (len(payload) & 1) != 0: payload = payload + '\x00'
        
        # Calculate block length in words, plus checksum
        length = 8 + len(payload) / 2
        binmac = self._mac.replace(':', '').lower().decode('hex')
        bindata = struct.pack('>LH6s', length, 0, binmac) + '\x0d\x0a\x0d\x0a' + payload
        wordsize = len(bindata) / 2
        checksum = 0x10000 - (sum(struct.unpack('>' + str(wordsize) +'H', bindata)) & 0xFFFF)
        bindata = struct.pack('>LH6s', length, checksum, binmac) + '\x0d\x0a\x0d\x0a' + payload
        
        return bindata
    