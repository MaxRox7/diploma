--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.0

-- Started on 2025-05-30 17:05:21

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 218 (class 1259 OID 24579)
-- Name: answer_options; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.answer_options (
    id_option integer NOT NULL,
    id_question integer,
    text_option character varying(255) NOT NULL
);


ALTER TABLE public.answer_options OWNER TO pguser;

--
-- TOC entry 217 (class 1259 OID 24578)
-- Name: answer_options_id_option_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.answer_options_id_option_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.answer_options_id_option_seq OWNER TO pguser;

--
-- TOC entry 3601 (class 0 OID 0)
-- Dependencies: 217
-- Name: answer_options_id_option_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.answer_options_id_option_seq OWNED BY public.answer_options.id_option;


--
-- TOC entry 220 (class 1259 OID 24588)
-- Name: answers; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.answers (
    id_answer integer NOT NULL,
    id_question integer,
    id_user integer,
    text_answer character varying(255) NOT NULL
);


ALTER TABLE public.answers OWNER TO pguser;

--
-- TOC entry 219 (class 1259 OID 24587)
-- Name: answers_id_answer_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.answers_id_answer_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.answers_id_answer_seq OWNER TO pguser;

--
-- TOC entry 3602 (class 0 OID 0)
-- Dependencies: 219
-- Name: answers_id_answer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.answers_id_answer_seq OWNED BY public.answers.id_answer;


--
-- TOC entry 244 (class 1259 OID 24821)
-- Name: certificates; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.certificates (
    id_certificate integer NOT NULL,
    id_user integer NOT NULL,
    id_course integer NOT NULL,
    date_issued timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    certificate_path character varying(255) NOT NULL
);


ALTER TABLE public.certificates OWNER TO pguser;

--
-- TOC entry 243 (class 1259 OID 24820)
-- Name: certificates_id_certificate_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.certificates_id_certificate_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.certificates_id_certificate_seq OWNER TO pguser;

--
-- TOC entry 3603 (class 0 OID 0)
-- Dependencies: 243
-- Name: certificates_id_certificate_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.certificates_id_certificate_seq OWNED BY public.certificates.id_certificate;


--
-- TOC entry 233 (class 1259 OID 24661)
-- Name: code_tasks; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.code_tasks (
    id_ct integer NOT NULL,
    id_question integer,
    input_ct text NOT NULL,
    output_ct character varying(1024) NOT NULL
);


ALTER TABLE public.code_tasks OWNER TO pguser;

--
-- TOC entry 232 (class 1259 OID 24660)
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.code_tasks_id_ct_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.code_tasks_id_ct_seq OWNER TO pguser;

--
-- TOC entry 3604 (class 0 OID 0)
-- Dependencies: 232
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.code_tasks_id_ct_seq OWNED BY public.code_tasks.id_ct;


--
-- TOC entry 235 (class 1259 OID 24672)
-- Name: course; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.course (
    id_course integer NOT NULL,
    name_course character varying(70) NOT NULL,
    desc_course text NOT NULL,
    with_certificate boolean,
    hourse_course character varying(5) NOT NULL,
    requred_year character varying(10),
    required_spec character varying(50),
    required_uni character varying(70),
    level_course character varying(50),
    tags_course character varying(255) NOT NULL
);


ALTER TABLE public.course OWNER TO pguser;

--
-- TOC entry 234 (class 1259 OID 24671)
-- Name: course_id_course_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.course_id_course_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.course_id_course_seq OWNER TO pguser;

--
-- TOC entry 3605 (class 0 OID 0)
-- Dependencies: 234
-- Name: course_id_course_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.course_id_course_seq OWNED BY public.course.id_course;


--
-- TOC entry 236 (class 1259 OID 24681)
-- Name: create_passes; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.create_passes (
    id_course integer NOT NULL,
    id_user integer NOT NULL,
    is_creator boolean DEFAULT false,
    date_complete timestamp without time zone
);


ALTER TABLE public.create_passes OWNER TO pguser;

--
-- TOC entry 238 (class 1259 OID 24690)
-- Name: feedback; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.feedback (
    id_feedback integer NOT NULL,
    id_course integer NOT NULL,
    text_feedback character varying(5000),
    date_feedback date NOT NULL,
    rate_feedback character varying(5) NOT NULL,
    id_user integer
);


ALTER TABLE public.feedback OWNER TO pguser;

--
-- TOC entry 237 (class 1259 OID 24689)
-- Name: feedback_id_feedback_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.feedback_id_feedback_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.feedback_id_feedback_seq OWNER TO pguser;

--
-- TOC entry 3606 (class 0 OID 0)
-- Dependencies: 237
-- Name: feedback_id_feedback_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.feedback_id_feedback_seq OWNED BY public.feedback.id_feedback;


--
-- TOC entry 240 (class 1259 OID 24701)
-- Name: lessons; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.lessons (
    id_lesson integer NOT NULL,
    id_course integer NOT NULL,
    id_stat integer,
    name_lesson character varying(255) NOT NULL,
    status_lesson character varying(255) NOT NULL
);


ALTER TABLE public.lessons OWNER TO pguser;

--
-- TOC entry 239 (class 1259 OID 24700)
-- Name: lessons_id_lesson_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.lessons_id_lesson_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.lessons_id_lesson_seq OWNER TO pguser;

--
-- TOC entry 3607 (class 0 OID 0)
-- Dependencies: 239
-- Name: lessons_id_lesson_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.lessons_id_lesson_seq OWNED BY public.lessons.id_lesson;


--
-- TOC entry 221 (class 1259 OID 24597)
-- Name: material; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.material (
    id_material character(10) NOT NULL,
    id_step integer,
    path_matial character varying(1024),
    link_material character varying(5000)
);


ALTER TABLE public.material OWNER TO pguser;

--
-- TOC entry 223 (class 1259 OID 24607)
-- Name: questions; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.questions (
    id_question integer NOT NULL,
    id_test integer,
    text_question character varying(255) NOT NULL,
    answer_question character varying(255) NOT NULL,
    type_question character varying(255) NOT NULL,
    image_question character varying(1024)
);


ALTER TABLE public.questions OWNER TO pguser;

--
-- TOC entry 222 (class 1259 OID 24606)
-- Name: questions_id_question_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.questions_id_question_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.questions_id_question_seq OWNER TO pguser;

--
-- TOC entry 3608 (class 0 OID 0)
-- Dependencies: 222
-- Name: questions_id_question_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.questions_id_question_seq OWNED BY public.questions.id_question;


--
-- TOC entry 225 (class 1259 OID 24618)
-- Name: results; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.results (
    id_result integer NOT NULL,
    id_answer integer NOT NULL,
    id_test integer NOT NULL,
    score_result character varying(255) NOT NULL
);


ALTER TABLE public.results OWNER TO pguser;

--
-- TOC entry 224 (class 1259 OID 24617)
-- Name: results_id_result_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.results_id_result_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.results_id_result_seq OWNER TO pguser;

--
-- TOC entry 3609 (class 0 OID 0)
-- Dependencies: 224
-- Name: results_id_result_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.results_id_result_seq OWNED BY public.results.id_result;


--
-- TOC entry 227 (class 1259 OID 24628)
-- Name: stat; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.stat (
    id_stat integer NOT NULL,
    id_user integer,
    id_course integer,
    id_result integer,
    prec_through character varying(255) NOT NULL
);


ALTER TABLE public.stat OWNER TO pguser;

--
-- TOC entry 226 (class 1259 OID 24627)
-- Name: stat_id_stat_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.stat_id_stat_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stat_id_stat_seq OWNER TO pguser;

--
-- TOC entry 3610 (class 0 OID 0)
-- Dependencies: 226
-- Name: stat_id_stat_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.stat_id_stat_seq OWNED BY public.stat.id_stat;


--
-- TOC entry 229 (class 1259 OID 24639)
-- Name: steps; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.steps (
    id_step integer NOT NULL,
    id_lesson integer NOT NULL,
    number_steps character varying(255) NOT NULL,
    status_step character varying(255) DEFAULT 'not_started'::character varying NOT NULL,
    type_step character varying(50) DEFAULT 'material'::character varying NOT NULL
);


ALTER TABLE public.steps OWNER TO pguser;

--
-- TOC entry 228 (class 1259 OID 24638)
-- Name: steps_id_step_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.steps_id_step_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.steps_id_step_seq OWNER TO pguser;

--
-- TOC entry 3611 (class 0 OID 0)
-- Dependencies: 228
-- Name: steps_id_step_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.steps_id_step_seq OWNED BY public.steps.id_step;


--
-- TOC entry 231 (class 1259 OID 24650)
-- Name: tests; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.tests (
    id_test integer NOT NULL,
    id_step integer NOT NULL,
    name_test character varying(255) NOT NULL,
    desc_test character varying(255)
);


ALTER TABLE public.tests OWNER TO pguser;

--
-- TOC entry 230 (class 1259 OID 24649)
-- Name: tests_id_test_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.tests_id_test_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tests_id_test_seq OWNER TO pguser;

--
-- TOC entry 3612 (class 0 OID 0)
-- Dependencies: 230
-- Name: tests_id_test_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.tests_id_test_seq OWNED BY public.tests.id_test;


--
-- TOC entry 242 (class 1259 OID 24713)
-- Name: users; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.users (
    id_user integer NOT NULL,
    fn_user character varying(255) NOT NULL,
    birth_user date NOT NULL,
    uni_user character varying(255) NOT NULL,
    role_user character varying(255) NOT NULL,
    spec_user character varying(255) NOT NULL,
    year_user integer NOT NULL,
    pass_user character varying(1024),
    edu_juser character varying(1024),
    sud_user character varying(1024),
    login_user character varying(255) NOT NULL,
    password_user character varying(255) NOT NULL
);


ALTER TABLE public.users OWNER TO pguser;

--
-- TOC entry 241 (class 1259 OID 24712)
-- Name: users_id_user_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.users_id_user_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_user_seq OWNER TO pguser;

--
-- TOC entry 3613 (class 0 OID 0)
-- Dependencies: 241
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- TOC entry 3321 (class 2604 OID 24582)
-- Name: answer_options id_option; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options ALTER COLUMN id_option SET DEFAULT nextval('public.answer_options_id_option_seq'::regclass);


--
-- TOC entry 3322 (class 2604 OID 24591)
-- Name: answers id_answer; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers ALTER COLUMN id_answer SET DEFAULT nextval('public.answers_id_answer_seq'::regclass);


--
-- TOC entry 3336 (class 2604 OID 24824)
-- Name: certificates id_certificate; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates ALTER COLUMN id_certificate SET DEFAULT nextval('public.certificates_id_certificate_seq'::regclass);


--
-- TOC entry 3330 (class 2604 OID 24664)
-- Name: code_tasks id_ct; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks ALTER COLUMN id_ct SET DEFAULT nextval('public.code_tasks_id_ct_seq'::regclass);


--
-- TOC entry 3331 (class 2604 OID 24675)
-- Name: course id_course; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course ALTER COLUMN id_course SET DEFAULT nextval('public.course_id_course_seq'::regclass);


--
-- TOC entry 3333 (class 2604 OID 24693)
-- Name: feedback id_feedback; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback ALTER COLUMN id_feedback SET DEFAULT nextval('public.feedback_id_feedback_seq'::regclass);


--
-- TOC entry 3334 (class 2604 OID 24704)
-- Name: lessons id_lesson; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons ALTER COLUMN id_lesson SET DEFAULT nextval('public.lessons_id_lesson_seq'::regclass);


--
-- TOC entry 3323 (class 2604 OID 24610)
-- Name: questions id_question; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions ALTER COLUMN id_question SET DEFAULT nextval('public.questions_id_question_seq'::regclass);


--
-- TOC entry 3324 (class 2604 OID 24621)
-- Name: results id_result; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results ALTER COLUMN id_result SET DEFAULT nextval('public.results_id_result_seq'::regclass);


--
-- TOC entry 3325 (class 2604 OID 24631)
-- Name: stat id_stat; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat ALTER COLUMN id_stat SET DEFAULT nextval('public.stat_id_stat_seq'::regclass);


--
-- TOC entry 3326 (class 2604 OID 24642)
-- Name: steps id_step; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps ALTER COLUMN id_step SET DEFAULT nextval('public.steps_id_step_seq'::regclass);


--
-- TOC entry 3329 (class 2604 OID 24653)
-- Name: tests id_test; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests ALTER COLUMN id_test SET DEFAULT nextval('public.tests_id_test_seq'::regclass);


--
-- TOC entry 3335 (class 2604 OID 24716)
-- Name: users id_user; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users ALTER COLUMN id_user SET DEFAULT nextval('public.users_id_user_seq'::regclass);


--
-- TOC entry 3569 (class 0 OID 24579)
-- Dependencies: 218
-- Data for Name: answer_options; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.answer_options (id_option, id_question, text_option) FROM stdin;
\.


--
-- TOC entry 3571 (class 0 OID 24588)
-- Dependencies: 220
-- Data for Name: answers; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.answers (id_answer, id_question, id_user, text_answer) FROM stdin;
\.


--
-- TOC entry 3595 (class 0 OID 24821)
-- Dependencies: 244
-- Data for Name: certificates; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.certificates (id_certificate, id_user, id_course, date_issued, certificate_path) FROM stdin;
1	1	8	2025-05-30 12:23:47.581266	certificates/cert_6839a3538d70d.pdf
\.


--
-- TOC entry 3584 (class 0 OID 24661)
-- Dependencies: 233
-- Data for Name: code_tasks; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.code_tasks (id_ct, id_question, input_ct, output_ct) FROM stdin;
\.


--
-- TOC entry 3586 (class 0 OID 24672)
-- Dependencies: 235
-- Data for Name: course; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course (id_course, name_course, desc_course, with_certificate, hourse_course, requred_year, required_spec, required_uni, level_course, tags_course) FROM stdin;
4	Python для крутых	круто круто	f	123	\N	\N	\N	\N	питон
8	jjjjjjjjj	jjjjjjjjjjj	t	1	\N	\N	\N	\N	php, web
9	sdfsdf	sdfsdfsdf	f	12	\N	\N	\N	\N	фывфыв
14	asdasd	asdasd	t	1	\N	\N	\N	\N	dsaf
\.


--
-- TOC entry 3587 (class 0 OID 24681)
-- Dependencies: 236
-- Data for Name: create_passes; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.create_passes (id_course, id_user, is_creator, date_complete) FROM stdin;
14	13	f	\N
9	13	f	2025-05-30 14:01:23.786676
8	13	f	2025-05-30 14:01:43.67443
4	13	f	2025-05-30 14:01:52.450335
14	14	f	\N
4	2	f	\N
4	1	t	\N
8	1	t	\N
8	2	f	\N
8	8	f	\N
4	8	f	\N
8	9	f	\N
4	9	f	\N
8	10	f	\N
4	10	f	\N
9	2	f	\N
9	10	f	\N
4	11	f	\N
9	11	f	\N
8	11	f	\N
14	12	f	\N
4	12	f	\N
14	10	f	\N
14	1	t	\N
9	1	t	\N
14	9	f	\N
9	9	f	\N
\.


--
-- TOC entry 3589 (class 0 OID 24690)
-- Dependencies: 238
-- Data for Name: feedback; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.feedback (id_feedback, id_course, text_feedback, date_feedback, rate_feedback, id_user) FROM stdin;
2	4	Найс	2025-05-29	5	\N
4	4	фффф	2025-05-29	5	2
6	9	asas	2025-05-30	5	2
8	9	1	2025-05-30	1	10
\.


--
-- TOC entry 3591 (class 0 OID 24701)
-- Dependencies: 240
-- Data for Name: lessons; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.lessons (id_lesson, id_course, id_stat, name_lesson, status_lesson) FROM stdin;
3	4	\N	Переменные	new
4	4	\N	Условные операторы	new
5	4	\N	Васька	new
10	8	\N	vbvbvb	new
11	9	\N	Переменные	new
17	14	\N	Переменные	new
\.


--
-- TOC entry 3572 (class 0 OID 24597)
-- Dependencies: 221
-- Data for Name: material; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.material (id_material, id_step, path_matial, link_material) FROM stdin;
MAT4693063	7	materials/Python_для_крутых/Переменные/Материал1_7/Приказ_КС.pdf	\N
MAT9453450	8	materials/Python_для_крутых/Переменные/Материал2_8/2025-pravila-provedenia-konkursa-formirovanie-rezerva-liderov-kibersporta.pdf	\N
MAT5906745	9	materials/Python_для_крутых/Условные_операторы/фывфыв_9/4-7.pdf	\N
MAT1909013	10	materials/Python_для_крутых/Условные_операторы/ячсячс_10/PrakticheskayaRabota7.pdf	\N
MAT2641418	11	materials/Python_для_крутых/Васька/фывфыв_11/pasport.pdf	\N
MAT5164769	18	materials/sdfsdf/Переменные/zxczxc_18/Приказ_фиджитал.pdf	\N
MAT8677937	21	materials/jjjjjjjjj/vbvbvb/Читайте_21/Приказ_фиджитал.pdf	\N
MAT6616247	25	materials/asdasd/Переменные/asd_25/Приказ_КС.pdf	\N
\.


--
-- TOC entry 3574 (class 0 OID 24607)
-- Dependencies: 223
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.questions (id_question, id_test, text_question, answer_question, type_question, image_question) FROM stdin;
\.


--
-- TOC entry 3576 (class 0 OID 24618)
-- Dependencies: 225
-- Data for Name: results; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.results (id_result, id_answer, id_test, score_result) FROM stdin;
\.


--
-- TOC entry 3578 (class 0 OID 24628)
-- Dependencies: 227
-- Data for Name: stat; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.stat (id_stat, id_user, id_course, id_result, prec_through) FROM stdin;
\.


--
-- TOC entry 3580 (class 0 OID 24639)
-- Dependencies: 229
-- Data for Name: steps; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.steps (id_step, id_lesson, number_steps, status_step, type_step) FROM stdin;
7	3	Материал1	completed	material
8	3	Материал2	completed	material
9	4	фывфыв	completed	material
10	4	ячсячс	completed	material
11	5	фывфыв	completed	material
18	11	zxczxc	completed	material
21	10	Читайте	completed	material
25	17	asd	completed	material
\.


--
-- TOC entry 3582 (class 0 OID 24650)
-- Dependencies: 231
-- Data for Name: tests; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.tests (id_test, id_step, name_test, desc_test) FROM stdin;
\.


--
-- TOC entry 3593 (class 0 OID 24713)
-- Dependencies: 242
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.users (id_user, fn_user, birth_user, uni_user, role_user, spec_user, year_user, pass_user, edu_juser, sud_user, login_user, password_user) FROM stdin;
1	Гаршина Юлия	2003-03-19	ЛГТУ	admin	Инфа	3	\N	\N	\N	garshina	$2y$12$XhKZi1qqaOMi7UFQgTsCluemygyw3INk6V/k9y9XuNEgshqnJYHG6
2	Болдырев Максим Романович	2003-03-19	ЛГТУ	student	Инфа	2	\N	\N	\N	maxim	$2y$12$tyO9ZJdqQHaFiyMY3eLYyehNtDKKgthQD.ErdkouOg9eqSu8Y299i
3	Пупкин Кирилл Васильевич	2003-03-19	ФЫВ	student	ФЫВ	1	\N	\N	\N	worker	$2y$12$Vi9IlObR0cP9O86E3gyIK.06lwsRV4MpXhaB9YpLvff.5gpSa4nxa
4	ФЫВФЫВФЫВ	123123-03-12	ФЫВФЫВ	student	ФЫВФЫВ	3	\N	\N	\N	asd	$2y$12$0noOBih5NYmUNVJk08HQlOLfPcjDMwnzntxUg06gwmqnv2Wev1Wmi
6	Болдырев Максим Романович	2003-03-19	фыв	student	фыв	1	\N	\N	\N	manager	$2y$12$3JDXebUCeJHsbPEF0/uM7.O6UYRup4zhSC4AvP3cF14xddULeyNQm
7	Мария Егоровна	2003-03-19	фыв	student	фыв	3	\N	\N	\N	mashka	$2y$12$pvBNY22tc/rV1QE4chBP5.AL8wNE.VEdxQNcd3.gf7wBbrLdDGWfi
8	Пупкин Кирилл Васильевич	2003-03-19	фыв	student	фыв	1	\N	\N	\N	dog	$2y$12$41pVBQvZpN2zD6JNkdURrOlYgRpRqapJyO.MEbtgwLjzs2l0ev9mq
9	asdasdasdasd	2003-03-19	asd	student	asd`	1	\N	\N	\N	cat	$2y$12$hw0LPH3ILsiY/Qtak8MW0Oa.rfC3JCrgLYV8IVR9HRlctlN/B6Ztu
10	Max Rox	2003-03-19	123	student	123	2	\N	\N	\N	rox1	$2y$12$K7Qi8alfy9gYanCwv9ZvWeUVI8wiTr7XnWrEZGHcZHJVnPQdYuuT2
11	Maxim Boldyrev	2003-03-19	фыв	student	фыв	4	\N	\N	\N	max_rox7	$2y$12$sEvDhwQcjxeO8sXke3ivUed1A2z5qIeT5JNFJ3wU49NqTmDnFzjcu
12	Болдырев Максим Романович	2003-03-19	asd	student	asd	1	\N	\N	\N	maxrox	$2y$12$oyPCevriVynO7YmyLXHUUOaE7Wk83iZq6K.iAfZke1DPihGi7HfRq
13	Гаршина Юлия	2003-03-19	фяыва	student	щортвыа	3	\N	\N	\N	gart	$2y$12$6hHdMcB9TTAqvotEQJCqJ.H9zeC1e.UbrhrIvgUCo3xg3aGHyn8ce
14	Болдырев Максим Романович	2003-03-19	asd	student	asd	1	\N	\N	\N	maxaaa	$2y$12$1bjYwQK39JrB60R2G8ulrODe8AAoQEiGVW0H.r9st0x6dvozqScuG
\.


--
-- TOC entry 3614 (class 0 OID 0)
-- Dependencies: 217
-- Name: answer_options_id_option_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answer_options_id_option_seq', 1, false);


--
-- TOC entry 3615 (class 0 OID 0)
-- Dependencies: 219
-- Name: answers_id_answer_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answers_id_answer_seq', 1, false);


--
-- TOC entry 3616 (class 0 OID 0)
-- Dependencies: 243
-- Name: certificates_id_certificate_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.certificates_id_certificate_seq', 1, true);


--
-- TOC entry 3617 (class 0 OID 0)
-- Dependencies: 232
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.code_tasks_id_ct_seq', 1, false);


--
-- TOC entry 3618 (class 0 OID 0)
-- Dependencies: 234
-- Name: course_id_course_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.course_id_course_seq', 14, true);


--
-- TOC entry 3619 (class 0 OID 0)
-- Dependencies: 237
-- Name: feedback_id_feedback_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.feedback_id_feedback_seq', 8, true);


--
-- TOC entry 3620 (class 0 OID 0)
-- Dependencies: 239
-- Name: lessons_id_lesson_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.lessons_id_lesson_seq', 17, true);


--
-- TOC entry 3621 (class 0 OID 0)
-- Dependencies: 222
-- Name: questions_id_question_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.questions_id_question_seq', 1, false);


--
-- TOC entry 3622 (class 0 OID 0)
-- Dependencies: 224
-- Name: results_id_result_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.results_id_result_seq', 1, false);


--
-- TOC entry 3623 (class 0 OID 0)
-- Dependencies: 226
-- Name: stat_id_stat_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.stat_id_stat_seq', 1, false);


--
-- TOC entry 3624 (class 0 OID 0)
-- Dependencies: 228
-- Name: steps_id_step_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.steps_id_step_seq', 25, true);


--
-- TOC entry 3625 (class 0 OID 0)
-- Dependencies: 230
-- Name: tests_id_test_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.tests_id_test_seq', 1, false);


--
-- TOC entry 3626 (class 0 OID 0)
-- Dependencies: 241
-- Name: users_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.users_id_user_seq', 14, true);


--
-- TOC entry 3400 (class 2606 OID 24827)
-- Name: certificates certificates_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_pkey PRIMARY KEY (id_certificate);


--
-- TOC entry 3341 (class 2606 OID 24584)
-- Name: answer_options pk_answer_options; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT pk_answer_options PRIMARY KEY (id_option);


--
-- TOC entry 3346 (class 2606 OID 24593)
-- Name: answers pk_answers; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT pk_answers PRIMARY KEY (id_answer);


--
-- TOC entry 3377 (class 2606 OID 24668)
-- Name: code_tasks pk_code_tasks; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT pk_code_tasks PRIMARY KEY (id_ct);


--
-- TOC entry 3380 (class 2606 OID 24679)
-- Name: course pk_course; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course
    ADD CONSTRAINT pk_course PRIMARY KEY (id_course);


--
-- TOC entry 3386 (class 2606 OID 24685)
-- Name: create_passes pk_create_passes; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT pk_create_passes PRIMARY KEY (id_course, id_user);


--
-- TOC entry 3390 (class 2606 OID 24697)
-- Name: feedback pk_feedback; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT pk_feedback PRIMARY KEY (id_feedback);


--
-- TOC entry 3394 (class 2606 OID 24708)
-- Name: lessons pk_lessons; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT pk_lessons PRIMARY KEY (id_lesson);


--
-- TOC entry 3350 (class 2606 OID 24603)
-- Name: material pk_material; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT pk_material PRIMARY KEY (id_material);


--
-- TOC entry 3353 (class 2606 OID 24614)
-- Name: questions pk_questions; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT pk_questions PRIMARY KEY (id_question);


--
-- TOC entry 3357 (class 2606 OID 24623)
-- Name: results pk_results; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT pk_results PRIMARY KEY (id_result);


--
-- TOC entry 3364 (class 2606 OID 24633)
-- Name: stat pk_stat; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT pk_stat PRIMARY KEY (id_stat);


--
-- TOC entry 3368 (class 2606 OID 24646)
-- Name: steps pk_steps; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT pk_steps PRIMARY KEY (id_step);


--
-- TOC entry 3372 (class 2606 OID 24657)
-- Name: tests pk_tests; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT pk_tests PRIMARY KEY (id_test);


--
-- TOC entry 3397 (class 2606 OID 24720)
-- Name: users pk_users; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT pk_users PRIMARY KEY (id_user);


--
-- TOC entry 3366 (class 1259 OID 24648)
-- Name: also_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX also_include_fk ON public.steps USING btree (id_lesson);


--
-- TOC entry 3338 (class 1259 OID 24585)
-- Name: answer_options_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answer_options_pk ON public.answer_options USING btree (id_option);


--
-- TOC entry 3342 (class 1259 OID 24594)
-- Name: answers_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answers_pk ON public.answers USING btree (id_answer);


--
-- TOC entry 3343 (class 1259 OID 24596)
-- Name: asnwers_to_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX asnwers_to_fk ON public.answers USING btree (id_user);


--
-- TOC entry 3344 (class 1259 OID 24595)
-- Name: assume_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX assume_fk ON public.answers USING btree (id_question);


--
-- TOC entry 3374 (class 1259 OID 24669)
-- Name: code_tasks_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX code_tasks_pk ON public.code_tasks USING btree (id_ct);


--
-- TOC entry 3360 (class 1259 OID 24637)
-- Name: counts_from_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX counts_from_fk ON public.stat USING btree (id_result);


--
-- TOC entry 3378 (class 1259 OID 24680)
-- Name: course_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX course_pk ON public.course USING btree (id_course);


--
-- TOC entry 3381 (class 1259 OID 24687)
-- Name: create_passes2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes2_fk ON public.create_passes USING btree (id_user);


--
-- TOC entry 3382 (class 1259 OID 24688)
-- Name: create_passes_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes_fk ON public.create_passes USING btree (id_course);


--
-- TOC entry 3383 (class 1259 OID 24686)
-- Name: create_passes_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX create_passes_pk ON public.create_passes USING btree (id_course, id_user);


--
-- TOC entry 3387 (class 1259 OID 24698)
-- Name: feedback_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX feedback_pk ON public.feedback USING btree (id_feedback);


--
-- TOC entry 3361 (class 1259 OID 24636)
-- Name: goes_into_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX goes_into_fk ON public.stat USING btree (id_course);


--
-- TOC entry 3355 (class 1259 OID 24625)
-- Name: goes_to_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX goes_to_fk ON public.results USING btree (id_answer);


--
-- TOC entry 3388 (class 1259 OID 24699)
-- Name: has_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_fk ON public.feedback USING btree (id_course);


--
-- TOC entry 3362 (class 1259 OID 24635)
-- Name: has_in_courses_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_in_courses_fk ON public.stat USING btree (id_user);


--
-- TOC entry 3384 (class 1259 OID 24845)
-- Name: idx_create_passes_date_complete; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_create_passes_date_complete ON public.create_passes USING btree (date_complete);


--
-- TOC entry 3391 (class 1259 OID 24710)
-- Name: include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX include_fk ON public.lessons USING btree (id_course);


--
-- TOC entry 3392 (class 1259 OID 24709)
-- Name: lessons_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX lessons_pk ON public.lessons USING btree (id_lesson);


--
-- TOC entry 3347 (class 1259 OID 24604)
-- Name: material_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX material_pk ON public.material USING btree (id_material);


--
-- TOC entry 3370 (class 1259 OID 24659)
-- Name: may_also_include2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_also_include2_fk ON public.tests USING btree (id_step);


--
-- TOC entry 3348 (class 1259 OID 24605)
-- Name: may_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_include_fk ON public.material USING btree (id_step);


--
-- TOC entry 3351 (class 1259 OID 24616)
-- Name: mean_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX mean_fk ON public.questions USING btree (id_test);


--
-- TOC entry 3375 (class 1259 OID 24670)
-- Name: might_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX might_include_fk ON public.code_tasks USING btree (id_question);


--
-- TOC entry 3339 (class 1259 OID 24586)
-- Name: must_have_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX must_have_fk ON public.answer_options USING btree (id_question);


--
-- TOC entry 3395 (class 1259 OID 24711)
-- Name: procent_pass_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX procent_pass_fk ON public.lessons USING btree (id_stat);


--
-- TOC entry 3354 (class 1259 OID 24615)
-- Name: questions_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX questions_pk ON public.questions USING btree (id_question);


--
-- TOC entry 3358 (class 1259 OID 24624)
-- Name: results_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX results_pk ON public.results USING btree (id_result);


--
-- TOC entry 3365 (class 1259 OID 24634)
-- Name: stat_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX stat_pk ON public.stat USING btree (id_stat);


--
-- TOC entry 3359 (class 1259 OID 24626)
-- Name: stats_in_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX stats_in_fk ON public.results USING btree (id_test);


--
-- TOC entry 3369 (class 1259 OID 24647)
-- Name: steps_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX steps_pk ON public.steps USING btree (id_step);


--
-- TOC entry 3373 (class 1259 OID 24658)
-- Name: tests_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX tests_pk ON public.tests USING btree (id_test);


--
-- TOC entry 3398 (class 1259 OID 24721)
-- Name: users_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX users_pk ON public.users USING btree (id_user);


--
-- TOC entry 3421 (class 2606 OID 24833)
-- Name: certificates certificates_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3422 (class 2606 OID 24828)
-- Name: certificates certificates_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3401 (class 2606 OID 24722)
-- Name: answer_options fk_answer_o_must_have_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT fk_answer_o_must_have_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3402 (class 2606 OID 24727)
-- Name: answers fk_answers_asnwers_t_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT fk_answers_asnwers_t_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3403 (class 2606 OID 24732)
-- Name: answers fk_answers_assume_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT fk_answers_assume_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3414 (class 2606 OID 24782)
-- Name: code_tasks fk_code_tas_might_inc_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT fk_code_tas_might_inc_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3415 (class 2606 OID 24787)
-- Name: create_passes fk_create_p_create_pa_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3416 (class 2606 OID 24792)
-- Name: create_passes fk_create_p_create_pa_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3417 (class 2606 OID 24797)
-- Name: feedback fk_feedback_has_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_has_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3418 (class 2606 OID 24815)
-- Name: feedback fk_feedback_user; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_user FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3419 (class 2606 OID 24802)
-- Name: lessons fk_lessons_include_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_include_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3420 (class 2606 OID 24807)
-- Name: lessons fk_lessons_procent_p_stat; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_procent_p_stat FOREIGN KEY (id_stat) REFERENCES public.stat(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3404 (class 2606 OID 24737)
-- Name: material fk_material_may_inclu_steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT fk_material_may_inclu_steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3405 (class 2606 OID 24742)
-- Name: questions fk_question_mean_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT fk_question_mean_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3406 (class 2606 OID 24747)
-- Name: results fk_results_goes_to_answers; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT fk_results_goes_to_answers FOREIGN KEY (id_answer) REFERENCES public.answers(id_answer) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3407 (class 2606 OID 24752)
-- Name: results fk_results_stats_in_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT fk_results_stats_in_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3409 (class 2606 OID 24757)
-- Name: stat fk_stat_counts_fr_results; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_counts_fr_results FOREIGN KEY (id_result) REFERENCES public.results(id_result) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3410 (class 2606 OID 24762)
-- Name: stat fk_stat_goes_into_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_goes_into_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3411 (class 2606 OID 24767)
-- Name: stat fk_stat_has_in_co_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_has_in_co_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3412 (class 2606 OID 24772)
-- Name: steps fk_steps_also_incl_lessons; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT fk_steps_also_incl_lessons FOREIGN KEY (id_lesson) REFERENCES public.lessons(id_lesson) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3413 (class 2606 OID 24777)
-- Name: tests fk_tests_may_also__steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT fk_tests_may_also__steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3408 (class 2606 OID 24840)
-- Name: results results_answer_fk; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT results_answer_fk FOREIGN KEY (id_answer) REFERENCES public.answers(id_answer) ON DELETE CASCADE;


-- Completed on 2025-05-30 17:05:23

--
-- PostgreSQL database dump complete
--

