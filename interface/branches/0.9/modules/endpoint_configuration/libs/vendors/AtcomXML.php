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
Config File Name   :at$macAdress.cfg
Config File Key    :
Download Protocol  :2
Download Mode      :1
Download Interval  :1
<<END OF FILE>>";

    return $content;
}
?>