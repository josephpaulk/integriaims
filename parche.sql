alter table ttask modify id_project int(10) signed;
alter table ttask modify id int(10) signed;
alter table ttask modify id_task int(10) signed;
update ttask set id=-1 where where id = 1;
update ttask set id=-2 where where id = 2;
update ttask set id=-3 where where id = 3;
update ttask set id=-4 where where id = 4;
update ttask set id_project = -1 where id < 0;
update tproject set id = -1 where id = 0;
alter table tproject modify id int(10) signed;
alter tworkunit_task alter id_task int(10) signed; 

alter table tkb_data ADD `id_language` varchar(15) NOT NULL default '';