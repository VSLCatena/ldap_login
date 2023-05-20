param (
	[Parameter(Mandatory=$true)][string]$WorkingDir,
    [Parameter(Mandatory=$true)][string]$PipelineName,
    [Parameter(Mandatory=$true)][string]$PipelineID,
    [switch]$RootFolder,
    [string]$DocTemplatePath = '.scripts\Update-ReadmeWikiPage.doc.ps1',
)
Install-Module -Name PSDocs -Force
import-module -Name PSDocs

# create file in root if RootFolder switch enabled
if($RootFolder.IsPresent -eq $true) {
     $outputPath = "$WorkingDir"
} else {
     $outputPath = "$WorkingDir\$PipelineName"
}

$metadatafile = "$outputPath\metadata.json"

$PSDocsInputObject = New-Object PsObject -property @{
    'MetadataFile' = $metadatafile
    'PipelineID' = $PipelineID
    'PipelineName' = $PipelineName
}

# Generate /WorkingDir/PipelineName/README.md
Invoke-PSDocument -Path "$WorkingDir\$DocTemplatePath" -InputObject $PSDocsInputObject -OutputPath $outputPath -Instance 
