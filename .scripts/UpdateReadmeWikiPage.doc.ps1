#
# Azure Resource Manager documentation definitions
#



# A function to break out parameters from an ARM template
function global:GetTemplateParameter {
    param (
        [Parameter(Mandatory = $True)]
        [String]$Path
    )

    process {
        $template = Get-Content $Path | ConvertFrom-Json;
        foreach ($property in $template.parameters.PSObject.Properties) {
            [PSCustomObject]@{
                Name = $property.Name
                Description = $property.Value.metadata.description
            }
        }
    }
}

# A function to import metadata
function global:GetTemplateMetadata {
    param (
        [Parameter(Mandatory = $True)]
        [String]$Path
    )

    process {
        $metadata = Get-Content $Path | ConvertFrom-Json;
        return $metadata;
    }
}
# Description: A definition to generate markdown for an ARM template
document 'Readme' {

    # Read JSON files
    $metadata = GetTemplateMetadata -Path $InputObject.MetadataFile;
    #$parameters = GetTemplateParameter -Path $InputObject.Template;

    #read content object
    $repos = $($InputObject.repos | convertfrom-json) | Group-Object category |Sort-Object -Property Name


    # Set document title
    Title $metadata.itemDisplayName

    '<!--

    DO NOT EDIT THIS PAGE!

    Every change will be overwritten by the pipeline generating this page.


    Parse & Design Script: .scripts\Update-ReadmeWikiPage.doc.ps1

    -->'

    # Write opening line
    $metadata.Description

    '[[_TOC_]]'

    # Generate example command line
    Section -Name 'Clone repository to local machine' -body {
            'git clone --branch <branch> <repo> [<dir>]' | Code powershell

    }
    "<br>"
    # Add each parameter to a table
    Section -force -name 'Readme' -body {

        foreach($cat in $objects) {
            "<br>"
            $items=$cat.group
            Section -force -Name $($cat.name.ToUpper()) -Body  {
                $items | Sort-Object -Property reponame | Table -Property @{ Name  = 'Language'; Expression  = { $($_.language).toUpper() }; Width = 105;  }, @{ Name  = 'Name'; Expression  = { "[$($_.reponame)]($($_.url))" }; Width = 220;  },@{ Name  = 'Definition'; Expression  = { "$($_.definition)" }; Width = 500;}, @{ Name  = 'Updated'; Expression  = { "$($_.latestupdate)"  }; Width = 100; },@{ Name  = 'By'; Expression  = { "$($_.latestupdateuser)" }; Width = 185;  },@{ Name  = 'Comment'; Expression  = { "$($_.latestupdatecomment)" }; Width = 400;  }
            }
        }
    }
}
