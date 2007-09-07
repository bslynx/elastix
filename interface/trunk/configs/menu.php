<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: menu.php,v 1.2 2007/08/09 00:25:31 avivar Exp $ */

    $arrMenu = array( "system"      => array( "Name"     => "System",
                                              "IdParent" => ""),
                      "poper"       => array( "Name"     => "Operator Panel",
                                              "IdParent" => ""),
                      "voicemails"  => array( "Name"     => "Voicemails",
                                              "IdParent" => ""),
                      "fax"         => array( "Name"     => "Fax",
                                              "IdParent" => ""),
                      "reports"     => array( "Name"     => "Reports",
                                              "IdParent" => ""),
                      "billing"   => array( "Name"     => "Billing",
                                              "IdParent" => ""),
                      "email"   => array( "Name"     => "Email",
                                              "IdParent" => ""),
                      "extras"      => array( "Name"     => "Extras",
                                              "IdParent" => ""),
                      "downloads"   => array( "Name"     => "Downloads",
                                              "IdParent" => ""),
                      "developer"   => array( "Name"     => "Developer",
                                              "IdParent" => ""),
                      "sysinfo"     => array( "Name"     => "System Info",
                                              "Type"     => "module",
                                              "Link"     => "sysinfo",
                                              "IdParent" => "system"),
                      "freepbx"     => array( "Name"     => "PBX Configuration",
                                              "Type"     => "framed",
                                              "Link"     => "admin/config.php?type=setup&elastix_user=" . $_SESSION['elastix_user'] .
                                                            "&elastix_pass=" . $_SESSION['elastix_pass'],
                                              "IdParent" => "system"),
                      "fop"         => array( "Name"     => "Flash Operator Panel",
                                              "Type"     => "framed",
                                              "Link"     => "panel",
                                              "IdParent" => "poper"),
                      "ari"         => array( "Name"     => "Asterisk Recording Interface",
                                              "Type"     => "framed",
                                              "Link"     => "recordings",
                                              "IdParent" => "voicemails"),
                      "crm"         => array( "Name"     => "SugarCRM",
                                              "Type"     => "framed",
                                              "Link"     => "crm",
                                              "IdParent" => "extras"),
                      "faxlist"     => array( "Name"     => "Virtual Fax List",
                                              "Type"     => "module",
                                              "Link"     => "fax",
                                              "IdParent" => "fax"),
                      "faxnew"      => array( "Name"     => "New Virtual Fax",
                                              "Type"     => "module",
                                              "Link"     => "newFax",
                                              "IdParent" => "fax"),
                      "faxmaster"      => array( "Name"     => "Fax Master",
                                              "Type"     => "module",
                                              "Link"     => "faxMaster",
                                              "IdParent" => "fax"),
                      "sphones"     => array( "Name"     => "Softphones",
                                              "Type"     => "framed",
                                              "Link"     => "static/softphones.htm",
                                              "IdParent" => "downloads"),
                      "faxutils"    => array( "Name"     => "Fax Utilities",
                                              "Type"     => "framed",
                                              "Link"     => "static/faxutils.htm",
                                              "IdParent" => "downloads"),
                      "a2b"         => array( "Name"     => "Calling Cards",
                                              "Type"     => "framed",
                                              "Link"     => "a2billing",
                                              "IdParent" => "extras"),
                      "network"     => array( "Name"     => "Network",
                                              "Type"     => "module",
                                              "Link"     => "userlist",
                                              "IdParent" => "system"),
                      "userlist"    => array( "Name"     => "User Management",
                                              "Type"     => "module",
                                              "IdParent" => "system"),
                      "group_permission" => array( "Name"     => "Group Permission",
                                              "Type"     => "module",
                                              "IdParent" => "system"),
                      "language" => array( "Name"     => "Language",
                                              "Type"     => "module",
                                              "IdParent" => "system"),
                      "shutdown"     => array( "Name"     => "Shutdown",
                                              "Type"     => "module",
                                              "IdParent" => "system"),
                      "cdrreport"   => array( "Name"     => "CDR Report",
                                              "Type"     => "module",
                                              "IdParent" => "reports"),
                      "channelusage"=> array( "Name"     => "Channels Usage",
                                              "Type"     => "module",
                                              "IdParent" => "reports"),
                      "outgoingcalls"   => array( "Name" => "Outgoing Calls",
                                              "Type"     => "module",
                                              "IdParent" => "reports"),
                      "incomingcalls"   => array( "Name" => "Incoming Calls",
                                              "Type"     => "module",
                                              "IdParent" => "reports"),
                      "menuadmin"   => array( "Name"     => "Menu Administrator",
                                              "Type"     => "module",
                                              "IdParent" => "developer"),
                      "billing_rates"   => array( "Name" => "Rates",
                                              "Type"     => "module",
                                              "IdParent" => "billing"),
                      "billing_report"   => array( "Name" => "Billing Report",
                                              "Type"     => "module",
                                              "IdParent" => "billing"),
                      "dest_distribution"   => array( "Name" => "Destination Distribution",
                                              "Type"     => "module",
                                              "IdParent" => "billing"),
                      "billing_setup"   => array( "Name" => "Billing Setup",
                                              "Type"     => "module",
                                              "IdParent" => "billing"),
                      "email_domains"   => array( "Name" => "Domains",
                                              "Type"     => "module",
                                              "IdParent" => "email"),
                      "email_accounts"   => array( "Name" => "Accounts",
                                              "Type"     => "module",
                                              "IdParent" => "email"),
                      "email_relay"   => array( "Name" => "Relay",
                                              "Type"     => "module",
                                              "IdParent" => "email"),
                      "webmail"         => array( "Name"     => "Webmail",
                                              "Type"     => "framed",
                                              "Link"     => "mail",
                                              "IdParent" => "extras"),
                      "faxvisor"        => array( "Name"     => "Fax Visor", /*Bruno agrego modulo*/
                                              "Type"     => "module",
                                              "Link"     => "faxVisor",
                                              "IdParent" => "fax"),
                      );
?>
