# from the command prompt - the password may be root, I believe you had to set it during script install.
mysql -u root -prachel

#if above didn't work
mysql -u root -proot

#you will enter mysql
CREATE DATABASE sphider_plus;

#confirm database exists
SHOW DATABASES;
exit


#IMPORT MYSQL DATABASE, AFTER LEAVING MYSQL
mysql -u root -prachel sphider_plus < /home/pi/sphider_plus.sql 