use hc_account;

/*==============================================================*/
/* Table: a_message                                             */
/*==============================================================*/
CREATE INDEX idx_a_message_user_name
  ON a_message
  (
    user_name(8)
  );

CREATE INDEX idx_a_message_to_user_name
  ON a_message
  (
    to_user_name(8)
  );

CREATE INDEX idx_a_message_msg_id
  ON a_message
  (
    msg_id(8)
  );

/*==============================================================*/
/* Table: a_relationships                                       */
/*==============================================================*/
CREATE INDEX idx_a_relationships_user_name
  ON a_relationships
  (
    user_name(8)
  );

CREATE INDEX idx_a_relationships_flows_name
  ON a_relationships
  (
    flows_name(8)
  );

CREATE INDEX idx_a_relationships_rs_id
  ON a_relationships
  (
    rs_id(8)
  );

/*==============================================================*/
/* Index: a_report_user                                         */
/*==============================================================*/
CREATE INDEX idx_a_report_user_rep_id
  ON a_report_user
  (
    rep_id(8)
  );

/*==============================================================*/
/* Index: a_role                                                */
/*==============================================================*/
CREATE INDEX idx_a_role_role_id
  ON a_role
  (
    role_id(8)
  );

CREATE INDEX idx_a_role_sort
  ON a_role
  (
    sort
  );

/*==============================================================*/
/* Index: a_role_rules                                          */
/*==============================================================*/
CREATE INDEX idx_a_role_rules_rr_id
  ON a_role_rules
  (
    rr_id(8)
  );

CREATE INDEX idx_a_role_rules_role_id
  ON a_role_rules
  (
    role_id(8)
  );

CREATE INDEX idx_a_role_rules_rule_id
  ON a_role_rules
  (
    rule_id(8)
  );

/*==============================================================*/
/* Index: a_rule                                                */
/*==============================================================*/
CREATE INDEX idx_a_rule_rule_id
  ON a_rule
  (
    rule_id(8)
  );

CREATE INDEX idx_a_rule_control
  ON a_rule
  (
    control(8)
  );


CREATE INDEX idx_a_rule_method
  ON a_rule
  (
    method(8)
  );

/*==============================================================*/
/* Index: a_user                                         		    */
/*==============================================================*/
CREATE UNIQUE INDEX uidx_a_user_user_name
  ON a_user
  (
    user_name
  );

/*==============================================================*/
/* Index: a_user_auth                                           */
/*==============================================================*/
CREATE UNIQUE INDEX uidx_a_user_auth_user_name
  ON a_user_auth
  (
    user_name
  );

CREATE INDEX idx_a_user_auth_phone
  ON a_user_auth
  (
    phone
  );

CREATE INDEX idx_a_user_auth_email
  ON a_user_auth
  (
    email(8)
  );

/*==============================================================*/
/* Index: a_log_details                                        */
/*==============================================================*/
CREATE INDEX idx_a_log_details_opd_id
  ON a_log_details
  (
    opd_id(8)
  );

CREATE INDEX idx_a_log_details_op_id
  ON a_log_details
  (
    op_id(8)
  );

/*==============================================================*/
/* Index: a_logs                                               */
/*==============================================================*/
CREATE INDEX idx_a_user_logs_op_id
  ON a_logs
  (
    op_id(8)
  );

CREATE INDEX idx_a_user_logs_user_name
  ON a_logs
  (
    user_name(8)
  );

/*==============================================================*/
/* Index: a_user_logs                                           */
/*==============================================================*/
CREATE INDEX idx_a_user_logs_op_id
  ON a_user_logs
  (
    op_id(8)
  );

CREATE INDEX idx_a_user_logs_user_name
  ON a_user_logs
  (
    user_name(8)
  );

CREATE INDEX idx_a_user_logs_tb_id
  ON a_user_logs
  (
    tb_id(8)
  );

CREATE INDEX idx_a_user_logs_tb_name
  ON a_user_logs
  (
    tb_name(8)
  );

/*==============================================================*/
/* Index: a_user_oauths                                         */
/*==============================================================*/
CREATE INDEX idx_a_user_oauths_oauth_id
  ON a_user_oauths
  (
    oauth_id(8)
  );

CREATE INDEX idx_a_user_oauths_user_name
  ON a_user_oauths
  (
    user_name(8)
  );

/*==============================================================*/
/* Index: a_user_roles                                          */
/*==============================================================*/
CREATE INDEX idx_a_user_roles_uro_id
  ON a_user_roles
  (
    uro_id(8)
  );

CREATE INDEX idx_a_user_roles_user_name
  ON a_user_roles
  (
    user_name(8)
  );

CREATE INDEX idx_a_user_roles_role_id
  ON a_user_roles
  (
    role_id(8)
  );

/*==============================================================*/
/* Index: a_user_stat                                           */
/*==============================================================*/
CREATE INDEX idx_a_user_stat_user_name
  ON a_user_stat
  (
    user_name(8)
  );

/*==============================================================*/
/* Index: a_user_tokens                                         */
/*==============================================================*/
CREATE INDEX idx_a_user_tokens_token_id
  ON a_user_tokens
  (
    token_id(8)
  );

CREATE INDEX idx_a_user_tokens_user_name
  ON a_user_tokens
  (
    user_name(8)
  );