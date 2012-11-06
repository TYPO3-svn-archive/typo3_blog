#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_typo3blog_author tinytext NOT NULL,
	tx_typo3blog_allow_comments tinyint(3) DEFAULT '0' NOT NULL,
	tx_typo3blog_tags tinytext,
	tx_typo3blog_create_datetime int(11) unsigned NOT NULL default '0',
	tx_typo3blog_exclude_page tinyint(3) DEFAULT '0' NOT NULL,
	tx_typo3blog_blogrolls text
);

#
# Table structure for table 'pages'
#
CREATE TABLE pages_language_overlay (
	tx_typo3blog_author tinytext NOT NULL,
	tx_typo3blog_allow_comments tinyint(3) DEFAULT '0' NOT NULL,
	tx_typo3blog_tags tinytext,
	tx_typo3blog_create_datetime int(11) unsigned NOT NULL default '0',
	tx_typo3blog_exclude_page tinyint(3) DEFAULT '0' NOT NULL,
	tx_typo3blog_blogrolls text
);

#
# Table structure for table 'tx_typo3blog_blogroll'
#
CREATE TABLE tx_typo3blog_blogroll (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(30) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,
	t3_origuid int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	fe_group int(11) DEFAULT '0' NOT NULL,
	name tinytext,
	title tinytext,
	link varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);
