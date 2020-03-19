use mysql;
update user set host='%' where user = 'root' and host='::1'; 
UPDATE user SET `Password`=PASSWORD('daemon') WHERE user='root'; 
