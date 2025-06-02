--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.0

-- Started on 2025-06-02 18:51:53

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
-- TOC entry 3644 (class 0 OID 0)
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
-- TOC entry 3645 (class 0 OID 0)
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
-- TOC entry 3646 (class 0 OID 0)
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
-- TOC entry 3647 (class 0 OID 0)
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
    tags_course character varying(255) NOT NULL,
    status_course character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    moderation_comment text
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
-- TOC entry 3648 (class 0 OID 0)
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
-- TOC entry 3649 (class 0 OID 0)
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
-- TOC entry 3650 (class 0 OID 0)
-- Dependencies: 239
-- Name: lessons_id_lesson_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.lessons_id_lesson_seq OWNED BY public.lessons.id_lesson;


--
-- TOC entry 221 (class 1259 OID 24597)
-- Name: material; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.material (
    id_material character(1000) NOT NULL,
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
-- TOC entry 3651 (class 0 OID 0)
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
-- TOC entry 3652 (class 0 OID 0)
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
-- TOC entry 3653 (class 0 OID 0)
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
-- TOC entry 3654 (class 0 OID 0)
-- Dependencies: 228
-- Name: steps_id_step_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.steps_id_step_seq OWNED BY public.steps.id_step;


--
-- TOC entry 249 (class 1259 OID 24884)
-- Name: test_answers; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.test_answers (
    id_answer integer NOT NULL,
    id_attempt integer,
    id_question integer,
    id_selected_option integer,
    is_correct boolean,
    answer_time timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    answer_text text
);


ALTER TABLE public.test_answers OWNER TO pguser;

--
-- TOC entry 248 (class 1259 OID 24883)
-- Name: test_answers_id_answer_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.test_answers_id_answer_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.test_answers_id_answer_seq OWNER TO pguser;

--
-- TOC entry 3655 (class 0 OID 0)
-- Dependencies: 248
-- Name: test_answers_id_answer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_answers_id_answer_seq OWNED BY public.test_answers.id_answer;


--
-- TOC entry 247 (class 1259 OID 24863)
-- Name: test_attempts; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.test_attempts (
    id_attempt integer NOT NULL,
    id_test integer,
    id_user integer,
    start_time timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    end_time timestamp without time zone,
    score integer,
    max_score integer,
    status character varying(20),
    CONSTRAINT test_attempts_status_check CHECK (((status)::text = ANY ((ARRAY['in_progress'::character varying, 'completed'::character varying, 'abandoned'::character varying])::text[])))
);


ALTER TABLE public.test_attempts OWNER TO pguser;

--
-- TOC entry 246 (class 1259 OID 24862)
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.test_attempts_id_attempt_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.test_attempts_id_attempt_seq OWNER TO pguser;

--
-- TOC entry 3656 (class 0 OID 0)
-- Dependencies: 246
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_attempts_id_attempt_seq OWNED BY public.test_attempts.id_attempt;


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
-- TOC entry 3657 (class 0 OID 0)
-- Dependencies: 230
-- Name: tests_id_test_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.tests_id_test_seq OWNED BY public.tests.id_test;


--
-- TOC entry 245 (class 1259 OID 24846)
-- Name: user_material_progress; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.user_material_progress (
    id_user integer NOT NULL,
    id_step integer NOT NULL,
    completed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_material_progress OWNER TO pguser;

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
    login_user character varying(255) NOT NULL,
    password_user character varying(255) NOT NULL,
    criminal_record_file character varying(255),
    status character varying(20) DEFAULT 'pending'::character varying,
    moderation_comment text,
    student_card character varying(255),
    passport_file character varying(255),
    diploma_file character varying(255)
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
-- TOC entry 3658 (class 0 OID 0)
-- Dependencies: 241
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- TOC entry 3335 (class 2604 OID 24582)
-- Name: answer_options id_option; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options ALTER COLUMN id_option SET DEFAULT nextval('public.answer_options_id_option_seq'::regclass);


--
-- TOC entry 3336 (class 2604 OID 24591)
-- Name: answers id_answer; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers ALTER COLUMN id_answer SET DEFAULT nextval('public.answers_id_answer_seq'::regclass);


--
-- TOC entry 3352 (class 2604 OID 24824)
-- Name: certificates id_certificate; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates ALTER COLUMN id_certificate SET DEFAULT nextval('public.certificates_id_certificate_seq'::regclass);


--
-- TOC entry 3344 (class 2604 OID 24664)
-- Name: code_tasks id_ct; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks ALTER COLUMN id_ct SET DEFAULT nextval('public.code_tasks_id_ct_seq'::regclass);


--
-- TOC entry 3345 (class 2604 OID 24675)
-- Name: course id_course; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course ALTER COLUMN id_course SET DEFAULT nextval('public.course_id_course_seq'::regclass);


--
-- TOC entry 3348 (class 2604 OID 24693)
-- Name: feedback id_feedback; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback ALTER COLUMN id_feedback SET DEFAULT nextval('public.feedback_id_feedback_seq'::regclass);


--
-- TOC entry 3349 (class 2604 OID 24704)
-- Name: lessons id_lesson; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons ALTER COLUMN id_lesson SET DEFAULT nextval('public.lessons_id_lesson_seq'::regclass);


--
-- TOC entry 3337 (class 2604 OID 24610)
-- Name: questions id_question; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions ALTER COLUMN id_question SET DEFAULT nextval('public.questions_id_question_seq'::regclass);


--
-- TOC entry 3338 (class 2604 OID 24621)
-- Name: results id_result; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results ALTER COLUMN id_result SET DEFAULT nextval('public.results_id_result_seq'::regclass);


--
-- TOC entry 3339 (class 2604 OID 24631)
-- Name: stat id_stat; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat ALTER COLUMN id_stat SET DEFAULT nextval('public.stat_id_stat_seq'::regclass);


--
-- TOC entry 3340 (class 2604 OID 24642)
-- Name: steps id_step; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps ALTER COLUMN id_step SET DEFAULT nextval('public.steps_id_step_seq'::regclass);


--
-- TOC entry 3357 (class 2604 OID 24887)
-- Name: test_answers id_answer; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers ALTER COLUMN id_answer SET DEFAULT nextval('public.test_answers_id_answer_seq'::regclass);


--
-- TOC entry 3355 (class 2604 OID 24866)
-- Name: test_attempts id_attempt; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts ALTER COLUMN id_attempt SET DEFAULT nextval('public.test_attempts_id_attempt_seq'::regclass);


--
-- TOC entry 3343 (class 2604 OID 24653)
-- Name: tests id_test; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests ALTER COLUMN id_test SET DEFAULT nextval('public.tests_id_test_seq'::regclass);


--
-- TOC entry 3350 (class 2604 OID 24716)
-- Name: users id_user; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users ALTER COLUMN id_user SET DEFAULT nextval('public.users_id_user_seq'::regclass);


--
-- TOC entry 3607 (class 0 OID 24579)
-- Dependencies: 218
-- Data for Name: answer_options; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.answer_options (id_option, id_question, text_option) FROM stdin;
30	21	to get
31	21	to the
32	21	other side
33	22	АСУ
34	22	Что  то
35	22	что то
36	23	Элизиум||Киберспорт
37	23	Зевс||Спорт
38	25	Потому что
39	25	Не потому что
40	26	Как то так
41	26	Ну вот так
42	27	1||1
43	27	2||2
44	28	1111
45	28	11111
\.


--
-- TOC entry 3609 (class 0 OID 24588)
-- Dependencies: 220
-- Data for Name: answers; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.answers (id_answer, id_question, id_user, text_answer) FROM stdin;
\.


--
-- TOC entry 3633 (class 0 OID 24821)
-- Dependencies: 244
-- Data for Name: certificates; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.certificates (id_certificate, id_user, id_course, date_issued, certificate_path) FROM stdin;
1	1	8	2025-05-30 12:23:47.581266	certificates/cert_6839a3538d70d.pdf
\.


--
-- TOC entry 3622 (class 0 OID 24661)
-- Dependencies: 233
-- Data for Name: code_tasks; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.code_tasks (id_ct, id_question, input_ct, output_ct) FROM stdin;
\.


--
-- TOC entry 3624 (class 0 OID 24672)
-- Dependencies: 235
-- Data for Name: course; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course (id_course, name_course, desc_course, with_certificate, hourse_course, requred_year, required_spec, required_uni, level_course, tags_course, status_course, moderation_comment) FROM stdin;
16	fxg	dfg	f	1	\N	\N	\N	\N	dfg	draft	\N
17	курс	курс	f	1	\N	\N	\N	\N	12	draft	\N
18	asd	asd	t	1	\N	\N	\N	\N	asd	draft	\N
19	Курс для крутой проверки	Этот курс я щас сделаю круто и полноценно	f	5	\N	\N	\N	\N	php, web. krutyak	draft	\N
20	asd	asd	f	12	\N	\N	\N	\N	12	draft	\N
21	ytuytu	tyutyu	t	12	1	Информатика	\N	beginner	dfg	draft	\N
22	фыфыфы	12	f	12	\N	\N	\N	\N	12	draft	\N
8	jjjjjjjjj	jjjjjjjjjjj	t	1	\N	\N	\N	\N	php, web	approved	\N
\.


--
-- TOC entry 3625 (class 0 OID 24681)
-- Dependencies: 236
-- Data for Name: create_passes; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.create_passes (id_course, id_user, is_creator, date_complete) FROM stdin;
16	15	f	2025-05-30 14:22:00.564226
18	3	f	2025-05-30 14:22:16.677608
18	16	f	2025-05-30 14:22:38.374152
18	2	f	2025-05-30 14:25:12.458045
17	2	f	\N
18	17	f	\N
17	17	f	\N
18	10	f	\N
17	10	f	\N
18	18	f	\N
17	18	f	\N
17	16	f	\N
18	19	f	\N
17	19	f	\N
16	1	t	\N
18	20	f	\N
17	20	f	\N
19	1	t	\N
19	2	f	2025-06-02 12:16:54.787512
19	10	f	\N
16	10	f	\N
19	3	f	2025-06-02 12:24:09.234734
19	9	f	\N
17	1	t	\N
8	13	f	\N
8	14	f	\N
8	2	f	\N
16	2	f	\N
16	9	f	\N
20	1	t	\N
20	17	f	\N
8	9	f	\N
20	9	f	2025-06-02 12:38:15.67285
20	3	f	2025-06-02 12:49:37.314823
20	10	f	\N
18	9	f	2025-06-02 13:01:16.152397
21	1	t	\N
21	21	f	\N
22	26	t	\N
18	1	t	\N
18	15	f	\N
17	9	f	\N
17	3	f	\N
16	3	f	\N
8	3	f	\N
17	15	f	\N
8	1	t	\N
8	8	f	\N
8	10	f	\N
8	11	f	\N
\.


--
-- TOC entry 3627 (class 0 OID 24690)
-- Dependencies: 238
-- Data for Name: feedback; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.feedback (id_feedback, id_course, text_feedback, date_feedback, rate_feedback, id_user) FROM stdin;
9	18	sdfdsf	2025-05-30	5	2
10	19	Классный курс	2025-06-02	5	2
11	19	asas	2025-06-02	5	3
\.


--
-- TOC entry 3629 (class 0 OID 24701)
-- Dependencies: 240
-- Data for Name: lessons; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.lessons (id_lesson, id_course, id_stat, name_lesson, status_lesson) FROM stdin;
10	8	\N	vbvbvb	new
19	16	\N	Переменные	new
20	17	\N	Условные операторы	new
21	17	\N	фывфыв	new
22	18	\N	Переменные	new
23	18	\N	asd	new
24	18	\N	Васька	new
25	19	\N	Что такое программирование?	new
26	19	\N	Что такое жизнь?	new
27	20	\N	Переменные	new
28	22	\N	фыв	new
\.


--
-- TOC entry 3610 (class 0 OID 24597)
-- Dependencies: 221
-- Data for Name: material; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.material (id_material, id_step, path_matial, link_material) FROM stdin;
MAT8677937                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              	21	materials/jjjjjjjjj/vbvbvb/Читайте_21/Приказ_фиджитал.pdf	\N
MAT0129978                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              	27	materials/fxg/Переменные/asd_27/Приказ_фиджитал.pdf	\N
MAT6051717                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              	50	materials/asd/Переменные/фы_50/2025-pravila-provedenia-konkursa-formirovanie-rezerva-liderov-kibersporta.pdf	\N
683d9589ea386                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	54	materials/garshina/Что такое программирование?/материал1_54.pdf	\N
683d95915e282                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	55	materials/garshina/Что такое программирование?/материал2_55.pdf	\N
683d95f81e73f                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	57	materials/garshina/Что такое жизнь?/Жизнь_57.pdf	\N
683d987c87004                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	59	materials/garshina/Переменные/as_59.pdf	\N
683d9b50db7f6                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	60	materials/garshina/asd/asas_60.pdf	\N
683dc525195cb                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	62	materials/maxrox1904@gmail.com/фыв/ми_62.pdf	\N
\.


--
-- TOC entry 3612 (class 0 OID 24607)
-- Dependencies: 223
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.questions (id_question, id_test, text_question, answer_question, type_question, image_question) FROM stdin;
21	20	why did the chicken crossed the road?	0,1,2	multi	\N
22	20	Какая лучшая кафедра?	0	single	\N
23	20	Деятельность актива ЛГТУ		match	\N
24	20	Напиши		code	\N
25	21	ну почему?	0	single	\N
26	21	Как	0	multi	\N
27	22	asdsad		match	\N
28	22	111	0,1	multi	\N
29	22	sdfsdfsdfsdf		code	\N
\.


--
-- TOC entry 3614 (class 0 OID 24618)
-- Dependencies: 225
-- Data for Name: results; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.results (id_result, id_answer, id_test, score_result) FROM stdin;
\.


--
-- TOC entry 3616 (class 0 OID 24628)
-- Dependencies: 227
-- Data for Name: stat; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.stat (id_stat, id_user, id_course, id_result, prec_through) FROM stdin;
\.


--
-- TOC entry 3618 (class 0 OID 24639)
-- Dependencies: 229
-- Data for Name: steps; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.steps (id_step, id_lesson, number_steps, status_step, type_step) FROM stdin;
50	22	фы	not_started	material
54	25	материал1	not_started	material
55	25	материал2	not_started	material
56	25	тест после материала	not_started	test
57	26	Жизнь	not_started	material
58	26	Тест	not_started	test
59	27	as	not_started	material
60	23	asas	not_started	material
61	24	test	not_started	test
62	28	ми	not_started	material
21	10	Читайте	completed	material
27	19	asd	completed	material
\.


--
-- TOC entry 3638 (class 0 OID 24884)
-- Dependencies: 249
-- Data for Name: test_answers; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.test_answers (id_answer, id_attempt, id_question, id_selected_option, is_correct, answer_time, answer_text) FROM stdin;
23	14	21	30	t	2025-06-02 12:16:43.789087	\N
24	14	22	33	t	2025-06-02 12:16:43.792159	\N
25	14	23	\N	t	2025-06-02 12:16:43.794578	\N
26	14	24	\N	t	2025-06-02 12:16:43.796107	\N
27	15	25	38	t	2025-06-02 12:16:52.801221	\N
28	15	26	40	t	2025-06-02 12:16:52.804085	\N
29	16	21	30	t	2025-06-02 12:18:37.024189	\N
30	16	22	33	t	2025-06-02 12:18:37.026426	\N
31	16	23	\N	t	2025-06-02 12:18:37.028279	\N
32	16	24	\N	t	2025-06-02 12:18:37.029565	\N
33	17	21	30	t	2025-06-02 12:23:51.53388	\N
34	17	22	33	t	2025-06-02 12:23:51.536793	\N
35	17	23	\N	t	2025-06-02 12:23:51.539218	\N
36	17	24	\N	t	2025-06-02 12:23:51.540791	\N
37	18	25	38	t	2025-06-02 12:24:02.097131	\N
38	18	26	40	t	2025-06-02 12:24:02.100062	\N
39	19	27	\N	t	2025-06-02 12:39:51.331882	\N
40	19	28	44	t	2025-06-02 12:39:51.334951	\N
41	19	29	\N	t	2025-06-02 12:39:51.336564	\N
42	20	27	\N	f	2025-06-02 12:49:55.77388	\N
43	20	28	44	t	2025-06-02 12:49:55.779871	\N
44	20	29	\N	t	2025-06-02 12:49:55.782486	\N
45	21	27	\N	t	2025-06-02 12:55:07.325412	["0","1"]
46	21	28	44	t	2025-06-02 12:55:07.328549	["0","1"]
47	21	29	\N	t	2025-06-02 12:55:07.330248	123123
\.


--
-- TOC entry 3636 (class 0 OID 24863)
-- Dependencies: 247
-- Data for Name: test_attempts; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.test_attempts (id_attempt, id_test, id_user, start_time, end_time, score, max_score, status) FROM stdin;
14	20	2	2025-06-02 12:16:43.782236	2025-06-02 12:16:43.782236	4	4	completed
15	21	2	2025-06-02 12:16:52.759146	2025-06-02 12:16:52.759146	2	2	completed
16	20	10	2025-06-02 12:18:37.017315	2025-06-02 12:18:37.017315	4	4	completed
17	20	3	2025-06-02 12:23:51.493151	2025-06-02 12:23:51.493151	4	4	completed
18	21	3	2025-06-02 12:24:02.055365	2025-06-02 12:24:02.055365	2	2	completed
19	22	9	2025-06-02 12:39:51.291529	2025-06-02 12:39:51.291529	3	3	completed
20	22	3	2025-06-02 12:49:55.768919	2025-06-02 12:49:55.768919	2	3	completed
21	22	10	2025-06-02 12:55:07.283706	2025-06-02 12:55:07.283706	3	3	completed
\.


--
-- TOC entry 3620 (class 0 OID 24650)
-- Dependencies: 231
-- Data for Name: tests; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.tests (id_test, id_step, name_test, desc_test) FROM stdin;
20	56	Новый тест	
21	58	Новый тест	
22	61	Новый тест	
\.


--
-- TOC entry 3634 (class 0 OID 24846)
-- Dependencies: 245
-- Data for Name: user_material_progress; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.user_material_progress (id_user, id_step, completed_at) FROM stdin;
15	27	2025-05-30 14:21:58.760134
2	54	2025-06-02 12:16:33.661684
2	55	2025-06-02 12:16:34.467987
2	57	2025-06-02 12:16:48.979706
10	54	2025-06-02 12:18:28.562323
10	55	2025-06-02 12:18:29.192111
10	57	2025-06-02 12:19:10.277269
3	54	2025-06-02 12:23:41.556144
3	55	2025-06-02 12:23:44.009788
3	57	2025-06-02 12:23:57.576582
9	54	2025-06-02 12:26:03.299362
9	50	2025-06-02 12:38:02.863157
9	59	2025-06-02 12:38:14.492136
10	50	2025-06-02 12:38:47.964909
10	60	2025-06-02 12:38:49.707266
9	60	2025-06-02 12:39:42.719693
3	59	2025-06-02 12:49:36.5386
3	50	2025-06-02 12:49:42.794859
3	60	2025-06-02 12:49:46.648917
\.


--
-- TOC entry 3631 (class 0 OID 24713)
-- Dependencies: 242
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.users (id_user, fn_user, birth_user, uni_user, role_user, spec_user, year_user, login_user, password_user, criminal_record_file, status, moderation_comment, student_card, passport_file, diploma_file) FROM stdin;
2	Болдырев Максим Романович	2003-03-19	ЛГТУ	student	Инфа	2	maxim	$2y$12$tyO9ZJdqQHaFiyMY3eLYyehNtDKKgthQD.ErdkouOg9eqSu8Y299i	\N	pending	\N	\N	\N	\N
3	Пупкин Кирилл Васильевич	2003-03-19	ФЫВ	student	ФЫВ	1	worker	$2y$12$Vi9IlObR0cP9O86E3gyIK.06lwsRV4MpXhaB9YpLvff.5gpSa4nxa	\N	pending	\N	\N	\N	\N
4	ФЫВФЫВФЫВ	123123-03-12	ФЫВФЫВ	student	ФЫВФЫВ	3	asd	$2y$12$0noOBih5NYmUNVJk08HQlOLfPcjDMwnzntxUg06gwmqnv2Wev1Wmi	\N	pending	\N	\N	\N	\N
6	Болдырев Максим Романович	2003-03-19	фыв	student	фыв	1	manager	$2y$12$3JDXebUCeJHsbPEF0/uM7.O6UYRup4zhSC4AvP3cF14xddULeyNQm	\N	pending	\N	\N	\N	\N
7	Мария Егоровна	2003-03-19	фыв	student	фыв	3	mashka	$2y$12$pvBNY22tc/rV1QE4chBP5.AL8wNE.VEdxQNcd3.gf7wBbrLdDGWfi	\N	pending	\N	\N	\N	\N
8	Пупкин Кирилл Васильевич	2003-03-19	фыв	student	фыв	1	dog	$2y$12$41pVBQvZpN2zD6JNkdURrOlYgRpRqapJyO.MEbtgwLjzs2l0ev9mq	\N	pending	\N	\N	\N	\N
9	asdasdasdasd	2003-03-19	asd	student	asd`	1	cat	$2y$12$hw0LPH3ILsiY/Qtak8MW0Oa.rfC3JCrgLYV8IVR9HRlctlN/B6Ztu	\N	pending	\N	\N	\N	\N
10	Max Rox	2003-03-19	123	student	123	2	rox1	$2y$12$K7Qi8alfy9gYanCwv9ZvWeUVI8wiTr7XnWrEZGHcZHJVnPQdYuuT2	\N	pending	\N	\N	\N	\N
11	Maxim Boldyrev	2003-03-19	фыв	student	фыв	4	max_rox7	$2y$12$sEvDhwQcjxeO8sXke3ivUed1A2z5qIeT5JNFJ3wU49NqTmDnFzjcu	\N	pending	\N	\N	\N	\N
12	Болдырев Максим Романович	2003-03-19	asd	student	asd	1	maxrox	$2y$12$oyPCevriVynO7YmyLXHUUOaE7Wk83iZq6K.iAfZke1DPihGi7HfRq	\N	pending	\N	\N	\N	\N
13	Гаршина Юлия	2003-03-19	фяыва	student	щортвыа	3	gart	$2y$12$6hHdMcB9TTAqvotEQJCqJ.H9zeC1e.UbrhrIvgUCo3xg3aGHyn8ce	\N	pending	\N	\N	\N	\N
14	Болдырев Максим Романович	2003-03-19	asd	student	asd	1	maxaaa	$2y$12$1bjYwQK39JrB60R2G8ulrODe8AAoQEiGVW0H.r9st0x6dvozqScuG	\N	pending	\N	\N	\N	\N
15	Болдырев Максим Романович	2003-03-19	ысвм	student	длоит	5	cat1	$2y$12$vLBn1cyuIjtT2/obwCxkZu0SbvFN.oUs/wCbwhvoGvm1F2KIlVOJW	\N	pending	\N	\N	\N	\N
16	asdsadas	0203-03-19	kjb	student	kjb	2	maxi	$2y$12$.QZy.iCVW5OXcrJG1G1jyuIXiP3eTSoX4bVcdIPxO5wWdPsyRw1.O	\N	pending	\N	\N	\N	\N
17	Гаршина Юлия	2003-03-19	asd	student	sad	3	max	$2y$12$jiZgCcQVQCS/0cZoz/9xkOddBOEo7rF/n/QmYGhYJfhWdKcpXbD1C	\N	pending	\N	\N	\N	\N
18	Гаршина Юлия	2003-03-19	фыа	student	фыва	2	rox2	$2y$12$.dAcyngBQg7FtyWNKLg8fe2Ba4NFx14M6CuRsMQFJajRr..gJYN6O	\N	pending	\N	\N	\N	\N
19	asdasdasd	2003-03-19	asd	student	asd	2	okak	$2y$12$Y3XpprFz4xhnXxGFcxyrmuv0/4fH.corayejUPn5bjmGXRWXAKL6W	\N	pending	\N	\N	\N	\N
20	Болдырев Максим Романович	2003-03-19	asd	student	asd	3	max1	$2y$12$DesbJupvhqEH7hsFhMXo3uOTvrrelfSA.rIrXQD4m0vsgx5KnZoGa	\N	pending	\N	\N	\N	\N
21	Maxim Boldyrev	2003-03-19	ЛГТУ	student	Информатика	1	oops	$2y$12$O4xRZxgvK/WJPH0JKq9XV.YtyN7eGZjqDNu3ZwBh5wHDnLNYbknlu	\N	pending	\N	\N	\N	\N
22	фывфывфыв	2003-03-19	ыва	student	выа	1	ooooo	$2y$12$UCh6tvfpNQJoS78o0Xaj8uOaXBb/.KBSHTPBXyS96vQp0YUR60Jf2	\N	pending	\N	\N	\N	\N
1	Гаршина Юлия	2003-03-19	ЛГТУ	admin	Инфа	3	garshina	$2y$12$XhKZi1qqaOMi7UFQgTsCluemygyw3INk6V/k9y9XuNEgshqnJYHG6	\N	approved	\N	\N	\N	\N
23	Гаршина Юлия	2003-03-19	dsf	student	dsf	1	garshina@mail.ru	$2y$12$cSKjisnLBIeloMpDWHwJJeTQMIC/Uas5.0Ufpzkin0LGhr2FL65iO	\N	approved		uploads/students/student_card_683dbca51878e_default.jpg	\N	\N
24	Гаршина Юлия	2003-03-19	ыфва	student	фыва	1	maxboltik@mail.ru	$2y$12$faxrVU94tln97uCpaz4tP.9RsyCkrnjHkm1lFJq8VixJKA5tPeW5u	\N	approved	Все круто	uploads/students/student_card_683dc1763deda_default.jpg	\N	\N
25	Болдырев Максим Романович	2003-03-19	asfd	student	sdf	3	yaz678@bk.ru	$2y$12$RgaPVqclsFdSKX6RvPKaoe/whNqAV.SpN6SvgO2MsfFV5oFt6hyQW	\N	rejected	Все некруто	uploads/students/student_card_683dc20addef0_Приказ КС.pdf	\N	\N
26	фыфыфы	2003-03-19	вап	teacher	вап	2	maxrox1904@gmail.com	$2y$12$Qm1TDSSRSkH38LA9jK5Ne.Zn.T6pDx02f6PRnU5VHxmqv3W8KxR76	uploads/teachers/criminal/criminal_683dc32d567f7_rNo_c2bRwys.jpg	approved		\N	uploads/teachers/passport/passport_683dc32d567f0_diplom.sql	uploads/teachers/diploma/diploma_683dc32d567f6_Приказ КС.pdf
27	Пупкин Кирилл Васильевич	2003-03-19	ыфва	teacher	ваып	2	asdsadasD@mail.ru	$2y$12$2T9g.ZfYsYaewsakcw20NO.ioKXZBJ2yC9SKTBffgc17cjZJFkCI6	uploads/teachers/criminal/criminal_683dc7568fa22_default.jpg	pending	\N	\N	uploads/teachers/passport/passport_683dc7568fa1b_default.jpg	uploads/teachers/diploma/diploma_683dc7568fa20_0QwgmBt4IN8.jpg
\.


--
-- TOC entry 3659 (class 0 OID 0)
-- Dependencies: 217
-- Name: answer_options_id_option_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answer_options_id_option_seq', 45, true);


--
-- TOC entry 3660 (class 0 OID 0)
-- Dependencies: 219
-- Name: answers_id_answer_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answers_id_answer_seq', 1, false);


--
-- TOC entry 3661 (class 0 OID 0)
-- Dependencies: 243
-- Name: certificates_id_certificate_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.certificates_id_certificate_seq', 1, true);


--
-- TOC entry 3662 (class 0 OID 0)
-- Dependencies: 232
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.code_tasks_id_ct_seq', 1, false);


--
-- TOC entry 3663 (class 0 OID 0)
-- Dependencies: 234
-- Name: course_id_course_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.course_id_course_seq', 22, true);


--
-- TOC entry 3664 (class 0 OID 0)
-- Dependencies: 237
-- Name: feedback_id_feedback_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.feedback_id_feedback_seq', 11, true);


--
-- TOC entry 3665 (class 0 OID 0)
-- Dependencies: 239
-- Name: lessons_id_lesson_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.lessons_id_lesson_seq', 28, true);


--
-- TOC entry 3666 (class 0 OID 0)
-- Dependencies: 222
-- Name: questions_id_question_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.questions_id_question_seq', 29, true);


--
-- TOC entry 3667 (class 0 OID 0)
-- Dependencies: 224
-- Name: results_id_result_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.results_id_result_seq', 31, true);


--
-- TOC entry 3668 (class 0 OID 0)
-- Dependencies: 226
-- Name: stat_id_stat_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.stat_id_stat_seq', 1, false);


--
-- TOC entry 3669 (class 0 OID 0)
-- Dependencies: 228
-- Name: steps_id_step_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.steps_id_step_seq', 62, true);


--
-- TOC entry 3670 (class 0 OID 0)
-- Dependencies: 248
-- Name: test_answers_id_answer_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_answers_id_answer_seq', 47, true);


--
-- TOC entry 3671 (class 0 OID 0)
-- Dependencies: 246
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_attempts_id_attempt_seq', 21, true);


--
-- TOC entry 3672 (class 0 OID 0)
-- Dependencies: 230
-- Name: tests_id_test_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.tests_id_test_seq', 22, true);


--
-- TOC entry 3673 (class 0 OID 0)
-- Dependencies: 241
-- Name: users_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.users_id_user_seq', 27, true);


--
-- TOC entry 3422 (class 2606 OID 24827)
-- Name: certificates certificates_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_pkey PRIMARY KEY (id_certificate);


--
-- TOC entry 3363 (class 2606 OID 24584)
-- Name: answer_options pk_answer_options; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT pk_answer_options PRIMARY KEY (id_option);


--
-- TOC entry 3368 (class 2606 OID 24593)
-- Name: answers pk_answers; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT pk_answers PRIMARY KEY (id_answer);


--
-- TOC entry 3399 (class 2606 OID 24668)
-- Name: code_tasks pk_code_tasks; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT pk_code_tasks PRIMARY KEY (id_ct);


--
-- TOC entry 3402 (class 2606 OID 24679)
-- Name: course pk_course; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course
    ADD CONSTRAINT pk_course PRIMARY KEY (id_course);


--
-- TOC entry 3408 (class 2606 OID 24685)
-- Name: create_passes pk_create_passes; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT pk_create_passes PRIMARY KEY (id_course, id_user);


--
-- TOC entry 3412 (class 2606 OID 24697)
-- Name: feedback pk_feedback; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT pk_feedback PRIMARY KEY (id_feedback);


--
-- TOC entry 3416 (class 2606 OID 24708)
-- Name: lessons pk_lessons; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT pk_lessons PRIMARY KEY (id_lesson);


--
-- TOC entry 3372 (class 2606 OID 24908)
-- Name: material pk_material; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT pk_material PRIMARY KEY (id_material);


--
-- TOC entry 3375 (class 2606 OID 24614)
-- Name: questions pk_questions; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT pk_questions PRIMARY KEY (id_question);


--
-- TOC entry 3379 (class 2606 OID 24623)
-- Name: results pk_results; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT pk_results PRIMARY KEY (id_result);


--
-- TOC entry 3386 (class 2606 OID 24633)
-- Name: stat pk_stat; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT pk_stat PRIMARY KEY (id_stat);


--
-- TOC entry 3390 (class 2606 OID 24646)
-- Name: steps pk_steps; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT pk_steps PRIMARY KEY (id_step);


--
-- TOC entry 3394 (class 2606 OID 24657)
-- Name: tests pk_tests; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT pk_tests PRIMARY KEY (id_test);


--
-- TOC entry 3419 (class 2606 OID 24720)
-- Name: users pk_users; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT pk_users PRIMARY KEY (id_user);


--
-- TOC entry 3431 (class 2606 OID 24890)
-- Name: test_answers test_answers_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_pkey PRIMARY KEY (id_answer);


--
-- TOC entry 3426 (class 2606 OID 24872)
-- Name: test_attempts test_attempts_id_test_id_user_start_time_key; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_test_id_user_start_time_key UNIQUE (id_test, id_user, start_time);


--
-- TOC entry 3428 (class 2606 OID 24870)
-- Name: test_attempts test_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_pkey PRIMARY KEY (id_attempt);


--
-- TOC entry 3424 (class 2606 OID 24851)
-- Name: user_material_progress user_material_progress_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_pkey PRIMARY KEY (id_user, id_step);


--
-- TOC entry 3388 (class 1259 OID 24648)
-- Name: also_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX also_include_fk ON public.steps USING btree (id_lesson);


--
-- TOC entry 3360 (class 1259 OID 24585)
-- Name: answer_options_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answer_options_pk ON public.answer_options USING btree (id_option);


--
-- TOC entry 3364 (class 1259 OID 24594)
-- Name: answers_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answers_pk ON public.answers USING btree (id_answer);


--
-- TOC entry 3365 (class 1259 OID 24596)
-- Name: asnwers_to_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX asnwers_to_fk ON public.answers USING btree (id_user);


--
-- TOC entry 3366 (class 1259 OID 24595)
-- Name: assume_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX assume_fk ON public.answers USING btree (id_question);


--
-- TOC entry 3396 (class 1259 OID 24669)
-- Name: code_tasks_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX code_tasks_pk ON public.code_tasks USING btree (id_ct);


--
-- TOC entry 3382 (class 1259 OID 24637)
-- Name: counts_from_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX counts_from_fk ON public.stat USING btree (id_result);


--
-- TOC entry 3400 (class 1259 OID 24680)
-- Name: course_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX course_pk ON public.course USING btree (id_course);


--
-- TOC entry 3403 (class 1259 OID 24687)
-- Name: create_passes2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes2_fk ON public.create_passes USING btree (id_user);


--
-- TOC entry 3404 (class 1259 OID 24688)
-- Name: create_passes_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes_fk ON public.create_passes USING btree (id_course);


--
-- TOC entry 3405 (class 1259 OID 24686)
-- Name: create_passes_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX create_passes_pk ON public.create_passes USING btree (id_course, id_user);


--
-- TOC entry 3409 (class 1259 OID 24698)
-- Name: feedback_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX feedback_pk ON public.feedback USING btree (id_feedback);


--
-- TOC entry 3383 (class 1259 OID 24636)
-- Name: goes_into_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX goes_into_fk ON public.stat USING btree (id_course);


--
-- TOC entry 3377 (class 1259 OID 24625)
-- Name: goes_to_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX goes_to_fk ON public.results USING btree (id_answer);


--
-- TOC entry 3410 (class 1259 OID 24699)
-- Name: has_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_fk ON public.feedback USING btree (id_course);


--
-- TOC entry 3384 (class 1259 OID 24635)
-- Name: has_in_courses_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_in_courses_fk ON public.stat USING btree (id_user);


--
-- TOC entry 3406 (class 1259 OID 24845)
-- Name: idx_create_passes_date_complete; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_create_passes_date_complete ON public.create_passes USING btree (date_complete);


--
-- TOC entry 3429 (class 1259 OID 24906)
-- Name: idx_test_answers_attempt; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_answers_attempt ON public.test_answers USING btree (id_attempt);


--
-- TOC entry 3413 (class 1259 OID 24710)
-- Name: include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX include_fk ON public.lessons USING btree (id_course);


--
-- TOC entry 3414 (class 1259 OID 24709)
-- Name: lessons_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX lessons_pk ON public.lessons USING btree (id_lesson);


--
-- TOC entry 3369 (class 1259 OID 24909)
-- Name: material_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX material_pk ON public.material USING btree (id_material);


--
-- TOC entry 3392 (class 1259 OID 24659)
-- Name: may_also_include2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_also_include2_fk ON public.tests USING btree (id_step);


--
-- TOC entry 3370 (class 1259 OID 24605)
-- Name: may_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_include_fk ON public.material USING btree (id_step);


--
-- TOC entry 3373 (class 1259 OID 24616)
-- Name: mean_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX mean_fk ON public.questions USING btree (id_test);


--
-- TOC entry 3397 (class 1259 OID 24670)
-- Name: might_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX might_include_fk ON public.code_tasks USING btree (id_question);


--
-- TOC entry 3361 (class 1259 OID 24586)
-- Name: must_have_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX must_have_fk ON public.answer_options USING btree (id_question);


--
-- TOC entry 3417 (class 1259 OID 24711)
-- Name: procent_pass_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX procent_pass_fk ON public.lessons USING btree (id_stat);


--
-- TOC entry 3376 (class 1259 OID 24615)
-- Name: questions_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX questions_pk ON public.questions USING btree (id_question);


--
-- TOC entry 3380 (class 1259 OID 24624)
-- Name: results_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX results_pk ON public.results USING btree (id_result);


--
-- TOC entry 3387 (class 1259 OID 24634)
-- Name: stat_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX stat_pk ON public.stat USING btree (id_stat);


--
-- TOC entry 3381 (class 1259 OID 24626)
-- Name: stats_in_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX stats_in_fk ON public.results USING btree (id_test);


--
-- TOC entry 3391 (class 1259 OID 24647)
-- Name: steps_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX steps_pk ON public.steps USING btree (id_step);


--
-- TOC entry 3395 (class 1259 OID 24658)
-- Name: tests_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX tests_pk ON public.tests USING btree (id_test);


--
-- TOC entry 3420 (class 1259 OID 24721)
-- Name: users_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX users_pk ON public.users USING btree (id_user);


--
-- TOC entry 3452 (class 2606 OID 24833)
-- Name: certificates certificates_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3453 (class 2606 OID 24828)
-- Name: certificates certificates_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3432 (class 2606 OID 24722)
-- Name: answer_options fk_answer_o_must_have_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT fk_answer_o_must_have_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3433 (class 2606 OID 24727)
-- Name: answers fk_answers_asnwers_t_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT fk_answers_asnwers_t_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3434 (class 2606 OID 24732)
-- Name: answers fk_answers_assume_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT fk_answers_assume_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3445 (class 2606 OID 24782)
-- Name: code_tasks fk_code_tas_might_inc_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT fk_code_tas_might_inc_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3446 (class 2606 OID 24787)
-- Name: create_passes fk_create_p_create_pa_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3447 (class 2606 OID 24792)
-- Name: create_passes fk_create_p_create_pa_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3448 (class 2606 OID 24797)
-- Name: feedback fk_feedback_has_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_has_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3449 (class 2606 OID 24815)
-- Name: feedback fk_feedback_user; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_user FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3450 (class 2606 OID 24802)
-- Name: lessons fk_lessons_include_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_include_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3451 (class 2606 OID 24807)
-- Name: lessons fk_lessons_procent_p_stat; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_procent_p_stat FOREIGN KEY (id_stat) REFERENCES public.stat(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3435 (class 2606 OID 24737)
-- Name: material fk_material_may_inclu_steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT fk_material_may_inclu_steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3436 (class 2606 OID 24742)
-- Name: questions fk_question_mean_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT fk_question_mean_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3437 (class 2606 OID 24747)
-- Name: results fk_results_goes_to_answers; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT fk_results_goes_to_answers FOREIGN KEY (id_answer) REFERENCES public.answers(id_answer) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3438 (class 2606 OID 24752)
-- Name: results fk_results_stats_in_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT fk_results_stats_in_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3440 (class 2606 OID 24757)
-- Name: stat fk_stat_counts_fr_results; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_counts_fr_results FOREIGN KEY (id_result) REFERENCES public.results(id_result) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3441 (class 2606 OID 24762)
-- Name: stat fk_stat_goes_into_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_goes_into_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3442 (class 2606 OID 24767)
-- Name: stat fk_stat_has_in_co_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_has_in_co_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3443 (class 2606 OID 24772)
-- Name: steps fk_steps_also_incl_lessons; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT fk_steps_also_incl_lessons FOREIGN KEY (id_lesson) REFERENCES public.lessons(id_lesson) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3444 (class 2606 OID 24777)
-- Name: tests fk_tests_may_also__steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT fk_tests_may_also__steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3439 (class 2606 OID 24840)
-- Name: results results_answer_fk; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT results_answer_fk FOREIGN KEY (id_answer) REFERENCES public.answers(id_answer) ON DELETE CASCADE;


--
-- TOC entry 3458 (class 2606 OID 24891)
-- Name: test_answers test_answers_id_attempt_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_attempt_fkey FOREIGN KEY (id_attempt) REFERENCES public.test_attempts(id_attempt);


--
-- TOC entry 3459 (class 2606 OID 24896)
-- Name: test_answers test_answers_id_question_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_question_fkey FOREIGN KEY (id_question) REFERENCES public.questions(id_question);


--
-- TOC entry 3460 (class 2606 OID 24901)
-- Name: test_answers test_answers_id_selected_option_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_selected_option_fkey FOREIGN KEY (id_selected_option) REFERENCES public.answer_options(id_option);


--
-- TOC entry 3456 (class 2606 OID 24873)
-- Name: test_attempts test_attempts_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test);


--
-- TOC entry 3457 (class 2606 OID 24878)
-- Name: test_attempts test_attempts_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3454 (class 2606 OID 24857)
-- Name: user_material_progress user_material_progress_id_step_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_id_step_fkey FOREIGN KEY (id_step) REFERENCES public.steps(id_step);


--
-- TOC entry 3455 (class 2606 OID 24852)
-- Name: user_material_progress user_material_progress_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user);


-- Completed on 2025-06-02 18:51:55

--
-- PostgreSQL database dump complete
--

