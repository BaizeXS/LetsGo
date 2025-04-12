@echo off
REM Windows批处理脚本，用于执行xampp.sh

REM 检查是否安装了Git Bash
where bash >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo 错误: 未找到bash命令。请确保Git Bash已安装。
    echo 您可以从 https://git-scm.com/downloads 下载Git for Windows。
    exit /b 1
)

REM 执行bash脚本
bash "%~dp0xampp.sh" %* 