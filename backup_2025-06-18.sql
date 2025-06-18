--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.5

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

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: pguser
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO pguser;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: pguser
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: answer_options; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.answer_options (
    id_option integer NOT NULL,
    id_question integer,
    text_option character varying(255) NOT NULL
);


ALTER TABLE public.answer_options OWNER TO pguser;

--
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
-- Name: answer_options_id_option_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.answer_options_id_option_seq OWNED BY public.answer_options.id_option;


--
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
-- Name: answers_id_answer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.answers_id_answer_seq OWNED BY public.answers.id_answer;


--
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
-- Name: certificates_id_certificate_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.certificates_id_certificate_seq OWNED BY public.certificates.id_certificate;


--
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
    CONSTRAINT code_tasks_language_check CHECK (((language)::text = ANY (ARRAY[('php'::character varying)::text, ('python'::character varying)::text, ('cpp'::character varying)::text])))
);


ALTER TABLE public.code_tasks OWNER TO pguser;

--
-- Name: TABLE code_tasks; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON TABLE public.code_tasks IS 'Stores code tasks for programming questions with input template and expected output';


--
-- Name: COLUMN code_tasks.input_ct; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.input_ct IS 'Input data or description for the code task';


--
-- Name: COLUMN code_tasks.output_ct; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.output_ct IS 'Expected output that the code should produce';


--
-- Name: COLUMN code_tasks.execution_timeout; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.execution_timeout IS 'Maximum execution time in seconds';


--
-- Name: COLUMN code_tasks.template_code; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.template_code IS 'Starting template code provided to the student';


--
-- Name: COLUMN code_tasks.language; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.language IS 'Programming language for the task (php, python, cpp)';


--
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
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.code_tasks_id_ct_seq OWNED BY public.code_tasks.id_ct;


--
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
-- Name: course_id_course_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.course_id_course_seq OWNED BY public.course.id_course;


--
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
-- Name: course_tags; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.course_tags (
    id_course integer NOT NULL,
    id_tag integer NOT NULL
);


ALTER TABLE public.course_tags OWNER TO pguser;

--
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
-- Name: course_views_id_view_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.course_views_id_view_seq OWNED BY public.course_views.id_view;


--
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
-- Name: feedback; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.feedback (
    id_feedback integer NOT NULL,
    id_course integer NOT NULL,
    text_feedback character varying(5000),
    date_feedback date NOT NULL,
    rate_feedback character varying(5) NOT NULL,
    id_user integer,
    status text DEFAULT 'pending'::text
);


ALTER TABLE public.feedback OWNER TO pguser;

--
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
-- Name: feedback_id_feedback_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.feedback_id_feedback_seq OWNED BY public.feedback.id_feedback;


--
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
-- Name: lessons_id_lesson_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.lessons_id_lesson_seq OWNED BY public.lessons.id_lesson;


--
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
-- Name: questions_id_question_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.questions_id_question_seq OWNED BY public.questions.id_question;


--
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
-- Name: results_id_result_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.results_id_result_seq OWNED BY public.results.id_result;


--
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
-- Name: stat_id_stat_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.stat_id_stat_seq OWNED BY public.stat.id_stat;


--
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
-- Name: steps_id_step_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.steps_id_step_seq OWNED BY public.steps.id_step;


--
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
-- Name: student_test_settings; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.student_test_settings (
    id_user integer NOT NULL,
    id_test integer NOT NULL,
    additional_attempts integer DEFAULT 0
);


ALTER TABLE public.student_test_settings OWNER TO pguser;

--
-- Name: tags; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.tags (
    id_tag integer NOT NULL,
    name_tag character varying(100) NOT NULL
);


ALTER TABLE public.tags OWNER TO pguser;

--
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
-- Name: tags_id_tag_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.tags_id_tag_seq OWNED BY public.tags.id_tag;


--
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
-- Name: COLUMN test_answers.ai_feedback; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.test_answers.ai_feedback IS 'Отзыв ИИ о коде студента';


--
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
-- Name: test_answers_id_answer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_answers_id_answer_seq OWNED BY public.test_answers.id_answer;


--
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
    CONSTRAINT test_attempts_status_check CHECK (((status)::text = ANY (ARRAY[('in_progress'::character varying)::text, ('completed'::character varying)::text, ('abandoned'::character varying)::text])))
);


ALTER TABLE public.test_attempts OWNER TO pguser;

--
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
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_attempts_id_attempt_seq OWNED BY public.test_attempts.id_attempt;


--
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
-- Name: test_grade_levels_id_level_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_grade_levels_id_level_seq OWNED BY public.test_grade_levels.id_level;


--
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
-- Name: tests_id_test_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.tests_id_test_seq OWNED BY public.tests.id_test;


--
-- Name: user_material_progress; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.user_material_progress (
    id_user integer NOT NULL,
    id_step integer NOT NULL,
    completed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_material_progress OWNER TO pguser;

--
-- Name: user_tag_interests; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.user_tag_interests (
    id_user integer NOT NULL,
    id_tag integer NOT NULL,
    interest_weight double precision DEFAULT 1.0
);


ALTER TABLE public.user_tag_interests OWNER TO pguser;

--
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
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- Name: answer_options id_option; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options ALTER COLUMN id_option SET DEFAULT nextval('public.answer_options_id_option_seq'::regclass);


--
-- Name: answers id_answer; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers ALTER COLUMN id_answer SET DEFAULT nextval('public.answers_id_answer_seq'::regclass);


--
-- Name: certificates id_certificate; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates ALTER COLUMN id_certificate SET DEFAULT nextval('public.certificates_id_certificate_seq'::regclass);


--
-- Name: code_tasks id_ct; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks ALTER COLUMN id_ct SET DEFAULT nextval('public.code_tasks_id_ct_seq'::regclass);


--
-- Name: course id_course; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course ALTER COLUMN id_course SET DEFAULT nextval('public.course_id_course_seq'::regclass);


--
-- Name: course_views id_view; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views ALTER COLUMN id_view SET DEFAULT nextval('public.course_views_id_view_seq'::regclass);


--
-- Name: feedback id_feedback; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback ALTER COLUMN id_feedback SET DEFAULT nextval('public.feedback_id_feedback_seq'::regclass);


--
-- Name: lessons id_lesson; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons ALTER COLUMN id_lesson SET DEFAULT nextval('public.lessons_id_lesson_seq'::regclass);


--
-- Name: questions id_question; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions ALTER COLUMN id_question SET DEFAULT nextval('public.questions_id_question_seq'::regclass);


--
-- Name: results id_result; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results ALTER COLUMN id_result SET DEFAULT nextval('public.results_id_result_seq'::regclass);


--
-- Name: stat id_stat; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat ALTER COLUMN id_stat SET DEFAULT nextval('public.stat_id_stat_seq'::regclass);


--
-- Name: steps id_step; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps ALTER COLUMN id_step SET DEFAULT nextval('public.steps_id_step_seq'::regclass);


--
-- Name: tags id_tag; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags ALTER COLUMN id_tag SET DEFAULT nextval('public.tags_id_tag_seq'::regclass);


--
-- Name: test_answers id_answer; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers ALTER COLUMN id_answer SET DEFAULT nextval('public.test_answers_id_answer_seq'::regclass);


--
-- Name: test_attempts id_attempt; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts ALTER COLUMN id_attempt SET DEFAULT nextval('public.test_attempts_id_attempt_seq'::regclass);


--
-- Name: test_grade_levels id_level; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels ALTER COLUMN id_level SET DEFAULT nextval('public.test_grade_levels_id_level_seq'::regclass);


--
-- Name: tests id_test; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests ALTER COLUMN id_test SET DEFAULT nextval('public.tests_id_test_seq'::regclass);


--
-- Name: users id_user; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users ALTER COLUMN id_user SET DEFAULT nextval('public.users_id_user_seq'::regclass);


--
-- Data for Name: answer_options; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.answer_options (id_option, id_question, text_option) FROM stdin;
1	76	Переменная - именованная область в памяти, которая может принимать определенное значение?
2	76	Переменная — это константа, которая не может изменять своё значение.
3	76	Переменная — это команда, которая выполняет определённое действие в программе.
4	76	Переменная — это тип данных, который определяет, какие значения можно хранить в памяти.
5	78	фыфы
6	78	фыфы
7	79	Переменная — именованная область в памяти, которая может принимать определенное значение?
8	79	Переменная — это набор констант, используемых в программе.
9	79	Переменная — это команда, которая выполняет определённые действия в программе.
10	79	Переменная — это характеристика, описывающая тип данных, но не их значение.
11	80	assort
12	80	sort
13	80	sortArray
14	80	ksort
15	81	count()||Подсчитывает количество элементов в массиве
16	81	str_replace()||Заменяет все вхождения подстроки в строке
17	81	array_merge()||Объединяет два или более массивов
18	81	file_get_contents()||Читает содержимое файла в строку
19	83	Переменная — это команда для выполнения определённых операций.
20	83	Переменная — это константа, которая не может изменять своё значение.
21	83	Переменная — именованная область в памяти, которая может принимать определенное значение?
22	83	Переменная — это тип данных, который определяет формат хранения информации.
23	84	count()||Подсчитывает количество элементов в массиве
24	84	str_replace()||Заменяет все вхождения подстроки в строке
25	84	array_merge()||Объединяет два или более массивов
26	84	file_get_contents()||Читает содержимое файла в строку
48	91	Переменная — именованная область в памяти, которая может принимать определенное значение?
49	91	Переменная — это константа, которая не может изменяться в процессе выполнения программы.
50	91	Переменная — это команда, которая выполняет определённые действия в программе.
51	91	Переменная — это характеристика, описывающая тип данных, но не их значение.
52	92	$data = $_POST['field_name'];
53	92	$data = file_get_contents('php://input');
54	92	$data = $_GET['field_name'];
55	92	$data = $_FILES['field_name'];
56	92	$data = $_SESSION['field_name'];
57	93	count()||Подсчитывает количество элементов в массиве
58	93	str_replace()||Заменяет все вхождения подстроки в строке
59	93	array_merge()||Объединяет два или более массивов
60	93	file_get_contents()||Читает содержимое файла в строку
\.


--
-- Data for Name: answers; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.answers (id_answer, id_question, id_user, text_answer) FROM stdin;
\.


--
-- Data for Name: certificates; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.certificates (id_certificate, id_user, id_course, date_issued, certificate_path) FROM stdin;
\.


--
-- Data for Name: code_tasks; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.code_tasks (id_ct, id_question, input_ct, output_ct, execution_timeout, template_code, language) FROM stdin;
14	77		hello world	5	<?php\r\n// Ваш код здесь\r\n?>	php
15	82		15	5	<?php\r\n\r\nfunction sumArrayElements($array) {\r\n    // ваш код здесь\r\n    return $result;\r\n}\r\n\r\n$array = [1, 2, 3, 4, 5];\r\n$result = sumArrayElements($array);\r\n\r\necho $result; // ожидаем вывод числа\r\n?>	php
17	94		15	5	<?php\r\nfunction sumArray(array $array): int {\r\n    // ваш код здесь\r\n}\r\n\r\n// Пример использования функции\r\n$numbers = [1, 2, 3, 4, 5];\r\necho sumArray($numbers); // ожидаем вывод: 15\r\n?>	php
\.


--
-- Data for Name: course; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course (id_course, name_course, desc_course, with_certificate, hourse_course, requred_year, required_spec, required_uni, level_course, tags_course, status_course, moderation_comment) FROM stdin;
33	Курс по основам C++	Учимся программировать на языке С++	f	12	\N	\N	\N	\N	C++	approved	\N
34	Основы Python	Курс по самому популярному языку программирования в мире	f	12	\N	\N	\N	\N	Python	approved	\N
36	Курс по веб разработке на PHP	Уникальный курс, который научит вас всему	f	12	\N	\N	\N	\N	Программирование, WEB, PHP	approved	
35	Учимся программировать на PHP	Курс для тех, кто хочет открыть мир программирования с PHP	f	12	\N	\N	\N	\N	Python, Программированиен	approved	\N
38	Высоконагруженные приложения на С++	Курс на С++	f	13	\N	\N	\N	\N	C++, highload	approved	\N
41	Основы PHP	Курс для тех, кто хочет открыть мир программирования с PHP	t	12	\N	\N	\N	\N	PHP, Программирование,	pending	\N
37	PHP в WEB разработке		t	12	\N	\N	\N	\N	PHP,	approved	
32	ML на C++	Хотите научиться создавать динамические веб-сайты и веб-приложения? PHP — один из самых популярных языков для backend-разработки, на котором работают WordPress, Facebook (ранние версии), Wikipedia и многие другие проекты.\r\n\r\nЭтот курс предназначен для тех, кто только начинает свой путь в программировании. Вы освоите базовые конструкции PHP, научитесь работать с базами данных, формами, сессиями и создадите свои первые веб-приложения.	t	12	\N	\N	\N	\N	PHP, Программирование	approved	
44	PHP для начинающих	Курс по PHP	t	3	\N	\N	\N	\N	PHP, Программирование,	approved	
\.


--
-- Data for Name: course_statistics; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course_statistics (id_course, views_count, enrollment_count, completion_count, average_rating, last_updated) FROM stdin;
32	32	0	0	0	2025-06-18 15:07:58.228107
33	1	0	0	0	2025-06-15 16:14:45.833515
38	9	0	0	0	2025-06-18 15:09:39.084303
44	29	0	0	0	2025-06-18 19:32:56.692158
36	32	0	0	0	2025-06-18 15:11:15.420424
35	25	0	0	0	2025-06-18 16:02:07.824128
41	1	0	0	0	2025-06-18 17:42:19.881435
37	39	0	0	0	2025-06-18 17:48:17.584551
34	7	0	0	0	2025-06-18 15:03:56.699493
\.


--
-- Data for Name: course_tags; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course_tags (id_course, id_tag) FROM stdin;
32	31
32	32
35	35
35	36
34	31
33	37
36	32
37	31
38	37
38	38
41	31
41	32
44	31
44	32
\.


--
-- Data for Name: course_views; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.course_views (id_view, id_course, id_user, view_timestamp) FROM stdin;
45	34	38	2025-06-15 15:49:39.907703
46	35	38	2025-06-15 16:01:06.93352
47	35	38	2025-06-15 16:01:26.261186
48	35	38	2025-06-15 16:05:44.980285
49	34	38	2025-06-15 16:14:30.632417
50	33	38	2025-06-15 16:14:45.830157
51	32	38	2025-06-15 16:15:35.205892
52	32	40	2025-06-15 16:16:00.821244
53	32	40	2025-06-15 16:16:01.375755
54	32	40	2025-06-15 16:16:02.273642
55	32	38	2025-06-15 16:16:24.113083
56	32	40	2025-06-15 16:16:54.858005
57	32	40	2025-06-15 16:16:57.439516
58	32	40	2025-06-15 16:17:05.262756
59	32	40	2025-06-15 16:35:36.265539
60	32	40	2025-06-15 16:36:21.075229
61	36	40	2025-06-15 16:36:26.019024
62	36	40	2025-06-15 16:36:27.562036
63	36	40	2025-06-15 16:36:28.456356
64	36	38	2025-06-15 16:37:18.004988
65	36	40	2025-06-15 16:37:48.232242
66	36	40	2025-06-15 16:37:53.972093
67	36	41	2025-06-15 16:38:19.185934
68	36	41	2025-06-15 16:38:19.805747
69	36	41	2025-06-15 16:38:20.549889
70	36	41	2025-06-15 16:39:49.286471
71	36	41	2025-06-15 16:39:51.755818
72	36	41	2025-06-15 16:41:37.185691
73	37	41	2025-06-15 16:41:40.873501
74	37	41	2025-06-15 16:41:41.746671
75	37	41	2025-06-15 16:41:42.441865
76	37	41	2025-06-15 16:41:45.149495
77	36	41	2025-06-15 16:41:48.082289
78	37	41	2025-06-15 16:41:55.222936
79	37	41	2025-06-15 16:42:22.634698
80	36	38	2025-06-15 16:43:15.82111
81	36	38	2025-06-15 16:43:18.624084
82	36	38	2025-06-15 16:43:23.29859
83	37	41	2025-06-15 16:43:49.055547
84	32	41	2025-06-15 16:44:12.806124
85	32	41	2025-06-15 16:44:13.398282
86	32	41	2025-06-15 16:44:14.049917
87	32	41	2025-06-15 16:44:16.9143
88	32	41	2025-06-15 16:44:24.336603
89	32	41	2025-06-15 16:44:24.884434
90	32	41	2025-06-15 16:44:25.434012
91	37	41	2025-06-15 16:45:15.726787
92	37	41	2025-06-15 16:46:24.948216
93	37	41	2025-06-15 16:46:25.637016
94	37	41	2025-06-15 16:48:16.590528
95	37	41	2025-06-15 16:48:17.140131
96	37	41	2025-06-15 16:48:17.628762
97	37	41	2025-06-15 16:48:18.01614
98	37	41	2025-06-15 16:48:18.490123
99	37	41	2025-06-15 16:48:19.164137
100	37	41	2025-06-15 16:49:08.815337
101	37	41	2025-06-15 16:49:13.136623
102	37	41	2025-06-15 16:49:15.911276
103	37	41	2025-06-15 16:49:16.437753
104	36	41	2025-06-15 16:49:28.062478
105	37	41	2025-06-15 16:49:29.755336
106	32	41	2025-06-15 16:49:31.159437
107	37	38	2025-06-15 16:50:21.317726
108	36	41	2025-06-15 16:50:54.871454
109	37	41	2025-06-15 16:51:02.544026
110	36	36	2025-06-18 14:39:29.992036
111	36	36	2025-06-18 14:39:33.349382
112	36	36	2025-06-18 14:39:35.33489
113	37	36	2025-06-18 14:45:24.242136
114	37	37	2025-06-18 14:47:28.292393
115	36	37	2025-06-18 14:47:31.196984
116	36	42	2025-06-18 14:47:55.309392
117	37	42	2025-06-18 14:52:05.410744
118	32	42	2025-06-18 14:52:07.549197
119	32	42	2025-06-18 14:52:15.99368
120	32	42	2025-06-18 14:52:16.72176
121	32	42	2025-06-18 14:52:19.297386
122	32	42	2025-06-18 14:52:25.067094
123	36	42	2025-06-18 14:53:07.035045
124	36	42	2025-06-18 14:53:22.32937
125	36	42	2025-06-18 14:53:23.370907
126	32	42	2025-06-18 14:53:27.550364
127	36	43	2025-06-18 14:53:54.399926
128	32	43	2025-06-18 14:53:56.695877
129	36	43	2025-06-18 14:55:08.765688
130	32	43	2025-06-18 14:55:10.81908
131	32	43	2025-06-18 14:55:29.065032
132	32	43	2025-06-18 14:55:29.915594
133	36	43	2025-06-18 14:56:01.968265
134	37	43	2025-06-18 14:56:05.027956
135	37	43	2025-06-18 14:56:06.28553
136	37	43	2025-06-18 14:56:07.028838
137	34	43	2025-06-18 14:56:23.102438
138	34	43	2025-06-18 14:56:23.82342
139	34	43	2025-06-18 14:56:24.440096
140	34	43	2025-06-18 14:56:26.199433
141	35	43	2025-06-18 14:57:11.743736
142	35	43	2025-06-18 14:57:18.229918
143	35	43	2025-06-18 14:57:19.480004
144	35	43	2025-06-18 14:57:20.230869
145	35	43	2025-06-18 14:57:33.015554
146	35	43	2025-06-18 14:58:10.839011
147	35	43	2025-06-18 14:59:09.389658
148	35	43	2025-06-18 14:59:13.645551
149	35	43	2025-06-18 14:59:20.314407
150	35	36	2025-06-18 14:59:31.563576
151	35	36	2025-06-18 14:59:33.502602
152	35	36	2025-06-18 15:03:28.303265
153	35	43	2025-06-18 15:03:48.520353
154	35	43	2025-06-18 15:03:52.422402
155	34	43	2025-06-18 15:03:56.660453
156	32	43	2025-06-18 15:03:59.515031
157	32	43	2025-06-18 15:04:02.05238
158	38	36	2025-06-18 15:04:05.917025
159	38	36	2025-06-18 15:04:21.096036
160	38	36	2025-06-18 15:04:22.953557
161	32	36	2025-06-18 15:05:05.144047
162	36	36	2025-06-18 15:05:07.442345
163	32	36	2025-06-18 15:07:58.223678
164	38	36	2025-06-18 15:08:35.693465
165	37	38	2025-06-18 15:08:52.918
166	38	43	2025-06-18 15:09:22.044769
167	38	43	2025-06-18 15:09:22.840627
168	38	43	2025-06-18 15:09:23.474153
169	38	43	2025-06-18 15:09:25.08164
170	38	43	2025-06-18 15:09:39.081153
171	37	43	2025-06-18 15:09:41.103257
172	37	43	2025-06-18 15:09:47.913909
173	35	43	2025-06-18 15:09:49.275676
174	37	38	2025-06-18 15:09:59.620442
175	37	43	2025-06-18 15:10:31.188341
176	36	38	2025-06-18 15:11:11.684014
177	36	38	2025-06-18 15:11:15.381595
178	35	38	2025-06-18 15:11:19.141547
179	35	43	2025-06-18 15:18:51.859465
180	35	41	2025-06-18 15:24:23.281014
181	35	41	2025-06-18 15:24:24.628617
182	35	41	2025-06-18 15:24:25.316236
185	35	41	2025-06-18 15:28:01.761691
186	37	38	2025-06-18 16:01:30.431554
187	35	38	2025-06-18 16:02:07.821026
188	41	38	2025-06-18 17:42:19.87739
189	37	45	2025-06-18 17:47:03.482447
190	37	45	2025-06-18 17:48:07.62806
191	37	45	2025-06-18 17:48:08.251767
192	37	45	2025-06-18 17:48:17.580796
200	44	50	2025-06-18 18:37:00.89158
201	44	50	2025-06-18 18:37:16.247956
202	44	51	2025-06-18 18:40:45.512685
203	44	51	2025-06-18 18:40:47.188832
204	44	51	2025-06-18 18:40:48.47016
205	44	51	2025-06-18 18:46:49.676874
206	44	51	2025-06-18 18:48:32.567552
207	44	51	2025-06-18 18:48:58.501341
208	44	51	2025-06-18 18:51:22.664715
209	44	51	2025-06-18 18:51:23.553086
210	44	50	2025-06-18 18:53:03.284672
211	44	51	2025-06-18 18:54:18.483363
212	44	52	2025-06-18 18:54:58.564237
213	44	52	2025-06-18 18:54:59.378646
214	44	52	2025-06-18 18:54:59.950732
215	44	52	2025-06-18 18:57:30.776147
216	44	52	2025-06-18 18:59:53.663518
217	44	41	2025-06-18 19:03:46.321044
218	44	41	2025-06-18 19:03:47.208539
219	44	41	2025-06-18 19:03:47.845085
220	44	43	2025-06-18 19:06:22.993441
221	44	43	2025-06-18 19:06:23.51898
222	44	43	2025-06-18 19:06:24.104553
223	44	51	2025-06-18 19:10:01.07209
224	44	51	2025-06-18 19:10:38.279875
225	44	51	2025-06-18 19:10:46.553446
226	44	51	2025-06-18 19:10:55.833566
227	44	51	2025-06-18 19:11:02.055938
228	44	51	2025-06-18 19:32:56.688534
\.


--
-- Data for Name: create_passes; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.create_passes (id_course, id_user, is_creator, date_complete) FROM stdin;
32	38	t	\N
33	38	t	\N
34	38	t	\N
35	38	t	\N
32	40	f	2025-06-15 16:16:57.452928
36	38	t	\N
36	40	f	2025-06-15 16:37:48.282181
36	41	f	2025-06-15 16:39:49.30061
37	38	t	\N
37	41	f	2025-06-15 16:41:45.170758
32	41	f	2025-06-15 16:44:16.927286
32	42	f	2025-06-18 14:52:19.311245
37	43	f	\N
34	43	f	\N
35	43	f	\N
38	36	t	\N
32	43	f	2025-06-18 15:04:02.066856
38	43	f	\N
35	41	f	\N
41	38	t	\N
44	50	t	\N
44	51	f	2025-06-18 18:48:32.584502
44	52	f	\N
44	41	f	\N
44	43	f	\N
\.


--
-- Data for Name: feedback; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.feedback (id_feedback, id_course, text_feedback, date_feedback, rate_feedback, id_user, status) FROM stdin;
17	32	Отличный курс	2025-06-18	5	42	approved
16	32	Отличный курс, все понравилось!	2025-06-15	5	40	pending
\.


--
-- Data for Name: lessons; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.lessons (id_lesson, id_course, id_stat, name_lesson, status_lesson) FROM stdin;
38	34	\N	AS	new
41	35	\N	Урок 1. Основы	new
42	35	\N	Урок 2. Циклы	new
43	32	\N	as	new
44	36	\N	Урок 1. Основы	new
45	37	\N	Переменные	new
46	38	\N	Урок 1. Основы	new
47	41	\N	Урок 1. Основы	new
49	44	\N	Урок 1. Основы	new
\.


--
-- Data for Name: material; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.material (id_material, id_step, path_matial, link_material) FROM stdin;
684eef7a1c874                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	82	materials/quixotesoulasas@gmail.com/Урок 1. Основы/Переменные_82.pdf	\N
684eefa4011a5                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	83	materials/quixotesoulasas@gmail.com/Урок 1. Основы/Ввод-вывод данных_83.pdf	\N
684eeff16dd2e                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	85	materials/quixotesoulasas@gmail.com/Урок 2. Циклы/Условные операторы_85.pdf	\N
684ef0090534e                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	86	\N	https://ya.ru/
684ef1e3d52e5                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	88	\N	https://ya.ru/
684ef57a69262                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	89	materials/quixotesoulasas@gmail.com/Урок 1. Основы/Тестовый шаг_89.pdf	\N
684ef78bc5bd7                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	91	\N	https://ya.ru/
6852db112157c                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	93	materials/quixotesoulasas@gmail.com/Урок 1. Основы/Переменные_93.pdf	\N
6852db2b5941a                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	94	materials/quixotesoulasas@gmail.com/Урок 1. Основы/Ввод-вывод данных_94.pdf	\N
6852fa8c69883                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	96	materials/quixotesoulasas@gmail.com/Урок 1. Основы/Синтаксис_96.pdf	\N
6853076eb5138                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	100	materials/maxrox1903@vk.com/Урок 1. Основы/Синтаксис PHP_100.pdf	\N
6853077d03824                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           	101	\N	https://vkvideo.ru/video-16108331_456254498
\.


--
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.questions (id_question, id_test, text_question, answer_question, type_question, image_question) FROM stdin;
76	33	Что такое переменная?	0	single	\N
77	33	Выведите hello world		code	\N
78	34	фыфы	0	single	\N
79	31	Что такое переменная?	0	single	\N
80	31	Какие функции сортируют массив по значению?	0,1	multi	\N
81	31	Сопоставьте функции PHP с их назначением		match	\N
82	31	Суммируйте элементы массива		code	\N
83	35	Что такое переменная?	2	single	\N
84	35	Сопоставьте функции php с их назначением		match	\N
91	37	Что такое переменная?	0	single	\N
92	37	Какие два способа являются корректными для получения данных из POST-запроса в PHP?	0,1	multi	\N
93	37	Сопоставьте функции PHP с их назначением		match	\N
94	37	Напишите функцию, которая будет складывать элементы массива		code	\N
\.


--
-- Data for Name: results; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.results (id_result, id_answer, id_test, score_result) FROM stdin;
\.


--
-- Data for Name: stat; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.stat (id_stat, id_user, id_course, id_result, prec_through) FROM stdin;
\.


--
-- Data for Name: steps; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.steps (id_step, id_lesson, number_steps, status_step, type_step) FROM stdin;
82	41	Переменные	not_started	material
83	41	Ввод-вывод данных	not_started	material
84	41	Тест по Уроку 1	not_started	test
85	42	Условные операторы	not_started	material
86	42	Циклы for и while	not_started	material
87	42	Итоговый тест по Уроку 2	not_started	test
88	43	1	not_started	material
89	44	Тестовый шаг	not_started	material
90	44	Тест	not_started	test
91	45	шаг	not_started	material
92	45	фы	not_started	test
93	47	Переменные	not_started	material
94	47	Ввод-вывод данных	not_started	material
95	47	Тест по Уроку 1	not_started	test
96	47	Синтаксис	not_started	material
100	49	Синтаксис PHP	not_started	material
101	49	Массивы и функции PHP	not_started	material
102	49	Итоговый тест	not_started	test
\.


--
-- Data for Name: student_analytics; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.student_analytics (id_user, id_course, lessons_completed, total_lessons, tests_completed, total_tests, average_test_score, last_activity, estimated_completion_date) FROM stdin;
\.


--
-- Data for Name: student_test_settings; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.student_test_settings (id_user, id_test, additional_attempts) FROM stdin;
\.


--
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.tags (id_tag, name_tag) FROM stdin;
31	PHP
32	Программирование
33	фы
34	AS
35	Python
36	Программированиен
37	C++
38	highload
\.


--
-- Data for Name: test_answers; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.test_answers (id_answer, id_attempt, id_question, id_selected_option, is_correct, answer_time, answer_text, ai_feedback) FROM stdin;
316	132	76	1	t	2025-06-15 16:37:02.900612	\N	\N
317	133	76	1	t	2025-06-15 16:38:49.45986	\N	\N
318	133	77	\N	f	2025-06-15 16:38:49.46591	<?php\r\necho "poka"\r\n?>	НЕПРАВИЛЬНО: вывод программы — 'poka', а не 'hello world'.
319	134	78	5	t	2025-06-18 15:10:36.571474	\N	\N
352	143	91	48	t	2025-06-18 19:02:22.957718	\N	\N
353	143	92	52	f	2025-06-18 19:02:22.964619	["0"]	\N
354	143	93	\N	f	2025-06-18 19:02:22.967254	["0","2","1","1"]	\N
355	143	94	\N	f	2025-06-18 19:02:22.97599	<?php\r\nfunction sumArray(array $array): int {\r\nфывфывфыв\r\n}\r\n\r\n// Пример использования функции\r\n$numbers = [1, 2, 3, 4, 5];\r\necho sumArray($numbers); // ожидаем вывод: 15\r\n?>	НЕПРАВИЛЬНО: код содержит синтаксическую ошибку и не выполняет задачу сложения элементов массива.
356	144	91	\N	f	2025-06-18 19:03:31.88876	\N	\N
357	144	92	\N	f	2025-06-18 19:03:31.890802	null	\N
358	144	93	\N	f	2025-06-18 19:03:31.892321	null	\N
359	144	94	\N	f	2025-06-18 19:03:31.894356	\N	
360	145	91	48	t	2025-06-18 19:07:08.325118	\N	\N
361	145	92	55	f	2025-06-18 19:07:08.327553	["3","4"]	\N
362	145	93	\N	f	2025-06-18 19:07:08.329085	["1","2","3","1"]	\N
363	145	94	\N	f	2025-06-18 19:07:08.331208	<?php\r\nfunction sumArray(array $array): int {\r\n    return 0;\r\n}\r\n\r\n// Пример использования функции\r\n$numbers = [1, 2, 3, 4, 5];\r\necho "15"; // ожидаем вывод: 15\r\n?>	НЕПРАВИЛЬНО: функция всегда возвращает 0, не суммируя элементы массива.
364	146	91	48	t	2025-06-18 19:09:27.883942	\N	\N
365	146	92	53	t	2025-06-18 19:09:27.886307	["1","0"]	\N
366	146	93	\N	t	2025-06-18 19:09:27.88803	["0","1","2","3"]	\N
367	146	94	\N	t	2025-06-18 19:09:27.890224	<?php\r\nfunction sumArray(array $array): int {\r\n    $sum = 0;\r\n    foreach ($array as $number) {\r\n        $sum += $number;\r\n    }\r\n    return $sum;\r\n}\r\n\r\n// Пример использования функции\r\n$numbers = [1, 2, 3, 4, 5];\r\necho sumArray($numbers); // Выведет: 15\r\n?>	ПРАВИЛЬНО: функция корректно суммирует элементы массива.
368	147	91	48	t	2025-06-18 19:10:29.426292	\N	\N
369	147	92	53	t	2025-06-18 19:10:29.428783	["1","0"]	\N
370	147	93	\N	t	2025-06-18 19:10:29.430324	["0","1","2","3"]	\N
371	147	94	\N	t	2025-06-18 19:10:29.432407	<?php\r\nfunction sumArray(array $array): int {\r\n    $sum = 0;\r\n    foreach ($array as $number) {\r\n        $sum += $number;\r\n    }\r\n    return $sum;\r\n}\r\n\r\n// Пример использования функции\r\n$numbers = [1, 2, 3, 4, 5];\r\necho sumArray($numbers); // Выведет: 15\r\n?>	ПРАВИЛЬНО: функция корректно суммирует элементы массива.
\.


--
-- Data for Name: test_attempts; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.test_attempts (id_attempt, id_test, id_user, start_time, end_time, score, max_score, status) FROM stdin;
132	33	40	2025-06-15 16:37:02.878722	2025-06-15 16:37:02.878722	1	1	completed
133	33	41	2025-06-15 16:38:49.420051	2025-06-15 16:38:49.420051	1	2	completed
134	34	43	2025-06-18 15:10:36.56539	2025-06-18 15:10:36.56539	1	1	completed
143	37	52	2025-06-18 19:02:22.942817	2025-06-18 19:02:22.942817	1	4	completed
144	37	52	2025-06-18 19:03:31.884812	2025-06-18 19:03:31.884812	0	4	completed
145	37	43	2025-06-18 19:07:08.286599	2025-06-18 19:07:08.286599	1	4	completed
146	37	43	2025-06-18 19:09:27.845398	2025-06-18 19:09:27.845398	4	4	completed
147	37	51	2025-06-18 19:10:29.388932	2025-06-18 19:10:29.388932	4	4	completed
\.


--
-- Data for Name: test_grade_levels; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.test_grade_levels (id_level, id_test, min_percentage, max_percentage, grade_name, grade_color) FROM stdin;
9	37	0	59	Не пройдено	#ff0000
10	37	60	74	Удовлетворительно	#ffa500
11	37	75	89	Хорошо	#2ecc40
12	37	90	100	Отлично	#0e6eb8
\.


--
-- Data for Name: tests; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.tests (id_test, id_step, name_test, desc_test, passing_percentage, max_attempts, time_between_attempts, show_results_after_completion, practice_mode) FROM stdin;
31	84	Новый тест		70	3	0	t	f
32	87	Новый тест		70	3	0	t	f
33	90	Новый тест		70	3	0	t	f
34	92	Новый тест		70	3	0	t	f
35	95	Новый тест		70	3	0	t	f
37	102	Новый тест		50	2	0	t	f
\.


--
-- Data for Name: user_material_progress; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.user_material_progress (id_user, id_step, completed_at) FROM stdin;
40	88	2025-06-15 16:16:56.292951
40	89	2025-06-15 16:36:43.962818
41	89	2025-06-15 16:38:23.116809
41	91	2025-06-15 16:41:43.914308
41	88	2025-06-15 16:44:16.157036
42	88	2025-06-18 14:52:18.517491
43	82	2025-06-18 14:58:49.755499
43	83	2025-06-18 14:59:05.421475
43	88	2025-06-18 15:04:01.705854
43	91	2025-06-18 15:09:45.416019
51	100	2025-06-18 18:40:56.64704
51	101	2025-06-18 18:41:04.019537
52	100	2025-06-18 18:55:01.258083
52	101	2025-06-18 18:55:02.040806
41	100	2025-06-18 19:03:48.941537
41	101	2025-06-18 19:03:49.608914
43	100	2025-06-18 19:06:25.372286
43	101	2025-06-18 19:06:26.341174
\.


--
-- Data for Name: user_tag_interests; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.user_tag_interests (id_user, id_tag, interest_weight) FROM stdin;
41	32	2.5
41	31	2.5
41	36	1.5
41	35	1.5
43	36	1.5
43	32	2
43	31	3
43	35	1.5
43	37	1.5
40	32	2
40	31	1.5
42	32	1.5
42	31	1.5
43	38	1.5
50	32	1.5
38	32	2.5
38	36	1.5
38	31	3
38	35	1.5
38	37	1.5
50	31	1.5
52	32	1.5
52	31	1.5
36	37	1.5
36	38	1.5
51	32	1.5
51	31	1.5
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: pguser
--

COPY public.users (id_user, fn_user, birth_user, uni_user, role_user, spec_user, year_user, login_user, password_user, criminal_record_file, status, moderation_comment, student_card, passport_file, diploma_file) FROM stdin;
36	Болдырев Максим Романович	2003-03-19		admin		0	quixotesoul@gmail.com	$2y$12$RoV53nS2I8ANMofHTogzBOTaT9.Ddn8aSMg3wFin3DqS5E2jTreuW	\N	approved	\N	\N	\N	\N
37	Иванов Иван Иванович	1999-07-20		teacher		0	yaz678@bk.ru	$2y$12$puvDk6SX4Gdvl01Fn82w0OJKmXqeUNwNHpGdvQ/ykgXcQIvjdhusC	uploads/teachers/criminal/criminal_684ee4b0c4587_3_3.jpg	approved		\N	uploads/teachers/passport/passport_684ee4b0c457d_Приказ КС.pdf	uploads/teachers/diploma/diploma_684ee4b0c4586_zwOdxUdaFxY.jpg
39	Albert Robertson	2003-03-19		student		0	quixotesasasasoul@gmail.com	$2y$12$lVTJtoZuTSrp16itvp3gKeNt6z9T7HRzRVoytBZh1TpGuh24muZay	\N	approved	\N	\N	\N	\N
51	Иванов Иван Иванович	2003-03-19		student		0	test-test@mail.ru	$2y$12$geoEdtQOXCywIpJCqvoKTOIXRmn9fcKWVJIwfsqzADe93r0idsRva	\N	approved	\N	\N	\N	\N
40	Albert Robertson	2003-03-19		student		0	rock@maiaalala.ru	$2y$12$0AJCp0GrvceR0m1PsMgGHerEfLyBWBSqSZn2tlfBVN/GXB3gIyi5u	\N	approved	\N	\N	\N	\N
41	Max Boldyrev	2003-03-19		student		0	maxrox1904@gmail.com	$2y$12$2IZfa9wLSJCTX/m1wGH2pOKiB6AUrH6SGxoUmJmX9zu62h/thCmrG	\N	approved	\N	\N	\N	\N
42	Болдырев Максим Романович	2003-03-19		student		0	yaz678@bkbkbk.ru	$2y$12$6M734zLG2fhW8KzhRUOiBOHXrhwPZu53uT6wil.Nxd0OliUGcmD8C	\N	approved	\N	\N	\N	\N
43	Иванов Иван Ивановичя	2003-03-19		student		0	test@bkbkbkb.ru	$2y$12$j1qzM3phz5DDQC6VEgxkQ.wSkfRLiHu..I7dQuZknlp0yQaAlZ3q.	\N	approved	\N	\N	\N	\N
38	Преподавалов Преподаватель Преподавалович	2003-03-19		teacher		0	quixotesoulasas@gmail.com	$2y$12$nKzGhv/9I7DFs9DGOmQoTe0d4UyX86UvYpKqw8kbARVV2NM11eIH2	uploads/teachers/criminal/criminal_684ee5c14475a_1_1.jpg	approved		\N	uploads/teachers/passport/passport_684ee5c144752_3_3.jpg	uploads/teachers/diploma/diploma_684ee5c144759_2_2.jpg
44	Иванов Иван Ивановичя	2003-03-19		teacher		0	test-example-mail@mail.ru	$2y$12$vEVUyLmSwh7rkkPLET0FE.9Z0TZTCwqHotOK7xxczikJNEUQuWrny	uploads/teachers/criminal/criminal_6852e142184bc_пользователь.jpg	pending	\N	\N	uploads/teachers/passport/passport_6852e142184b4_физ тесты.jpg	uploads/teachers/diploma/diploma_6852e142184bb_вся.jpg
45	Dimi Junior	2003-03-19		student		0	sdgfdf@bklkkk.ru	$2y$12$Xo8ClC4rR255XkkAjoMtieJFExN7ORiTVbtF1OTp41nAJFBvfhTo6	\N	approved	\N	\N	\N	\N
52	Иванов Иван Иванович	2003-03-12		student		0	AKSMAKSM@BK.RU	$2y$12$rnpYy3CLtDmcbYsUrt1E1uiieoXTDWN5drS2HBG5yU.9HmPD6FqWK	\N	approved	\N	\N	\N	\N
50	Болдырев Максим Романович	2003-03-19		teacher		0	maxrox1903@vk.com	$2y$12$XJEsX8gZ8W2uprK9RnsGy.lBjy2iaKgrzBH3RAejWJf6GLiFvTdm.	uploads/teachers/criminal/criminal_68530715ce1cc_заглушка 3.jpg	approved		\N	uploads/teachers/passport/passport_68530715ce1c5_заглушка 1.jpg	uploads/teachers/diploma/diploma_68530715ce1cb_заглушка 2.jpg
\.


--
-- Name: answer_options_id_option_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answer_options_id_option_seq', 60, true);


--
-- Name: answers_id_answer_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answers_id_answer_seq', 1, false);


--
-- Name: certificates_id_certificate_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.certificates_id_certificate_seq', 1, true);


--
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.code_tasks_id_ct_seq', 17, true);


--
-- Name: course_id_course_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.course_id_course_seq', 44, true);


--
-- Name: course_views_id_view_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.course_views_id_view_seq', 228, true);


--
-- Name: feedback_id_feedback_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.feedback_id_feedback_seq', 18, true);


--
-- Name: lessons_id_lesson_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.lessons_id_lesson_seq', 49, true);


--
-- Name: questions_id_question_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.questions_id_question_seq', 94, true);


--
-- Name: results_id_result_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.results_id_result_seq', 31, true);


--
-- Name: stat_id_stat_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.stat_id_stat_seq', 1, false);


--
-- Name: steps_id_step_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.steps_id_step_seq', 102, true);


--
-- Name: tags_id_tag_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.tags_id_tag_seq', 38, true);


--
-- Name: test_answers_id_answer_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_answers_id_answer_seq', 371, true);


--
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_attempts_id_attempt_seq', 147, true);


--
-- Name: test_grade_levels_id_level_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_grade_levels_id_level_seq', 12, true);


--
-- Name: tests_id_test_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.tests_id_test_seq', 37, true);


--
-- Name: users_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.users_id_user_seq', 52, true);


--
-- Name: certificates certificates_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_pkey PRIMARY KEY (id_certificate);


--
-- Name: course_statistics course_statistics_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_statistics
    ADD CONSTRAINT course_statistics_pkey PRIMARY KEY (id_course);


--
-- Name: course_tags course_tags_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_pkey PRIMARY KEY (id_course, id_tag);


--
-- Name: course_views course_views_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_pkey PRIMARY KEY (id_view);


--
-- Name: answer_options pk_answer_options; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT pk_answer_options PRIMARY KEY (id_option);


--
-- Name: answers pk_answers; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT pk_answers PRIMARY KEY (id_answer);


--
-- Name: code_tasks pk_code_tasks; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT pk_code_tasks PRIMARY KEY (id_ct);


--
-- Name: course pk_course; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course
    ADD CONSTRAINT pk_course PRIMARY KEY (id_course);


--
-- Name: create_passes pk_create_passes; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT pk_create_passes PRIMARY KEY (id_course, id_user);


--
-- Name: feedback pk_feedback; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT pk_feedback PRIMARY KEY (id_feedback);


--
-- Name: lessons pk_lessons; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT pk_lessons PRIMARY KEY (id_lesson);


--
-- Name: material pk_material; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT pk_material PRIMARY KEY (id_material);


--
-- Name: questions pk_questions; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT pk_questions PRIMARY KEY (id_question);


--
-- Name: results pk_results; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT pk_results PRIMARY KEY (id_result);


--
-- Name: stat pk_stat; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT pk_stat PRIMARY KEY (id_stat);


--
-- Name: steps pk_steps; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT pk_steps PRIMARY KEY (id_step);


--
-- Name: tests pk_tests; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT pk_tests PRIMARY KEY (id_test);


--
-- Name: users pk_users; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT pk_users PRIMARY KEY (id_user);


--
-- Name: student_analytics student_analytics_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_pkey PRIMARY KEY (id_user, id_course);


--
-- Name: student_test_settings student_test_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_pkey PRIMARY KEY (id_user, id_test);


--
-- Name: tags tags_name_tag_key; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_name_tag_key UNIQUE (name_tag);


--
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id_tag);


--
-- Name: test_answers test_answers_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_pkey PRIMARY KEY (id_answer);


--
-- Name: test_attempts test_attempts_id_test_id_user_start_time_key; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_test_id_user_start_time_key UNIQUE (id_test, id_user, start_time);


--
-- Name: test_attempts test_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_pkey PRIMARY KEY (id_attempt);


--
-- Name: test_grade_levels test_grade_levels_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels
    ADD CONSTRAINT test_grade_levels_pkey PRIMARY KEY (id_level);


--
-- Name: user_material_progress user_material_progress_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_pkey PRIMARY KEY (id_user, id_step);


--
-- Name: user_tag_interests user_tag_interests_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_pkey PRIMARY KEY (id_user, id_tag);


--
-- Name: also_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX also_include_fk ON public.steps USING btree (id_lesson);


--
-- Name: answer_options_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answer_options_pk ON public.answer_options USING btree (id_option);


--
-- Name: answers_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answers_pk ON public.answers USING btree (id_answer);


--
-- Name: asnwers_to_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX asnwers_to_fk ON public.answers USING btree (id_user);


--
-- Name: assume_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX assume_fk ON public.answers USING btree (id_question);


--
-- Name: code_tasks_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX code_tasks_pk ON public.code_tasks USING btree (id_ct);


--
-- Name: counts_from_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX counts_from_fk ON public.stat USING btree (id_result);


--
-- Name: course_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX course_pk ON public.course USING btree (id_course);


--
-- Name: create_passes2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes2_fk ON public.create_passes USING btree (id_user);


--
-- Name: create_passes_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes_fk ON public.create_passes USING btree (id_course);


--
-- Name: create_passes_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX create_passes_pk ON public.create_passes USING btree (id_course, id_user);


--
-- Name: feedback_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX feedback_pk ON public.feedback USING btree (id_feedback);


--
-- Name: goes_into_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX goes_into_fk ON public.stat USING btree (id_course);


--
-- Name: goes_to_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX goes_to_fk ON public.results USING btree (id_answer);


--
-- Name: has_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_fk ON public.feedback USING btree (id_course);


--
-- Name: has_in_courses_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_in_courses_fk ON public.stat USING btree (id_user);


--
-- Name: idx_code_tasks_language; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_code_tasks_language ON public.code_tasks USING btree (language);


--
-- Name: idx_create_passes_date_complete; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_create_passes_date_complete ON public.create_passes USING btree (date_complete);


--
-- Name: idx_test_answers_attempt; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_answers_attempt ON public.test_answers USING btree (id_attempt);


--
-- Name: idx_test_attempts_complete_time; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_attempts_complete_time ON public.test_attempts USING btree (id_test, id_user, end_time);


--
-- Name: idx_test_grade_levels; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_grade_levels ON public.test_grade_levels USING btree (id_test);


--
-- Name: include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX include_fk ON public.lessons USING btree (id_course);


--
-- Name: lessons_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX lessons_pk ON public.lessons USING btree (id_lesson);


--
-- Name: material_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX material_pk ON public.material USING btree (id_material);


--
-- Name: may_also_include2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_also_include2_fk ON public.tests USING btree (id_step);


--
-- Name: may_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_include_fk ON public.material USING btree (id_step);


--
-- Name: mean_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX mean_fk ON public.questions USING btree (id_test);


--
-- Name: might_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX might_include_fk ON public.code_tasks USING btree (id_question);


--
-- Name: must_have_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX must_have_fk ON public.answer_options USING btree (id_question);


--
-- Name: procent_pass_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX procent_pass_fk ON public.lessons USING btree (id_stat);


--
-- Name: questions_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX questions_pk ON public.questions USING btree (id_question);


--
-- Name: results_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX results_pk ON public.results USING btree (id_result);


--
-- Name: stat_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX stat_pk ON public.stat USING btree (id_stat);


--
-- Name: stats_in_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX stats_in_fk ON public.results USING btree (id_test);


--
-- Name: steps_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX steps_pk ON public.steps USING btree (id_step);


--
-- Name: tests_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX tests_pk ON public.tests USING btree (id_test);


--
-- Name: users_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX users_pk ON public.users USING btree (id_user);


--
-- Name: certificates certificates_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: certificates certificates_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: course_statistics course_statistics_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_statistics
    ADD CONSTRAINT course_statistics_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- Name: course_tags course_tags_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- Name: course_tags course_tags_id_tag_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_id_tag_fkey FOREIGN KEY (id_tag) REFERENCES public.tags(id_tag) ON DELETE CASCADE;


--
-- Name: course_views course_views_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- Name: course_views course_views_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- Name: answer_options fk_answer_o_must_have_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT fk_answer_o_must_have_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: answers fk_answers_asnwers_t_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT fk_answers_asnwers_t_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: answers fk_answers_assume_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT fk_answers_assume_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: code_tasks fk_code_tas_might_inc_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT fk_code_tas_might_inc_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: create_passes fk_create_p_create_pa_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: create_passes fk_create_p_create_pa_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: feedback fk_feedback_has_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_has_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: feedback fk_feedback_user; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_user FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- Name: lessons fk_lessons_include_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_include_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: lessons fk_lessons_procent_p_stat; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_procent_p_stat FOREIGN KEY (id_stat) REFERENCES public.stat(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: material fk_material_may_inclu_steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT fk_material_may_inclu_steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: questions fk_question_mean_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT fk_question_mean_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: results fk_results_goes_to_answers; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT fk_results_goes_to_answers FOREIGN KEY (id_answer) REFERENCES public.answers(id_answer) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: results fk_results_stats_in_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT fk_results_stats_in_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: stat fk_stat_counts_fr_results; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_counts_fr_results FOREIGN KEY (id_result) REFERENCES public.results(id_result) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: stat fk_stat_goes_into_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_goes_into_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: stat fk_stat_has_in_co_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_has_in_co_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: steps fk_steps_also_incl_lessons; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT fk_steps_also_incl_lessons FOREIGN KEY (id_lesson) REFERENCES public.lessons(id_lesson) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: tests fk_tests_may_also__steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT fk_tests_may_also__steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: results results_answer_fk; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT results_answer_fk FOREIGN KEY (id_answer) REFERENCES public.answers(id_answer) ON DELETE CASCADE;


--
-- Name: student_analytics student_analytics_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- Name: student_analytics student_analytics_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- Name: student_test_settings student_test_settings_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON DELETE CASCADE;


--
-- Name: student_test_settings student_test_settings_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- Name: test_answers test_answers_id_attempt_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_attempt_fkey FOREIGN KEY (id_attempt) REFERENCES public.test_attempts(id_attempt);


--
-- Name: test_answers test_answers_id_question_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_question_fkey FOREIGN KEY (id_question) REFERENCES public.questions(id_question);


--
-- Name: test_answers test_answers_id_selected_option_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_selected_option_fkey FOREIGN KEY (id_selected_option) REFERENCES public.answer_options(id_option);


--
-- Name: test_attempts test_attempts_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test);


--
-- Name: test_attempts test_attempts_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- Name: test_grade_levels test_grade_levels_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels
    ADD CONSTRAINT test_grade_levels_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON DELETE CASCADE;


--
-- Name: user_material_progress user_material_progress_id_step_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_id_step_fkey FOREIGN KEY (id_step) REFERENCES public.steps(id_step);


--
-- Name: user_material_progress user_material_progress_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- Name: user_tag_interests user_tag_interests_id_tag_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_id_tag_fkey FOREIGN KEY (id_tag) REFERENCES public.tags(id_tag) ON DELETE CASCADE;


--
-- Name: user_tag_interests user_tag_interests_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pguser
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


--
-- PostgreSQL database dump complete
--

