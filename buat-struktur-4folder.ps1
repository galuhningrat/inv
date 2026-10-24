# ============================================================
# Hanya mencetak isi 4 folder: app, database, resources, routes
# ============================================================

$Root = "C:\Users\galuhningrat\Documents\Project\inv-stti"
$Out  = Join-Path $Root "struktur-4-folder.txt"
$TargetFolders = @("app", "database", "resources", "routes")

function Write-Tree {
    param($Path, $Indent, $Writer)
    $items = Get-ChildItem -LiteralPath $Path -Force |
             Sort-Object { -not $_.PSIsContainer }, Name
    foreach ($item in $items) {
        if ($item.PSIsContainer) {
            $Writer.WriteLine("$Indent+-- $($item.Name)\")
            Write-Tree -Path $item.FullName -Indent "$Indent|   " -Writer $Writer
        } else {
            $Writer.WriteLine("$Indent|   $($item.Name)")
        }
    }
}

$w = New-Object System.IO.StreamWriter($Out, $false, [System.Text.Encoding]::UTF8)
try {
    $w.WriteLine("STRUKTUR 4 FOLDER PROJECT INV-STTI")
    $w.WriteLine("Root   : $Root")
    $w.WriteLine("Dibuat : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')")
    $w.WriteLine("")
    $w.WriteLine("CATATAN: Hanya folder app, database, resources, routes.")
    $w.WriteLine("vendor, node_modules, storage, public, config, bootstrap,")
    $w.WriteLine("tests, dan file root TIDAK disertakan.")
    $w.WriteLine("")

    foreach ($folder in $TargetFolders) {
        $full = Join-Path $Root $folder
        $w.WriteLine("=" * 60)
        $w.WriteLine("FOLDER: $folder")
        $w.WriteLine("=" * 60)
        $w.WriteLine("$folder\")
        if (Test-Path -LiteralPath $full) {
            Write-Tree -Path $full -Indent "" -Writer $w
        } else {
            $w.WriteLine("  (tidak ditemukan)")
        }
        $w.WriteLine("")
    }
}
finally { $w.Close() }

Write-Host "Selesai -> $Out" -ForegroundColor Green