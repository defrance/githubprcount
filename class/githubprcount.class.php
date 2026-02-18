<?php
/* Copyright (C) 2016-2026	Charlene BENKE	<charlene@patas-monkey.com>
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
 *	\file	   htdocs/custom/githubprcount/class/githubprcount.class.php
 *	\ingroup	tools
 *	\brief	  File of class to githubprcount moduls
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/geturl.lib.php';


class GithubPrCount
{
    public function __construct( $db)
    {

    }

    /* ============================
     * Public API
     * ============================ */

    /**
     * Main entry point
     * @return array [login => nb_pr]
     */
    public function getStats(): array
    {
        $contributors = $this->getContributors();
        $stats = [];

        foreach ($contributors as $login) {
            $stats[$login] = $this->getUserClosedPrCount($login);
            $sleeptimer=getDolGlobalString("githubprcount_sleeptimer");
            usleep($sleeptimer);
        }

        arsort($stats);

        return $stats;
    }

    /* ============================
     * Contributors
     * ============================ */

    public function getContributors($per_page = 100): array
    {
        $githubtoken = getDolGlobalString("githubprcount_githubtoken");
        $owner=getDolGlobalString("githubprcount_owner");
        $application=getDolGlobalString("githubprcount_application");
        

        $contributors = [];
        $page = 1;
        $url = sprintf(
            'https://api.github.com/repos/%s/%s/contributors?per_page=%d&page=%d',
            $owner,
            $application,
            $per_page,
            $page
        );
        $data = getURLContent(
                $url, 'GET', "", 1,
                array(
                    "User-Agent: githubprcount",
                    "Authorization: Bearer ".$githubtoken,
                    "Accept: application/vnd.github+json")
            );
        $arrayData  = json_decode($data['content'], true);
        $year = date('Y');
        $sleeptimer = getDolGlobalString("githubprcount_sleeptimer");
        foreach ($arrayData as $user) {
            if (!empty($user['login'] && $user['type'] === 'User')) {
                $currentContribs = $this->getUserClosedPrCount($user['login'],  $year);
                usleep($sleeptimer);
                $currentContribsPrev = $this->getUserClosedPrCount($user['login'],  $year - 1);
                usleep($sleeptimer);
                $currentContribsPrevPrev = $this->getUserClosedPrCount($user['login'],  $year - 2);
                usleep($sleeptimer);
                if ($currentContribs + $currentContribsPrev + $currentContribsPrevPrev > 0) {
                    $dataUser = getURLContent(
                        $user['url'], 'GET', "", 1,
                        array(
                            "User-Agent: githubprcount",
                            "Authorization: Bearer ".$githubtoken,
                            "Accept: application/vnd.github+json")
                    );   
                    $arrayDataUser = json_decode($dataUser['content'], true);
                    $contributors[$user['login']] = array(
                        'login' => $user['login'],
                        'avatar_url' => $user['avatar_url'],
                        "html_url" => $user['html_url'],
                        "contributions" => $user['contributions'],
                        "contribs" => $currentContribs,
                        "contribsprev" => $currentContribsPrev,
                        "contribsprevprev" => $currentContribsPrevPrev,
                        "name" => $arrayDataUser['name'],
                        "company" => $arrayDataUser['company'],
                        "blog" => $arrayDataUser['blog'],
                        "location" => $arrayDataUser['location'],
                        "email" => $arrayDataUser['email'],
                        "hireable" => $arrayDataUser['hireable'],
                        "bio" => $arrayDataUser['bio'],
                    );
                }
            }
        }

        // trie des contributeurs par nombre de PRs
        uasort($contributors, function ($a, $b) {
            return $b['contribsprev'] <=> $a['contribsprev'];
        });

        return $contributors;
    }

	/**
	 *	Return clicable name (with picto eventually)
	 *
	 *	@param		int			$withpicto		0=_No picto, 1=Includes the picto in the linkn, 2=Picto only
	 *	@return		string						String with URL
	 */
	public function getNomUrl($contributor, $withpicto = 0)
	{
		global $langs, $hookmanager, $action, $db;

		$result='';
		$lien = '<a href="'.$contributor['html_url'].'"';
        $picto = "<img src='".$contributor['avatar_url']."' alt='".$contributor['login']."' style='width:32px;height:32px;border-radius:50%;'>";
        $lienfin = '</a>';

		$label= $picto;
		$label.= '&nbsp;'.$contributor['name'];
        $label.=' - '.$contributor['company'];
        $label.='<br> '.$contributor['blog'];
        $label.='<br> '.$contributor['location'];
        $contributor['email']? $label.='<br> '.$contributor['email']:'';
        //$label.='<br> '.$contributor['hireable'];
        $label.='<br> '.$contributor['bio'];


		$linkclose = ' title="'.dol_escape_htmltag($label, 1).'"';
		$linkclose.=' class="classfortooltip" >';
		if (! is_object($hookmanager)) {
			include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
			$hookmanager=new HookManager($db);
		}
		$hookmanager->initHooks(array('githubprcount'));
		$parameters=array(
                'login' => $contributor['login'],
                'contributor' => $contributor,
            );
		// Note that $action and $object may have been modified by some hooks
		$hookmanager->executeHooks('getnomurltooltip', $parameters, $this, $action);
		$linkclose = ($hookmanager->resPrint ? $hookmanager->resPrint : $linkclose);

		if ($withpicto)
            $result.= $lien.$linkclose.$picto.$lienfin;
        else
            $result.= $lien.$linkclose.$contributor['login'].$lienfin;

        return $result;
	}

    /* ============================
     * PR count per user
     * ============================ */

    public function getUserClosedPrCount(string $login, int $year): int
    {
        $githubtoken = getDolGlobalString("githubprcount_githubtoken");
        $owner=getDolGlobalString("githubprcount_owner");
        $application=getDolGlobalString("githubprcount_application");

        $query = sprintf(
            'repo:%s/%s is:pr is:closed author:%s merged:%d-01-01..%d-12-31',
            $owner,
            $application,
            $login,
            $year,
            $year
        );

        $url = 'https://api.github.com/search/issues?q=' . urlencode($query) . '&per_page=1';

        $data = getURLContent(
                $url, 'GET', "", 1,
                array(
                    "User-Agent: githubprcount",
                    "Authorization: Bearer ".$githubtoken,
                    "Accept: application/vnd.github+json")
            );
        $arrayData  = json_decode($data['content'], true);
        $count = (int)($arrayData ['total_count'] ?? 0);
        return $count;
    }
    public function getElementCount($typePeriod, $typeinfo ="pr", $state="open"): int
    {
        $githubtoken = getDolGlobalString("githubprcount_githubtoken");
        $owner=getDolGlobalString("githubprcount_owner");
        $application=getDolGlobalString("githubprcount_application");
        $sleeptimer = getDolGlobalString("githubprcount_sleeptimer");
        usleep($sleeptimer);
        if ($state === "open") {
            $filter = 'repo:%s/%s is:%s state:open created:>%s';
        } else {
            $filter = 'repo:%s/%s is:%s state:closed closed:>%s';
        }
        
        switch ($typePeriod) {
            case "M":
                // on décale la date d'un mois
                $prevDate = date('Y-m-d', strtotime('-1 month'));
               
                break;
            case "Q":
                // on décale la date d'un trimestre
                $prevDate = date('Y-m-d', strtotime('-3 months'));
                break;
            case "Y":
            case "R":
                // on décale la date d'un an
                $prevDate = date('Y-m-d', strtotime('-1 year'));
                break;
            default:
                throw new InvalidArgumentException("Invalid typePeriod: $typePeriod");
        }

        if ($typePeriod === "R") {
            if ($state === "open") {
                $filter = 'repo:%s/%s is:%s state:open created:<=%s';
            } else {
                $filter = 'repo:%s/%s is:%s state:closed closed:<=%s';
            }
        }
        $query = sprintf(
            $filter,
            $owner,
            $application,
            $typeinfo,
            $prevDate,
        );

        $url = 'https://api.github.com/search/issues?q=' . urlencode($query) . '&per_page=1';

        //print "URL: $url\n";
        $data = getURLContent(
                $url, 'GET', "", 1,
                array(
                    "User-Agent: githubprcount",
                    "Authorization: Bearer ".$githubtoken,
                    "Accept: application/vnd.github+json")
            );
        $arrayData  = json_decode($data['content'], true);
        $count = (int)($arrayData ['total_count'] ?? 0);
        return $count;
    }

}