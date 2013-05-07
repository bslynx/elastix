<?php 

function templatesFileVoptech($ipAdressServer)
{

$content= <<<TEMP
<<VOIP CONFIG FILE>>Version:2.0002                            

<GLOBAL CONFIG MODULE>
Static IP          :192.168.1.179
Static NetMask     :255.255.255.0
Static GateWay     :192.168.1.1
Default Protocol   :2
Primary DNS        :202.96.134.133
Alter DNS          :202.96.128.68
DHCP Mode          :1
DHCP Dns           :1
Domain Name        :
Host Name          :VOIP
Pppoe Mode         :0
HTL Start Port     :10000
HTL Port Number    :200
SNTP Server        :209.81.9.7
Enable SNTP        :1
Time Zone          :56
Enable Daylight    :0
SNTP Time Out      :60
DayLight Shift Min :60
DayLight Start Mon :3
DayLight Start Week:5
DayLight Start Wday:0
DayLight Start Hour:2
DayLight Start Min :0
DayLight End Mon   :10
DayLight End Week  :5
DayLight End Wday  :0
DayLight End Hour  :2
DayLight End Min   :0
MMI Set            :-1
MTU Length         :1500
Register WD Time   :0

<LAN CONFIG MODULE>
Lan Ip             :192.168.10.1
Lan NetMask        :255.255.255.0
Bridge Mode        :0

<TELE CONFIG MODULE>
Dial End With #    :1
Dial Fixed Length  :0
Fixed Length       :11
Dial With Timeout  :1
Dial Timeout value :5
Dialpeer With Line :0
Poll Sequence      :0
Accept Any Call    :1
Phone Prefix       :
Local Area Code    :
IP call network    :.
--Port Config--    :
P1 No Disturb      :0
P1 Mute            :0
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
P1 Warm Line Time  :0
P1 Extention No.   :
P1 Auto HandDown   :0
P1 Auto Handdown Ti:3
P1 Hotline Num     :
P1 Record Server   :
P1 Enable Record   :0
P1 Busy N/A Line   :0

<DSP CONFIG MODULE>
Signal Standard    :1
Handdown Time      :200
G729 Payload Length:1
G723 Bit Rate      :1
G722 Timestamps    :0
VAD                :0
Ring Type          :1
Dtmf Payload Type  :101
Disable Handfree   :0
RTP PROBE          :0
--Port Config--    :
P1 Output Vol      :5
P1 Input Vol       :3
P1 HandFree Vol    :5
P1 RingTone Vol    :5
P1 Codec           :-1
P1 Voice Record    :0
P1 Record Playing  :1
P1 UserDef Voice   :0
P1 First Codec     :1
P1 Second Codec    :0
P1 Third Codec     :17
P1 Forth Codec     :15
P1 Fifth Codec     :23
P1 Sixth Codec     :9

<SIP CONFIG MODULE>
SIP  Port          :5060
Stun Address       :
Stun Port          :3478
Stun Effect Time   :50
SIP  Differv       :0
Extern Address     :
Url Convert        :1
Reg Retry Time     :30
Strict BranchPrefix:1
--SIP Line List--  :
SIP1 Phone Number  :
SIP1 Display Name  :
SIP1 Sip Name      :
SIP1 Register Addr :
SIP1 Register Port :5060
SIP1 Register User :
SIP1 Register Pwd  :
SIP1 Register TTL  :60
SIP1 Enable Reg    :0
SIP1 Proxy Addr    :$ipAdressServer
SIP1 Proxy Port    :5060
SIP1 Proxy User    :
SIP1 Proxy Pwd     :
SIP1 Signal Enc    :0
SIP1 Signal Key    :
SIP1 Media Enc     :0
SIP1 Media Key     :
SIP1 Local Domain  :
SIP1 Fwd Service   :0
SIP1 Ring Type     :0
SIP1 Fwd Number    :
SIP1 Hotline Number:
SIP1 Enable Detect :0
SIP1 Detect TTL    :60
SIP1 Server Type   :0
SIP1 User Agent    :Voip Phone 1.0
SIP1 PRACK         :0
SIP1 KEEP AUTH     :0
SIP1 Session Timer :0
SIP1 Gruu          :0
SIP1 DTMF Mode     :1
SIP1 DTMF SIP-INFO :0
SIP1 Use Stun      :0
SIP1 Via Port      :1
SIP1 Subscribe     :0
SIP1 Sub Expire    :300
SIP1 Single Codec  :0
SIP1 CLIR          :0
SIP1 Strict Proxy  :0
SIP1 Direct Contact:0
SIP1 History Info  :0
SIP1 DNS SRV       :0
SIP1 Transfer Expir:0
SIP1 Ban Anonymous :0
SIP1 Dial Without R:0
SIP1 DisplayName Qu:0
SIP1 Presence Mode :0
SIP1 RFC Ver       :1
SIP1 Signal Port   :0
SIP1 Transport     :0
SIP1 Use Mixer     :0
SIP1 Mixer Uri     :
SIP1 Long Contact  :0
SIP1 Auto TCP      :0
SIP1 Click to Talk :0
SIP1 Mwi No.       :
SIP1 Park No.      :
SIP1 Help No.      :
SIP2 Phone Number  :
SIP2 Display Name  :
SIP2 Sip Name      :
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
SIP2 Ring Type     :0
SIP2 Fwd Number    :
SIP2 Hotline Number:
SIP2 Enable Detect :0
SIP2 Detect TTL    :60
SIP2 Server Type   :0
SIP2 User Agent    :Voip Phone 1.0
SIP2 PRACK         :0
SIP2 KEEP AUTH     :0
SIP2 Session Timer :0
SIP2 Gruu          :0
SIP2 DTMF Mode     :1
SIP2 DTMF SIP-INFO :0
SIP2 Use Stun      :0
SIP2 Via Port      :1
SIP2 Subscribe     :0
SIP2 Sub Expire    :300
SIP2 Single Codec  :0
SIP2 CLIR          :0
SIP2 Strict Proxy  :0
SIP2 Direct Contact:0
SIP2 History Info  :0
SIP2 DNS SRV       :0
SIP2 Transfer Expir:0
SIP2 Ban Anonymous :0
SIP2 Dial Without R:0
SIP2 DisplayName Qu:0
SIP2 Presence Mode :0
SIP2 RFC Ver       :1
SIP2 Signal Port   :0
SIP2 Transport     :0
SIP2 Use Mixer     :0
SIP2 Mixer Uri     :
SIP2 Long Contact  :0
SIP2 Auto TCP      :0
SIP2 Click to Talk :0
SIP2 Mwi No.       :
SIP2 Park No.      :
SIP2 Help No.      :

<IAX2 CONFIG MODULE>
Server   Address   :$ipAdressServer
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
Pppoe Ip Address   :

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
Enable PVID        :0
PVID Value         :0

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
Enable DHCP Server :1
Enable DNS Relay   :1
DHCP Update Flag   :0
TFTP  Server       :0.0.0.0
--DHCP List--      :
Item1 name         :lan
Item1 Start Ip     :192.168.10.1
Item1 End Ip       :192.168.10.30
Item1 Param        :snmk=255.255.255.0:maxl=1440:rout=192.168.10.1:dnsv=192.168.10.1

<NAT CONFIG MODULE>
Enable Nat         :1
Enable Ftp ALG     :1
Enable H323 ALG    :0
Enable PPTP ALG    :1
Enable IPSec ALG   :1

<PHONE CONFIG MODULE>
Keypad Password    :123
KeyLock Password   :123
Enable KeyLock     :0
LCD Logo           :VOIP PHONE
LCD Constrast      :4
LCD Luminance      :1
Backlight Off Time :30
Time Display Style :0
Display Time       :1
Resolve Address    :
MWI Number         :
Phone Model        :VoIP Phone
About Info         :
Serivce URL        :
--Function Key--   :
Fkey1 Type         :4
Fkey1 Value        :F_MWI
Fkey1 Title        :
Fkey2 Type         :0
Fkey2 Value        :
Fkey2 Title        :
Fkey3 Type         :0
Fkey3 Value        :
Fkey3 Title        :
Fkey4 Type         :0
Fkey4 Value        :
Fkey4 Title        :
Fkey5 Type         :0
Fkey5 Value        :
Fkey5 Title        :
Fkey6 Type         :0
Fkey6 Value        :
Fkey6 Title        :
Fkey7 Type         :0
Fkey7 Value        :
Fkey7 Title        :
Fkey8 Type         :0
Fkey8 Value        :
Fkey8 Title        :
Fkey9 Type         :0
Fkey9 Value        :
Fkey9 Title        :
Fkey10 Type        :0
Fkey10 Value       :
Fkey10 Title       :
Fkey11 Type        :0
Fkey11 Value       :
Fkey11 Title       :
Memo Number        :0

<AUTOUPDATE CONFIG MODULE>
Download Username  :user
Download password  :pass
Download Server IP :$ipAdressServer
Config File Name   :
Config File Key    :
Download Protocol  :1
Download Mode      :0
Download Interval  :1
DHCP Option 66     :0

<VPN CONFIG MODULE>
VPN mode           :-1
L2TP LNS IP        :
L2TP User Name     :
L2TP Password      :
Enable VPN Tunnel  :0
VPN Server IP      :0.0.0.0
VPN Server Port    :80
Server Group ID    :VPN
Server Area Code   :12345
<<END OF FILE>>
TEMP;

    return $content;
}

function PrincipalFileVoptech($model,$tech,$DisplayName, $id_device, $secret, $arrParameters, $ipAdressServer, $macAdress, $versionCfg)
{
 $versionCfg = isset($versionCfg)?$versionCfg:'2.0002';
 $configNetwork ="";
 $ByDHCP = existsValueVoptech($arrParameters,'By_DHCP',"");
   // 1 indica que es por DHCP y 0 por estatico
if($ByDHCP==="0"){ // 0 es IP Estatica
$configNetwork ="
Static IP          :".existsValueVoptech($arrParameters,'IP','')."
Static NetMask     :".existsValueVoptech($arrParameters,'Mask','')."
Static GateWay     :".existsValueVoptech($arrParameters,'GW','')."
Primary DNS        :".existsValueVoptech($arrParameters,'DNS1','')."
Alter DNS          :".existsValueVoptech($arrParameters,'DNS2','')."
DHCP Mode          :$ByDHCP
DHCP Dns           :$ByDHCP
";
}
elseif($ByDHCP==="1"){ // 0 es IP Estatica
$configNetwork ="
DHCP Mode          :$ByDHCP
DHCP Dns           :$ByDHCP
";
}
else{ 
$configNetwork = "";
}
# CONTENT
   $content= "<<VOIP CONFIG FILE>>Version:$versionCfg 

<GLOBAL CONFIG MODULE>
$configNetwork

Default Protocol   :2

Time Zone          :".existsValueVoptech($arrParameters,'Time_Zone',12)."

<LAN CONFIG MODULE>
Bridge Mode        :".existsValueVoptech($arrParameters,'Bridge',1);

if ($tech == "iax2"){
$content.="
<SIP CONFIG MODULE>
--SIP Line List--  :
SIP1 Enable Reg    :0
SIP1 Mwi No.       :*97

<IAX2 CONFIG MODULE>
Server   Address   :$ipAdressServer
Server   Port      :4569
User     Name      :$id_device
User     Password  :$secret
User     Number    :$id_device
Voice    Number    :
Voice    Text      :mail
EchoTest Number    :1
EchoTest Text      :echo
Local    Port      :4569
Enable   Register  :1
Refresh  Time      :60
Enable   G.729     :0";
}

else{

$content.="
<SIP CONFIG MODULE>
SIP  Port          :5060
Stun Address       :
Stun Port          :3478
Stun Effect Time   :50
SIP  Differv       :0
Extern Address     :
Url Convert        :1
Reg Retry Time     :30
Strict BranchPrefix:1
--SIP Line List--  :
SIP1 Phone Number  :$id_device
SIP1 Display Name  :$DisplayName
SIP1 Sip Name      :
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
SIP1 Local Domain  :
SIP1 Fwd Service   :0
SIP1 Ring Type     :0
SIP1 Fwd Number    :
SIP1 Hotline Number:
SIP1 Enable Detect :0
SIP1 Detect TTL    :60
SIP1 Server Type   :0
SIP1 User Agent    :Voip Phone 1.0
SIP1 PRACK         :0
SIP1 KEEP AUTH     :0
SIP1 Session Timer :0
SIP1 Gruu          :0
SIP1 DTMF Mode     :1
SIP1 DTMF SIP-INFO :0
SIP1 Use Stun      :0
SIP1 Via Port      :1
SIP1 Subscribe     :0
SIP1 Sub Expire    :300
SIP1 Single Codec  :0
SIP1 CLIR          :0
SIP1 Strict Proxy  :0
SIP1 Direct Contact:0
SIP1 History Info  :0
SIP1 DNS SRV       :0
SIP1 Transfer Expir:0
SIP1 Ban Anonymous :0
SIP1 Dial Without R:0
SIP1 DisplayName Qu:0
SIP1 Presence Mode :0
SIP1 RFC Ver       :1
SIP1 Signal Port   :5060
SIP1 Transport     :0
SIP1 Use Mixer     :0
SIP1 Mixer Uri     :
SIP1 Long Contact  :0
SIP1 Auto TCP      :0
SIP1 Click to Talk :0
SIP1 Mwi No.       :*97
SIP1 Park No.      :
SIP1 Help No.      :

SIP2 Enable Reg    :0

<IAX2 CONFIG MODULE>
Enable   Register  :0";
}
switch($model){
	case "VI2006":
        	$content .= paramVoptechVI2006();
            	break;
	case "VI2007":
		$content .= paramVoptechVI2007();
            	break;
	default: 
		$content .= paramVoptechVI2008($tech);
		break;
}
$content.="
<DHCP CONFIG MODULE>
Enable DHCP Server :0
Enable DNS Relay   :0

<AUTOUPDATE CONFIG MODULE>
Download Username  :user
Download password  :pass
Download Server IP :$ipAdressServer
Config File Name   :$macAdress.cfg
Config File Key    :
Download Protocol  :2
Download Mode      :1
Download Interval  :1
DHCP Option 66     :0

<<END OF FILE>>";

    return $content;
}

function paramVoptechVI2006()
{
$content="
<PHONE CONFIG MODULE>
MWI Number         :*97";
return $content;
}

function paramVoptechVI2007()
{
$content="
<PHONE CONFIG MODULE>
MWI Number         :*97
--Function Key--   :
Fkey1 Type         :4
Fkey1 Value        :F_MWI";
return $content;
}

function paramVoptechVI2008($tech)
{
$content="
<PHONE CONFIG MODULE>
MWI Number         :*97
--Function Key--   :
Fkey1 Type         :2";
if ($tech == "iax2"){
$content.="
Fkey1 Value        :IAX2";
}
else{
$content.="
Fkey1 Value        :SIP1";
}
return $content;
}

function getNonceVOPTECH($ip)
{
	$token = null;
	while(true){
	    $home_html = file_get_contents("http://$ip");
	    if($home_html === false) break;

	    if(preg_match("/<input type=\"hidden\" name=\"nonce\" value=\"([0-9a-zA-Z]+)\">/",$home_html,$arrTokens)){
		if(isset($arrTokens[1])){
		    $token = $arrTokens[1];
		    break;
		}
	    }
	}
	return $token;
}

//Funcion para obtener el codigo html de la pagina inicial luego del login
function getInitialPageVOPTECH($ip, $nonce, $user, $passwd)
{	
	$respuesta = null;
	$encoded   = "$user:".md5("$user:$passwd:$nonce");
	$conexion  = @fsockopen($ip,80);
	if ($conexion) {
	   $dataSend = "encoded=$encoded&nonce=$nonce&goto=Logon&URL=/";
	   $dataLenght = strlen($dataSend);
	   $headerRequest = createHeaderHttpVOPTECH($ip,"/",$dataLenght,$nonce);
	   fputs($conexion,$headerRequest.$dataSend);
	   while(($r = fread($conexion,2048)) != ""){
		//if($getOnlyVersion){
		  //  if(preg_match("/<<VOIP CONFIG FILE>>Version:([2-9]{1}\.[0-9]{4})/",$r,$arrTokens)){
		    //    if(isset($arrTokens[1])){
		      //      $respuesta = $arrTokens[1];
		        //    break;
		        //}
		    //}
		//}
		//else
		    $respuesta .= $r;
	    if(preg_match('/<\/html>/',$r))
		break;
	   }
	   fclose($conexion);
	}
	else
	   echo "Error Conección $ip\n";
	return $respuesta;
}

function logoutVOPTECH($ip, $nonce)
{
	$respuesta = null;
	$conexion  = @fsockopen($ip,80);
	if ($conexion) {
	   $dataSend = "DefaultLogout=Logout";
	   $dataLenght = strlen($dataSend);
	   $headerRequest = createHeaderHttpVOPTECH($ip,"/LogOut.htm",$dataLenght,$nonce);
	   fputs($conexion,$headerRequest.$dataSend);
	   while(($r = fread($conexion,2048)) != ""){       
		if (preg_match('/200 OK/',$r,$match)){
		    $respuesta=$match[0];
		    break;
		}
	   }
	   fclose($conexion);
	}
	else
	   echo "Error Conección $ip\n";
	return $respuesta;
}

function createHeaderHttpVOPTECH($ip_remote, $file_remote, $dataLenght, $nonce)
{
	$headerRequest  = "POST $file_remote HTTP/1.1\r\n";
	$headerRequest .= "Host: $ip_remote\r\n";
	$headerRequest .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$headerRequest .= "Cookie: auth=$nonce\r\n";
	$headerRequest .= "Connection: keep-alive\r\n";
	$headerRequest .= "Content-Length: $dataLenght\r\n\r\n";

	return $headerRequest;
}

function getVersionConfigFileVOPTECH($ip, $user, $passwd)
{
	$nonce = getNonceVOPTECH($ip);
	usleep(500000);
	$fileC = getConfigFileVOPTECH($ip,$nonce,$user,$passwd,true);
	usleep(500000);
	$logou = logoutVOPTECH($ip,$nonce);
	return $fileC;
}

function getConfigFileVOPTECH($ip, $nonce, $user, $passwd, $getOnlyVersion = false)
{
	$respuesta = null;
	$encoded   = "$user:".md5("$user:$passwd:$nonce");
	$conexion  = @fsockopen($ip,80);

	if ($conexion) {
		$dataSend = "encoded=$encoded&nonce=$nonce&goto=Logon&URL=/";
		$dataLenght = strlen($dataSend);
		$headerRequest = createHeaderHttpVOPTECH($ip,"/config.txt",$dataLenght,$nonce);
		fputs($conexion,$headerRequest.$dataSend);

		while(($r = fread($conexion,2048)) != ""){
			if($getOnlyVersion){
				if(preg_match("/<<VOIP CONFIG FILE>>Version:([2-9]{1}\.[0-9]{4})/",$r,$arrTokens)){
					if(isset($arrTokens[1])){
						$respuesta = $arrTokens[1];
						break;
					}
				}
			}
			else
				$respuesta .= $r;
		}
		fclose($conexion);
	}
	else
		echo "Error Conección $ip\n";

		return $respuesta;
}

function arrVoptech($ipAdressServer, $macAdress)
{
    $arrFanvil = array(
        "download"  => "tftp -ip $ipAdressServer -file $macAdress.cfg",
        "save"      => "",
	"reload"    => "",
    );

    return $arrFanvil;
}

function existsValueVoptech($arr, $key, $default)
{
    if(isset($arr[$key])){
        $value = trim($arr[$key]);
        if($value != "") return $value;
        else return $default;
    }
    else return $default;
}
/////////////////////////////////////////////////////////////////////////////////////////////////////



