@echo off
cd C:\laragon\www\puskesmas-antrian
php spark queue:generate
echo [%date% %time%] Queue generation completed >> generate-log.txt