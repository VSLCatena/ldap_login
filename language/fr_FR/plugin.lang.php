<?php
/*
Plugin Name: Ldap_Login
Description: Permet de se logger via une authentification ldap
Plugin URI: http://www.22decembre.eu
Author: 22decembre
Author URI:http://www.22decembre.eu
___________________________________

Language Name: Français [FR]
*/

$lang['All LDAP users can use their ldap password everywhere on piwigo if needed.'] = 'Les utilisateurs ldap peuvent utiler leur mot de passe ldap partout où necessaire dans cette galerie piwigo.';
$lang['New users when ldap auth is successfull']                                    = 'Nouveaux utilisateurs piwigo en cas de connection LDAP';
$lang['Ldap_Login Plugin']                                                          = 'Ldap_Login Plugin';
$lang['Warning: LDAP Extension missing.']                                           = 'Attention: Extension LDAP manquante.';

// ldap server connection

$lang['Ldap server host connection']                                                    = 'Connection au serveur Ldap';
$lang['If empty, standard protocol ports will be used by the software.']                = 'Les ports standarts seront utilisés si ce champs est laissé vide.';
$lang['If empty, localhost and standard protocol ports will be used in configuration.'] = 'Localhost et les ports standard du protocol seront utilisés si ces champs sont laissés vides.';
$lang['Ldap server host']                                                               = 'Hote du serveur Ldap';
$lang['Secure connexion']                                                               = 'Connexion sécurisée (ldaps)';
$lang['Ldap port']                                                                      = 'Port a utiliser';
$lang['Base DN']                                                                        = 'Racine du serveur LDAP (e.g.: dc=example,dc=com):';

// ldap attributes

$lang['Base DN']                                  = 'Arbre ldap à explorer où rechercher les utilisateurs et les groupes (ex : dc=exemple,dc=com)';
$lang['Attribute corresponding to the user name'] = 'Attribut correspondant au nom d\'utilisateur';

// ldap users
$lang['Ldap users']                               = 'utilisateurs Ldap';
$lang['Users branch']                             = 'Branche où les utilisateurs LDAP peuvent être trouvés (e.g.: ou=users):';
$lang['Attribute corresponding to the user name'] = 'Attribut correspondant au nom d\'utilisateur (e.g.: uid):';
$lang['Search Ldap users ?']                      = 'Faire une recherche sur le nom des utilisateurs ? Vous n\'avez pas besoin de cela si votre annuaire est assez simple (e.g.: uid=user,ou=people,dc=example,dc=com). Si vous n\'utilisez pas cette option, vous épargnez une requete Ldap.';

// ldap groups
$lang['Ldap groups']          = 'groupes Ldap';
$lang['Groups branch']        = 'Branche où les groupes LDAP peuvent être trouvés (e.g.: ou=groups):';
$lang['Search Ldap groups ?'] = 'Faire une recherche sur le nom des groupes ? Vous n\'avez pas besoin de cela si votre annuaire est assez simple (e.g.: cn=groupname,ou=groups,dc=example,dc=com). Si vous n\'utilisez pas cette option, vous épargnez une requete Ldap.';
$lang['If you create a <a href="admin.php?page=group_list">piwigo group</a> with the same name as an ldap one, all members of the ldap group will automatically join the piwigo group at their next authentication. This allows you to create <a href="admin.php?page=help&section=groups">specific right access management</a> (restrict access to a particaular album...).']
    = 'Si vous créez un <a href="admin.php?page=group_list">groupe piwigo</a> avec le même nom qu\'un groupe ldap, tous les membres de ce groupe ldap seront membres du groupe piwigo à leur prochaine connexion. Ceci vous permet de mettre en place des <a href="admin.php?page=help&section=groups">contrôles d\'accès</a>. Pour enlever les utilisateurs de ces groupes, ils doivent d\'abord être enlevés des groupes ldap, ensuite seulement des groupes piwigo.';
$lang['To get them out of these roles, they must be sorted of the ldap group and then role updated in the <a href="admin.php?page=user_list">piwigo admin</a>. If a group is mandatory as described in the <a href="admin.php?page=plugin-Ldap_Login-newusers">new piwigo users tab</a>, then they must also belong to the users group.']
    = 'Pour retirer ces droits d\'administration, vous devez enlever ces utilisateurs du groupe ldap puis mettre à jour leur role dans la <a href="admin.php?page=user_list">config piwigo</a>. Si l\'appartenance à un groupe est obligatoire comme indiqué dans <a href="admin.php?page=plugin-Ldap_Login-newusers">l\'onglet nouveaux utilisateurs</a>, alors les administrateurs et webmasters ldap doivent aussi appartenir à ce groupe ldap.';

// ldap connection credentials

$lang['Ldap connection credentials']                                    = 'Identifiants de connection LDAP';
$lang['Let the fields blank if the ldap accept anonymous connections.'] = 'Laissez les champs vides si le ldap accepte les connexions anonymes.';
$lang['Bind DN, field in full ldap style']                              = 'Identifiant de connection, avec syntaxe LDAP complète (par ex : cn=admin,dc=example,dc=com).';
$lang['Bind password']                                                  = 'Mot de passe de connection';

// test and save

$lang['Username']                                                                           = 'Votre nom d\'utilisateur LDAP';
$lang['Your password']                                                                      = 'Votre mot de passe LDAP.';
$lang['Ldap_Login Test']                                                                    = 'Test du plugin Ldap_Login';
$lang['You must save the settings with the Save button just up there before testing here.'] = 'Vous devez sauvegarder les paramètres avec le bouton Enregistrer juste au dessus avant de faire ce test.';
$lang['Test Settings']                                                                      = 'Tester les paramètres';
$lang['Save']                                                                               = 'Enregistrer';

// new piwigo users

$lang['Do you want to send mail to the new users, like casual piwigo users receive ?']                 = 'Voulez-vous envoyer le courriel habituel aux nouveaux utilisateurs de Piwigo crées par le plugin ?';
$lang['Do you allow new piwigo users to be created when users authenticate succesfully on the ldap ?'] = 'Voulez-vous créer des utilisateurs piwigo lorsque quelqu\'un se connecte avec des identifiants ldap valides ?';
$lang['Do you want admins to be advertised by mail in case of new users creation upon ldap login ?']   = 'Voulez-vous que les administrateurs du site soient prévenus lors de ces créations d\'utilisateurs ?';

$lang['Ldap_Login configuration'] = 'configuration Ldap_Login ';
