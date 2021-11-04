# Charaktere-auf-Eis
Dieses Plugin erweitert das Board um eine Übersicht von Charakteren auf Eis. Ausgewählte Benutzergruppen können ihre Charaktere auf dieser Seite selbstständig aufs Eis legen lassen. Die User können dort durch eine Dropbox einen Charakter von seinen Charakteren auswählen und durch ein Datumfeld sich ein Enddatum setzen. In den Einstellungen hat das Team die Möglichkeit die maximale Monate für eine Eiszeit anzugeben. Bedeutet wenn ein User sich am 04.11.2021 auf Eis legt kann er maximal beim Datumsfeld bis zum 04.02.2022 wählen. 
Neue Eiszeiten müssen erst vom Team freigeschaltet werden. Teamaccounts werden automatisch freigeschaltet. Beim Freischalten wird dem Team angezeigt, wie viele Charaktere von dem User schon auf Eis liegen und wie viele Charaktere dieser User insgesamt besitzt. Das Team wird über einen Index-Alertbanner über neue Eiszeiten informiert.
Eiszeiten können nach Einstellungen verlängert werden. Mit maximal Anzahl der selbstständigen Verlängerungen und die Anzahl der Tage einer Verlängerung. Das Team hat immer die Möglichkeit. Auch besitzt nur das Team die Möglichkeit User von dieser Liste zu streichen. So behält das Team eine Übersicht.
Der User erhält ab einer festgelegten Anzahl an Tagen vor ablaufen seiner Frist eine Benachrichtigung auf dem Index.
Wenn sich ein Charaktere auf Eis gelegt wird, wird ein entsprechende Profilfeld auf Ja gesetzt und kann so auch für andere Plugins wie das Whiteliste oder Blacklist Plugin von aheartforspinach (Sophie) genutzt werden. Die Eiszeitübersicht kann auf Wunsch vor Gäste versteckt werden.

# Datenbank-Änderungen
Hinzugefügte Tabellen:
- PRÄFIX_ice

# Neue Templates
- ice
- ice_add
- ice_modcp
- ice_modcp_bit
- ice_modcp_nav
- ice_userbit

# Template Änderungen - neue Variablen
- header - {$ice_headerUser}{$ice_headerTeam}
- modcp_nav_users - {$nav_ice}

# ACP-Einstellungen - Charaktere auf Eis
- Benutzergruppen
- Benachrichtigung
- FID des Eiszeit-Profilfeldes
- Maximale Eiszeit
- Spielername
- Verlängerungen
- Anzahl der Verlängerungen
- Verlängerungszeitraum
- Gäste Ansicht
- Listen PHP (Navigation Ergänzung)

# Voraussetzungen
- Eingebundene Icons von Fontawesome (kann man sonst auch in der php ändern)
- <a href="https://www.mybb.de/erweiterungen/18x/plugins-verschiedenes/enhanced-account-switcher/" target="_blank">Accountswitcher</a> von doylecc
- Eiszeit Profilfeld (s.u Profilfeld)

# Empfehlungen 
- <a href="https://github.com/MyBBStuff/MyAlerts" target="_blank">MyAlerts</a> von EuanT

# Profilfeld
Damit das Plugin fehlerfrei funktioniert muss es ein Eiszeitprofilfeld vorhanden sein. Ich habe mich dagegen entschieden bei der Installation automatisch ein Profilfeld zu erstellen, denn evtl kommt es am Ende so, dass einige Boards dann doppelt so ein Profilfeld haben, weil sie vorher schon so eins erstellt haben.
Um ein passendes Profilfeld zu erstellen müsst ihr im ACP unter Konfiguration -> Eigene Profilfelder ein neues Profilfeld hinzufügen.
Titel: Eiszeit
Kurzbeschreibung: Liegt dieser Charakter auf Eis?
Feldtyp: Auswahlbox
Zeilenanzahl: 2
Auwählbare Optionen?: Nein Ja
Sortierung: euch überlassen 
Benötigt? Nein
Zeige bei Registrierung? Nein
Im Profil anzeigen? euch überlassen
In Beiträgen anzeigen? euch überlassen
Sichtbar für keine
Bearbeitbar von keine

# Sonstiges
In meinem Board wollte ich die Charaktere auf Eis besonders darstellen, damit es erkennbar wird. Das ich keine extra Gruppe dafür erstellen wollte, habe ich mich an dem SG Tutorial "Teammitglieder ohne extra Gruppe gesondert markieren" https://storming-gates.de/showthread.php?tid=18840 orientiert und eine Abfrage dafür geschrieben. Wer das auch möchte kann dies auch einbauen.
Dafür muss man einmal die inc/functions.php an zwei Stellen bearbeiten. 
1) sucht nach: 
```
global $groupscache, $cache, $plugins;
```
ersetzt es durch: 
```
global $groupscache, $cache, $plugins, $db, $mybb;
```

2) sucht nach: 
```
$format = stripslashes($format);
```
und fügt <b>davor</b> ein:
```
$icefid_setting = $mybb->settings['ice_fid'];
$icefid = "fid".$icefid_setting;
$ice_uid = $db->fetch_field($db->query("SELECT uid FROM ".TABLE_PREFIX."users where username = '$username'"), "uid");
$ice_user = $db->fetch_field($db->query("SELECT $icefid FROM ".TABLE_PREFIX."userfields where ufid = '$ice_uid'"), "$icefid");
if(strstr($ice_user,"Ja")) {
    $format = "".$ugroup['namestyle']."<sup>EIS</sup>";
}
```
Durch diese Änderung behält der Charaktere immer noch seine eigentliche Gruppenfarbe und bekommt hinter seinen Namen ein hochgestelltes Eis. Das könnte ihr natürlich anpassen wie ihr wollt. Falls ihr etwas <b>vor</b> dem Usernamen zu stehen haben wollt müsst ihr in die "" vor .$ugroup['namestyle']. etwas schreiben. Ich hab sie extra drin gelassen, damit jedes einfacher anpassen könnt. <b>Wichtig</b>, falls ihr class Sachen oder style machen wollte, so kommen dort ja auch " (class=" ") vor und das wird zu einem Fehler kommen, wenn ihr es nicht ordentlich macht. Damit dies nicht passiert müsst ihr das ganze dann so schreiben:
```
class=\"....\"
```
So sieht das ganze nun bei mir aus:
<img src="https://www.bilder-hochladen.net/files/m4bn-a6-3f1d.png">

# Links
- https://euerforum.de/misc.php?action=ice
- https://euerforum.de/modcp.php?action=ice

# Demo
Eiszeit-Übersicht - Usersicht
<img src="https://www.bilder-hochladen.net/files/big/m4bn-aa-1c47.png">

Eiszeit-Übersicht - Teamsicht
<img src="https://www.bilder-hochladen.net/files/big/m4bn-a9-1248.png">

Maximal Limit beim Enddatum

<img src="https://www.bilder-hochladen.net/files/big/m4bn-a7-f0b0.png">

ModCP
<img src="https://www.bilder-hochladen.net/files/m4bn-a8-2ddb.png">