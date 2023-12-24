rem DEVELOPMENT ENVIRONMENT WRAPPER

rem Start XAMPP
rem start /D "D:\Program Files 64\Xampp" xampp_start.exe

rem Start Apache
rem start /D "D:\Program Files 64\Xampp\apache\bin\" httpd.exe

rem Start MySQL
rem start /D "D:\Program Files 64\Xampp\mysql\bin\" mysql.exe

rem Start Microsoft Edge
start microsoft-edge:http://localhost:8000

rem Start Laravel
php artisan serve --host 127.0.0.1 --port 8000
