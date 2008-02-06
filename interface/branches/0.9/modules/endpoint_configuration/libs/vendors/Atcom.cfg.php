<?php
/*
    PrincipalFileAtcom nos retorna el contenido del archivo de configuracion de los EndPoint
    Atcom, para ello es necesario enviarle el DisplayName, id_device, secret, ipAdressServer, mac_adress.
*/
function PrincipalFileAtcom($DisplayName, $id_device, $secret, $ipAdressServer, $macAdress)
{
    $content=
"<<VOIP CONFIG FILE>>Version:2.0002

<GLOBAL CONFIG MODULE>
DHCP Mode          :1
SNTP Server        :$ipAdressServer
Enable SNTP        :1

<DHCP CONFIG MODULE>
DHCP Update Flag   :1
TFTP  Server       :$ipAdressServer

<SIP CONFIG MODULE>
SIP  Port          :5060
Stun Address       :
Stun Port          :3478
Stun Effect Time   :50
SIP  Differv       :0
DTMF Mode          :1
Extern Address     :
Url Convert        :1
--SIP Line List--  :
SIP1 Phone Number  :$id_device
SIP1 Display Name  :$DisplayName
SIP1 Register Addr :$ipAdressServer
SIP1 Register Port :5060
SIP1 Register User :$id_device
SIP1 Register Pwd  :$secret
SIP1 Register TTL  :60
SIP1 Enable Reg    :1
SIP1 Proxy Addr    :$ipAdressServer
SIP1 Proxy Port    :5060
SIP1 Proxy User    :$id_device
SIP1 Proxy Pwd     :$secret
SIP1 Signal Enc    :0
SIP1 Signal Key    :
SIP1 Media Enc     :0
SIP1 Media Key     :
SIP1 Local Domain  :$ipAdressServer
SIP1 Fwd Service   :0
SIP1 Fwd Number    :
SIP1 Enable Detect :0
SIP1 Detect TTL    :60
SIP1 Server Type   :0
SIP1 User Agent    :Voip Phone 1.0
SIP1 PRACK         :1
SIP1 KEEP AUTH     :1
SIP1 Session Timer :0
SIP1 DTMF Mode     :0
SIP1 Use Stun      :0
SIP1 Via Port      :1
SIP1 Subscribe     :0
SIP1 Sub Expire    :300
SIP1 Single Codec  :0
SIP1 RFC Ver       :1
SIP1 Use Mixer     :0
SIP1 Mixer Uri     :

<AUTOUPDATE CONFIG MODULE>
Download Username  :user
Download password  :pass
Download Server IP :$ipAdressServer
Config File Name   :atc$macAdress.cfg
Config File Key    :
Download Protocol  :2
Download Mode      :1
Download Interval  :1
<<END OF FILE>>";

    return $content;
}

function templatesFileAtcom($ipAdressServer)
{
    $content= <<<TEMP
<<VOIP CONFIG FILE>>Version:2.0002                            

<GLOBAL CONFIG MODULE>
Static IP          :
Static NetMask     :
Static GateWay     :
Default Protocol   :2
Primary DNS        :
Alter DNS          :
DHCP Mode          :1
Domain Name        :
Host Name          :VOIP
Pppoe Mode         :0
HTL Start Port     :10000
HTL Port Number    :200
SNTP Server        :$ipAdressServer
Enable SNTP        :1
Time Zone          :22
Enable Daylight    :0
SNTP Time Out      :60
MMI Set            :1

<LAN CONFIG MODULE>
Lan Ip             :192.168.10.1
Lan NetMask        :255.255.255.0
Bridge Mode        :1

<TELE CONFIG MODULE>
Dial End With #    :1
Dial Fixed Length  :0
Fixed Length       :11
Dial With Timeout  :1
Dial Timeout value :3
Poll Sequence      :0
Accept Any Call    :1
Phone Prefix       :
Local Area Code    :
IP call network    :.
--Port Config--    :
P1 No Disturb      :0
P1 No Dial Out     :0
P1 No Empty Calling:0
P1 Enable CallerId :1
P1 Forward Service :0
P1 SIP TransNum    :
P1 SIP TransAddr   :
P1 SIP TransPort   :5060
P1 CallWaiting     :1
P1 CallTransfer    :1
P1 Call3Way        :1
P1 AutoAnswer      :0
P1 No Answer Time  :20
P1 Extention No.   :
P1 Hotline Num     :
P1 Record Server   :
P1 Enable Record   :0
P1 Busy N/A Line   :0

<DSP CONFIG MODULE>
Signal Standard    :11
Handdown Time      :200
G729 Payload Length:1
VAD                :1
Ring Type          :1
Dtmf Payload Type  :101
Disable Handfree   :0
--Port Config--    :
P1 Output Vol      :1
P1 Input Vol       :1
P1 HandFree Vol    :2
P1 RingTone Vol    :6
P1 Codec           :17
P1 Voice Record    :0
P1 Record Playing  :0
P1 UserDef Voice   :0

<SIP CONFIG MODULE>
SIP  Port          :5060
Stun Address       :
Stun Port          :3478
Stun Effect Time   :50
SIP  Differv       :0
DTMF Mode          :1
Extern Address     :
Url Convert        :1
--SIP Line List--  :
SIP1 Phone Number  :
SIP1 Display Name  :
SIP1 Register Addr :$ipAdressServer
SIP1 Register Port :5060
SIP1 Register User :
SIP1 Register Pwd  :
SIP1 Register TTL  :60
SIP1 Enable Reg    :1
SIP1 Proxy Addr    :$ipAdressServer
SIP1 Proxy Port    :5060
SIP1 Proxy User    :
SIP1 Proxy Pwd     :
SIP1 Signal Enc    :0
SIP1 Signal Key    :
SIP1 Media Enc     :0
SIP1 Media Key     :
SIP1 Local Domain  :$ipAdressServer
SIP1 Fwd Service   :0
SIP1 Fwd Number    :
SIP1 Enable Detect :0
SIP1 Detect TTL    :60
SIP1 Server Type   :0
SIP1 User Agent    :Voip Phone 1.0
SIP1 PRACK         :1
SIP1 KEEP AUTH     :1
SIP1 Session Timer :0
SIP1 DTMF Mode     :0
SIP1 Use Stun      :0
SIP1 Via Port      :1
SIP1 Subscribe     :0
SIP1 Sub Expire    :300
SIP1 Single Codec  :0
SIP1 RFC Ver       :1
SIP1 Use Mixer     :0
SIP1 Mixer Uri     :
SIP2 Phone Number  :
SIP2 Display Name  :
SIP2 Register Addr :
SIP2 Register Port :5060
SIP2 Register User :
SIP2 Register Pwd  :
SIP2 Register TTL  :60
SIP2 Enable Reg    :0
SIP2 Proxy Addr    :
SIP2 Proxy Port    :5060
SIP2 Proxy User    :
SIP2 Proxy Pwd     :
SIP2 Signal Enc    :0
SIP2 Signal Key    :
SIP2 Media Enc     :0
SIP2 Media Key     :
SIP2 Local Domain  :
SIP2 Fwd Service   :0
SIP2 Fwd Number    :
SIP2 Enable Detect :0
SIP2 Detect TTL    :60
SIP2 Server Type   :0
SIP2 User Agent    :Voip Phone 1.0
SIP2 PRACK         :1
SIP2 KEEP AUTH     :1
SIP2 Session Timer :0
SIP2 DTMF Mode     :0
SIP2 Use Stun      :0
SIP2 Via Port      :1
SIP2 Subscribe     :0
SIP2 Sub Expire    :300
SIP2 Single Codec  :0
SIP2 RFC Ver       :1
SIP2 Use Mixer     :0
SIP2 Mixer Uri     :

<IAX2 CONFIG MODULE>
Server   Address   :
Server   Port      :4569
User     Name      :
User     Password  :
User     Number    :
Voice    Number    :0
Voice    Text      :mail
EchoTest Number    :1
EchoTest Text      :echo
Local    Port      :4569
Enable   Register  :0
Refresh  Time      :60
Enable   G.729     :0

<PPPoE CONFIG MODULE>
Pppoe User         :user123
Pppoe Password     :password
Pppoe Service      :ANY

<MMI CONFIG MODULE>
Telnet Port        :23
Web Port           :80
Remote Control     :1
Enable MMI Filter  :0
Telnet Prompt      :
--MMI Account--    :
Account1 Name      :admin
Account1 Pass      :admin
Account1 Level     :10
Account2 Name      :guest
Account2 Pass      :guest
Account2 Level     :5

<QOS CONFIG MODULE>
Enable VLAN        :0
Enable diffServ    :0
DiffServ Value     :184
VLAN ID            :256
802.1P Value       :0
VLAN Recv Check    :1
Data VLAN ID       :254
Data 802.1P Value  :0
Diff Data Voice    :0

<DEBUG CONFIG MODULE>
MGR Trace Level    :0
SIP Trace Level    :0
IAX Trace Level    :0
Trace File Info    :0

<AAA CONFIG MODULE>
Enable Syslog      :0
Syslog address     :0.0.0.0
Syslog port        :514

<ACCESS CONFIG MODULE>
Enable In Access   :0
Enable Out Access  :0

<DHCP CONFIG MODULE>
Enable DHCP Server :0
Enable DNS Relay   :0
DHCP Update Flag   :0
TFTP  Server       :0.0.0.0
--DHCP List--      :
Item1 name         :lan
Item1 Start Ip     :192.168.10.2
Item1 End Ip       :192.168.10.50
Item1 Param        :snmk=255.255.255.0:maxl=1440:rout=192.168.10.1:dnsv=192.168.10.1

<NAT CONFIG MODULE>
Enable Nat         :0
Enable Ftp ALG     :1
Enable H323 ALG    :0
Enable PPTP ALG    :1
Enable IPSec ALG   :1

<PHONE CONFIG MODULE>
Keypad Password    :123
LCD Logo           :VOIP PHONE
Memory Key 1       :
Memory Key 2       :
Memory Key 3       :
Memory Key 4       :
Memory Key 5       :
Memory Key 6       :
Memory Key 7       :
Memory Key 8       :
Memory Key 9       :
Memory Key 10      :

<AUTOUPDATE CONFIG MODULE>
Download Username  :user
Download password  :pass
Download Server IP :$ipAdressServer
Config File Name   :atc\$MAC.cfg
Config File Key    :
Download Protocol  :2
Download Mode      :1
Download Interval  :1

<VPN CONFIG MODULE>
VPN mode           :0
L2TP LNS IP        :
L2TP User Name     :
L2TP Password      :
Enable VPN Tunnel  :0
VPN Server IP      :0.0.0.0
VPN Server Port    :80
Server Group ID    :VPN
Server Area Code   :12345
<<END OF FILE>>softkey3 value: *79
TEMP;

    return $content;
}
?>