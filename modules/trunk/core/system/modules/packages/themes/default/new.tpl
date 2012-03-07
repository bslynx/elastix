    <table border='0' cellpadding='0' callspacing='0' width='100%' height='44'>
        <tr class="letra12">
            <td width='70%'>{$nombre_paquete.LABEL} &nbsp; {$nombre_paquete.INPUT}
                <input type='submit' class='button' name='submit_nombre' value='{$Search}' />                
            </td>
            <td rowspan='2' id='relojArena'> 
            </td>
        </tr>
        <tr class="letra12">
            <td width='200'>{$submitInstalado.LABEL} &nbsp; {$submitInstalado.INPUT}</td>
        </tr>
    </table>
    <input type='hidden' id='estaus_reloj' value='apagado' />
{literal}
<script type='text/javascript'>
    function mostrarReloj()
    {
        var nodoReloj = document.getElementById('relojArena');
        var estatus   = document.getElementById('estaus_reloj');
        if(estatus.value=='apagado'){
            estatus.value='prendido';
            nodoReloj.innerHTML = "<img src='modules/packages/images/hourglass.gif' align='absmiddle' /> <br /> <font style='font-size:12px; color:red'>{/literal}{$UpdatingRepositories}{literal}...</font>";
            $("#neo-table-header-filterrow").data("neo-table-header-filterrow-status", "hidden");
            $("#neo-tabla-header-row-filter-1").click();
            xajax_actualizarRepositorios();
        }
        else alert("{/literal}{$accionEnProceso}{literal}");
    }
    function installPackage(paquete)
    {
        var nodoReloj = document.getElementById('relojArena');
        var estatus   = document.getElementById('estaus_reloj');
        if(estatus.value=='apagado'){
            estatus.value='prendido';
            nodoReloj.innerHTML = "<img src='images/hourglass.gif' align='absmiddle' /> <br /> <font style='font-size:12px; color:red'>{/literal}{$InstallPackage}{literal}: "+ paquete +"...</font>";
            $("#neo-table-header-filterrow").data("neo-table-header-filterrow-status", "hidden");
            $("#neo-tabla-header-row-filter-1").click();
            xajax_installPaquete(paquete);
        }
        else alert("{/literal}{$accionEnProceso}{literal}");
    }
</script>
{/literal}
