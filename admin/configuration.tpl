{combine_css path=$LDAP_LOGIN_PATH|cat:"style.css"} 
{html_head }
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous"> 
{/html_head} 
{*<!-- add inline JS -->*} 

{combine_script id="popper" require="jquery" path="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"} 
{combine_script id="bootstrap" require="jquery" path="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"}


{* <!-- add inline JS --> *}
{footer_script require="jquery"}

    function updateExampleFilter() {
        
        var userclass = document.getElementById("ld_user_class").value;
        var attr = document.getElementById("ld_user_attr").value;
        var filter = document.getElementById("ld_user_filter").value;
        var username = document.getElementById("username").value;
        var string = '&(&(objectClass=' + userclass + ')(' + attr + '=' + username + '))(' + filter +')'
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
            var enabledfields = {
			'ld_azure_clientid': '',
			'ld_azure_tenant': '',
			'ld_azure_clientsecret': '',
			'ld_azure_redirecturi': ''
			}
            $( "#LdapSettingsBlock" ).addClass( "visually-hidden " );
            $( "#AzureSettingsBlock" ).removeClass( "visually-hidden" );
			break;
		  case 'ld_auth_ldap':
			var disabledfields = {
			'ld_azure_clientid': '',
			'ld_azure_tenant': '',
			'ld_azure_clientsecret': '',
			'ld_azure_redirecturi': ''
			}
            $( "#AzureSettingsBlock" ).addClass( "visually-hidden " );
            $( "#LdapSettingsBlock" ).removeClass( "visually-hidden" );
			break;
		}		
		for (var key in disabledfields) {      
			var input = document.getElementById(key);
			input.value = disabledfields[key]
			input.disabled = true
		}
        for (var key in enabledfields) {      
			var input = document.getElementById(key);
			input.value = enabledfields[key]
			input.disabled = false
		}

	}

    $( document ).ready(function() {
        updateExampleFilter();
        console.log("ready")
    });  
{/footer_script}


<h2>{'Ldap_Login Plugin'|@translate}</h2>


  
  
<div class="container">
	<form method="post" action="{$PLUGIN_ACTION}" class="general">
	<div class="row">
		<div class="col-12">
            <p>{'Use Azure or LDAP for user authentication'|@translate}</p> {if isset($WARN_GENERAL)}<i style="color:red;">{$WARN_GENERAL|@translate}</i>{/if}
            <br>
             {if (!extension_loaded('ldap'))}
            <p style="color:red;">{'Warning: LDAP Extension missing.'|@translate}</p>
            <br /> {/if}
            <fieldset class="form-group">
                <legend>{'General settings'|@translate}</legend>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="ld_forgot_url_label">{'Alternative URL for password reset'|@translate}</span>
                    <input type="text" id="ld_forgot_url" name="LD_FORGOT_URL" class="form-control" value="{$LD_FORGOT_URL}" placeholder="https://piwigo.company.tld/password.php" aria-describedby="ld_forgot_url_label">
                </div>
                <span id="ld_debug_location_help" class="form-text text-muted">{'Company directory password reset URL (https://mycompany.com/passreset.php) Default: Piwigo "password.php"'|@translate}</span>                  
                <div class="input-group mb-3">
                    <span class="input-group-text" id="ld_debug_location_label">{'Log location'|@translate}</span>
                    <input type="text" id="ld_debug_location" name="LD_DEBUG_LOCATION" class="form-control" value="{$LD_DEBUG_LOCATION}" placeholder="/var/log/" aria-describedby="ld_debug_location_label">
                </div>
                <span id="ld_debug_location_help" class="form-text text-muted">{'Log location help: Field to define the location of debug.log. Protect the location with .htaccess or store in /var/log/ (most secure)'|@translate}</span>                  
                    
                
                <!-- <p> -->
                    <!-- <label style="display:inline-block; width:15%;" for="ld_forgot_url"><span>{'Url for password reset'|@translate}</span></label> -->
                    <!-- <input size="70" type="text" id="ld_forgot_url" name="LD_FORGOT_URL" value="{$LD_FORGOT_URL}" /> {if isset($WARN_LD_FORGOT_URL)}<i style="color:red;">{$WARN_LD_FORGOT_URL}</i>{/if} -->
                    <!-- <br> <i style="margin:15%;">Company directory password reset URL (https://mycompany.com/passreset.php) Default: Piwigo "password.php"</i> -->
                    <!-- <br> </p> -->
                <!-- <p> -->
                    <!-- <label style="display:inline-block; width:15%;" style="display:inline-block; width:120px; text-align:right;" for="ld_debug_location"><span>{'Log location'|@translate}</span></label> -->
                    <!-- <input size="70" type="text" id="ld_debug_location" name="LD_DEBUG_LOCATION" value="{$LD_DEBUG_LOCATION}" /> {if isset($WARN_LD_DEBUG_LOCATION)}<i style="color:red;">{$WARN_LD_DEBUG_LOCATION}</i>{/if} -->
                    <!-- <br> <i style="margin:15%;">Field to define the location of debug.log. Protect the location with .htaccess or store in /var/log/ (most secure)</i> -->
                    <!-- <br> </p> -->
                    
                <div class="form-check form-switch">
                  <input class="form-check-input" id="ld_debug" name="LD_DEBUG" type="checkbox" value="{$LD_DEBUG}" id="flexCheckDefault" checked>
                  <label class="form-check-label" for="flexCheckDefault">
                    {'Enable logs'|@translate}
                  </label>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" id="ld_debug_clearupdate" name="LD_DEBUG_CLEARUPDATE" type="checkbox" value="{$LD_DEBUG_CLEARUPDATE}"  id="flexCheckChecked" checked>
                  <label class="form-check-label" for="flexCheckChecked">
                    {'Clear logs after plugin update'|@translate}
                  </label>
                </div>						
                <!-- <p> -->
                
                    <!-- <label style="display:inline-block; width:15%;" for="ld_debug"> {if isset($LD_DEBUG) } -->
                        <!-- <input type="checkbox" id="ld_debug" name="LD_DEBUG" value="{$LD_DEBUG}" checked /> {else} -->
                        <!-- <input type="checkbox" id="ld_debug" name="LD_DEBUG" value="{$LD_DEBUG}" /> {/if}{'Enable logs'|@translate}</label>{if isset($WARN_LD_DEBUG)}<i style="color:red;">{$WARN_LD_DEBUG}</i>{/if} </p> -->
                <!-- <p> -->
                    <!-- <label style="display:inline-block; width:15%;" for="ld_debug_clearupdate"> {if isset($LD_DEBUG_CLEARUPDATE) } -->
                        <!-- <input type="checkbox" id="ld_debug_clearupdate" name="LD_DEBUG_CLEARUPDATE" value="{$LD_DEBUG_CLEARUPDATE}" checked /> {else} -->
                        <!-- <input type="checkbox" id="ld_debug_clearupdate" name="LD_DEBUG_CLEARUPDATE" value="{$LD_DEBUG_CLEARUPDATE}" /> {/if}</label> {if isset($WARN_LD_DEBUG_CLEARUPDATE)}<i style="color:red;">{$WARN_LD_DEBUG_CLEARUPDATE}</i>{/if} </p> -->
                <div class="input-group mb-3">
                    <label class="input-group-text" for="ld_debug_level">{'Debug level'|@translate}</label>
                    <select class="form-select" aria-label="{'Debug level'|@translate}" id="ld_debug_level" name="LD_DEBUG_LEVEL">
                            <option id="ld_debug_level_fatal" name="LD_DEBUG_LEVEL_fatal" value="fatal" disabled {if 'fatal'==$LD_DEBUG_LEVEL}selected{/if}>Fatal</option>
                            <option id="ld_debug_level_error" name="LD_DEBUG_LEVEL_error" value="error" disabled {if 'error'==$LD_DEBUG_LEVEL}selected{/if}> Error</option>
                            <option id="ld_debug_level_warning" name="LD_DEBUG_LEVEL_warning" value="warning" disabled {if 'warning'==$LD_DEBUG_LEVEL}selected{/if}>Warning</option>
                            <option id="ld_debug_level_info" name="LD_DEBUG_LEVEL_info" value="info" disabled {if 'info'==$LD_DEBUG_LEVEL}selected{/if}>Info</option>
                            <option id="ld_debug_level_debug" name="LD_DEBUG_LEVEL_debug" value="debug" {if 'debug'== $LD_DEBUG_LEVEL}selected{/if}>Debug</option>
                    </select>
		</select> 
                    </select>
                </div>
                
                <!-- <div style="margin:1em;"> -->
                    <!-- <label style="display:inline-block; width:15%;" style="display:inline-block; width:120px; text-align:right;" for="ld_debug_level"><span>{'Debug level'|@translate}</span></label> -->
                    <!-- <select id="ld_debug_level" name="LD_DEBUG_LEVEL"> -->
                        <!-- <option id="ld_debug_level_fatal" name="LD_DEBUG_LEVEL_fatal" value="fatal" disabled {if 'fatal'==$LD_DEBUG_LEVEL}selected{/if}>Fatal</option> -->
                        <!-- <option id="ld_debug_level_error" name="LD_DEBUG_LEVEL_error" value="error" disabled {if 'error'==$LD_DEBUG_LEVEL}selected{/if}> Error</option> -->
                        <!-- <option id="ld_debug_level_warning" name="LD_DEBUG_LEVEL_warning" value="warning" disabled {if 'warning'==$LD_DEBUG_LEVEL}selected{/if}>Warning</option> -->
                        <!-- <option id="ld_debug_level_info" name="LD_DEBUG_LEVEL_info" value="info" disabled {if 'info'==$LD_DEBUG_LEVEL}selected{/if}>Info</option> -->
                        <!-- <option id="ld_debug_level_debug" name="LD_DEBUG_LEVEL_debug" value="debug" {if 'debug'== $LD_DEBUG_LEVEL}selected{/if}>Debug</option> -->
                    <!-- </select> -->
                <div class="tooltipcss" > More info..
                        <div class="tooltiptext">
                            <div style="margin:1em; "> FATAL: The service/app is going to stop or becomes unusable. An operator should definitely look into this soon.
                                <br> ERROR: Fatal for a particular request, but the service/app continues servicing. An operator should look at this soon(ish)
                                <br> WARN: A note on something that should probably be looked at by an operator eventually.
                                <br> INFO: Detail on regular operation.
                                <br> DEBUG: Anything else, i.e. too verbose to be included in INFO level. </div>
                        </div>
                </div> {if isset($WARN_LD_DEBUG_LEVEL)}<i style="color:red;">{$WARN_LD_DEBUG_LEVEL}</i>{/if}
            </fieldset>
		</div>
	</div>
	</div>	
	</fieldset>
    <div class="row"  id="LdapSettingsBlock">
		<div class="col-6">
			<fieldset class="form-group">
				<legend>{'Ldap server host connection'|@translate}</legend>
                <div class="input-group mb-3">
                    <label class="input-group-text" for="ld_server_type">{'LDAP Server Type'|@translate}</label>
                    <select class="form-select" aria-label="{'LDAP Server Type'|@translate}" id="ld_server_type" name="LD_SERVER_TYPE" onChange="disableADFields(this)">
                        <option id="ld_server_rfc2307" name="LD_SERVER_RFC2307" value="RFC2307">OpenLDAP (RFC2307)</option>
                        <option id="ld_server_rfc2307bis" name="LD_SERVER_RFC2307BIS" value="RFC2307BIS">Active Directory (RFC2307bis)</option>
                        <option id="ld_server_other" name="LD_SERVER_OTHER" value="OTHER">Other</option>
                    </select>
                </div>
                
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="ld_host-addon1">{'Server FQDN/IP'|@translate}</span>
                    </div>
                <input class="form-control"  placeholder="ldap.domain.tld" aria-label="{'IP or hostname of the directory server.'|@translate}"  size="70"  type="text" id="ld_host" name="LD_HOST" value="{$LD_HOST}" />
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="ld_port-addon1">{'Ldap port'|@translate}</span>
                    </div>
                    <input class="form-control"  type="text" id="ld_port" name="LD_PORT" value="{$LD_PORT}" placeholder="{'389 or 636'|@translate}" aria-label="{'389 or 636'|@translate}"  />
                      <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" aria-label="Checkbox for following text input"  id="ld_use_ssl" name="LD_USE_SSL" type="checkbox" value="{$LD_USE_SSL}"  checked>
                    </div>
                    <span class="input-group-text" id="ld_use_ssl-addon1">{'Secure connection'|@translate}</span>
                </div>                
                <!-- <label for="ld_host">{'Server location'|@translate}</label> -->
                <!-- <input size="70" type="text" id="ld_host" name="LD_HOST" value="{$LD_HOST}" /> {if isset($WARN_LD_HOST)}<i style="color:red;">{$WARN_LD_HOST}</i>{/if} -->
                <!-- <br> <i style="margin:15%;">{'IP or hostname of the directory server.'|@translate}</i> -->
                <!-- <br> -->
                <!-- <label for="ld_port">{'Ldap port'|@translate}</label> -->
                <!-- <input type="text" id="ld_port" name="LD_PORT" value="{$LD_PORT}" /> {if isset($WARN_LD_PORT)}<i style="color:red;">{$WARN_LD_PORT}</i>{/if} -->
                <!-- <br> <i style="margin:15%;">{'If empty, localhost and standard protocol ports (389/636) will be used in configuration.'|@translate}</i> -->
                <!-- <br> </p> -->
                <!-- <div class="form-check form-switch"> -->
                  <!-- <input class="form-check-input" id="ld_use_ssl" name="LD_USE_SSL" type="checkbox" value="{$LD_USE_SSL}"  id="ld_use_ssl" checked> -->
                  <!-- <label class="form-check-label" for="ld_use_ssl"> -->
                    <!-- {'Secure connection'|@translate} -->
                  <!-- </label> -->
                <!-- </div>	 -->
                <!-- <label for="ld_use_ssl"> {if isset($LD_USE_SSL) } -->
                    <!-- <input type="checkbox" id="ld_use_ssl" name="LD_USE_SSL" value="{$LD_USE_SSL}" checked /> {else} -->
                    <!-- <input type="checkbox" id="ld_use_ssl" name="LD_USE_SSL" value="{$LD_USE_SSL}" /> {/if} {'Secure connexion'|@translate}</label> {if isset($WARN_LD_USE_SSL)}<i style="color:red;">{$WARN_LD_USE_SSL}</i>{/if} -->
                <!-- <br> -->
                <!-- <br> -->
                <!-- <label for="ld_basedn">{'Base DN:'|@translate}</label> -->
                <!-- <input size="70" type="text" id="ld_basedn" name="LD_BASEDN" value="{$LD_BASEDN}" /> {if isset($WARN_LD_BASEDN)}<i style="color:red;">{$WARN_LD_BASEDN}</i>{/if} -->
                <!-- <br> <i style="margin:15%;">{'The highest accessible OU or Base DN'|@translate}</i> -->
                <!-- <br> -->
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="ld_basedn-addon1">{'Base DN:'|@translate}</span>
                    </div>
                    <input class="form-control"  placeholder="dc=domain,dc=tld" aria-label="dc=domain,dc=tld"  size="70"  type="text" id="ld_basedn" name="LD_BASEDN" value="{$LD_BASEDN}" />
                </div>
                <span id="ld_basedn_help" class="form-text text-muted">{'The highest accessible OU or Base DN'|@translate}</span>                  
                <br>
			</fieldset>
		</div>
		<div class="col-6">
			<fieldset class="form-group">
				<legend>{'LDAP Credentials'|@translate}</legend>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="ld_binddn-addon1">{'Bind (Service account) DN'|@translate}</span>
                    </div>
                 <input class="form-control"  type="text" id="ld_binddn" name="LD_BINDDN" value="{$LD_BINDDN}" placeholder="cn=admin,dc=domain,dc=tld" aria-label="cn=admin,dc=domain,dc=tld"  />
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="ld_bindpw-addon1">{'Bind (Service account) password'|@translate}</span>
                    </div>
                 <input class="form-control"  type="password" id="ld_bindpw" name="LD_BINDPW" value="{$LD_BINDPW}" />
                </div>    
                <span id="ld_binddn_help" class="form-text text-muted">{'Keep BOTH fields blank if the ldap accept anonymous connections. '|@translate}</span>
				<!-- <ul> -->
					<!-- <li> -->
						<!-- <label for="ld_binddn">{'Bind (Service account) DN'|@translate}</label> -->
						<!-- <br> -->
						<!-- <input size="100" type="text" id="ld_binddn" name="LD_BINDDN" value="{$LD_BINDDN}" /> {if isset($WARN_LD_BINDDN)}<i style="color:red;">{$WARN_LD_BINDDN}</i>{/if} </li> -->
					<!-- <li> -->
						<!-- <label for="ld_bindpw">{'Bind (Service account) password'|@translate}</label> -->
						<!-- <br> -->
						<!-- <input type="password" id="ld_bindpw" name="LD_BINDPW" /> {if isset($WARN_LD_BINDPW)}<i style="color:red;">{$WARN_LD_BINDPW}</i>{/if} </li> -->
				<!-- </ul> <i>{'Keep BOTH fields blank if the ldap accept anonymous connections. '|@translate}</i>  -->
            </fieldset>



			<!-- <input type="submit" value="{'Reset to AD-values'|@translate}" name="RESET_AD" /> -->
			<!-- <br> -->
			<!-- <br> -->
			<!-- <input type="submit" value="{'Reset to OpenLDAP-values'|@translate}" name="RESET_OL" /> </div> -->
        </div>
    </div>
    <div class="row">
		<div class="col-12">
            <!-- <input type="submit" value="{'Save'|@translate}" name="save" /> -->
            <!-- <input type="submit" value="{'Save & test'|@translate}" name="savetest" />  -->
            <button type="submit" class="btn btn-primary btn-lg btn-block" value="{'Save'|@translate}" name="save">{'Save'|@translate}</button>
            <!-- <button type="submit" class="btn btn-secondary btn-lg btn-block btn-warning" value="{'Save & test'|@translate}" name="savetest">{'Save & test'|@translate}</button>         -->
        </div>
    </div>
    
    <br>
	<div class="row">
		<div class="col-12">	                   
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item"  role="presentation">
                  <a class="nav-link active" data-bs-toggle="tab" href="#tabUserSchema" role="tab" aria-expanded="false" aria-controls="tabUserSchema">{'User Schema'|@translate}</a>
                </li>
                <li class="nav-item"  role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#tabGroupSchema" role="tab" aria-expanded="false" aria-controls="tabGroupSchema">{'Group Schema'|@translate}</a>
                </li>
                <li class="nav-item"  role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#tabMembershipSchema" role="tab" aria-expanded="false" aria-controls="tabMembershipSchema">{'Membership Schema'|@translate}</a>
                </li>
                <li class="nav-item"  role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#tabMembershipSettings" role="tab" aria-expanded="false" aria-controls="tabMembershipSettings">{'Membership Settings'|@translate}</a>
                </li>
                <li class="nav-item"  role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#close" role="tab" aria-expanded="false" aria-controls="close"><button type="button" class="btn-close" aria-label="Close"></button></a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="tabUserSchema">
                  <div class="card card-body">
                    <fieldset class="form-group">
                        <legend>{'User Schema Settings'|@translate}</legend> 
	<legend>{'User Schema Settings'|@translate}</legend>
                        <legend>{'User Schema Settings'|@translate}</legend> 
                        <i>Required for user filter: (&(&(objectClass=<b>User_Object_Class</b>)(<b>Username_Attribute</b>=Login_Form_username))(<b>User_Object_Filter</b>)</i>
                        <input class="form-control" id="exampleDiv" readonly>
                        <br><br>
                        <span id="ld_user_class_help" class="form-text text-muted">{'The LDAP user object class type to use when loading users'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_user_class-addon1">{'User Object Class:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control"  onchange="updateExampleFilter()" id="ld_user_class" name="LD_USER_CLASS" value="{$LD_USER_CLASS}" placeholder="" aria-label="{'User Object Class:'|@translate}" >
                        </div>
                        
                        <span id="ld_user_class_help" class="form-text text-muted">{'The attribute field to use on the user object. Examples: cn, sAMAccountName.'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_user_attr-addon1">{'Username Attribute'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control"  onchange="updateExampleFilter()" id="ld_user_attr" name="LD_USER_ATTR" value="{$LD_USER_ATTR}" placeholder="" aria-label="{'Username Attribute'|@translate}" >
                        </div>

                        <span id="ld_user_class_help" class="form-text text-muted">{'The filter to use when searching user objects'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_user_filter-addon1">{'User Object Filter:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" onchange="updateExampleFilter()" id="ld_user_filter" name="LD_USER_FILTER" value="{$LD_USER_FILTER}" placeholder="" aria-label="{'User Object Filter:'|@translate}" >
                        </div>
                        
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_user_class">{'User Object Class:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_user_class" name="LD_USER_CLASS" value="{$LD_USER_CLASS}" /> {if isset($WARN_LD_USER_CLASS)}<i style="color:red;">{$WARN_LD_USER_CLASS}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The LDAP user object class type to use when loading users'|@translate}</i> -->
                            <!-- <br> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_user_attr">{'Username Attribute'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_user_attr" name="LD_USER_ATTR" value="{$LD_USER_ATTR}" /> {if isset($WARN_LD_USER_ATTR)}<i style="color:red;">{$WARN_LD_USER_ATTR}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The attribute field to use on the user object. Examples: cn, sAMAccountName.'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_user_filter">{'User Object Filter:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_user_filter" name="LD_USER_FILTER" value="{$LD_USER_FILTER}" /> {if isset($WARN_LD_USER_FILTER)}<i style="color:red;">{$WARN_LD_USER_FILTER}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The filter to use when searching user objects'|@translate}</i> -->
                            <!-- <br> </p> -->
                    </fieldset>
                  </div>
                </div> 
                <div class="tab-pane fade" id="tabGroupSchema">
                  <div class="card card-body">
                    <fieldset class="form-group">
                        <legend>{'Group Schema Settings'|@translate}</legend>
                        <span id="ld_user_class_help" class="form-text text-muted">{'LDAP attribute objectClass value to search for when loading groups.'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_group_class-addon1">{'Group Object Class:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_group_class" name="LD_GROUP_CLASS" value="{$LD_GROUP_CLASS}" placeholder="" aria-label="{'Group Object Class:'|@translate}" >
                        </div>                        
 
                        <span id="ld_user_class_help" class="form-text text-muted">{'The filter to use when searching group objects.'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_group_filter-addon1">{'Group Object Filter:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_group_filter" name="LD_GROUP_FILTER" value="{$LD_GROUP_FILTER}" placeholder="" aria-label="{'Group Object Filter:'|@translate}" >
                        </div>   
                        
                        <span id="ld_user_class_help" class="form-text text-muted">{'The attribute field to use when loading the group name.'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_group_attr-addon1">{'Group Name Attribute:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_group_attr" name="LD_GROUP_ATTR" value="{$LD_GROUP_ATTR}" placeholder="" aria-label="{'Group Name Attribute:'|@translate}" >
                        </div>   

                        <span id="ld_user_class_help" class="form-text text-muted">{'The attribute field to use when loading the group description.'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_group_desc-addon1">{'Group Description:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_group_desc" name="LD_GROUP_DESC" value="{$LD_GROUP_DESC}" placeholder="" aria-label="{'Group Description:'|@translate}" >
                        </div>   
                        
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_class">{'Group Object Class:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_group_class" name="LD_GROUP_CLASS" value="{$LD_GROUP_CLASS}" /> {if isset($WARN_LD_GROUP_CLASS)}<i style="color:red;">{$WARN_LD_GROUP_CLASS}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'LDAP attribute objectClass value to search for when loading groups.'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_filter">{'Group Object Filter:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_group_filter" name="LD_GROUP_FILTER" value="{$LD_GROUP_FILTER}" /> {if isset($WARN_LD_GROUP_FILTER)}<i style="color:red;">{$WARN_LD_GROUP_FILTER}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The filter to use when searching group objects.'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_attr">{'Group Name Attribute:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_group_attr" name="LD_GROUP_ATTR" value="{$LD_GROUP_ATTR}" /> {if isset($WARN_LD_GROUP_ATTR)}<i style="color:red;">{$WARN_LD_GROUP_ATTR}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The attribute field to use when loading the group name.'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_desc">{'Group Description:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_group_desc" name="LD_GROUP_DESC" value="{$LD_GROUP_DESC}" /> {if isset($WARN_LD_GROUP_DESC)}<i style="color:red;">{$WARN_LD_GROUP_DESC}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The attribute field to use when loading the group description.'|@translate}</i> </p> -->
                    </fieldset>
                  </div>
                </div> 
                <div class="tab-pane fade " id="tabMembershipSchema">
                  <div class="card card-body">
                    <fieldset class="form-group">
                        <legend>{'Membership Schema Settings'|@translate}</legend>
                        
                        <span id="ld_user_class_help" class="form-text text-muted">{'The attribute field to use when loading the group members from the group.'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_group_member_attr-addon1">{'Group Membership Attribute:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_group_member_attr" name="LD_GROUP_MEMBER_ATTR" value="{$LD_GROUP_MEMBER_ATTR}" placeholder="" aria-label="{'Group Membership Attribute:'|@translate}" >
                        </div>                           

                        <span id="ld_user_class_help" class="form-text text-muted">{'The attribute field when loading groups from a user.'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_user_member_attr-addon1">{'User Membership Attribute:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_user_member_attr" name="LD_USER_MEMBER_ATTR" value="{$LD_USER_MEMBER_ATTR}" placeholder="" aria-label="{'User Membership Attribute:'|@translate}" >
                        </div>                             
                        
                        <div class="btn-group">
                          <input type="radio" class="btn-check" name="LD_MEMBERSHIP_USER" id="ld_membership_user1"  autocomplete="off" checked/>
                          <label class="btn btn-outline-secondary" for="ld_membership_user1">{'Use USER membership attribute'|@translate}</label>

                          <input type="radio" class="btn-check" name="LD_MEMBERSHIP_USER" id="ld_membership_user2"  autocomplete="off" />
                          <label class="btn btn-outline-secondary" for="ld_membership_user2">{'Use GROUP membership attribute'|@translate}</label>
                        </div>                        
                        
                        
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_member_attr">{'Group Membership Attribute:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_group_member_attr" name="LD_GROUP_MEMBER_ATTR" value="{$LD_GROUP_MEMBER_ATTR}" /> {if isset($WARN_LD_GROUP_MEMBER_ATTR)}<i style="color:red;">{$WARN_LD_GROUP_MEMBER_ATTR}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The attribute field to use when loading the group members from the group.'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_user_member_attr">{'User Membership Attribute:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_user_member_attr" name="LD_USER_MEMBER_ATTR" value="{$LD_USER_MEMBER_ATTR}" /> {if isset($WARN_LD_USER_MEMBER_ATTR)}<i style="color:red;">{$WARN_LD_USER_MEMBER_ATTR}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The attribute field when loading groups from a user.'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_membership_user"> {if isset($LD_MEMBERSHIP_USER) } -->
                                <!-- <input type="checkbox" id="ld_membership_user" name="LD_MEMBERSHIP_USER" value="{$LD_MEMBERSHIP_USER}" checked /> {else} -->
                                <!-- <input type="checkbox" id="ld_membership_user" name="LD_MEMBERSHIP_USER" value="{$LD_MEMBERSHIP_USER}" /> {/if}{'Use user membership attribute'|@translate}</label> {if isset($WARN_LD_MEMBERSHIP_USER)}<i style="color:red;">{$WARN_LD_MEMBERSHIP_USER}</i>{/if} </p> -->
                    </fieldset>
                  </div>
                </div>
                <div class="tab-pane fade " id="tabMembershipSettings">
                  <div class="card card-body">
                    <fieldset class="form-group">
                        <legend>{'Membership Settings'|@translate}</legend> 
                        <a href="https://piwigo.org/doc/doku.php?id=user_documentation:use:features:user_status" target="_blank	" style="position: relative;display: inline-block;border-bottom: 1px dotted black;">More info about built-in groups on Piwigo.org</a>
                        <br>
                        <br>
                        
                        <span id="ld_user_class_help" class="form-text text-muted">{'The group that will get user rights (DN).'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="checkbox" aria-label="Checkbox for following text input"  id="ld_group_user_active" name="LD_GROUP_USER_ACTIVE" type="checkbox" value="{$LD_GROUP_USER_ACTIVE}"  checked>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_group_user-addon1">{'Group corresponding with users:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_group_user" name="LD_GROUP_USER" value="{$LD_GROUP_USER}" placeholder="" aria-label="{'Group corresponding with users:'|@translate}" >
                        </div> 

                        <span id="ld_user_class_help" class="form-text text-muted">{'The group that will get admininistrator rights (DN).'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="checkbox" aria-label="Checkbox for following text input"  id="ld_group_admin_active" name="LD_GROUP_ADMIN_ACTIVE" type="checkbox" value="{$LD_GROUP_ADMIN_ACTIVE}"  checked>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_group_admin-addon1">{'Group corresponding with administrators:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_group_admin" name="LD_GROUP_ADMIN" value="{$LD_GROUP_ADMIN}" placeholder="" aria-label="{'Group corresponding with administrators:'|@translate}" >
                        </div> 
	
                        <span id="ld_user_class_help" class="form-text text-muted">{'The group that will get webmaster rights (DN).'|@translate}</span>
                        <div class="input-group mb-3">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="checkbox" aria-label="Checkbox for following text input"  id="ld_group_webmaster_active" name="LD_GROUP_WEBMASTER_ACTIVE" type="checkbox" value="{$LD_GROUP_WEBMASTER_ACTIVE}"  checked>
                            </div>
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="ld_group_webmaster-addon1">{'Group corresponding with webmasters:'|@translate}</span>
                            </div>            
                            <input type="text" class="form-control" id="ld_group_webmaster" name="LD_GROUP_WEBMASTER" value="{$LD_GROUP_WEBMASTER}" placeholder="" aria-label="{'Group corresponding with webmasters:'|@translate}" >
                        </div> 
	
                        <!-- <br> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_user">{'Group corresponding with users:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_group_user" name="LD_GROUP_USER" value="{$LD_GROUP_USER}" /> {if isset($WARN_LD_GROUP_USER)}<i style="color:red;">{$WARN_LD_GROUP_USER}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The group that will get user rights (DN).'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_admin">{'Group corresponding with administrators:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_group_admin" name="LD_GROUP_ADMIN" value="{$LD_GROUP_ADMIN}" /> {if isset($WARN_LD_GROUP_ADMIN)}<i style="color:red;">{$WARN_LD_GROUP_ADMIN}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The group that will get admininistrator rights (DN).'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_WEBMASTER">{'Group corresponding with webmasters:'|@translate}</label> -->
                            <!-- <input size="70" type="text" id="ld_group_webmaster" name="LD_GROUP_WEBMASTER" value="{$LD_GROUP_WEBMASTER}" /> {if isset($WARN_LD_GROUP_WEBMASTER)}<i style="color:red;">{$WARN_LD_GROUP_WEBMASTER}</i>{/if} -->
                            <!-- <br> <i style="margin:15%;">{'The group that will get webmaster rights (DN).'|@translate}</i> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_user_active"> {if isset($LD_GROUP_USER_ACTIVE) } -->
                                <!-- <input type="checkbox" id="ld_group_user_active" name="LD_GROUP_USER_ACTIVE" value="{$LD_GROUP_USER_ACTIVE}" checked /> {else} -->
                                <!-- <input type="checkbox" id="ld_group_user_active" name="LD_GROUP_USER_ACTIVE" value="{$LD_GROUP_USER_ACTIVE}" /> {/if}{'Use user groups'|@translate}</label> <i>Note: Minimum membership to gain access. Disabled, no check for group membership.</i> {if isset($WARN_LD_GROUP_USER_ACTIVE)}<i style="color:red;">{$WARN_LD_GROUP_USER_ACTIVE}</i>{/if} -->
                            <!-- <br> </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_admin_active"> {if isset($LD_GROUP_ADMIN_ACTIVE) } -->
                                <!-- <input type="checkbox" id="ld_group_admin_active" name="ld_group_admin_active" value="{$LD_GROUP_ADMIN_ACTIVE}" checked /> {else} -->
                                <!-- <input type="checkbox" id="ld_group_admin_active" name="LD_GROUP_ADMIN_ACTIVE" value="{$LD_GROUP_ADMIN_ACTIVE}" /> {/if}{'Use administrator groups.'|@translate}</label> <i>Note: Dynamic when enabled and persistent when disabled. Change manual 'level' of user when disabled.</i> {if isset($WARN_LD_GROUP_ADMIN_ACTIVE)}<i style="color:red;">{$WARN_LD_GROUP_ADMIN_ACTIVE}</i>{/if} </p> -->
                        <!-- <p> -->
                            <!-- <label style="display:inline-block; width:15%;" for="ld_group_webmaster_active"> {if $LD_GROUP_WEBMASTER_ACTIVE } -->
                                <!-- <input type="checkbox" id="ld_group_webmaster_active" name="ld_group_WEBMASTER_active" value="{$LD_GROUP_WEBMASTER_ACTIVE}" checked /> {else} -->
                                <!-- <input type="checkbox" id="ld_group_webmaster_active" name="LD_GROUP_WEBMASTER_ACTIVE" value="{$LD_GROUP_WEBMASTER_ACTIVE}" /> {/if}{'Use Webmaster groups.'|@translate}</label> <i>Note: Dynamic when enabled and persistent when disabled. Change manual 'level' of user when disabled.</i> {if isset($WARN_LD_GROUP_WEBMASTER_ACTIVE)}<i style="color:red;">{$WARN_LD_GROUP_WEBMASTER_ACTIVE}</i>{/if} </p> -->
                    </fieldset>
                  </div>
                </div>
                <div class="tab-pane fade " id="close">
                </div>                  
            </div>                        
        </div>
     </div>   
    <!-- <div class="row"> -->
		<!-- <div class="col"> -->
			<!-- <fieldset class="form-group"> -->
				<!-- <legend>{'User Schema Settings'|@translate}</legend> <i>Required for user filter: (&(&(objectClass=<b>User_Object_Class</b>)(<b>Username_Attribute</b>=Login_Form_username))(<b>User_Object_Filter</b>)</i> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_user_class">{'User Object Class:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_user_class" name="LD_USER_CLASS" value="{$LD_USER_CLASS}" /> {if isset($WARN_LD_USER_CLASS)}<i style="color:red;">{$WARN_LD_USER_CLASS}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The LDAP user object class type to use when loading users'|@translate}</i> -->
					<!-- <br> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_user_attr">{'Username Attribute'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_user_attr" name="LD_USER_ATTR" value="{$LD_USER_ATTR}" /> {if isset($WARN_LD_USER_ATTR)}<i style="color:red;">{$WARN_LD_USER_ATTR}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The attribute field to use on the user object. Examples: cn, sAMAccountName.'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_user_filter">{'User Object Filter:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_user_filter" name="LD_USER_FILTER" value="{$LD_USER_FILTER}" /> {if isset($WARN_LD_USER_FILTER)}<i style="color:red;">{$WARN_LD_USER_FILTER}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The filter to use when searching user objects'|@translate}</i> -->
					<!-- <br> </p> -->
			<!-- </fieldset> -->
		<!-- </div> -->
		<!-- <div class="col"> -->
			<!-- <fieldset class="form-group"> -->
				<!-- <legend>{'Group Schema Settings'|@translate}</legend> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_class">{'Group Object Class:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_group_class" name="LD_GROUP_CLASS" value="{$LD_GROUP_CLASS}" /> {if isset($WARN_LD_GROUP_CLASS)}<i style="color:red;">{$WARN_LD_GROUP_CLASS}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'LDAP attribute objectClass value to search for when loading groups.'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_filter">{'Group Object Filter:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_group_filter" name="LD_GROUP_FILTER" value="{$LD_GROUP_FILTER}" /> {if isset($WARN_LD_GROUP_FILTER)}<i style="color:red;">{$WARN_LD_GROUP_FILTER}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The filter to use when searching group objects.'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_attr">{'Group Name Attribute:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_group_attr" name="LD_GROUP_ATTR" value="{$LD_GROUP_ATTR}" /> {if isset($WARN_LD_GROUP_ATTR)}<i style="color:red;">{$WARN_LD_GROUP_ATTR}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The attribute field to use when loading the group name.'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_desc">{'Group Description:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_group_desc" name="LD_GROUP_DESC" value="{$LD_GROUP_DESC}" /> {if isset($WARN_LD_GROUP_DESC)}<i style="color:red;">{$WARN_LD_GROUP_DESC}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The attribute field to use when loading the group description.'|@translate}</i> </p> -->
			<!-- </fieldset> -->
		<!-- </div> -->
		<!-- <div class="col"> -->
			<!-- <fieldset class="form-group"> -->
				<!-- <legend>{'Membership Schema Settings'|@translate}</legend> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_member_attr">{'Group Membership Attribute:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_group_member_attr" name="LD_GROUP_MEMBER_ATTR" value="{$LD_GROUP_MEMBER_ATTR}" /> {if isset($WARN_LD_GROUP_MEMBER_ATTR)}<i style="color:red;">{$WARN_LD_GROUP_MEMBER_ATTR}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The attribute field to use when loading the group members from the group.'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_user_member_attr">{'User Membership Attribute:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_user_member_attr" name="LD_USER_MEMBER_ATTR" value="{$LD_USER_MEMBER_ATTR}" /> {if isset($WARN_LD_USER_MEMBER_ATTR)}<i style="color:red;">{$WARN_LD_USER_MEMBER_ATTR}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The attribute field when loading groups from a user.'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_membership_user"> {if isset($LD_MEMBERSHIP_USER) } -->
						<!-- <input type="checkbox" id="ld_membership_user" name="LD_MEMBERSHIP_USER" value="{$LD_MEMBERSHIP_USER}" checked /> {else} -->
						<!-- <input type="checkbox" id="ld_membership_user" name="LD_MEMBERSHIP_USER" value="{$LD_MEMBERSHIP_USER}" /> {/if}{'Use user membership attribute'|@translate}</label> {if isset($WARN_LD_MEMBERSHIP_USER)}<i style="color:red;">{$WARN_LD_MEMBERSHIP_USER}</i>{/if} </p> -->
			<!-- </fieldset> -->
			<!-- <fieldset class="form-group"> -->
				<!-- <legend>{'Membership Settings'|@translate}</legend> <a href="https://piwigo.org/doc/doku.php?id=user_documentation:use:features:user_status" target="_blank	" style="position: relative;display: inline-block;border-bottom: 1px dotted black;margin-left: 10px;">More info about built-in groups on Piwigo.org</a> -->
				<!-- <br> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_user">{'Group corresponding with users:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_group_user" name="LD_GROUP_USER" value="{$LD_GROUP_USER}" /> {if isset($WARN_LD_GROUP_USER)}<i style="color:red;">{$WARN_LD_GROUP_USER}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The group that will get user rights (DN).'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_admin">{'Group corresponding with administrators:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_group_admin" name="LD_GROUP_ADMIN" value="{$LD_GROUP_ADMIN}" /> {if isset($WARN_LD_GROUP_ADMIN)}<i style="color:red;">{$WARN_LD_GROUP_ADMIN}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The group that will get admininistrator rights (DN).'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_WEBMASTER">{'Group corresponding with webmasters:'|@translate}</label> -->
					<!-- <input size="70" type="text" id="ld_group_webmaster" name="LD_GROUP_WEBMASTER" value="{$LD_GROUP_WEBMASTER}" /> {if isset($WARN_LD_GROUP_WEBMASTER)}<i style="color:red;">{$WARN_LD_GROUP_WEBMASTER}</i>{/if} -->
					<!-- <br> <i style="margin:15%;">{'The group that will get webmaster rights (DN).'|@translate}</i> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_user_active"> {if isset($LD_GROUP_USER_ACTIVE) } -->
						<!-- <input type="checkbox" id="ld_group_user_active" name="LD_GROUP_USER_ACTIVE" value="{$LD_GROUP_USER_ACTIVE}" checked /> {else} -->
						<!-- <input type="checkbox" id="ld_group_user_active" name="LD_GROUP_USER_ACTIVE" value="{$LD_GROUP_USER_ACTIVE}" /> {/if}{'Use user groups'|@translate}</label> <i>Note: Minimum membership to gain access. Disabled, no check for group membership.</i> {if isset($WARN_LD_GROUP_USER_ACTIVE)}<i style="color:red;">{$WARN_LD_GROUP_USER_ACTIVE}</i>{/if} -->
					<!-- <br> </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_admin_active"> {if isset($LD_GROUP_ADMIN_ACTIVE) } -->
						<!-- <input type="checkbox" id="ld_group_admin_active" name="ld_group_admin_active" value="{$LD_GROUP_ADMIN_ACTIVE}" checked /> {else} -->
						<!-- <input type="checkbox" id="ld_group_admin_active" name="LD_GROUP_ADMIN_ACTIVE" value="{$LD_GROUP_ADMIN_ACTIVE}" /> {/if}{'Use administrator groups.'|@translate}</label> <i>Note: Dynamic when enabled and persistent when disabled. Change manual 'level' of user when disabled.</i> {if isset($WARN_LD_GROUP_ADMIN_ACTIVE)}<i style="color:red;">{$WARN_LD_GROUP_ADMIN_ACTIVE}</i>{/if} </p> -->
				<!-- <p> -->
					<!-- <label style="display:inline-block; width:15%;" for="ld_group_webmaster_active"> {if $LD_GROUP_WEBMASTER_ACTIVE } -->
						<!-- <input type="checkbox" id="ld_group_webmaster_active" name="ld_group_WEBMASTER_active" value="{$LD_GROUP_WEBMASTER_ACTIVE}" checked /> {else} -->
						<!-- <input type="checkbox" id="ld_group_webmaster_active" name="LD_GROUP_WEBMASTER_ACTIVE" value="{$LD_GROUP_WEBMASTER_ACTIVE}" /> {/if}{'Use Webmaster groups.'|@translate}</label> <i>Note: Dynamic when enabled and persistent when disabled. Change manual 'level' of user when disabled.</i> {if isset($WARN_LD_GROUP_WEBMASTER_ACTIVE)}<i style="color:red;">{$WARN_LD_GROUP_WEBMASTER_ACTIVE}</i>{/if} </p> -->
			<!-- </fieldset> -->
		<!-- </div> -->
	<!-- </div> -->
	</form>
	<form method="post" action="{$PLUGIN_CHECK}" class="general">
		<fieldset class="form-group">
			<legend>{'Ldap_Login Test'|@translate}</legend> <i>{'You must save the settings with the Save button just up there before testing here.'|@translate}</i>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="username-addon1">{'Username'|@translate}</span>
                </div>            
                <input type="text" class="form-control" id="username" name="USERNAME" value="{$USERNAME}" placeholder="" aria-label="{'Username'|@translate}" >
            </div>

            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="password-addon1">{'Your password'|@translate}</span>
                </div>            
                <input type="password" class="form-control" id="password" name="PASSWORD"  placeholder="" aria-label="{'Your password'|@translate}" >
            </div>
            <input type="submit" class="btn btn-primary btn-lg btn-block" value="{'Test Settings'|@translate}" name="check_ldap" />
            
            
			<!-- <ul> -->
				<!-- <li> -->
					<!-- <label for="username">{'Username'|@translate}</label> -->
					<!-- <br> -->
					<!-- <input type="text" id="username" name="USERNAME" value="{$USERNAME}" /> </li> -->
				<!-- <li> -->
					<!-- <label for="password">{'Your password'|@translate}</label> -->
					<!-- <br> -->
					<!-- <input type="password" id="password" name="PASSWORD" value="{if isset($WARN_LD_BINDPW)}{$PASSWORD}{/if}" /> </li> -->
			<!-- </ul> {if (!empty($LD_CHECK_LDAP))} {$LD_CHECK_LDAP} {/if} </fieldset> -->
		<!-- <p> -->
			<!-- <input type="submit" value="{'Test Settings'|@translate}" name="check_ldap" /> </p> -->
	</form>
</div>
{*<!--
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
		<i>{'If using MS AD, choose Active Directory, else choose OpenLDAP'|@translate}</i>		
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
-->*}