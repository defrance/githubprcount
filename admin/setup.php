<?php
/* Copyright (C) 2014-2026		Charlene BENKE	<charlene@patas-monkey.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *  \file	   htdocs/githubprcount/admin/setup.php
 *  \ingroup	githubprcount
 *  \brief	  Page d'administration-configuration du module githubprcount
 */

$res=0;
if (! $res && file_exists("../../main.inc.php"))
	$res=@include "../../main.inc.php";					// For root directory
if (! $res && file_exists("../../../main.inc.php"))
	$res=@include "../../../main.inc.php";				// For "custom" directory

dol_include_once("/githubprcount/core/lib/githubprcount.lib.php");
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once DOL_DOCUMENT_ROOT."/core/class/html.formadmin.class.php";
require_once DOL_DOCUMENT_ROOT."/core/class/html.form.class.php";
require_once DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php";

$langs->load("admin");
$langs->load("other");
$langs->load("githubprcount@githubprcount");

// Security check
if (! $user->admin  ) accessforbidden();

$action = GETPOST('action', 'alpha');

$form = new Form($db);
/*
 * Actions
 */

// juste besoin de saisir le service associé au transport

if ($action == 'setvalue' ) {
	dolibarr_set_const($db, "githubprcount_githubtoken", GETPOST('githubtoken'), 'chaine', 0, '', $conf->entity);
	dolibarr_set_const($db, "githubprcount_owner", GETPOST('owner'), 'chaine', 0, '', $conf->entity);
	dolibarr_set_const($db, "githubprcount_application", GETPOST('application'), 'chaine', 0, '', $conf->entity);
	dolibarr_set_const($db, "githubprcount_nbuserlimit", GETPOST('nbuserlimit'), 'chaine', 0, '', $conf->entity);
	dolibarr_set_const($db, "githubprcount_sleeptimer", GETPOST('sleeptimer'), 'chaine', 0, '', $conf->entity);
	dolibarr_set_const($db, "githubprcount_underlininglimit", GETPOST('underlininglimit'), 'chaine', 0, '', $conf->entity);

	$mesg = "<font class='ok'>".$langs->trans("SettingSaved")."</font>";
}

$githubtoken=getDolGlobalString("githubprcount_githubtoken");
$owner=getDolGlobalString("githubprcount_owner");
$application=getDolGlobalString("githubprcount_application");
$nbuserlimit=getDolGlobalString("githubprcount_nbuserlimit");
$sleeptimer=getDolGlobalString("githubprcount_sleeptimer");
$underlininglimit=getDolGlobalString("githubprcount_underlininglimit");
/*
 * View
 */

$page_name = $langs->trans("githubprcountSetup") . " - " . $langs->trans("githubprcountGeneralSetting");
llxHeader('', $page_name);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($page_name, $linkback, 'title_setup');



$head = githubprcount_admin_prepare_head();

print dol_get_fiche_head($head, 'setup', $langs->trans("githubprcount"), -1, "githubprcount@githubprcount");

load_fiche_titre($langs->trans("githubprcountSettingValue"));
print '<br>';
print '<form method="post" action="setup.php">';
print '<input type="hidden" name="action" value="setvalue">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<table class="noborder" >';
print '<tr class="liste_titre">';
print '<td></td><td  align=left>'.$langs->trans("Description").'</td>';
print '<td align=center>'.$langs->trans("Value").'</td>';
print '</tr>'."\n";

print '<tr >';
print '<td width=20%  align=left>'.$langs->trans("GithubToken").'</td>';
print '<td align=left>'.$langs->trans("InfoGithubToken").'</td>';
print '<td  align=right>';
print '<br><input type="text" name="githubtoken" size="50" value="'.$githubtoken.'">';
print '</td></tr>'."\n";

print '<tr >';
print '<td width=20%  align=left>'.$langs->trans("OwnerApplication").'</td>';
print '<td align=left>'.$langs->trans("InfoOwnerApplication").'</td>';
print '<td nowrap align=right>';
print '<br><input type="text" name="owner" size="10" value="'.$owner.'"> &nbsp;/&nbsp;';
print '<input type="text" name="application" size="10" value="'.$application.'">';
print '</td></tr>'."\n";


print '<tr >';
print '<td width=20%  align=left>'.$langs->trans("UserLimitSleepTimer").'</td>';
print '<td align=left>'.$langs->trans("InfoUserLimitSleepTimer").'</td>';
print '<td align=right >';
print '<input type="text" name="nbuserlimit" size="4" value="'.$nbuserlimit.'"> &nbsp;';
print '<input type="text" name="sleeptimer" size="10" value="'.$sleeptimer.'">';
print '</td></tr>'."\n";


print '<tr >';
print '<td width=20%  align=left>'.$langs->trans("UnderliningLimit").'</td>';
print '<td align=left>'.$langs->trans("InfoUnderliningLimit").'</td>';
print '<td align=right >';
print '<textarea name="underlininglimit" rows="4" cols="60">'.$underlininglimit.'</textarea>';
print '</td></tr>'."\n";



print '<tr ><td colspan=2></td><td align=right>';
// Boutons d'action
//print '<div class="tabsAction">';
print '<input type="submit" class="butAction" value="'.$langs->trans("Modify").'">';
//print '</div>';
print '</td></tr>'."\n";
print '</table>';
print '</form>';

/*
 *  Infos pour le support
 */
print '<br>';
libxml_use_internal_errors(true);
$sxe = simplexml_load_string(nl2br(file_get_contents('../changelog.xml')));
if ($sxe === false) {
	echo "Erreur lors du chargement du XML\n";
	foreach (libxml_get_errors() as $error)
		print $error->message;
	exit;
} else
	$tblversions=$sxe->Version;

$currentversion = $tblversions[count($tblversions)-1];

print '<table class="noborder" width="100%">'."\n";
print '<tr class="liste_titre">'."\n";
print '<td width=20%>'.$langs->trans("SupportModuleInformation").'</td>'."\n";
print '<td>'.$langs->trans("Value").'</td>'."\n";
print "</tr>\n";
print '<tr><td >'.$langs->trans("DolibarrVersion").'</td><td>'.DOL_VERSION.'</td></tr>'."\n";
print '<tr><td >'.$langs->trans("ModuleVersion").'</td>';
print '<td>'.$currentversion->attributes()->Number." (".$currentversion->attributes()->MonthVersion.')</td></tr>'."\n";
print '<tr><td >'.$langs->trans("PHPVersion").'</td><td>'.version_php().'</td></tr>'."\n";
print '<tr><td >'.$langs->trans("DatabaseVersion").'</td>';
print '<td>'.$db::LABEL." ".$db->getVersion().'</td></tr>'."\n";
print '<tr><td >'.$langs->trans("WebServerVersion").'</td>';
print '<td>'.$_SERVER["SERVER_SOFTWARE"].'</td></tr>'."\n";
print '<tr>'."\n";
print '<td colspan="2">'.$langs->trans("SupportModuleInformationDesc").'</td></tr>'."\n";
print "</table>\n";

// Show messages
dol_htmloutput_mesg($mesg, '', 'ok');

// Footer
llxFooter();
$db->close();
