<?php
/*
Plugin Name: Ldap_Login
Description: Permet de se logger via une authentification ldap
Plugin URI: http://www.22decembre.eu
Author: 22decembre
Author URI:http://www.22decembre.eu
___________________________________

Language Name: Dansk [DK]
*/
$lang['All LDAP users can use their ldap password everywhere on piwigo if needed.'] = 'Alle LDAP-brugere kan om nødvendigt benytte deres LDAP-adgangskode over alt i Piwigo.';
$lang['New users when ldap auth is successfull']                                    = 'Nye bruger ved vellykket LDAP-autentifikation';
$lang['Ldap_Login Plugin']                                                          = 'Ldap_Login Plugin';
$lang['Ldap_Login configuration']                                                   = 'Opsætning af Ldap_Login';
$lang['Warning: LDAP Extension missing.']                                           = 'Advarsel: LDAP-udvidelse mangler.';

// ldap server connection

$lang['Ldap server host connection']                                                    = 'LDAP-serverforbindelse';
$lang['If empty, standard protocol ports will be used by the software.']                = 'Hvis tomt benyttes standard-protokolporte af programmellet.';
$lang['If empty, localhost and standard protocol ports will be used in configuration.'] = 'Hvis tomt benyttes localhost og standard-protokolporte i opsætningen.';
$lang['Ldap server host']                                                               = 'LDAP-værtsadresse';
$lang['Secure connexion']                                                               = 'Sikker forbindelse (ldaps)';
$lang['Ldap port']                                                                      = 'LDAP-port';

// ldap attributes

$lang['Base DN']                                  = 'Base DN hvor LDAP-brugerne findes (f.eks.: ou=users,dc=example,dc=com):';
$lang['Attribute corresponding to the user name'] = 'Attribut der svarer til brugernavnet';

// ldap connection credentials
$lang['Ldap connection credentials']                                    = 'LDAP-loginoplysninger';
$lang['Let the fields blank if the ldap accept anonymous connections.'] = 'Hvis LDAP accepterer anonyme logins, skal felterne være tomme.';
$lang['Bind DN, field in full ldap style']                              = 'Bind DN på LDAP-form (f.eks.: cn=admin,dc=example,dc=com).';
$lang['Bind password']                                                  = 'Bind-adgangskode';

// test and save

$lang['Username']                                                                           = 'Dit LDAP-brugernavn';
$lang['Your password']                                                                      = 'Din LDAP-adgangskode.';
$lang['Ldap_Login Test']                                                                    = 'Test af Ldap_Login';
$lang['You must save the settings with the Save button just up there before testing here.'] = 'Du skal gemme indstillingerne med knappen Gem herover, før du kan afprøve dem.';
$lang['Save']                                                                               = 'Gem';
$lang['Test Settings']                                                                      = 'Afprøv indstillingerne';

// new piwigo users

$lang['Do you want to send mail to the new users, like casual piwigo users receive ?']                 = 'Skal nye brugere modtage en mail på samme måde som tilfældige brugere?';
$lang['Do you allow new piwigo users to be created when users authenticate succesfully on the ldap ?'] = 'Må Piwigo oprette nye brugere, når de med succes autentificeres i LDAP?';
$lang['Do you want admins to be advertised by mail in case of new users creation upon ldap login ?']   = 'Skal administratorerne have besked pr. mail i tilfælde af at nye brugere oprettes ved login via LDAP?';

$lang['Search Ldap groups ?']                                                                                                                                                                                                                                                                                                                                                  = 'Søg efter LDAP-brugere? Hvis grupperne er fordelt på flere branches eller OU, er det nødvendigt. Hvis det springes over, spares der en LDAP-forespørgsl. Der er måske ikke nødvendigt, hvis LDAP-træet er simpelt (fx: cn=gruppenavn,ou=grupper,dc=eksempel,dc=com).';
$lang['Search Ldap users ?']                                                                                                                                                                                                                                                                                                                                                   = 'Søg efter LDAP-brugere? Hvis brugerne er fordelt på flere branches eller OU, er det nødvendigt. Hvis det springes over, spares der en LDAP-forespørgsl. Der er måske ikke nødvendigt, hvis LDAP-træet er simpelt (fx: uid=bruger,ou=personer,dc=eksempel,dc=com).';
$lang['To get them out of these roles, they must be sorted of the ldap group and then role updated in the <a href="admin.php?page=user_list">piwigo admin</a>. If a group is mandatory as described in the <a href="admin.php?page=plugin-Ldap_Login-newusers">new piwigo users tab</a>, then they must also belong to the users group.']                                      = 'For at få dem ud af disse roller, skal de sorteres i LDAP-gruppen og dernæst rolleopdateres i <a href="admin.php?page=user_list">piwigo-admin</a>. Hvis en gruppe er obligatorisk, som beskrevet på <a href="admin.php?page=plugin-Ldap_Login-newusers">fanebladet nye piwigo-brugere</a>, skal de også høre til brugere-gruppen.';
$lang['Users branch']                                                                                                                                                                                                                                                                                                                                                          = 'Branch hvor LDAP-brugere skal findes (fx: ou=brugere):';
$lang['Groups branch']                                                                                                                                                                                                                                                                                                                                                         = 'Branch for LDAP-grupper skal findes (fx: ou=groups):';
$lang['If you create a <a href="admin.php?page=group_list">piwigo group</a> with the same name as an ldap one, all members of the ldap group will automatically join the piwigo group at their next authentication. This allows you to create <a href="admin.php?page=help&section=groups">specific right access management</a> (restrict access to a particaular album...).'] = 'Hvis man opretter en <a href="admin.php?page=group_list">piwigo-gruppe</a> med det samme navn som i LDAP, vil alle medlemmer af LDAP-gruppen automatisk blive en del af piwigo-gruppen ved deres næste autentifikation. Dermed er det muligt at oprette <a href="admin.php?page=help&section=groups">specifik adgangskontrol</a> (begrænse adgang til et bestemt album...). Men først er det nødvendigt at fjerne brugerne fra deres LDAP-grupper, før piwigo-grupperne kan opdateres.';
$lang['Ldap groups']                                                                                                                                                                                                                                                                                                                                                           = 'LDAP-grupper';
$lang['Ldap users']                                                                                                                                                                                                                                                                                                                                                            = 'LDAP-brugere';
$lang['All LDAP users can use their ldap password everywhere on piwigo if needed.']                                                                                                                                                                                                                                                                                            = 'Ldab bruger kan bruge ldap adgangskode overalt, de har brug.';
$lang['New users when ldap auth is successfull']                                                                                                                                                                                                                                                                                                                               = 'Nye bruger i tilfælde af en vellykket ldap';
$lang['Ldap_Login Plugin']                                                                                                                                                                                                                                                                                                                                                     = 'Ldap_Login Plugin';
$lang['Ldap_Login configuration']                                                                                                                                                                                                                                                                                                                                              = 'Ldap_Login konfiguration';
$lang['Warning: LDAP Extension missing.']                                                                                                                                                                                                                                                                                                                                      = 'Php-ldap udvidelse mangler.';

// ldap server connection

$lang['Ldap server host connection']                                                    = 'Ldap server forbindelse';
$lang['If empty, standard protocol ports will be used by the software.']                = 'Hvis dette felt er tomt, standart protokol ip-port vil blev brugt.';
$lang['If empty, localhost and standard protocol ports will be used in configuration.'] = 'Hvis dette felterne er tomt, localhost og standart ip-port vil blev brugt.';
$lang['Ldap server host']                                                               = 'LDAP server adresse';
$lang['Secure connexion']                                                               = 'ssl forbindelse (ldaps)';
$lang['Ldap port']                                                                      = 'Ip-port hvor LDAP lytter';

// ldap attributes

$lang['Ldap attributes']                          = 'Attributer ldap';
$lang['Base DN']                                  = 'Base DN hvor er brugerne (f.eks : ou=people,dc=example,dc=com)';
$lang['Ldap filter']                              = '';
$lang['Attribute corresponding to the user name'] = 'Attribut der svarer til brugernavnet.';

// ldap connection credentials
$lang['Ldap connection credentials']                                    = 'Ldap forbindelse DN og adgangskode';
$lang['Let the fields blank if the ldap accept anonymous connections.'] = 'Lade felterne tomt hvid LDAP acceptere anonym forbindelser.';
$lang['Bind DN, field in full ldap style']                              = 'Bind DN med den ldap stil (f.eks : cn=admin,dc=example,dc=com).';
$lang['Bind password']                                                  = 'Adgangskode';

// test and save

$lang['Username']                                                                           = 'Din LDAP brugernavn.';
$lang['Your password']                                                                      = 'Din LDAP adgangskode.';
$lang['Ldap_Login Test']                                                                    = 'Ldap_Login Test';
$lang['You must save the settings with the Save button just up there before testing here.'] = 'Du skal gemme indstillingerne med knappen Gem lige deroppe før testning her';
$lang['Save']                                                                               = 'Gem';
$lang['Test Settings']                                                                      = 'Test instillingerne.';

// new piwigo users

$lang['If the LDAP doesn\'t furnish the mail address, users can set it up in the profile page.']       = 'Hvis LDAP ikke give mail addresse, brugerne kan give i profile side.';
$lang['Do you want to send mail to the new users, like casual piwigo users receive ?']                 = 'Vil du send beskeder til nye bruger, ligesom tilfældige brugere modtager?';
$lang['Do you allow new piwigo users to be created when users authenticate succesfully on the ldap ?'] = 'Ønsker du at oprette nye brugere, når Ldap forbindelse er en succes?';
$lang['Do you want admins to be advertised by mail in case of new users creation upon ldap login ?']   = 'Vil du send beskeder til administratorer da vi oprette nye bruger ?';
