@echo off

title PHP Server and Queue Worker

for %%I in (.) do set cur_dir=%%~nxI

if "%cur_dir%"=="bin" (
	cd ../..
)

:: Lancer la commande 'php artisan queue:work' en arrière-plan avec un titre "Queue Worker"
start /b .\app\bin\queue-worker.bat

:: Lancer la commande 'php artisan serve' en arrière-plan avec un titre "PHP Server"
start /b .\app\bin\server.bat

exit
