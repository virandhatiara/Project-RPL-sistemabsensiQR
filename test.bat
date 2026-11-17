@echo off
REM Script Pengujian Unit Test Otomatis untuk Sistem Absensi SMK NU WARGABINANGUN
REM Rekayasa Perangkat Lunak (RPL) - Antigravity CI/CD

echo ========================================================
echo       MENJALANKAN UNIT TESTING (PHPUNIT) SECARA LOKAL
echo ========================================================
echo.

WHERE php >nul 2>nul
IF %ERRORLEVEL% EQU 0 (
    SET PHP_BIN=php
) ELSE (
    IF EXIST "C:\xampp\php\php.exe" (
        SET PHP_BIN=C:\xampp\php\php.exe
    ) ELSE (
        echo [ERROR] PHP tidak ditemukan di PATH maupun C:\xampp\php\php.exe!
        pause
        exit /b 1
    )
)

echo Menggunakan PHP: %PHP_BIN%
"%PHP_BIN%" vendor/bin/phpunit --colors=always --testdox

echo.
echo ========================================================
echo               PENGUJIAN SELESAI
echo ========================================================
