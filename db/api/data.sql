use hc_account;

/*==============================================================*/
/* Table: a_user                                                */
/*==============================================================*/
INSERT INTO `a_user` (
	`user_name`,
	`nick_name`,
	`full_name`,	
	`status`,
	`update_time`,
	`delete_time`,
	`create_time`
)
VALUES
	(
		'admin',
		'管理员',
		'管理员',		
		1,
		NOW(),
		NULL,
		NOW()
	);


/*==============================================================*/
/* Table: a_user_auth                                           */
/*==============================================================*/
INSERT INTO `a_user_auth` (	
	`user_name`,
	`pwd`,
	`update_time`,
	`delete_time`,
	`create_time`
)
VALUES
	(
		'admin',
		'0bc9e18bad80480daeb11d19b6660533',	
		NOW(),
		NULL,
		NOW()
	); 


/*==============================================================*/
/* Table: a_report_user                                         */
/*==============================================================*/
INSERT INTO `a_report_user` (	
	`rep_id`,
	`rep_date`,
	`users`,
	`logins_pc`,
	`logins_mobile`,
	`update_time`,
	`create_time`
)
VALUES
	(	
	    'e21984f2cf5645e69db2c6efd5d9fc20',
		NOW(),
		'1',
		'0',
		'0',
		NOW(),
		NOW()
	);

/*==============================================================*/
/* Table: a_conf                                                */
/*==============================================================*/
INSERT INTO `a_conf` (
	`conf_key`,
	`conf_value`,
	`update_time`,
	`delete_time`,
	`create_time`
)
VALUES
	(
		'switch',
		'',
		NOW(),
		NULL,
		NOW()
	);

INSERT INTO `a_conf` (
	`conf_key`,
	`conf_value`,
	`update_time`,
	`delete_time`,
	`create_time`
)
VALUES
	(
		'video',
		'{"water_mark":2,"img_path":"","fill":1,"finger_mark":1}',
		NOW(),
		NULL,
		NOW()
	);


/*==============================================================*/
/* Table: a_user_stat                                           */
/*==============================================================*/
INSERT INTO a_user_stat (
	user_name,
	update_time,
	create_time
) SELECT
	user_name,
	NOW(),
	NOW()
FROM
	a_user
ORDER BY
	id;

/*==============================================================*/
/* Table: a_area_area                                           */
/*==============================================================*/
ALTER TABLE a_area_area ADD `delete_time` datetime DEFAULT NULL COMMENT '删除时间';

/*==============================================================*/
/* Table: a_area_city                                           */
/*==============================================================*/
ALTER TABLE a_area_city ADD `delete_time` datetime DEFAULT NULL COMMENT '删除时间';

/*==============================================================*/
/* Table: a_area_province                                           */
/*==============================================================*/
ALTER TABLE a_area_province ADD `delete_time` datetime DEFAULT NULL COMMENT '删除时间';
