mysql -u 'userfrosting_adm' --password='wheresmyship' << EOF
use userfrosting;

create table uf_shipments(id int NOT NULL auto_increment, userid int, trackingNumber varchar(50), primary key(id));
alter table uf_user add googleauth varchar(1000);
alter table uf_user add firstgrab int(1) not null default 1;
alter table uf_user add lastemaildate int(30) not null default 0;
