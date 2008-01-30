<?php
/*
    PrincipalFileLinksys nos retorna el contenido del archivo de configuracion de los EndPoint
    Linksys, para ello es necesario enviarle el UserID, Password.
*/
function PrincipalFileLinksys($DisplayName, $id_device, $secret, $ipAdressServer)
{
    $content="<flat-profile>
    <Resync_Periodic ua=\"na\">3600</Resync_Periodic>
    <Proxy_1_ ua=\"na\">$ipAdressServer</Proxy_1_>
    <Outbound_Proxy_1_ ua=\"na\">$ipAdressServer</Outbound_Proxy_1_>
    <Primary_NTP_Server ua=\"na\">$ipAdressServer</Primary_NTP_Server>
    <Profile_Rule ua=\"na\">tftp://$ipAdressServer/spa\$MA.cfg</Profile_Rule>
 <!-- Subscriber Information -->
    <Text_Logo group=\"Phone/General\">$DisplayName</Text_Logo>
    <Station_Name group=\"Phone/General\">$DisplayName</Station_Name>
    <Voice_Mail_Number group=\"Phone/General\"></Voice_Mail_Number>
    <Display_Name_1_ ua=\"na\">$DisplayName</Display_Name_1_>
    <Short_Name_1_ ua=\"na\">$id_device</Short_Name_1_> 
    <User_ID_1_ ua=\"na\">$id_device</User_ID_1_>
    <Password_1_ ua=\"na\">$secret</Password_1_>
 <!-- Speed Dial -->
    <Speed_Dial_2 ua=\"rw\"/>
    <Speed_Dial_3 ua=\"rw\"/>
    <Speed_Dial_4 ua=\"rw\"/>
    <Speed_Dial_5 ua=\"rw\"/>
    <Speed_Dial_6 ua=\"rw\"/>
    <Speed_Dial_7 ua=\"rw\"/>
    <Speed_Dial_8 ua=\"rw\"/>
    <Speed_Dial_9 ua=\"rw\"/>
</flat-profile>";

    return $content;
}
?>