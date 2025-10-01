@echo off
REM Run all multi-tenant tests

echo ================================
echo Running Multi-Tenant Test Suite
echo ================================
echo.

echo 1. Running Policy Tests...
php artisan test tests/Unit/Policies --compact

echo.
echo 2. Running Helper Function Tests...
php artisan test tests/Unit/HelpersTest.php --compact

echo.
echo 3. Running Data Isolation Tests...
php artisan test tests/Feature/MultiTenant/DataIsolationTest.php --compact

echo.
echo 4. Running Authorization Tests...
php artisan test tests/Feature/MultiTenant/AuthorizationTest.php --compact

echo.
echo ================================
echo All Multi-Tenant Tests Complete!
echo ================================
pause
