create table sys_groups(
  group_id int unsigned not null auto_increment primary key,
  `group` varchar(45) not null unique key
);
insert into sys_groups(`group`) values('Everyone'),('Authenticated users'),('Administrators');

create table sys_resources(
  resource_id int unsigned not null auto_increment primary key,
  resource varchar(100) not null unique key
);

create table sys_perms(
  perm_id int unsigned not null auto_increment primary key,
  user_id int unsigned,
  group_id int unsigned,
  resource_id int unsigned not null,
  allow tinyint unsigned not null default 1,
  r tinyint unsigned not null default 0,
  w tinyint unsigned not null default 0,
  o tinyint unsigned not null default 0,
  foreign key (group_id) references sys_groups(group_id),
  foreign key (resource_id) references sys_resources(resource_id)
);

create table sys_usergroup(
  usergroup_id int unsigned not null auto_increment primary key,
  user_id int unsigned not null,
  group_id int unsigned not null,
  foreign key (group_id) references sys_groups(group_id)
);