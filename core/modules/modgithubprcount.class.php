<?php
/* Copyright (C) 2014-2026	Charlene BENKE	<charlene@patas-monkey.com>
 * Module pour gerer la saisie pièces simplifiée
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
include_once DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php";

/**
 * 		\class	  modcustomline
 *	  \brief	  Description and activation class for module customLine
 */
class modgithubprcount extends DolibarrModules
{

	public $dolibarrminversion;
	public $dolistore_id;

	/**
	 *   \brief	  Constructor. Define names, constants, directories, boxes, permissions
	 *   @param	  DB $db	  Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		global $langs; // $conf,

		$langs->load('githubprcount@githubprcount');

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 160191;
		//$this->dolistore_id = 805;		// Id of module in Dolistore

		$this->editor_name = "<b>Patas-Monkey</b>";
		$this->editor_url = "http://www.patas-monkey.com";

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		$this->family = "Patas-Tools";

		// Module label (no space allowed),
		$this->name = strtolower(preg_replace('/^mod/i', '', get_class($this)));

		// Module description, used if translation string 'ModuleXXXDesc'
		$this->description = $langs->trans("GithubPRCountPresentation");

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = $this->getLocalVersion();

		// Key used in llx_const table to save module status enabled/disabled
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		$this->picto=$this->name .'.png@'.$this->name ;

		// Defined if the directory /mymodule/inc/triggers/ contains triggers or not
		$this->module_parts = array(
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();
		$r=0;


		// Dependencies
		// List of modules id that must be enabled if this module is enabled
		$this->depends = array();
		$this->requiredby = array();
		$this->conflictwith = array();

		$this->phpmin = array(4,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,4);	// Minimum version of Dolibarr required by module

		$this->langfiles = array($this->name ."@". $this->name );

		// Config pages
		$this->config_page_url = array("setup.php@".$this->name );

		// Constants
		// List of particular constants to add when module is enabled
		$this->const = array();
		// Array to add new pages in new tabs

		$tabsArray = array();
		$this->tabs = $tabsArray;

		// Boxes
		$this->boxes = array();			// List of boxes
		$r=0;

		// Permissions
		$this->rights = array();
		$this->rights_class = $this->name ;
		$r=0;

		$r++;
		$this->rights[$r][0] = 16019101;
		$this->rights[$r][1] = 'Accès au module GithubPRCount';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'read';

		$r = 0;
		$this->menu[$r]=array('fk_menu'=>'fk_mainmenu=tools',
				'type'=>'left',
				'titre'=>'GithubPRCount',
				'mainmenu'=>'tools',
				'leftmenu'=>'githubprcount',
				'url'=>'/githubprcount/index.php',
				'prefix' => img_picto('', 'object_githubprcount@githubprcount', 'style="width:16px;padding-right: 10px"'),
				'langs'=>'githubprcount@githubprcount',
				'position'=>200, 'enabled'=>'1',
				'perms'=>'$user->hasRight("githubprcount", "read")',
				'target'=>'', 'user'=>2
			);
		$r++;
		// $this->menu[$r]=array('fk_menu'=>'fk_mainmenu=tools,fk_leftmenu=githubprcount',
		// 		'type'=>'left',
		// 		'titre'=>'Synthesis',
		// 		'mainmenu'=>'', 'leftmenu'=>'',
		// 		'url'=>'/user/list.php?contextpage=githubprcount',
		// 		'langs'=>'moreexpense@moreexpense',
		// 		'position'=>110, 'enabled'=>'1',
		// 		'perms'=>'$user->hasRight("moreexpense", "lire")',
		// 		'target'=>'', 'user'=>2
		// 	);
		// $r++;

		// Main menu entries
		//$this->menu = array();			// List of menus to add
	}

	/**
	 *		\brief	  Function called when module is enabled.
	 *					The init function add constants, boxes, permissions and menus
	 *					(defined in constructor) into Dolibarr database.
	 *					It also creates data directories.
	 * @param	 string	 $options	 Options
	 *	  @return	 int			 1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$sql = array();
		$this->load_tables();
		return $this->_init($sql, $options);
	}

	/**
	 *		\brief		Function called when module is disabled.
	 *			  	Remove from database constants, boxes and permissions from Dolibarr database.
	 *					Data directories are not deleted.
	 * @param	 string	 $options	 Options
	 *	  @return	 int			 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *		\brief		Function to load tables
	 *	  @return	 int	 1 if OK, 0 if KO
	 */
	public function load_tables()
	{
		return $this->_load_tables('/'.$this->name.'/sql/');
	}


	/**
	 * Obtient la version du module depuis le dolistore ou le fichier changelog.xml.
	 *
	 * @param int $translated Indique si la version doit être traduite (par défaut à 1).
	 * @return mixed La version de quelque chose.
	 */
	public function getVersion($translated = 1)
	{
		global $langs, $conf;
		$currentversion = $this->version;

		if (!getDolGlobalString("PATASMONKEY_CHECKVERSION"))
			return $currentversion;

		if ($this->disabled) {
			$newversion= $langs->trans("DolibarrMinVersionRequiered")." : ".$this->dolibarrminversion;
			$currentversion="<font color=red><b>".img_error($newversion).$currentversion."</b></font>";
			return $currentversion;
		}

		// récupération de la version du module depuis le dolistore
		if (!empty($this->dolistore_id) && !empty(getDolGlobalString("MAIN_MODULE_DOLISTORE_API_KEY"))) {
			$header = "Accept: application/xml\r\n";
			$header.= "Authorization: Basic ".base64_encode(getDolGlobalString("MAIN_MODULE_DOLISTORE_API_KEY").':');
			$header.= "\r\n";
			$changelog = @file_get_contents(
					getDolGlobalString("MAIN_MODULE_DOLISTORE_API_SRV").'/api/products/'.$this->dolistore_id,
					false,  stream_context_create(array('http' => array('header' => $header)))
				);
			if ($changelog === false)	// not connected
				return $currentversion;

			$xmlParser = xml_parser_create();
			xml_parse_into_struct($xmlParser, $changelog, $vals);
			xml_parser_free($xmlParser);

			foreach ($vals as $val) {
				if (array_key_exists('value', $val) && strlen(($val['value'])) > 2) {
					if ($val['tag'] == "MODULE_VERSION")
						$currentversion = $val['value'];
					if ($val['tag'] == "DOLIBARR_MAX")
						$lastversion = $val['value'];
				}
			}
		} else {
			$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
			$changelog = @file_get_contents(
					str_replace("www", "dlbdemo", $this->editor_url).'/htdocs/custom/'.$this->name.'/changelog.xml',
					false, $context
				);
			if ($changelog === false)	// not connected
				return $currentversion;
			else {
				$sxelast = simplexml_load_string(nl2br($changelog));
				if ($sxelast === false)
					return $currentversion;
				else
					$tblversionslast=$sxelast->Version;

				$lastversion = $tblversionslast[count($tblversionslast)-1]->attributes()->Number;
			}
		}

		if ($lastversion != (string) $this->version) {
			if ($lastversion > (string) $this->version) {
				$newversion= $langs->trans("NewVersionAviable")." : ".$lastversion;
				$retVersion= "<font title='".$newversion."' color=orange><b>";
			} else {
				$retVersion= "<font title='Version Pilote ".$this->version."' color=red><b>";
				$retVersion.= $lastversion."+";
			}
			$retVersion.= $currentversion."</b></font>";
		}
		return $retVersion;
	}
	/**
	 *		\brief		Function to get changelog of module
	 *	  @return	 string	 Changelog of module
	 */
	public function getChangeLog()
	{
		// Libraries
		dol_include_once("/".$this->name."/core/lib/patasmonkey.lib.php");
		return getChangeLog($this->name);
	}

	/**
	 *		\brief		Function to get local version of module
	 *	  @return	 string	 Local version of module
	 */
	public function getLocalVersion()
	{
		global $langs;
		$context  = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
		$changelog = @file_get_contents(dol_buildpath($this->name, 0).'/changelog.xml', false, $context);
		$sxelast = simplexml_load_string(nl2br($changelog));
		if ($sxelast === false)
			return $langs->trans("ChangelogXMLError");
		else {
			$tblversionslast=$sxelast->Version;
			$currentversion = (string) $tblversionslast[count($tblversionslast)-1]->attributes()->Number;
			$tblDolibarr=$sxelast->Dolibarr;
			$minVersionDolibarr=$tblDolibarr->attributes()->minVersion;
			if ((int) DOL_VERSION < (int) $minVersionDolibarr) {
				$this->dolibarrminversion=$minVersionDolibarr;
				$this->disabled = true;
			}
		}
		return $currentversion;
	}
}
