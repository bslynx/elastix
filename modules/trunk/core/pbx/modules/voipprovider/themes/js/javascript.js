/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function displayConfig(){
    var xhr = objAjax();
    var selec = document.getElementById("type_provider");
    var escogida=selec.options[selec.selectedIndex].text;
    
    if(selec.value!="none"){
        xhr.open("GET","modules/voipprovider/libs/controller.php?action=setConfig&type="+escogida,true);
        
        xhr.onreadystatechange = function()
        {
            controllerDisplayConfig(xhr);
        }
        xhr.send(null);  
    }else{
        //document.getElementById("configuration").innerHTML = "";
        document.getElementById("detail").style.display = "none";
        document.getElementById("username").value = "";
        document.getElementById("type").value= "";
        document.getElementById("secret").value = "";
        document.getElementById("qualify").value = "";
        document.getElementById("insecure").value = "";
        document.getElementById("host").value = "";
        document.getElementById("fromuser").value = "";
        document.getElementById("fromdomain").value = "";
        document.getElementById("dtmfmode").value = "";
        document.getElementById("disallow").value = "";
        document.getElementById("context").value = "";
        document.getElementById("allow").value = "";
        document.getElementById("trustrpid").value = "";
        document.getElementById("sendrpid").value = "";
        document.getElementById("canreinvite").value = "";
    }
}

function controllerDisplayConfig(xhr)
{
    if(xhr.readyState==4)
    {
        if(xhr.status==200)
        {
            var val = xhr.responseText;
            var parser=new DOMParser();
            xmlDoc=parser.parseFromString(val,"text/xml");
            document.getElementById("detail").style.display = "block";

            var db=xmlDoc.getElementsByTagName("configs");
            var attribute=db[0].getElementsByTagName("attribute");
            for(var i=0;i<attribute.length;i++)
            {
                var username=attribute[i].getElementsByTagName("username")[0];
                var type_=attribute[i].getElementsByTagName("type")[0];
                var secret=attribute[i].getElementsByTagName("secret")[0];
                var qualify=attribute[i].getElementsByTagName("qualify")[0];
                var insecure=attribute[i].getElementsByTagName("insecure")[0];
                var host_=attribute[i].getElementsByTagName("host")[0];
                var fromuser=attribute[i].getElementsByTagName("fromuser")[0];
                var fromdomain=attribute[i].getElementsByTagName("fromdomain")[0];
                var dtmfmode=attribute[i].getElementsByTagName("dtmfmode")[0];
                var disallow=attribute[i].getElementsByTagName("disallow")[0];
                var context=attribute[i].getElementsByTagName("context")[0];
                var allow=attribute[i].getElementsByTagName("allow")[0];
                var trustrpid=attribute[i].getElementsByTagName("trustrpid")[0];
                var sendrpid=attribute[i].getElementsByTagName("sendrpid")[0];
                var canreinvite=attribute[i].getElementsByTagName("canreinvite")[0];
            }
            if(username != null){
                document.getElementById("username").value = username.firstChild.nodeValue;
            }else document.getElementById("username").value = "";
            if(type_ != null){ 
                document.getElementById("type").value = type_.firstChild.nodeValue;
                document.getElementById("text_type").style.display = "block";
            }else{ 
                document.getElementById("type").value= "";
                document.getElementById("text_type").style.display = "none";
            }
            if(secret != null){ 
                document.getElementById("secret").value = secret.firstChild.nodeValue;
            }else document.getElementById("secret").value = "";
            if(qualify != null){ 
                document.getElementById("qualify").value = qualify.firstChild.nodeValue;
                document.getElementById("text_qualify").style.display = "block"; 
            }else{ 
                document.getElementById("qualify").value = "";
                document.getElementById("text_qualify").style.display = "none";
            }
            if(insecure != null){ 
                document.getElementById("insecure").value = insecure.firstChild.nodeValue;
                document.getElementById("text_insecure").style.display = "block"; 
            }else{ 
                document.getElementById("insecure").value = "";
                document.getElementById("text_insecure").style.display = "none";
            }
            if(host_ != null){ 
                document.getElementById("host").value = host_.firstChild.nodeValue;
                document.getElementById("text_host").style.display = "block";
            }else{ 
                document.getElementById("host").value = "";
                document.getElementById("text_host").style.display = "none";
            }if(fromuser != null){ 
                document.getElementById("fromuser").value = fromuser.firstChild.nodeValue;
                document.getElementById("text_fromuser").style.display = "block";
            }else{ 
                document.getElementById("fromuser").value = "";
                document.getElementById("text_fromuser").style.display = "none";
            }if(fromdomain != null){ 
                document.getElementById("fromdomain").value = fromdomain.firstChild.nodeValue;
                document.getElementById("text_fromdomain").style.display = "block";
            }else{ 
                document.getElementById("fromdomain").value = "";
                document.getElementById("text_fromdomain").style.display = "none";
            }if(dtmfmode != null){ 
                document.getElementById("dtmfmode").value = dtmfmode.firstChild.nodeValue;
                document.getElementById("text_dtmfmode").style.display = "block";
            }else{ 
                document.getElementById("dtmfmode").value = "";
                document.getElementById("text_dtmfmode").style.display = "none";
            }
            if(disallow != null){ 
                document.getElementById("disallow").value = disallow.firstChild.nodeValue;
                document.getElementById("text_disallow").style.display = "block";
            }else{ 
                document.getElementById("disallow").value = "";
                document.getElementById("text_disallow").style.display = "none";
            }
            if(context != null){ 
                document.getElementById("context").value = context.firstChild.nodeValue;
                document.getElementById("text_context").style.display = "block";
            }else{ 
                document.getElementById("context").value = "";
                document.getElementById("text_context").style.display = "none";
            }
            if(allow != null){ 
                document.getElementById("allow").value = allow.firstChild.nodeValue;
                document.getElementById("text_allow").style.display = "block";
            }else{ 
                document.getElementById("allow").value = "";
                document.getElementById("text_allow").style.display = "none";
            }
            if(trustrpid != null){ 
                document.getElementById("trustrpid").value = trustrpid.firstChild.nodeValue;
                document.getElementById("text_trustrpid").style.display = "block";
            }else{ 
                document.getElementById("trustrpid").value = "";
                document.getElementById("text_trustrpid").style.display = "none";
            }
            if(sendrpid != null){
                document.getElementById("sendrpid").value = sendrpid.firstChild.nodeValue;
                document.getElementById("text_sendrpid").style.display = "block";
            }else{ 
                document.getElementById("sendrpid").value = "";
                document.getElementById("text_sendrpid").style.display = "none";
            }
            if(canreinvite != null){
                document.getElementById("canreinvite").value = canreinvite.firstChild.nodeValue;
                document.getElementById("text_canreinvite").style.display = "block";
            }else{ 
                document.getElementById("canreinvite").value = "";
                document.getElementById("text_canreinvite").style.display = "none";
            }
            //var db = xhr.responseText;
            //document.getElementById("configuration").innerHTML = db;
        }
    }
}

function objAjax()
{
    var xmlhttp=false;
    try 
    {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    }
    catch(e) {
        try {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch(E) {
            xmlhttp = false;
	    }
    }
    if (!xmlhttp && typeof XMLHttpRequest!='undefined')
    {
        xmlhttp = new XMLHttpRequest();
    }
    return xmlhttp;
} 