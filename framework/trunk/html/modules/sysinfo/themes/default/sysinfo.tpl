<script type="text/javascript" src ="/libs/js/jquery/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="modules/{$module_name}/themes/js/jquery.color.js"></script>
<script type="text/javascript" src="modules/{$module_name}/themes/js/jquery.easing.1.3.js"></script>
  <link href="modules/{$module_name}/themes/css/mb.scrollable.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="modules/{$module_name}/themes/js/mbScrollable.js"></script>
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td width="50%">
			<div class="info_sys">
				<table width="99%" border="0" cellspacing="0" cellpadding="0" class="tabForm2">
					<tr class="moduleTitle">
						<td class="moduleTitle" valign="middle" colspan="2">&nbsp;&nbsp;<img src="images/memory.png" border="0" align="absmiddle">&nbsp;&nbsp;{$SYSTEM_INFO_TITLE1}</td>
					</tr>
					<tr class="tabForm">
						<td class="bordes_info">
							<table width="99%" border="0" cellspacing="5" cellpadding="0" class="tabForm2">
								<tr>
									<td height="37%">{$CPU_INFO_TITLE}: </td>
									<td>{$cpu_info}</td>
								</tr>
								<tr>
									<td height="38%">{$UPTIME_TITLE}:</td>
									<td>{$uptime}</td>
								</tr>
								<tr>
									<td>{$CPU_USAGE_TITLE}:</td>
									<td>{$cpu_usage}</td>
								</tr>
								<tr>
									<td>{$MEMORY_USAGE_TITLE}:</td>
									<td>{$mem_usage}</td>
								</tr>
								<tr>
									<td>{$SWAP_USAGE_TITLE}:</td>
									<td>{$swap_usage}</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>			
			</div>
		</td>
		<td width="50%">
			<div class="info_sys">
				<table width="99%" border="0" cellspacing="0" cellpadding="0" class="tabForm2">
					<tr class="moduleTitle">
						<td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="modules/{$module_name}/images/RSS.png" border="0" align="absmiddle">&nbsp;&nbsp;{$News}</td>
					</tr>
					<tr class="tabForm">
						<td class="bordes_info">
							<table width="99%" border="0" cellspacing="1" cellpadding="0" class="tabForm2">
								<tr>
									<td>
										<div id="wrapper">
											<div id="orizontal"><br><br>
												{$rss}
												<div id="controls">
													<!--<div class="first">first</div>-->
													<div class="prev">{$prev}</div>
													<div class="next">{$next}</div>
													<!--<div class="last">last</div><br>
													<div class="pageIndex"></div><br>
													<div class="start">start</div>
													<div class="stop">stop</div>-->
													<span style="cursor:pointer;color:red; font-weight:bold;" onclick="cambiarVertical();">{$show_vertical}</span><br>
												</div>
											</div>
											<div id="vertical" style="display:none;">
												{$rss2}
												<div id="controls1">
													<!--<div class="first">first</div>-->
													<div class="prev">{$prev}</div>
													<div class="next">{$next}</div>
													<!--<div class="last">last</div>
													<div class="pageIndex"></div>
													<div class="start">start</div>
													<div class="stop">stop</div>-->
													<span style="cursor:pointer;color:red; font-weight:bold;" onclick="cambiarHorizontal();">{$show_horizontal}</span><br>
												</div>
											</div>
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>
<br>
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr class="moduleTitle">
  <td class="moduleTitle" valign="middle">&nbsp;&nbsp;<img src="images/hd.png" border="0" align="absmiddle">&nbsp;&nbsp;{$SYSTEM_INFO_TITLE2}</td>
</tr>
<tr>
  <td>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
        {$info}
    </table>
  </td>
</tr>
</table>
