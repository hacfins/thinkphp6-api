/*==============================================================*/
/* Database name:  hc_account                                   */
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     2019-08-21 17:48:40                          */
/*==============================================================*/


/*==============================================================*/
/* Database: hc_account                                         */
/*==============================================================*/
create database hc_common;

use hc_common;

/*==============================================================*/
/* Table: a_conf                                                */
/*==============================================================*/
create table a_conf
(
   id                   int unsigned not null auto_increment,
   conf_key             varchar(16) not null comment '配置键',
   conf_value           text not null default '' comment '配置值',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_conf comment '站点配置';

/*==============================================================*/
/* Table: a_log_details                                         */
/*==============================================================*/
create table a_log_details
(
   id                   int unsigned not null auto_increment,
   opd_id               char(32) not null comment '详情id号',
   op_id                char(32) not null comment '操作id号',
   opd_table            varchar(24) not null default '' comment '操作表',
   opd_key              varchar(32) not null default '' comment '表的逻辑id号',
   opd_diff             text not null default '' comment '修改说明（例如将xx改为xx.....）',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_log_details comment '用户操作日志详情表';

/*==============================================================*/
/* Table: a_logs                                                */
/*==============================================================*/
create table a_logs
(
   id                   int unsigned not null auto_increment,
   op_id                char(32) not null comment '操作id号',
   user_name            varchar(20) not null comment '用户名',
   op                   varchar(24) not null default '' comment '操作（例如修改课表）',
   op_type              tinyint not null default 1 comment '操作类型（如增删改）',
   op_url               varchar(32) not null default '' comment '操作的url（请求的URL地址）',
   op_params            text not null default '' comment '操作的参数',
   op_result            int not null default 1 comment '操作结果（操作成功/操作失败/操作异常等）',
   op_comment           varchar(255) not null default '' comment '操作说明',
   use_time             int not null default 0 comment '耗时（微秒）',
   use_io               int not null default 0 comment '吞吐量',
   use_mem              varchar(16) not null default '0 kb' comment '内存消耗（kb）',
   os_name              varchar(24) not null default '' comment '系统名称（Windows XP, MacOS 10）',
   os_family            varchar(16) not null default '' comment '系统家族（ like Linux, Windows, MacOS）',
   os_version           varchar(16) not null default '' comment '系统版本（ like XP, Vista, 10）',
   device_model         varchar(16) not null default '' comment '设备模型（like iPad, iPhone, Nexus）',
   city                 varchar(16) default '' comment '操作城市（济南）',
   ip                   int unsigned not null default 0 comment '操作ip',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '操作时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_logs comment '用户操作日志表';

/*==============================================================*/
/* Table: a_message                                             */
/*==============================================================*/
create table a_message
(
   id                   int unsigned not null auto_increment,
   msg_id               char(32) not null comment '消息id号',
   user_name            varchar(20) not null comment '创建人',
   to_user_name         varchar(20) not null default '' comment '接收消息的用户',
   event_id             char(32) not null default '' comment '事件id号（消息触发者）',
   queue                smallint not null comment '消息类型（评论、关注、点赞、活动、通知等）',
   pd                   varchar(20) not null default '' comment '消息主体名称(学院、公开课、系统、粉丝)',
   title                text not null default '' comment '消息标题',
   content              text not null default '' comment '消息内容(没有时，为空)',
   status               tinyint not null default 1 comment '未读、已读',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_message comment '消息通知';

/*==============================================================*/
/* Table: a_relationships                                       */
/*==============================================================*/
create table a_relationships
(
   id                   int unsigned not null auto_increment,
   rs_id                char(32) not null comment '逻辑id号',
   user_name            varchar(20) not null comment '用户名',
   flows_name           varchar(20) not null comment 'user_name 关注了 flows_name',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除标记',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_relationships comment '关注粉丝表';

/*==============================================================*/
/* Table: a_report_user                                         */
/*==============================================================*/
create table a_report_user
(
   id                   int unsigned not null auto_increment,
   rep_id               char(32) not null comment '统计id号',
   rep_date             date not null comment '统计日期',
   users                int not null default 0 comment '新增用户数',
   logins_pc            int not null default 0 comment 'PC端登录数',
   logins_mobile        int not null comment '手持端登录数',
   update_time          datetime not null,
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_report_user comment '用户报表';

/*==============================================================*/
/* Table: a_role                                                */
/*==============================================================*/
create table a_role
(
   id                   int unsigned not null auto_increment,
   role_id              char(32) not null comment '角色id号',
   user_name            varchar(20) not null comment '用户名',
   role_name            varchar(20) not null comment '角色名称',
   role_type            tinyint not null default 1 comment '角色类型（系统角色（不可删除）、普通角色等）',
   sort                 smallint not null default 99 comment '排序字段',
   description          varchar(128) not null default '' comment '描述信息',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除标记',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_role comment '角色表';

/*==============================================================*/
/* Table: a_role_rules                                          */
/*==============================================================*/
create table a_role_rules
(
   id                   int unsigned not null auto_increment,
   rr_id                char(32) not null comment '逻辑id号',
   user_name            varchar(20) not null comment '用户名',
   role_id              char(32) not null comment '角色id号',
   rule_id              char(32) not null comment '权限id号',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_role_rules comment '角色权限映射表';

/*==============================================================*/
/* Table: a_rule                                                */
/*==============================================================*/
create table a_rule
(
   id                   int unsigned not null auto_increment,
   rule_id              char(32) not null comment '权限id号',
   user_name            varchar(20) not null comment '用户名',
   module               varchar(16) not null comment '模块组',
   control              varchar(24) not null comment '控制',
   method               varchar(24) not null comment '方法',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除标记',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_rule comment '权限表';

/*==============================================================*/
/* Table: a_user                                                */
/*==============================================================*/
create table a_user
(
   id                   int unsigned not null auto_increment,
   user_name            varchar(20) not null comment '用户名',
   nick_name            varchar(20) not null default '' comment '昵称',
   full_name            varchar(20) not null default '' comment '姓名',
   sex                  tinyint not null default 0 comment '性别',
   avator               varchar(255) not null default '' comment '用户头像',
   adcode               int not null default 0 comment '行政区划代码',
   company              varchar(40) not null default '' comment '机构名称',
   birthday             date not null default '1970-01-01' comment '生日',
   description          varchar(128) not null default '' comment '描述信息',
   reg_ip               int unsigned not null default 0 comment '注册IP',
   status               tinyint not null default 2 comment '状态',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_user comment '用户信息表';

/*==============================================================*/
/* Table: a_user_auth                                           */
/*==============================================================*/
create table a_user_auth
(
   id                   int unsigned not null auto_increment,
   user_name            varchar(20) not null comment '用户名',
   pwd                  varchar(255) not null comment '密码',
   phone                char(15) not null default '' comment '手机号码',
   email                varchar(32) not null default '' comment '邮箱',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_user_auth comment '本地授权信息表';

/*==============================================================*/
/* Table: a_user_logs                                           */
/*==============================================================*/
create table a_user_logs
(
   id                   int unsigned not null auto_increment,
   op_id                char(32) not null comment '操作id号',
   user_name            varchar(20) not null comment '用户名',
   op_type              tinyint not null default 1 comment '操作类型',
   tb_id                char(32) not null default '' comment '表id号',
   tb_name              varchar(24) not null default '' comment '表名',
   os_name              varchar(24) not null default '' comment '系统名称（Windows XP, MacOS 10）',
   os_family            varchar(16) not null default '' comment '系统家族（ like Linux, Windows, MacOS）',
   os_version           varchar(16) not null default '' comment '系统版本（ like XP, Vista, 10）',
   device_model         varchar(16) not null default '' comment '设备模型（like iPad, iPhone, Nexus）',
   city                 varchar(16) default '' comment '操作城市（济南）',
   ip                   int unsigned not null default 0 comment '操作ip',
   description          varchar(24) not null default '' comment '备注',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '登录时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_user_logs comment '用户操作日志表';

/*==============================================================*/
/* Table: a_user_oauths                                         */
/*==============================================================*/
create table a_user_oauths
(
   id                   int unsigned not null auto_increment,
   oauth_id             char(32) not null comment '第三方应用的唯一标识',
   user_name            varchar(20) not null comment '用户名',
   oauth_type           tinyint not null default 1 comment '第三方应用类型',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_user_oauths comment '第三方授权信息表';

/*==============================================================*/
/* Table: a_user_roles                                          */
/*==============================================================*/
create table a_user_roles
(
   id                   int unsigned not null auto_increment,
   uro_id               char(32) not null comment '逻辑id号',
   user_name            varchar(20) not null comment '用户名',
   role_id              char(32) not null comment '角色id号',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除时间',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_user_roles comment '用户角色映射表';

/*==============================================================*/
/* Table: a_user_stat                                           */
/*==============================================================*/
create table a_user_stat
(
   id                   int unsigned not null auto_increment,
   user_name            varchar(20) not null comment '用户名',
   followers            int not null default 0 comment '关注数',
   following            int not null default 0 comment '粉丝数',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime comment '删除标记',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_user_stat comment '用户统计表';

/*==============================================================*/
/* Table: a_user_tokens                                         */
/*==============================================================*/
create table a_user_tokens
(
   id                   int unsigned not null auto_increment,
   token_id             char(32) not null comment '授权的token',
   user_name            varchar(20) not null comment '用户名',
   expire               datetime not null comment '过期时间',
   os_type              tinyint not null default 1 comment '登录平台类型（PC\Mobile\平板）',
   status               tinyint not null default 1 comment '状态',
   update_time          datetime not null comment '更新时间',
   delete_time          datetime default NULL comment '删除标记',
   create_time          datetime not null comment '创建时间',
   primary key (id)
)
engine = InnoDB
default charset = utf8
collate = utf8_general_ci;

alter table a_user_tokens comment '登录信息表';

