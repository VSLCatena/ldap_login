<?php
/*
Plugin Name: Ldap_Login
Version: 1.0.1
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
$lang['Ldap_Login Plugin configuration']                                            = 'Configuration du plugin Ldap_Login';
$lang['Warning: LDAP Extension missing.']                                           = 'Attention: Extension LDAP manquante.';

// ldap server connection

$lang['Ldap server host connection']                                                    = 'Connection au serveur Ldap';
$lang['If empty, standard protocol ports will be used by the software.']                = 'Les ports standarts seront utilisés si ce champs est laissé vide.';
$lang['If empty, localhost and standard protocol ports will be used in configuration.'] = 'Localhost et les ports standard du protocol seront utilisés si ces champs sont laissés vides.';
$lang['Ldap server host']                                                               = 'Hote du serveur Ldap';
$lang['Secure connexion']                                                               = 'Connexion sécurisée (ldaps)';
$lang['Ldap port']                                                                      = 'Port a utiliser';

// ldap attributes

$lang['Ldap attributes']                          = 'Attributs ldap';
$lang['Base DN']                                  = 'Arbre ldap à explorer où rechercher les utilisateurs (ex : ou=users,dc=exemple,dc=com)';
$lang['Ldap filter']                              = 'Filtre de recherche';
$lang['Attribute corresponding to the user name'] = 'Attribut correspondant au nom d\'utilisateur';

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

$lang['If the LDAP doesn\'t furnish the mail address, users can set it up in the profile page.']       = 'Si le ldap ne fournit pas l\'adresse courriel, les utilisateurs peuvent l\'enregistrer dans la page de profil.';
$lang['Do you want to send mail to the new users, like casual piwigo users receive ?']                 = 'Voulez-vous envoyer le courriel habituel aux nouveaux utilisateurs de Piwigo crées par le plugin ?';
$lang['Do you allow new piwigo users to be created when users authenticate succesfully on the ldap ?'] = 'Voulez-vous créer des utilisateurs piwigo lorsque quelqu\'un se connecte avec des identifiants ldap valides ?';
$lang['Do you want admins to be advertised by mail in case of new users creation upon ldap login ?']   = 'Voulez-vous que les administrateurs du site soient prévenus lors de ces créations d\'utilisateurs ?';
