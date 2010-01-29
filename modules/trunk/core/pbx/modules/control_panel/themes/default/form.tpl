<link   type="text/css"        href="/libs/js/jquery/css/ui-lightness/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
<script type="text/javascript" src ="/libs/js/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src ="/libs/js/jquery/js/jquery-ui-1.7.2.custom.min.js"></script>

<link rel="stylesheet"         href="modules/{$module_name}/themes/faq.css" type="text/css" />
<script type="text/javascript" src ="/modules/{$module_name}/themes/jquery.faq.js"></script>

<link   rel ="stylesheet"      href="modules/{$module_name}/themes/style.css" />
<script type="text/javascript" src ="/modules/{$module_name}/themes/javascript.js"></script>

<div id="contentWrap">
    <div id="content">

    <div id="tool"> 
        <input type="button" value="Reload" onclick="loadSizeArea()"/>
        <span id="timewaiting">Half waiting time Queue: </span>
        <span id="allqueue">Calls in Queue: {$total_queues}</span>
    </div>

    <ul id="sortable-list1">
    <div id="contentExtension">
        <li id="headExtension">
        {$descripArea1} 
        <table border ='0' cellspacing="0" cellpadding="0">
            <tr>
                {counter start=0 skip=1 print=false assign=cnt}
                {foreach key=ext item=ext_info name=arrExtInfo from=$arrDevicesExten}
                {math assign="p" equation="ceil($lengthExten/$size1)"}
                {if $cnt%$p==0}
                <td valign='top'>
                    <table border ='0' cellspacing="0" cellpadding="0">
                {/if}
                        <tr>
                            <td class="areaDrop" id="dev_{$ext}">
                              {if $ext_info.status eq 'on'}
                                <div class="item_box" id="ext_{$ext}" >
                              {elseif $ext_info.status eq 'off'}
                                <div class="item_box item_boxOff" id="ext_{$ext}" >
                              {/if}
                                    <div style='float:left;border: black solid 0px'>
                                        <a class="tooltipInfo" href="#"><img class="infor_box" src="modules/{$module_name}/images/info.png" /><span>{$ext_info.full_name}&nbsp;</span></a>
                                    </div>
                                    <div style='float:left;width:115px;text-align:left;padding-left:4px;'>
                                        <b>{$ext}:</b>&nbsp;{$ext_info.short_name}<br /><span class="monitor">&nbsp;&nbsp;</span><span class="monitor">&nbsp;</span>
                                    </div>
                                    <div style='border: black solid 0px; float:left'>
                                        {if $ext_info.voicemail eq 1}<a class="Ntooltip" href="#"><img id="mail_{$ext}" class="mail_box" src="modules/{$module_name}/images/mail.png"/><span>{$ext_info.voicemail_cnt}&nbsp;</span></a>{/if}
                                    </div>
                                    <div style='border: black solid 0px'>
                                        <img id="phone_{$ext}" class="phone_box" src="modules/{$module_name}/images/phhonez0.png" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                {if ($cnt+1)%$p==0 or $smarty.foreach.arrExtInfo.last}
                    </table>
                </td>
                {/if}
                {counter}
                {/foreach}
            </tr>
        </table>
        </li>
    </div><!--End ContentExtensions -->

    <div id="contentTrunks">
        <li id="headTrunks">
        {$descripArea6}
            <table border ='0' cellspacing="0" cellpadding="0">
                <tr>
                    {counter start=0 skip=1 print=false assign=cnt}
                    {foreach key=tru item=trun_info name=arrTrunkInfo from=$arrTrunks}
                    {math assign="p" equation="ceil($lengthTrunks/$size6)"}
                    {if $cnt%$p==0}
                    <td valign='top'>
                        <table border ='0' cellspacing="0" cellpadding="0">
                    {/if}
                            <tr>
                                <td >
                                    <div class="trunk_box" id="tru_{$trun_info}" >
                                        <div style='float:left;border: black solid 0px'>
                                            <a class="tooltipInfo" href="#"><img class="infor_box" src="modules/{$module_name}/images/info.png" /><span>{$trun_info}&nbsp;</span></a>
                                        </div>
                                        <div style='float:left;width:115px;text-align:left;padding-left:4px;' id="trunks">
                                            &nbsp;<b>{$trun_info}</b><br /><span class="monitor">&nbsp;&nbsp;</span><span class="monitor">&nbsp;</span>
                                        </div>
                                        <div style='border: black solid 0px'>
                                            <img id="trun_{$tru}" class="phone_boxtrunk" src="modules/{$module_name}/images/icon_trunk2.png" />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                    {if ($cnt+1)%$p==0  or $smarty.foreach.arrTrunkInfo.last}
                        </table>
                    </td>
                    {/if}
                    {counter}
                    {/foreach}
                </tr>
            </table>
        </li>
    </div><!--End ContentTrunks -->
    </ul>
    </div><!--End Content -->

    <dl id="faq">
        <ul id="sortable-list2" class="sortable">
            <li class="state1">
            <dt id="headArea1">{$descripArea2} -- {$lengthArea2} ext<span id="editArea2">[Edit Name]</span></dt>
            <dd id="contentArea1">
                <!--<div id="headArea1">{$descripArea2}  {$lengthArea2} Extensions<span id="editArea2">[Edit Name]</span></div>-->
                <!--pregunto si la tabla item_box es null o arrExtInfoA1 es vacia de iddevices entonces muestra-->
                {if $arrDevicesArea1==null}     
                <div id="subContent1" class="areaDropSub1"> </div>
                <div id="subcontent2" class="areaDropSub1"> </div> 
                {elseif $arrDevicesArea1!=null}
                <!--caso contrario sera una tabla con los devices que esten en dentro de la tabla item_box-->
                <table border ='0' cellspacing="0" cellpadding="0">
                    <tr>
                        {counter start=0 skip=1 print=false assign=cnt}
                        {foreach key=extA1 item=ext_infoA1 name=arrExtInfoA1 from=$arrDevicesArea1}
                        {math assign="p" equation="ceil($lengthArea2/$size2)"}
                        {if $cnt%$p==0}
                        <td valign='top'>
                            <table border ='0' cellspacing="0" cellpadding="0">
                        {/if}
                            <tr>
                                <td class="areaDrop" id="dev_{$extA1}" >
                                {if $ext_infoA1.status eq 'on'}
                                    <div class="item_box" id="ext_{$extA1}" >
                                {elseif $ext_infoA1.status eq 'off'}
                                    <div class="item_box item_boxOff" id="ext_{$extA1}" >
                                {/if}
                                        <div style='float:left;border: black solid 0px'>
                                            <a class="tooltipInfo" href="#"><img class="infor_box" src="modules/{$module_name}/images/info.png" /><span>{$ext_infoA1.full_name}&nbsp;</span></a>
                                        </div>
                                        <div style='float:left;width:115px;text-align:left;padding-left:4px;'>
                                            <b>{$extA1}:</b>&nbsp;{$ext_infoA1.short_name}<br /><span class="monitor">{$ext_infoA1.call_dstn}&nbsp;&nbsp;</span><span class="monitor">{$ext_infoA1.speak_time}&nbsp;</span>
                                        </div>
                                        <div style='float:left;border: black solid 0px'>
                                            {if $ext_infoA1.voicemail eq 1}<a class="Ntooltip" href="#"><img id="mail_{$extA1}" class="mail_box" src="modules/{$module_name}/images/mail.png" /><span>{$ext_infoA1.voicemail_cnt}&nbsp;</span></a>{/if}
                                        </div>
                                        <div style='border: black solid 0px'>
                                            <img id="phone_{$extA1}" class="phone_box" src="modules/{$module_name}/images/phhonez0.png" />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        {if ($cnt+1)%$p==0  or $smarty.foreach.arrExtInfoA1.last}
                            </table>
                        </td>
                        {/if}
                        {counter}
                        {/foreach}
                    </tr>
                </table>
                {/if}
            </dd>
            </li>
    
            <li class="state1">
            <dt id="headArea2">{$descripArea3} -- {$lengthArea3} ext<span id="editArea3">[Edit Name]</span></dt>
            <dd id="contentArea2">
                <!--<div id="headArea2">{$descripArea3}  {$lengthArea3} Extensions<span id="editArea3">[Edit Name]</span></div>-->
                {if $arrDevicesArea2==null}
                <div id="subContent1" class="areaDropSub2"> </div>
                <div id="subcontent2" class="areaDropSub2"> </div>
                {elseif $arrDevicesArea2!=null}
                <table border ='0' cellspacing="0" cellpadding="0">
                    <tr>
                        {counter start=0 skip=1 print=false assign=cnt}
                        {foreach key=extA2 item=ext_infoA2 name=arrExtInfoA2 from=$arrDevicesArea2}
                        {math assign="p" equation="ceil($lengthArea3/$size3)"}
                        {if $cnt%$p==0}
                        <td valign='top'>
                            <table border ='0' cellspacing="0" cellpadding="0">
                        {/if}
                            <tr>
                                <td class="areaDrop" id="dev_{$extA2}">
                                {if $ext_infoA2.status eq 'on'}
                                    <div class="item_box" id="ext_{$extA2}" >
                                {elseif $ext_infoA2.status eq 'off'}
                                    <div class="item_box item_boxOff" id="ext_{$extA2}" >
                                {/if}
                                        <div style='float:left;border: black solid 0px'>
                                            <a class="tooltipInfo" href="#"><img class="infor_box" src="modules/{$module_name}/images/info.png" /><span>{$ext_infoA2.full_name}&nbsp;</span></a>
                                        </div>
                                        <div style='float:left;width:115px;text-align:left;padding-left:4px;'>
                                            <b>{$extA2}:</b>&nbsp;{$ext_infoA2.short_name}<br /><span class="monitor">{$ext_infoA2.call_dstn}&nbsp;&nbsp;</span><span class="monitor">{$ext_infoA2.speak_time}&nbsp;</span>
                                        </div>
                                        <div style='float:left;border: black solid 0px'>
                                            {if $ext_infoA2.voicemail eq 1}<a class="Ntooltip" href="#"><img id="mail_{$extA2}" class="mail_box" src="modules/{$module_name}/images/mail.png" /><span>{$ext_infoA2.voicemail_cnt}&nbsp;</span></a>{/if}
                                        </div>
                                        <div style='border: black solid 0px'>
                                            <img id="phone_{$extA2}" class="phone_box" src="modules/{$module_name}/images/phhonez0.png" />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        {if ($cnt+1)%$p==0  or $smarty.foreach.arrExtInfoA2.last}
                            </table>
                        </td>
                        {/if}
                        {counter}
                        {/foreach}
                    </tr>
                </table>
                {/if}
            </dd>
            </li> 
    
            <li class="state1">
            <dt id="headArea3">{$descripArea4} -- {$lengthArea4} ext<span id="editArea4">[Edit Name]</span></dt>
            <dd id="contentArea3">
                <!--<div id="headArea3">{$descripArea4}  {$lengthArea4} Extensions<span id="editArea4">[Edit Name]</span></div>-->
                {if $arrDevicesArea3==null}
                <div id="subContent1" class="areaDropSub3"> </div>
                <div id="subcontent2" class="areaDropSub3"> </div>
                {elseif $arrDevicesArea3!=null}
                <table border ='0' cellspacing="0" cellpadding="0">
                    <tr>
                        {counter start=0 skip=1 print=false assign=cnt}
                        {foreach key=extA3 item=ext_infoA3 name=arrExtInfoA3 from=$arrDevicesArea3}
                        {math assign="p" equation="ceil($lengthArea4/$size4)"}
                        {if $cnt%$p==0}
                        <td valign='top'>
                            <table border ='0' cellspacing="0" cellpadding="0">
                        {/if}
                                <tr>
                                <td class="areaDrop" id="dev_{$extA3}">
                                {if $ext_infoA3.status eq 'on'}
                                    <div class="item_box" id="ext_{$extA3}" >
                                {elseif $ext_infoA3.status eq 'off'}
                                    <div class="item_box item_boxOff" id="ext_{$extA3}" >
                                {/if}
                                        <div style='float:left;border: black solid 0px'>
                                            <a class="tooltipInfo" href="#"><img class="infor_box" src="modules/{$module_name}/images/info.png" /><span>{$ext_infoA3.full_name}&nbsp;</span></a>
                                        </div>
                                        <div style='float:left;width:115px;text-align:left;padding-left:4px;'>
                                            <b>{$extA3}:</b>&nbsp;{$ext_infoA3.short_name}<br /><span class="monitor">{$ext_infoA3.call_dstn}&nbsp;&nbsp;</span><span class="monitor">{$ext_infoA3.speak_time}&nbsp;</span>
                                        </div>
                                        <div style='float:left;border: black solid 0px'>
                                            {if $ext_infoA3.voicemail eq 1}<a class="Ntooltip" href="#"><img id="mail_{$extA3}" class="mail_box" src="modules/{$module_name}/images/mail.png" /><span>{$ext_infoA3.voicemail_cnt}&nbsp;</span></a>{/if}
                                        </div>
                                        <div style='border: black solid 0px'>
                                            <img id="phone_{$extA3}" class="phone_box" src="modules/{$module_name}/images/phhonez0.png" />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        {if ($cnt+1)%$p==0  or $smarty.foreach.arrExtInfoA3.last}
                            </table>
                        </td>
                        {/if}
                        {counter}
                        {/foreach}
                    </tr>
                </table>
                {/if}
            </dd>
            </li>
    
            <li class="state1">
            <dt id="headQueues">{$descripArea5} </dt>
            <dd id="contentQueues">
                <!--<div id="headQueues">{$descripArea5} </div>-->
                <table border ='0' cellspacing="0" cellpadding="0">
                    <tr>
                        {counter start=0 skip=1 print=false assign=cnt}
                        {foreach key=que item=queu_info name=arrQueuInfo from=$arrQueues}
                        {math assign="p" equation="ceil($lengthQueues/$size5)"}
                        {if $cnt%$p==0}
                        <td valign='top'>
                            <table border ='0' cellspacing="0" cellpadding="0">
                        {/if}
                                <tr>
                                    <td >
                                        <div class="queue_box" id="que_{$que}" >
                                            <div style='float:left;border: black solid 0px'>
                                                <a class="tooltipInfo" href="#"><img class="infor_box" src="modules/{$module_name}/images/info.png" /><span>{$queu_info.members}&nbsp;</span></a>
                                            </div>
                                            <div style='float:left;width:115px;text-align:left;padding-left:4px;'>
                                                <b>{$queu_info.number}:</b>&nbsp;{$queu_info.number}<br />calls waiting:<span class="monitor">&nbsp;{$queu_info.queue_wait}&nbsp;</span>
                                            </div>
                                            <div style='border: black solid 0px'>
                                                <img id="phone_{$queu_info.number}" class="phone_boxqueue" src="modules/{$module_name}/images/icon_queue.png" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                        {if ($cnt+1)%$p==0  or $smarty.foreach.arrQueuInfo.last}
                            </table>
                        </td>
                        {/if}
                        {counter}
                        {/foreach}
                    </tr>
                </table>
            </dd>
            </li>
        </ul>
        
        <ul id="sortable-hidden" class="sortable">
            <li class="state2">
    
            </li>
        </ul>
    </dl>
    
</div> <!--End of the div contentWrap-->

<div id="layer1" class="move">
    <div class="layer_handle">            
        <a href="#" id="close1">[ x ]</a>
        Preferences
    </div>
    <div id="layer1_content">
        <form id="layer1_form" method="post" action="">
            <legend >Display Settings</legend><br />
            <table>
                <tr>
                    <td><label style='font-size: 11px'>Name:</label></td>
                    <td><input type="text" id="descrip1" name="descrip1" size="30" maxlength="79"><br /></td>
                </tr>
                <tr>
                    <td></td>
                    <td align="right">
                        <input type="button" value="Save" onclick="saveDescriptionArea1()"/>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<div id="layer2" class="move">
    <div class="layer_handle">            
        <a href="#" id="close2">[ x ]</a>
        Preferences
    </div>
    <div id="layer1_content">
        <form id="layer1_form" method="post" action="">
            <legend >Display Settings</legend><br />
            <table>
                <tr>
                    <td><label style='font-size: 11px'>Name:</label></td>
                    <td><input type="text" id="descrip2" name="descrip2" size="30" maxlength="79"><br /></td>
                </tr>
                <tr>
                    <td></td>
                    <td align="right">
                        <input type="button" value="Save" onclick="saveDescriptionArea2()"/>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<div id="layer3" class="move">
    <div class="layer_handle">
        <a href="#" id="close3">[ x ]</a>
        Preferences
    </div>
    <div id="layer1_content">
        <form id="layer1_form" method="post" action="">
            <legend >Display Settings</legend><br />
            <table>
                <tr>
                    <td><label style='font-size: 11px'>Name:</label></td>
                    <td><input type="text" id="descrip3" name="descrip3" size="30" maxlength="79"><br /></td>
                </tr>
                <tr>
                    <td></td>
                    <td align="right">
                        <input type="button" value="Save" onclick="saveDescriptionArea3()"/>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<input type="hidden" id="lengthA2" name="lengthA2" value="{$lengthArea2}"/>
<input type="hidden" id="lengthA3" name="lengthA3" value="{$lengthArea3}"/>
<input type="hidden" id="lengthA4" name="lengthA4" value="{$lengthArea4}"/>

<input type="hidden" id="nameArea1" name="nameArea1" value="{$nameA1}"/>
<input type="hidden" id="nameArea2" name="nameArea2" value="{$nameA2}"/>
<input type="hidden" id="nameArea3" name="nameArea3" value="{$nameA3}"/>
<input type="hidden" id="nameArea4" name="nameArea4" value="{$nameA4}"/>
<input type="hidden" id="nameArea5" name="nameArea5" value="{$nameA5}"/>
<input type="hidden" id="nameArea6" name="nameArea6" value="{$nameA6}"/>

<input type="hidden" id="heightA1" name="heightA1" value="{$height1}"/>
<input type="hidden" id="heightA2" name="heightA2" value="{$height2}"/>
<input type="hidden" id="heightA3" name="heightA3" value="{$height3}"/>
<input type="hidden" id="heightA4" name="heightA4" value="{$height4}"/>
<input type="hidden" id="heightA5" name="heightA5" value="{$height5}"/>
<input type="hidden" id="heightA6" name="heightA6" value="{$height6}"/>

<input type="hidden" id="widthA1" name="widthA1" value="{$width1}"/>
<input type="hidden" id="widthA2" name="widthA2" value="{$width2}"/>
<input type="hidden" id="widthA3" name="widthA3" value="{$width3}"/>
<input type="hidden" id="widthA4" name="widthA4" value="{$width4}"/>
<input type="hidden" id="widthA5" name="widthA5" value="{$width5}"/>
<input type="hidden" id="widthA6" name="widthA6" value="{$width6}"/>