<?php

// ÜBERSICHTSSEITE
$l['ice_page_title'] = "Charaktere auf Eis";
$l['ice_page_desc'] = "Ob aus zeitlichen Gründen oder weil ihr mit dem Charakter im Moment nicht mehr klarkommt, bei uns könnt ihr eure Charaktere für drei Monate auf Eis legen lassen. Dabei ist es egal, ob es nur einer ist oder eure ganze Bande auf der Liste steht. Wir verstehen, dass jeder mal eine Auszeit braucht und Schwierigkeiten mit Charakteren hier und da auftreten, weswegen wir euch in der Anzahl der Charaktere, die man auf Eis legen darf, nicht limitieren wollen.
Nach einer abgelaufenen Eiszeit müsst ihr jedoch posten, bevor ihr euch wieder abwesend melden könnt.";
$l['ice_page_name'] = "Charaktere";
$l['ice_page_days'] = "verbleibende Tage";
$l['ice_userbit_extension'] = "{1}x verlängert";
// Fristentexte
$l['ice_remainingdays_expired'] = "Die Eiszeit ist <b>abgelaufen</b> am {1}!";
$l['ice_remainingdays_today'] = "nur noch <b>heute</b>!";
$l['ice_remainingdays_one'] = "noch <b>einen</b> Tag!";
$l['ice_remainingdays_moreDays'] = "noch <b>{1} Monate</b> und <b>{2} Tage</b> bis zum <i>{3}</i>"; 

// HINZUFÜGEN
$l['ice_add_title'] = "Einen Charakter auf Eis legen";
$l['ice_add_option'] = "Charakter auswählen";
$l['ice_add_send'] = "abschicken";

// MODCP
$l['ice_modcp'] = "Eiszeiten freischalten";
$l['ice_modcp_accepted'] = "Eiszeit akzeptieren";
$l['ice_modcp_declined'] = "Eiszeit ablehnen";
$l['ice_modcp_userbit_requestby'] = "Eiszeitanfrage von <b>{1}</b>";
$l['ice_modcp_userbit_request'] = "Es handelt sich um <b>{1}</b> und soll <b>bis zum {2}</b> auf Eis gelegt werden.";
$l['ice_modcp_userbit_characount'] = "{1} hat schon <b>{2}</b> von insgesamt <b>{3}</b> Charakteren auf Eis zu liegen.";


// WEITERLEITUNGSSEITEN
$l['ice_redirect_extend'] = "Du hast deine Eiszeit um <b>{1} Tage</b> verlängert! Deine neue Eiszeit endet am <b>{2}</b>. Du wirst nun zurückgeleitet.";
$l['ice_redirect_deleteUser'] = "Du hast die Eiszeit von <b>{1}</b> beendet! Du wirst nun zurückgeleitet.";
$l['ice_redirect_add'] = "Du hast deinen Charakter <b>{1}</b> erfolgreich <b>bis zum {2}</b> auf Eis gelegt! Du wirst nun zurückgeleitet.";
$l['ice_redirect_acceptModcp'] = "Du hast die Eiszeit von <b>{1}</b> erfolgreich angenommen! Du wirst nun zurückgeleitet.";
$l['ice_redirect_deleteModcp'] = "Du hast die Eiszeit von <b>{1}</b> erfolgreich abgelehnt! Du wirst nun zurückgeleitet.";

// BANNER 
// Team
$l['ice_alert_banner_team_expired'] = '<div class="red_alert">Es {1} ingesamt {2} <a href="misc.php?action=ice">{3}</a> ausgelaufen.</div>';
$l['ice_alert_banner_team_new'] = '<div class="red_alert">Es gibt {1} neue <a href="modcp.php?action=ice">{2}</a> zum freischalten!</div>';
// User
$l['ice_alert_banner_user'] = '<div class="red_alert">Deine <a href="misc.php?action=ice">Eiszeit</a> für <b>{1}</b> läuft {2} ab. {3}</div>';
$l['ice_alert_banner_user_expired'] = '<div class="red_alert">Deine <a href="misc.php?action=ice">Eiszeit</a> für <b>{1}</b> ist {2} abgelaufen.</div>';
// Verlängeruneg
$l['ice_alert_extensionswNumber_banner_user'] = 'Du kannst noch <b>{1}x</b> verlängern.';
$l['ice_alert_notextensionsNumber_banner_user'] = 'Du kannst nicht mehr verlängern.';
$l['ice_alert_extensionsNumber_banner_user'] = 'Du kannst noch verlängern.';

// MyALERTS
$l['ice_myalert_accepted'] = '{1} hat deine Eiszeit akzeptiert!';
$l['myalerts_setting_ice_myalert_accepted'] = "Benachrichtigung, wenn ein Teammitglied deine Eiszeit akzeptiert hat?";
$l['ice_myalert_declined'] = '{1} hat deine Eiszeit abgelehnt!';
$l['myalerts_setting_ice_myalert_declined'] = "Benachrichtigung, wenn ein Teammitglied deine Eiszeit abgelehnt hat?";

// PROFIL
$l['ice_note'] = '{1} liegt momentan auf Eis.';
$l['ice_sinceandreturn'] = "Liegt bis zum <b>{1}</b> auf Eis.";

?>
