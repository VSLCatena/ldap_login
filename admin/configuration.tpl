{combine_css path=$LDAP_LOGIN_PATH|cat:"style.css"}
<h2>{'Ldap_Login Plugin'|@translate}</h2>

<div id="configContent">

<p>{'All LDAP users can use their ldap password everywhere on piwigo if needed.'|@translate}</p>
{if isset($WARN_GENERAL) and $WARN_GENERAL}<i style="color:red;">{$WARN_GENERAL|@translate}</i>{/if}<br>
<form method="post" action="{$PLUGIN_ACTION}" class="general">

	{if (!extension_loaded('ldap'))}
		<p style="color:red;">{'Warning: LDAP Extension missing.'|@translate}</p>
		<br />
	{/if}


	<fieldset class="mainConf">
	<legend>{'General settings'|@translate}</legend>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_forgot_url"><span>{'Url for password reset'|@translate}</span></label>
	<input size="70" type="text" id="ld_forgot_url" name="LD_FORGOT_URL" value="{$LD_FORGOT_URL}" />
	{if isset($WARN_LD_FORGOT_URL) and $WARN_LD_FORGOT_URL}<i style="color:red;">{$WARN_LD_FORGOT_URL}</i>{/if}<br>
	<i style="margin:15%;">Company directory password reset URL (https://mycompany.com/passreset.php) Default: Piwigo "password.php"</i>
	<br>
	</p>
	<p>
	<label style="display:inline-block; width:15%;" style="display:inline-block; width:120px; text-align:right;" for="ld_debug_location"><span>{'Log location'|@translate}</span></label>
	<input size="70" type="text" id="ld_debug_location" name="LD_DEBUG_LOCATION" value="{$LD_DEBUG_LOCATION}"/>
	{if isset($WARN_LD_DEBUG_LOCATION) and $WARN_LD_DEBUG_LOCATION}<i style="color:red;">{$WARN_LD_DEBUG_LOCATION}</i>{/if}<br>
	<i style="margin:15%;">Field to define the location of debug.log. Protect the location with .htaccess or store in absolute paths like /var/log/ (most secure). Relative paths always resolve to to ./logs/ldap_login.log</i><br>
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_debug">
        <input type='hidden' value='0' name='LD_DEBUG'>
        <input type="checkbox" id="ld_debug" name="LD_DEBUG" value="1" {if $LD_DEBUG== 1}checked{/if}>
        {'Enable logs'|@translate}</label>{if isset($WARN_LD_DEBUG) and $WARN_LD_DEBUG}<i style="color:red;">{$WARN_LD_DEBUG}</i>{/if}
    </p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_debug_clearupdate">
        <input type='hidden' value='0' name='LD_DEBUG_CLEARUPDATE'>
        <input type="checkbox" id="ld_debug_clearupdate" name="LD_DEBUG_CLEARUPDATE" value="1" {if $LD_DEBUG_CLEARUPDATE== 1}checked{/if}>
	{'Clear logs after plugin update'|@translate}</label>
	{if isset($WARN_LD_DEBUG_CLEARUPDATE) and $WARN_LD_DEBUG_CLEARUPDATE}<i style="color:red;">{$WARN_LD_DEBUG_CLEARUPDATE}</i>{/if}
    </p>
	<div style="margin:1em;">
	<label style="display:inline-block; width:15%;" style="display:inline-block; width:120px; text-align:right;" for="ld_debug_level"><span>{'Debug level'|@translate}</span></label>
		<select id="ld_debug_level" name="LD_DEBUG_LEVEL" value="{$LD_DEBUG_LEVEL}">
			<option id="ld_debug_level_fatal" name="LD_DEBUG_LEVEL_fatal" value="fatal" disabled {if 'fatal' == {$LD_DEBUG_LEVEL}}selected{/if} >Fatal</option>
			<option id="ld_debug_level_error" name="LD_DEBUG_LEVEL_error" value="error" disabled {if 'error' == {$LD_DEBUG_LEVEL}}selected{/if} >  Error</option>
			<option id="ld_debug_level_warning" name="LD_DEBUG_LEVEL_warning" value="warning"disabled {if 'warning' == {$LD_DEBUG_LEVEL}}selected{/if} >Warning</option>
			<option id="ld_debug_level_info" name="LD_DEBUG_LEVEL_info" value="info" disabled {if 'info' == {$LD_DEBUG_LEVEL}}selected{/if} >Info</option>
			<option id="ld_debug_level_debug" name="LD_DEBUG_LEVEL_debug" value="debug"  {if 'debug' == {$LD_DEBUG_LEVEL}}selected{/if} >Debug</option>
		</select> 
		<div class="tooltip">More info..<div class="tooltiptext">
			<div style="margin:1em; ">	FATAL: The service/app is going to stop or becomes unusable. An operator should definitely look into this soon.<br>
				ERROR: Fatal for a particular request, but the service/app continues servicing. An operator should look at this soon(ish)<br>
				WARN: A note on something that should probably be looked at by an operator eventually.<br>
				INFO: Detail on regular operation.<br>
				DEBUG: Anything else, i.e. too verbose to be included in INFO level.
			</div></div>
		</div>
	{if isset($WARN_LD_DEBUG_LEVEL) and $WARN_LD_DEBUG_LEVEL}<i style="color:red;">{$WARN_LD_DEBUG_LEVEL}</i>{/if} 
	
	</div>	
	</fieldset>
	
	
	<fieldset class="mainConf">
	<legend>{'Ldap server host connection'|@translate}</legend>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_host">{'Server location'|@translate}</label>
	<input size="70" type="text" id="ld_host" name="LD_HOST" value="{$LD_HOST}" />
	{if isset($WARN_LD_HOST) and $WARN_LD_HOST}<i style="color:red;">{$WARN_LD_HOST}</i>{/if}<br>
	<i style="margin:15%;">{'IP or hostname of the directory server.'|@translate}</i><br>
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_port">{'Ldap port'|@translate}</label>
	<input type="text" id="ld_port" name="LD_PORT" value="{$LD_PORT}" />
	{if isset($WARN_LD_PORT) and $WARN_LD_PORT}<i style="color:red;">{$WARN_LD_PORT}</i>{/if}<br>
	<i style="margin:15%;">{'If empty, localhost and standard protocol ports (389/636) will be used in configuration.'|@translate}</i><br>
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_use_ssl">
        <input type='hidden' value='0' name='LD_USE_SSL'>
        <input type="checkbox" id="ld_use_ssl" name="LD_USE_SSL" value="1" {if $LD_USE_SSL== 1}checked{/if}>
	{'Secure connexion'|@translate}</label>
	{if isset($WARN_LD_USE_SSL) and $WARN_LD_USE_SSL}<i style="color:red;">{$WARN_LD_USE_SSL}</i>{/if}<br><br>
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_basedn">{'Base DN:'|@translate}</label>
	<input size="70" type="text" id="ld_basedn" name="LD_BASEDN" value="{$LD_BASEDN}" />
	{if isset($WARN_LD_BASEDN) and $WARN_LD_BASEDN}<i style="color:red;">{$WARN_LD_BASEDN}</i>{/if}<br>
	<i style="margin:15%;">{'The highest accessible OU or Base DN'|@translate}</i><br>
	</p>
    </fieldset>
    
	
   <fieldset class="mainConf">
	<legend>{'User Schema Settings'|@translate}</legend>
	<i>Required for user filter: (&(&(objectClass=<b>User_Object_Class</b>)(<b>Username_Attribute</b>=Login_Form_username))(<b>User_Object_Filter</b>)</i>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_user_class">{'User Object Class:'|@translate}</label>
	<input size="70" type="text" id="ld_user_class" name="LD_USER_CLASS" value="{$LD_USER_CLASS}" />
	{if isset($WARN_LD_USER_CLASS) and $WARN_LD_USER_CLASS}<i style="color:red;">{$WARN_LD_USER_CLASS}</i>{/if}<br>
	<i style="margin:15%;">{'The LDAP user object class type to use when loading users'|@translate}</i><br>
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_user_attr">{'Username Attribute'|@translate}</label>
	<input size="70" type="text" id="ld_user_attr" name="LD_USER_ATTR" value="{$LD_USER_ATTR}" />
	{if isset($WARN_LD_USER_ATTR) and $WARN_LD_USER_ATTR}<i style="color:red;">{$WARN_LD_USER_ATTR}</i>{/if}<br>
	<i style="margin:15%;">{'The attribute field to use on the user object. Examples: cn, sAMAccountName.'|@translate}</i>	
    </p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_user_filter">{'User Object Filter:'|@translate}</label>
	<input size="70" type="text" id="ld_user_filter" name="LD_USER_FILTER" value="{$LD_USER_FILTER}" />
	{if isset($WARN_LD_USER_FILTER) and $WARN_LD_USER_FILTER}<i style="color:red;">{$WARN_LD_USER_FILTER}</i>{/if}<br>
	<i style="margin:15%;">{'The filter to use when searching user objects'|@translate}</i><br>	
	</p>
	</fieldset>
	
	
	
   <fieldset class="mainConf">
	<legend>{'Group Schema Settings'|@translate}</legend>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_class">{'Group Object Class:'|@translate}</label>
	<input size="70" type="text" id="ld_group_class" name="LD_GROUP_CLASS" value="{$LD_GROUP_CLASS}" />
	{if isset($WARN_LD_GROUP_CLASS) and $WARN_LD_GROUP_CLASS}<i style="color:red;">{$WARN_LD_GROUP_CLASS}</i>{/if}<br>
	<i style="margin:15%;">{'LDAP attribute objectClass value to search for when loading groups.'|@translate}</i>	
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_filter">{'Group Object Filter:'|@translate}</label>
	<input size="70" type="text" id="ld_group_filter" name="LD_GROUP_FILTER" value="{$LD_GROUP_FILTER}" />
	{if isset($WARN_LD_GROUP_FILTER) and $WARN_LD_GROUP_FILTER}<i style="color:red;">{$WARN_LD_GROUP_FILTER}</i>{/if}<br>
	<i style="margin:15%;">{'The filter to use when searching group objects.'|@translate}</i>	
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_attr">{'Group Name Attribute:'|@translate}</label>
	<input size="70" type="text" id="ld_group_attr" name="LD_GROUP_ATTR" value="{$LD_GROUP_ATTR}" />
	{if isset($WARN_LD_GROUP_ATTR) and $WARN_LD_GROUP_ATTR}<i style="color:red;">{$WARN_LD_GROUP_ATTR}</i>{/if}<br>
	<i style="margin:15%;">{'The attribute field to use when loading the group name.'|@translate}</i>	
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_desc">{'Group Description:'|@translate}</label>
	<input size="70" type="text" id="ld_group_desc" name="LD_GROUP_DESC" value="{$LD_GROUP_DESC}" />
	{if isset($WARN_LD_GROUP_DESC) and $WARN_LD_GROUP_DESC}<i style="color:red;">{$WARN_LD_GROUP_DESC}</i>{/if}<br>
	<i style="margin:15%;">{'The attribute field to use when loading the group description.'|@translate}</i>	
    </p>
	</fieldset>
	
	
	
   <fieldset class="mainConf">
	<legend>{'Membership Schema Settings'|@translate}</legend>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_member_attr">{'Group Membership Attribute:'|@translate}</label>
	<input size="70" type="text" id="ld_group_member_attr" name="LD_GROUP_MEMBER_ATTR" value="{$LD_GROUP_MEMBER_ATTR}" />
	{if isset($WARN_LD_GROUP_MEMBER_ATTR) and $WARN_LD_GROUP_MEMBER_ATTR}<i style="color:red;">{$WARN_LD_GROUP_MEMBER_ATTR}</i>{/if}<br>
	<i style="margin:15%;">{'The attribute field to use when loading the group members from the group.'|@translate}</i>	
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_user_member_attr">{'User Membership Attribute:'|@translate}</label>
	<input size="70" type="text" id="ld_user_member_attr" name="LD_USER_MEMBER_ATTR" value="{$LD_USER_MEMBER_ATTR}" />
	{if isset($WARN_LD_USER_MEMBER_ATTR) and $WARN_LD_USER_MEMBER_ATTR}<i style="color:red;">{$WARN_LD_USER_MEMBER_ATTR}</i>{/if}<br>
	<i style="margin:15%;">{'The attribute field when loading groups from a user.'|@translate}</i>	
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_membership_user">
        <input type='hidden' value='0' name='LD_MEMBERSHIP_USER'>
        <input type="checkbox" id="ld_membership_user" name="LD_MEMBERSHIP_USER" value="1" {if $LD_MEMBERSHIP_USER== 1}checked{/if}>
	{'Use user membership attribute'|@translate}</label>
	{if isset($WARN_LD_MEMBERSHIP_USER) and $WARN_LD_MEMBERSHIP_USER}<i style="color:red;">{$WARN_LD_MEMBERSHIP_USER}</i>{/if}
	</p>


    </fieldset>
	
   <fieldset class="mainConf">
	<legend>{'Membership Settings'|@translate}</legend>
	<a href="https://piwigo.org/doc/doku.php?id=user_documentation:use:features:user_status" target="_blank	" style="position: relative;display: inline-block;border-bottom: 1px dotted black;margin-left: 10px;">More info about built-in groups on Piwigo.org</a><br>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_user">{'Group corresponding with users:'|@translate}</label>
	<input size="70" type="text" id="ld_group_user" name="LD_GROUP_USER" value="{$LD_GROUP_USER}" />
	{if isset($WARN_LD_GROUP_USER) and $WARN_LD_GROUP_USER}<i style="color:red;">{$WARN_LD_GROUP_USER}</i>{/if}<br>
	<i style="margin:15%;">{'The group that will get user rights (DN).'|@translate}</i>	
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_admin">{'Group corresponding with administrators:'|@translate}</label>
	<input size="70" type="text" id="ld_group_admin" name="LD_GROUP_ADMIN" value="{$LD_GROUP_ADMIN}" />
	{if isset($WARN_LD_GROUP_ADMIN) and $WARN_LD_GROUP_ADMIN}<i style="color:red;">{$WARN_LD_GROUP_ADMIN}</i>{/if}<br>
	<i style="margin:15%;">{'The group that will get admininistrator rights (DN).'|@translate}</i>	
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_WEBMASTER">{'Group corresponding with webmasters:'|@translate}</label>
	<input size="70" type="text" id="ld_group_webmaster" name="LD_GROUP_WEBMASTER" value="{$LD_GROUP_WEBMASTER}" />
	{if isset($WARN_LD_GROUP_WEBMASTER) and $WARN_LD_GROUP_WEBMASTER}<i style="color:red;">{$WARN_LD_GROUP_WEBMASTER}</i>{/if}<br>
	<i style="margin:15%;">{'The group that will get webmaster rights (DN).'|@translate}</i>	
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_user_active">
        <input type='hidden' value='0' name='LD_GROUP_USER_ACTIVE'>
        <input type="checkbox" id="ld_group_user_active" name="LD_GROUP_USER_ACTIVE" value="1" {if $LD_GROUP_USER_ACTIVE== 1}checked{/if}>
	{'Use user groups'|@translate}</label>
	<i>Note: Minimum membership to gain access. Disabled, no check for group membership.</i>
	{if isset($WARN_LD_GROUP_USER_ACTIVE) and $WARN_LD_GROUP_USER_ACTIVE}<i style="color:red;">{$WARN_LD_GROUP_USER_ACTIVE}</i>{/if}<br>
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_admin_active">
        <input type='hidden' value='0' name='LD_GROUP_ADMIN_ACTIVE'>
        <input type="checkbox" id="ld_group_admin_active" name="LD_GROUP_ADMIN_ACTIVE" value="1" {if $LD_GROUP_ADMIN_ACTIVE== 1}checked{/if}>
	{'Use administrator groups.'|@translate}</label>
	<i>Note: Dynamic when enabled and persistent when disabled. Change manual 'level' of user when disabled.</i>
	{if isset($WARN_LD_GROUP_ADMIN_ACTIVE) and $WARN_LD_GROUP_ADMIN_ACTIVE}<i style="color:red;">{$WARN_LD_GROUP_ADMIN_ACTIVE}</i>{/if}
	</p>
	<p>
	<label style="display:inline-block; width:15%;" for="ld_group_webmaster_active">
	<input type='hidden' value='0' name='LD_GROUP_ADMIN_ACTIVE'>
        <input type="checkbox" id="ld_group_webmaster_active" name="LD_GROUP_WEBMASTER_ACTIVE" value="1" {if $LD_GROUP_WEBMASTER_ACTIVE== 1}checked{/if}>
	{'Use Webmaster groups.'|@translate}</label>
	<i>Note: Dynamic when enabled and persistent when disabled. Change manual 'level' of user when disabled.</i>
	{if isset($WARN_LD_GROUP_WEBMASTER_ACTIVE) and $WARN_LD_GROUP_WEBMASTER_ACTIVE}<i style="color:red;">{$WARN_LD_GROUP_WEBMASTER_ACTIVE}</i>{/if}
	</p>
    </fieldset>	
	
	
	
	
	
	
	
<!--	
    <fieldset class="mainConf">
	<legend>{'Ldap attributes'|@translate}</legend>
	<ul>
		<li>
			<label for="ld_server">{'Server mode:'|@translate}</label><br>
				<select name="LD_SERVER" id="ld_server">
				  <option value="ad" 		{if 'ad' == {$LD_SERVER}}selected{/if}>Active Directory</option>
				  <option value="openldap"	{if 'openldap' == {$LD_SERVER}}selected{/if}>OpenLDAP</option>
				</select>
		</li>
		<i>{'If using MS AD, choose Active Directory, else choose OpenLDAP'|@translate}</i>		
		<li>
			<label for="basedn">{'Base DN'|@translate}</label>
			<br>
			<input size="70" type="text" id="basedn" name="BASEDN" value="{$BASEDN}" />
		</li>
		<br>
		<li>
			<label for="ld_attr">{'Attribute corresponding to the user name'|@translate}</label>
			<br>
			<input type="text" id="ld_attr" name="LD_ATTR" value="{$LD_ATTR}" />
		</li>
		<i>For AD it is often 'sAMAccountName'. For OpenLDAP, it is often 'userid'. Please check your directory details for the correct attribute.</i>
	</ul>
    </fieldset>
    <fieldset class="mainConf">
	<legend>{'Ldap Group attributes'|@translate}</legend>
	<ul>
		<li>
			<label for="groupdn">{'DN of group for membership-check and calculated CN (using RegEx)'|@translate}</label>
			<br>
			<input size="70" type="text" id="ld_group" name="LD_GROUP" value="{$LD_GROUP}" /><input disabled type="text" value='{$LD_GROUP|regex_replace:"/,[a-z]+.*/":""}' />
		</li>
		<li>
		<label for="groupdn_class">{'Class of group:'|@translate}</label>
		<br>
		<select name="LD_GROUP_CLASS" id="ld_group_class" >
		  <option value="group"  		{if 'group' == {$LD_GROUP_CLASS}}selected{/if}>group</option>
		  <option value="posixgroup" 	{if 'posixgroup' == {$LD_GROUP_CLASS}}selected{/if}>posixGroup</option>
		</select>
		</li>
		<i>{'Depending on server configuration the class may differ, choose accordingly. OpenLDAP: posixGroup. AD: group.'|@translate}</i>
		<br>
		<li>
		<label for="ld_group_member_attrib">{'Attribute for members in group:'|@translate}</label>
		<br>
		<select name="LD_GROUP_MEMBER_ATTRIB" id="ld_group_member_attrib" >
		  <option value="member"  		>member</option>
		  <option value="memberUid" 	{if 'memberUid' == {$LD_GROUP_MEMBER_ATTRIB}}selected{/if}>memberUid</option>
		</select>
		</li>
		<i>{'Depending on server configuration the attribute may differ, choose accordingly. OpenLDAP: memberUid. AD:member.'|@translate}</i>
		
	</ul>
    </fieldset>
-->    
    <fieldset class="mainConf">
	<legend>{'Ldap connection credentials'|@translate}</legend>
	<ul>
		<li>
			<label for="ld_binddn">{'Bind (Service account) DN'|@translate}</label>
			<br>
			<input size="100" type="text" id="ld_binddn" name="LD_BINDDN" value="{$LD_BINDDN}" />
			{if isset($WARN_LD_BINDDN) and $WARN_LD_BINDDN}<i style="color:red;">{$WARN_LD_BINDDN}</i>{/if}
		</li>
		
		<li>
			<label for="ld_bindpw">{'Bind (Service account) password'|@translate}</label>
			<br>
			<input type="password" id="ld_bindpw" name="LD_BINDPW" />
			{if isset($WARN_LD_BINDPW) and $WARN_LD_BINDPW}<i style="color:red;">{$WARN_LD_BINDPW}</i>{/if}
		</li>
	</ul>
	<i>{'Keep BOTH fields blank if the ldap accept anonymous connections. '|@translate}</i>
</fieldset>
 
<p>
<input type="submit" value="{'Save'|@translate}" name="save" />
<input type="submit" value="{'Save & test'|@translate}" name="savetest" /><br>
</p>

<input type="submit" value="{'Reset to AD-values'|@translate}" name="RESET_AD" /><br><br>
<input type="submit" value="{'Reset to OpenLDAP-values'|@translate}" name="RESET_OL" />

</form>

<form method="post" action="{$PLUGIN_CHECK}" class="general">
<fieldset class="mainConf">
<legend>{'Ldap_Login Test'|@translate}</legend>
<i>{'You must save the settings with the Save button just up there before testing here.'|@translate}</i>
	<ul>
		<li>
			<label for="username">{'Username'|@translate}</label>
			<br>
			<input type="text" id="username" name="USERNAME" value="{$USERNAME}" />
		</li>
		
		<li>
			<label for="ld_attr">{'Your password'|@translate}</label>
			<br>
			<input type="password" id="password" name="PASSWORD" value="{if isset($WARN_LD_BINDPW)}{$PASSWORD}{/if}" />
		</li>
	</ul>
	
	{if (!empty($LD_CHECK_LDAP))}
 		{$LD_CHECK_LDAP}
	{/if}

</fieldset>
<p><input type="submit" value="{'Test Settings'|@translate}" name="check_ldap" /></p>

</form>
</div>
