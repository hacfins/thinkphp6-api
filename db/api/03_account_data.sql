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




INSERT INTO `a_role` (`role_id`, `user_name`, `role_name`, `role_type`, `sort`, `update_time`,`create_time`) VALUES ('e4e638fa71cc41c5898d42f453dba534', 'admin', '普通用户', 2, 2, NOW(), NOW());
INSERT INTO `a_role` (`role_id`, `user_name`, `role_name`, `role_type`, `sort`, `update_time`,`create_time`) VALUES ('9630534592ed4b1981faef04218113f5', 'admin', '访客', 2, 1, NOW(), NOW());




INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('2ba9c39c866c47d5bb28764441619813', 'admin', 'api', 'auth.role', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('352ab6e3edb5496fa67bc27f78bf96b6', 'admin', 'api', 'auth.role', 'add', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('1627258d774a422c8932c61311643830', 'admin', 'api', 'auth.role', 'modify', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('b7385df8b99f4502bb995b7cb629b939', 'admin', 'api', 'auth.role', 'move_to', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('0385b5f8ef684a64b6487ea53fbdac41', 'admin', 'api', 'auth.role', 'exist_name', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('6d6f391eec444212acc47816b214b7e0', 'admin', 'api', 'auth.role', 'del', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('cb8f3a0ade5846d884b540d3057bf361', 'admin', 'api', 'auth.role', 'info', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('ac8b6f327fbe41428cccb81c8b21724f', 'admin', 'api', 'auth.role', 'get_list', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('0865809fa4074005ad35f18303f56d8c', 'admin', 'api', 'auth.auth', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('425dbfbd93674a009cd5a795b292bdb1', 'admin', 'api', 'auth.auth', 'refresh', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('3cf864ebf6cf4fd79cbdee4561483afc', 'admin', 'api', 'auth.auth', 'set_rules', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('ec5490279a7b48daa8334a918d80a141', 'admin', 'api', 'auth.auth', 'get_list', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('50579e5542e74d61a9ddff405f06fae4', 'admin', 'api', 'auth.auth', 'view_list', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('b3e005552b284c4da4d178e86f0b8629', 'admin', 'api', 'auth.userrole', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('898614dad73e4619aaad2ab3f538e8a9', 'admin', 'api', 'auth.userrole', 'modify', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('41fe4152e7764b7e9f3fb00410031002', 'admin', 'api', 'auth.userrole', 'get_list', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('21e36c8e458e47ce9d11c16e393297c0', 'admin', 'api', 'location.area', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('1e96b8eea522499d99929aad52e742fa', 'admin', 'api', 'location.area', 'getcitylistall', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('9b2bfc03afbf4e1099db8d95f54267f8', 'admin', 'api', 'location.area', 'getarealist', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('af46f0b1a3c6464f89988f89a2585156', 'admin', 'api', 'passport.common', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('c6860412bd604f1daea432e0c2495f5b', 'admin', 'api', 'passport.common', 'attach', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('4a9fa010697447b7971c226351b7b88c', 'admin', 'api', 'passport.common', 'existname', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('0f766a6d503a4571897a9763b08ca771', 'admin', 'api', 'passport.common', 'existemail', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('535ff9833b1e4d9a87e42978929f9bd6', 'admin', 'api', 'passport.common', 'existphone', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('38c75d889d914df99f77dbcc84976c9b', 'admin', 'api', 'passport.common', 'captcha', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('7f8423399d6e4d308c2d6f2c67bfd052', 'admin', 'api', 'passport.common', 'checkcaptcha', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('ab77a085ad5d43ec8ab2e7048736f36e', 'admin', 'api', 'passport.common', 'verify', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('7cdaef7a2de04370a0f20acb689df515', 'admin', 'api', 'passport.common', 'checkverify', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('ea5c398d0a3f43fcb18b152ea7f2da5a', 'admin', 'api', 'passport.user', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('b74b6f9c68624e0fa9e1560d67c9a9ca', 'admin', 'api', 'passport.user', 'login', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('4532d91a268e406d8a7597951b13a9f2', 'admin', 'api', 'passport.user', 'logout', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('b9dbcfd440814e0ea48cbab464916b7b', 'admin', 'api', 'passport.user', 'open_url', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('8576f2e1f6ff468cbe9254116480748d', 'admin', 'api', 'passport.user', 'register', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('b4a407450f9148af9cfcf17dc8febf97', 'admin', 'api', 'passport.user', 'register_email', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('6967476fb2b24e6abf8596401e30d02d', 'admin', 'api', 'passport.user', 'findpwd_email', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('375b8c4497684ee58503b3531ea70336', 'admin', 'api', 'passport.user', 'findpwd_phone', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('8d5615ac68fd4ebf9dda38a55db39db6', 'admin', 'api', 'passport.wxlogin', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('b7719bb5715b44bd9575adca18b3ee0f', 'admin', 'api', 'passport.wxlogin', 'jssdksign', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('161a75eaf6154fd1842044fe9f90aeb0', 'admin', 'api', 'passport.wxlogin', 'checkqrcode', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('7d231a5510f54389ae1f73d25e0bf7f6', 'admin', 'api', 'passport.wxlogin', 'bindlogin', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('e0da8ac190694a69be38ef006c73b7ff', 'admin', 'api', 'passport.wxlogin', 'register_bind', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('cb31fddea155405f8587d5ac6091d54e', 'admin', 'api', 'passport.wxlogin', 'delbind', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('0db1f90995bf4f30b034f9503c79877b', 'admin', 'api', 'passport.wxlogin', 'isbind', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('8bff6fb85db0407a955ff56cbf90e5f1', 'admin', 'api', 'passport.wxlogin', 'baseredirect', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('10c155146a234231ad7b78706fb1df10', 'admin', 'api', 'passport.wxlogin', 'baseinfo', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('728057d8b59540218e05b6d554cee083', 'admin', 'api', 'passport.wxlogin', 'userinfo', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('0346f5ce0b1246a1967b4f293cca3941', 'admin', 'api', 'report.userreport', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('dabe737f01d84fb8b6d41bfafbe7da76', 'admin', 'api', 'report.userreport', 'summary', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('f3cf065a62114fbaa2a71fa97b32a5fc', 'admin', 'api', 'report.userreport', 'statistic', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('8fa4e7c4562f4fdbabb6646da7768b50', 'admin', 'api', 'user.user', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('a3b37ba6cb204082b6f08f986e30e6d7', 'admin', 'api', 'user.user', 'modifyphone', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('4c9b5997944f44a988d1805ae52f2e93', 'admin', 'api', 'user.user', 'modifyemail', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('8ce5c3ebe98b4f8aadd4f342a86d6feb', 'admin', 'api', 'user.user', 'modifypwd', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('c0b07bf587bd42f4809ef63f54dbe3b9', 'admin', 'api', 'user.user', 'modifyinfo', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('16756d36b70e4032831f3519eb5d8c9b', 'admin', 'api', 'user.user', 'uploadavator', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('40e7d91b4d49450ab851186cda12740e', 'admin', 'api', 'user.user', 'saveavator', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('6556322174ed4c44b39f5e3792ad00a2', 'admin', 'api', 'user.user', 'info', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('4db94070b7e245e68426b83ce087ee24', 'admin', 'api', 'user.user', 'others_info', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('20b03a8709ab45b19bd85d14aa57a6cf', 'admin', 'api', 'user.user', 'info_ex', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('c6bedef03aae422cacf984a95e3dc9e9', 'admin', 'api', 'user.message', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('1dd2b252c9ea497198ea817498665357', 'admin', 'api', 'user.message', 'add', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('cca1275c916a4e96b0d085737d942191', 'admin', 'api', 'user.message', 'clear', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('c743682ffa3f450ca7b8ccc6d1110819', 'admin', 'api', 'user.message', 'dels', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('2fd41e245a594aadb1d3f4cac634ae31', 'admin', 'api', 'user.message', 'mark', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('41423c80ae044fa3b6c4113159d18c83', 'admin', 'api', 'user.message', 'mark_all', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('9b2f38e98dae43d2aeebc92a54f721a7', 'admin', 'api', 'user.message', 'to_me', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('503c5e33630e45f29a0ca3b2b30c449a', 'admin', 'api', 'user.message', 'get_count', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('9777f2be5a8a4dd3a35bdbacd20064f0', 'admin', 'api', 'user.relation', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('8e4869cd78234daaa4d80201fb135b33', 'admin', 'api', 'user.relation', 'follows', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('4eba6570671f49fb91004dd6f3c2dca0', 'admin', 'api', 'user.relation', 'follow', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('8127c160ea1b49ffb1bd647ff74b1202', 'admin', 'api', 'user.relation', 'abrogate', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('38417dbe39674449b17755ec439ce136', 'admin', 'api', 'user.userlog', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('647736a27e40437982d873978b32fcf3', 'admin', 'api', 'user.userlog', 'getlist', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('ad09162ddaec43dcbf61be5a9884525d', 'admin', 'api', 'user.usermanage', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('5a9eb7b340994f0d8544565ef83119f7', 'admin', 'api', 'user.usermanage', 'add', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('a387f057894b481ba66522dbee530a1f', 'admin', 'api', 'user.usermanage', 'enabled', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('dd14b295bb23454ba96c55de0384d173', 'admin', 'api', 'user.usermanage', 'getlist', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('cd1d82d2dfce4236a5a265237e41d822', 'admin', 'api', 'user.usermanage', 'batch_download', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('f8254ada75fc4ab48c889d4ddce84d38', 'admin', 'api', 'user.usermanage', 'batch_import', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('8f409d36828c4af3b39c88fd79d60fca', 'admin', 'api', 'user.usermanage', 'batch_report', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('57bf2002df494be9a78cb58933c1325f', 'admin', 'api', 'website.access', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('2cf9dbab97fb4b44a2216c439649888c', 'admin', 'api', 'website.access', 'info', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`,`create_time`) VALUES ('2a9e006ea5564f48b85a5b632be340a2', 'admin', 'api', 'website.access', 'modify', NOW(), NOW());

INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('063a5b1b6712469d8038b454fafacd1b', 'admin', 'api', 'user.userlog', 'get_list_admin', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('c392725fbaa64a3fa5dfb53ad1227d37', 'admin', 'api', 'user.userlog', 'index_user', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('7f0c44cef53a4974bc6143ce87d4f2a7', 'admin', 'api', 'user.userlog', 'info', NOW(), NOW());

INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('de8daff861794ec2b2a274b43c34739f', 'admin', 'api', 'website.video', 'index', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('c1640975301f4e498adbe8e24fa8ff32', 'admin', 'api', 'website.video', 'upload_img', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('7256ec545b124d4298cd763b5914cc19', 'admin', 'api', 'website.video', 'info', NOW(), NOW());
INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('84e7ba647e944361a557a8f5492d3681', 'admin', 'api', 'website.video', 'modify', NOW(), NOW());

INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('631b5c2b722d4887833ce0460d818f6f', 'admin', 'api', 'report.userreport', 'stat_provide', NOW(), NOW());

INSERT INTO `a_rule` (`rule_id`, `user_name`, `module`, `control`, `method`,  `update_time`, `create_time`) VALUES ('5488fcbd5250461090ac28e6396915c8', 'admin', 'api', 'user.user', 'modifyinfo_admin', NOW(), NOW());




INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('b0e62407b8f04275ac742fde3c11ea0c', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '50579e5542e74d61a9ddff405f06fae4', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('5742147eb83a418dbf8c27dbff63bc3c', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '21e36c8e458e47ce9d11c16e393297c0', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('be9b7aa6590b4988875bbb65dda32b1a', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '1e96b8eea522499d99929aad52e742fa', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('bc5b27170b6b4359ac7f9c649d11f94d', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '9b2bfc03afbf4e1099db8d95f54267f8', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('d0735558fb52444ab2c789f60e1c3424', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'af46f0b1a3c6464f89988f89a2585156', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('119f8d2a26104d26a7afc74897b2e925', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'c6860412bd604f1daea432e0c2495f5b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('d213759759474554998383711745488b', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '4a9fa010697447b7971c226351b7b88c', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('5a99b6c51dc94303839ee810f7cb7570', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '0f766a6d503a4571897a9763b08ca771', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('df147b61feb24ebeba9c27078504f12d', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '535ff9833b1e4d9a87e42978929f9bd6', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('0e7812ae810741faab68de8067f31823', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '38c75d889d914df99f77dbcc84976c9b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('42278849292b4914abbd03ce3d0ce425', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '7f8423399d6e4d308c2d6f2c67bfd052', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('f1b09a0ebd064a9ba919c2a190a191fb', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'ab77a085ad5d43ec8ab2e7048736f36e', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('b17ad76ae4344fdba90b896f98d774ed', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '7cdaef7a2de04370a0f20acb689df515', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('de41bcd0fbdc4b5db1cffe8f9fc52f00', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'ea5c398d0a3f43fcb18b152ea7f2da5a', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('82bf0e7a4c78456291ccc646165e7087', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'b74b6f9c68624e0fa9e1560d67c9a9ca', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('28f67d33feeb41008d5a1f19048362de', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '4532d91a268e406d8a7597951b13a9f2', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('68d3046a9e1e423ebc14452d074ca166', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'b9dbcfd440814e0ea48cbab464916b7b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('44cd20ff769f4f14ba64eeaa84ee3126', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '8576f2e1f6ff468cbe9254116480748d', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('b14eab7bf3504bf5adf6e2704f0d5c62', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'b4a407450f9148af9cfcf17dc8febf97', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('7a8c3b74fae8499791f43050d328d4a1', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '6967476fb2b24e6abf8596401e30d02d', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('d5f089bb408a48caa815936a25ef2b21', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '375b8c4497684ee58503b3531ea70336', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('0294919a57e84c4abf0eaf63198daf93', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '8d5615ac68fd4ebf9dda38a55db39db6', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('c8bf7fbd7fbe4695b963336705fa3f94', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'b7719bb5715b44bd9575adca18b3ee0f', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('4290c1544a7342dda383f20f15057463', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '161a75eaf6154fd1842044fe9f90aeb0', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('1e4cd9ffbe384ccc97b686f506f1de62', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '7d231a5510f54389ae1f73d25e0bf7f6', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('c8a4eddbe7ca454eb3a6fa98dc23ba7e', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'e0da8ac190694a69be38ef006c73b7ff', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('b801081300754f61986e7578a7f7be33', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'cb31fddea155405f8587d5ac6091d54e', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('3cf62f9cd76f48498222e41230e5248d', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '0db1f90995bf4f30b034f9503c79877b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('b11fc9da21d24237b1c24a65704c6e73', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '8bff6fb85db0407a955ff56cbf90e5f1', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('48ef8e8f8bff47dca317480e17d8fb6e', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '10c155146a234231ad7b78706fb1df10', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('6e8ebe433dd04712b265b01a7521e9ee', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '728057d8b59540218e05b6d554cee083', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('3d89762cba5946889ddc37540031ff7c', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'c6bedef03aae422cacf984a95e3dc9e9', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('aa03a1f3dd2b43ddaa20904f049f6f85', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '1dd2b252c9ea497198ea817498665357', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('16a5967a6e0843019850699c11b0dab9', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'cca1275c916a4e96b0d085737d942191', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('d3915b1ebe74420a8be19af3b6b08da6', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'c743682ffa3f450ca7b8ccc6d1110819', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('c5051db788c9447786566158172e7985', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '2fd41e245a594aadb1d3f4cac634ae31', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('6cea78e3bd7a413eacf453bf7e0de456', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '41423c80ae044fa3b6c4113159d18c83', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('f0bc69d4087e46aab46774ce53ed96ae', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '9b2f38e98dae43d2aeebc92a54f721a7', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('b63fc34482ae40ec898617756d7753f8', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '503c5e33630e45f29a0ca3b2b30c449a', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('d832f2eed7cc4c7883d6e8b317a7f8bb', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '9777f2be5a8a4dd3a35bdbacd20064f0', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('efb4701702a94191b54bb9d7dae39bd6', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '8e4869cd78234daaa4d80201fb135b33', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('c9048d8a000c46d18cab79166bd4fd34', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '4eba6570671f49fb91004dd6f3c2dca0', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('5a65d86d8d6241c79c2d7f550a558002', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '8127c160ea1b49ffb1bd647ff74b1202', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('fe7854d4b11341df8db70802b874761f', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '8fa4e7c4562f4fdbabb6646da7768b50', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('3c622fab2f0e4fed9c820ba5235788cf', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'a3b37ba6cb204082b6f08f986e30e6d7', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('612d2d6edfa74f5086d0bf68fcf39afa', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '4c9b5997944f44a988d1805ae52f2e93', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('3bef0a1319ae4f2684af67ef4f392812', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '8ce5c3ebe98b4f8aadd4f342a86d6feb', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('bca27b75d72c4a5aa9b6300b21ddc2db', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'c0b07bf587bd42f4809ef63f54dbe3b9', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('959eea651b144570916e34533f3ab9f1', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '16756d36b70e4032831f3519eb5d8c9b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('ebb4d716f8a4445d990897bf4e3dc0b8', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '40e7d91b4d49450ab851186cda12740e', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('c3aa7bb4bd1b42d3963c850f33bef01c', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '6556322174ed4c44b39f5e3792ad00a2', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('cf17e9e3024e4e23ba130d5844375000', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '4db94070b7e245e68426b83ce087ee24', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('6a808df5f2a24507998d8e17f0ffa423', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '20b03a8709ab45b19bd85d14aa57a6cf', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('32d6e5af844d4581bb9141cbdb453ca9', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '647736a27e40437982d873978b32fcf3', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('a735162ea7cb4121bde5b27bb9ff45b5', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '2cf9dbab97fb4b44a2216c439649888c', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('3446373aa9a04083ae212e67328c7cc7', 'admin', 'e4e638fa71cc41c5898d42f453dba534', 'c392725fbaa64a3fa5dfb53ad1227d37', NOW(), NOW());

INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('3905f562a6544b20b904109b065036cf', 'admin', '9630534592ed4b1981faef04218113f5', '50579e5542e74d61a9ddff405f06fae4', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('cd37d011241042cf965cfabb3632cf77', 'admin', '9630534592ed4b1981faef04218113f5', '21e36c8e458e47ce9d11c16e393297c0', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('b18dc02dcef8437fba917e2d90ab2ca0', 'admin', '9630534592ed4b1981faef04218113f5', '1e96b8eea522499d99929aad52e742fa', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('6a86554865c3452fbf02cb216bc42117', 'admin', '9630534592ed4b1981faef04218113f5', '9b2bfc03afbf4e1099db8d95f54267f8', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('571b7a3d679c4c4eb43a466ba36fdccb', 'admin', '9630534592ed4b1981faef04218113f5', 'af46f0b1a3c6464f89988f89a2585156', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('9ca525eb0d4a468fac66a27cfc51717c', 'admin', '9630534592ed4b1981faef04218113f5', 'c6860412bd604f1daea432e0c2495f5b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('50e46065e3c841eebb67bffe0f607fe8', 'admin', '9630534592ed4b1981faef04218113f5', '4a9fa010697447b7971c226351b7b88c', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('68b6a20d41c94a6abfbccf3d87f992ab', 'admin', '9630534592ed4b1981faef04218113f5', '0f766a6d503a4571897a9763b08ca771', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('e928384d0c124075acdaf39be0f89302', 'admin', '9630534592ed4b1981faef04218113f5', '535ff9833b1e4d9a87e42978929f9bd6', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('543101df6b224c8a93b4706eae7e7abd', 'admin', '9630534592ed4b1981faef04218113f5', '38c75d889d914df99f77dbcc84976c9b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('a8ee9501914c4886b1d988a01256be96', 'admin', '9630534592ed4b1981faef04218113f5', '7f8423399d6e4d308c2d6f2c67bfd052', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('3ec648ad36694f0ea3eec55719a0d609', 'admin', '9630534592ed4b1981faef04218113f5', 'ab77a085ad5d43ec8ab2e7048736f36e', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('e2a062f2020e40f9aa9b75cc29d20188', 'admin', '9630534592ed4b1981faef04218113f5', '7cdaef7a2de04370a0f20acb689df515', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('9f31938b02de41689118d84e3108effd', 'admin', '9630534592ed4b1981faef04218113f5', 'ea5c398d0a3f43fcb18b152ea7f2da5a', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('5b59d89862ab484b9a8e2c36d468e437', 'admin', '9630534592ed4b1981faef04218113f5', 'b74b6f9c68624e0fa9e1560d67c9a9ca', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('8eeb049bdf0f496f9256c393d339fd71', 'admin', '9630534592ed4b1981faef04218113f5', '4532d91a268e406d8a7597951b13a9f2', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('d0cc0b04c7c74d38b1c4366c408e3dd5', 'admin', '9630534592ed4b1981faef04218113f5', 'b9dbcfd440814e0ea48cbab464916b7b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('d61e1a78ff254f90a2a2d2b20216ece4', 'admin', '9630534592ed4b1981faef04218113f5', '8576f2e1f6ff468cbe9254116480748d', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('e80e99d965c04c21bb99121de0d084fb', 'admin', '9630534592ed4b1981faef04218113f5', 'b4a407450f9148af9cfcf17dc8febf97', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('3e5c63a14a3548a1aac964108fb7fdf7', 'admin', '9630534592ed4b1981faef04218113f5', '6967476fb2b24e6abf8596401e30d02d', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('0b104feb983c4cae88c85869a35b0a18', 'admin', '9630534592ed4b1981faef04218113f5', '375b8c4497684ee58503b3531ea70336', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('9488fa35ab9d433fbe2ee78cea21cc28', 'admin', '9630534592ed4b1981faef04218113f5', '8d5615ac68fd4ebf9dda38a55db39db6', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('becd9d2313c94792bcf8a0cac8b7f070', 'admin', '9630534592ed4b1981faef04218113f5', 'b7719bb5715b44bd9575adca18b3ee0f', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('63ad19faa86e4812b97a6a1541ea428e', 'admin', '9630534592ed4b1981faef04218113f5', '161a75eaf6154fd1842044fe9f90aeb0', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('42db1c67319247b8bddbc0945eff0a50', 'admin', '9630534592ed4b1981faef04218113f5', '7d231a5510f54389ae1f73d25e0bf7f6', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('f1931d5f3bf04696bcf40ff4727de9fa', 'admin', '9630534592ed4b1981faef04218113f5', 'e0da8ac190694a69be38ef006c73b7ff', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('06b877b7747c4ed5b933cb00b5d2c7de', 'admin', '9630534592ed4b1981faef04218113f5', 'cb31fddea155405f8587d5ac6091d54e', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('df0e8e78965441fca6b402d040ec1af7', 'admin', '9630534592ed4b1981faef04218113f5', '0db1f90995bf4f30b034f9503c79877b', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('881c5223eac74cab8f2ed079a3d20069', 'admin', '9630534592ed4b1981faef04218113f5', '8bff6fb85db0407a955ff56cbf90e5f1', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('7e31578d993a47c9b61afd72bad38ce4', 'admin', '9630534592ed4b1981faef04218113f5', '10c155146a234231ad7b78706fb1df10', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('bf7f8e2ce75a43569214ff47eb28e45f', 'admin', '9630534592ed4b1981faef04218113f5', '728057d8b59540218e05b6d554cee083', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('256ca9cd5c064d8aac9eda7a1045bdb8', 'admin', '9630534592ed4b1981faef04218113f5', '503c5e33630e45f29a0ca3b2b30c449a', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('c3128858b8e34f618f7c03e7730ff690', 'admin', '9630534592ed4b1981faef04218113f5', '8e4869cd78234daaa4d80201fb135b33', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('5b1b78c5b0764418a58522dccb0c07d9', 'admin', '9630534592ed4b1981faef04218113f5', '6556322174ed4c44b39f5e3792ad00a2', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('15f213fde9b54a5a881445ec5a6c1555', 'admin', '9630534592ed4b1981faef04218113f5', '4db94070b7e245e68426b83ce087ee24', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('84b64038de5e4003ac9ed09704f064bf', 'admin', '9630534592ed4b1981faef04218113f5', '2cf9dbab97fb4b44a2216c439649888c', NOW(), NOW());

INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('fe47df77aaf946cb94b23492ca4f7912', 'admin', '9630534592ed4b1981faef04218113f5', '7256ec545b124d4298cd763b5914cc19', NOW(), NOW());
INSERT INTO `a_role_rules` (`rr_id`,`user_name`, `role_id`, `rule_id`,`update_time`,`create_time`) VALUES ('21bc323e37a9435a876246eb8800e819', 'admin', 'e4e638fa71cc41c5898d42f453dba534', '7256ec545b124d4298cd763b5914cc19', NOW(), NOW());
