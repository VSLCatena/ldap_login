param (
	[Parameter(Mandatory=$true)][string]$WorkingDir,
    [string]$DocTemplatePath = "$WorkingDir/.scripts/Update-ReadmeWikiPage.doc.ps1"
)
Install-Module -Name PSDocs -Force
import-module -Name PSDocs

# create file in root if RootFolder switch enabled
$outputPath = "$WorkingDir"

$datafile = "$outputPath/data.json"

$regex = "\s\'(?<key>ld_.*)' ?=> ?(?<value>.*)'?,"
$default = (Get-Content $WorkingDir/class.ldap.php | select-string -AllMatches -pattern $regex ).matches |Foreach-Object { 
    [pscustomobject]@{ "name"=$_.groups.where({$_.name -eq 'key'}).value;"value"=$_.groups.where({$_.name -eq 'value'}).value }  
}
$default | ConvertTo-Json | Out-File -FilePath $datafile

$metadatafile = "$outputPath/metadata.json"

$PSDocsInputObject = New-Object PsObject -property @{
    'DataFile' = $Datafile
    'MetadataFile' = $Metadatafile
}

# Generate /WorkingDir/PipelineName/README.md
Invoke-PSDocument -Path $DocTemplatePath -InputObject $PSDocsInputObject -OutputPath $outputPath -Instance Readme
