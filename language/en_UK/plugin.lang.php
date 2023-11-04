<?php
/*
Plugin Name: Ldap_Login
Version: 1.0.1
Description: Permet de se logger via une authentification ldap
Plugin URI: http://www.22decembre.eu
Author: 22decembre
Author URI:http://www.22decembre.eu
___________________________________

Language Name: English [UK]
*/

$lang['All LDAP users can use their ldap password everywhere on piwigo if needed.'] = 'All LDAP users can use their LDAP password everywhere in Piwigo if needed.';
$lang['New users when ldap auth is successfull']                                    = 'New users when LDAP auth is successful';
$lang['Ldap_Login Plugin']                                                          = 'Ldap_Login Plugin';
$lang['Ldap_Login configuration']                                                   = 'Ldap_Login configuration';
$lang['Warning: LDAP Extension missing.']                                           = 'Warning: LDAP Extension missing.';

// ldap server connection

$lang['Ldap server host connection']                                                    = 'LDAP server connection';
$lang['If empty, standard protocol ports will be used by the software.']                = 'If empty, standard protocol ports will be used by the software.';
$lang['If empty, localhost and standard protocol ports will be used in configuration.'] = 'If empty, localhost and standard protocol ports will be used in configuration.';
$lang['Ldap server host']                                                               = 'LDAP server host';
$lang['Secure connexion']                                                               = 'Secure connection (ldaps)';
$lang['Ldap port']                                                                      = 'LDAP port';
$lang['Base DN']                                                                        = 'Base DN of LDAP server (e.g.: dc=example,dc=com):';

// ldap users
$lang['Ldap users']                               = 'LDAP users';
$lang['Users branch']                             = 'Branch where LDAP users should be found (e.g.: ou=users):';
$lang['Attribute corresponding to the user name'] = 'Attribute corresponding to the user name';
$lang['Search Ldap users ?']                      = 'Search Ldap users ? If you have your users widespreaded in several branchs or OU, you will need this. If you avoid it, you save one ldap request. You may not need it if your ldap tree is simple (e.g.: uid=user,ou=people,dc=example,dc=com).';

// ldap groups
$lang['Ldap groups']          = 'LDAP groups';
$lang['Groups branch']        = 'Branch where LDAP groups should be found (e.g.: ou=groups):';
$lang['Search Ldap groups ?'] = 'Search Ldap users ? If you have your groups widespreaded in several branchs or OU, you will need this. If you avoid it, you save one ldap request. You may not need it if your ldap tree is simple (e.g.: cn=groupname,ou=groups,dc=example,dc=com).';

$lang['If you create a <a href="admin.php?page=group_list">piwigo group</a> with the same name as an ldap one, all members of the ldap group will automatically join the piwigo group at their next authentication. This allows you to create <a href="admin.php?page=help&section=groups">specific right access management</a> (restrict access to a particaular album...).']
    = 'If you create a <a href="admin.php?page=group_list">piwigo group</a> with the same name as an ldap one, all members of the ldap group will automatically join the piwigo group at their next authentication. This allows you to create <a href="admin.php?page=help&section=groups">specific right access management</a> (restrict access to a particaular album...). Yet, in order to out this users, you must first get them out of the ldap groups, then the piwigo groups can be updated.';
$lang['To get them out of these roles, they must be sorted of the ldap group and then role updated in the <a href="admin.php?page=user_list">piwigo admin</a>. If a group is mandatory as described in the <a href="admin.php?page=plugin-Ldap_Login-newusers">new piwigo users tab</a>, then they must also belong to the users group.']
    = 'To get them out of these roles, they must be sorted of the ldap group and then role updated in the <a href="admin.php?page=user_list">piwigo admin</a>. If a group is mandatory as described in the <a href="admin.php?page=plugin-Ldap_Login-newusers">new piwigo users tab</a>, then they must also belong to the users group.';

// ldap connection credentials

$lang['Ldap connection credentials']                                    = 'LDAP connection credentials';
$lang['Let the fields blank if the ldap accept anonymous connections.'] = 'Leave the fields empty if LDAP accepts anonymous connections.';
$lang['Bind DN, field in full ldap style']                              = 'Bind DN in LDAP style (e.g.: cn=admin,dc=example,dc=com).';
$lang['Bind password']                                                  = 'Bind password';

// test and save

$lang['Username']                                                                           = 'Your LDAP username';
$lang['Your password']                                                                      = 'Your LDAP password';
$lang['Ldap_Login Test']                                                                    = 'Ldap_Login Test';
$lang['You must save the settings with the Save button just up there before testing here.'] = 'You need to save settings using the Save button above, before testing them.';
$lang['Save']                                                                               = 'Save';
$lang['Test Settings']                                                                      = 'Test settings';

// new piwigo users

$lang['Do you want admins to be advertised by mail in case of new users creation upon ldap login ?']   = 'Should admins be notified by mail in case of creation of new users upon LDAP login?';
$lang['Do you want to send mail to the new users, like casual piwigo users receive ?']                 = 'Should new users receive mail similar to casual Piwigo users?';
$lang['Do you allow new piwigo users to be created when users authenticate succesfully on the ldap ?'] = 'Should new Piwigo users be created when users authenticate succesfully via LDAP?';
$lang['Do you want admins to be advertised by mail in case of new users creation upon ldap login ?']   = 'Do you want admins to be advertised by mail in case of new users creation upon ldap login ?';
