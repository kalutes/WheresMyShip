 mysql_config_editor set --login-path=local --host=localhost --user=userfrosting_adm --password
 if [ $(mysql --login-path=local -N -s -e "select count(*) from information_schema.tables where \
        table_schema='userfrosting' and table_name='uf_shipments';") -eq 1 ]; then
    echo "table already exists"
else
    mysql --login-path=local -e "CREATE TABLE userfrosting.uf_shipments (id int NOT NULL AUTO_INCREMENT, userid int, trackingNumber varchar(50),shipDate int, origin varchar(1000),destination varchar(1000), currentLocation varchar(1000), eta int, PRIMARY KEY (id));"
fi
VAR=$(mysql --login-path=local -N -s  -e "select * from information_schema.COLUMNS where table_schema='userfrosting' and table_name='uf_user' and column_name='googleauth'")
if [ -z "$VAR" ]; then
 mysql --login-path=local -e "alter table userfrosting.uf_user add googleauth text not null;"
else
	echo "column already exists"
fi
VAR=$(mysql --login-path=local -N -s  -e "select * from information_schema.COLUMNS where table_schema='userfrosting' and table_name='uf_user' and column_name='firstgrab'")
if [ -z "$VAR" ]; then
 mysql --login-path=local -e "alter table userfrosting.uf_user add firstgrab int(1) not null default 1;"
else
	echo "column already exists"
fi
VAR=$(mysql --login-path=local -N -s -e "select * from information_schema.COLUMNS where table_schema='userfrosting' and table_name='uf_user' and column_name='lastemaildate'")
if [ -z "$VAR" ]; then
 mysql --login-path=local -e "alter table userfrosting.uf_user add lastemaildate int(30) not null default 0;"
else
	echo "column already exists"
fi
