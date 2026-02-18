<?php
/* Copyright (C) 2014-2024	Charlene BENKE	<charlene@patas-monkey.com>
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
 * or see http://www.gnu.org/
 */

/**
 *		\file	   htdocs/customtooltip/core/lib/customtooltip.lib.php
 *		\brief	  Ensemble de fonctions de base pour customtooltip
 */


/**
 *  Return array head with list of tabs to view object informations
 *
 *  @param	Object	$appliname		 Member
 *  @return array		   		head
 */
function getChangeLog($appliname)
{
	global $langs;

	$urlmonkey ="https://www.patas-monkey.com";
	$ret= '<table class="noborder" cellspacing="5">';
	$ret.='<tbody><tr class="liste_titre">';
	$ret.='<td rowspan="3" align="center"><a href="'.$urlmonkey.'">';
	$ret.='<img src="../img/patas-monkey_logo.png" alt="" /></a>';
	$ret.='<br/><b>'.$langs->trans("Slogan").'</b>';
	$ret.='</td>';

	$ret.='<td align="left" >';
	$ret.='<a href="'.$urlmonkey.'/formulaire-de-contact/" target="_blank" class="butAction"';
	$ret.=' style="text-align:left;min-width:180px">';
	$ret.= img_picto("Changelog", 'fontawesome_hand-holding-medical_fas_orange_1.5em');
	$ret.= "&nbsp;".$langs->trans("Supports");
	$ret.='</a>';

	$ret.='</td>';
	$ret.='<td rowspan="3" align="center">';
	$ret.='<b>'.$langs->trans("LienDolistore").'</b><br/>';
	$ret.='<a href="http://docs.patas-monkey.com/dolistore" target="_blank" >';
	$ret.='<img border="0" width="50%" src="'.DOL_URL_ROOT.'/theme/dolistore_logo.png">';
	$ret.='</a>';
	$ret.='</td></tr>';
	$ret.='<tr align="left" class="liste_titre">';

	$ret.='<td width="20%" align=left>';
	$ret.='<a href="'.$urlmonkey.'/boutique" target="_blank" class="butAction"';
	$ret.=' style="text-align:left;min-width:180px">';
	$ret.= img_picto("Boutique", 'fontawesome_shopping-cart_fas_orange_1.5em');
	$ret.= "&nbsp;".$langs->trans("Services");
	$ret.='</a>';

	$ret.='</td></tr>';
	$ret.='<tr align="center" class="liste_titre">';

	$ret.='<td align="left">';
	$ret.='<a href="https://wiki.patas-monkey.com/" target="_blank" class="butAction"';
	$ret.=' style="text-align:left;min-width:180px">';
	$ret.= img_picto("Boutique", 'fontawesome_book_fas_orange_1.5em');
	$ret.= "&nbsp;".$langs->trans("Documentations");
	$ret.='</a>';

	$ret.='</td></tr>';
	$ret.='</tbody>';
	$ret.='</table>';
	$ret.='<br><br>';

	print load_fiche_titre($langs->trans("Changelog"), "", "object_customtooltip.png@myclock");
	$ret.='<br>';

	$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
	$changelog = @file_get_contents(
			str_replace("www", "dlbdemo", $urlmonkey).'/htdocs/custom/'.$appliname.'/changelog.xml', false, $context
	);

	// not connected
	$tblversionslast=array();
	if ($changelog !== false) {
		$sxelast = simplexml_load_string(nl2br($changelog));
		if ($sxelast !== false)
			$tblversionslast=$sxelast->Version;
	}
	libxml_use_internal_errors(true);
	$sxe = simplexml_load_string(nl2br(file_get_contents(dol_buildpath("/".$appliname, 0).'/changelog.xml')));
	$tblversions=array();
	if ($sxe !== false)
		$tblversions=$sxe->Version;

	$ret.='<table class="noborder" >';
	$ret.='<tr class="liste_titre">';
	$ret.='<th align=center width=100px>'.$langs->trans("NumberVersion").'</th>';
	$ret.='<th align=center width=100px>'.$langs->trans("MonthVersion").'</th>';
	$ret.='<th align=left >'.$langs->trans("ChangesVersion").'</th></tr>' ;

	if (count($tblversionslast) > count($tblversions)) {
		// il y a du nouveau
		for ($i = count($tblversionslast)-1; $i >=0; $i--) {
			$ret.= getLineVersion($tblversionslast[$i], " bgcolor=#FF6600 ", $sxe);
		}
	} elseif (count($tblversionslast) < count($tblversions) && count($tblversionslast) > 0) {
		// version expérimentale
		for ($i = count($tblversions)-1; $i >=0; $i--) {
			$ret.= getLineVersion($tblversions[$i], " bgcolor=lightgreen ", $sxelast);
		}
	} else {
		//on est à jour des versions ou pas de connection internet
		for ($i = count($tblversions)-1; $i >=0; $i--) {
			$ret.= getLineVersion($tblversions[$i]);
		}
	}
	$ret.='</table><br>';
	return $ret;
}

/**
 *  Return array head with list of tabs to view object informations
 *
 *  @param	Object	$tbllineversion		 Member
 *  @param	string	$selectColor	 Member
 *  @param	Object	$sxeInfo		 Member
 *  @return string	html line
 */
function getLineVersion($tbllineversion, $selectColor = "", $sxeInfo = null)
{
	$ret="<tr >";
	$color="";
	if (!empty($sxeInfo))
		if (empty($sxeInfo->xpath('//Version[@Number="'.$tbllineversion->attributes()->Number.'"]')))
			$color=$selectColor;
	$ret.='<td align=center '.$color.' valign=top>'.$tbllineversion->attributes()->Number.'</td>';
	$ret.='<td align=center '.$color.' valign=top>'.$tbllineversion->attributes()->MonthVersion.'</td>' ;
	$ret.='<td align=left '.$color.' valign=top>';
	foreach ($tbllineversion->change as $changeline)
		$ret.= $changeline->attributes()->type.'&nbsp;-&nbsp;'.$changeline.'<br>';
	$ret.='</td></tr>';
	return $ret;
}
