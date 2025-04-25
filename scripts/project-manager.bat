@echo off
REM Windows batch script for executing project-manager.sh

REM Check if Git Bash is installed
where bash >nul 2>nul || (
    echo Error: bash command not found. Please make sure Git Bash is installed.
    echo You can download Git for Windows from https://git-scm.com/downloads.
    exit /b 1
)

REM Execute bash script
bash ./scripts/project-manager.sh %* 