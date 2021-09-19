<?php
// +-----------------------------------------------------------------------+
// | This file is part of Piwigo.                                          |
// |                                                                       |
// | For copyright and license information, please view the COPYING.txt    |
// | file that was distributed with this source code.                      |
// +-----------------------------------------------------------------------+
$lang['Let the fields blank if the ldap accept anonymous connections.'] = 'Laat de velden leeg als LDAP anonieme verbindingen accepteert.';
$lang['Save'] = 'Opslaan';
$lang['Search Ldap groups ?'] = 'Ldap-gebruikers zoeken? Als je groepen wijdverspreid zijn in verschillende filialen of OU, heb je dit nodig. Als je het vermijdt, bespaar je één ldap-verzoek. Je hebt het misschien niet nodig als je ldap-structuur eenvoudig is (bijvoorbeeld: cn=groepsnaam, ou=groepen, dc=voorbeeld, dc=com).';
$lang['To get them out of these roles, they must be sorted of the ldap group and then role updated in the <a href="admin.php?page=user_list">piwigo admin</a>. If a group is mandatory as described in the <a href="admin.php?page=plugin-Ldap_Login-newusers">new piwigo users tab</a>, then they must also belong to the users group.'] = 'Om ze uit deze rollen te krijgen, moeten ze worden gesorteerd in de ldap-groep en vervolgens de rol worden bijgewerkt in de <a href="admin.php?page=user_list"> piwigo admin </a>. Als een groep verplicht is zoals beschreven in het <a href="admin.php?page=plugin-Ldap_Login-newusers"> tabblad nieuwe piwigo-gebruikers </a>, dan moeten ze ook tot de gebruikersgroep behoren.';
$lang['Search Ldap users ?'] = 'Ldap-gebruikers zoeken? Als je gebruikers wijdverspreid zijn in verschillende filialen of OU, heb je dit nodig. Als je het vermijdt, bespaar je één ldap-verzoek. Je hebt het misschien niet nodig als je ldap-structuur eenvoudig is (bijvoorbeeld: uid=gebruiker, ou=mensen, dc=voorbeeld, dc=com).';
$lang['You must save the settings with the Save button just up there before testing here.'] = 'Je moet de instellingen opslaan met de knop Opslaan hierboven voordat je ze test.';
$lang['Your password'] = 'Je LDAP-wachtwoord';
$lang['Ldap_Login configuration'] = 'Ldap_Login configuratie';
$lang['New users when ldap auth is successfull'] = 'Nieuwe gebruikers wanneer LDAP-verificatie is gelukt';
$lang['Secure connexion'] = 'Beveiligde verbinding (ldaps)';
$lang['Test Settings'] = 'Test-instellingen';
$lang['Username'] = 'Je LDAP-gebruikersnaam';
$lang['Users branch'] = 'Tak waar LDAP-gebruikers moeten worden gevonden (bijv .: ou=gebruikers):';
$lang['Warning: LDAP Extension missing.'] = 'Waarschuwing: LDAP-extensie ontbreekt.';
$lang['All LDAP users can use their ldap password everywhere on piwigo if needed.'] = 'Alle LDAP-gebruikers kunnen indien nodig hun LDAP-wachtwoord overal in Piwigo gebruiken.';
$lang['Do you want admins to be advertised by mail in case of new users creation upon ldap login ?'] = 'Wil je dat beheerders per e-mail worden geadverteerd voor het geval er nieuwe gebruikers worden aangemaakt na het inloggen op ldap?';
$lang['Do you want to send mail to the new users, like casual piwigo users receive ?'] = 'Moeten nieuwe gebruikers e-mail ontvangen die vergelijkbaar is met gewone Piwigo-gebruikers?';
$lang['If empty, localhost and standard protocol ports will be used in configuration.'] = 'Indien leeg, worden localhost en standaard protocolpoorten gebruikt in de configuratie.';
$lang['If empty, standard protocol ports will be used by the software.'] = 'Indien leeg, worden standaard protocolpoorten gebruikt door de software.';
$lang['If you create a <a href="admin.php?page=group_list">piwigo group</a> with the same name as an ldap one, all members of the ldap group will automatically join the piwigo group at their next authentication. This allows you to create <a href="admin.php?page=help&section=groups">specific right access management</a> (restrict access to a particaular album...).'] = 'Als je een <a href="admin.php?page=group_list"> piwigo-groep </a> maakt met dezelfde naam als een ldap-groep, zullen alle leden van de ldap-groep automatisch lid worden van de piwigo-groep bij hun volgende authenticatie. Hiermee kun je <a href="admin.php?page=help&section=groups"> specifiek rechtentoegangsbeheer </a> maken (toegang tot een bepaald album beperken ...). Om deze gebruikers te verwijderen, moet je ze echter eerst uit de ldap-groepen halen, waarna de piwigo-groepen kunnen worden bijgewerkt.';
$lang['Ldap connection credentials'] = 'LDAP-verbindingsreferenties';
$lang['Attribute corresponding to the user name'] = 'Kenmerk dat overeenkomt met de gebruikersnaam';
$lang['Base DN'] = 'Basis-DN van LDAP-server (bijvoorbeeld: dc=voorbeeld, dc=com):';
$lang['Bind DN, field in full ldap style'] = 'Bind DN in LDAP-stijl (bijvoorbeeld: cn = admin, dc=voorbeeld, dc=com).';
$lang['Bind password'] = 'Bind wachtwoord';
$lang['Do you allow new piwigo users to be created when users authenticate succesfully on the ldap ?'] = 'Moeten er nieuwe Piwigo-gebruikers worden gemaakt wanneer gebruikers zich succesvol authenticeren via LDAP?';
$lang['Groups branch'] = 'Tak waar LDAP-groepen moeten worden gevonden (bijv.: ou=groepen):';
$lang['Ldap groups'] = 'LDAP-groepen';
$lang['Ldap port'] = 'LDAP-poort';
$lang['Ldap server host'] = 'LDAP-serverhost';
$lang['Ldap server host connection'] = 'LDAP-serververbinding';
$lang['Ldap users'] = 'LDAP-gebruikers';
$lang['Ldap_Login Plugin'] = 'Plug-in voor Ldap_Login';
$lang['Ldap_Login Test'] = 'Ldap_Login-test';