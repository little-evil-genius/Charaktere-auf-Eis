<?php
// Direktzugriff auf die Datei aus Sicherheitsgründen sperren
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// HOOKS
// Die Seite
$plugins->add_hook("misc_start", "ice_misc");
// Banner-Benachrichtigungen
$plugins->add_hook('global_intermediate', 'ice_global');
// Mod-CP
$plugins->add_hook('modcp_nav', 'ice_modcp_nav');
$plugins->add_hook("modcp_start", "ice_modcp");
// Profil
$plugins->add_hook("member_profile_end", "ice_memberprofile");
// Online Anzeige
$plugins->add_hook("fetch_wol_activity_end", "ice_online_activity");
$plugins->add_hook("build_friendly_wol_location_end", "ice_online_location");
// MyAlerts Stuff
if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
	$plugins->add_hook("global_start", "ice_myalert_alerts");
}

 
// Die Informationen, die im Pluginmanager angezeigt werden
function ice_info(){
	return array(
		"name"		=> "Charaktere auf Eis",
		"description"	=> "Dieses Plugin erweitert das Board um eine Übersicht von Charakteren auf Eis. Ausgewählte Benutzergruppen können ihre Charaktere auf dieser Seite selbstständig aufs Eis legen lassen. Neue Eiszeiten müssen erst vom Team freigeschaltet werden.",
		"website"	=> "https://github.com/little-evil-genius/Charaktere-auf-Eis",
		"author"	=> "little.evil.genius",
		"authorsite"	=> "https://storming-gates.de/member.php?action=profile&uid=1712",
		"version"	=> "1.0",
		"compatibility" => "18*"
	);
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin installiert wird (optional).
function ice_install(){

    global $db, $cache, $mybb;

    // DATENBANK HINZUFÜGEN
    $db->query("CREATE TABLE ".TABLE_PREFIX."ice(
        `ice_id` int(10) NOT NULL AUTO_INCREMENT,
        `uid` int(10) NOT NULL,
        `charactername` VARCHAR(500),
        `expirationDate` date,
        `extension` int(10) DEFAULT 0,
        `acceptedTeam` int(10),
        PRIMARY KEY(`ice_id`),
        KEY `sid` (`ice_id`)
        )
        ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1
    ");

    // EINSTELLUNGEN HINZUFÜGEN
    $setting_group = array(
        'name'          => 'ice',
        'title'         => 'Charaktere auf Eis',
        'description'   => 'Einstellungen für das Eiszeit-Plugin',
        'disporder'     => 1,
        'isdefault'     => 0
    );
        
    $gid = $db->insert_query("settinggroups", $setting_group); 

    $setting_array = array(
        // Erlaubte Gruppen
        'ice_groups' => array(
            'title' => 'Benutzergruppe',
            'description' => 'Welche Gruppen dürfen ihre Charaktere auf Eis legen?',
            'optionscode' => 'groupselect',
            'value' => '2', // Default
            'disporder' => 1
        ),
        // Benachrichtigung
        'ice_alert' => array(
            'title' => 'Benachrichtigung',
            'description' => 'Wie viele Tage vor Ablauf der Eiszeit sollen User eine Benachrichtigung bekommen über ihre ablaufende Frist?',
            'optionscode' => 'text',
            'value' => '7', // Default
            'disporder' => 2
        ),
        // Eiszeit-Profilfeld
        'ice_fid' => array(
            'title' => 'FID des Eiszeit-Profilfeldes',
            'description' => 'Wie lautet die FID des Profilfeldes "Auf Eis"?',
            'optionscode' => 'text',
            'value' => '38', // Default
            'disporder' => 3
        ),
        // Maximale Eiszeit
        'ice_maxtime' => array(
            'title' => 'Maximale Eiszeit',
            'description' => 'Wie viele Monate dürfen sich User maximal auf Eis legen lassen?',
            'optionscode' => 'numeric',
            'value' => '3', // Default
            'disporder' => 4
        ),
        // Spielername
        'ice_playerfid' => array(
            'title' => 'Spielername',
            'description' => 'Wie lautet die FID vom Profilfeld, wo der Spielername hinterlegt wird?',
            'optionscode' => 'numeric',
            'value' => '5', // Default
            'disporder' => 5
        ),
        // Verlängern
        'ice_extension' => array(
            'title' => 'Verlängerungen',
            'description' => 'Dürfen User ihre Eiszeit selbstständig verlängern?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 6
        ),
        // max Anzahl der Verlängerungen
        'ice_extension_max' => array(
            'title' => 'Anzahl der Verlängerungen',
            'description' => 'Wie oft dürfen User ihre Eiszeit verlängern? Bei unbegrenzt einfach leer lassen.',
            'optionscode' => 'text',
            'value' => '2', // Default
            'disporder' => 7
        ),
        // Tage der Verlängerung
        'ice_extension_days' => array(
            'title' => 'Verlängerungszeitraum',
            'description' => 'Um wie viele Tage soll die Eiszeit verlängert werden bei einer Verlängerung?',
            'optionscode' => 'text',
            'value' => '14', // Default
            'disporder' => 8
        ),
        // Gäste Ansicht
        'ice_guest' => array(
            'title' => 'Gäste Ansicht',
            'description' => 'Dürfen Gäste die Liste mit den Charakteren auf Eis sehen?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 9
        ),
        // Profil Notiz
        'ice_memberprofile' => array(
            'title' => 'Profil Notiz',
            'description' => 'Soll im Profil eine Notiz erscheinen, wenn der Charakter auf Eis liegt?',
            'optionscode' => 'yesno',
            'value' => '1', // Default
            'disporder' => 10
        ),
        // Listen
        'ice_lists' => array(
            'title' => 'Listen PHP (Navigation Ergänzung)',
            'description' => 'Wie heißt die Hauptseite eurer Listen-Seite? Dies dient zur Ergänzung der Navigation. Falls nicht gewünscht einfach leer lassen.',
            'optionscode' => 'text',
            'value' => 'listen.php', // Default
            'disporder' => 11
        ),
   
    );
    
    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid']  = $gid;
        $db->insert_query('settings', $setting);
    }

    rebuild_settings();

    // TEMPLATES EINFÜGEN
    // Übersichtsseite
	$insert_array = array(
		'title'        => 'ice',
		'template'    => $db->escape_string('<html>
        <head>
            <title>{$mybb->settings[\'bbname\']} - {$lang->ice_page_title}</title>
            {$headerinclude}
        </head>
        <body>
            {$header}
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                    <td class="thead" colspan="2"><strong>{$lang->ice_page_title}</strong></td>
                </tr>	
                <tr>
                    <td colspan="2">
                        <blockquote>Hier kann ein Text stehen zu euren speziellen Eiszeit Regeln oder eine Anleitung für diese Übersicht</blockquote>
                    </td>
                </tr>
                {$ice_add}
                <tr>
                    <td class="thead" width="55%">
                        {$lang->ice_page_name}
                    </td>
                    <td class="thead" width="55%">
                        {$lang->ice_page_days}
                    </td>
                </tr>
                {$charactersOnIce}
            </table>
            {$footer}
        </body>	
    </html>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

    // Charakter auf Eis legen
	$insert_array = array(
		'title'        => 'ice_add',
		'template'    => $db->escape_string('<tr>
        <td class="tcat" colspan="2"><strong>{$lang->ice_add_title}</strong></td>
    </tr>
    <tr>
        <td align="center" colspan="2">
            <form id="ice_add" method="post" action="misc.php?action=ice_add">
                <select name="icename" class="icename">
                    <option value="">{$lang->ice_add_option}</option>
                    {$charas_select}
                </select>
                <input type="date" name="icedate" max="{$maxtime}" min="{$mintime}" class="textbox"><br><br>  
                <input type="hidden" name="action" value="ice_add">	
                <input type="submit" value="{$lang->ice_add_send}" name="ice_add" class="button">  
            </form>
        </td>
    </tr>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

    // Charakter die auf Eis liegen
	$insert_array = array(
		'title'        => 'ice_userbit',
		'template'    => $db->escape_string('<tr style="text-align:center;">
        <td>
            {$username} ({$playername}) &nbsp; {$buttonExtend} {$deletUser}
        </td>
        <td>
            {$deadlineText}
        </td>
    </tr>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

    // ModCP Seite
	$insert_array = array(
		'title'        => 'ice_modcp',
		'template'    => $db->escape_string('<html>
        <head>
            <title>{$mybb->settings[\'bbname\']} -  {$lang->ice_modcp}</title>
            {$headerinclude}
        </head>
        <body>
            {$header}
            <table width="100%" border="0" align="center">
                <tr>
                    {$modcp_nav}
                    <td valign="top">
                        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                            <tr>
                                <td class="thead">
                                    <strong>{$lang->ice_modcp}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td class="trow2">
                                    {$ice_modcp_bit}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            {$footer}
        </body>
    </html>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

    // Neue Eisanfragen im ModCP
	$insert_array = array(
		'title'        => 'ice_modcp_bit',
		'template'    => $db->escape_string('<table width="100%" border="0">
        <tbody>
            <tr>
                <td class="thead" colspan="2">{$ice_modcp_userbit_requestby}</td>
            </tr>
            <tr>
                <td align="center" colspan="2">{$ice_modcp_userbit_request}</td>    
            </tr>
            <tr>
                <td align="center" colspan="2">{$ice_modcp_userbit_characount}</td>   
            </tr>
            <tr>
                <td class="trow2" align="center" width="50%">
                    <a href="modcp.php?action=ice&acceptModcp={$ice_id}" class="button">{$lang->ice_modcp_accepted}</a>
                </td>
                
                <td class="trow2" align="center" width="50%">
                    <a href="modcp.php?action=ice&deleteModcp={$ice_id}" class="button">{$lang->ice_modcp_declined}</a> 
                </td>
            </tr>
        </tbody>
    </table>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

    // ModCP Navi
	$insert_array = array(
		'title'        => 'ice_modcp_nav',
		'template'    => $db->escape_string('<tr>
        <td class="trow1 smalltext">
            <a href="modcp.php?action=ice" class="modcp_nav_item modcp_nav_reports">{$lang->ice_modcp}</a>			
        </td>
    </tr>'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

    // Profil-Notiz
	$insert_array = array(
		'title'        => 'ice_memberprofile',
		'template'    => $db->escape_string('<fieldset>
        <legend><strong>{$ice_note}</strong></legend>
        <span class="smalltext">{$ice_sinceandreturn}</span>
        </fieldset>
        <br />'),
		'sid'        => '-1',
		'version'    => '',
		'dateline'    => TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

}
 
// Funktion zur Überprüfung des Installationsstatus; liefert true zurürck, wenn Plugin installiert, sonst false (optional).
function ice_is_installed(){
    global $db;

    if ($db->table_exists("ice")) {
        return true;
    }
    return false;
} 
 
// Diese Funktion wird aufgerufen, wenn das Plugin deinstalliert wird (optional).
function ice_uninstall(){

    global $db;

    // DATENBANK LÖSCHEN
    if($db->table_exists("ice"))
    {
        $db->drop_table("ice");
    }
    
    // EINSTELLUNGEN LÖSCHEN
    $db->delete_query('settings', "name LIKE 'ice%'");
    $db->delete_query('settinggroups', "name = 'ice'");

    rebuild_settings();

    // TEMPLATES LÖSCHEN
    $db->delete_query("templates", "title LIKE '%ice%'");

}
 
// Diese Funktion wird aufgerufen, wenn das Plugin aktiviert wird.
function ice_activate(){

    require MYBB_ROOT."/inc/adminfunctions_templates.php";

    // MyALERTS STUFF
    if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

        //Alert beim annehmen
		$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('ice_myalert_accepted'); // The codename for your alert type. Can be any unique string.
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);

        // Alert beim Ablehnen
        $alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('ice_myalert_declined'); // The codename for your alert type. Can be any unique string.
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);

		$alertTypeManager->add($alertType);
	}

    // VARIABLEN EINFÜGEN
	// Index-Benachrichtigung
    find_replace_templatesets("header", "#" . preg_quote('{$awaitingusers}') . "#i", '{$awaitingusers} {$ice_headerUser}{$ice_headerTeam}{$ice_newIcetime}');
    // Moderatoren-CP Navigation
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('{$nav_ipsearch}').'#i', '{$nav_ipsearch} {$nav_ice}');
    // Profil-Notiz
    find_replace_templatesets("member_profile", "#".preg_quote('{$awaybit}').'#i', '{$awaybit} {$ice_memberprofile}');
}
 
// Diese Funktion wird aufgerufen, wenn das Plugin deaktiviert wird.
function ice_deactivate(){

    require MYBB_ROOT."/inc/adminfunctions_templates.php";

    // MyALERTS STUFF
    if (class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();

		if (!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}

        $alertTypeManager->deleteByCode('ice_myalert_accepted');
        $alertTypeManager->deleteByCode('ice_myalert_declined');
	}

    // VARIABLEN ENTFERNEN
    find_replace_templatesets("header", "#".preg_quote('{$ice_headerUser}{$ice_headerTeam}{$ice_newIcetime}')."#i", '', 0);
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('{$nav_ice}')."#i", '', 0);
    find_replace_templatesets("member_profile", "#".preg_quote('{$ice_memberprofile}')."#i", '', 0);
}

##############################
### FUNKTIONEN - THE MAGIC ###
##############################

// HINWEIS ÜBER ABLAUF DER FRIST(EN)
function ice_global() {

	global $db, $cache, $mybb, $templates, $headerinclude, $theme, $header, $footer, $lang, $ice_headerTeam, $ice_newIcetime, $ice_headerUser;
	
    // SPRACHDATEI LADEN
    $lang->load('ice');

    // EINSTELLUNGEN
    $ice_groups_setting = $mybb->settings['ice_groups'];
    $ice_maxtime_setting = $mybb->settings['ice_maxtime'];
    $ice_extension_setting = $mybb->settings['ice_extension'];
    $ice_extension_max_setting = $mybb->settings['ice_extension_max'];
    $ice_extension_days_setting = $mybb->settings['ice_extension_days'];
    $ice_guest_setting = $mybb->settings['ice_guest'];
    $ice_lists_setting = $mybb->settings['ice_lists'];
    $ice_alert_setting = $mybb->settings['ice_alert'];
    $playerfid_setting = $mybb->settings['ice_playerfid'];
    $playerfid = "fid".$playerfid_setting;
    $icefid_setting = $mybb->settings['ice_fid'];
    $icefid = "fid".$icefid_setting;

	// USER-ID
    $user_id = $mybb->user['uid'];

    // ACCOUNTSWITCHER
    $charas = ice_get_allchars($user_id);
    $charastring = implode(",", array_keys($charas));

    // HEUTE
    $today = new DateTime(date("Y-m-d", time()));

    // TEAM MELDUNGEN
	if ($mybb->usergroup['canmodcp'] == 1) {

        // Abgelaufene Eiszeiten
        $deadlineUserCount = (int) $db->fetch_array($db->simple_select('ice', 'COUNT(uid)', "expirationDate < '" . $today->format('Y-m-d') . "'"))["COUNT(uid)"];
		
        if ($deadlineUserCount == 1) {
            $ice_headerTeam = $lang->sprintf($lang->ice_alert_banner_team_expired, 'ist', '<b>eine</b>', 'Eiszeit');
        } elseif ($deadlineUserCount > 1) {
            $ice_headerTeam = $lang->sprintf($lang->ice_alert_banner_team_expired, 'sind', '<b>' . $deadlineUserCount . '</b>', 'Eiszeiten');
        } else {
            $ice_headerTeam = "";
        }

        // Neue Eiszeitanfragen
        $NewUserCount = (int) $db->fetch_array($db->simple_select('ice', 'COUNT(uid)', "acceptedTeam = 0"))["COUNT(uid)"];
		
        if ($NewUserCount == 1) {
            $ice_newIcetime = $lang->sprintf($lang->ice_alert_banner_team_new, '<b>eine</b>', 'Eiszeitanfrage');
        } elseif ($NewUserCount > 1) {
            $ice_newIcetime = $lang->sprintf($lang->ice_alert_banner_team_new, '<b>' . $deadlineUserCount . '</b>', 'Eiszeitanfragen');
        } else {
            $ice_newIcetime = "";
        }

    }

    // USER MELDUNGEN
	$ice_alert_query = $db->query("SELECT * FROM ".TABLE_PREFIX."ice
	WHERE uid IN ({$charastring})
    AND acceptedTeam = 1
	");

    while ($alert = $db->fetch_array($ice_alert_query)) {

		$EndDate  = new DateTime($alert['expirationDate']);
		$intvl = $EndDate->diff($today); // Differenz

		// Anzahl restlicher Tage
		$deadlineDays = $intvl->days; 

		// Enddatum in Datumsformat
        $deadlineDay = $EndDate->format('d.m.Y');

        // Accountswitcher-Kram
        $charas = ice_get_allchars($user_id);
        //hier den string bauen ich hänge hinten und vorne noch ein komma dran um so was wie 1 udn 100 abzufangen
        $charastring = ",".implode(",", array_keys($charas)).",";
        $pos = strpos($charastring, ",".$alert['uid'].",");

		// Username 
		$iceusername = get_user($alert['uid'])['username'];

        // Verlängerunstexte
		// User dürfen verlängern
		if ($ice_extension_setting == 1) {
            // Maximallimit gesetzt			
            if ($ice_extension_max_setting != "") {
                $Number = $ice_extension_max_setting - $alert['extension'];
                if ($Number > 0) {
                    $extensionsNumber = $lang->sprintf($lang->ice_alert_extensionswNumber_banner_user, $Number);
                } else {
                    $extensionsNumber = "{$lang->ice_alert_notextensionsNumber_banner_user}";
                }
             } else {
                // Kein Maximallimit 
                $extensionsNumber = "{$lang->ice_alert_extensionsNumber_banner_user}";
            }
          } else {
            $extensionsNumber = "";
        }

        // Bannertexte
		// restliche Tage kleiner/gleich als Benachrichtungstage && ist das heutige Datum größer als das Enddatum, dann Banner
		if ($deadlineDays <= $ice_alert_setting AND $EndDate->format('Y-m-d') >= $today->format('Y-m-d') AND $pos !== false) {

			// Restliche Tage beträgt 0 - nur noch heute
			if ($deadlineDays == 0) {
				$ice_headerUser .= $lang->sprintf($lang->ice_alert_banner_user, $iceusername, '<b>heute</b>', $extensionsNumber);
			} 
			// Restliche Tage beträgt 1 - nur noch bis morgen
			elseif ($deadlineDays == 1) {
				$ice_headerUser .= $lang->sprintf($lang->ice_alert_banner_user, $iceusername, '<b>morgen</b>', $extensionsNumber);
			} 
			// Restliche Tage beträgt Anzahl der Einstellung
			elseif ($deadlineDays <= $ice_alert_setting) {
				$ice_headerUser .= $lang->sprintf($lang->ice_alert_banner_user, $iceusername, 'in <b>' . $deadlineDays . ' Tagen</b>', $extensionsNumber);
			} 

		} 
		// Frist ist ausgelaufen - heutige Datum ist größer als das Enddatum && kein Thema gepostet
		elseif ($EndDate->format('Y-m-d') < $today->format('Y-m-d') AND $pos !== false) {
            $ice_headerUser .= $lang->sprintf($lang->ice_alert_banner_user_expired, $iceusername, 'am <b>' . $deadlineDay . '</b>');
		} 
        // restliche Tage größer als Benachrichtungstage && Enddatum ist größer als heutiges Datum - kein Banner
		elseif ($deadlineDays > $ice_alert_setting AND $EndDate->format('Y-m-d') > $today->format('Y-m-d')) {
			$ice_headerUser .= "";
		} else {
            $ice_headerUser .="";
        }

    }
}

// DIE ÜBERSICHTSSEITE
function ice_misc(){

    global $db, $cache, $mybb, $lang, $templates, $page, $theme, $header, $headerinclude, $footer, $charactersOnIce, $maxtime, $mintime, $charas_select, $deletUser, $buttonExtend;
	
	// SPRACHDATEI
    $lang->load('ice');

	// HEUTE
	$today = new DateTime(date("Y-m-d", time()));
	
	// USER-ID
    $user_id = $mybb->user['uid'];

    // EINSTELLUNGEN
    $ice_groups_setting = $mybb->settings['ice_groups'];
    $ice_maxtime_setting = $mybb->settings['ice_maxtime'];
    $ice_extension_setting = $mybb->settings['ice_extension'];
    $ice_extension_max_setting = $mybb->settings['ice_extension_max'];
    $ice_extension_days_setting = $mybb->settings['ice_extension_days'];
    $ice_guest_setting = $mybb->settings['ice_guest'];
    $ice_lists_setting = $mybb->settings['ice_lists'];
    $playerfid_setting = $mybb->settings['ice_playerfid'];
    $playerfid = "fid".$playerfid_setting;
    $icefid_setting = $mybb->settings['ice_fid'];
    $icefid = "fid".$icefid_setting;

    // Maximale Monate
    $mintime = $today->format('Y-m-d');
    $maxmonth = date_add($today, date_interval_create_from_date_string("$ice_maxtime_setting months")); //$today wird auch auf den Wert gesetzt
    $maxtime = $maxmonth->format('Y-m-d');

    // AUSWAHLMÖGLICHKEIT CHARAKTERE DROPBOX GENERIEREN
	// Kategorien
    $charas_select = ""; 
	$charas = ice_get_allchars($user_id);
	foreach ($charas as $chara) {
		$charas_select .= "<option value='{$chara}'>{$chara}</option>";
	}

    // EISZEIT-ÜBERSICHT
    if($mybb->input['action'] == "ice"){

        // NAVIGATION
        if(!empty($ice_lists_setting)){
            add_breadcrumb("Listen", "$ice_lists_setting");
            add_breadcrumb($lang->ice_page_title, "misc.php?action=ice");
        } else{
            add_breadcrumb($lang->ice_page_title, "misc.php?action=ice");
        }

        // GÄSTE KÖNNEN DEN INHALT NICHT SEHEN
        if($mybb->user['uid'] == 0 AND $ice_guest_setting != 1)
        {
            error_no_permission();
            return;
        } 

        // Nur den Gruppen, den es erlaubt ist, ihre Charaktere auf Eis zusetzen, ist es erlaubt, den Link zu sehen.
        if(is_member($ice_groups_setting)) {
            eval("\$ice_add = \"".$templates->get("ice_add")."\";");
        }
        
        // CHARAKTERE AUF EIS
		$alliceuser_query = $db->query("SELECT * FROM ".TABLE_PREFIX."ice
        WHERE acceptedTeam = 1
		ORDER BY expirationDate ASC, charactername ASC
		");

        while($ice = $db->fetch_array($alliceuser_query)) {

			// LEER LAUFEN LASSEN
			$ice_id = "";
			$uid = "";
			$username = "";
			$expirationDate = "";
            $playername = "";
            $deadlineDate = "";
            $extension = "";
	
			// MIT INFORMATIONEN FÜLLEN
			$ice_id = $ice['ice_id'];
			$uid = $ice['uid'];
            $username = build_profile_link($ice['charactername'], $ice['uid']);

            $playername = $db->fetch_field($db->simple_select("userfields", "$playerfid", "ufid = '{$uid}'"), "$playerfid");

            // RESTLICHE TAGE & ENDDATUM
			$EndDate  = new DateTime($ice['expirationDate']);
            $todayDate = new DateTime(date("Y-m-d", time()));
            $intvl = $todayDate->diff($EndDate); // Differenz

            // Enddatum in Datumsformat
			$deadlineDate = $EndDate->format('d.m.Y');

            // Abgelaufen
            if ($EndDate->format('Y-m-d') < $todayDate->format('Y-m-d')) {
				$deadlineText = $lang->sprintf($lang->ice_remainingdays_expired, $deadlineDate);
			} elseif ($intvl->d == 0) {
                $deadlineText = "{$lang->ice_remainingdays_today}";
            } elseif ($intvl->d == 1) {
                $deadlineText = "{$lang->ice_remainingdays_one}";
            }
            else {
				$deadlineText = $lang->sprintf($lang->ice_remainingdays_moreDays, $intvl->m, $intvl->d, $deadlineDate);
			}

            $extension = $lang->sprintf($lang->ice_userbit_extension, $ice['extension']);
            
			$charas = ice_get_allchars($user_id);
			//hier den string bauen ich hänge hinten und vorne noch ein komma dran um so was wie 1 udn 100 abzufangen
			$charastring = ",".implode(",", array_keys($charas)).",";
			$pos = strpos($charastring, ",".$ice['uid'].",");

            // Charakter von der Liste löschen - Teamoption
			if ($mybb->usergroup['canmodcp'] == 1) {
				$deletUser = "<a href=\"misc.php?action=ice&deletUser={$ice_id}\"><i class=\"fas fa-user-times\" original-title=\"User streichen?\"></i></a>";
			} else {
				$deletUser	= "";
			}

            // Verlängerungsbutton
			// Team kann es immer die Option sehen
			if($mybb->usergroup['canmodcp'] == 1){
				$buttonExtend = "<a href=\"misc.php?action=ice&extend={$ice_id}\"><i class=\"fas fa-plus extend\" original-title=\"Eiszeit verlängern?\"></i></a>";
            } 
			// User kann die Option sehen && noch nicht abgelaufen
			elseif ($pos !== false AND $EndDate->format('Y-m-d') >= $todayDate->format('Y-m-d') AND $ice_extension_setting == 1) {

                // Verlängerungslimit 
				if ($ice_extension_max_setting != ''){
					// Verlängerungsanzahl kleiner als Maximal
					if ($ice_extension_max_setting < $ice['extension']) {
						$buttonExtend = "";
					} else {
						$buttonExtend =  "<a href=\"misc.php?action=ice&extend={$ice_id}\"><i class=\"fas fa-plus extend\" original-title=\"Eiszeit verlängern?\"></i></a>";
					}
				} 
				// Kein Verlängerungslimit
				else {
					$buttonExtend = "<a href=\"misc.php?action=ice&extend={$ice_id}\"><i class=\"fas fa-plus extend\" original-title=\"Eiszeit verlängern?\"></i></a>";
				}

			} 
			// Gäste & andere User
			else {
				$buttonExtend = "";
			}

            eval("\$charactersOnIce .= \"" . $templates->get ("ice_userbit") . "\";");

        }

        // Eiszeit verlängern
		$extend= $mybb->input['extend'];
		if($extend) {

            // GÄSTE KÖNNEN DEN INHALT NICHT SEHEN
            if($mybb->user['uid'] == 0)
            {
                error_no_permission();
            } 

			$db->query("UPDATE ".TABLE_PREFIX."ice 
			SET expirationDate = DATE_ADD(expirationDate, INTERVAL $ice_extension_days_setting DAY), extension = extension + 1  
			WHERE ice_id = $extend
			");

             // User-ID suchen
             $icedate = $db->fetch_field($db->simple_select("ice", "expirationDate", "ice_id = '{$extend}'"), "expirationDate");

             $EndDate  = new DateTime($icedate);
             $deadlineDate = $EndDate->format('d.m.Y');
			
             redirect("misc.php?action=ice", $lang->sprintf($lang->ice_redirect_extend, $ice_extension_days_setting, $deadlineDate));
		
            }

        // Eiszeit löschen
        $delete = $mybb->input['deletUser'];        
        if($delete) {

            // GÄSTE KÖNNEN DEN INHALT NICHT SEHEN
            if($mybb->user['uid'] == 0)
            {
                error_no_permission();
            } 

            // User-ID suchen
            $iceuid = $db->fetch_field($db->simple_select("ice", "uid", "ice_id = '{$delete}'"), "uid");

            // Profilfeld Eiszeit updaten
            $PFice = array(
                "$icefid" => "Nein",
            );

            $db->update_query("userfields", $PFice, "ufid = '".$iceuid."'");

            $db->delete_query("ice", "ice_id = '$delete'");

            $iceuser_name = get_user($iceuid)['username'];
        
            redirect("misc.php?action=ice", $lang->sprintf($lang->ice_redirect_deleteUser, $iceuser_name));
        }

        
        // Listenmenü
        eval("\$menu .= \"" . $templates->get ("listen_nav") . "\";");
		
		// TEMPLATE FÜR DIE SEITE
		eval("\$page = \"".$templates->get("ice")."\";");
		output_page($page);
		die();
    }

    // CHARAKTER AUF EIS LEGEN
    elseif($_POST['ice_add']) {
        
        if($mybb->input['icename'] == "") {
            error("Es muss eine Charakter ausgewählt werden!");
        } elseif($mybb->input['icedate'] == "") {
            error("Es muss eine Enddatum ausgewählt werden!");
        } else {

            // Bei Teammitgliedern sofort freigeschaltet
            if($mybb->usergroup['canmodcp'] == '1'){
                $accepted = 1;
            } else {
                $accepted = 0;       
            }

            $icedate = $db->escape_string ($_POST['icedate']);       
            $icename = $db->escape_string ($_POST['icename']);

            // User-ID suchen       
            $iceuid = $db->fetch_field($db->simple_select("users", "uid", "username = '{$icename}'"), "uid");

            $new_icetime = array(
                "expirationDate" => $icedate,
                "charactername" => $icename,
                "uid" => $iceuid,
                "acceptedTeam" => $accepted       
            );

            // Neuer User in der Eiszeit-Datenbank       
            $db->insert_query("ice", $new_icetime);

            if($mybb->usergroup['canmodcp'] == '1'){
            
                // Profilfeld Eiszeit updaten bei Teammitgliedern
                $new_PFice = array(
                    "$icefid" => "Ja",       
                );

                $db->update_query("userfields", $new_PFice, "ufid = '".$iceuid."'");       
            }
            
            $EndDate  = new DateTime($icedate);
			$deadlineDate = $EndDate->format('d.m.Y');

            redirect("misc.php?action=ice", $lang->sprintf($lang->ice_redirect_add, $icename, $deadlineDate));  
        }
    }
}

// MOD-CP - NAVIGATION
function ice_modcp_nav() {

    global $db, $mybb, $templates, $theme, $header, $headerinclude, $footer, $lang, $modcp_nav, $nav_ice;
    
    $lang->load('ice');
    
    eval("\$nav_ice = \"".$templates->get ("ice_modcp_nav")."\";");
}

// MOD-CP - SEITE
function ice_modcp() {
   
    global $mybb, $templates, $lang, $header, $headerinclude, $footer, $db, $page, $modcp_nav, $education_modcp_bit;

    // SPRACHDATEI
    $lang->load('ice');

     // EINSTELLUNGEN
     $ice_groups_setting = $mybb->settings['ice_groups'];
     $ice_maxtime_setting = $mybb->settings['ice_maxtime'];
     $ice_extension_setting = $mybb->settings['ice_extension'];
     $ice_extension_max_setting = $mybb->settings['ice_extension_max'];
     $ice_extension_days_setting = $mybb->settings['ice_extension_days'];
     $playerfid_setting = $mybb->settings['ice_playerfid'];
     $playerfid = "fid".$playerfid_setting;
     $icefid_setting = $mybb->settings['ice_fid'];
     $icefid = "fid".$icefid_setting;

    
     if($mybb->get_input('action') == 'ice') {

        // Add a breadcrumb
        add_breadcrumb($lang->nav_modcp, "modcp.php");
        add_breadcrumb($lang->ice_modcp, "modcp.php?action=ice");

        // CHARAKTERE AUF EIS
		$newiceuser_query = $db->query("SELECT * FROM ".TABLE_PREFIX."ice
        WHERE acceptedTeam = 0
		ORDER BY expirationDate ASC, charactername ASC
		");

        while($modcp = $db->fetch_array($newiceuser_query)) {
   
            // Alles leer laufen lassen
            $ice_id = "";
            $ice_uid = "";
            $charactername = "";
            $deadlineDate = "";
            $playername = "";
   
            // Füllen wir mal alles mit Informationen
            $ice_id = $modcp['ice_id'];
            $ice_uid = $modcp['uid'];
            $charactername = $modcp['charactername'];

            // Enddatum in Datumsformat
            $EndDate  = new DateTime($modcp['expirationDate']);
			$deadlineDate = $EndDate->format('d.m.Y');
            
            // Spielername
            $playername = $db->fetch_field($db->simple_select("userfields", "$playerfid", "ufid = '{$ice_uid}'"), "$playerfid");

            $charas_modcp = ice_get_allchars_modcp($ice_uid);
            $charastring_modcp = implode(",", array_keys($charas_modcp));

            // Zählen wie viele Charakter von dem User auf Eis liegen
            $charaonIce_query = $db->query("SELECT * FROM ".TABLE_PREFIX."ice
            WHERE uid IN ({$charastring_modcp})
            AND acceptedTeam = 1
            ");
            $count_charaonIce = mysqli_num_rows($charaonIce_query);

            // Zählen wie viele Charakter der User insgesamt hat
            $allcharas_query = $db->query("SELECT * FROM ".TABLE_PREFIX."users
            WHERE uid IN ({$charastring_modcp})
            ");
            $count_allcharas = mysqli_num_rows($allcharas_query);

            // Texte 
            $ice_modcp_userbit_requestby = $lang->sprintf($lang->ice_modcp_userbit_requestby, $playername);
            $ice_modcp_userbit_request = $lang->sprintf($lang->ice_modcp_userbit_request, $charactername, $deadlineDate);
            $ice_modcp_userbit_characount = $lang->sprintf($lang->ice_modcp_userbit_characount, $playername, $count_charaonIce, $count_allcharas);
            
            eval("\$ice_modcp_bit .= \"".$templates->get("ice_modcp_bit")."\";");
        }

        // Eiszeit annehmen
        $acceptModcp = $mybb->input['acceptModcp'];        
        if($acceptModcp) {

            // User-ID suchen
            $iceuid = $db->fetch_field($db->simple_select("ice", "uid", "ice_id = '{$acceptModcp}'"), "uid");

            // Username 
            $icename = get_user($iceuid)['username'];

            // Profilfeld Eiszeit updaten
            $PFice = array(
                "$icefid" => "Ja",
            );

            $db->update_query("userfields", $PFice, "ufid = '".$iceuid."'");

            $db->query("UPDATE ".TABLE_PREFIX."ice SET acceptedTeam = 1 WHERE ice_id = '".$acceptModcp."'");

            // MyALERTS STUFF
            $query_alert = $db->simple_select("ice", "*", "ice_id = '{$acceptModcp}'");
            while ($alert_accept = $db->fetch_array ($query_alert)) {
                if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                    $user = get_user($alert['uid']);
                    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('ice_myalert_accepted');
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$alert_accept['uid'], $alertType, (int)$acceptModcp);
                        $alert->setExtraDetails([
                            'username' => $user['username'],
                            'name' => $alert_accept['charactername'],
                            'expirationDate' => $alert_accept['expirationDate']
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }  
            }
        
            redirect("modcp.php?action=ice", $lang->sprintf($lang->ice_redirect_acceptModcp, $icename));
        }

        // Eiszeit ablehnen
        $deleteModcp = $mybb->input['deleteModcp'];        
        if($deleteModcp) {

            // User-ID suchen
            $iceuid = $db->fetch_field($db->simple_select("ice", "uid", "ice_id = '{$deleteModcp}'"), "uid");

            // Username 
            $icename = get_user($iceuid)['username'];

            $db->delete_query("ice", "ice_id = '$deleteModcp'");

            // MyALERTS STUFF
            $query_alert = $db->simple_select("ice", "*", "ice_id = '{$deleteModcp}'");
            while ($alert_delete = $db->fetch_array ($query_alert)) {
                if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')) {
                    $user = get_user($alert['uid']);
                    $alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('ice_myalert_declined');
                    if ($alertType != NULL && $alertType->getEnabled()) {
                        $alert = new MybbStuff_MyAlerts_Entity_Alert((int)$alert_delete['uid'], $alertType, (int)$deleteModcp);
                        $alert->setExtraDetails([
                            'username' => $user['username'],
                        ]);
                        MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
                    }
                }  
            }
        
            redirect("modcp.php?action=ice",  $lang->sprintf($lang->ice_redirect_deleteModcp, $icename));
        }

        // TEMPLATE FÜR DIE SEITE
        eval("\$page = \"".$templates->get("ice_modcp")."\";");
        output_page($page);
        die();

    }
}

// ANZEIGE IM PORFIL
function ice_memberprofile() {
   
    global $db, $mybb, $lang, $templates, $theme, $memprofile, $ice_memberprofile;

    // SPRACHDATEI LADEN
    $lang->load("ice");
    
    // EINSTELLUNGEN
    $ice_memberprofile_setting = $mybb->settings['ice_memberprofile'];

    $uid = $mybb->get_input('uid', MyBB::INPUT_INT);

    $profile_query = $db->query("SELECT * FROM ".TABLE_PREFIX."ice
    WHERE uid = '".$uid."'
    AND acceptedTeam = 1
    ");

    while($prof = $db->fetch_array($profile_query)){

        // Alles leer laufen lassen
        $ice_id = "";
        $charactername = "";
        $deadlineDate = "";
        $EndDate = "";
       
        // Füllen wir mal alles mit Informationen
        $ice_id = $prof['ice_id'];
        $charactername = $prof['charactername'];

        // RESTLICHE TAGE & ENDDATUM
        $EndDate  = new DateTime($prof['expirationDate']);
        // Enddatum in Datumsformat
        $deadlineDate = $EndDate->format('d.m.Y');

        $ice_note = $lang->sprintf($lang->ice_note, $charactername);
        $ice_sinceandreturn = $lang->sprintf($lang->ice_sinceandreturn, $deadlineDate);

       if ($ice_memberprofile_setting == 1) { 
        eval("\$ice_memberprofile .= \"".$templates->get("ice_memberprofile")."\";");  
       } else {
        $ice_memberprofile = "";
       }
    }    

}

// ACCOUNTSWITCHER HILFSFUNKTION - Header & Hinzufügen & Button
function ice_get_allchars($user_id) {
    global $db, $mybb;

    //für den fall nicht mit hauptaccount online
    $as_uid = intval($mybb->user['as_uid']);

    $charas = array();
    if ($as_uid == 0) {
      // as_uid = 0 wenn hauptaccount oder keiner angehangen
      $get_all_users = $db->query("SELECT uid,username FROM " . TABLE_PREFIX . "users WHERE (as_uid = $user_id) OR (uid = $user_id) ORDER BY username");
    } else if ($as_uid != 0) {
      //id des users holen wo alle an gehangen sind 
      $get_all_users = $db->query("SELECT uid,username FROM " . TABLE_PREFIX . "users WHERE (as_uid = $as_uid) OR (uid = $user_id) OR (uid = $as_uid) ORDER BY username");
    }
    while ($users = $db->fetch_array($get_all_users)) {
  
      $uid = $users['uid'];
      $charas[$uid] = $users['username'];
    }
    return $charas;  

}

// ACCOUNTSWITCHER HILFSFUNKTION - Modcp
function ice_get_allchars_modcp($ice_uid) {
    global $db, $mybb;

    $user = get_user($ice_uid);

    # check if account is main account
    if($user['as_uid'] != "0") {
        # if not, get all infos from uid's main account
        $ice_uid = $user['as_uid'];
        $user = get_user($ice_uid);
    }

    # get all users that are linked via account switcher
    $as_uid_modcp = $db->fetch_field($db->simple_select("users", "as_uid", "uid = '{$ice_uid}'"), "as_uid");
    if(empty($as_uid_modcp)) {
        $as_uid_modcp = $ice_uid;
    }

    $charas_modcp = array();
    if ($as_uid_modcp == 0) {
      // as_uid = 0 wenn hauptaccount oder keiner angehangen
      $get_all_users_modcp = $db->query("SELECT uid,username FROM " . TABLE_PREFIX . "users WHERE (as_uid = $ice_uid) OR (uid = $ice_uid) ORDER BY username");
    } else if ($as_uid_modcp != 0) {
      //id des users holen wo alle an gehangen sind 
      $get_all_users_modcp = $db->query("SELECT uid,username FROM " . TABLE_PREFIX . "users WHERE (as_uid = $as_uid_modcp) OR (uid = $ice_uid) OR (uid = $as_uid_modcp) ORDER BY username");
    }
    while ($users_modcp = $db->fetch_array($get_all_users_modcp)) {
  
      $uid_modcp = $users_modcp['uid'];
      $charas_modcp[$uid_modcp] = $users_modcp['username'];
    }
    return $charas_modcp;  

}

// ONLINE ANZEIGE - WER IST WO
function ice_online_activity($user_activity) {

    global $parameters, $user;

    $split_loc = explode(".php", $user_activity['location']);
    if($split_loc[0] == $user['location']) {
        $filename = '';
    } else {
        $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
    }
    
    switch ($filename) {
        case 'misc':
        if($parameters['action'] == "ice" && empty($parameters['site'])) {
            $user_activity['activity'] = "ice";
        }
        break;
    }
      

    return $user_activity;
}

function ice_online_location($plugin_array) {

    global $mybb, $theme, $lang;

	if($plugin_array['user_activity']['activity'] == "ice") {
		$plugin_array['location_name'] = "Sieht sich die <a href=\"misc.php?action=ice\">Übersicht der Eiszeiten</a> an.";
	}


    return $plugin_array;
}

// MyALERTS STUFF
function ice_myalert_alerts() {

	global $mybb, $lang;
	
    $lang->load('ice');

    // EISZEIT ANNEHMEN
    /**
    * Alert formatter for my custom alert type.
    */
    class MybbStuff_MyAlerts_Formatter_IceAcceptedFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
    {
        /**
        * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
        *
        * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
        *
        * @return string The formatted alert string.
        */
	
        public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
        {
            global $db;
            $alertContent = $alert->getExtraDetails();
            $userid = $db->fetch_field($db->simple_select("users", "uid", "username = '{$alertContent['username']}'"), "uid");
            $user = get_user($userid);
            $alertContent['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
            return $this->lang->sprintf(
                $this->lang->ice_myalert_accepted,
                $outputAlert['from_user'],
                $alertContent['username'],
                $outputAlert['dateline']
            );	
        }

        /**
        * Init function called before running formatAlert(). Used to load language files and initialize other required
        * resources.
        *
        * @return void
        */
        public function init()
        {
            if (!$this->lang->ice) {
                $this->lang->load('ice');
            }	
        }

        /**
        * Build a link to an alert's content so that the system can redirect to it.
        *
        * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
        *
        * @return string The built alert, preferably an absolute link.
        */
        public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
        {
            return $this->mybb->settings['bburl'] . '/misc.php?action=ice';
        }	
    }

    if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

        if (!$formatterManager) {
            $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);		
        }

        $formatterManager->registerFormatter(
            new MybbStuff_MyAlerts_Formatter_IceAcceptedFormatter($mybb, $lang, 'ice_myalert_accepted')
        );
    }

	// EISZEIT ABLEHNEN
    /**
    * Alert formatter for my custom alert type.
    */
	class MybbStuff_MyAlerts_Formatter_IceDeclinedFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
	{
	    /**
        * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
        *
        * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
        *
        * @return string The formatted alert string.
        */
	    public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
	    {
			global $db;
			$alertContent = $alert->getExtraDetails();
            $userid = $db->fetch_field($db->simple_select("users", "uid", "username = '{$alertContent['username']}'"), "uid");
            $user = get_user($userid);
            $alertContent['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
	        return $this->lang->sprintf(
	            $this->lang->ice_myalert_declined,
				$outputAlert['from_user'],
				$alertContent['username'],
	            $outputAlert['dateline']
	        );
	    }

	    /**
        * Init function called before running formatAlert(). Used to load language files and initialize other required
        * resources.
        *
        * @return void
        */
	    public function init()
	    {
	        if (!$this->lang->ice) {
	            $this->lang->load('ice');
	        }
	    }

	    /**
        * Build a link to an alert's content so that the system can redirect to it.
        *
        * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
        *
        * @return string The built alert, preferably an absolute link.
        */
	    public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
	    {
	        return $this->mybb->settings['bburl'] . '/misc.php?action=ice';
	    }
	}

	if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();

		if (!$formatterManager) {
			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}

		$formatterManager->registerFormatter(
				new MybbStuff_MyAlerts_Formatter_IceDeclinedFormatter($mybb, $lang, 'ice_myalert_declined')
		);
	} 
}
