<?php
/* Copyright (C) 2015-2026		Charlene Benke		<charlene@patas-monkey.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file	   htdocs/githubprcount/index.php
 *  \ingroup	githubprcount
 *  \brief	  Page accueil de githubprcount
 */

$res=@include "../main.inc.php";					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include $_SERVER['DOCUMENT_ROOT']."/main.inc.php"; // Use on dev env only
if (! $res) $res=@include "../../main.inc.php";		// For "custom" directory

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

dol_include_once('/githubprcount/class/githubprcount.class.php');
dol_include_once('/githubprcount/core/lib/githubprcount.lib.php');



$result = restrictedArea($user, 'githubprcount', );
$langs->load("githubprcount@githubprcount");

$formfile = new FormFile($db);
$githubprcount_static = new Githubprcount($db);

$nbuserlimit=getDolGlobalString("githubprcount_nbuserlimit");
$year = date('Y');
$underlininglimit=getDolGlobalString("githubprcount_underlininglimit");
if (!empty($underlininglimit)) {
	//on convertie la liste des limites de soulignement en tableau par saut de ligne
	$underlininglimitArray = explode(PHP_EOL, $underlininglimit);
	$underArray = [];
	foreach($underlininglimitArray as $key => $value) {
		$color = explode(":", $value);
		$underArray[$color[0]] = trim($color[1]);
	}
} else {
	$underArray = [
			100 => '#FFD700',
			50 =>  '#C0C0C0', 
			25 =>  '#E6B17A', 
			10 =>  '#81D4FA', 
			1 =>   '#81C784'
		];
}

/*
 * View
 */

$transAreaType = $langs->trans("githubPRCountArea");
$helpurl='https://wiki.patas-monkey.com/index.php?title=githubprcount';

llxHeader("", $transAreaType, $helpurl);

print load_fiche_titre($transAreaType, '', "githubprcount@githubprcount");


$dirOutput = $conf->githubprcount->multidir_output[$conf->entity];

$filename = "pageresult";
$diroutputmassaction = $dirOutput.'/temp/massgeneration/'.$user->id;
$now = dol_now();
$file = $diroutputmassaction.'/'.$filename.'_'.dol_print_date($now, '%Y%m').'.html';

if (!file_exists($diroutputmassaction)) {
	if (dol_mkdir($diroutputmassaction) < 0) {
		$this->error = $langs->trans("ErrorCanNotCreateDir", $diroutputmassaction);
		return 0;
	}
}

// on créer le fichier d'export
$fileExport = fopen($file, "w");

$headfile = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Github PR Count</title>';

fwrite($fileExport, $headfile."\n");

$sideTable = "";
$sideTable.= '<table border="0" width="100%" class="notopnoleftnoright">';
$sideTable.= '<tr><td valign="top" width="30%" class="notopnoleft">';

$sideTable.= '<table class="noborder" width="100%">';
$sideTable.= '<tr><td valign="top" width="30%" class="notopnoleft">';

$nbopenmonth = $githubprcount_static->getElementCount("M");
$nbopenquarter = $githubprcount_static->getElementCount("Q");
$nbopenyear = $githubprcount_static->getElementCount("Y");
$nbopenrest = $githubprcount_static->getElementCount("R");

$sideTable.= "<table class='noborder centpercent'>\n";
$sideTable.= "<tr class='liste_titre'><th colspan=2>";
$sideTable.= img_picto($langs->trans("OpenPR"), 'fontawesome_github-alt_fab_green_1.2em');
$sideTable.= "&nbsp;".$langs->trans("OpenPR")."</th></tr>\n";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousMonth")."</td><td align=right>".$nbopenmonth."</td></tr>\n";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousQuarter")."</td><td align=right>".$nbopenquarter."</td></tr>\n";	
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousYear")."</td><td align=right>".$nbopenyear."</td></tr>\n";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousPeriod")."</td><td align=right>".$nbopenrest."</td></tr>\n";
$sideTable.= "</table>\n";


$nbopenmonth = $githubprcount_static->getElementCount("M", "issue");
$nbopenquarter = $githubprcount_static->getElementCount("Q", "issue");
$nbopenyear = $githubprcount_static->getElementCount("Y", "issue");
$nbopenrest = $githubprcount_static->getElementCount("R", "issue");


$sideTable.= "<table class='noborder centpercent'>\n";
$sideTable.= '<tr class="liste_titre"><th colspan=2>';
$sideTable.= img_picto($langs->trans("OpenIssue"), 'fontawesome_github_fab_green_1.2em');
$sideTable.= "&nbsp;".$langs->trans("OpenIssue")."</th></tr>\n";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousMonth")."</td><td align=right>".$nbopenmonth."</td></tr>\n";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousQuarter")."</td><td align=right>".$nbopenquarter."</td></tr>\n";	
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousYear")."</td><td align=right>".$nbopenyear."</td></tr>\n";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousPeriod")."</td><td align=right>".$nbopenrest."</td></tr>\n";
$sideTable.= "</table>\n";


$nbopenmonth = $githubprcount_static->getElementCount("M", "pr", "closed");
$nbopenquarter = $githubprcount_static->getElementCount("Q", "pr", "closed");
$nbopenyear = $githubprcount_static->getElementCount("Y", "pr", "closed");
$nbopenrest = $githubprcount_static->getElementCount("R", "pr", "closed");


$sideTable.= "<table class='noborder centpercent'>\n";
$sideTable.= '<tr class="liste_titre"><th colspan=2>';
$sideTable.= img_picto($langs->trans("ClosedPR"), 'fontawesome_github-alt_fab_red_1.2em');
$sideTable.= "&nbsp;".$langs->trans("ClosedPR")."</th></tr>\n";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousMonth")."</td><td align=right>".$nbopenmonth."</td></tr>";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousQuarter")."</td><td align=right>".$nbopenquarter."</td></tr>";	
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousYear")."</td><td align=right>".$nbopenyear."</td></tr>";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousPeriod")."</td><td align=right>".$nbopenrest."</td></tr>";
$sideTable.= "</table>\n";


$nbopenmonth = $githubprcount_static->getElementCount("M", "issue", "closed");
$nbopenquarter = $githubprcount_static->getElementCount("Q", "issue", "closed");
$nbopenyear = $githubprcount_static->getElementCount("Y", "issue", "closed");
$nbopenrest = $githubprcount_static->getElementCount("R", "issue", "closed");


$sideTable.= "<table class='noborder centpercent'>\n";
$sideTable.= '<tr class="liste_titre"><th colspan=2>';
$sideTable.= img_picto($langs->trans("ClosedIssue"), 'fontawesome_github_fab_red_1.2em');
$sideTable.= "&nbsp;".$langs->trans("ClosedIssue").'</th></tr>';
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousMonth")."</td><td align=right>".$nbopenmonth."</td></tr>";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousQuarter")."</td><td align=right>".$nbopenquarter."</td></tr>";	
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousYear")."</td><td align=right>".$nbopenyear."</td></tr>";
$sideTable.= '<tr><td align="left">'.$langs->trans("PreviousPeriod")."</td><td align=right>".$nbopenrest."</td></tr>";
$sideTable.= "</table>\n";


$sideTable.= '</td><td valign="top" width="70%" class="notopnoleftnoright">';
$nbStatus[] = 0;
$totalStatus[] = 0;
$nbType[] = 0;
$totalType[] = 0;

fwrite($fileExport, $sideTable."\n");
print $sideTable;

$tableList= "<table class='noborder' width='100%'>\n";
$tableList.= '<tr class="liste_titre"><td >'.$langs->trans("UserList").'</td>';
$tableList.= '<td align=right>'.$langs->trans("PR").' '.($year -2).'</td>';
$tableList.= '<td align=right>'.$langs->trans("PR").' '.($year -1).'</td>';
$tableList.= '<td align=right>'.$langs->trans("PR").' '.($year)."</td></tr>\n";

$contributors = $githubprcount_static->getContributors($nbuserlimit);

foreach ($contributors as $login => $contributor) {
	$colorRow = "#FFFFFF";
	foreach ($underArray as $limit => $color) {
		if ($contributor['contribsprev'] >= $limit) {
			$colorRow = $color;
			break;
		}
	}
	$statProducts= "<tr style='background:".$colorRow."'>";
	$statProducts.= '<td align=left valign="middle">';
	$statProducts.= $githubprcount_static->getNomUrl( $contributor, 1);
	//$statProducts.= '<img src="'.$contributor['avatar_url'].'" alt="'.$login.'" style="width:32px;height:32px;border-radius:50%;">';
	$statProducts.= "&nbsp;".$login;
	$statProducts.= "<br>".$contributor['contributions']." Commits";
	$statProducts.='</td>';
	$statProducts.= '<td align="right">'.$contributor['contribsprevprev'].'</td>';
	$statProducts.= '<td align="right">'.$contributor['contribsprev'].'</td>';
	$statProducts.= '<td align="right">'.$contributor['contribs'].'</td>';
	$statProducts.= "</tr>\n";

	$tableList.= $statProducts;
}


$tableList.= "</table>\n";
fwrite($fileExport, $tableList."\n");
print $tableList;

$urlsource = $_SERVER['PHP_SELF'];
$delallowed = 0;
// le lien pour télécharger le fichier d'export
print '<br>';
print $formfile->showdocuments(
		'massfilesarea_githubprcount', '', $diroutputmassaction.'/', $urlsource, 0, 
		$delallowed, '', 1, 1, 0, 48, 1, "", "", '', '', '', null, 0
	);

$tableList= "</td></tr></table>\n";

print $tableList;
fwrite($fileExport, $tableList."\n");
fclose($fileExport);

llxFooter();
