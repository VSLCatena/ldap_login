#
# A function to import data
function global:Get-TemplateData {
    param (
        [Parameter(Mandatory = $True)]
        [String]$Path
    )

    process {
        $data = Get-Content -path $Path | ConvertFrom-Json;
        return $data;
    }
}
# Description: A definition to generate markdown for an ARM template
Document 'Readme' {

    # Read JSON files
    $metadata = Get-TemplateData -Path $InputObject.MetadataFile
    $data = Get-TemplateData -Path $InputObject.DataFile
    $data | Foreach-Object { 
        $k = $_ 
        $k |  add-member -MemberType NoteProperty -Name description -value $metadata.keys.where({$_.key -eq $k.name}).description -force 
    }

    # Set document title
    Title $metadata.itemDisplayName
    '<!--

    DO NOT EDIT THIS PAGE!
    Every change will be overwritten by the pipeline generating this page.
    Parse & Design Script: .\.scripts\Update-ReadmeWikiPage.doc.ps1

    -->'
    # Write opening line
    $metadata.Description

    '[[_TOC_]]'
    '<br>'

    Section -force -Name 'Ldap_Login' -Body {
        'LDAP authentication plugin for piwigo'
        '_Incompatible with other Directory Authentication plugins, please deactivate the previous version before using this version. Also bear in mind that it can not be guarranteed that this plugin is safe. I try to keep security as high as possible but keep in mind that YOU are responsible for YOUR server._ '

        Section -force -Name 'Default config' -Body {
            'Most settings can be changed when the plugin activates and you visit configuration page.'
            $data | Sort-Object -Property name | Table -Property @{ Name = 'Key'; Expression = { $_.name }; }, @{ Name = 'Default Value'; Expression = { $_.value };  }, @{ Name = 'Definition'; Expression = { $_.description };  };
            }
    }
}
