@echo off
title PegaChave - Inicializador de Servidores Locais
chcp 65001 > nul

echo =======================================================
echo        🔑 INICIANDO SERVIDORES DO PEGACHAVE 🔑
echo =======================================================
echo.

:: Obter o diretório atual do script
set "BASE_DIR=%~dp0"

echo [1/3] Iniciando Banco de Dados MySQL em segundo plano...
:: Inicia o MySQL daemon usando a configuração local
start /B "MySQL-PegaChave" "%BASE_DIR%bin\mysql\bin\mysqld.exe" --defaults-file="%BASE_DIR%bin\mysql\my.ini"

:: Pequena pausa para garantir a inicialização do MySQL
timeout /t 3 /nobreak > nul

echo [2/3] Iniciando Servidor Web PHP (localhost:8000)...
:: Inicia o PHP em segundo plano
start /B "PHP-PegaChave" "%BASE_DIR%bin\php\php.exe" -S localhost:8000

:: Pausa rápida
timeout /t 1 /nobreak > nul

echo [3/3] Abrindo o Quiosque no seu navegador padrão...
:: Abre o navegador no Quiosque
start http://localhost:8000

echo.
echo =======================================================
echo        ✅ SERVIDORES INICIADOS COM SUCESSO! ✅
echo =======================================================
echo.
echo   * Quiosque: http://localhost:8000
echo   * Admin:    http://localhost:8000/admin_login.php
echo.
echo  Para encerrar os servidores, basta fechar esta janela
echo  ou pressionar Ctrl+C.
echo =======================================================
echo.

:: Manter a janela aberta monitorando os logs ou aguardando encerramento
pause
