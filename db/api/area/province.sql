use hc_account;

/*==============================================================*/
/* Table: a_area_province                                       */
/*==============================================================*/
create table a_area_province
(
   code                 int unsigned not null auto_increment,
   name                 varchar(16) not null comment '名称',
   primary key (code)
)
engine = InnoDB
default charset = utf8
collate = utf8_bin;

alter table a_area_province comment '省';

-- ----------------------------
-- Records of province
-- ----------------------------
INSERT INTO a_area_province VALUES (11, '北京市');
INSERT INTO a_area_province VALUES (12, '天津市');
INSERT INTO a_area_province VALUES (13, '河北省');
INSERT INTO a_area_province VALUES (14, '山西省');
INSERT INTO a_area_province VALUES (15, '内蒙古自治区');
INSERT INTO a_area_province VALUES (21, '辽宁省');
INSERT INTO a_area_province VALUES (22, '吉林省');
INSERT INTO a_area_province VALUES (23, '黑龙江省');
INSERT INTO a_area_province VALUES (31, '上海市');
INSERT INTO a_area_province VALUES (32, '江苏省');
INSERT INTO a_area_province VALUES (33, '浙江省');
INSERT INTO a_area_province VALUES (34, '安徽省');
INSERT INTO a_area_province VALUES (35, '福建省');
INSERT INTO a_area_province VALUES (36, '江西省');
INSERT INTO a_area_province VALUES (37, '山东省');
INSERT INTO a_area_province VALUES (41, '河南省');
INSERT INTO a_area_province VALUES (42, '湖北省');
INSERT INTO a_area_province VALUES (43, '湖南省');
INSERT INTO a_area_province VALUES (44, '广东省');
INSERT INTO a_area_province VALUES (45, '广西壮族自治区');
INSERT INTO a_area_province VALUES (46, '海南省');
INSERT INTO a_area_province VALUES (50, '重庆市');
INSERT INTO a_area_province VALUES (51, '四川省');
INSERT INTO a_area_province VALUES (52, '贵州省');
INSERT INTO a_area_province VALUES (53, '云南省');
INSERT INTO a_area_province VALUES (54, '西藏自治区');
INSERT INTO a_area_province VALUES (61, '陕西省');
INSERT INTO a_area_province VALUES (62, '甘肃省');
INSERT INTO a_area_province VALUES (63, '青海省');
INSERT INTO a_area_province VALUES (64, '宁夏回族自治区');
INSERT INTO a_area_province VALUES (65, '新疆维吾尔自治区');
