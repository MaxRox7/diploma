--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.0

-- Started on 2025-06-15 10:31:56

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
-- TOC entry 3705 (class 0 OID 0)
-- Dependencies: 217
-- Name: answer_options_id_option_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.answer_options_id_option_seq OWNED BY public.answer_options.id_option;


--
-- TOC entry 238 (class 1259 OID 24821)
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
-- TOC entry 237 (class 1259 OID 24820)
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
-- TOC entry 3706 (class 0 OID 0)
-- Dependencies: 237
-- Name: certificates_id_certificate_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.certificates_id_certificate_seq OWNED BY public.certificates.id_certificate;


--
-- TOC entry 227 (class 1259 OID 24661)
-- Name: code_tasks; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.code_tasks (
    id_ct integer NOT NULL,
    id_question integer,
    input_ct text NOT NULL,
    output_ct character varying(1024) NOT NULL,
    execution_timeout integer DEFAULT 5,
    template_code text,
    language character varying(20) DEFAULT 'php'::character varying,
    CONSTRAINT code_tasks_language_check CHECK (((language)::text = ANY ((ARRAY['php'::character varying, 'python'::character varying, 'cpp'::character varying])::text[])))
);


ALTER TABLE public.code_tasks OWNER TO pguser;

--
-- TOC entry 3707 (class 0 OID 0)
-- Dependencies: 227
-- Name: TABLE code_tasks; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON TABLE public.code_tasks IS 'Stores code tasks for programming questions with input template and expected output';


--
-- TOC entry 3708 (class 0 OID 0)
-- Dependencies: 227
-- Name: COLUMN code_tasks.input_ct; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.input_ct IS 'Input data or description for the code task';


--
-- TOC entry 3709 (class 0 OID 0)
-- Dependencies: 227
-- Name: COLUMN code_tasks.output_ct; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.output_ct IS 'Expected output that the code should produce';


--
-- TOC entry 3710 (class 0 OID 0)
-- Dependencies: 227
-- Name: COLUMN code_tasks.execution_timeout; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.execution_timeout IS 'Maximum execution time in seconds';


--
-- TOC entry 3711 (class 0 OID 0)
-- Dependencies: 227
-- Name: COLUMN code_tasks.template_code; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.template_code IS 'Starting template code provided to the student';


--
-- TOC entry 3712 (class 0 OID 0)
-- Dependencies: 227
-- Name: COLUMN code_tasks.language; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.language IS 'Programming language for the task (php, python, cpp)';


--
-- TOC entry 226 (class 1259 OID 24660)
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
-- TOC entry 3713 (class 0 OID 0)
-- Dependencies: 226
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.code_tasks_id_ct_seq OWNED BY public.code_tasks.id_ct;


--
-- TOC entry 229 (class 1259 OID 24672)
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
-- TOC entry 228 (class 1259 OID 24671)
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
-- TOC entry 3714 (class 0 OID 0)
-- Dependencies: 228
-- Name: course_id_course_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.course_id_course_seq OWNED BY public.course.id_course;


--
-- TOC entry 248 (class 1259 OID 41291)
-- Name: course_statistics; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.course_statistics (
    id_course integer NOT NULL,
    views_count integer DEFAULT 0,
    enrollment_count integer DEFAULT 0,
    completion_count integer DEFAULT 0,
    average_rating double precision DEFAULT 0,
    last_updated timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.course_statistics OWNER TO pguser;

--
-- TOC entry 246 (class 1259 OID 41260)
-- Name: course_tags; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.course_tags (
    id_course integer NOT NULL,
    id_tag integer NOT NULL
);


ALTER TABLE public.course_tags OWNER TO pguser;

--
-- TOC entry 250 (class 1259 OID 41307)
-- Name: course_views; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.course_views (
    id_view integer NOT NULL,
    id_course integer,
    id_user integer,
    view_timestamp timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.course_views OWNER TO pguser;

--
-- TOC entry 249 (class 1259 OID 41306)
-- Name: course_views_id_view_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.course_views_id_view_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.course_views_id_view_seq OWNER TO pguser;

--
-- TOC entry 3715 (class 0 OID 0)
-- Dependencies: 249
-- Name: course_views_id_view_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.course_views_id_view_seq OWNED BY public.course_views.id_view;


--
-- TOC entry 230 (class 1259 OID 24681)
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
-- TOC entry 232 (class 1259 OID 24690)
-- Name: feedback; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.feedback (
    id_feedback integer NOT NULL,
    id_course integer NOT NULL,
    text_feedback character varying(5000),
    date_feedback date NOT NULL,
    rate_feedback character varying(5) NOT NULL,
    id_user integer,
    status character varying(20) DEFAULT 'pending'::character varying
);


ALTER TABLE public.feedback OWNER TO pguser;

--
-- TOC entry 231 (class 1259 OID 24689)
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
-- TOC entry 3716 (class 0 OID 0)
-- Dependencies: 231
-- Name: feedback_id_feedback_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.feedback_id_feedback_seq OWNED BY public.feedback.id_feedback;


--
-- TOC entry 234 (class 1259 OID 24701)
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
-- TOC entry 233 (class 1259 OID 24700)
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
-- TOC entry 3717 (class 0 OID 0)
-- Dependencies: 233
-- Name: lessons_id_lesson_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.lessons_id_lesson_seq OWNED BY public.lessons.id_lesson;


--
-- TOC entry 219 (class 1259 OID 24597)
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
-- TOC entry 221 (class 1259 OID 24607)
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
-- TOC entry 220 (class 1259 OID 24606)
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
-- TOC entry 3718 (class 0 OID 0)
-- Dependencies: 220
-- Name: questions_id_question_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.questions_id_question_seq OWNED BY public.questions.id_question;


--
-- TOC entry 223 (class 1259 OID 24639)
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
-- TOC entry 222 (class 1259 OID 24638)
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
-- TOC entry 3719 (class 0 OID 0)
-- Dependencies: 222
-- Name: steps_id_step_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.steps_id_step_seq OWNED BY public.steps.id_step;


--
-- TOC entry 251 (class 1259 OID 41324)
-- Name: student_analytics; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.student_analytics (
    id_user integer NOT NULL,
    id_course integer NOT NULL,
    lessons_completed integer DEFAULT 0,
    total_lessons integer DEFAULT 0,
    tests_completed integer DEFAULT 0,
    total_tests integer DEFAULT 0,
    average_test_score double precision DEFAULT 0,
    last_activity timestamp without time zone,
    estimated_completion_date date
);


ALTER TABLE public.student_analytics OWNER TO pguser;

--
-- TOC entry 254 (class 1259 OID 41367)
-- Name: student_test_settings; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.student_test_settings (
    id_user integer NOT NULL,
    id_test integer NOT NULL,
    additional_attempts integer DEFAULT 0
);


ALTER TABLE public.student_test_settings OWNER TO pguser;

--
-- TOC entry 245 (class 1259 OID 41252)
-- Name: tags; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.tags (
    id_tag integer NOT NULL,
    name_tag character varying(100) NOT NULL
);


ALTER TABLE public.tags OWNER TO pguser;

--
-- TOC entry 244 (class 1259 OID 41251)
-- Name: tags_id_tag_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.tags_id_tag_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tags_id_tag_seq OWNER TO pguser;

--
-- TOC entry 3720 (class 0 OID 0)
-- Dependencies: 244
-- Name: tags_id_tag_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.tags_id_tag_seq OWNED BY public.tags.id_tag;


--
-- TOC entry 243 (class 1259 OID 24884)
-- Name: test_answers; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.test_answers (
    id_answer integer NOT NULL,
    id_attempt integer,
    id_question integer,
    id_selected_option integer,
    is_correct boolean,
    answer_time timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    answer_text text,
    ai_feedback text
);


ALTER TABLE public.test_answers OWNER TO pguser;

--
-- TOC entry 3721 (class 0 OID 0)
-- Dependencies: 243
-- Name: COLUMN test_answers.ai_feedback; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.test_answers.ai_feedback IS 'Отзыв ИИ о коде студента';


--
-- TOC entry 242 (class 1259 OID 24883)
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
-- TOC entry 3722 (class 0 OID 0)
-- Dependencies: 242
-- Name: test_answers_id_answer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_answers_id_answer_seq OWNED BY public.test_answers.id_answer;


--
-- TOC entry 241 (class 1259 OID 24863)
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
-- TOC entry 240 (class 1259 OID 24862)
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
-- TOC entry 3723 (class 0 OID 0)
-- Dependencies: 240
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_attempts_id_attempt_seq OWNED BY public.test_attempts.id_attempt;


--
-- TOC entry 253 (class 1259 OID 41352)
-- Name: test_grade_levels; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.test_grade_levels (
    id_level integer NOT NULL,
    id_test integer,
    min_percentage integer NOT NULL,
    max_percentage integer NOT NULL,
    grade_name character varying(50) NOT NULL,
    grade_color character varying(20) DEFAULT '#000000'::character varying,
    CONSTRAINT test_grade_levels_check CHECK ((min_percentage < max_percentage)),
    CONSTRAINT test_grade_levels_max_percentage_check CHECK (((max_percentage >= 0) AND (max_percentage <= 100))),
    CONSTRAINT test_grade_levels_min_percentage_check CHECK (((min_percentage >= 0) AND (min_percentage <= 100)))
);


ALTER TABLE public.test_grade_levels OWNER TO pguser;

--
-- TOC entry 252 (class 1259 OID 41351)
-- Name: test_grade_levels_id_level_seq; Type: SEQUENCE; Schema: public; Owner: pguser
--

CREATE SEQUENCE public.test_grade_levels_id_level_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.test_grade_levels_id_level_seq OWNER TO pguser;

--
-- TOC entry 3724 (class 0 OID 0)
-- Dependencies: 252
-- Name: test_grade_levels_id_level_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_grade_levels_id_level_seq OWNED BY public.test_grade_levels.id_level;


--
-- TOC entry 225 (class 1259 OID 24650)
-- Name: tests; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.tests (
    id_test integer NOT NULL,
    id_step integer NOT NULL,
    name_test character varying(255) NOT NULL,
    desc_test character varying(255),
    passing_percentage integer DEFAULT 70,
    max_attempts integer DEFAULT 3,
    time_between_attempts integer DEFAULT 0,
    show_results_after_completion boolean DEFAULT true,
    practice_mode boolean DEFAULT false,
    CONSTRAINT tests_max_attempts_check CHECK ((max_attempts > 0)),
    CONSTRAINT tests_passing_percentage_check CHECK (((passing_percentage >= 0) AND (passing_percentage <= 100)))
);


ALTER TABLE public.tests OWNER TO pguser;

--
-- TOC entry 224 (class 1259 OID 24649)
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
-- TOC entry 3725 (class 0 OID 0)
-- Dependencies: 224
-- Name: tests_id_test_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.tests_id_test_seq OWNED BY public.tests.id_test;


--
-- TOC entry 239 (class 1259 OID 24846)
-- Name: user_material_progress; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.user_material_progress (
    id_user integer NOT NULL,
    id_step integer NOT NULL,
    completed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_material_progress OWNER TO pguser;

--
-- TOC entry 247 (class 1259 OID 41275)
-- Name: user_tag_interests; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.user_tag_interests (
    id_user integer NOT NULL,
    id_tag integer NOT NULL,
    interest_weight double precision DEFAULT 1.0
);


ALTER TABLE public.user_tag_interests OWNER TO pguser;

--
-- TOC entry 236 (class 1259 OID 24713)
-- Name: users; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.users (
    id_user integer NOT NULL,
    fn_user character varying(255) NOT NULL,
    birth_user date NOT NULL,
    uni_user character varying(255) NOT NULL,
    role_user character varying(255) NOT NULL,
    spec_user character varying(255),
    year_user integer,
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
-- TOC entry 235 (class 1259 OID 24712)
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
-- TOC entry 3726 (class 0 OID 0)
-- Dependencies: 235
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- TOC entry 3355 (class 2604 OID 24582)
-- Name: answer_options id_option; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options ALTER COLUMN id_option SET DEFAULT nextval('public.answer_options_id_option_seq'::regclass);


--
-- TOC entry 3377 (class 2604 OID 24824)
-- Name: certificates id_certificate; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates ALTER COLUMN id_certificate SET DEFAULT nextval('public.certificates_id_certificate_seq'::regclass);


--
-- TOC entry 3366 (class 2604 OID 24664)
-- Name: code_tasks id_ct; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks ALTER COLUMN id_ct SET DEFAULT nextval('public.code_tasks_id_ct_seq'::regclass);


--
-- TOC entry 3369 (class 2604 OID 24675)
-- Name: course id_course; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course ALTER COLUMN id_course SET DEFAULT nextval('public.course_id_course_seq'::regclass);


--
-- TOC entry 3391 (class 2604 OID 41310)
-- Name: course_views id_view; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views ALTER COLUMN id_view SET DEFAULT nextval('public.course_views_id_view_seq'::regclass);


--
-- TOC entry 3372 (class 2604 OID 24693)
-- Name: feedback id_feedback; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback ALTER COLUMN id_feedback SET DEFAULT nextval('public.feedback_id_feedback_seq'::regclass);


--
-- TOC entry 3374 (class 2604 OID 24704)
-- Name: lessons id_lesson; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons ALTER COLUMN id_lesson SET DEFAULT nextval('public.lessons_id_lesson_seq'::regclass);


--
-- TOC entry 3356 (class 2604 OID 24610)
-- Name: questions id_question; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions ALTER COLUMN id_question SET DEFAULT nextval('public.questions_id_question_seq'::regclass);


--
-- TOC entry 3357 (class 2604 OID 24642)
-- Name: steps id_step; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps ALTER COLUMN id_step SET DEFAULT nextval('public.steps_id_step_seq'::regclass);


--
-- TOC entry 3384 (class 2604 OID 41255)
-- Name: tags id_tag; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags ALTER COLUMN id_tag SET DEFAULT nextval('public.tags_id_tag_seq'::regclass);


--
-- TOC entry 3382 (class 2604 OID 24887)
-- Name: test_answers id_answer; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers ALTER COLUMN id_answer SET DEFAULT nextval('public.test_answers_id_answer_seq'::regclass);


--
-- TOC entry 3380 (class 2604 OID 24866)
-- Name: test_attempts id_attempt; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts ALTER COLUMN id_attempt SET DEFAULT nextval('public.test_attempts_id_attempt_seq'::regclass);


--
-- TOC entry 3398 (class 2604 OID 41355)
-- Name: test_grade_levels id_level; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels ALTER COLUMN id_level SET DEFAULT nextval('public.test_grade_levels_id_level_seq'::regclass);


--
-- TOC entry 3360 (class 2604 OID 24653)
-- Name: tests id_test; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests ALTER COLUMN id_test SET DEFAULT nextval('public.tests_id_test_seq'::regclass);


--
-- TOC entry 3375 (class 2604 OID 24716)
-- Name: users id_user; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users ALTER COLUMN id_user SET DEFAULT nextval('public.users_id_user_seq'::regclass);


--
-- TOC entry 3663 (class 0 OID 24579)
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
48	30	ctrl+d
49	30	ctrl+f
50	31	я
51	31	ты
52	31	он
53	32	я
54	32	ты
55	33	1||1
56	33	2||2
57	35	через переменную $_POST
58	35	Через переменную $_GET.
59	35	Через переменную $_SESSION.
60	35	Через переменную $_REQUEST.
61	36	через переменную $_POST
62	36	Через переменную $_GET.
63	36	Через переменную $_SESSION.
64	36	Через переменную $_REQUEST.
65	37	через переменную $_POST
66	37	Через переменную $_GET.
67	37	Через переменную $_SESSION.
68	37	Через переменную $_REQUEST.
69	38	через переменную $_POST
70	38	Через переменную $_GET.
71	38	Через переменную $_SESSION.
72	38	Через переменную $_REQUEST.
73	39	через переменную $_POST
74	39	Через переменную $_GET.
75	39	Через переменную $_SESSION.
76	39	Через переменную $_REQUEST.
77	40	через переменную $_POST
78	40	Через переменную $_GET.
79	40	Через переменную $_SESSION.
80	40	Через переменную $_REQUEST.
81	41	через переменную $_POST
82	41	Через переменную $_GET.
83	41	Через переменную $_SESSION.
84	41	Через переменную $_REQUEST.
85	42	через переменную $_POST
86	42	Через переменную $_GET.
87	42	Через переменную $_SESSION.
88	42	Через переменную $_REQUEST.
89	43	через переменную $_POST
90	43	Через переменную $_GET.
91	43	Через переменную $_SESSION.
92	43	Через переменную $_REQUEST.
93	44	через переменную $_POST
94	44	Через переменную $_GET.
95	44	Через переменную $_SESSION.
96	44	Через переменную $_REQUEST.
97	45	через переменную $_POST
98	45	Через переменную $_GET.
99	45	Через переменную $_SESSION.
100	45	Через переменную $_REQUEST.
101	46	через переменную $_POST
102	46	Через переменную $_GET.
103	46	Через переменную $_SESSION.
104	46	Через переменную $_REQUEST.
105	47	через переменную $_POST
106	47	Через переменную $_GET.
107	47	Через переменную $_SESSION.
108	47	Через переменную $_REQUEST.
109	48	через переменную $_POST
110	48	Через переменную $_GET.
111	48	Через переменную $_SESSION.
112	48	Через переменную $_REQUEST.
113	49	через переменную $_POST
114	49	Через переменную $_GET.
115	49	Через переменную $_SESSION.
116	49	Через переменную $_REQUEST.
117	50	через переменную $_POST
118	50	Через переменную $_GET.
119	50	Через переменную $_SESSION.
120	50	Через переменную $_REQUEST.
121	51	через переменную $_POST
122	51	Через переменную $_GET.
123	51	Через переменную $_SESSION.
124	51	Через переменную $_REQUEST.
125	52	через переменную $_POST
126	52	Через переменную $_GET.
127	52	Через переменную $_SESSION.
128	52	Через переменную $_REQUEST.
129	53	через переменную $_POST
130	53	Через переменную $_GET.
131	53	Через переменную $_SESSION.
132	53	Через переменную $_REQUEST.
133	54	через переменную $_POST
134	54	Через переменную $_GET.
135	54	Через переменную $_SESSION.
136	54	Через переменную $_REQUEST.
137	55	через переменную $_POST
138	55	Через переменную $_GET.
139	55	Через переменную $_SESSION.
140	55	Через переменную $_REQUEST.
141	56	1||1
142	56	2||2
143	66	я
144	66	Ты
145	66	Он
146	66	Мы
147	67	я
148	67	Я
149	67	Ты
150	67	Он/она/оно (например, животное или предмет)
151	68	1||1
152	68	2||2
157	73	$_POST
158	73	$_GET
159	73	$POST
160	73	$_SESSION
161	74	BMW
162	74	Toyota
163	74	Mercedes-Benz
164	74	Ford
\.


--
-- TOC entry 3683 (class 0 OID 24821)
-- Dependencies: 238
-- Data for Name: certificates; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.certificates (id_certificate, id_user, id_course, date_issued, certificate_path) FROM stdin;
1	1	8	2025-05-30 12:23:47.581266	certificates/cert_6839a3538d70d.pdf
\.


--
-- TOC entry 3672 (class 0 OID 24661)
-- Dependencies: 227
-- Data for Name: code_tasks; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.code_tasks (id_ct, id_question, input_ct, output_ct, execution_timeout, template_code, language) FROM stdin;
1	57		'Сумма массива: 15',	5	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array);\r\n?>',	php
2	58		# Ожидаемый вывод: 15	5	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Ожидаемый вывод: 15	python
3	59		 Ожидаемый вывод: 15	5	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	cpp
4	60		15	5	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	php
5	61		Сумма элементов массива: 15	5	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	cpp
6	62		15	5	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	python
7	63		hello world	5	<?php\r\n// Ваш код здесь\r\n?>	php
8	64		hello world	5	# Ваш код здесь	python
9	65		hello world	5	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	cpp
10	70		15	5	<?php\r\n\r\nfunction sumOfFourElements($arr) {\r\n    // Ваш код здесь\r\n    return null;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfFourElements($array);\r\necho $result;\r\n?>	php
11	71		hello world	5	# Напишите ваш код здесь	python
12	72		Результат: 8	5	#include <iostream>\r\n\r\nint main() {\r\n    int a = 3;\r\n    int b = 5;\r\n    int res;\r\n\r\n    // Здесь должно быть решение\r\n\r\n    std::cout << "Результат: " << res << std::endl;\r\n    return 0;\r\n}	cpp
13	75		15	5	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	php
\.


--
-- TOC entry 3674 (class 0 OID 24672)
-- Dependencies: 229
-- Data for Name: course; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course (id_course, name_course, desc_course, with_certificate, hourse_course, requred_year, required_spec, required_uni, level_course, tags_course, status_course, moderation_comment) FROM stdin;
27	фыфы	фыфы	f	12	\N	\N	\N	\N	фы	approved	as
28	Курс с тестом	курс	f	12	\N	\N	\N	\N	php, c++, python	approved	\N
29	Курс по php	Курс по php	f	1	\N	\N	\N	\N	PHP	approved	
30	Курс для тегов	1111	f	12	\N	\N	\N	\N	PHP, JavaScript, Web-разработка,	approved	
31	sdfsdfsdf	sdfsdfsdf	f	1	\N	\N	\N	\N	PHP, JavaScript, Web-разработка	approved	ываыва
16	fxg	dfg	f	1	\N	\N	\N	\N	dfg	pending	\N
17	курс	курс	f	1	\N	\N	\N	\N	12	pending	\N
18	asd	asd	t	1	\N	\N	\N	\N	asd	pending	\N
20	asd	asd	f	12	\N	\N	\N	\N	12	pending	\N
21	ytuytu	tyutyu	t	12	1	Информатика	\N	beginner	dfg	pending	\N
8	jjjjjjjjj	jjjjjjjjjjj	t	1	\N	\N	\N	\N	php, web	pending	\N
22	фыфыфы	12	f	12	\N	\N	\N	\N	12	pending	asasas
19	Курс для крутой проверки	Этот курс я щас сделаю круто и полноценно	f	5	\N	\N	\N	\N	php, web. krutyak	pending	фыфыфы
23	курс макс рокс	авп	f	1	\N	\N	\N	\N	2	approved	фы
25	asasas	12	f	12	\N	\N	\N	\N	1	approved	фыфы
24	Фигма с нуля	Будем учиться на баннерах с ватой	t	36	\N	\N	\N	\N	дизайн, сайты, фигма	approved	Я тебя люблю
26	тест курс	выаыва	f	12	\N	\N	\N	\N	php	approved	Я люблю тебя
\.


--
-- TOC entry 3693 (class 0 OID 41291)
-- Dependencies: 248
-- Data for Name: course_statistics; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course_statistics (id_course, views_count, enrollment_count, completion_count, average_rating, last_updated) FROM stdin;
22	0	1	0	0	2025-06-08 13:42:54.10812
16	0	6	0	0	2025-06-08 13:42:54.10812
19	0	5	0	0	2025-06-08 13:42:54.10812
21	0	2	0	0	2025-06-08 13:42:54.10812
17	0	11	0	0	2025-06-08 13:42:54.10812
20	0	5	0	0	2025-06-08 13:42:54.10812
18	0	11	0	0	2025-06-08 13:42:54.10812
8	0	9	0	0	2025-06-08 13:42:54.10812
24	13	23	0	0	2025-06-08 12:33:31.764304
23	12	4	0	0	2025-06-08 14:01:48.877959
29	30	0	0	0	2025-06-08 14:02:03.48284
26	5	22	0	0	2025-06-08 11:48:22.256336
28	14	4	0	0	2025-06-08 14:04:03.181705
25	8	3	0	0	2025-06-08 14:11:06.631032
30	22	0	0	0	2025-06-13 10:43:31.259927
31	45	0	0	0	2025-06-13 11:21:28.76743
27	3	21	0	0	2025-06-08 12:31:29.009343
\.


--
-- TOC entry 3691 (class 0 OID 41260)
-- Dependencies: 246
-- Data for Name: course_tags; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course_tags (id_course, id_tag) FROM stdin;
29	5
30	5
30	6
30	2
\.


--
-- TOC entry 3695 (class 0 OID 41307)
-- Dependencies: 250
-- Data for Name: course_views; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course_views (id_view, id_course, id_user, view_timestamp) FROM stdin;
1	29	28	2025-06-08 10:54:43.95921
2	29	7	2025-06-08 10:55:21.223642
3	29	1	2025-06-08 10:55:49.568187
4	28	7	2025-06-08 10:55:56.65992
5	28	7	2025-06-08 10:56:00.211912
6	29	7	2025-06-08 10:56:05.676625
7	29	7	2025-06-08 10:56:06.25426
8	29	7	2025-06-08 10:56:06.995175
9	29	7	2025-06-08 10:56:09.984019
10	29	7	2025-06-08 10:56:13.709126
11	29	28	2025-06-08 10:57:04.198136
12	29	7	2025-06-08 10:57:48.193084
13	29	28	2025-06-08 10:58:08.478602
14	29	28	2025-06-08 10:59:10.29828
15	25	7	2025-06-08 11:14:33.022613
16	26	7	2025-06-08 11:14:36.225059
17	26	7	2025-06-08 11:14:38.129234
18	29	34	2025-06-08 11:15:43.388988
19	29	34	2025-06-08 11:15:44.73778
20	29	34	2025-06-08 11:15:45.620318
21	29	34	2025-06-08 11:15:51.016307
22	24	34	2025-06-08 11:21:34.04048
23	24	34	2025-06-08 11:21:34.62716
24	24	34	2025-06-08 11:22:17.2261
25	24	34	2025-06-08 11:23:16.746324
26	29	35	2025-06-08 11:35:29.981214
27	29	35	2025-06-08 11:35:30.89513
28	24	35	2025-06-08 11:42:44.529348
29	23	35	2025-06-08 11:42:48.854694
30	30	35	2025-06-08 11:47:10.743362
31	30	35	2025-06-08 11:47:11.480418
32	30	35	2025-06-08 11:47:40.183739
33	30	35	2025-06-08 11:47:46.265951
34	30	35	2025-06-08 11:47:48.585707
35	26	35	2025-06-08 11:48:20.932286
36	26	35	2025-06-08 11:48:21.395825
37	26	35	2025-06-08 11:48:22.216772
38	24	28	2025-06-08 11:52:26.507284
39	24	28	2025-06-08 11:52:31.312183
40	31	28	2025-06-08 11:52:48.750262
41	24	35	2025-06-08 12:03:32.135821
42	24	35	2025-06-08 12:03:33.128241
43	24	35	2025-06-08 12:03:34.02155
44	24	28	2025-06-08 12:05:13.39857
45	23	35	2025-06-08 12:30:12.718834
46	23	35	2025-06-08 12:30:13.188854
47	23	35	2025-06-08 12:30:13.942397
48	23	35	2025-06-08 12:30:16.243829
49	25	35	2025-06-08 12:30:19.937452
50	25	35	2025-06-08 12:30:20.428678
51	25	35	2025-06-08 12:30:21.166716
52	31	35	2025-06-08 12:30:46.868301
53	31	35	2025-06-08 12:30:47.532763
54	31	35	2025-06-08 12:30:48.215952
55	27	35	2025-06-08 12:31:27.654962
56	27	35	2025-06-08 12:31:28.133532
57	27	35	2025-06-08 12:31:29.006124
58	31	35	2025-06-08 12:32:04.802515
59	30	35	2025-06-08 12:33:10.975076
60	29	35	2025-06-08 12:33:13.366998
61	28	35	2025-06-08 12:33:15.41023
62	28	35	2025-06-08 12:33:21.109765
63	24	35	2025-06-08 12:33:26.341394
64	24	35	2025-06-08 12:33:31.761338
65	31	35	2025-06-08 12:33:33.576381
66	30	2	2025-06-08 12:33:52.901758
67	30	2	2025-06-08 12:33:53.713369
68	30	2	2025-06-08 12:33:54.495
69	30	2	2025-06-08 12:33:56.518921
70	30	2	2025-06-08 12:33:58.595093
71	29	2	2025-06-08 12:34:01.215481
72	29	2	2025-06-08 12:34:01.763796
73	29	2	2025-06-08 12:34:02.416958
74	29	2	2025-06-08 12:34:05.559755
75	30	2	2025-06-08 12:34:09.248656
76	31	2	2025-06-08 12:34:11.016527
77	31	2	2025-06-08 12:34:11.726736
78	31	2	2025-06-08 12:34:12.278774
79	31	2	2025-06-08 12:34:33.280105
80	31	2	2025-06-08 12:43:11.563925
81	28	2	2025-06-08 12:43:13.683833
82	28	2	2025-06-08 12:43:14.172322
83	28	2	2025-06-08 12:43:16.221361
84	31	2	2025-06-08 12:55:48.397232
85	31	2	2025-06-08 12:59:35.737963
86	31	2	2025-06-08 13:09:14.098807
87	31	2	2025-06-08 13:09:15.099028
88	31	2	2025-06-08 13:16:03.082482
89	31	2	2025-06-08 13:16:50.501325
90	28	2	2025-06-08 13:16:53.935545
91	29	6	2025-06-08 13:22:20.191206
92	29	6	2025-06-08 13:22:20.683172
93	29	6	2025-06-08 13:22:21.469342
94	23	6	2025-06-08 13:22:27.401603
95	23	6	2025-06-08 13:22:28.030236
96	23	6	2025-06-08 13:22:28.571208
97	23	6	2025-06-08 13:22:30.015478
98	30	6	2025-06-08 13:22:31.422747
99	30	6	2025-06-08 13:22:31.911988
100	30	6	2025-06-08 13:22:32.556263
101	30	6	2025-06-08 13:22:34.032905
102	31	6	2025-06-08 13:22:36.334856
103	31	6	2025-06-08 13:22:36.765482
104	31	6	2025-06-08 13:22:37.426327
105	25	6	2025-06-08 13:39:55.653195
106	25	6	2025-06-08 13:39:56.211059
107	25	6	2025-06-08 13:39:57.199826
108	31	6	2025-06-08 13:40:20.15804
109	31	6	2025-06-08 13:40:21.992374
110	28	6	2025-06-08 13:40:24.805788
111	28	6	2025-06-08 13:40:28.876581
112	31	9	2025-06-08 13:40:42.996886
113	31	9	2025-06-08 13:40:43.426658
114	31	9	2025-06-08 13:40:44.08709
115	31	9	2025-06-08 13:40:55.815372
116	31	9	2025-06-08 13:40:59.597951
117	31	8	2025-06-08 13:41:07.510984
118	31	8	2025-06-08 13:41:07.948615
119	31	8	2025-06-08 13:41:08.549141
120	31	8	2025-06-08 13:41:21.832671
121	31	28	2025-06-08 13:42:19.489649
122	31	28	2025-06-08 13:42:21.775192
123	31	8	2025-06-08 13:44:49.767148
124	31	22	2025-06-08 13:45:01.900162
125	31	22	2025-06-08 13:45:02.342952
126	31	22	2025-06-08 13:45:03.354587
127	31	22	2025-06-08 13:45:24.36863
128	31	22	2025-06-08 13:48:33.751626
129	31	15	2025-06-08 13:48:51.188666
130	31	15	2025-06-08 13:48:51.772411
131	31	15	2025-06-08 13:48:52.463631
132	23	15	2025-06-08 14:01:47.534514
133	23	15	2025-06-08 14:01:48.234358
134	23	15	2025-06-08 14:01:48.875265
135	29	15	2025-06-08 14:01:54.671128
136	28	15	2025-06-08 14:02:00.168985
137	29	15	2025-06-08 14:02:02.134544
138	29	15	2025-06-08 14:02:02.671439
139	29	15	2025-06-08 14:02:03.479399
140	30	15	2025-06-08 14:02:08.385405
141	30	15	2025-06-08 14:02:11.136598
142	30	15	2025-06-08 14:02:13.574778
143	30	15	2025-06-08 14:03:32.714605
144	30	15	2025-06-08 14:03:34.522852
145	28	15	2025-06-08 14:04:01.91286
146	28	15	2025-06-08 14:04:02.410501
147	28	15	2025-06-08 14:04:03.178701
148	25	15	2025-06-08 14:11:06.62787
149	31	28	2025-06-08 14:25:17.173232
150	31	6	2025-06-08 14:33:32.496096
151	30	1	2025-06-13 10:43:31.254248
152	31	38	2025-06-13 11:21:28.728198
\.


--
-- TOC entry 3675 (class 0 OID 24681)
-- Dependencies: 230
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
23	26	t	\N
18	1	t	\N
23	28	f	2025-06-03 10:01:17.354284
24	28	t	\N
23	2	f	\N
18	15	f	\N
17	9	f	\N
17	3	f	\N
16	3	f	\N
8	3	f	\N
17	15	f	\N
23	9	f	2025-06-03 10:36:33.785426
25	28	t	\N
25	17	f	2025-06-03 10:38:37.078964
25	2	f	\N
24	2	f	\N
24	9	f	\N
24	3	f	\N
24	10	f	\N
24	6	f	\N
24	8	f	\N
24	7	f	\N
24	12	f	\N
8	1	t	\N
24	15	f	\N
8	8	f	\N
24	19	f	\N
24	25	f	\N
24	20	f	\N
8	10	f	\N
24	22	f	\N
24	18	f	\N
24	13	f	\N
24	16	f	\N
8	11	f	\N
24	29	f	2025-06-04 15:04:59.181412
24	24	f	\N
24	30	f	\N
24	31	f	\N
26	28	t	\N
26	2	f	2025-06-05 09:57:07.87469
26	9	f	2025-06-05 09:58:28.380094
26	3	f	2025-06-05 10:05:28.047959
26	8	f	\N
26	16	f	\N
26	29	f	2025-06-05 10:25:17.137593
26	13	f	2025-06-05 10:30:40.27986
26	22	f	\N
26	31	f	\N
26	30	f	\N
26	18	f	2025-06-05 10:54:33.814922
26	10	f	2025-06-05 10:54:39.170457
26	14	f	2025-06-05 10:54:56.072916
26	6	f	2025-06-05 11:07:48.062751
26	12	f	\N
26	23	f	\N
26	15	f	2025-06-05 11:37:21.204933
26	32	f	2025-06-05 11:44:53.799942
24	32	f	\N
26	25	f	2025-06-05 12:43:29.251179
26	19	f	\N
27	28	t	\N
27	6	f	\N
27	23	f	\N
27	7	f	2025-06-05 13:03:40.234646
27	24	f	\N
27	25	f	\N
27	2	f	\N
27	3	f	2025-06-05 13:13:08.426719
27	8	f	2025-06-05 13:26:39.554257
27	9	f	\N
27	10	f	2025-06-05 13:47:28.945326
27	12	f	\N
27	13	f	\N
27	14	f	\N
24	33	f	\N
26	33	f	\N
27	33	f	2025-06-05 14:12:35.633058
27	18	f	2025-06-05 14:17:36.730345
27	19	f	2025-06-05 14:28:14.418555
27	20	f	\N
27	29	f	\N
27	22	f	\N
27	30	f	\N
28	28	t	\N
28	6	f	\N
28	23	f	\N
28	7	f	2025-06-08 10:03:34.216813
29	28	t	\N
29	7	f	2025-06-08 10:56:09.99781
30	28	t	\N
29	34	f	2025-06-08 11:15:51.031309
24	34	f	\N
29	35	f	2025-06-08 11:36:26.512575
31	28	t	\N
30	35	f	2025-06-08 11:47:46.280317
26	35	f	\N
24	35	f	\N
23	35	f	2025-06-08 12:30:16.256285
25	35	f	\N
31	35	f	\N
27	35	f	\N
28	35	f	2025-06-08 12:33:21.127502
30	2	f	2025-06-08 12:33:56.536788
29	2	f	2025-06-08 12:34:05.575917
31	2	f	\N
28	2	f	\N
29	6	f	\N
23	6	f	\N
30	6	f	\N
25	6	f	\N
31	6	f	2025-06-08 13:40:20.170467
31	9	f	2025-06-08 13:40:55.828997
31	8	f	2025-06-08 13:41:21.845745
31	22	f	2025-06-08 13:45:24.418189
31	15	f	\N
23	15	f	\N
29	15	f	\N
30	15	f	\N
28	15	f	\N
\.


--
-- TOC entry 3677 (class 0 OID 24690)
-- Dependencies: 232
-- Data for Name: feedback; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.feedback (id_feedback, id_course, text_feedback, date_feedback, rate_feedback, id_user, status) FROM stdin;
14	29	Хороший	2025-06-08	5	7	approved
15	30	фы	2025-06-08	5	35	approved
13	25	asas	2025-06-03	5	17	approved
12	23	оформила кредит с кайфом, спасибо!	2025-06-03	5	28	approved
10	19	Классный курс	2025-06-02	5	2	approved
11	19	asas	2025-06-02	5	3	approved
9	18	sdfdsf	2025-05-30	5	2	approved
\.


--
-- TOC entry 3679 (class 0 OID 24701)
-- Dependencies: 234
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
29	23	\N	Переменные	new
30	24	\N	Что такое FIGMA?	new
31	25	\N	Переменные	new
32	26	\N	Переменные	new
33	27	\N	фы	new
34	28	\N	тестик	new
35	29	\N	урок	new
36	30	\N	as	new
37	31	\N	asas	new
\.


--
-- TOC entry 3664 (class 0 OID 24597)
-- Dependencies: 219
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
683dca3327f5f                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	63	materials/maxrox1904@gmail.com/Переменные/ывывыв_63.pdf	\N
683ec8ca6cc07                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	65	materials/quixotesoul@gmail.com/Что такое FIGMA?/Как начать работу в програмее?_65.pdf	\N
683ed04a95a0c                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	66	materials/quixotesoul@gmail.com/Переменные/asasas_66.pdf	\N
68455a468dcb8                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	75	materials/quixotesoul@gmail.com/тестик/тестик_75.pdf	\N
684562279386a                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	77	\N	https://dzen.ru/?clid=2411725&yredirect=true
68456bcab6184                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	78	materials/quixotesoul@gmail.com/урок/первый_78.pdf	\N
68456bd340b0a                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	79	\N	https://dzen.ru/?clid=2411725&yredirect=true
68456d3d8f477                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	80	materials/quixotesoul@gmail.com/as/as_80.pdf	\N
\.


--
-- TOC entry 3666 (class 0 OID 24607)
-- Dependencies: 221
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.questions (id_question, id_test, text_question, answer_question, type_question, image_question) FROM stdin;
54	25	1. Как получить данные POST-запроса?	0	single	\N
55	25	1. Как получить данные POST-запроса?	0	single	\N
56	25	фы		match	\N
57	23	Напишите функцию sum_array, которая принимает массив чисел и возвращает их сумму.		code	\N
58	23	Сумма массива		code	\N
59	23	Сумма массива		code	\N
60	27	Сумма элементов массива из функции		code	\N
61	27	Сумма элементов массива из функции		code	\N
21	20	why did the chicken crossed the road?	0,1,2	multi	\N
22	20	Какая лучшая кафедра?	0	single	\N
23	20	Деятельность актива ЛГТУ		match	\N
24	20	Напиши		code	\N
25	21	ну почему?	0	single	\N
26	21	Как	0	multi	\N
27	22	asdsad		match	\N
28	22	111	0,1	multi	\N
29	22	sdfsdfsdfsdf		code	\N
62	27	Сумма элементов массива из функции		code	\N
63	28	Вывести hello world, и больше ничего		code	\N
30	23	Какой комбинацией можно найти нужный фрейм?	1	single	\N
31	24	кто?	2	single	\N
32	24	не	0,1	multi	\N
33	24	фывфыв		match	\N
34	24	почему?		code	\N
35	25	1. Как получить данные POST-запроса?	0	single	\N
36	25	1. Как получить данные POST-запроса?	0	single	\N
37	25	1. Как получить данные POST-запроса?	0	single	\N
38	25	1. Как получить данные POST-запроса?	0	single	\N
39	25	1. Как получить данные POST-запроса?	0	single	\N
40	25	1. Как получить данные POST-запроса?	0	single	\N
41	25	1. Как получить данные POST-запроса?	0	single	\N
42	25	1. Как получить данные POST-запроса?	0	single	\N
43	25	1. Как получить данные POST-запроса?	0	single	\N
44	25	1. Как получить данные POST-запроса?	0	single	\N
45	25	1. Как получить данные POST-запроса?	0	single	\N
46	25	1. Как получить данные POST-запроса?	0	single	\N
47	25	1. Как получить данные POST-запроса?	0	single	\N
48	25	1. Как получить данные POST-запроса?	0	single	\N
49	25	1. Как получить данные POST-запроса?	0	single	\N
50	25	1. Как получить данные POST-запроса?	0	single	\N
51	25	1. Как получить данные POST-запроса?	0	single	\N
52	25	1. Как получить данные POST-запроса?	0	single	\N
53	25	1. Как получить данные POST-запроса?	0	single	\N
64	28	Вывести hello world, и больше ничего		code	\N
65	28	Вывести hello world, и больше ничего		code	\N
66	29	кто?	0	single	\N
67	29	кто?	0,1	multi	\N
68	29	число?		match	\N
70	29	Сумма 4 элементов массива		code	\N
71	29	вывести hello world		code	\N
72	29	Сложить две переменные 3 и 5 в переменную res		code	\N
73	30	Как получить значение в переменную из пост запроса в PHP?	0	single	\N
74	30	Какая лучшая машина?	0,1	multi	\N
75	30	Сумма элементов массива из функции		code	\N
\.


--
-- TOC entry 3668 (class 0 OID 24639)
-- Dependencies: 223
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
63	29	ывывыв	not_started	material
64	30	контрольный срез	not_started	test
65	30	Как начать работу в програмее?	not_started	material
66	31	asasas	not_started	material
67	31	тест1	not_started	test
68	31	asas	not_started	test
70	32	код тест	not_started	test
21	10	Читайте	completed	material
71	33	фы	not_started	test
72	34	тест	not_started	test
75	34	тестик	not_started	material
77	34	as	not_started	material
27	19	asd	completed	material
78	35	первый	not_started	material
79	35	Ссылка	not_started	material
80	36	as	not_started	material
81	37	asas	not_started	test
\.


--
-- TOC entry 3696 (class 0 OID 41324)
-- Dependencies: 251
-- Data for Name: student_analytics; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.student_analytics (id_user, id_course, lessons_completed, total_lessons, tests_completed, total_tests, average_test_score, last_activity, estimated_completion_date) FROM stdin;
15	16	1	1	0	0	0	2025-06-08 13:42:02.143408	\N
3	18	2	2	1	1	0	2025-06-08 13:42:02.143408	\N
16	18	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
2	18	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
2	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
17	18	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
17	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
10	18	2	2	1	1	0	2025-06-08 13:42:02.143408	\N
10	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
18	18	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
18	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
16	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
19	18	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
19	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
1	16	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
20	18	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
20	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
1	19	0	3	0	2	0	2025-06-08 13:42:02.143408	\N
2	19	3	3	2	2	0	2025-06-08 13:42:02.143408	\N
10	19	3	3	1	2	0	2025-06-08 13:42:02.143408	\N
10	16	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
3	19	3	3	2	2	0	2025-06-08 13:42:02.143408	\N
9	19	2	3	1	2	0	2025-06-08 13:42:02.143408	\N
1	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
13	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
14	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
2	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
2	16	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
9	16	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
1	20	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
17	20	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
9	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
9	20	1	1	0	0	0	2025-06-08 13:42:02.143408	\N
3	20	1	1	0	0	0	2025-06-08 13:42:02.143408	\N
10	20	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
9	18	2	2	1	1	0	2025-06-08 13:42:02.143408	\N
1	21	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
21	21	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
26	22	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
26	23	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
1	18	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
28	23	1	1	0	0	0	2025-06-08 13:42:02.143408	\N
28	24	0	1	0	1	0	2025-06-08 13:42:02.143408	\N
2	23	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
15	18	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
9	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
3	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
3	16	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
3	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
15	17	0	0	0	0	0	2025-06-08 13:42:02.143408	\N
9	23	1	1	0	0	0	2025-06-08 13:42:02.143408	\N
28	25	0	1	0	2	0	2025-06-08 13:42:02.143408	\N
17	25	1	1	1	2	0	2025-06-08 13:42:02.143408	\N
2	25	1	1	0	2	0	2025-06-08 13:42:02.143408	\N
2	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
9	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
3	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
10	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
6	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
8	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
7	24	1	1	1	1	0	2025-06-08 13:42:02.143408	\N
12	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
1	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
15	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
8	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
19	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
25	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
20	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
10	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
22	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
18	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
13	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
16	24	0	1	0	1	0	2025-06-08 13:42:02.143408	\N
11	8	0	1	0	0	0	2025-06-08 13:42:02.143408	\N
29	24	1	1	1	1	0	2025-06-08 13:42:02.143408	\N
24	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
30	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
31	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
28	26	0	0	0	1	0	2025-06-08 13:42:02.143408	\N
2	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
9	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
3	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
8	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
16	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
29	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
13	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
22	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
31	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
30	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
18	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
10	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
14	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
6	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
12	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
23	26	0	0	0	1	0	2025-06-08 13:42:02.143408	\N
15	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
32	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
32	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
25	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
19	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
28	27	0	0	0	1	0	2025-06-08 13:42:02.143408	\N
6	27	0	0	0	1	0	2025-06-08 13:42:02.143408	\N
23	27	0	0	0	1	0	2025-06-08 13:42:02.143408	\N
7	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
24	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
25	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
2	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
3	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
8	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
9	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
10	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
12	27	0	0	0	1	0	2025-06-08 13:42:02.143408	\N
13	27	0	0	0	1	0	2025-06-08 13:42:02.143408	\N
14	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
33	24	0	1	1	1	0	2025-06-08 13:42:02.143408	\N
33	26	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
33	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
18	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
19	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
20	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
29	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
22	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
30	27	0	0	1	1	0	2025-06-08 13:42:02.143408	\N
28	28	0	2	0	1	0	2025-06-08 13:42:02.143408	\N
6	28	0	2	1	1	0	2025-06-08 13:42:02.143408	\N
23	28	0	2	1	1	0	2025-06-08 13:42:02.143408	\N
7	28	1	2	1	1	0	2025-06-08 13:42:02.143408	\N
\.


--
-- TOC entry 3699 (class 0 OID 41367)
-- Dependencies: 254
-- Data for Name: student_test_settings; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.student_test_settings (id_user, id_test, additional_attempts) FROM stdin;
6	30	3
\.


--
-- TOC entry 3690 (class 0 OID 41252)
-- Dependencies: 245
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.tags (id_tag, name_tag) FROM stdin;
1	Программирование
2	Web-разработка
3	Базы данных
4	Python
5	PHP
6	JavaScript
7	Дизайн
8	Алгоритмы
9	Математика
10	Тестирование
\.


--
-- TOC entry 3688 (class 0 OID 24884)
-- Dependencies: 243
-- Data for Name: test_answers; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.test_answers (id_answer, id_attempt, id_question, id_selected_option, is_correct, answer_time, answer_text, ai_feedback) FROM stdin;
23	14	21	30	t	2025-06-02 12:16:43.789087	\N	\N
24	14	22	33	t	2025-06-02 12:16:43.792159	\N	\N
25	14	23	\N	t	2025-06-02 12:16:43.794578	\N	\N
26	14	24	\N	t	2025-06-02 12:16:43.796107	\N	\N
27	15	25	38	t	2025-06-02 12:16:52.801221	\N	\N
28	15	26	40	t	2025-06-02 12:16:52.804085	\N	\N
29	16	21	30	t	2025-06-02 12:18:37.024189	\N	\N
30	16	22	33	t	2025-06-02 12:18:37.026426	\N	\N
31	16	23	\N	t	2025-06-02 12:18:37.028279	\N	\N
32	16	24	\N	t	2025-06-02 12:18:37.029565	\N	\N
33	17	21	30	t	2025-06-02 12:23:51.53388	\N	\N
34	17	22	33	t	2025-06-02 12:23:51.536793	\N	\N
35	17	23	\N	t	2025-06-02 12:23:51.539218	\N	\N
36	17	24	\N	t	2025-06-02 12:23:51.540791	\N	\N
37	18	25	38	t	2025-06-02 12:24:02.097131	\N	\N
38	18	26	40	t	2025-06-02 12:24:02.100062	\N	\N
39	19	27	\N	t	2025-06-02 12:39:51.331882	\N	\N
40	19	28	44	t	2025-06-02 12:39:51.334951	\N	\N
41	19	29	\N	t	2025-06-02 12:39:51.336564	\N	\N
42	20	27	\N	f	2025-06-02 12:49:55.77388	\N	\N
43	20	28	44	t	2025-06-02 12:49:55.779871	\N	\N
44	20	29	\N	t	2025-06-02 12:49:55.782486	\N	\N
45	21	27	\N	t	2025-06-02 12:55:07.325412	["0","1"]	\N
46	21	28	44	t	2025-06-02 12:55:07.328549	["0","1"]	\N
47	21	29	\N	t	2025-06-02 12:55:07.330248	123123	\N
48	23	21	30	t	2025-06-02 16:18:42.467919	["0","1","2"]	\N
49	23	22	33	t	2025-06-02 16:18:42.471237	\N	\N
50	23	23	\N	t	2025-06-02 16:18:42.473247	["0","1"]	\N
51	23	24	\N	t	2025-06-02 16:18:42.474688	фыфы	\N
52	25	31	52	t	2025-06-03 10:38:33.382938	\N	\N
53	25	32	53	t	2025-06-03 10:38:33.386419	["0","1"]	\N
54	25	33	\N	t	2025-06-03 10:38:33.388064	["0","1"]	\N
55	25	34	\N	t	2025-06-03 10:38:33.389084	asas	\N
56	26	30	48	f	2025-06-04 11:22:39.295033	\N	\N
57	26	57	\N	t	2025-06-04 11:22:39.299226	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	\N
58	27	30	49	t	2025-06-04 12:44:54.824064	\N	\N
59	27	57	\N	f	2025-06-04 12:44:54.827742	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	\N
60	27	58	\N	f	2025-06-04 12:44:54.829431	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    total = 0\r\n    for num in arr:\r\n        total += num\r\n    return total\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Вывод: Сумма массива: 15	\N
61	27	59	\N	f	2025-06-04 12:44:54.830967	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    int sum = 0;\r\n    for (int num : arr) {\r\n        sum += num;\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Вывод: Сумма массива: 15\r\n    return 0;\r\n}	\N
62	28	30	48	f	2025-06-04 12:53:38.318151	\N	\N
63	28	57	\N	f	2025-06-04 12:53:38.321578	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 4];\r\necho "Сумма массива: " . sum_array($test_array);\r\n?>',	\N
64	28	58	\N	f	2025-06-04 12:53:38.323206	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Ожидаемый вывод: 15	\N
65	28	59	\N	f	2025-06-04 12:53:38.324908	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
66	29	30	49	t	2025-06-04 13:03:16.500736	\N	\N
67	29	57	\N	f	2025-06-04 13:03:16.503481	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: 15 " . sum_array($test_array);\r\n?>',	\N
68	29	58	\N	f	2025-06-04 13:03:16.504744	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
130	45	60	\N	f	2025-06-05 09:56:04.364035	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>	\N
131	45	61	\N	f	2025-06-05 09:56:04.368093	\r\n#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    int sum = 0;\r\n    for (int i = 0; i < size; i++) {\r\n        sum += arr[i];\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
249	86	63	\N	f	2025-06-05 14:34:58.364488	<?php\r\n// Ваш код здесь\r\n?>	НЕПРАВИЛЬНО: код не содержит вывода 'hello world'.
355	177	68	\N	t	2025-06-08 14:11:02.045091	["0","1"]	\N
69	29	59	\N	f	2025-06-04 13:03:16.506869	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15 ";  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
70	30	30	49	t	2025-06-04 13:10:58.010504	\N	\N
71	30	57	\N	f	2025-06-04 13:10:58.013414	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: 15 " . sum_array($test_array);\r\n?>',	\N
72	30	58	\N	f	2025-06-04 13:10:58.014832	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
73	30	59	\N	f	2025-06-04 13:10:58.016321	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15 ";  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
74	31	30	49	t	2025-06-04 13:12:56.476394	\N	\N
75	31	57	\N	f	2025-06-04 13:12:56.479653	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	\N
76	31	58	\N	f	2025-06-04 13:12:56.480997	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    total = 0\r\n    for num in arr:\r\n        total += num\r\n    return total\r\n\r\n# Тестирование функции\r\nif __name__ == "__main__":\r\n    test_array = [1, 2, 3, 4, 5]\r\n    print(f"Сумма массива: {sum_array(test_array)}")  # Вывод: Сумма массива: 15	\N
77	31	59	\N	f	2025-06-04 13:12:56.482283	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    int sum = 0;\r\n    for (int num : arr) {\r\n        sum += num;\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Вывод: Сумма массива: 15\r\n    return 0;\r\n}	\N
78	32	30	48	f	2025-06-04 13:25:40.901056	\N	\N
79	32	57	\N	f	2025-06-04 13:25:40.905196	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array);\r\n?>',	\N
80	32	58	\N	f	2025-06-04 13:25:40.906712	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Ожидаемый вывод: 15	\N
81	32	59	\N	f	2025-06-04 13:25:40.908141	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    int sum = 0;\r\n    for (int num : arr) {\r\n        sum += num;\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Вывод: Сумма массива: 15\r\n    return 0;\r\n}	\N
82	33	30	48	f	2025-06-04 13:26:30.363553	\N	\N
83	33	57	\N	f	2025-06-04 13:26:30.365869	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: 15 ";\r\n?>',	\N
84	33	58	\N	f	2025-06-04 13:26:30.367412	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
103	38	57	\N	f	2025-06-04 13:51:24.737702	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: 15 " . sum_array($test_array);\r\n?>',	\N
104	38	58	\N	f	2025-06-04 13:51:24.742482	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    total = 0\r\n    for num in arr:\r\n        total += num\r\n    return total\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Вывод: Сумма массива: 15	\N
250	86	64	\N	f	2025-06-05 14:34:58.369055	# Ваш код здесь	НЕПРАВИЛЬНО: код не представлен, невозможно проверить его корректность.
85	33	59	\N	t	2025-06-04 13:26:30.369044	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15 ";  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
86	34	30	49	t	2025-06-04 13:29:02.91345	\N	\N
87	34	57	\N	f	2025-06-04 13:29:02.916498	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	\N
88	34	58	\N	f	2025-06-04 13:29:02.918033	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    total = 0\r\n    for num in arr:\r\n        total += num\r\n    return total\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Вывод: Сумма массива: 15	\N
89	34	59	\N	f	2025-06-04 13:29:02.919552	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15";  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
90	35	30	49	t	2025-06-04 13:35:29.025751	\N	\N
91	35	57	\N	f	2025-06-04 13:35:29.02895	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	\N
92	35	58	\N	f	2025-06-04 13:35:29.030618	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
93	35	59	\N	f	2025-06-04 13:35:29.032196	#include <iostream>\r\n#include <vector>\r\n\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    int sum = 0;\r\n    for (int num : arr) {\r\n        sum += num;\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Вывод: Сумма массива: 15\r\n    return 0;\r\n}	\N
94	36	30	49	t	2025-06-04 13:40:25.978934	\N	\N
95	36	57	\N	f	2025-06-04 13:40:25.982144	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array);\r\n?>',	\N
96	36	58	\N	f	2025-06-04 13:40:25.983583	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Ожидаемый вывод: 15	\N
97	36	59	\N	f	2025-06-04 13:40:25.985049	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
98	37	30	48	f	2025-06-04 13:45:18.289586	\N	\N
99	37	57	\N	f	2025-06-04 13:45:18.302722	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	\N
100	37	58	\N	f	2025-06-04 13:45:18.306607	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Ожидаемый вывод: 15	\N
101	37	59	\N	f	2025-06-04 13:45:18.310598	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
102	38	30	49	t	2025-06-04 13:51:24.731242	\N	\N
132	45	62	\N	f	2025-06-05 09:56:04.370372	def sum_of_elements(arr):\r\n    return sum(arr)\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)  # Выведет 15	\N
311	124	71	\N	f	2025-06-08 11:38:09.490268	print("suka")	НЕПРАВИЛЬНО: вывод не соответствует ожидаемому
105	38	59	\N	f	2025-06-04 13:51:24.746587	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    int sum = 0;\r\n    for (int num : arr) {\r\n        sum += num;\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Вывод: Сумма массива: 15\r\n    return 0;\r\n}\r\n	\N
106	39	30	49	t	2025-06-04 13:54:28.016391	\N	\N
107	39	57	\N	f	2025-06-04 13:54:28.022683	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	\N
108	39	58	\N	f	2025-06-04 13:54:28.026859	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
109	39	59	\N	f	2025-06-04 13:54:28.031191	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15";  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
110	40	30	48	f	2025-06-04 13:59:34.836044	\N	\N
112	40	58	\N	f	2025-06-04 13:59:34.846863	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Ожидаемый вывод: 15	\N
113	40	59	\N	f	2025-06-04 13:59:34.850889	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
111	40	57	\N	f	2025-06-04 13:59:34.842862	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array);\r\n?>',	ВЕРДИКТ: Решение правильное.\n\nКод реализует функцию для суммирования элементов массива с использованием встроенной функции array_sum(), что является оптимальным решением в PHP. Функция корректно принимает массив в качестве аргумента и возвращает сумму всех его элементов.\n\nАлгоритмическая сложность: O(n), где n - количество элементов массива.\n\nКод написан лаконично и соответствует стандартам PSR. Использование встроенной функции array_sum() является предпочтительным подходом, так как она оптимизирована и обрабатывает все краевые случаи.
114	41	30	49	t	2025-06-04 15:03:49.899758	\N	\N
116	41	58	\N	f	2025-06-04 15:03:49.909533	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
117	41	59	\N	f	2025-06-04 15:03:49.912548	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15 " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
129	44	59	\N	f	2025-06-04 15:25:23.580287	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15 " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
133	46	60	\N	f	2025-06-05 09:57:42.132832	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
134	46	61	\N	f	2025-06-05 09:57:42.13592	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
135	46	62	\N	f	2025-06-05 09:57:42.13845	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
349	175	68	\N	f	2025-06-08 14:06:37.931122	null	\N
350	175	70	\N	f	2025-06-08 14:06:37.933448	\N	
351	175	71	\N	f	2025-06-08 14:06:37.935537	\N	
115	41	57	\N	f	2025-06-04 15:03:49.90653	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	ВЕРДИКТ: Решение правильное.\n\nКод реализует функцию для суммирования элементов массива с использованием встроенной функции array_sum(), что является оптимальным решением в PHP. Функция корректно принимает массив в качестве аргумента и возвращает сумму всех его элементов.\n\nАлгоритмическая сложность: O(n), где n - количество элементов массива.\n\nКод написан лаконично и соответствует стандартам PSR. Использование встроенной функции array_sum() является предпочтительным подходом, так как она оптимизирована и обрабатывает все краевые случаи.
118	42	30	49	t	2025-06-04 15:10:43.846051	\N	\N
120	42	58	\N	f	2025-06-04 15:10:43.85559	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    total = 0\r\n    for num in arr:\r\n        total += num\r\n    return total\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Вывод: Сумма массива: 15	\N
121	42	59	\N	f	2025-06-04 15:10:43.858564	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15 ";  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
119	42	57	\N	f	2025-06-04 15:10:43.852567	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: 15 " . sum_array($test_array);\r\n?>',	ВЕРДИКТ: Решение правильное.\n\nКод реализует функцию для суммирования элементов массива с использованием встроенной функции array_sum(), что является оптимальным решением в PHP. Функция корректно принимает массив в качестве аргумента и возвращает сумму всех его элементов.\n\nАлгоритмическая сложность: O(n), где n - количество элементов массива.\n\nКод написан лаконично и соответствует стандартам PSR. Использование встроенной функции array_sum() является предпочтительным подходом, так как она оптимизирована и обрабатывает все краевые случаи.
122	43	30	49	t	2025-06-04 15:18:48.622636	\N	\N
124	43	58	\N	f	2025-06-04 15:18:48.641837	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
125	43	59	\N	f	2025-06-04 15:18:48.646829	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15 ";  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
123	43	57	\N	f	2025-06-04 15:18:48.634574	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	ВЕРДИКТ: Решение правильное.\n\nКод реализует функцию для суммирования элементов массива с использованием встроенной функции array_sum(), что является оптимальным решением в PHP. Функция корректно принимает массив в качестве аргумента и возвращает сумму всех его элементов.\n\nАлгоритмическая сложность: O(n), где n - количество элементов массива.\n\nКод написан лаконично и соответствует стандартам PSR. Использование встроенной функции array_sum() является предпочтительным подходом, так как она оптимизирована и обрабатывает все краевые случаи.
126	44	30	49	t	2025-06-04 15:25:23.571819	\N	\N
127	44	57	\N	f	2025-06-04 15:25:23.5765	<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array); // Вывод: Сумма массива: 15\r\n?>	\N
128	44	58	\N	f	2025-06-04 15:25:23.57842	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
246	85	63	\N	f	2025-06-05 14:30:16.569353	<?php\r\n// Ваш код здесь\r\n?>	\N
247	85	64	\N	f	2025-06-05 14:30:16.572432	# Ваш код здесь	\N
136	47	60	\N	f	2025-06-05 09:58:42.463277	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
137	47	61	\N	f	2025-06-05 09:58:42.466875	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
138	47	62	\N	f	2025-06-05 09:58:42.469263	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
139	48	60	\N	f	2025-06-05 09:59:42.628104	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>\r\n	\N
140	48	61	\N	f	2025-06-05 09:59:42.630433	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    int sum = 0;\r\n    for (int i = 0; i < size; i++) {\r\n        sum += arr[i];\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
141	48	62	\N	f	2025-06-05 09:59:42.632568	def sum_of_elements(arr):\r\n    return sum(arr)\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)  # Выведет 15	\N
142	49	60	\N	f	2025-06-05 10:07:03.70786	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>	\N
143	49	61	\N	f	2025-06-05 10:07:03.715361	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    int sum = 0;\r\n    for (int i = 0; i < size; i++) {\r\n        sum += arr[i];\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
144	49	62	\N	f	2025-06-05 10:07:03.721454	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
145	50	60	\N	f	2025-06-05 10:18:10.570946	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
146	50	61	\N	f	2025-06-05 10:18:10.573156	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
147	50	62	\N	f	2025-06-05 10:18:10.574292	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
148	53	60	\N	f	2025-06-05 10:23:49.803177	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>	\N
149	53	61	\N	f	2025-06-05 10:23:49.805851	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
150	53	62	\N	f	2025-06-05 10:23:49.807008	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
151	54	60	\N	f	2025-06-05 10:28:01.68606	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
152	54	61	\N	f	2025-06-05 10:28:01.688599	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    int sum = 0;\r\n    for (int i = 0; i < size; i++) {\r\n        sum += arr[i];\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
153	54	62	\N	f	2025-06-05 10:28:01.689947	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint("15")	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
154	55	60	\N	f	2025-06-05 10:47:02.661918	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
155	55	61	\N	f	2025-06-05 10:47:02.665354	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
156	55	62	\N	f	2025-06-05 10:47:02.667784	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint("15")	\N
157	56	60	\N	f	2025-06-05 10:50:19.323976	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>\r\n	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
158	56	61	\N	f	2025-06-05 10:50:19.325865	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
352	175	72	\N	f	2025-06-08 14:06:37.937511	\N	
159	56	62	\N	f	2025-06-05 10:50:19.327084	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint("15")	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
160	57	60	\N	f	2025-06-05 10:52:56.685056	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
161	57	61	\N	f	2025-06-05 10:52:56.687255	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
162	57	62	\N	f	2025-06-05 10:52:56.688382	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
163	58	60	\N	f	2025-06-05 10:55:15.30544	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
164	58	61	\N	f	2025-06-05 10:55:15.307457	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
165	58	62	\N	f	2025-06-05 10:55:15.308438	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	Автоматический анализ кода недоступен. Код проверен по соответствию выходных данных.
166	59	60	\N	f	2025-06-05 11:01:42.245599	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>\r\n	Ошибка при обращении к ИИ: Failed to connect to localhost port 81 after 0 ms: Couldn't connect to server
167	59	61	\N	f	2025-06-05 11:01:42.24778	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    int sum = 0;\r\n    for (int i = 0; i < size; i++) {\r\n        sum += arr[i];\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	Ошибка при обращении к ИИ: Failed to connect to localhost port 81 after 0 ms: Couldn't connect to server
168	59	62	\N	f	2025-06-05 11:01:42.2488	def sum_of_elements(arr):\r\n    return sum(arr)\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)  # Выведет 15	Ошибка при обращении к ИИ: Failed to connect to localhost port 81 after 0 ms: Couldn't connect to server
169	60	60	\N	f	2025-06-05 11:08:13.242401	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	Ошибка при обращении к ИИ: Failed to connect to localhost port 81 after 0 ms: Couldn't connect to server
170	60	61	\N	f	2025-06-05 11:08:13.244813	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	Ошибка при обращении к ИИ: Failed to connect to localhost port 81 after 0 ms: Couldn't connect to server
171	60	62	\N	f	2025-06-05 11:08:13.245913	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	Ошибка при обращении к ИИ: Failed to connect to localhost port 81 after 0 ms: Couldn't connect to server
172	61	60	\N	f	2025-06-05 11:35:53.812503	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
173	61	61	\N	f	2025-06-05 11:35:53.815681	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
174	61	62	\N	f	2025-06-05 11:35:53.818119	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
175	62	60	\N	f	2025-06-05 11:38:29.387194	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
176	62	61	\N	f	2025-06-05 11:38:29.390204	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
177	62	62	\N	f	2025-06-05 11:38:29.392751	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
178	63	60	\N	f	2025-06-05 11:38:33.117399	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
353	177	66	143	t	2025-06-08 14:11:02.040681	\N	\N
179	63	61	\N	f	2025-06-05 11:38:33.120586	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
180	63	62	\N	f	2025-06-05 11:38:33.123087	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
181	64	60	\N	f	2025-06-05 11:42:37.244379	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
182	64	61	\N	f	2025-06-05 11:42:37.2475	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
183	64	62	\N	f	2025-06-05 11:42:37.249914	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
184	65	60	\N	f	2025-06-05 11:42:41.585519	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
185	65	61	\N	f	2025-06-05 11:42:41.588662	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
186	65	62	\N	f	2025-06-05 11:42:41.591379	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
187	66	60	\N	f	2025-06-05 11:42:53.738917	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
188	66	61	\N	f	2025-06-05 11:42:53.7418	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
189	66	62	\N	f	2025-06-05 11:42:53.744191	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
190	67	60	\N	f	2025-06-05 11:43:43.327474	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
191	67	61	\N	f	2025-06-05 11:43:43.33056	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
192	67	62	\N	f	2025-06-05 11:43:43.33312	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
193	68	30	48	f	2025-06-05 11:45:02.214341	\N	\N
195	68	58	\N	f	2025-06-05 11:45:02.220595	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Ожидаемый вывод: 15	\N
196	68	59	\N	f	2025-06-05 11:45:02.222938	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
194	68	57	\N	f	2025-06-05 11:45:02.218191	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array);\r\n?>',	ВЕРДИКТ: Решение правильное.\n\nКод реализует функцию для суммирования элементов массива с использованием встроенной функции array_sum(), что является оптимальным решением в PHP. Функция корректно принимает массив в качестве аргумента и возвращает сумму всех его элементов.\n\nАлгоритмическая сложность: O(n), где n - количество элементов массива.\n\nКод написан лаконично и соответствует стандартам PSR. Использование встроенной функции array_sum() является предпочтительным подходом, так как она оптимизирована и обрабатывает все краевые случаи.
197	69	60	\N	f	2025-06-05 12:44:13.036512	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	\N
198	69	61	\N	f	2025-06-05 12:44:13.040446	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
199	69	62	\N	f	2025-06-05 12:44:13.043499	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	\N
248	85	65	\N	f	2025-06-05 14:30:16.574929	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
200	70	63	\N	f	2025-06-05 13:03:34.162221	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>	\N
201	70	64	\N	f	2025-06-05 13:03:34.166573	def sum_of_elements(arr):\r\n    return sum(arr)\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)  # Выведет 15\r\n	\N
202	70	65	\N	f	2025-06-05 13:03:34.16982	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
203	71	63	\N	f	2025-06-05 13:03:54.74273	<?php\r\n// Ваш код здесь\r\n?>	\N
204	71	64	\N	f	2025-06-05 13:03:54.746454	# Ваш код здесь	\N
205	71	65	\N	f	2025-06-05 13:03:54.749528	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
206	72	63	\N	f	2025-06-05 13:05:54.422973	<?php\r\n// Ваш код здесь\r\n?>	\N
207	72	64	\N	f	2025-06-05 13:05:54.426683	# Ваш код здесь	\N
208	72	65	\N	f	2025-06-05 13:05:54.429821	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
209	73	63	\N	f	2025-06-05 13:07:25.438158	<?php\r\n// Ваш код здесь\r\n?>	\N
210	73	64	\N	f	2025-06-05 13:07:25.441691	# Ваш код здесь	\N
211	73	65	\N	f	2025-06-05 13:07:25.445064	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
212	74	63	\N	f	2025-06-05 13:10:48.392694	<?php\r\n// Ваш код здесь\r\n?>	\N
213	74	64	\N	f	2025-06-05 13:10:48.396401	# Ваш код здесь	\N
214	74	65	\N	f	2025-06-05 13:10:48.39951	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
215	75	63	\N	f	2025-06-05 13:13:21.076275	<?php\r\n// Ваш код здесь\r\n?>	\N
216	75	64	\N	f	2025-06-05 13:13:21.080229	# Ваш код здесь	\N
217	75	65	\N	f	2025-06-05 13:13:21.084384	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
218	76	63	\N	f	2025-06-05 13:26:54.919028	<?php\r\n// Ваш код здесь\r\n?>	\N
219	76	64	\N	f	2025-06-05 13:26:54.923141	# Ваш код здесь	\N
220	76	65	\N	f	2025-06-05 13:26:54.926081	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
221	77	63	\N	f	2025-06-05 13:28:28.049503	<?php\r\n// Ваш код здесь\r\n?>	\N
222	77	64	\N	f	2025-06-05 13:28:28.053387	# Ваш код здесь	\N
223	77	65	\N	f	2025-06-05 13:28:28.05679	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
224	78	63	\N	f	2025-06-05 13:56:39.082147	<?php\r\n// Ваш код здесь\r\n?>	\N
225	78	64	\N	f	2025-06-05 13:56:39.086269	# Ваш код здесь	\N
226	78	65	\N	f	2025-06-05 13:56:39.089564	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
227	79	30	49	t	2025-06-05 13:58:47.875268	\N	\N
229	79	58	\N	t	2025-06-05 13:58:47.88375	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: 15")  # Ожидаемый вывод: 15	\N
230	79	59	\N	t	2025-06-05 13:58:47.887097	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: 15" ;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	\N
228	79	57	\N	f	2025-06-05 13:58:47.88016	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>\r\n	ВЕРДИКТ: Решение правильное.\n\nКод реализует функцию для суммирования элементов массива с использованием встроенной функции array_sum(), что является оптимальным решением в PHP. Функция корректно принимает массив в качестве аргумента и возвращает сумму всех его элементов.\n\nАлгоритмическая сложность: O(n), где n - количество элементов массива.\n\nКод написан лаконично и соответствует стандартам PSR. Использование встроенной функции array_sum() является предпочтительным подходом, так как она оптимизирована и обрабатывает все краевые случаи.
231	80	63	\N	f	2025-06-05 14:08:06.249738	<?php\r\n// Ваш код здесь\r\n?>	\N
232	80	64	\N	f	2025-06-05 14:08:06.2538	# Ваш код здесь	\N
233	80	65	\N	f	2025-06-05 14:08:06.257062	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
234	81	60	\N	f	2025-06-05 14:08:46.197088	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    return array_sum($arr);\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // выведет 15\r\n?>\r\n	\N
235	81	61	\N	t	2025-06-05 14:08:46.201229	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    int sum = 0;\r\n    for (int i = 0; i < size; i++) {\r\n        sum += arr[i];\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}\r\n	\N
236	81	62	\N	t	2025-06-05 14:08:46.204804	def sum_of_elements(arr):\r\n    return sum(arr)\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)  # Выведет 15\r\n	\N
237	82	63	\N	f	2025-06-05 14:12:59.905146	<?php\r\n// Ваш код здесь\r\n?>	\N
238	82	64	\N	f	2025-06-05 14:12:59.909495	# Ваш код здесь	\N
239	82	65	\N	f	2025-06-05 14:12:59.913014	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
240	83	63	\N	f	2025-06-05 14:18:51.224169	<?php\r\necho "hello world"\r\n?>	\N
241	83	64	\N	t	2025-06-05 14:18:51.227493	print("hello world")	\N
242	83	65	\N	t	2025-06-05 14:18:51.230286	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    int sum = 0;\r\n    for (int i = 0; i < size; i++) {\r\n        sum += arr[i];\r\n    }\r\n    return sum;\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	\N
243	84	63	\N	f	2025-06-05 14:28:37.078879	<?php\r\n// Ваш код здесь\r\n?>	\N
244	84	64	\N	f	2025-06-05 14:28:37.082881	# Ваш код здесь	\N
245	84	65	\N	f	2025-06-05 14:28:37.086164	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	\N
251	86	65	\N	f	2025-06-05 14:34:58.372545	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	НЕПРАВИЛЬНО: код не выводит 'hello world', так как в представленном коде нет инструкций для вывода текста.
252	87	63	\N	f	2025-06-05 14:39:17.455143	<?php\r\n// Ваш код здесь\r\n?>	НЕПРАВИЛЬНО: код не содержит вывода 'hello world'.
253	87	64	\N	f	2025-06-05 14:39:17.458039	# Ваш код здесь	НЕПРАВИЛЬНО: код не представлен, невозможно проверить его корректность.
254	87	65	\N	f	2025-06-05 14:39:17.460223	#include <iostream>\r\n\r\nint main() {\r\n    // ваш код здесь\r\n    return 0;\r\n}	НЕПРАВИЛЬНО: код не выводит 'hello world', так как в представленном коде нет инструкций для вывода текста.
285	120	66	143	t	2025-06-08 09:47:42.430423	\N	\N
286	120	67	147	t	2025-06-08 09:47:42.433709	["0","1"]	\N
287	120	68	\N	t	2025-06-08 09:47:42.435259	["0","1"]	\N
288	120	70	\N	f	2025-06-08 09:47:42.439247	<?php\r\n\r\nfunction sumOfFourElements($arr) {\r\n    $res = array_sum($arr);\r\n    return $res;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfFourElements($array);\r\necho $result;\r\n?>	НЕПРАВИЛЬНО: функция sumOfFourElements должна суммировать только четыре элемента массива, а не все.
289	120	71	\N	f	2025-06-08 09:47:42.441083	print("poka")	НЕПРАВИЛЬНО: вывод программы не соответствует ожидаемому результату
290	120	72	\N	f	2025-06-08 09:47:42.442817	#include <iostream>\r\n\r\nint main() {\r\n    int a = 3;\r\n    int b = 5;\r\n    int res;\r\n\r\n    // Здесь должно быть решение\r\n\r\n    std::cout << "Результат: 8 ";\r\n    return 0;\r\n}	НЕПРАВИЛЬНО: хотя код компилируется и выводит правильный результат, в комментарии указано 'std::cout << "Результат: 8 ";', что не соответствует ожидаемому шаблону вывода.
291	121	66	144	f	2025-06-08 09:58:58.734292	\N	\N
292	121	67	147	t	2025-06-08 09:58:58.736842	["0","1"]	\N
293	121	68	\N	t	2025-06-08 09:58:58.738418	["0","1"]	\N
294	121	70	\N	f	2025-06-08 09:58:58.743112	<?php\r\n\r\nfunction sumOfFourElements($arr) {\r\n    $res = array_sum($arr);\r\n    return $res;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfFourElements($array);\r\necho $result;\r\n?>	НЕПРАВИЛЬНО: функция должна возвращать сумму только четырёх элементов, а не всех элементов массива.
295	121	71	\N	f	2025-06-08 09:58:58.744989	print("pika")	НЕПРАВИЛЬНО: вывод не соответствует ожидаемому ('pika' вместо 'hello world')
296	121	72	\N	f	2025-06-08 09:58:58.747075	#include <iostream>\r\n\r\nint main() {\r\n    int a = 3;\r\n    int b = 5;\r\n    int res;\r\n\r\n    // Здесь должно быть решение\r\n\r\n    std::cout << "Результат: " << res << std::endl;\r\n    return 0;\r\n}	НЕПРАВИЛЬНО: необходимо добавить операцию сложения переменных a и b и присвоить результат переменной res.
297	122	66	143	t	2025-06-08 10:01:51.548286	\N	\N
298	122	67	147	t	2025-06-08 10:01:51.550654	["0","1"]	\N
299	122	68	\N	t	2025-06-08 10:01:51.552182	["0","1"]	\N
300	122	70	\N	f	2025-06-08 10:01:51.554266	<?php\r\n\r\nfunction sumOfFourElements($arr) {\r\n    $sum = 0;\r\n    for ($i = 0; $i < 4 && $i < count($arr); $i++) {\r\n        $sum += $arr[$i];\r\n    }\r\n    return $sum;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfFourElements($array);\r\necho $result; // Выведет 10 (1+2+3+4)\r\n?>	НЕПРАВИЛЬНО: функция корректно суммирует только первые четыре элемента массива, однако в массиве $array всего пять элементов, и сумма должна быть 15 (1+2+3+4+5), а не 10.
301	122	71	\N	t	2025-06-08 10:01:51.556027	print("hello world")	ПРАВИЛЬНО: код корректно выводит 'hello world'
302	122	72	\N	t	2025-06-08 10:01:51.557871	#include <iostream>\r\n\r\nint main() {\r\n    int a = 3;\r\n    int b = 5;\r\n    int res = a+b;\r\n\r\n    // Здесь должно быть решение\r\n\r\n    std::cout << "Результат: " << res << std::endl;\r\n    return 0;\r\n}	ПРАВИЛЬНО: задача решена корректно, переменные a и b складываются и результат сохраняется в переменную res, которая затем выводится на экран.
303	123	30	48	f	2025-06-08 11:23:24.072128	\N	\N
304	123	57	\N	t	2025-06-08 11:23:24.076395	'<?php\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param array $arr Массив чисел\r\n * @return int Сумма элементов массива\r\n */\r\nfunction sum_array($arr) {\r\n    // Ваш код здесь\r\n}\r\n\r\n// Тестирование функции\r\n$test_array = [1, 2, 3, 4, 5];\r\necho "Сумма массива: " . sum_array($test_array);\r\n?>',	ПРАВИЛЬНО: функция корректно суммирует элементы массива.
305	123	58	\N	f	2025-06-08 11:23:24.078267	def sum_array(arr):\r\n    """\r\n    Функция для суммирования элементов массива\r\n    \r\n    :param arr: Список чисел\r\n    :return: Сумма элементов\r\n    """\r\n    pass  # Замените pass на ваш код\r\n\r\n# Тестирование функции\r\ntest_array = [1, 2, 3, 4, 5]\r\nprint(f"Сумма массива: {sum_array(test_array)}")  # Ожидаемый вывод: 15	НЕПРАВИЛЬНО: функция sum_array не реализована.
306	123	59	\N	f	2025-06-08 11:23:24.080072	#include <iostream>\r\n#include <vector>\r\n\r\nusing namespace std;\r\n\r\n/**\r\n * Функция для суммирования элементов массива\r\n * \r\n * @param arr Вектор чисел\r\n * @return Сумма элементов\r\n */\r\nint sumArray(const vector<int>& arr) {\r\n    // Замените этот комментарий вашим кодом\r\n    return 0;\r\n}\r\n\r\nint main() {\r\n    // Тестирование функции\r\n    vector<int> testArray = {1, 2, 3, 4, 5};\r\n    cout << "Сумма массива: " << sumArray(testArray) << endl;  // Ожидаемый вывод: 15\r\n    return 0;\r\n}	НЕПРАВИЛЬНО: функция sumArray не выполняет суммирование элементов массива. Она просто возвращает 0.
307	124	66	143	t	2025-06-08 11:38:09.479472	\N	\N
308	124	67	147	t	2025-06-08 11:38:09.48292	["0","1"]	\N
309	124	68	\N	t	2025-06-08 11:38:09.484882	["0","1"]	\N
310	124	70	\N	f	2025-06-08 11:38:09.487964	<?php\r\n\r\nfunction sumOfFourElements($arr) {\r\n    $res = "biba";\r\n    return null;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfFourElements($array);\r\necho "15";\r\n?>	НЕПРАВИЛЬНО: функция sumOfFourElements не выполняет суммирование элементов массива и возвращает null вместо корректного результата.
354	177	67	147	t	2025-06-08 14:11:02.043463	["0","1"]	\N
312	124	72	\N	f	2025-06-08 11:38:09.492809	#include <iostream>\r\n\r\nint main() {\r\n    int a = 3;\r\n    int b = 5;\r\n    int res;\r\n\r\n    // Здесь должно быть решение\r\n\r\n    std::cout << "Результат: 8 ";\r\n    return 0;\r\n}	НЕПРАВИЛЬНО: отсутствует операция сложения переменных a и b в переменной res.
313	125	60	\N	f	2025-06-08 11:48:30.294862	<?php\r\n\r\nfunction sumArrayElements($arr) {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	НЕПРАВИЛЬНО: функция sumArrayElements не содержит корректного алгоритма для суммирования элементов массива.
314	125	61	\N	t	2025-06-08 11:48:30.297759	#include <iostream>\r\nusing namespace std;\r\n\r\nint sum_of_array(int arr[], int size) {\r\n    // Ваш код здесь\r\n}\r\n\r\nint main() {\r\n    int arr[] = {1, 2, 3, 4, 5};\r\n    int size = sizeof(arr) / sizeof(arr[0]);\r\n    cout << "Сумма элементов массива: " << sum_of_array(arr, size) << endl;\r\n    return 0;\r\n}	ПРАВИЛЬНО: задача решена корректно, функция sum_of_array правильно вычисляет сумму элементов массива.
315	125	62	\N	f	2025-06-08 11:48:30.299623	def sum_of_elements(arr):\r\n    # Здесь напишите ваш код\r\n    pass\r\n\r\n# Пример использования функции\r\narray = [1, 2, 3, 4, 5]\r\nresult = sum_of_elements(array)\r\nprint(result)	НЕПРАВИЛЬНО: функция sum_of_elements не содержит кода для вычисления суммы элементов массива.
316	162	73	157	t	2025-06-08 13:39:52.266259	\N	\N
317	162	74	161	f	2025-06-08 13:39:52.269545	["0"]	\N
318	162	75	\N	f	2025-06-08 13:39:52.272338	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	НЕПРАВИЛЬНО: функция sumOfArrayElements не содержит корректного алгоритма для вычисления суммы элементов массива.
319	163	31	50	f	2025-06-08 13:40:18.021932	\N	\N
320	163	32	53	f	2025-06-08 13:40:18.024535	["0"]	\N
321	163	33	\N	t	2025-06-08 13:40:18.026163	["0","1"]	\N
322	163	34	\N	f	2025-06-08 13:40:18.028067	<?php\r\nfunction sum_array($arr) {\r\n    $sum = 0;\r\n    foreach ($arr as $value) {\r\n        $sum += $value;\r\n    }\r\n    return $sum;\r\n}\r\n\r\n// Пример использования\r\n$numbers = [1, 2, 3, 4, 5];\r\necho sum_array($numbers); // Выведет: 15\r\n?>	
323	164	73	157	t	2025-06-08 13:40:51.840934	\N	\N
324	164	74	161	f	2025-06-08 13:40:51.843914	["0"]	\N
325	164	75	\N	f	2025-06-08 13:40:51.846044	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	НЕПРАВИЛЬНО: функция sumOfArrayElements не содержит корректного алгоритма для суммирования элементов массива.
326	165	73	160	f	2025-06-08 13:41:17.258843	\N	\N
327	165	74	164	f	2025-06-08 13:41:17.261218	["3"]	\N
328	165	75	\N	f	2025-06-08 13:41:17.263225	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	НЕПРАВИЛЬНО: функция sumOfArrayElements не содержит корректного алгоритма для вычисления суммы элементов массива.
329	166	73	160	f	2025-06-08 13:45:15.344847	\N	\N
330	166	74	164	f	2025-06-08 13:45:15.347534	["3"]	\N
331	166	75	\N	f	2025-06-08 13:45:15.349652	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	НЕПРАВИЛЬНО: функция sumOfArrayElements не содержит корректного алгоритма для вычисления суммы элементов массива.
332	171	73	160	f	2025-06-08 13:52:35.364728	\N	\N
333	171	74	164	f	2025-06-08 13:52:35.368953	["3"]	\N
334	171	75	\N	f	2025-06-08 13:52:35.372469	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	НЕПРАВИЛЬНО: функция sumOfArrayElements не содержит корректного алгоритма для вычисления суммы элементов массива.
335	172	73	160	f	2025-06-08 13:54:36.578737	\N	\N
336	172	74	163	f	2025-06-08 13:54:36.580982	["2"]	\N
337	172	75	\N	f	2025-06-08 13:54:36.583036	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    // ваш код здесь\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	НЕПРАВИЛЬНО: функция sumOfArrayElements не содержит корректного алгоритма для вычисления суммы элементов массива.
338	173	73	157	t	2025-06-08 13:55:15.850263	\N	\N
339	173	74	161	f	2025-06-08 13:55:15.853121	["0"]	\N
340	173	75	\N	t	2025-06-08 13:55:15.855237	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    $res = array_sum($array);\r\n    return $res;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	ПРАВИЛЬНО: задача решена корректно, функция sumOfArrayElements правильно использует встроенную функцию array_sum для вычисления суммы элементов массива.
341	174	66	143	t	2025-06-08 14:06:30.790267	\N	\N
342	174	67	147	t	2025-06-08 14:06:30.793127	["0","1"]	\N
343	174	68	\N	t	2025-06-08 14:06:30.79461	["0","1"]	\N
344	174	70	\N	f	2025-06-08 14:06:30.796517	<?php\r\n\r\nfunction sumOfFourElements($arr) {\r\n    // Ваш код здесь\r\n    return null;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfFourElements($array);\r\necho $result;\r\n?>	НЕПРАВИЛЬНО: функция sumOfFourElements не возвращает корректную сумму четырёх элементов массива. Она возвращает null, что не соответствует ожидаемому результату.
345	174	71	\N	f	2025-06-08 14:06:30.798373	# Напишите ваш код здесь	НЕПРАВИЛЬНО: код не представлен
346	174	72	\N	f	2025-06-08 14:06:30.800116	#include <iostream>\r\n\r\nint main() {\r\n    int a = 3;\r\n    int b = 5;\r\n    int res;\r\n\r\n    // Здесь должно быть решение\r\n\r\n    std::cout << "Результат: " << res << std::endl;\r\n    return 0;\r\n}	НЕПРАВИЛЬНО: необходимо добавить операцию сложения переменных a и b и присвоить результат переменной res.
347	175	66	\N	f	2025-06-08 14:06:37.927413	\N	\N
348	175	67	\N	f	2025-06-08 14:06:37.929609	null	\N
356	177	70	\N	f	2025-06-08 14:11:02.047897	<?php\r\n\r\nfunction sumOfFourElements($arr) {\r\n    $res = \r\n    return null;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfFourElements($array);\r\necho $result;\r\n?>	НЕПРАВИЛЬНО: функция sumOfFourElements не возвращает корректную сумму элементов массива. Она возвращает null.
357	177	71	\N	t	2025-06-08 14:11:02.050816	print("hello world")	ПРАВИЛЬНО: код корректно выводит 'hello world'
358	177	72	\N	t	2025-06-08 14:11:02.05329	#include <iostream>\r\n\r\nint main() {\r\n    int a = 3;\r\n    int b = 5;\r\n    int res = a+b;\r\n\r\n    // Здесь должно быть решение\r\n\r\n    std::cout << "Результат: " << res << std::endl;\r\n    return 0;\r\n}	ПРАВИЛЬНО: задача решена корректно, переменные a и b складываются и результат сохраняется в переменную res, которая затем выводится на экран.
359	178	73	157	t	2025-06-08 14:34:16.803606	\N	\N
360	178	74	161	f	2025-06-08 14:34:16.806246	["0"]	\N
361	178	75	\N	t	2025-06-08 14:34:16.808434	<?php\r\n\r\nfunction sumOfArrayElements(array $array): int {\r\n    $res = array_sum($array);\r\n    return $res;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumOfArrayElements($array);\r\n\r\necho $result; // вывод результата\r\n?>	ПРАВИЛЬНО: задача решена корректно, функция sumOfArrayElements правильно использует встроенную функцию array_sum для вычисления суммы элементов массива.
\.


--
-- TOC entry 3686 (class 0 OID 24863)
-- Dependencies: 241
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
22	20	9	2025-06-02 16:17:18.475066	\N	\N	4	in_progress
23	20	9	2025-06-02 16:18:42.428812	2025-06-02 16:18:42.428812	4	4	completed
24	24	28	2025-06-03 10:37:44.584512	\N	\N	4	in_progress
25	24	17	2025-06-03 10:38:33.378714	2025-06-03 10:38:33.378714	4	4	completed
26	23	2	2025-06-04 11:22:39.290023	2025-06-04 11:22:39.290023	1	2	completed
27	23	9	2025-06-04 12:44:54.778764	2025-06-04 12:44:54.778764	1	4	completed
28	23	3	2025-06-04 12:53:38.274658	2025-06-04 12:53:38.274658	0	4	completed
29	23	10	2025-06-04 13:03:16.459706	2025-06-04 13:03:16.459706	1	4	completed
30	23	6	2025-06-04 13:10:57.996536	2025-06-04 13:10:57.996536	1	4	completed
31	23	8	2025-06-04 13:12:56.434082	2025-06-04 13:12:56.434082	1	4	completed
32	23	7	2025-06-04 13:25:40.858768	2025-06-04 13:25:40.858768	0	4	completed
33	23	12	2025-06-04 13:26:30.359259	2025-06-04 13:26:30.359259	1	4	completed
34	23	15	2025-06-04 13:29:02.872759	2025-06-04 13:29:02.872759	1	4	completed
35	23	19	2025-06-04 13:35:28.984837	2025-06-04 13:35:28.984837	1	4	completed
36	23	25	2025-06-04 13:40:25.938697	2025-06-04 13:40:25.938697	1	4	completed
37	23	20	2025-06-04 13:45:18.248876	2025-06-04 13:45:18.248876	0	4	completed
38	23	22	2025-06-04 13:51:24.689525	2025-06-04 13:51:24.689525	1	4	completed
39	23	18	2025-06-04 13:54:27.975558	2025-06-04 13:54:27.975558	1	4	completed
40	23	13	2025-06-04 13:59:34.794949	2025-06-04 13:59:34.794949	0	4	completed
41	23	29	2025-06-04 15:03:49.893471	2025-06-04 15:03:49.893471	1	4	completed
42	23	24	2025-06-04 15:10:43.805876	2025-06-04 15:10:43.805876	1	4	completed
43	23	30	2025-06-04 15:18:48.617297	2025-06-04 15:18:48.617297	1	4	completed
44	23	31	2025-06-04 15:25:23.531451	2025-06-04 15:25:23.531451	1	4	completed
45	27	2	2025-06-05 09:56:04.351968	2025-06-05 09:56:04.351968	0	3	completed
46	27	9	2025-06-05 09:57:42.12776	2025-06-05 09:57:42.12776	0	3	completed
47	27	10	2025-06-05 09:58:42.45804	2025-06-05 09:58:42.45804	0	3	completed
48	27	3	2025-06-05 09:59:42.623881	2025-06-05 09:59:42.623881	0	3	completed
49	27	18	2025-06-05 10:07:03.678088	2025-06-05 10:07:03.678088	0	3	completed
50	27	8	2025-06-05 10:18:10.566514	2025-06-05 10:18:10.566514	0	3	completed
51	27	16	2025-06-05 10:20:03.590231	2025-06-05 10:20:03.590231	0	3	completed
52	27	14	2025-06-05 10:21:57.351392	2025-06-05 10:21:57.351392	0	3	completed
53	27	29	2025-06-05 10:23:49.763352	2025-06-05 10:23:49.763352	0	3	completed
54	27	13	2025-06-05 10:28:01.682448	2025-06-05 10:28:01.682448	0	3	completed
55	27	22	2025-06-05 10:47:02.656253	2025-06-05 10:47:02.656253	0	3	completed
56	27	31	2025-06-05 10:50:19.320883	2025-06-05 10:50:19.320883	0	3	completed
57	27	30	2025-06-05 10:52:56.682251	2025-06-05 10:52:56.682251	0	3	completed
58	27	25	2025-06-05 10:55:15.302969	2025-06-05 10:55:15.302969	0	3	completed
59	27	6	2025-06-05 11:01:42.242027	2025-06-05 11:01:42.242027	0	3	completed
60	27	12	2025-06-05 11:08:13.23892	2025-06-05 11:08:13.23892	0	3	completed
61	27	15	2025-06-05 11:35:53.806717	2025-06-05 11:35:53.806717	0	3	completed
62	27	32	2025-06-05 11:38:29.38211	2025-06-05 11:38:29.38211	0	3	completed
63	27	32	2025-06-05 11:38:33.113066	2025-06-05 11:38:33.113066	0	3	completed
64	27	32	2025-06-05 11:42:37.238675	2025-06-05 11:42:37.238675	0	3	completed
65	27	32	2025-06-05 11:42:41.580752	2025-06-05 11:42:41.580752	0	3	completed
66	27	32	2025-06-05 11:42:53.734424	2025-06-05 11:42:53.734424	0	3	completed
67	27	32	2025-06-05 11:43:43.322062	2025-06-05 11:43:43.322062	0	3	completed
68	23	32	2025-06-05 11:45:02.210447	2025-06-05 11:45:02.210447	0	4	completed
69	27	19	2025-06-05 12:44:13.028975	2025-06-05 12:44:13.028975	0	3	completed
70	28	7	2025-06-05 13:03:34.119297	2025-06-05 13:03:34.119297	0	3	completed
71	28	24	2025-06-05 13:03:54.737519	2025-06-05 13:03:54.737519	0	3	completed
72	28	25	2025-06-05 13:05:54.417524	2025-06-05 13:05:54.417524	0	3	completed
73	28	2	2025-06-05 13:07:25.432945	2025-06-05 13:07:25.432945	0	3	completed
74	28	3	2025-06-05 13:10:48.387309	2025-06-05 13:10:48.387309	0	3	completed
75	28	8	2025-06-05 13:13:21.07034	2025-06-05 13:13:21.07034	0	3	completed
76	28	9	2025-06-05 13:26:54.912517	2025-06-05 13:26:54.912517	0	3	completed
77	28	10	2025-06-05 13:28:28.043607	2025-06-05 13:28:28.043607	0	3	completed
78	28	14	2025-06-05 13:56:39.038258	2025-06-05 13:56:39.038258	0	3	completed
79	23	33	2025-06-05 13:58:47.833725	2025-06-05 13:58:47.833725	3	4	completed
80	28	33	2025-06-05 14:08:06.243492	2025-06-05 14:08:06.243492	0	3	completed
81	27	33	2025-06-05 14:08:46.153131	2025-06-05 14:08:46.153131	3	3	completed
82	28	18	2025-06-05 14:12:59.880904	2025-06-05 14:12:59.880904	0	3	completed
83	28	19	2025-06-05 14:18:51.181141	2025-06-05 14:18:51.181141	2	3	completed
84	28	20	2025-06-05 14:28:37.072619	2025-06-05 14:28:37.072619	0	3	completed
85	28	29	2025-06-05 14:30:16.544675	2025-06-05 14:30:16.544675	0	3	completed
86	28	22	2025-06-05 14:34:58.320831	2025-06-05 14:34:58.320831	0	3	completed
87	28	30	2025-06-05 14:39:17.413813	2025-06-05 14:39:17.413813	0	3	completed
120	29	6	2025-06-08 09:47:42.389402	2025-06-08 09:47:42.389402	3	6	completed
121	29	23	2025-06-08 09:58:58.69624	2025-06-08 09:58:58.69624	2	6	completed
122	29	7	2025-06-08 10:01:51.51106	2025-06-08 10:01:51.51106	5	6	completed
123	23	34	2025-06-08 11:23:24.066308	2025-06-08 11:23:24.066308	1	4	completed
124	29	35	2025-06-08 11:38:09.440685	2025-06-08 11:38:09.440685	3	6	completed
125	27	35	2025-06-08 11:48:30.290405	2025-06-08 11:48:30.290405	1	3	completed
126	23	35	2025-06-08 12:11:40.68545	\N	\N	\N	in_progress
127	23	35	2025-06-08 12:16:31.217906	\N	\N	\N	in_progress
128	23	35	2025-06-08 12:16:37.445114	\N	\N	\N	in_progress
129	23	35	2025-06-08 12:19:23.895116	\N	\N	\N	in_progress
130	23	35	2025-06-08 12:19:30.101919	\N	\N	\N	in_progress
131	23	35	2025-06-08 12:19:34.227591	\N	\N	\N	in_progress
132	23	35	2025-06-08 12:25:54.448707	\N	\N	\N	in_progress
133	23	35	2025-06-08 12:26:00.190843	\N	\N	\N	in_progress
134	23	35	2025-06-08 12:26:15.184422	\N	\N	\N	in_progress
135	23	35	2025-06-08 12:26:18.66148	\N	\N	\N	in_progress
136	30	35	2025-06-08 12:32:06.836699	\N	\N	\N	in_progress
137	30	35	2025-06-08 12:32:17.669045	\N	\N	\N	in_progress
138	30	35	2025-06-08 12:32:25.566587	\N	\N	\N	in_progress
139	30	35	2025-06-08 12:33:00.959598	\N	\N	\N	in_progress
140	30	2	2025-06-08 12:34:35.397621	\N	\N	\N	in_progress
141	30	2	2025-06-08 12:37:00.238581	\N	\N	\N	in_progress
142	30	2	2025-06-08 12:37:05.872379	\N	\N	\N	in_progress
143	30	2	2025-06-08 12:40:21.448249	\N	\N	\N	in_progress
144	30	2	2025-06-08 12:42:55.031833	\N	\N	\N	in_progress
145	30	2	2025-06-08 12:43:04.93368	\N	\N	\N	in_progress
146	29	2	2025-06-08 12:43:18.529954	\N	\N	\N	in_progress
147	29	2	2025-06-08 12:44:44.479291	\N	\N	\N	in_progress
148	29	2	2025-06-08 12:45:38.759944	\N	\N	\N	in_progress
149	29	2	2025-06-08 12:46:45.459908	\N	\N	\N	in_progress
150	29	2	2025-06-08 12:48:57.852525	\N	\N	\N	in_progress
151	29	2	2025-06-08 12:55:37.37882	\N	\N	\N	in_progress
152	30	2	2025-06-08 12:55:51.996402	\N	\N	\N	in_progress
153	30	2	2025-06-08 12:59:38.724996	\N	\N	\N	in_progress
154	30	2	2025-06-08 13:09:44.618505	\N	\N	\N	in_progress
155	30	2	2025-06-08 13:16:05.219618	\N	\N	\N	in_progress
156	29	2	2025-06-08 13:16:55.804235	\N	\N	\N	in_progress
157	29	2	2025-06-08 13:19:34.530357	\N	\N	\N	in_progress
158	30	6	2025-06-08 13:22:40.624343	\N	\N	\N	in_progress
159	30	6	2025-06-08 13:25:20.516925	\N	\N	\N	in_progress
160	30	6	2025-06-08 13:25:41.947381	\N	\N	\N	in_progress
161	30	6	2025-06-08 13:26:59.606356	\N	\N	\N	in_progress
162	30	6	2025-06-08 13:39:52.261883	2025-06-08 13:39:52.261883	1	3	completed
163	24	6	2025-06-08 13:40:18.019199	2025-06-08 13:40:18.019199	1	4	completed
164	30	9	2025-06-08 13:40:51.800491	2025-06-08 13:40:51.800491	1	3	completed
165	30	8	2025-06-08 13:41:17.219014	2025-06-08 13:41:17.219014	0	3	completed
166	30	22	2025-06-08 13:45:15.340142	2025-06-08 13:45:15.340142	0	3	completed
171	30	15	2025-06-08 13:52:35.358612	2025-06-08 13:52:35.358612	0	3	completed
172	30	15	2025-06-08 13:54:36.574395	2025-06-08 13:54:36.574395	0	3	completed
173	30	15	2025-06-08 13:55:15.810594	2025-06-08 13:55:15.810594	2	3	completed
174	29	15	2025-06-08 14:06:30.751323	2025-06-08 14:06:30.751323	3	6	completed
175	29	15	2025-06-08 14:06:37.923699	2025-06-08 14:06:37.923699	0	6	completed
177	29	15	2025-06-08 14:11:02.036384	2025-06-08 14:11:02.036384	5	6	completed
178	30	6	2025-06-08 14:34:16.79889	2025-06-08 14:34:16.79889	2	3	completed
\.


--
-- TOC entry 3698 (class 0 OID 41352)
-- Dependencies: 253
-- Data for Name: test_grade_levels; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.test_grade_levels (id_level, id_test, min_percentage, max_percentage, grade_name, grade_color) FROM stdin;
1	23	0	59	Не пройдено	#DB2828
2	23	60	74	Удовлетворительно	#F2711C
3	23	75	89	Хорошо	#2185D0
4	23	90	100	Отлично	#21BA45
\.


--
-- TOC entry 3670 (class 0 OID 24650)
-- Dependencies: 225
-- Data for Name: tests; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.tests (id_test, id_step, name_test, desc_test, passing_percentage, max_attempts, time_between_attempts, show_results_after_completion, practice_mode) FROM stdin;
20	56	Новый тест		70	3	0	t	f
21	58	Новый тест		70	3	0	t	f
22	61	Новый тест		70	3	0	t	f
24	67	Новый тест		70	3	0	t	f
25	68	Новый тест		70	3	0	t	f
27	70	Новый тест		70	3	0	t	f
28	71	Новый тест		70	3	0	t	f
29	72	Новый тест		70	3	0	t	f
30	81	Новый тест		70	3	0	t	f
23	64	Новый тест		70	3	0	t	f
\.


--
-- TOC entry 3684 (class 0 OID 24846)
-- Dependencies: 239
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
9	55	2025-06-02 16:17:16.669472
28	63	2025-06-03 10:01:16.281781
9	63	2025-06-03 10:36:33.188469
17	66	2025-06-03 10:38:23.072659
2	66	2025-06-04 10:58:24.643045
7	65	2025-06-04 13:53:38.264113
29	65	2025-06-04 15:04:58.106382
7	75	2025-06-08 10:03:29.795102
7	78	2025-06-08 10:56:08.530193
7	79	2025-06-08 10:56:09.24145
34	78	2025-06-08 11:15:47.394419
34	79	2025-06-08 11:15:50.33032
35	78	2025-06-08 11:36:21.163245
35	79	2025-06-08 11:36:25.904314
35	80	2025-06-08 11:47:45.234354
35	63	2025-06-08 12:30:15.269237
35	66	2025-06-08 12:30:24.49723
35	75	2025-06-08 12:33:19.712246
35	77	2025-06-08 12:33:20.460418
2	80	2025-06-08 12:33:56.180271
2	78	2025-06-08 12:34:03.93859
2	79	2025-06-08 12:34:05.055286
6	78	2025-06-08 13:22:24.011315
6	79	2025-06-08 13:22:24.874757
6	66	2025-06-08 13:39:59.071618
\.


--
-- TOC entry 3692 (class 0 OID 41275)
-- Dependencies: 247
-- Data for Name: user_tag_interests; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.user_tag_interests (id_user, id_tag, interest_weight) FROM stdin;
15	5	2
15	2	1.5
7	5	1.5
15	6	1.5
2	5	2
2	2	1.5
34	5	1.5
2	6	1.5
6	2	1.5
6	5	2
6	6	1.5
35	2	1.5
35	5	2
35	6	1.5
28	5	2
28	2	1.5
28	6	1.5
\.


--
-- TOC entry 3681 (class 0 OID 24713)
-- Dependencies: 236
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.users (id_user, fn_user, birth_user, uni_user, role_user, spec_user, year_user, login_user, password_user, criminal_record_file, status, moderation_comment, student_card, passport_file, diploma_file) FROM stdin;
4	ФЫВФЫВФЫВ	123123-03-12	ФЫВФЫВ	student	ФЫВФЫВ	3	asd	$2y$12$0noOBih5NYmUNVJk08HQlOLfPcjDMwnzntxUg06gwmqnv2Wev1Wmi	\N	approved	\N	\N	\N	\N
6	Болдырев Максим Романович	2003-03-19	фыв	student	фыв	1	manager	$2y$12$3JDXebUCeJHsbPEF0/uM7.O6UYRup4zhSC4AvP3cF14xddULeyNQm	\N	approved	\N	\N	\N	\N
1	Гаршина Юлия	2003-03-19	ЛГТУ	admin	Инфа	3	garshina	$2y$12$XhKZi1qqaOMi7UFQgTsCluemygyw3INk6V/k9y9XuNEgshqnJYHG6	\N	approved	\N	\N	\N	\N
23	Гаршина Юлия	2003-03-19	dsf	student	dsf	1	garshina@mail.ru	$2y$12$cSKjisnLBIeloMpDWHwJJeTQMIC/Uas5.0Ufpzkin0LGhr2FL65iO	\N	approved		uploads/students/student_card_683dbca51878e_default.jpg	\N	\N
7	Мария Егоровна	2003-03-19	фыв	student	фыв	3	mashka	$2y$12$pvBNY22tc/rV1QE4chBP5.AL8wNE.VEdxQNcd3.gf7wBbrLdDGWfi	\N	approved	\N	\N	\N	\N
24	Гаршина Юлия	2003-03-19	ыфва	student	фыва	1	maxboltik@mail.ru	$2y$12$faxrVU94tln97uCpaz4tP.9RsyCkrnjHkm1lFJq8VixJKA5tPeW5u	\N	approved	Все круто	uploads/students/student_card_683dc1763deda_default.jpg	\N	\N
25	Болдырев Максим Романович	2003-03-19	asfd	student	sdf	3	yaz678@bk.ru	$2y$12$RgaPVqclsFdSKX6RvPKaoe/whNqAV.SpN6SvgO2MsfFV5oFt6hyQW	\N	approved	Все некруто	uploads/students/student_card_683dc20addef0_Приказ КС.pdf	\N	\N
26	фыфыфы	2003-03-19	вап	teacher	вап	2	maxrox1904@gmail.com	$2y$12$Qm1TDSSRSkH38LA9jK5Ne.Zn.T6pDx02f6PRnU5VHxmqv3W8KxR76	uploads/teachers/criminal/criminal_683dc32d567f7_rNo_c2bRwys.jpg	approved		\N	uploads/teachers/passport/passport_683dc32d567f0_diplom.sql	uploads/teachers/diploma/diploma_683dc32d567f6_Приказ КС.pdf
2	Болдырев Максим Романович	2003-03-19	ЛГТУ	student	Инфа	2	maxim	$2y$12$tyO9ZJdqQHaFiyMY3eLYyehNtDKKgthQD.ErdkouOg9eqSu8Y299i	\N	approved	\N	\N	\N	\N
3	Пупкин Кирилл Васильевич	2003-03-19	ФЫВ	student	ФЫВ	1	worker	$2y$12$Vi9IlObR0cP9O86E3gyIK.06lwsRV4MpXhaB9YpLvff.5gpSa4nxa	\N	approved	\N	\N	\N	\N
8	Пупкин Кирилл Васильевич	2003-03-19	фыв	student	фыв	1	dog	$2y$12$41pVBQvZpN2zD6JNkdURrOlYgRpRqapJyO.MEbtgwLjzs2l0ev9mq	\N	approved	\N	\N	\N	\N
9	asdasdasdasd	2003-03-19	asd	student	asd`	1	cat	$2y$12$hw0LPH3ILsiY/Qtak8MW0Oa.rfC3JCrgLYV8IVR9HRlctlN/B6Ztu	\N	approved	\N	\N	\N	\N
10	Max Rox	2003-03-19	123	student	123	2	rox1	$2y$12$K7Qi8alfy9gYanCwv9ZvWeUVI8wiTr7XnWrEZGHcZHJVnPQdYuuT2	\N	approved	\N	\N	\N	\N
11	Maxim Boldyrev	2003-03-19	фыв	student	фыв	4	max_rox7	$2y$12$sEvDhwQcjxeO8sXke3ivUed1A2z5qIeT5JNFJ3wU49NqTmDnFzjcu	\N	approved	\N	\N	\N	\N
12	Болдырев Максим Романович	2003-03-19	asd	student	asd	1	maxrox	$2y$12$oyPCevriVynO7YmyLXHUUOaE7Wk83iZq6K.iAfZke1DPihGi7HfRq	\N	approved	\N	\N	\N	\N
13	Гаршина Юлия	2003-03-19	фяыва	student	щортвыа	3	gart	$2y$12$6hHdMcB9TTAqvotEQJCqJ.H9zeC1e.UbrhrIvgUCo3xg3aGHyn8ce	\N	approved	\N	\N	\N	\N
14	Болдырев Максим Романович	2003-03-19	asd	student	asd	1	maxaaa	$2y$12$1bjYwQK39JrB60R2G8ulrODe8AAoQEiGVW0H.r9st0x6dvozqScuG	\N	approved	\N	\N	\N	\N
15	Болдырев Максим Романович	2003-03-19	ысвм	student	длоит	5	cat1	$2y$12$vLBn1cyuIjtT2/obwCxkZu0SbvFN.oUs/wCbwhvoGvm1F2KIlVOJW	\N	approved	\N	\N	\N	\N
16	asdsadas	0203-03-19	kjb	student	kjb	2	maxi	$2y$12$.QZy.iCVW5OXcrJG1G1jyuIXiP3eTSoX4bVcdIPxO5wWdPsyRw1.O	\N	approved	\N	\N	\N	\N
17	Гаршина Юлия	2003-03-19	asd	student	sad	3	max	$2y$12$jiZgCcQVQCS/0cZoz/9xkOddBOEo7rF/n/QmYGhYJfhWdKcpXbD1C	\N	approved	\N	\N	\N	\N
18	Гаршина Юлия	2003-03-19	фыа	student	фыва	2	rox2	$2y$12$.dAcyngBQg7FtyWNKLg8fe2Ba4NFx14M6CuRsMQFJajRr..gJYN6O	\N	approved	\N	\N	\N	\N
19	asdasdasd	2003-03-19	asd	student	asd	2	okak	$2y$12$Y3XpprFz4xhnXxGFcxyrmuv0/4fH.corayejUPn5bjmGXRWXAKL6W	\N	approved	\N	\N	\N	\N
20	Болдырев Максим Романович	2003-03-19	asd	student	asd	3	max1	$2y$12$DesbJupvhqEH7hsFhMXo3uOTvrrelfSA.rIrXQD4m0vsgx5KnZoGa	\N	approved	\N	\N	\N	\N
29	фыфыфыфы	2003-03-19	вап	student	вапр	2	asdasdasdbn@mail.ru	$2y$12$Mrw4FkYaP10HzGcwAxOmRePRvDwQtOpbn5Qog55mBncn0i/pwmHOu	\N	approved	\N	uploads/students/student_card_68405fab0f76a_diplom_dump.sql	\N	\N
22	фывфывфыв	2003-03-19	ыва	student	выа	1	ooooo	$2y$12$UCh6tvfpNQJoS78o0Xaj8uOaXBb/.KBSHTPBXyS96vQp0YUR60Jf2	\N	approved	\N	\N	\N	\N
28	Золотарева Мария Егоровна	1999-07-20	ЛГТУ	teacher	Туризм	4	quixotesoul@gmail.com	$2y$12$v8DyFv1arPtPubx8fMP4MOdy8zm1R6AkAq5wOFGQJm8.JOV.dc/uO	\N	approved	секси	uploads/students/student_card_683ec79d06858_default.jpg	\N	\N
30	asasasas	20003-03-19	asd	student	asd	2	a8a9a8@bk.ru	$2y$12$Az/CpJQPtG.B77g6NlIpFO2UViYKZK3T5wOTMm8R6OI8Ty0cZcaB6	\N	approved	\N	uploads/students/student_card_6840639974cbb_diplom_dump.sql	\N	\N
21	Maxim Boldyrev	2003-03-19	ЛГТУ	teacher	Информатика	1	oops	$2y$12$O4xRZxgvK/WJPH0JKq9XV.YtyN7eGZjqDNu3ZwBh5wHDnLNYbknlu	\N	approved	\N	\N	\N	\N
27	Пупкин Кирилл Васильевич	2003-03-19	ыфва	teacher	ваып	2	asdsadasD@mail.ru	$2y$12$2T9g.ZfYsYaewsakcw20NO.ioKXZBJ2yC9SKTBffgc17cjZJFkCI6	uploads/teachers/criminal/criminal_683dc7568fa22_default.jpg	approved	\N	\N	uploads/teachers/passport/passport_683dc7568fa1b_default.jpg	uploads/teachers/diploma/diploma_683dc7568fa20_0QwgmBt4IN8.jpg
31	ЫФЫФЫФЫ	2003-03-19	ФЫ	student	ФЫ	2	russia@bkb.ru	$2y$12$rJJXMmL.KpS1yjK3o1CsReJA9dhP.hwKK15aJahPA/xECPXdjPRbS	\N	approved	\N	uploads/students/student_card_6840648c25f7f_curl_test.php	\N	\N
32	asasa	3712-09-12	12	student	12	1	rus@bbkbkb.ru	$2y$12$rbAZ2Jx/R7SuopucqEwMWuputR/R/wenV7zvWvZMAIqZQ84Cqu0I.	\N	approved	\N	uploads/students/student_card_684181a06bf8c_requirements.txt	\N	\N
33	ыфыфы	2003-03-19	ыв	student	ыв	1	jasjasj@bkk.ru	$2y$12$jILnwfJsvj1AJ2Tvy7NaMeGl5LN4GlRH9QuJ1hb/UukFeIaTvC3ti	\N	approved	\N	uploads/students/student_card_6841a259ea915_main.py	\N	\N
34	Тестов Тест Тестович	2003-03-19	ЛГТУ	student	Информатика	3	sdfsdf@bkbkbkb.ru	$2y$12$D1WPAk01wOT9vqd.xqe95eBHgxuar5IjshARd/79h5DC1oWjVMXVm	\N	approved		uploads/students/student_card_684570ca4a810_requirements.txt	\N	\N
35	Зверев Алексей Генадьевич	2002-07-04	ЛГТУ	student	Инфа	1	work.zverev2002@mail.ru	$2y$12$3G6nKu2dh6kLEy4SwfvluOsYF7pXBJ4sfSPzJ0RXa7afC8OcdYU2S	\N	approved	секси	uploads/students/student_card_68457531eeff6_default.jpg	\N	\N
38	фыфыфыфы фыфыфы фыфыфы	2003-03-19		student		0	sdflgjkdflkg@bkbkb.ru	$2y$12$nHaYehrth5tvn1.x/q7HcOF2fxEmG4dI8P75UMDe6lwfWMxANHIE6	\N	approved	\N	\N	\N	\N
37	asasasas	2003-03-19	ЛГТУ	student	Информатика	4	dsgfsdgf@basasask.ru	$2y$12$gzwunUnA4cmBbB3FnSvME.cHKTbjZzmJWsNr.kqy0sTQZF/pRNdzu	\N	approved		uploads/students/student_card_684c078f92728_default.jpg	\N	\N
36	Преподаватель Преподавалов Преподаваловчи	2003-03-19	ЛГТУ	teacher	\N	\N	sfgdfg@bk.ru	$2y$12$YqJxonx9p0bmnVYB2/Xe5Ov.p5fY1UdUsh8I7CDLqtUYlnrH0znRS	uploads/teachers/criminal/criminal_684c0772699f4_default.jpg	approved		\N	uploads/teachers/passport/passport_684c0772699eb_auto_clicker.py	uploads/teachers/diploma/diploma_684c0772699f2_main.py
\.


--
-- TOC entry 3727 (class 0 OID 0)
-- Dependencies: 217
-- Name: answer_options_id_option_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answer_options_id_option_seq', 164, true);


--
-- TOC entry 3728 (class 0 OID 0)
-- Dependencies: 237
-- Name: certificates_id_certificate_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.certificates_id_certificate_seq', 1, true);


--
-- TOC entry 3729 (class 0 OID 0)
-- Dependencies: 226
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.code_tasks_id_ct_seq', 13, true);


--
-- TOC entry 3730 (class 0 OID 0)
-- Dependencies: 228
-- Name: course_id_course_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.course_id_course_seq', 31, true);


--
-- TOC entry 3731 (class 0 OID 0)
-- Dependencies: 249
-- Name: course_views_id_view_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.course_views_id_view_seq', 152, true);


--
-- TOC entry 3732 (class 0 OID 0)
-- Dependencies: 231
-- Name: feedback_id_feedback_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.feedback_id_feedback_seq', 15, true);


--
-- TOC entry 3733 (class 0 OID 0)
-- Dependencies: 233
-- Name: lessons_id_lesson_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.lessons_id_lesson_seq', 37, true);


--
-- TOC entry 3734 (class 0 OID 0)
-- Dependencies: 220
-- Name: questions_id_question_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.questions_id_question_seq', 75, true);


--
-- TOC entry 3735 (class 0 OID 0)
-- Dependencies: 222
-- Name: steps_id_step_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.steps_id_step_seq', 81, true);


--
-- TOC entry 3736 (class 0 OID 0)
-- Dependencies: 244
-- Name: tags_id_tag_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.tags_id_tag_seq', 30, true);


--
-- TOC entry 3737 (class 0 OID 0)
-- Dependencies: 242
-- Name: test_answers_id_answer_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_answers_id_answer_seq', 361, true);


--
-- TOC entry 3738 (class 0 OID 0)
-- Dependencies: 240
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_attempts_id_attempt_seq', 178, true);


--
-- TOC entry 3739 (class 0 OID 0)
-- Dependencies: 252
-- Name: test_grade_levels_id_level_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_grade_levels_id_level_seq', 4, true);


--
-- TOC entry 3740 (class 0 OID 0)
-- Dependencies: 224
-- Name: tests_id_test_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.tests_id_test_seq', 30, true);


--
-- TOC entry 3741 (class 0 OID 0)
-- Dependencies: 235
-- Name: users_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.users_id_user_seq', 38, true);


--
-- TOC entry 3455 (class 2606 OID 24827)
-- Name: certificates certificates_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_pkey PRIMARY KEY (id_certificate);


--
-- TOC entry 3475 (class 2606 OID 41300)
-- Name: course_statistics course_statistics_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_statistics
    ADD CONSTRAINT course_statistics_pkey PRIMARY KEY (id_course);


--
-- TOC entry 3471 (class 2606 OID 41264)
-- Name: course_tags course_tags_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_pkey PRIMARY KEY (id_course, id_tag);


--
-- TOC entry 3477 (class 2606 OID 41313)
-- Name: course_views course_views_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_pkey PRIMARY KEY (id_view);


--
-- TOC entry 3411 (class 2606 OID 24584)
-- Name: answer_options pk_answer_options; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT pk_answer_options PRIMARY KEY (id_option);


--
-- TOC entry 3432 (class 2606 OID 24668)
-- Name: code_tasks pk_code_tasks; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT pk_code_tasks PRIMARY KEY (id_ct);


--
-- TOC entry 3435 (class 2606 OID 24679)
-- Name: course pk_course; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course
    ADD CONSTRAINT pk_course PRIMARY KEY (id_course);


--
-- TOC entry 3441 (class 2606 OID 24685)
-- Name: create_passes pk_create_passes; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT pk_create_passes PRIMARY KEY (id_course, id_user);


--
-- TOC entry 3445 (class 2606 OID 24697)
-- Name: feedback pk_feedback; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT pk_feedback PRIMARY KEY (id_feedback);


--
-- TOC entry 3449 (class 2606 OID 24708)
-- Name: lessons pk_lessons; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT pk_lessons PRIMARY KEY (id_lesson);


--
-- TOC entry 3415 (class 2606 OID 24908)
-- Name: material pk_material; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT pk_material PRIMARY KEY (id_material);


--
-- TOC entry 3418 (class 2606 OID 24614)
-- Name: questions pk_questions; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT pk_questions PRIMARY KEY (id_question);


--
-- TOC entry 3422 (class 2606 OID 24646)
-- Name: steps pk_steps; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT pk_steps PRIMARY KEY (id_step);


--
-- TOC entry 3426 (class 2606 OID 24657)
-- Name: tests pk_tests; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT pk_tests PRIMARY KEY (id_test);


--
-- TOC entry 3452 (class 2606 OID 24720)
-- Name: users pk_users; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT pk_users PRIMARY KEY (id_user);


--
-- TOC entry 3479 (class 2606 OID 41333)
-- Name: student_analytics student_analytics_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_pkey PRIMARY KEY (id_user, id_course);


--
-- TOC entry 3484 (class 2606 OID 41372)
-- Name: student_test_settings student_test_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_pkey PRIMARY KEY (id_user, id_test);


--
-- TOC entry 3467 (class 2606 OID 41259)
-- Name: tags tags_name_tag_key; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_name_tag_key UNIQUE (name_tag);


--
-- TOC entry 3469 (class 2606 OID 41257)
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id_tag);


--
-- TOC entry 3465 (class 2606 OID 24890)
-- Name: test_answers test_answers_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_pkey PRIMARY KEY (id_answer);


--
-- TOC entry 3460 (class 2606 OID 24872)
-- Name: test_attempts test_attempts_id_test_id_user_start_time_key; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_test_id_user_start_time_key UNIQUE (id_test, id_user, start_time);


--
-- TOC entry 3462 (class 2606 OID 24870)
-- Name: test_attempts test_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_pkey PRIMARY KEY (id_attempt);


--
-- TOC entry 3482 (class 2606 OID 41361)
-- Name: test_grade_levels test_grade_levels_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels
    ADD CONSTRAINT test_grade_levels_pkey PRIMARY KEY (id_level);


--
-- TOC entry 3457 (class 2606 OID 24851)
-- Name: user_material_progress user_material_progress_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_pkey PRIMARY KEY (id_user, id_step);


--
-- TOC entry 3473 (class 2606 OID 41280)
-- Name: user_tag_interests user_tag_interests_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_pkey PRIMARY KEY (id_user, id_tag);


--
-- TOC entry 3420 (class 1259 OID 24648)
-- Name: also_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX also_include_fk ON public.steps USING btree (id_lesson);


--
-- TOC entry 3408 (class 1259 OID 24585)
-- Name: answer_options_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answer_options_pk ON public.answer_options USING btree (id_option);


--
-- TOC entry 3428 (class 1259 OID 24669)
-- Name: code_tasks_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX code_tasks_pk ON public.code_tasks USING btree (id_ct);


--
-- TOC entry 3433 (class 1259 OID 24680)
-- Name: course_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX course_pk ON public.course USING btree (id_course);


--
-- TOC entry 3436 (class 1259 OID 24687)
-- Name: create_passes2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes2_fk ON public.create_passes USING btree (id_user);


--
-- TOC entry 3437 (class 1259 OID 24688)
-- Name: create_passes_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes_fk ON public.create_passes USING btree (id_course);


--
-- TOC entry 3438 (class 1259 OID 24686)
-- Name: create_passes_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX create_passes_pk ON public.create_passes USING btree (id_course, id_user);


--
-- TOC entry 3442 (class 1259 OID 24698)
-- Name: feedback_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX feedback_pk ON public.feedback USING btree (id_feedback);


--
-- TOC entry 3443 (class 1259 OID 24699)
-- Name: has_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_fk ON public.feedback USING btree (id_course);


--
-- TOC entry 3429 (class 1259 OID 33057)
-- Name: idx_code_tasks_language; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_code_tasks_language ON public.code_tasks USING btree (language);


--
-- TOC entry 3439 (class 1259 OID 24845)
-- Name: idx_create_passes_date_complete; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_create_passes_date_complete ON public.create_passes USING btree (date_complete);


--
-- TOC entry 3463 (class 1259 OID 24906)
-- Name: idx_test_answers_attempt; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_answers_attempt ON public.test_answers USING btree (id_attempt);


--
-- TOC entry 3458 (class 1259 OID 41383)
-- Name: idx_test_attempts_complete_time; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_attempts_complete_time ON public.test_attempts USING btree (id_test, id_user, end_time);


--
-- TOC entry 3480 (class 1259 OID 41384)
-- Name: idx_test_grade_levels; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_grade_levels ON public.test_grade_levels USING btree (id_test);


--
-- TOC entry 3446 (class 1259 OID 24710)
-- Name: include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX include_fk ON public.lessons USING btree (id_course);


--
-- TOC entry 3447 (class 1259 OID 24709)
-- Name: lessons_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX lessons_pk ON public.lessons USING btree (id_lesson);


--
-- TOC entry 3412 (class 1259 OID 24909)
-- Name: material_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX material_pk ON public.material USING btree (id_material);


--
-- TOC entry 3424 (class 1259 OID 24659)
-- Name: may_also_include2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_also_include2_fk ON public.tests USING btree (id_step);


--
-- TOC entry 3413 (class 1259 OID 24605)
-- Name: may_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_include_fk ON public.material USING btree (id_step);


--
-- TOC entry 3416 (class 1259 OID 24616)
-- Name: mean_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX mean_fk ON public.questions USING btree (id_test);


--
-- TOC entry 3430 (class 1259 OID 24670)
-- Name: might_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX might_include_fk ON public.code_tasks USING btree (id_question);


--
-- TOC entry 3409 (class 1259 OID 24586)
-- Name: must_have_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX must_have_fk ON public.answer_options USING btree (id_question);


--
-- TOC entry 3450 (class 1259 OID 24711)
-- Name: procent_pass_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX procent_pass_fk ON public.lessons USING btree (id_stat);


--
-- TOC entry 3419 (class 1259 OID 24615)
-- Name: questions_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX questions_pk ON public.questions USING btree (id_question);


--
-- TOC entry 3423 (class 1259 OID 24647)
-- Name: steps_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX steps_pk ON public.steps USING btree (id_step);


--
-- TOC entry 3427 (class 1259 OID 24658)
-- Name: tests_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX tests_pk ON public.tests USING btree (id_test);


--
-- TOC entry 3453 (class 1259 OID 24721)
-- Name: users_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX users_pk ON public.users USING btree (id_user);


--
-- TOC entry 3496 (class 2606 OID 24833)
-- Name: certificates certificates_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3497 (class 2606 OID 24828)
-- Name: certificates certificates_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3509 (class 2606 OID 41301)
-- Name: course_statistics course_statistics_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_statistics
    ADD CONSTRAINT course_statistics_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- TOC entry 3505 (class 2606 OID 41265)
-- Name: course_tags course_tags_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- TOC entry 3506 (class 2606 OID 41270)
-- Name: course_tags course_tags_id_tag_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_id_tag_fkey FOREIGN KEY (id_tag) REFERENCES public.tags(id_tag) ON DELETE CASCADE;


--
-- TOC entry 3510 (class 2606 OID 41314)
-- Name: course_views course_views_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- TOC entry 3511 (class 2606 OID 41319)
-- Name: course_views course_views_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 3485 (class 2606 OID 24722)
-- Name: answer_options fk_answer_o_must_have_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT fk_answer_o_must_have_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3490 (class 2606 OID 24782)
-- Name: code_tasks fk_code_tas_might_inc_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT fk_code_tas_might_inc_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3491 (class 2606 OID 24787)
-- Name: create_passes fk_create_p_create_pa_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3492 (class 2606 OID 24792)
-- Name: create_passes fk_create_p_create_pa_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3493 (class 2606 OID 24797)
-- Name: feedback fk_feedback_has_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_has_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3494 (class 2606 OID 24815)
-- Name: feedback fk_feedback_user; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_user FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3495 (class 2606 OID 24802)
-- Name: lessons fk_lessons_include_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_include_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3486 (class 2606 OID 24737)
-- Name: material fk_material_may_inclu_steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT fk_material_may_inclu_steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3487 (class 2606 OID 24742)
-- Name: questions fk_question_mean_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT fk_question_mean_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3488 (class 2606 OID 24772)
-- Name: steps fk_steps_also_incl_lessons; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT fk_steps_also_incl_lessons FOREIGN KEY (id_lesson) REFERENCES public.lessons(id_lesson) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3489 (class 2606 OID 24777)
-- Name: tests fk_tests_may_also__steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT fk_tests_may_also__steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3512 (class 2606 OID 41339)
-- Name: student_analytics student_analytics_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- TOC entry 3513 (class 2606 OID 41334)
-- Name: student_analytics student_analytics_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 3515 (class 2606 OID 41378)
-- Name: student_test_settings student_test_settings_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON DELETE CASCADE;


--
-- TOC entry 3516 (class 2606 OID 41373)
-- Name: student_test_settings student_test_settings_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 3502 (class 2606 OID 24891)
-- Name: test_answers test_answers_id_attempt_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_attempt_fkey FOREIGN KEY (id_attempt) REFERENCES public.test_attempts(id_attempt);


--
-- TOC entry 3503 (class 2606 OID 24896)
-- Name: test_answers test_answers_id_question_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_question_fkey FOREIGN KEY (id_question) REFERENCES public.questions(id_question);


--
-- TOC entry 3504 (class 2606 OID 24901)
-- Name: test_answers test_answers_id_selected_option_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_selected_option_fkey FOREIGN KEY (id_selected_option) REFERENCES public.answer_options(id_option);


--
-- TOC entry 3500 (class 2606 OID 24873)
-- Name: test_attempts test_attempts_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test);


--
-- TOC entry 3501 (class 2606 OID 24878)
-- Name: test_attempts test_attempts_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3514 (class 2606 OID 41362)
-- Name: test_grade_levels test_grade_levels_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels
    ADD CONSTRAINT test_grade_levels_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON DELETE CASCADE;


--
-- TOC entry 3498 (class 2606 OID 24857)
-- Name: user_material_progress user_material_progress_id_step_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_id_step_fkey FOREIGN KEY (id_step) REFERENCES public.steps(id_step);


--
-- TOC entry 3499 (class 2606 OID 24852)
-- Name: user_material_progress user_material_progress_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3507 (class 2606 OID 41286)
-- Name: user_tag_interests user_tag_interests_id_tag_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_id_tag_fkey FOREIGN KEY (id_tag) REFERENCES public.tags(id_tag) ON DELETE CASCADE;


--
-- TOC entry 3508 (class 2606 OID 41281)
-- Name: user_tag_interests user_tag_interests_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


-- Completed on 2025-06-15 10:31:58

--
-- PostgreSQL database dump complete
--

