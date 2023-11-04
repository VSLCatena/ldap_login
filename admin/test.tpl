{combine_css path=$LDAP_LOGIN_PATH|cat:"style.css"} 
{html_head }
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous"> 
{/html_head} 
{*<!-- add inline JS -->*} 

{combine_script id="popper" require="jquery" path="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"} 
{combine_script id="bootstrap" require="jquery" path="https://cdn.jsdelivr.net/npm/bootstrap@5.2/dist/js/bootstrap.min.js"}


{* <!-- add inline JS --> *}
{footer_script require="jquery"}
{/footer_script}

<div class="titrePage">
	<h2>{'Ldap_Login Plugin'|@translate}</h2>
</div>

<div id="configContent">
    {if $LD_AUTH_TYPE=='ld_auth_ldap'}
        <form method="post" action="{$PLUGIN_CHECK}" class="general">
        <div class="container">
            <fieldset class="form-group">
                <legend>{'ldap_login Test'|@translate}</legend>
                <i>{'Test the configuration of your authentication below.'|@translate}</i>

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

                {if (!empty($LD_CHECK_LDAP))}
                    {$LD_CHECK_LDAP}
                {/if}
            </fieldset>
            </div>
        </form>
    {elseif $LD_AUTH_TYPE=='ld_auth_azure'}
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <center>{if isset($OAUTH_URL)}<a href="{{$OAUTH_URL}}">{else}{/if}<img
                                src="https://learn.microsoft.com/en-us/azure/active-directory/develop/media/howto-add-branding-in-apps/ms-symbollockup_signin_dark.png"></a>
                    </center>
                    {if isset($JWT_CONTENT)}
                        <hr>
                        <h3>id_token</h3>
                        <pre>{print_r($JWT_CONTENT['id_token'],true)}</pre>
                        <hr>
                        <h3>access_token</h3>
                        <pre>{print_r($JWT_CONTENT['access_token'],true)}</pre>
                        </p>
                    {/if}
                </div>
            </div>
</div>
    {/if}
</div>