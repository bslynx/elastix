<script type="text/javascript">
 
var serv_date2=new Date({$CURRENT_DATETIME});
{literal}
function formatear_long2(val){
var salida2=(val.toString().length==1)? "0"+val : val;
return salida2;
}

function displaytime2(){
serv_date2.setSeconds(serv_date2.getSeconds()+1);

var datestring2=formatear_long2(serv_date2.getDate())+", "+serv_date2.getFullYear();
var timestring2=formatear_long2(serv_date2.getHours())+":"+formatear_long2(serv_date2.getMinutes())+":"+formatear_long2(serv_date2.getSeconds())

document.getElementById("SERVER_TIME").innerHTML=datestring2+" "+timestring2;
//document.getElementById("servertime").innerHTML=datestring2+" "+timestring2;
}
{/literal}

setInterval("displaytime2()", 1000);


</script>


<form action="#" method="POST">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/clock.gif" border="0" align="absmiddle">&nbsp;&nbsp;{$title}</td>
</tr>
<tr>
  <td>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
            <td align='left'><input type="submit" name="Actualizar" value="{$INDEX_ACTUALIZAR}" onClick="return confirm('{$TIME_MSG_1}');" /></td>
          </tr>
     </table>
</td>
</tr>
  <tr>
    <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">

          <tr> 
            <td width="15%"><b>{$INDEX_HORA_SERVIDOR}:</b></td>
            <td>{$MES_ACTUAL} <span id="SERVER_TIME" align='right'></span></td>
          </tr>
          <tr>
            <td width="15%"><b>{$TIME_NUEVA_FECHA}:</b></td>         
            <td><input type="text" name="date" id="datepicker" value="{$CURRENT_DATE}" style = "width: 10em; color: rgb(136, 68, 0); background-color: rgb(250, 250, 250); border: 1px solid rgb(153, 153, 153); text-align: center;" READONLY>
          <!--  <td>{html_select_date prefix="ServerDate_" start_year="2000" end_year="+10" field_order="YMD"}
            </td>-->
          </tr>
          <tr>
            <td width="15%"><b>{$TIME_NUEVA_HORA}:</b></td>
            <td>{html_select_time prefix="ServerDate_" }
            </td>
          </tr>
          <tr>
            <td width="15%"><b>{$TIME_NUEVA_ZONA}:</b></td>
            <td>{html_options name="TimeZone" selected=$ZONA_ACTUAL values=$LISTA_ZONAS output=$LISTA_ZONAS }
            </td>
          </tr>

        </table>
    </td>
  </tr>
  </table>
  <input type='hidden' name='configurar_hora' id='configurar_hora' value='0' />
</form>
