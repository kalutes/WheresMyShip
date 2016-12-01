mysql -u 'userfrosting_adm' --password='wheresmyship' << EOF
use userfrosting;

create table uf_shipments(id int NOT NULL auto_increment, userid int, trackingNumber varchar(50), primary key(id));

