<?php

// +-----------------------------------------------------------------------+
// | This file is part of Piwigo.                                          |
// |                                                                       |
// | For copyright and license information, please view the COPYING.txt    |
// | file that was distributed with this source code.                      |
// +-----------------------------------------------------------------------+
$lang['To get them out of these roles, they must be sorted of the ldap group and then role updated in the <a href="admin.php?page=user_list">piwigo admin</a>. If a group is mandatory as described in the <a href="admin.php?page=plugin-Ldap_Login-newusers">new piwigo users tab</a>, then they must also belong to the users group.']                                      = 'Per treure\'ls d\'aquests rols, han d\'estar fora del grup LDAP i el rol actualitzat a l\'<a href="admin.php?page=user_list">administració de Piwigo</a>. Si un grup és obligatori tal i com es descriu a la pestanya <a href="admin.php?page=plugin-Ldap_Login-newusers">nous usuaris de Piwigo</a>, també han de pertànyer al grup d\'usuaris.';
$lang['Search Ldap groups ?']                                                                                                                                                                                                                                                                                                                                                  = 'Cercar usuaris de LDAP? Si teniu els grups repartits per diferents branques o OU, ho necessitareu. Si ho eviteu, us estalvieu una sol·licitud LDAP. És possible que no ho necessiteu si el vostre arbre LDAP és senzill (p.ex.: cn=nomgrup,ou=grups,dc=exemple,dc=com).';
$lang['Search Ldap users ?']                                                                                                                                                                                                                                                                                                                                                   = 'Cercar usuaris de LDAP? Si teniu els usuaris repartits per diferents branques o OU, ho necessitareu. Si ho eviteu, us estalvieu una sol·licitud LDAP. És possible que no ho necessiteu si el vostre arbre LDAP és senzill (p.ex.: uid=usuari,ou=persones,dc=exemple,dc=com).';
$lang['If you create a <a href="admin.php?page=group_list">piwigo group</a> with the same name as an ldap one, all members of the ldap group will automatically join the piwigo group at their next authentication. This allows you to create <a href="admin.php?page=help&section=groups">specific right access management</a> (restrict access to a particaular album...).'] = 'Si creeu un <a href="admin.php?page=group_list">grup de Piwigo</a> amb el mateix nom que un de LDAP, tots els membres del grup LDAP s\'uniran al grup de Piwigo en la seva propera autenticació. Això us permet crear <a href="admin.php?page=help&section=groups">gestió de drets específics d\'accés</a> (restringir l\'accés a un àlbum determinat, etc). Per tant, per deixar fora aquests usuaris, heu de treure\'ls primer dels grups de LDAP, i aleshores podreu actualitzar els grups de Piwigo.';
$lang['All LDAP users can use their ldap password everywhere on piwigo if needed.']                                                                                                                                                                                                                                                                                            = 'Tots els usuaris de LDAP poden utilitzar la seva contrasenya LDAP a tot arreu en Piwigo si és necessari.';
$lang['Attribute corresponding to the user name']                                                                                                                                                                                                                                                                                                                              = 'Atribut que correspon al nom d\'usuari';
$lang['Base DN']                                                                                                                                                                                                                                                                                                                                                               = 'DN base del servidor LDAP (p. ex: dc=example,dc=com):';
$lang['Bind DN, field in full ldap style']                                                                                                                                                                                                                                                                                                                                     = 'Vincle DN en estil LDAP (p.ex.: cn=admin,dc=example,dc=com):';
$lang['Bind password']                                                                                                                                                                                                                                                                                                                                                         = 'Contrasenya del vincle';
$lang['Do you allow new piwigo users to be created when users authenticate succesfully on the ldap ?']                                                                                                                                                                                                                                                                         = 'S\'han de crear nous usuaris de Piwigo quan els usuaris s\'autentiquen amb èxit mitjançant LDAP?';
$lang['Do you want admins to be advertised by mail in case of new users creation upon ldap login ?']                                                                                                                                                                                                                                                                           = 'Voleu que s\'avisi els administradors per correu quan es creïn nous usuaris en autenticar-se per LDAP?';
$lang['Do you want to send mail to the new users, like casual piwigo users receive ?']                                                                                                                                                                                                                                                                                         = 'Els nous usuaris han de rebre correu de manera semblant als usuaris esporàdics de Piwigo?';
$lang['Groups branch']                                                                                                                                                                                                                                                                                                                                                         = 'Branca on s\'haurien de trobar els grups LDAP (p.ex.: ou=groups):';
$lang['If empty, localhost and standard protocol ports will be used in configuration.']                                                                                                                                                                                                                                                                                        = 'Si es deixa buit, a la configuració s\'utilitzarà el localhost i els protocols estàndard.';
$lang['If empty, standard protocol ports will be used by the software.']                                                                                                                                                                                                                                                                                                       = 'Si es deixa buit, el programari utilitzarà els ports estàndard per als protocols.';
$lang['Ldap connection credentials']                                                                                                                                                                                                                                                                                                                                           = 'Credencials de connexió LDAP';
$lang['Ldap groups']                                                                                                                                                                                                                                                                                                                                                           = 'Grups de LDAP';
$lang['Ldap port']                                                                                                                                                                                                                                                                                                                                                             = 'Port de LDAP';
$lang['Ldap server host']                                                                                                                                                                                                                                                                                                                                                      = 'Host del servidor de LDAP';
$lang['Ldap server host connection']                                                                                                                                                                                                                                                                                                                                           = 'Connexió del servidor de LDAP';
$lang['Ldap users']                                                                                                                                                                                                                                                                                                                                                            = 'Usuaris de LDAP';
$lang['Ldap_Login Plugin']                                                                                                                                                                                                                                                                                                                                                     = 'Connector Ldap_Login';
$lang['Ldap_Login Test']                                                                                                                                                                                                                                                                                                                                                       = 'Prova de Ldap_Login';
$lang['Ldap_Login configuration']                                                                                                                                                                                                                                                                                                                                              = 'Configuració de Ldap_Login';
$lang['Let the fields blank if the ldap accept anonymous connections.']                                                                                                                                                                                                                                                                                                        = 'Deixeu els camps buits si LDAP accepta connexions anònimes';
$lang['New users when ldap auth is successfull']                                                                                                                                                                                                                                                                                                                               = 'Nous usuaris quan l\'autenticació mitjançant LDAP té èxit';
$lang['Save']                                                                                                                                                                                                                                                                                                                                                                  = 'Desa';
$lang['Secure connexion']                                                                                                                                                                                                                                                                                                                                                      = 'Connexió segura (ldaps)';
$lang['Test Settings']                                                                                                                                                                                                                                                                                                                                                         = 'Paràmetres de prova';
$lang['Username']                                                                                                                                                                                                                                                                                                                                                              = 'El vostre nom d\'usuari LDAP';
$lang['Users branch']                                                                                                                                                                                                                                                                                                                                                          = 'Branca on s\'haurien de trobar els usuaris LDAP (p.ex.: ou=usuaris):';
$lang['Warning: LDAP Extension missing.']                                                                                                                                                                                                                                                                                                                                      = 'Advertència: Manca l\'extensió LDAP.';
$lang['You must save the settings with the Save button just up there before testing here.']                                                                                                                                                                                                                                                                                    = 'Heu de desar els paràmetres mitjançant el botó Desa d\'aquí dalt abans de provar-los.';
$lang['Your password']                                                                                                                                                                                                                                                                                                                                                         = 'La vostra contrasenya LDAP';
