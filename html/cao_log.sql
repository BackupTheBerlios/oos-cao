CREATE TABLE cao_log (
  id int(11) NOT NULL auto_increment,
  date datetime NOT NULL default '0000-00-00 00:00:00',
  user varchar(64) NOT NULL default '',
  pw varchar(64) NOT NULL default '',
  method varchar(64) NOT NULL default '',
  action varchar(64) NOT NULL default '',
  post_data mediumtext,
  get_data mediumtext,
  PRIMARY KEY  (id)
) TYPE=MyISAM