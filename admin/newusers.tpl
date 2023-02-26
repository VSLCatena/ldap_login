{literal}
<style>
label
{
    display: block;
    width: 250px;
    float: left;
}
</style>
{/literal}

<div class="titrePage">
	<h2>{'Ldap_Login Plugin'|@translate}</h2>
</div>

<i>{"If the LDAP doesn't furnish the mail address, users can set it up in the profile page."|@translate}</i>
<form method="post" action="{$PLUGIN_NEWUSERS}" class="general">

<fieldset>
	<legend>{'Ldap_Login configuration'|@translate}</legend>
	
    <p>
	{if $LD_ALLOW_NEWUSERS}
		<input type="checkbox" id="ld_allow_newusers" name="LD_ALLOW_NEWUSERS" value="{$LD_ALLOW_NEWUSERS}" checked />
	{else}
		<input type="checkbox" id="ld_allow_newusers" name="LD_ALLOW_NEWUSERS" value="{$LD_ALLOW_NEWUSERS}" />
	{/if}
	{'Do you allow new piwigo users to be created when users authenticate succesfully on the ldap ?'|@translate}
    </p>
    <p>
	{if $LD_USE_MAIL}
		<input type="checkbox" id="ld_use_mail" name="LD_USE_MAIL" value="{$LD_USE_MAIL}" checked />
	{else}
		<input type="checkbox" id="ld_use_mail" name="LD_USE_MAIL" value="{$LD_USE_MAIL}" />
	{/if}
	{'Import mailadress from LDAP to Piwigo?'|@translate}
    <input type="submit" value="{'Clear all mail addresses in Piwigo'|@translate}" name="clear_mail" />
	<i>This will delete all imported mailaddresses in Piwigo user database (excluding the main admin)</i>
	</p> 
	<p>
	{if $LD_ALLOW_PROFILE}
		<input type="checkbox" id="ld_allow_profile" name="LD_ALLOW_PROFILE" value="{$LD_ALLOW_PROFILE}" checked />
	{else}
		<input type="checkbox" id="ld_allow_profile" name="LD_ALLOW_PROFILE" value="{$LD_ALLOW_PROFILE}" />
	{/if}
	{'Redirect new users to profile page?'|@translate}
    </p>

    <p>
	{if $LD_ADVERTISE_ADMINS}
		<input type="checkbox" id="ld_advertise_admin_new_ldapuser" name="LD_ADVERTISE_ADMINS" value="{$LD_ADVERTISE_ADMINS}" checked />
	{else}
		<input type="checkbox" id="ld_advertise_admin_new_ldapuser" name="LD_ADVERTISE_ADMINS" value="{$LD_ADVERTISE_ADMINS}" />
	{/if}
	{'Do you want admins to be advertised by mail in case of new users creation upon ldap login ?'|@translate}
    </p>
    
    <p>
	{if $LD_SEND_CASUAL_MAIL}
		<input type="checkbox" id="ld_send_password_by_mail_ldap" name="LD_SEND_CASUAL_MAIL" value="{$LD_SEND_CASUAL_MAIL}" checked />
	{else}
		<input type="checkbox" id="ld_send_password_by_mail_ldap" name="LD_SEND_CASUAL_MAIL" value="{$LD_SEND_CASUAL_MAIL}" />
	{/if}
	{'Do you want to send mail to the new users, like casual piwigo users receive ?'|@translate}
    </p>
    
</fieldset>
 
<p>
<input type="submit" value="{'Save'|@translate}" name="save" />
</p>
</form>