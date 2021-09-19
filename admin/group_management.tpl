<style>
tr.border_bottom td {
	border-bottom:1pt dashed grey;
}  
th {
	text-align:left !important;
}

td.content {
	font-size:x-small !important;
}


</style>
<h2>{'Ldap_Login Plugin'|@translate}</h2>

<div id="configContent">
	<fieldset>
	<h4 style="text-align: left;">{'Please use at own risk.'|@translate}</h4>
	<p style="text-align: left;">
	{'This page is for managing groups using LDAP. 
	First fill in your group OU and hit submit. 
	After that, use Refresh to get your groups. '|@translate}</p>
	<p style="text-align: left;">
	{'Removal of the group and users must be done manually! '|@translate}</p>
	</fieldset>

	<form method="post" action="{$SYNC_SETTINGS}" class="general">
		<fieldset class="mainConf">
		<br />
			<legend>{'Sync settings'|@translate}</legend>
			<i>{'You must have a working system before executing these functions.'|@translate}</i>
			<table class="table table-striped table-framed" style="margin-left: unset; margin-right:unset; min-width:50% !important;">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><input type="checkbox" id="sync_groups" name="sync[item][groups]" value="1" /></td>
						<td>{'Create Groups'|@translate}</td>
						<td><i>{'Activate the below selected groups with Piwigo, by creating them.'|@translate}</i></td>
					</tr>
					<tr>
						<td><input type="checkbox" id="sync_users" name="sync[item][users]" value="1" /></td>
						<td>{'Sync Users'|@translate}</td>
						<td><i>{'Sync members of groups, by adding them from Piwigo to groups '|@translate}</i></td>
					</tr>
					<tr>
						<td><input type="checkbox" id="sync_ldap" name="sync[item][ldap]" value="1" /></td>
						<td>{'Sync LDAP'|@translate}</td>
						<td><i>{'Remove old users, by looking into LDAP. New users will be automatically made with first login.'|@translate}</i></td>
					</tr>					
				</tbody>
			</table>
		</fieldset>
		
		<fieldset class="mainConf">
		<legend>{'DN with the groups'|@translate}</legend>	
		<p>
			<label style="display:inline-block; width:15%;" for="ld_group_basedn">{'Group Base DN:'|@translate}</label>
			<input size="70" type="text" id="ld_group_basedn" name="ld_group_basedn" value="{$LD_GROUP_BASEDN}" />
			<br><i style="margin:15%;">{'Base DN of groups where all albums reside.'|@translate}</i><br>
		</p>
		</fieldset>
		
		<p style="text-align: left !important;" >
		<i>{'Press Submit for the above actions. Only press "Refresh" to get new data via LDAP. Use with care.'|@translate}</i><br /><br />
			<input style="margin-left:10px; margin-right:10px;" type="submit" value="{'Submit'|@translate}" name="sync_action_submit" />
			<input style="margin-left:10px; margin-right:10px;" type="submit" value="{'Refresh'|@translate}" name="sync_action_refresh" />
		</p>
		<br />
		<fieldset class="mainConf">
			<br />
			<legend>{'Groups found in Group Base DN'|@translate}</legend>
			<table class="table table-striped table-framed" style="width:80%; margin-left: unset; margin-right:unset;  !important; ">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><font color="black">Name</font></th>				
						<th><font color="black">Members</font></th>				
					</tr>
					<tr>
						<th>&nbsp;</th>
					</tr>
					<tr>
						<th>&nbsp;</th>
						<th>&nbsp;</th>
						<th>Depth</th>
						<th>Object</th>
						<th>Path</th>
					</tr>	
				</thead>
				<tbody>	
					{foreach $LD_SYNC_DATA.0 as $key => $value}
						<tr class="border_bottom">
							<td>		
								<input type="checkbox" id="ld_sync_data_{$value.cn}" value=1 name="sync[groups][{$value.cn}]"  {if $value.active == True} checked="checked"{/if} />
							</td>
							<td>{$value.cn}</td>
							</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						{foreach $LD_SYNC_DATA.1.$key as $key2 => $value2}
							<tr >		
								<td class="content" >&nbsp;</td>
								<td class="content">&nbsp;</h6></td>
								<td class="content">{$value2.depth}</td>
								<td class="content">{$value2.cn}</td>
								<td class="content">{$value2.path}</td>
							</tr>		
						{/foreach}
					{/foreach}
				</tbody>
			</table>
			<br />
			<i>{'Depth is relative depth from highest group (0=top, 1=Group names, 2=Members, 3+ =submembers). Path is relative path from group to the members.'|@translate}</i><br />	
		</fieldset>
	</form>

</div>