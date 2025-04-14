# PowerShell Script for migrating files from LetsGo directory to root
# Author: AI Assistant
# Date: 当前日期

# Create backup directory if not exists
if (-not (Test-Path -Path "./temp_backup/LetsGo_backup")) {
    New-Item -ItemType Directory -Path "./temp_backup/LetsGo_backup" -Force
}

# Function to handle file conflicts
function Copy-FileWithConflictCheck {
    param (
        [string]$sourcePath,
        [string]$destPath,
        [string]$relativePath
    )

    $backupPath = "./temp_backup/LetsGo_backup/$relativePath"
    
    # Create backup directory structure
    $backupDir = Split-Path -Path $backupPath -Parent
    if (-not (Test-Path -Path $backupDir)) {
        New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
    }
    
    # Check if destination file exists
    if (Test-Path -Path $destPath) {
        # Compare files
        $diffResult = Compare-Object -ReferenceObject (Get-Content -Path $sourcePath -Raw) -DifferenceObject (Get-Content -Path $destPath -Raw) -ErrorAction SilentlyContinue
        
        if ($null -ne $diffResult) {
            # Files are different, backup the source file
            Write-Host "Conflict detected for $relativePath. Backing up LetsGo version..." -ForegroundColor Yellow
            Copy-Item -Path $sourcePath -Destination $backupPath -Force
            Write-Host "  - Backed up to $backupPath" -ForegroundColor Cyan
        } else {
            Write-Host "Files are identical: $relativePath" -ForegroundColor Green
        }
    } else {
        # Destination file doesn't exist, copy from source
        Write-Host "Copying new file: $relativePath" -ForegroundColor Green
        New-Item -ItemType Directory -Path (Split-Path -Path $destPath -Parent) -Force -ErrorAction SilentlyContinue | Out-Null
        Copy-Item -Path $sourcePath -Destination $destPath -Force
    }
}

# Function to recursively process directories
function Process-Directory {
    param (
        [string]$sourceDir,
        [string]$destDir,
        [string]$relativePath = ""
    )

    # Get all files in the source directory
    $files = Get-ChildItem -Path $sourceDir -File
    
    foreach ($file in $files) {
        $relativeFilePath = if ($relativePath -eq "") { $file.Name } else { "$relativePath/$($file.Name)" }
        $sourceFilePath = "$sourceDir/$($file.Name)"
        $destFilePath = "$destDir/$($file.Name)"
        
        Copy-FileWithConflictCheck -sourcePath $sourceFilePath -destPath $destFilePath -relativePath $relativeFilePath
    }
    
    # Process subdirectories
    $dirs = Get-ChildItem -Path $sourceDir -Directory
    
    foreach ($dir in $dirs) {
        $relativeSubDir = if ($relativePath -eq "") { $dir.Name } else { "$relativePath/$($dir.Name)" }
        $sourceSubDir = "$sourceDir/$($dir.Name)"
        $destSubDir = "$destDir/$($dir.Name)"
        
        # Skip the .git directory
        if ($dir.Name -eq ".git") {
            Write-Host "Skipping .git directory" -ForegroundColor Magenta
            continue
        }
        
        # Create destination directory if it doesn't exist
        if (-not (Test-Path -Path $destSubDir)) {
            New-Item -ItemType Directory -Path $destSubDir -Force | Out-Null
            Write-Host "Created directory: $destSubDir" -ForegroundColor Cyan
        }
        
        # Process the subdirectory
        Process-Directory -sourceDir $sourceSubDir -destDir $destSubDir -relativePath $relativeSubDir
    }
}

# Main execution
Write-Host "Starting migration from LetsGo directory to root..." -ForegroundColor Cyan
Process-Directory -sourceDir "./LetsGo" -destDir "."
Write-Host "Migration complete. Any conflicting files were backed up to temp_backup/LetsGo_backup/" -ForegroundColor Green
Write-Host "Please verify your application still works correctly." -ForegroundColor Yellow 