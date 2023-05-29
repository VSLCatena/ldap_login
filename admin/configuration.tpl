{combine_css path=$LDAP_LOGIN_PATH|cat:"style.css"} 
{html_head }
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous"> 
{/html_head} 
{*<!-- add inline JS -->*} 

{combine_script id="popper" require="jquery" path="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"} 
{combine_script id="bootstrap" require="jquery" path="https://cdn.jsdelivr.net/npm/bootstrap@5.2/dist/js/bootstrap.min.js"}


{* <!-- add inline JS --> *}
{footer_script require="jquery"}

    function updateExampleFilter() {
        
        var userclass = document.getElementById("ld_user_class").value;
        var attr = document.getElementById("ld_user_attr").value;
        var filter = document.getElementById("ld_user_filter").value;
        var username = "Login_Form_username"
        if(filter.length <= 0) {
            var string = '(&(objectClass=' + userclass + ')(' + attr + '=' + username + '))'
        } else {
            var string = '(&(&(objectClass=' + userclass + ')(' + attr + '=' + username + '))(' + filter +'))'
        }
        var exampleDiv = document.getElementById("exampleDiv")
        exampleDiv.value = string
    }
    
    
    
	function disableADFields(obj) {
		switch (obj.value) {
		  case 'RFC2307':
			var rfc = {
				'ld_user_attr': 'cn',
				'ld_user_class': 'inetOrgPerson',
				'ld_group_class': 'groupOfNames'
			}
			break;
		  case 'RFC2307BIS':
			var rfc = {
			'ld_user_attr': 'sAMAccountname',
			'ld_user_class': 'user',
			'ld_group_class': 'group'
			}
			break;
		  case 'OTHER':
			var rfc = {
			'ld_user_attr': '',
			'ld_user_class': '',
			'ld_group_class': ''
			}
			break;
		}		
		for (var key in rfc) {      
			var input = document.getElementById(key);
			input.value = rfc[key]
			input.disabled = obj.value != "OTHER";
		}
        updateExampleFilter();
	}
    function toggleAuthFields(obj) {
		switch (obj.value) {
		  case 'ld_auth_azure':
            $( "#LdapSettingsBlock" ).addClass( "visually-hidden " );
            $( "#AzureSettingsBlock" ).removeClass( "visually-hidden" );
            
            $( "#nav-link-tabUserSchema" ).addClass( "disabled" );
            $( "#nav-link-tabMembershipSchema" ).addClass( "disabled" );
            $( "#nav-link-tabGroupSchema" ).addClass( "disabled" );
            
            $('a[href="#tabMembershipSettings"]').tab('show');
			break;
		  case 'ld_auth_ldap':
            $( "#AzureSettingsBlock" ).addClass( "visually-hidden " );
            $( "#LdapSettingsBlock" ).removeClass( "visually-hidden" );
            
            $( "#nav-link-tabUserSchema" ).removeClass( "disabled" );
            $( "#nav-link-tabMembershipSchema" ).removeClass( "disabled" );
            $( "#nav-link-tabGroupSchema" ).removeClass( "disabled" );
            
            $('a[href="#tabUserSchema"]').tab('show');
			break;
		}		


	}

    $( document ).ready(function() {
        updateExampleFilter();
        console.log("ready")
    });  
{/footer_script}


<h2>{'ldap_login Plugin'|@translate}</h2>

<div id="configContent">
<form method="post" action="{$PLUGIN_ACTION}" class="general">
    <div class="container">
            <fieldset class="form-group">
                <div class="row">
                    <div class="col-12">
                        <p>{'Use Azure or LDAP for user authentication'|@translate}</p> {if isset($WARN_GENERAL)}<i
                            style="color:red;">{$WARN_GENERAL|@translate}</i>{/if}
                        <br>
                        {if (!extension_loaded('ldap'))}
                            <p style="color:red;">{'Warning: LDAP Extension missing.'|@translate}</p>
                        <br /> {/if}
                        <legend>{'General settings'|@translate}</legend>
                        <div class="card">
                            <div class="card-body">                        
                                <div class="form-inline">
                                    <div class="form-group row">
                                        <label for="ld_forgot_url" class="col-sm-2 col-form-label">{'Password Reset URL'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" id="ld_forgot_url" name="LD_FORGOT_URL" class="form-control"
                                                value="{$LD_FORGOT_URL}" placeholder="https://piwigo.company.tld/password.php"
                                                aria-describedby="ld_forgot_url_help">
                                                <small id="ld_forgot_url_help" class="text-muted">
                                                    {'Company directory password reset URL (https://mycompany.com/passreset.php) Default: Piwigo "password.php"'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="form-inline">
                                    <div class="form-group row">
                                        <label for="ld_debug_location" class="col-sm-2 col-form-label">{'Log location'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" id="ld_debug_location" name="LD_DEBUG_LOCATION"
                                                class="form-control" value="{$LD_DEBUG_LOCATION}" placeholder="/var/log/"
                                                aria-describedby="ld_debug_location_label">
                                            <small id="ld_debug_location_help" class="text-muted">
                                                {'Log location help: Field to define the location of ldap_login.log. Protect the location with .htaccess or store in /var/log/ (most secure)'|@translate}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                

                                <div class="form-check form-switch">
                                    <input type='hidden' value='0' name='LD_DEBUG'>
                                    <input class="form-check-input" id="ld_debug" name="LD_DEBUG" type="checkbox"
                                        value="1"  {if $LD_DEBUG== 1}checked{/if}>
                                    <label class="form-check-label" for="ld_debug">
                                        {'Write to ldap_login.log file'|@translate}
                                    </label>
                                </div>

                                <div class="form-check form-switch">
                                    <input type='hidden' value='0' name='LD_DEBUG_PHP'>
                                    <input class="form-check-input" id="ld_debug_php" name="LD_DEBUG_PHP" type="checkbox"
                                        value="1"  {if $LD_DEBUG_PHP== 1}checked{/if}>
                                    <label class="form-check-label" for="ld_debug_php">
                                        {'Write to PHP error log'|@translate}
                                    </label>
                                </div>

                                <div class="form-check form-switch">
                                    <input type='hidden' value='0' name='LD_DEBUG_CLEARUPDATE'>
                                    <input class="form-check-input" id="ld_debug_clearupdate" name="LD_DEBUG_CLEARUPDATE"
                                        type="checkbox" value="1"  {if $LD_DEBUG_CLEARUPDATE== 1}checked{/if}>
                                    <label class="form-check-label" for="ld_debug_clearupdate">
                                        {'Clear logs after plugin update'|@translate}
                                    </label>
                                </div>
                                <br>

                                <div class="form-inline">
                            
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label"
                                            for="ld_debug_level">{'Debug level'|@translate}</label>
                                        <div class="col-sm-4">
                                            <select class="form-select" aria-label="{'Debug level'|@translate}"
                                                id="ld_debug_level" name="LD_DEBUG_LEVEL">
                                                <option id="ld_debug_level_fatal" name="LD_DEBUG_LEVEL_fatal" value="fatal"
                                                    disabled {if 'fatal'==$LD_DEBUG_LEVEL}selected{/if}>Fatal</option>
                                                <option id="ld_debug_level_error" name="LD_DEBUG_LEVEL_error" value="error"
                                                    disabled {if 'error'==$LD_DEBUG_LEVEL}selected{/if}> Error</option>
                                                <option id="ld_debug_level_warning" name="LD_DEBUG_LEVEL_warning"
                                                    value="warning" disabled {if 'warning'==$LD_DEBUG_LEVEL}selected{/if}>
                                                    Warning</option>
                                                <option id="ld_debug_level_info" name="LD_DEBUG_LEVEL_info" value="info"
                                                    {if 'info'==$LD_DEBUG_LEVEL}selected{/if}>Info</option>
                                                <option id="ld_debug_level_debug" name="LD_DEBUG_LEVEL_debug" value="debug"
                                                    {if 'debug'== $LD_DEBUG_LEVEL}selected{/if}>Debug</option>
                                            </select>
                                        </div>
                                    </div>
                                    <span class="icon-help-circled tiptip" style="cursor:help" title="
                                        <b>FATAL</b>: The service/app is going to stop or becomes unusable. 
                                        <br><b>ERROR</b>: Fatal for a particular request, but the service/app continues servicing.
                                        <br><b>WARN</b>: A note on something that should probably be looked at
                                        <br><b>INFO</b>: Detail on regular operation.
                                        <br><b>DEBUG</b>: Anything else, i.e. too verbose to be included in INFO level.
                                        </div>
                                    ">More info..</span> 
                                    {if isset($WARN_LD_DEBUG_LEVEL)}<i style="color:red;">{$WARN_LD_DEBUG_LEVEL}</i>{/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset class="form-group">
                <div class="row">
                    <div class="col-12">
                        <legend>{'Auth settings'|@translate}</legend>
  
                        <div class="form-check">
                            <input class="btn-check form-check-input" type="radio" name="LD_AUTH_TYPE" id="ld_auth_azure"
                                value="ld_auth_azure" onChange="toggleAuthFields(this)" {if 'ld_auth_azure'==$LD_AUTH_TYPE}checked{/if}>
                            <label class="btn btn-lg" for="ld_auth_azure">
                                Azure
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="btn-check form-check-input" type="radio" name="LD_AUTH_TYPE" id="ld_auth_ldap" value="ld_auth_ldap"
                                onChange="toggleAuthFields(this)" {if 'ld_auth_ldap'==$LD_AUTH_TYPE}checked{/if}>
                            <label class="btn btn-lg" for="ld_auth_ldap">
                                LDAP(S)
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset class="form-group">
                <div class="card">
                    <div class="card-body">                 
                    <div class="row {if 'ld_auth_azure'!=$LD_AUTH_TYPE}visually-hidden{/if}" id="AzureSettingsBlock">
                        <h2 class="card-title">{'Azure settings'|@translate}</h2>
                            <div class="col">

                            
                            
                            
            
                                    
                            <div class="form-inline mb-3">
                                <div class="form-group row">
                                    <label for="ld_azure_tenant" class="col-sm-2 col-form-label" >{'Tenant ID'|@translate}</label>
                                    <div class="col-sm-10">
                                    <input type="text" class="form-control" id="ld_azure_tenant_id" name="LD_AZURE_TENANT_ID" value="{$LD_AZURE_TENANT_ID}"
                                    placeholder="fake-8cedf1be-5920-478e-85ff-b6909f288d10" aria-label="{'Azure Tenant'|@translate}"
                                    >
                                            <small id="ld_azure_tenant_help" class="text-muted">
                                                {'Azure Tenant ID. Use {TENANT_ID} as placeholder for URLs below'|@translate}
                                            </small>
                                    </div>
                                </div>
                            </div>   
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_clientid" class="col-sm-2 col-form-label" >{'Client ID'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_azure_client_id" name="LD_AZURE_CLIENT_ID" value="{$LD_AZURE_CLIENT_ID}"
                                        placeholder="fake-11b1f4a2-a86b-44c5-a773-ead7dceed5e2" aria-label="{'Azure Client ID'|@translate}" 
                                        >
                                                <small id="ld_azure_clientid_help" class="text-muted">
                                                    {'Azure Application Client ID.'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>     
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_clientsecret" class="col-sm-2 col-form-label" >{'Client Secret'|@translate}</label>
                                        <div class="col-sm-10">
                                        <input type="password" class="form-control" id="ld_azure_client_secret" name="LD_AZURE_CLIENT_SECRET" value="{$LD_AZURE_CLIENT_SECRET}"
                                        placeholder="" aria-label="{'Azure Client Secret'|@translate}">
                                                <small id="ld_azure_clientsecret_help" class="text-muted">
                                                    {'Azure Application Client Secret.'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_auth_url" class="col-sm-2 col-form-label" >{'Authorization URL'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_azure_auth_url" name="LD_AZURE_AUTH_URL" value="{$LD_AZURE_AUTH_URL}"
                                        placeholder="https://example.com/oauth/authorize" aria-label="{'Authorization URL'|@translate}" 
                                        >
                                                <small id="ld_azure_auth_url_help" class="text-muted">
                                                    {'Authorization URL'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>   
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_token_url" class="col-sm-2 col-form-label" >{'Access token URL'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_azure_token_url" name="LD_AZURE_TOKEN_URL" value="{$LD_AZURE_TOKEN_URL}"
                                        placeholder="https://example.com/oauth/token" aria-label="{'Access token URL'|@translate}" 
                                        >
                                                <small id="ld_azure_token_url_help" class="text-muted">
                                                    {'Access token URL'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_resource_url" class="col-sm-2 col-form-label" >{'Resource URL'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_azure_token_url" name="LD_AZURE_RESOURCE_URL" value="{$LD_AZURE_RESOURCE_URL}"
                                        placeholder="https://example.com/user" aria-label="{'Resource URL'|@translate}" 
                                        >
                                                <small id="ld_azure_resource_url_help" class="text-muted">
                                                    {'Resource URL'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>                                                                                                   
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_redirecturi" class="col-sm-2 col-form-label" >{'Redirect URI'|@translate}</label>
                                        <div class="col-sm-10">
                                        <input type="text" class="form-control" id="ld_azure_redirect_uri" name="LD_AZURE_REDIRECT_URI" value="{$LD_AZURE_REDIRECT_URI}"
                                        placeholder="https://piwigo.domain.tld/callback" aria-label="{'Azure Redirect URI'|@translate}"
                                        >
                                                <small id="ld_azure_clientsecret_help" class="text-muted">
                                                    {'Azure Application Redirect URI.'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_logout_url" class="col-sm-2 col-form-label" >{'Logout URL'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_azure_logout_url" name="LD_AZURE_LOGOUT_URL" value="{$LD_AZURE_LOGOUT_URL}"
                                        placeholder="https://example.com/logout" aria-label="{'Logout URL'|@translate}" 
                                        >
                                                <small id="ld_azure_logout_url_help" class="text-muted">
                                                    {'Logout URL'|@translate}
                                                </small>
                                        </div>
                                    </div>          
                                </div>
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_jwks_url" class="col-sm-2 col-form-label">{'JWKS URL'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_azure_logout_url" name="LD_AZURE_JWKS_URL" value="{$LD_AZURE_JWKS_URL}" placeholder="https://example.com/logout" aria-label="{'JSON Web Key Sets Public URL'|@translate}">
                                            <small id="ld_azure_jwks_url_help" class="text-muted">
                                                {'JSON Web Key Sets Public URL'|@translate}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_claim_name" class="col-sm-2 col-form-label" >{'Claim name'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_azure_claim_name" name="LD_AZURE_CLAIM_NAME" value="{$LD_AZURE_CLAIM_NAME}" placeholder="roles" aria-label="{'Claim name'|@translate}">
                                                <small id="ld_azure_claim_name_help" class="text-muted">
                                                    {'Claim name'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>          
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_user_identifier" class="col-sm-2 col-form-label" >{'User identifier'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_azure_user_identifier" name="LD_AZURE_USER_IDENTIFIER" value="{$LD_AZURE_USER_IDENTIFIER}"
                                        placeholder="id" aria-label="{'User identifier'|@translate}" 
                                        >
                                                <small id="ld_azure_user_identifier_help" class="text-muted">
                                                    {'User identifier'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>                                                                                      
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_azure_scopes" class="col-sm-2 col-form-label" >{'Scopes'|@translate}</label>
                                        <div class="col-sm-10">
                                        <input type="text" class="form-control" id="ld_azure_scopes" name="LD_AZURE_SCOPES" value="{$LD_AZURE_SCOPES}"
                                        placeholder="user.read" aria-label="{'Azure Application User Scopes'|@translate}"
                                        >
                                                <small id="ld_azure_scopes_help" class="text-muted">
                                                    {'Azure Application User Scopes'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>                                                         
                            </div>
                        </div>

                        <div class="row {if 'ld_auth_ldap'!=$LD_AUTH_TYPE}visually-hidden{/if}" id="LdapSettingsBlock">
                            <div class="col-xl-6">
                            
                                <h2 class="card-title">{'Connection'|@translate}</h2>
                                <hr>
                                <div class="input-group mb-3">
                                    <label class="input-group-text" for="ld_server_type">{'LDAP Server Type'|@translate}</label>
                                    <select class="form-select" aria-label="{'LDAP Server Type'|@translate}" id="ld_server_type"
                                        name="LD_SERVER_TYPE" onChange="disableADFields(this)">
                                        <option id="ld_server_rfc2307" name="LD_SERVER_RFC2307" value="RFC2307">OpenLDAP (RFC2307)</option>
                                        <option id="ld_server_rfc2307bis" name="LD_SERVER_RFC2307BIS" value="RFC2307BIS">Active Directory
                                            (RFC2307bis)</option>
                                        <option id="ld_server_other" name="LD_SERVER_OTHER" value="OTHER">Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_forgot_url" class="col-sm-2 col-form-label">{'Host'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" id="ld_host" name="LD_HOST" class="form-control"
                                                placeholder="ldap.domain.tld"
                                                aria-describedby="ld_host_help" aria-label="{'IP, FQDN or hostname of the directory server.'|@translate}" size="70" type="text"
                                                value="{$LD_HOST}" />
                                                <small id="ld_host_help" class="text-muted">
                                                    {'IP or hostname of the directory server.'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                    <label class="col-sm-2 col-form-label"
                                        for="ld_port">{'Ldap port'|@translate}</label>
                                        <div class="col-sm-10">
                                        <div class="input-group">
                                            <input type="number" id="ld_port" name="LD_PORT" class="form-control"
                                                placeholder="{'389 or 636'|@translate}" aria-describedby="ld_port_help"
                                                aria-label="{'389 or 636'|@translate}" type="text" value="{$LD_PORT}" />
                                            <div class="input-group-text">
                                                <input type='hidden' value='0' name='LD_USE_SSL'>                                                          
                                                <input class="form-check-input mt-0" type="checkbox"
                                                    aria-label="Checkbox for following text input" id="ld_use_ssl"
                                                    name="LD_USE_SSL" type="checkbox" value="1" {if $LD_USE_SSL== 1}checked{/if}>
                                            </div>
                                            <span class="input-group-text"
                                                id="ld_use_ssl-addon1">{'Secure connection'|@translate}</span>
                                                </div>
                                                <small id="ld_port_help" class="text-muted">
                                                    {'389 or 636'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>
                
                                
                                <div class="form-inline">
                                    <div class="form-group row">
                                        <label for="ld_basedn" class="col-sm-2 col-form-label">{'Base DN:'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" id="ld_basedn" name="LD_BASEDN" class="form-control"
                                                placeholder="dc=domain,dc=tld"
                                                aria-describedby="ld_basedn_help" aria-label=" {'The highest accessible OU or Base DN'|@translate}" size="70" type="text"
                                                value="{$LD_BASEDN}" />
                                                <small id="ld_basedn_help" class="text-muted">
                                                {'The highest accessible OU or Base DN'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>
                
                            </div>
                            <div class="col-xl-6">
                                
                                <h2 class="card-title">{'Credentials'|@translate}</h2>
                                <hr>
                                <div class="form-inline mb-3">
                                    <div class="form-group row">
                                        <label for="ld_binddn" class="col-sm-2 col-form-label">{'Bind DN'|@translate}</label>
                                        <div class="col-sm-10">
                                            <input type="text" id="ld_binddn" name="LD_BINDDN" class="form-control"
                                                placeholder="cn=admin,dc=domain,dc=tld"
                                                aria-describedby="ld_binddn_help" aria-label="cn=admin,dc=domain,dc=tld"
                                                value="{$LD_BINDDN}" />
                                                <small id="ld_binddn_help" class="text-muted">
                                                    {'User (Service account) described by DN performing the bind'|@translate}
                                                </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-inline mb-3">
                                <div class="form-group row">
                                    <label for="ld_bindpw" class="col-sm-2 col-form-label">{'Bind password'|@translate}</label>
                                    <div class="col-sm-10">
                                        <input type="password" id="ld_bindpw" name="LD_BINDPW" class="form-control"
                                            aria-describedby="ld_host_help" aria-label="{'Password of bind account'|@translate}" size="70" type="text"
                                            value="{$LD_BINDPW}" />
                                            <small id="ld_bindpw_help" class="text-muted">
                                                {'Keep BOTH fields blank if the ldap accept anonymous connections. '|@translate}
                                            </small>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <br>
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a id="nav-link-tabUserSchema" class="nav-link {if 'ld_auth_azure'!=$LD_AUTH_TYPE}active{/if} {if 'ld_auth_azure'==$LD_AUTH_TYPE}disabled{/if}" data-bs-toggle="tab" href="#tabUserSchema" role="tab" aria-expanded="false"
                                    aria-controls="tabUserSchema">{'User Schema'|@translate}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a id="nav-link-tabGroupSchema" class="nav-link {if 'ld_auth_azure'==$LD_AUTH_TYPE}disabled{/if}" data-bs-toggle="tab" href="#tabGroupSchema" role="tab" aria-expanded="false"
                                    aria-controls="tabGroupSchema">{'Group Schema'|@translate}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a id="nav-link-tabMembershipSchema" class="nav-link {if 'ld_auth_azure'==$LD_AUTH_TYPE}disabled{/if}" data-bs-toggle="tab" href="#tabMembershipSchema" role="tab" aria-expanded="false"
                                    aria-controls="tabMembershipSchema">{'Membership Schema'|@translate}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a id="nav-link-tabMembershipSettings" class="nav-link {if 'ld_auth_azure'==$LD_AUTH_TYPE}active{/if}" data-bs-toggle="tab" href="#tabMembershipSettings" role="tab" aria-expanded="false"
                                    aria-controls="tabMembershipSettings">{'Membership Settings'|@translate}</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#close" role="tab" aria-expanded="false"
                                    aria-controls="close"><button type="button" class="btn-close" aria-label="Close"></button></a>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade {if 'ld_auth_azure'!=$LD_AUTH_TYPE}show active{/if}" id="tabUserSchema">
                                <div class="card card-body"> 
                                <h2 class="card-title">{'User Schema Settings'|@translate}</h2>
                                    <br>
                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="exampleDiv" class="col-sm-2 col-form-label">{'User Filter Generator:'|@translate}</label>
                                            <div class="col-sm-10">
                                                <input readonly type="text" class="form-control" id="exampleDiv" >
                                                    <small id="ld_user_class_help" class="text-muted">
                                                    <i>{'(&(&(objectClass=<b>User_Object_Class</b>)(<b>Username_Attribute</b>=Login_Form_username))(<b>User_Object_Filter</b>))'|@translate}</i>
                                                    </small>
                                            </div>
                                        </div>
                                    </div>  
                                                                        
                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_user_class" class="col-sm-2 col-form-label">{'User Object Class:'|@translate}</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" onchange="updateExampleFilter()" id="ld_user_class"
                                                name="LD_USER_CLASS" value="{$LD_USER_CLASS}" placeholder=""
                                                aria-label="{'User Object Class:'|@translate}">
                                                    <small id="ld_user_class_help" class="text-muted">
                                                        {'The LDAP user object class type to use when loading users'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>                                      
                                    

                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_user_attr" class="col-sm-2 col-form-label">{'Username Attribute'|@translate}</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" onchange="updateExampleFilter()" id="ld_user_attr"
                                                name="LD_USER_ATTR" value="{$LD_USER_ATTR}" placeholder=""
                                                aria-label="{'Username Attribute'|@translate}">
                                                    <small id="ld_user_attr_help" class="text-muted">
                                                        {'The attribute field to use on the user object. Examples: cn, sAMAccountName.'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>                                       
                                    

                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_user_filter" class="col-sm-2 col-form-label">{'User Object Filter:'|@translate}</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" onchange="updateExampleFilter()" id="ld_user_filter"
                                                name="LD_USER_FILTER" value="{$LD_USER_FILTER}" placeholder=""
                                                aria-label="{'User Object Filter:'|@translate}">
                                                    <small id="ld_user_filter_help" class="text-muted">
                                                    {'The filter to use when searching user objects'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>                                     
                                    
                                
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tabGroupSchema">
                                <div class="card card-body">
                                <h2 class="card-title">{'Group Schema Settings'|@translate}</h2>
                                    
                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_group_class" class="col-sm-2 col-form-label">{'Group Object Class:'|@translate}</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="ld_group_class" name="LD_GROUP_CLASS"
                                                value="{$LD_GROUP_CLASS}" placeholder=""
                                                aria-label="{'Group Object Class:'|@translate}">
                                                    <small id="ld_group_class_help" class="text-muted">
                                                        {'LDAP attribute objectClass value to search for when loading groups.'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>  
                                    

                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_group_filter" class="col-sm-2 col-form-label">{'Group Object Filter:'|@translate}</label>
                                            <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_group_filter" name="LD_GROUP_FILTER"
                                            value="{$LD_GROUP_FILTER}" placeholder=""
                                            aria-label="{'Group Object Filter:'|@translate}">
                                                    <small id="ld_group_filter_help" class="text-muted">
                                                    {'The filter to use when searching group objects.'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>                                     
                                    

                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_group_attr" class="col-sm-2 col-form-label">{'Group Name Attribute:'|@translate}</label>
                                            <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_group_attr" name="LD_GROUP_ATTR"
                                            value="{$LD_GROUP_ATTR}" placeholder=""
                                            aria-label="{'Group Name Attribute:'|@translate}">
                                                    <small id="ld_group_attr_help" class="text-muted">
                                                    {'The attribute field to use when loading the group name.'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>                                        
                                    

                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_group_desc" class="col-sm-2 col-form-label">{'Group Description:'|@translate}</label>
                                            <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_group_desc" name="LD_GROUP_DESC"
                                            value="{$LD_GROUP_DESC}" placeholder="" aria-label="{'Group Description:'|@translate}">
                                                    <small id="ld_group_desc_help" class="text-muted">
                                                    {'The attribute field to use when loading the group description.'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>   

                                </div>
                            </div>
                            <div class="tab-pane fade " id="tabMembershipSchema">
                                <div class="card card-body">
                                <h2 class="card-title">{'Membership Schema Settings'|@translate}</h2>

                                    
                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_group_member_attr" class="col-sm-2 col-form-label">{'Group Membership Attribute:'|@translate}</label>
                                            <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_group_member_attr"
                                            name="LD_GROUP_MEMBER_ATTR" value="{$LD_GROUP_MEMBER_ATTR}" placeholder=""
                                            aria-label="{'Group Membership Attribute:'|@translate}">
                                                    <small id="ld_group_member_attr_help" class="text-muted">
                                                    {'The attribute field to use when loading the group members from the group.'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>                                   
                                    
                                    <div class="form-inline mb-3">
                                        <div class="form-group row">
                                            <label for="ld_user_member_attr" class="col-sm-2 col-form-label">{'User Membership Attribute:'|@translate}</label>
                                            <div class="col-sm-10">
                                            <input type="text" class="form-control" id="ld_user_member_attr" name="LD_USER_MEMBER_ATTR"
                                            value="{$LD_USER_MEMBER_ATTR}" placeholder=""
                                            aria-label="{'User Membership Attribute:'|@translate}">
                                                    <small id="ld_user_member_attr_help" class="text-muted">
                                                    {'The attribute field when loading groups from a user.'|@translate}
                                                    </small>
                                            </div>
                                        </div>
                                    </div>   
                                                                    
                                    <div class="btn-group">
                                        <input type="radio" class="btn-check" name="LD_MEMBERSHIP_USER" id="ld_membership_user1"
                                            autocomplete="off" {if $LD_MEMBERSHIP_USER== 1}checked{/if} />
                                        <label class="btn btn-outline-secondary"
                                            for="ld_membership_user1">{'Use USER membership attribute'|@translate}</label>

                                        <input type="radio" class="btn-check" name="LD_MEMBERSHIP_USER" id="ld_membership_user2"
                                            autocomplete="off" {if $LD_MEMBERSHIP_USER== 0}checked{/if} />
                                        <label class="btn btn-outline-secondary"
                                            for="ld_membership_user2">{'Use GROUP membership attribute'|@translate}</label>
                                    </div>

                                    </div>
                                </div>
                                <div class="tab-pane fade  {if 'ld_auth_azure'==$LD_AUTH_TYPE}show active{/if}" id="tabMembershipSettings">
                                    <div class="card card-body">
                                    <h2 class="card-title">{'Membership Settings'|@translate}</h2>
                                        <a href="https://piwigo.org/doc/doku.php?id=user_documentation:use:features:user_status"
                                            target="_blank	"
                                            style="position: relative;display: inline-block;border-bottom: 1px dotted black;">More info
                                            about built-in groups on Piwigo.org</a>
                                        <br>
                                        <br>

                                        <span id="ld_user_class_help"
                                            class="form-text text-muted">{'The group that will get user rights (DN for LDAP).'|@translate}</span>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text">
                                                <input type='hidden' value='0' name='LD_GROUP_USER_ACTIVE'>
                                                <input class="form-check-input mt-0" type="checkbox"
                                                    aria-label="Checkbox for following text input" id="ld_group_user_active"
                                                    name="LD_GROUP_USER_ACTIVE" type="checkbox" value="1" {if $LD_GROUP_USER_ACTIVE== 1}checked{/if}>
                                            </div>
                                            <div class="input-group-prepend col-xl-4">
                                                <span class="input-group-text"
                                                    id="ld_group_user-addon1">{'Group corresponding with users:'|@translate}</span>
                                            </div>
                                            <input type="text" class="form-control" id="ld_group_user" name="LD_GROUP_USER"
                                                value="{$LD_GROUP_USER}" placeholder=""
                                                aria-label="{'Group corresponding with users:'|@translate}">
                                        </div>

                                        <span id="ld_user_class_help"
                                            class="form-text text-muted">{'The group that will get admininistrator rights (DN for LDAP).'|@translate}</span>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text">
                                                <input type='hidden' value='0' name='LD_GROUP_ADMIN_ACTIVE'>
                                                <input class="form-check-input mt-0" type="checkbox"
                                                    aria-label="Checkbox for following text input" id="ld_group_admin_active"
                                                    name="LD_GROUP_ADMIN_ACTIVE" type="checkbox" value="1"
                                                    {if $LD_GROUP_ADMIN_ACTIVE== 1}checked{/if}>
                                            </div>
                                            <div class="input-group-prepend col-xl-4">
                                                <span class="input-group-text"
                                                    id="ld_group_admin-addon1">{'Group corresponding with administrators:'|@translate}</span>
                                            </div>
                                            <input type="text" class="form-control" id="ld_group_admin" name="LD_GROUP_ADMIN"
                                                value="{$LD_GROUP_ADMIN}" placeholder=""
                                                aria-label="{'Group corresponding with administrators:'|@translate}">
                                        </div>

                                        <span id="ld_user_class_help"
                                            class="form-text text-muted">{'The group that will get webmaster rights (DN for LDAP).'|@translate}</span>
                                        <div class="input-group mb-3">
                                            <div class="input-group-text">
                                                <input type='hidden' value='0' name='LD_GROUP_WEBMASTER_ACTIVE'>
                                                <input class="form-check-input mt-0" type="checkbox"
                                                    aria-label="Checkbox for following text input" id="ld_group_webmaster_active"
                                                    name="LD_GROUP_WEBMASTER_ACTIVE" type="checkbox"
                                                    value="1"  {if $LD_GROUP_WEBMASTER_ACTIVE== 1}checked{/if}>
                                            </div>
                                            <div class="input-group-prepend  col-xl-4">
                                                <span class="input-group-text"
                                                    id="ld_group_webmaster-addon1">{'Group corresponding with webmasters:'|@translate}</span>
                                            </div>
                                            <input type="text" class="form-control" id="ld_group_webmaster" name="LD_GROUP_WEBMASTER"
                                                value="{$LD_GROUP_WEBMASTER}" placeholder=""
                                                aria-label="{'Group corresponding with webmasters:'|@translate}">
                                        </div>

                                    </div>
                                </div>
                                <div class="tab-pane fade " id="close"></div>
                            </div>
                        </div>
                            </div>
                                                <br>
                                                <div class="row">
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary btn-lg btn-block" value="{'Save'|@translate}"
                                                        name="save">{'Save'|@translate}</button>
                                                </div>
                                            </div>                                                
                                        </form>
        </fieldset>
        </div>
    </form>
</div>
<div id="tiptip_holder" style="max-width: 300px; margin: 896px 0px 0px 555px; display: none;" class="tip_bottom">
    <div id="tiptip_arrow" style="margin-left: 144px; margin-top: -12px;">
        <div id="tiptip_arrow_inner"></div>
    </div>
    <div id="tiptip_content">

    </div>
</div>