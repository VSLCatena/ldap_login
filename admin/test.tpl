{html_head }
{/html_head} 
{*<!-- add inline JS -->*} 

{* <!-- add inline JS --> *}
{footer_script }
{/footer_script}



<div id="configContent">
<form method="post" action="{$PLUGIN_CHECK}" class="general">
    <fieldset class="form-group">
        <legend>{'ldap_login Test'|@translate}</legend>
        <i>{'You must save the settings with the Save button just up there before testing here.'|@translate}</i>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="username-addon1">{'Username'|@translate}</span>
            </div>
            <input type="text" class="form-control" id="username" name="USERNAME" value="{$USERNAME}" placeholder=""
                aria-label="{'Username'|@translate}">
        </div>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="password-addon1">{'Your password'|@translate}</span>
            </div>
            <input type="password" class="form-control" id="password" name="PASSWORD" placeholder=""
                aria-label="{'Your password'|@translate}">
        </div>
        <input type="submit" class="btn btn-primary btn-lg btn-block" value="{'Test Settings'|@translate}"
            name="check_ldap" />

</form>