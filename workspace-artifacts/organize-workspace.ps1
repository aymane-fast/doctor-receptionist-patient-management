$ErrorActionPreference = 'Stop'
$workspaceRoot = (Resolve-Path -LiteralPath 'C:\Users\fast\Desktop\Coding\doctor_apointment').Path
$bundleRoot = [System.IO.Path]::GetFullPath((Join-Path $workspaceRoot 'workspace-artifacts'))
$workspacePrefix = $workspaceRoot.TrimEnd('\') + '\'
if (!$bundleRoot.StartsWith($workspacePrefix, [System.StringComparison]::OrdinalIgnoreCase)) {
    throw 'Artifact folder must stay inside the workspace.'
}

$plan = @(
    @{ Source = 'outputs'; Target = 'outputs' },
    @{ Source = 'output'; Target = 'output' },
    @{ Source = 'tmp'; Target = 'tmp' },
    @{ Source = 'docs'; Target = 'docs' },
    @{ Source = 'AI_SETUP_GUIDE.md'; Target = 'guides\AI_SETUP_GUIDE.md' },
    @{ Source = 'AI_VISUAL_GUIDE.md'; Target = 'guides\AI_VISUAL_GUIDE.md' },
    @{ Source = 'QUICK_START_AI.md'; Target = 'guides\QUICK_START_AI.md' },
    @{ Source = 'AUDIT_CHECKLIST.md'; Target = 'docs\AUDIT_CHECKLIST.md' },
    @{ Source = 'database-documentation.html'; Target = 'diagrams\database-documentation.html' },
    @{ Source = 'database-schema.html'; Target = 'diagrams\database-schema.html' },
    @{ Source = 'test-modal.html'; Target = 'experiments\test-modal.html' }
)

$manifest = [System.Collections.Generic.List[object]]::new()
# Validate every resolved source and destination before moving any content.
foreach ($entry in $plan) {
    $source = [System.IO.Path]::GetFullPath((Join-Path $workspaceRoot $entry.Source))
    $target = [System.IO.Path]::GetFullPath((Join-Path $bundleRoot $entry.Target))
    if (!$source.StartsWith($workspacePrefix, [System.StringComparison]::OrdinalIgnoreCase) -or
        !$target.StartsWith($bundleRoot + '\', [System.StringComparison]::OrdinalIgnoreCase)) {
        throw 'A move escaped the intended workspace/artifact folder.'
    }
    $entry.SourcePath = $source
    $entry.TargetPath = $target
    $item = Get-Item -LiteralPath $source -Force -ErrorAction SilentlyContinue
    $existingTarget = Get-Item -LiteralPath $target -Force -ErrorAction SilentlyContinue
    if (!$item -and !$existingTarget) { throw "Neither original nor archived item exists: $source" }
    if ($existingTarget) {
        $existingFiles = if ($existingTarget.PSIsContainer) { @(Get-ChildItem -LiteralPath $target -File -Recurse -Force) } else { @($existingTarget) }
        foreach ($file in $existingFiles) {
            $suffix = if ($existingTarget.PSIsContainer) { $file.FullName.Substring($target.Length).TrimStart('\') } else { '' }
            $oldPath = if ($suffix) { Join-Path $source $suffix } else { $source }
            if (Test-Path -LiteralPath $oldPath) { throw "File collision; refusing overwrite: $oldPath" }
            $manifest.Add([pscustomobject]@{
                OriginalPath = $oldPath.Substring($workspacePrefix.Length)
                ArchivedPath = $file.FullName.Substring($workspacePrefix.Length)
                Bytes = $file.Length
                SHA256 = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash
            })
        }
    }
    $files = if (!$item) { @() } elseif ($item.PSIsContainer) { @(Get-ChildItem -LiteralPath $source -File -Recurse -Force) } else { @($item) }
    foreach ($file in $files) {
        $suffix = if ($item.PSIsContainer) { $file.FullName.Substring($source.Length).TrimStart('\') } else { '' }
        $newPath = if ($suffix) { Join-Path $target $suffix } else { $target }
        $manifest.Add([pscustomobject]@{
            OriginalPath = $file.FullName.Substring($workspacePrefix.Length)
            ArchivedPath = $newPath.Substring($workspacePrefix.Length)
            Bytes = $file.Length
            SHA256 = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash
        })
    }
}

# Record the baseline before any further move, so interruption is recoverable.
$manifest | Export-Csv -LiteralPath (Join-Path $bundleRoot 'move-manifest.csv') -NoTypeInformation -Encoding utf8

function Move-PreservedItem([string] $source, [string] $target) {
    $source = [System.IO.Path]::GetFullPath($source)
    $target = [System.IO.Path]::GetFullPath($target)
    if (!$source.StartsWith($workspacePrefix, [System.StringComparison]::OrdinalIgnoreCase) -or
        !$target.StartsWith($bundleRoot + '\', [System.StringComparison]::OrdinalIgnoreCase)) { throw 'Unsafe move path.' }
    if (!(Test-Path -LiteralPath $source)) { return }
    if (!(Test-Path -LiteralPath $target)) {
        $parent = Split-Path -Parent $target
        if (!(Test-Path -LiteralPath $parent)) { New-Item -Path $parent -ItemType Directory | Out-Null }
        Move-Item -LiteralPath $source -Destination $target
        return
    }
    if (!(Get-Item -LiteralPath $source -Force).PSIsContainer -or !(Get-Item -LiteralPath $target -Force).PSIsContainer) {
        throw "Refusing overwrite: $target"
    }
    foreach ($child in @(Get-ChildItem -LiteralPath $source -Force)) {
        Move-PreservedItem $child.FullName (Join-Path $target $child.Name)
    }
    # Keep even empty original directories; do not delete them.
    $emptyTarget = Join-Path $bundleRoot ('empty-original-folders\' + $source.Substring($workspacePrefix.Length))
    if (Test-Path -LiteralPath $emptyTarget) { $emptyTarget += '-' + [guid]::NewGuid().ToString('N') }
    $parent = Split-Path -Parent $emptyTarget
    if (!(Test-Path -LiteralPath $parent)) { New-Item -Path $parent -ItemType Directory | Out-Null }
    Move-Item -LiteralPath $source -Destination $emptyTarget
}

foreach ($entry in $plan) {
    Move-PreservedItem $entry.SourcePath $entry.TargetPath
}

foreach ($file in $manifest) {
    $newPath = Join-Path $workspaceRoot $file.ArchivedPath
    if (!(Test-Path -LiteralPath $newPath) -or
        (Get-FileHash -LiteralPath $newPath -Algorithm SHA256).Hash -ne $file.SHA256) {
        throw "Archive verification failed: $($file.ArchivedPath)"
    }
}
$manifest | Export-Csv -LiteralPath (Join-Path $bundleRoot 'move-manifest.csv') -NoTypeInformation -Encoding utf8
[pscustomobject]@{
    FilesMoved = $manifest.Count
    BytesPreserved = ($manifest | Measure-Object -Property Bytes -Sum).Sum
    VerifiedHashes = $manifest.Count
    Folder = $bundleRoot
} | ConvertTo-Json
