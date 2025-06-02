/*==============================================================*/
/* Table: Answer_options                                        */
/*==============================================================*/
create table Answer_options (
   id_option            SERIAL               not null,
   id_question          INT4                 null,
   text_option          VARCHAR(255)         not null,
   constraint PK_ANSWER_OPTIONS primary key (id_option)
);

/*==============================================================*/
/* Index: Answer_options_PK                                     */
/*==============================================================*/
create unique index Answer_options_PK on Answer_options (
id_option
);

/*==============================================================*/
/* Index: must_have_FK                                          */
/*==============================================================*/
create  index must_have_FK on Answer_options (
id_question
);

/*==============================================================*/
/* Table: Answers                                               */
/*==============================================================*/
create table Answers (
   id_answer            SERIAL               not null,
   id_question          INT4                 null,
   id_user              INT4                 null,
   text_answer          VARCHAR(255)         not null,
   constraint PK_ANSWERS primary key (id_answer)
);

/*==============================================================*/
/* Index: Answers_PK                                            */
/*==============================================================*/
create unique index Answers_PK on Answers (
id_answer
);

/*==============================================================*/
/* Index: assume_FK                                             */
/*==============================================================*/
create  index assume_FK on Answers (
id_question
);

/*==============================================================*/
/* Index: asnwers_to_FK                                         */
/*==============================================================*/
create  index asnwers_to_FK on Answers (
id_user
);

/*==============================================================*/
/* Table: Material                                              */
/*==============================================================*/
create table Material (
   id_material          CHAR(10)             not null,
   id_step              INT4                 null,
   path_matial          VARCHAR(1024)        null,
   link_material        VARCHAR(5000)        null,
   constraint PK_MATERIAL primary key (id_material)
);

/*==============================================================*/
/* Index: Material_PK                                           */
/*==============================================================*/
create unique index Material_PK on Material (
id_material
);

/*==============================================================*/
/* Index: may_include_FK                                        */
/*==============================================================*/
create  index may_include_FK on Material (
id_step
);

/*==============================================================*/
/* Table: Questions                                             */
/*==============================================================*/
create table Questions (
   id_question          SERIAL               not null,
   id_test              INT4                 null,
   text_question        VARCHAR(255)         not null,
   answer_question      VARCHAR(255)         not null,
   type_question        VARCHAR(255)         not null,
   image_question       VARCHAR(1024)        null,
   constraint PK_QUESTIONS primary key (id_question)
);

/*==============================================================*/
/* Index: Questions_PK                                          */
/*==============================================================*/
create unique index Questions_PK on Questions (
id_question
);

/*==============================================================*/
/* Index: mean_FK                                               */
/*==============================================================*/
create  index mean_FK on Questions (
id_test
);

/*==============================================================*/
/* Table: Results                                               */
/*==============================================================*/
create table Results (
   id_result            SERIAL               not null,
   id_answer            INT4                 not null,
   id_test              INT4                 not null,
   score_result         VARCHAR(255)         not null,
   constraint PK_RESULTS primary key (id_result)
);

/*==============================================================*/
/* Index: Results_PK                                            */
/*==============================================================*/
create unique index Results_PK on Results (
id_result
);

/*==============================================================*/
/* Index: goes_to_FK                                            */
/*==============================================================*/
create  index goes_to_FK on Results (
id_answer
);

/*==============================================================*/
/* Index: stats_in_FK                                           */
/*==============================================================*/
create  index stats_in_FK on Results (
id_test
);

/*==============================================================*/
/* Table: Stat                                                  */
/*==============================================================*/
create table Stat (
   id_stat              SERIAL               not null,
   id_user              INT4                 null,
   id_course            INT4                 null,
   id_result            INT4                 null,
   prec_through         VARCHAR(255)         not null,
   constraint PK_STAT primary key (id_stat)
);

/*==============================================================*/
/* Index: Stat_PK                                               */
/*==============================================================*/
create unique index Stat_PK on Stat (
id_stat
);

/*==============================================================*/
/* Index: has_in_courses_FK                                     */
/*==============================================================*/
create  index has_in_courses_FK on Stat (
id_user
);

/*==============================================================*/
/* Index: goes_into_FK                                          */
/*==============================================================*/
create  index goes_into_FK on Stat (
id_course
);

/*==============================================================*/
/* Index: counts_from_FK                                        */
/*==============================================================*/
create  index counts_from_FK on Stat (
id_result
);

/*==============================================================*/
/* Table: Steps                                                 */
/*==============================================================*/
create table Steps (
   id_step              SERIAL               not null,
   id_lesson            INT4                 not null,
   number_steps         VARCHAR(255)         not null,
   status_step          VARCHAR(255)         not null,
   type_step           VARCHAR(50)          not null,
   constraint PK_STEPS primary key (id_step)
);

/*==============================================================*/
/* Index: Steps_PK                                              */
/*==============================================================*/
create unique index Steps_PK on Steps (
id_step
);

/*==============================================================*/
/* Index: also_include_FK                                       */
/*==============================================================*/
create  index also_include_FK on Steps (
id_lesson
);

/*==============================================================*/
/* Table: Tests                                                 */
/*==============================================================*/
create table Tests (
   id_test              SERIAL               not null,
   id_step              INT4                 not null,
   name_test            VARCHAR(255)         not null,
   desc_test            VARCHAR(255)         null,
   constraint PK_TESTS primary key (id_test)
);

/*==============================================================*/
/* Index: Tests_PK                                              */
/*==============================================================*/
create unique index Tests_PK on Tests (
id_test
);

/*==============================================================*/
/* Index: may_also_include2_FK                                  */
/*==============================================================*/
create  index may_also_include2_FK on Tests (
id_step
);

/*==============================================================*/
/* Table: code_tasks                                            */
/*==============================================================*/
create table code_tasks (
   id_ct                SERIAL               not null,
   id_question          INT4                 null,
   input_ct             TEXT                 not null,
   output_ct            VARCHAR(1024)        not null,
   constraint PK_CODE_TASKS primary key (id_ct)
);

/*==============================================================*/
/* Index: code_tasks_PK                                         */
/*==============================================================*/
create unique index code_tasks_PK on code_tasks (
id_ct
);

/*==============================================================*/
/* Index: might_include_FK                                      */
/*==============================================================*/
create  index might_include_FK on code_tasks (
id_question
);

/*==============================================================*/
/* Table: course                                                */
/*==============================================================*/
create table course (
   id_course            SERIAL               not null,
   name_course          VARCHAR(70)          not null,
   desc_course          TEXT                 not null,
   with_certificate     BOOL                 null,
   hourse_course        VARCHAR(5)           not null,
   requred_year         VARCHAR(10)          null,
   required_spec        VARCHAR(50)          null,
   required_uni         VARCHAR(70)          null,
   level_course         VARCHAR(50)          null,
   tags_course          VARCHAR(255)         not null,
   certificate_enabled  BOOLEAN              default false,
   constraint PK_COURSE primary key (id_course)
);

/*==============================================================*/
/* Index: course_PK                                             */
/*==============================================================*/
create unique index course_PK on course (
id_course
);

/*==============================================================*/
/* Table: create_passes                                         */
/*==============================================================*/
create table create_passes (
   id_course            INT4                 not null,
   id_user              INT4                 not null,
   constraint PK_CREATE_PASSES primary key (id_course, id_user)
);

/*==============================================================*/
/* Index: create_passes_PK                                      */
/*==============================================================*/
create unique index create_passes_PK on create_passes (
id_course,
id_user
);

/*==============================================================*/
/* Index: create_passes2_FK                                     */
/*==============================================================*/
create  index create_passes2_FK on create_passes (
id_user
);

/*==============================================================*/
/* Index: create_passes_FK                                      */
/*==============================================================*/
create  index create_passes_FK on create_passes (
id_course
);

/*==============================================================*/
/* Table: feedback                                              */
/*==============================================================*/
create table feedback (
   id_feedback          SERIAL               not null,
   id_course            INT4                 not null,
   id_user              INT4                 not null,
   text_feedback        VARCHAR(5000)        null,
   date_feedback        DATE                 not null,
   rate_feedback        VARCHAR(5)           not null,
   constraint PK_FEEDBACK primary key (id_feedback)
);

/*==============================================================*/
/* Index: feedback_PK                                           */
/*==============================================================*/
create unique index feedback_PK on feedback (
id_feedback
);

/*==============================================================*/
/* Index: has_FK                                                */
/*==============================================================*/
create  index has_FK on feedback (
id_course
);

/*==============================================================*/
/* Index: feedback_user_FK                                      */
/*==============================================================*/
create  index feedback_user_FK on feedback (
id_user
);

/*==============================================================*/
/* Table: lessons                                               */
/*==============================================================*/
create table lessons (
   id_lesson            SERIAL               not null,
   id_course            INT4                 not null,
   id_stat              INT4                 null,
   name_lesson          VARCHAR(255)         not null,
   status_lesson        VARCHAR(255)         not null,
   constraint PK_LESSONS primary key (id_lesson)
);

/*==============================================================*/
/* Index: lessons_PK                                            */
/*==============================================================*/
create unique index lessons_PK on lessons (
id_lesson
);

/*==============================================================*/
/* Index: include_FK                                            */
/*==============================================================*/
create  index include_FK on lessons (
id_course
);

/*==============================================================*/
/* Index: procent_pass_FK                                       */
/*==============================================================*/
create  index procent_pass_FK on lessons (
id_stat
);

/*==============================================================*/
/* Table: users                                                 */
/*==============================================================*/
create table users (
   id_user              SERIAL               not null,
   fn_user              VARCHAR(255)         not null,
   birth_user           DATE                 not null,
   uni_user             VARCHAR(255)         not null,
   role_user            VARCHAR(255)         not null,
   spec_user            VARCHAR(255)         not null,
   year_user            INT4                 not null,
   pass_user            VARCHAR(1024)        null,
   edu_juser            VARCHAR(1024)        null,
   sud_user             VARCHAR(1024)        null,
   login_user           VARCHAR(255)         not null,
   password_user        VARCHAR(255)         not null,
   constraint PK_USERS primary key (id_user)
);

/*==============================================================*/
/* Index: users_PK                                              */
/*==============================================================*/
create unique index users_PK on users (
id_user
);

alter table Answer_options
   add constraint FK_ANSWER_O_MUST_HAVE_QUESTION foreign key (id_question)
      references Questions (id_question)
      on delete restrict on update restrict;

alter table Answers
   add constraint FK_ANSWERS_ASNWERS_T_USERS foreign key (id_user)
      references users (id_user)
      on delete restrict on update restrict;

alter table Answers
   add constraint FK_ANSWERS_ASSUME_QUESTION foreign key (id_question)
      references Questions (id_question)
      on delete restrict on update restrict;

alter table Material
   add constraint FK_MATERIAL_MAY_INCLU_STEPS foreign key (id_step)
      references Steps (id_step)
      on delete restrict on update restrict;

alter table Questions
   add constraint FK_QUESTION_MEAN_TESTS foreign key (id_test)
      references Tests (id_test)
      on delete restrict on update restrict;

alter table Results
   add constraint FK_RESULTS_GOES_TO_ANSWERS foreign key (id_answer)
      references Answers (id_answer)
      on delete restrict on update restrict;

alter table Results
   add constraint FK_RESULTS_STATS_IN_TESTS foreign key (id_test)
      references Tests (id_test)
      on delete restrict on update restrict;

alter table Stat
   add constraint FK_STAT_COUNTS_FR_RESULTS foreign key (id_result)
      references Results (id_result)
      on delete restrict on update restrict;

alter table Stat
   add constraint FK_STAT_GOES_INTO_COURSE foreign key (id_course)
      references course (id_course)
      on delete restrict on update restrict;

alter table Stat
   add constraint FK_STAT_HAS_IN_CO_USERS foreign key (id_user)
      references users (id_user)
      on delete restrict on update restrict;

alter table Steps
   add constraint FK_STEPS_ALSO_INCL_LESSONS foreign key (id_lesson)
      references lessons (id_lesson)
      on delete restrict on update restrict;

alter table Tests
   add constraint FK_TESTS_MAY_ALSO__STEPS foreign key (id_step)
      references Steps (id_step)
      on delete restrict on update restrict;

alter table code_tasks
   add constraint FK_CODE_TAS_MIGHT_INC_QUESTION foreign key (id_question)
      references Questions (id_question)
      on delete restrict on update restrict;

alter table create_passes
   add constraint FK_CREATE_P_CREATE_PA_COURSE foreign key (id_course)
      references course (id_course)
      on delete restrict on update restrict;

alter table create_passes
   add constraint FK_CREATE_P_CREATE_PA_USERS foreign key (id_user)
      references users (id_user)
      on delete restrict on update restrict;

alter table feedback
   add constraint FK_FEEDBACK_HAS_COURSE foreign key (id_course)
      references course (id_course)
      on delete restrict on update restrict;

alter table feedback
   add constraint FK_FEEDBACK_USER foreign key (id_user)
      references users (id_user)
      on delete restrict on update restrict;

alter table lessons
   add constraint FK_LESSONS_INCLUDE_COURSE foreign key (id_course)
      references course (id_course)
      on delete restrict on update restrict;

alter table lessons
   add constraint FK_LESSONS_PROCENT_P_STAT foreign key (id_stat)
      references Stat (id_stat)
      on delete restrict on update restrict;

/*==============================================================*/
/* Table: certificates                                           */
/*==============================================================*/
create table certificates (
   id_certificate       SERIAL               not null,
   id_user             INT4                 not null,
   id_course           INT4                 not null,
   date_issued         TIMESTAMP            not null,
   certificate_path    VARCHAR(1024)        not null,
   constraint PK_CERTIFICATES primary key (id_certificate)
);

/*==============================================================*/
/* Index: certificates_PK                                        */
/*==============================================================*/
create unique index certificates_PK on certificates (
id_certificate
);

/*==============================================================*/
/* Index: user_certificates_FK                                   */
/*==============================================================*/
create index user_certificates_FK on certificates (
id_user
);

/*==============================================================*/
/* Index: course_certificates_FK                                 */
/*==============================================================*/
create index course_certificates_FK on certificates (
id_course
);

alter table certificates
   add constraint FK_CERTIF_USER_CERTIF_USERS foreign key (id_user)
      references users (id_user)
      on delete restrict on update restrict;

alter table certificates
   add constraint FK_CERTIF_COURSE_CERTIF_COURSE foreign key (id_course)
      references course (id_course)
      on delete restrict on update restrict;

