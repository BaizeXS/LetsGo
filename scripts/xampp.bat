@echo off
REM Windows batch script for executing xampp.sh

REM Check if Git Bash is installed
where bash >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo Error: bash command not found. Please ensure Git Bash is installed.
    echo You can download Git for Windows from https://git-scm.com/downloads.
    exit /b 1
)

REM Execute the bash script
bash "%~dp0xampp.sh" %* 