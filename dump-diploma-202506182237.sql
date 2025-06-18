--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.0

-- Started on 2025-06-18 22:37:02

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
-- TOC entry 5 (class 2615 OID 57636)
-- Name: public; Type: SCHEMA; Schema: -; Owner: pguser
--

-- *not* creating schema, since initdb creates it


ALTER SCHEMA public OWNER TO pguser;

--
-- TOC entry 3754 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: pguser
--

COMMENT ON SCHEMA public IS '';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 217 (class 1259 OID 57637)
-- Name: answer_options; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.answer_options (
    id_option integer NOT NULL,
    id_question integer,
    text_option character varying(255) NOT NULL
);


ALTER TABLE public.answer_options OWNER TO pguser;

--
-- TOC entry 218 (class 1259 OID 57640)
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
-- TOC entry 3756 (class 0 OID 0)
-- Dependencies: 218
-- Name: answer_options_id_option_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.answer_options_id_option_seq OWNED BY public.answer_options.id_option;


--
-- TOC entry 219 (class 1259 OID 57641)
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
-- TOC entry 220 (class 1259 OID 57644)
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
-- TOC entry 3757 (class 0 OID 0)
-- Dependencies: 220
-- Name: answers_id_answer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.answers_id_answer_seq OWNED BY public.answers.id_answer;


--
-- TOC entry 221 (class 1259 OID 57645)
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
-- TOC entry 222 (class 1259 OID 57649)
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
-- TOC entry 3758 (class 0 OID 0)
-- Dependencies: 222
-- Name: certificates_id_certificate_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.certificates_id_certificate_seq OWNED BY public.certificates.id_certificate;


--
-- TOC entry 223 (class 1259 OID 57650)
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
-- TOC entry 3759 (class 0 OID 0)
-- Dependencies: 223
-- Name: TABLE code_tasks; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON TABLE public.code_tasks IS 'Stores code tasks for programming questions with input template and expected output';


--
-- TOC entry 3760 (class 0 OID 0)
-- Dependencies: 223
-- Name: COLUMN code_tasks.input_ct; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.input_ct IS 'Input data or description for the code task';


--
-- TOC entry 3761 (class 0 OID 0)
-- Dependencies: 223
-- Name: COLUMN code_tasks.output_ct; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.output_ct IS 'Expected output that the code should produce';


--
-- TOC entry 3762 (class 0 OID 0)
-- Dependencies: 223
-- Name: COLUMN code_tasks.execution_timeout; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.execution_timeout IS 'Maximum execution time in seconds';


--
-- TOC entry 3763 (class 0 OID 0)
-- Dependencies: 223
-- Name: COLUMN code_tasks.template_code; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.template_code IS 'Starting template code provided to the student';


--
-- TOC entry 3764 (class 0 OID 0)
-- Dependencies: 223
-- Name: COLUMN code_tasks.language; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.code_tasks.language IS 'Programming language for the task (php, python, cpp)';


--
-- TOC entry 224 (class 1259 OID 57658)
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
-- TOC entry 3765 (class 0 OID 0)
-- Dependencies: 224
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.code_tasks_id_ct_seq OWNED BY public.code_tasks.id_ct;


--
-- TOC entry 225 (class 1259 OID 57659)
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
-- TOC entry 226 (class 1259 OID 57665)
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
-- TOC entry 3766 (class 0 OID 0)
-- Dependencies: 226
-- Name: course_id_course_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.course_id_course_seq OWNED BY public.course.id_course;


--
-- TOC entry 227 (class 1259 OID 57666)
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
-- TOC entry 228 (class 1259 OID 57674)
-- Name: course_tags; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.course_tags (
    id_course integer NOT NULL,
    id_tag integer NOT NULL
);


ALTER TABLE public.course_tags OWNER TO pguser;

--
-- TOC entry 229 (class 1259 OID 57677)
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
-- TOC entry 230 (class 1259 OID 57681)
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
-- TOC entry 3767 (class 0 OID 0)
-- Dependencies: 230
-- Name: course_views_id_view_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.course_views_id_view_seq OWNED BY public.course_views.id_view;


--
-- TOC entry 231 (class 1259 OID 57682)
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
-- TOC entry 232 (class 1259 OID 57686)
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
-- TOC entry 233 (class 1259 OID 57691)
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
-- TOC entry 3768 (class 0 OID 0)
-- Dependencies: 233
-- Name: feedback_id_feedback_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.feedback_id_feedback_seq OWNED BY public.feedback.id_feedback;


--
-- TOC entry 234 (class 1259 OID 57692)
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
-- TOC entry 235 (class 1259 OID 57697)
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
-- TOC entry 3769 (class 0 OID 0)
-- Dependencies: 235
-- Name: lessons_id_lesson_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.lessons_id_lesson_seq OWNED BY public.lessons.id_lesson;


--
-- TOC entry 236 (class 1259 OID 57698)
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
-- TOC entry 237 (class 1259 OID 57703)
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
-- TOC entry 238 (class 1259 OID 57708)
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
-- TOC entry 3770 (class 0 OID 0)
-- Dependencies: 238
-- Name: questions_id_question_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.questions_id_question_seq OWNED BY public.questions.id_question;


--
-- TOC entry 239 (class 1259 OID 57709)
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
-- TOC entry 240 (class 1259 OID 57712)
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
-- TOC entry 3771 (class 0 OID 0)
-- Dependencies: 240
-- Name: results_id_result_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.results_id_result_seq OWNED BY public.results.id_result;


--
-- TOC entry 241 (class 1259 OID 57713)
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
-- TOC entry 242 (class 1259 OID 57716)
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
-- TOC entry 3772 (class 0 OID 0)
-- Dependencies: 242
-- Name: stat_id_stat_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.stat_id_stat_seq OWNED BY public.stat.id_stat;


--
-- TOC entry 243 (class 1259 OID 57717)
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
-- TOC entry 244 (class 1259 OID 57724)
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
-- TOC entry 3773 (class 0 OID 0)
-- Dependencies: 244
-- Name: steps_id_step_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.steps_id_step_seq OWNED BY public.steps.id_step;


--
-- TOC entry 245 (class 1259 OID 57725)
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
-- TOC entry 246 (class 1259 OID 57733)
-- Name: student_test_settings; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.student_test_settings (
    id_user integer NOT NULL,
    id_test integer NOT NULL,
    additional_attempts integer DEFAULT 0
);


ALTER TABLE public.student_test_settings OWNER TO pguser;

--
-- TOC entry 247 (class 1259 OID 57737)
-- Name: tags; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.tags (
    id_tag integer NOT NULL,
    name_tag character varying(100) NOT NULL
);


ALTER TABLE public.tags OWNER TO pguser;

--
-- TOC entry 248 (class 1259 OID 57740)
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
-- TOC entry 3774 (class 0 OID 0)
-- Dependencies: 248
-- Name: tags_id_tag_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.tags_id_tag_seq OWNED BY public.tags.id_tag;


--
-- TOC entry 249 (class 1259 OID 57741)
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
-- TOC entry 3775 (class 0 OID 0)
-- Dependencies: 249
-- Name: COLUMN test_answers.ai_feedback; Type: COMMENT; Schema: public; Owner: pguser
--

COMMENT ON COLUMN public.test_answers.ai_feedback IS 'Отзыв ИИ о коде студента';


--
-- TOC entry 250 (class 1259 OID 57747)
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
-- TOC entry 3776 (class 0 OID 0)
-- Dependencies: 250
-- Name: test_answers_id_answer_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_answers_id_answer_seq OWNED BY public.test_answers.id_answer;


--
-- TOC entry 251 (class 1259 OID 57748)
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
-- TOC entry 252 (class 1259 OID 57753)
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
-- TOC entry 3777 (class 0 OID 0)
-- Dependencies: 252
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_attempts_id_attempt_seq OWNED BY public.test_attempts.id_attempt;


--
-- TOC entry 253 (class 1259 OID 57754)
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
-- TOC entry 254 (class 1259 OID 57761)
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
-- TOC entry 3778 (class 0 OID 0)
-- Dependencies: 254
-- Name: test_grade_levels_id_level_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.test_grade_levels_id_level_seq OWNED BY public.test_grade_levels.id_level;


--
-- TOC entry 255 (class 1259 OID 57762)
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
-- TOC entry 256 (class 1259 OID 57774)
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
-- TOC entry 3779 (class 0 OID 0)
-- Dependencies: 256
-- Name: tests_id_test_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.tests_id_test_seq OWNED BY public.tests.id_test;


--
-- TOC entry 257 (class 1259 OID 57775)
-- Name: user_material_progress; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.user_material_progress (
    id_user integer NOT NULL,
    id_step integer NOT NULL,
    completed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_material_progress OWNER TO pguser;

--
-- TOC entry 258 (class 1259 OID 57779)
-- Name: user_tag_interests; Type: TABLE; Schema: public; Owner: pguser
--

CREATE TABLE public.user_tag_interests (
    id_user integer NOT NULL,
    id_tag integer NOT NULL,
    interest_weight double precision DEFAULT 1.0
);


ALTER TABLE public.user_tag_interests OWNER TO pguser;

--
-- TOC entry 259 (class 1259 OID 57783)
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
-- TOC entry 260 (class 1259 OID 57789)
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
-- TOC entry 3780 (class 0 OID 0)
-- Dependencies: 260
-- Name: users_id_user_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: pguser
--

ALTER SEQUENCE public.users_id_user_seq OWNED BY public.users.id_user;


--
-- TOC entry 3370 (class 2604 OID 58125)
-- Name: answer_options id_option; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options ALTER COLUMN id_option SET DEFAULT nextval('public.answer_options_id_option_seq'::regclass);


--
-- TOC entry 3371 (class 2604 OID 58108)
-- Name: answers id_answer; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers ALTER COLUMN id_answer SET DEFAULT nextval('public.answers_id_answer_seq'::regclass);


--
-- TOC entry 3372 (class 2604 OID 58126)
-- Name: certificates id_certificate; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates ALTER COLUMN id_certificate SET DEFAULT nextval('public.certificates_id_certificate_seq'::regclass);


--
-- TOC entry 3374 (class 2604 OID 58127)
-- Name: code_tasks id_ct; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks ALTER COLUMN id_ct SET DEFAULT nextval('public.code_tasks_id_ct_seq'::regclass);


--
-- TOC entry 3377 (class 2604 OID 58128)
-- Name: course id_course; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course ALTER COLUMN id_course SET DEFAULT nextval('public.course_id_course_seq'::regclass);


--
-- TOC entry 3384 (class 2604 OID 58129)
-- Name: course_views id_view; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views ALTER COLUMN id_view SET DEFAULT nextval('public.course_views_id_view_seq'::regclass);


--
-- TOC entry 3387 (class 2604 OID 58130)
-- Name: feedback id_feedback; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback ALTER COLUMN id_feedback SET DEFAULT nextval('public.feedback_id_feedback_seq'::regclass);


--
-- TOC entry 3389 (class 2604 OID 58131)
-- Name: lessons id_lesson; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons ALTER COLUMN id_lesson SET DEFAULT nextval('public.lessons_id_lesson_seq'::regclass);


--
-- TOC entry 3390 (class 2604 OID 58132)
-- Name: questions id_question; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions ALTER COLUMN id_question SET DEFAULT nextval('public.questions_id_question_seq'::regclass);


--
-- TOC entry 3391 (class 2604 OID 58116)
-- Name: results id_result; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results ALTER COLUMN id_result SET DEFAULT nextval('public.results_id_result_seq'::regclass);


--
-- TOC entry 3392 (class 2604 OID 58117)
-- Name: stat id_stat; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat ALTER COLUMN id_stat SET DEFAULT nextval('public.stat_id_stat_seq'::regclass);


--
-- TOC entry 3393 (class 2604 OID 58133)
-- Name: steps id_step; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps ALTER COLUMN id_step SET DEFAULT nextval('public.steps_id_step_seq'::regclass);


--
-- TOC entry 3402 (class 2604 OID 58134)
-- Name: tags id_tag; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags ALTER COLUMN id_tag SET DEFAULT nextval('public.tags_id_tag_seq'::regclass);


--
-- TOC entry 3403 (class 2604 OID 58135)
-- Name: test_answers id_answer; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers ALTER COLUMN id_answer SET DEFAULT nextval('public.test_answers_id_answer_seq'::regclass);


--
-- TOC entry 3405 (class 2604 OID 58136)
-- Name: test_attempts id_attempt; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts ALTER COLUMN id_attempt SET DEFAULT nextval('public.test_attempts_id_attempt_seq'::regclass);


--
-- TOC entry 3407 (class 2604 OID 58137)
-- Name: test_grade_levels id_level; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels ALTER COLUMN id_level SET DEFAULT nextval('public.test_grade_levels_id_level_seq'::regclass);


--
-- TOC entry 3409 (class 2604 OID 58138)
-- Name: tests id_test; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests ALTER COLUMN id_test SET DEFAULT nextval('public.tests_id_test_seq'::regclass);


--
-- TOC entry 3417 (class 2604 OID 58139)
-- Name: users id_user; Type: DEFAULT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users ALTER COLUMN id_user SET DEFAULT nextval('public.users_id_user_seq'::regclass);


--
-- TOC entry 3705 (class 0 OID 57637)
-- Dependencies: 217
-- Data for Name: answer_options; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.answer_options VALUES (1, 76, 'Переменная - именованная область в памяти, которая может принимать определенное значение?');
INSERT INTO public.answer_options VALUES (2, 76, 'Переменная — это константа, которая не может изменять своё значение.');
INSERT INTO public.answer_options VALUES (3, 76, 'Переменная — это команда, которая выполняет определённое действие в программе.');
INSERT INTO public.answer_options VALUES (4, 76, 'Переменная — это тип данных, который определяет, какие значения можно хранить в памяти.');
INSERT INTO public.answer_options VALUES (5, 78, 'фыфы');
INSERT INTO public.answer_options VALUES (6, 78, 'фыфы');
INSERT INTO public.answer_options VALUES (7, 79, 'Переменная — именованная область в памяти, которая может принимать определенное значение?');
INSERT INTO public.answer_options VALUES (8, 79, 'Переменная — это набор констант, используемых в программе.');
INSERT INTO public.answer_options VALUES (9, 79, 'Переменная — это команда, которая выполняет определённые действия в программе.');
INSERT INTO public.answer_options VALUES (10, 79, 'Переменная — это характеристика, описывающая тип данных, но не их значение.');
INSERT INTO public.answer_options VALUES (11, 80, 'assort');
INSERT INTO public.answer_options VALUES (12, 80, 'sort');
INSERT INTO public.answer_options VALUES (13, 80, 'sortArray');
INSERT INTO public.answer_options VALUES (14, 80, 'ksort');
INSERT INTO public.answer_options VALUES (15, 81, 'count()||Подсчитывает количество элементов в массиве');
INSERT INTO public.answer_options VALUES (16, 81, 'str_replace()||Заменяет все вхождения подстроки в строке');
INSERT INTO public.answer_options VALUES (17, 81, 'array_merge()||Объединяет два или более массивов');
INSERT INTO public.answer_options VALUES (18, 81, 'file_get_contents()||Читает содержимое файла в строку');
INSERT INTO public.answer_options VALUES (19, 83, 'Переменная — это команда для выполнения определённых операций.');
INSERT INTO public.answer_options VALUES (20, 83, 'Переменная — это константа, которая не может изменять своё значение.');
INSERT INTO public.answer_options VALUES (21, 83, 'Переменная — именованная область в памяти, которая может принимать определенное значение?');
INSERT INTO public.answer_options VALUES (22, 83, 'Переменная — это тип данных, который определяет формат хранения информации.');
INSERT INTO public.answer_options VALUES (23, 84, 'count()||Подсчитывает количество элементов в массиве');
INSERT INTO public.answer_options VALUES (24, 84, 'str_replace()||Заменяет все вхождения подстроки в строке');
INSERT INTO public.answer_options VALUES (25, 84, 'array_merge()||Объединяет два или более массивов');
INSERT INTO public.answer_options VALUES (26, 84, 'file_get_contents()||Читает содержимое файла в строку');
INSERT INTO public.answer_options VALUES (48, 91, 'Переменная — именованная область в памяти, которая может принимать определенное значение?');
INSERT INTO public.answer_options VALUES (49, 91, 'Переменная — это константа, которая не может изменяться в процессе выполнения программы.');
INSERT INTO public.answer_options VALUES (50, 91, 'Переменная — это команда, которая выполняет определённые действия в программе.');
INSERT INTO public.answer_options VALUES (51, 91, 'Переменная — это характеристика, описывающая тип данных, но не их значение.');
INSERT INTO public.answer_options VALUES (52, 92, '$data = $_POST[''field_name''];');
INSERT INTO public.answer_options VALUES (53, 92, '$data = file_get_contents(''php://input'');');
INSERT INTO public.answer_options VALUES (54, 92, '$data = $_GET[''field_name''];');
INSERT INTO public.answer_options VALUES (55, 92, '$data = $_FILES[''field_name''];');
INSERT INTO public.answer_options VALUES (56, 92, '$data = $_SESSION[''field_name''];');
INSERT INTO public.answer_options VALUES (57, 93, 'count()||Подсчитывает количество элементов в массиве');
INSERT INTO public.answer_options VALUES (58, 93, 'str_replace()||Заменяет все вхождения подстроки в строке');
INSERT INTO public.answer_options VALUES (59, 93, 'array_merge()||Объединяет два или более массивов');
INSERT INTO public.answer_options VALUES (60, 93, 'file_get_contents()||Читает содержимое файла в строку');


--
-- TOC entry 3707 (class 0 OID 57641)
-- Dependencies: 219
-- Data for Name: answers; Type: TABLE DATA; Schema: public; Owner: pguser
--



--
-- TOC entry 3709 (class 0 OID 57645)
-- Dependencies: 221
-- Data for Name: certificates; Type: TABLE DATA; Schema: public; Owner: pguser
--



--
-- TOC entry 3711 (class 0 OID 57650)
-- Dependencies: 223
-- Data for Name: code_tasks; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.code_tasks VALUES (14, 77, '', 'hello world', 5, '<?php
// Ваш код здесь
?>', 'php');
INSERT INTO public.code_tasks VALUES (15, 82, '', '15', 5, '<?php

function sumArrayElements($array) {
    // ваш код здесь
    return $result;
}

$array = [1, 2, 3, 4, 5];
$result = sumArrayElements($array);

echo $result; // ожидаем вывод числа
?>', 'php');
INSERT INTO public.code_tasks VALUES (17, 94, '', '15', 5, '<?php
function sumArray(array $array): int {
    // ваш код здесь
}

// Пример использования функции
$numbers = [1, 2, 3, 4, 5];
echo sumArray($numbers); // ожидаем вывод: 15
?>', 'php');


--
-- TOC entry 3713 (class 0 OID 57659)
-- Dependencies: 225
-- Data for Name: course; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.course VALUES (33, 'Курс по основам C++', 'Учимся программировать на языке С++', false, '12', NULL, NULL, NULL, NULL, 'C++', 'approved', NULL);
INSERT INTO public.course VALUES (34, 'Основы Python', 'Курс по самому популярному языку программирования в мире', false, '12', NULL, NULL, NULL, NULL, 'Python', 'approved', NULL);
INSERT INTO public.course VALUES (36, 'Курс по веб разработке на PHP', 'Уникальный курс, который научит вас всему', false, '12', NULL, NULL, NULL, NULL, 'Программирование, WEB, PHP', 'approved', '');
INSERT INTO public.course VALUES (35, 'Учимся программировать на PHP', 'Курс для тех, кто хочет открыть мир программирования с PHP', false, '12', NULL, NULL, NULL, NULL, 'Python, Программированиен', 'approved', NULL);
INSERT INTO public.course VALUES (38, 'Высоконагруженные приложения на С++', 'Курс на С++', false, '13', NULL, NULL, NULL, NULL, 'C++, highload', 'approved', NULL);
INSERT INTO public.course VALUES (41, 'Основы PHP', 'Курс для тех, кто хочет открыть мир программирования с PHP', true, '12', NULL, NULL, NULL, NULL, 'PHP, Программирование,', 'pending', NULL);
INSERT INTO public.course VALUES (37, 'PHP в WEB разработке', '', true, '12', NULL, NULL, NULL, NULL, 'PHP,', 'approved', '');
INSERT INTO public.course VALUES (32, 'ML на C++', 'Хотите научиться создавать динамические веб-сайты и веб-приложения? PHP — один из самых популярных языков для backend-разработки, на котором работают WordPress, Facebook (ранние версии), Wikipedia и многие другие проекты.

Этот курс предназначен для тех, кто только начинает свой путь в программировании. Вы освоите базовые конструкции PHP, научитесь работать с базами данных, формами, сессиями и создадите свои первые веб-приложения.', true, '12', NULL, NULL, NULL, NULL, 'PHP, Программирование', 'approved', '');
INSERT INTO public.course VALUES (44, 'PHP для начинающих', 'Курс по PHP', true, '3', NULL, NULL, NULL, NULL, 'PHP, Программирование,', 'approved', '');


--
-- TOC entry 3715 (class 0 OID 57666)
-- Dependencies: 227
-- Data for Name: course_statistics; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.course_statistics VALUES (32, 32, 0, 0, 0, '2025-06-18 15:07:58.228107');
INSERT INTO public.course_statistics VALUES (33, 1, 0, 0, 0, '2025-06-15 16:14:45.833515');
INSERT INTO public.course_statistics VALUES (38, 9, 0, 0, 0, '2025-06-18 15:09:39.084303');
INSERT INTO public.course_statistics VALUES (44, 29, 0, 0, 0, '2025-06-18 19:32:56.692158');
INSERT INTO public.course_statistics VALUES (36, 32, 0, 0, 0, '2025-06-18 15:11:15.420424');
INSERT INTO public.course_statistics VALUES (35, 25, 0, 0, 0, '2025-06-18 16:02:07.824128');
INSERT INTO public.course_statistics VALUES (41, 1, 0, 0, 0, '2025-06-18 17:42:19.881435');
INSERT INTO public.course_statistics VALUES (37, 39, 0, 0, 0, '2025-06-18 17:48:17.584551');
INSERT INTO public.course_statistics VALUES (34, 7, 0, 0, 0, '2025-06-18 15:03:56.699493');


--
-- TOC entry 3716 (class 0 OID 57674)
-- Dependencies: 228
-- Data for Name: course_tags; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.course_tags VALUES (32, 31);
INSERT INTO public.course_tags VALUES (32, 32);
INSERT INTO public.course_tags VALUES (35, 35);
INSERT INTO public.course_tags VALUES (35, 36);
INSERT INTO public.course_tags VALUES (34, 31);
INSERT INTO public.course_tags VALUES (33, 37);
INSERT INTO public.course_tags VALUES (36, 32);
INSERT INTO public.course_tags VALUES (37, 31);
INSERT INTO public.course_tags VALUES (38, 37);
INSERT INTO public.course_tags VALUES (38, 38);
INSERT INTO public.course_tags VALUES (41, 31);
INSERT INTO public.course_tags VALUES (41, 32);
INSERT INTO public.course_tags VALUES (44, 31);
INSERT INTO public.course_tags VALUES (44, 32);


--
-- TOC entry 3717 (class 0 OID 57677)
-- Dependencies: 229
-- Data for Name: course_views; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.course_views VALUES (45, 34, 38, '2025-06-15 15:49:39.907703');
INSERT INTO public.course_views VALUES (46, 35, 38, '2025-06-15 16:01:06.93352');
INSERT INTO public.course_views VALUES (47, 35, 38, '2025-06-15 16:01:26.261186');
INSERT INTO public.course_views VALUES (48, 35, 38, '2025-06-15 16:05:44.980285');
INSERT INTO public.course_views VALUES (49, 34, 38, '2025-06-15 16:14:30.632417');
INSERT INTO public.course_views VALUES (50, 33, 38, '2025-06-15 16:14:45.830157');
INSERT INTO public.course_views VALUES (51, 32, 38, '2025-06-15 16:15:35.205892');
INSERT INTO public.course_views VALUES (52, 32, 40, '2025-06-15 16:16:00.821244');
INSERT INTO public.course_views VALUES (53, 32, 40, '2025-06-15 16:16:01.375755');
INSERT INTO public.course_views VALUES (54, 32, 40, '2025-06-15 16:16:02.273642');
INSERT INTO public.course_views VALUES (55, 32, 38, '2025-06-15 16:16:24.113083');
INSERT INTO public.course_views VALUES (56, 32, 40, '2025-06-15 16:16:54.858005');
INSERT INTO public.course_views VALUES (57, 32, 40, '2025-06-15 16:16:57.439516');
INSERT INTO public.course_views VALUES (58, 32, 40, '2025-06-15 16:17:05.262756');
INSERT INTO public.course_views VALUES (59, 32, 40, '2025-06-15 16:35:36.265539');
INSERT INTO public.course_views VALUES (60, 32, 40, '2025-06-15 16:36:21.075229');
INSERT INTO public.course_views VALUES (61, 36, 40, '2025-06-15 16:36:26.019024');
INSERT INTO public.course_views VALUES (62, 36, 40, '2025-06-15 16:36:27.562036');
INSERT INTO public.course_views VALUES (63, 36, 40, '2025-06-15 16:36:28.456356');
INSERT INTO public.course_views VALUES (64, 36, 38, '2025-06-15 16:37:18.004988');
INSERT INTO public.course_views VALUES (65, 36, 40, '2025-06-15 16:37:48.232242');
INSERT INTO public.course_views VALUES (66, 36, 40, '2025-06-15 16:37:53.972093');
INSERT INTO public.course_views VALUES (67, 36, 41, '2025-06-15 16:38:19.185934');
INSERT INTO public.course_views VALUES (68, 36, 41, '2025-06-15 16:38:19.805747');
INSERT INTO public.course_views VALUES (69, 36, 41, '2025-06-15 16:38:20.549889');
INSERT INTO public.course_views VALUES (70, 36, 41, '2025-06-15 16:39:49.286471');
INSERT INTO public.course_views VALUES (71, 36, 41, '2025-06-15 16:39:51.755818');
INSERT INTO public.course_views VALUES (72, 36, 41, '2025-06-15 16:41:37.185691');
INSERT INTO public.course_views VALUES (73, 37, 41, '2025-06-15 16:41:40.873501');
INSERT INTO public.course_views VALUES (74, 37, 41, '2025-06-15 16:41:41.746671');
INSERT INTO public.course_views VALUES (75, 37, 41, '2025-06-15 16:41:42.441865');
INSERT INTO public.course_views VALUES (76, 37, 41, '2025-06-15 16:41:45.149495');
INSERT INTO public.course_views VALUES (77, 36, 41, '2025-06-15 16:41:48.082289');
INSERT INTO public.course_views VALUES (78, 37, 41, '2025-06-15 16:41:55.222936');
INSERT INTO public.course_views VALUES (79, 37, 41, '2025-06-15 16:42:22.634698');
INSERT INTO public.course_views VALUES (80, 36, 38, '2025-06-15 16:43:15.82111');
INSERT INTO public.course_views VALUES (81, 36, 38, '2025-06-15 16:43:18.624084');
INSERT INTO public.course_views VALUES (82, 36, 38, '2025-06-15 16:43:23.29859');
INSERT INTO public.course_views VALUES (83, 37, 41, '2025-06-15 16:43:49.055547');
INSERT INTO public.course_views VALUES (84, 32, 41, '2025-06-15 16:44:12.806124');
INSERT INTO public.course_views VALUES (85, 32, 41, '2025-06-15 16:44:13.398282');
INSERT INTO public.course_views VALUES (86, 32, 41, '2025-06-15 16:44:14.049917');
INSERT INTO public.course_views VALUES (87, 32, 41, '2025-06-15 16:44:16.9143');
INSERT INTO public.course_views VALUES (88, 32, 41, '2025-06-15 16:44:24.336603');
INSERT INTO public.course_views VALUES (89, 32, 41, '2025-06-15 16:44:24.884434');
INSERT INTO public.course_views VALUES (90, 32, 41, '2025-06-15 16:44:25.434012');
INSERT INTO public.course_views VALUES (91, 37, 41, '2025-06-15 16:45:15.726787');
INSERT INTO public.course_views VALUES (92, 37, 41, '2025-06-15 16:46:24.948216');
INSERT INTO public.course_views VALUES (93, 37, 41, '2025-06-15 16:46:25.637016');
INSERT INTO public.course_views VALUES (94, 37, 41, '2025-06-15 16:48:16.590528');
INSERT INTO public.course_views VALUES (95, 37, 41, '2025-06-15 16:48:17.140131');
INSERT INTO public.course_views VALUES (96, 37, 41, '2025-06-15 16:48:17.628762');
INSERT INTO public.course_views VALUES (97, 37, 41, '2025-06-15 16:48:18.01614');
INSERT INTO public.course_views VALUES (98, 37, 41, '2025-06-15 16:48:18.490123');
INSERT INTO public.course_views VALUES (99, 37, 41, '2025-06-15 16:48:19.164137');
INSERT INTO public.course_views VALUES (100, 37, 41, '2025-06-15 16:49:08.815337');
INSERT INTO public.course_views VALUES (101, 37, 41, '2025-06-15 16:49:13.136623');
INSERT INTO public.course_views VALUES (102, 37, 41, '2025-06-15 16:49:15.911276');
INSERT INTO public.course_views VALUES (103, 37, 41, '2025-06-15 16:49:16.437753');
INSERT INTO public.course_views VALUES (104, 36, 41, '2025-06-15 16:49:28.062478');
INSERT INTO public.course_views VALUES (105, 37, 41, '2025-06-15 16:49:29.755336');
INSERT INTO public.course_views VALUES (106, 32, 41, '2025-06-15 16:49:31.159437');
INSERT INTO public.course_views VALUES (107, 37, 38, '2025-06-15 16:50:21.317726');
INSERT INTO public.course_views VALUES (108, 36, 41, '2025-06-15 16:50:54.871454');
INSERT INTO public.course_views VALUES (109, 37, 41, '2025-06-15 16:51:02.544026');
INSERT INTO public.course_views VALUES (110, 36, 36, '2025-06-18 14:39:29.992036');
INSERT INTO public.course_views VALUES (111, 36, 36, '2025-06-18 14:39:33.349382');
INSERT INTO public.course_views VALUES (112, 36, 36, '2025-06-18 14:39:35.33489');
INSERT INTO public.course_views VALUES (113, 37, 36, '2025-06-18 14:45:24.242136');
INSERT INTO public.course_views VALUES (114, 37, 37, '2025-06-18 14:47:28.292393');
INSERT INTO public.course_views VALUES (115, 36, 37, '2025-06-18 14:47:31.196984');
INSERT INTO public.course_views VALUES (116, 36, 42, '2025-06-18 14:47:55.309392');
INSERT INTO public.course_views VALUES (117, 37, 42, '2025-06-18 14:52:05.410744');
INSERT INTO public.course_views VALUES (118, 32, 42, '2025-06-18 14:52:07.549197');
INSERT INTO public.course_views VALUES (119, 32, 42, '2025-06-18 14:52:15.99368');
INSERT INTO public.course_views VALUES (120, 32, 42, '2025-06-18 14:52:16.72176');
INSERT INTO public.course_views VALUES (121, 32, 42, '2025-06-18 14:52:19.297386');
INSERT INTO public.course_views VALUES (122, 32, 42, '2025-06-18 14:52:25.067094');
INSERT INTO public.course_views VALUES (123, 36, 42, '2025-06-18 14:53:07.035045');
INSERT INTO public.course_views VALUES (124, 36, 42, '2025-06-18 14:53:22.32937');
INSERT INTO public.course_views VALUES (125, 36, 42, '2025-06-18 14:53:23.370907');
INSERT INTO public.course_views VALUES (126, 32, 42, '2025-06-18 14:53:27.550364');
INSERT INTO public.course_views VALUES (127, 36, 43, '2025-06-18 14:53:54.399926');
INSERT INTO public.course_views VALUES (128, 32, 43, '2025-06-18 14:53:56.695877');
INSERT INTO public.course_views VALUES (129, 36, 43, '2025-06-18 14:55:08.765688');
INSERT INTO public.course_views VALUES (130, 32, 43, '2025-06-18 14:55:10.81908');
INSERT INTO public.course_views VALUES (131, 32, 43, '2025-06-18 14:55:29.065032');
INSERT INTO public.course_views VALUES (132, 32, 43, '2025-06-18 14:55:29.915594');
INSERT INTO public.course_views VALUES (133, 36, 43, '2025-06-18 14:56:01.968265');
INSERT INTO public.course_views VALUES (134, 37, 43, '2025-06-18 14:56:05.027956');
INSERT INTO public.course_views VALUES (135, 37, 43, '2025-06-18 14:56:06.28553');
INSERT INTO public.course_views VALUES (136, 37, 43, '2025-06-18 14:56:07.028838');
INSERT INTO public.course_views VALUES (137, 34, 43, '2025-06-18 14:56:23.102438');
INSERT INTO public.course_views VALUES (138, 34, 43, '2025-06-18 14:56:23.82342');
INSERT INTO public.course_views VALUES (139, 34, 43, '2025-06-18 14:56:24.440096');
INSERT INTO public.course_views VALUES (140, 34, 43, '2025-06-18 14:56:26.199433');
INSERT INTO public.course_views VALUES (141, 35, 43, '2025-06-18 14:57:11.743736');
INSERT INTO public.course_views VALUES (142, 35, 43, '2025-06-18 14:57:18.229918');
INSERT INTO public.course_views VALUES (143, 35, 43, '2025-06-18 14:57:19.480004');
INSERT INTO public.course_views VALUES (144, 35, 43, '2025-06-18 14:57:20.230869');
INSERT INTO public.course_views VALUES (145, 35, 43, '2025-06-18 14:57:33.015554');
INSERT INTO public.course_views VALUES (146, 35, 43, '2025-06-18 14:58:10.839011');
INSERT INTO public.course_views VALUES (147, 35, 43, '2025-06-18 14:59:09.389658');
INSERT INTO public.course_views VALUES (148, 35, 43, '2025-06-18 14:59:13.645551');
INSERT INTO public.course_views VALUES (149, 35, 43, '2025-06-18 14:59:20.314407');
INSERT INTO public.course_views VALUES (150, 35, 36, '2025-06-18 14:59:31.563576');
INSERT INTO public.course_views VALUES (151, 35, 36, '2025-06-18 14:59:33.502602');
INSERT INTO public.course_views VALUES (152, 35, 36, '2025-06-18 15:03:28.303265');
INSERT INTO public.course_views VALUES (153, 35, 43, '2025-06-18 15:03:48.520353');
INSERT INTO public.course_views VALUES (154, 35, 43, '2025-06-18 15:03:52.422402');
INSERT INTO public.course_views VALUES (155, 34, 43, '2025-06-18 15:03:56.660453');
INSERT INTO public.course_views VALUES (156, 32, 43, '2025-06-18 15:03:59.515031');
INSERT INTO public.course_views VALUES (157, 32, 43, '2025-06-18 15:04:02.05238');
INSERT INTO public.course_views VALUES (158, 38, 36, '2025-06-18 15:04:05.917025');
INSERT INTO public.course_views VALUES (159, 38, 36, '2025-06-18 15:04:21.096036');
INSERT INTO public.course_views VALUES (160, 38, 36, '2025-06-18 15:04:22.953557');
INSERT INTO public.course_views VALUES (161, 32, 36, '2025-06-18 15:05:05.144047');
INSERT INTO public.course_views VALUES (162, 36, 36, '2025-06-18 15:05:07.442345');
INSERT INTO public.course_views VALUES (163, 32, 36, '2025-06-18 15:07:58.223678');
INSERT INTO public.course_views VALUES (164, 38, 36, '2025-06-18 15:08:35.693465');
INSERT INTO public.course_views VALUES (165, 37, 38, '2025-06-18 15:08:52.918');
INSERT INTO public.course_views VALUES (166, 38, 43, '2025-06-18 15:09:22.044769');
INSERT INTO public.course_views VALUES (167, 38, 43, '2025-06-18 15:09:22.840627');
INSERT INTO public.course_views VALUES (168, 38, 43, '2025-06-18 15:09:23.474153');
INSERT INTO public.course_views VALUES (169, 38, 43, '2025-06-18 15:09:25.08164');
INSERT INTO public.course_views VALUES (170, 38, 43, '2025-06-18 15:09:39.081153');
INSERT INTO public.course_views VALUES (171, 37, 43, '2025-06-18 15:09:41.103257');
INSERT INTO public.course_views VALUES (172, 37, 43, '2025-06-18 15:09:47.913909');
INSERT INTO public.course_views VALUES (173, 35, 43, '2025-06-18 15:09:49.275676');
INSERT INTO public.course_views VALUES (174, 37, 38, '2025-06-18 15:09:59.620442');
INSERT INTO public.course_views VALUES (175, 37, 43, '2025-06-18 15:10:31.188341');
INSERT INTO public.course_views VALUES (176, 36, 38, '2025-06-18 15:11:11.684014');
INSERT INTO public.course_views VALUES (177, 36, 38, '2025-06-18 15:11:15.381595');
INSERT INTO public.course_views VALUES (178, 35, 38, '2025-06-18 15:11:19.141547');
INSERT INTO public.course_views VALUES (179, 35, 43, '2025-06-18 15:18:51.859465');
INSERT INTO public.course_views VALUES (180, 35, 41, '2025-06-18 15:24:23.281014');
INSERT INTO public.course_views VALUES (181, 35, 41, '2025-06-18 15:24:24.628617');
INSERT INTO public.course_views VALUES (182, 35, 41, '2025-06-18 15:24:25.316236');
INSERT INTO public.course_views VALUES (185, 35, 41, '2025-06-18 15:28:01.761691');
INSERT INTO public.course_views VALUES (186, 37, 38, '2025-06-18 16:01:30.431554');
INSERT INTO public.course_views VALUES (187, 35, 38, '2025-06-18 16:02:07.821026');
INSERT INTO public.course_views VALUES (188, 41, 38, '2025-06-18 17:42:19.87739');
INSERT INTO public.course_views VALUES (189, 37, 45, '2025-06-18 17:47:03.482447');
INSERT INTO public.course_views VALUES (190, 37, 45, '2025-06-18 17:48:07.62806');
INSERT INTO public.course_views VALUES (191, 37, 45, '2025-06-18 17:48:08.251767');
INSERT INTO public.course_views VALUES (192, 37, 45, '2025-06-18 17:48:17.580796');
INSERT INTO public.course_views VALUES (200, 44, 50, '2025-06-18 18:37:00.89158');
INSERT INTO public.course_views VALUES (201, 44, 50, '2025-06-18 18:37:16.247956');
INSERT INTO public.course_views VALUES (202, 44, 51, '2025-06-18 18:40:45.512685');
INSERT INTO public.course_views VALUES (203, 44, 51, '2025-06-18 18:40:47.188832');
INSERT INTO public.course_views VALUES (204, 44, 51, '2025-06-18 18:40:48.47016');
INSERT INTO public.course_views VALUES (205, 44, 51, '2025-06-18 18:46:49.676874');
INSERT INTO public.course_views VALUES (206, 44, 51, '2025-06-18 18:48:32.567552');
INSERT INTO public.course_views VALUES (207, 44, 51, '2025-06-18 18:48:58.501341');
INSERT INTO public.course_views VALUES (208, 44, 51, '2025-06-18 18:51:22.664715');
INSERT INTO public.course_views VALUES (209, 44, 51, '2025-06-18 18:51:23.553086');
INSERT INTO public.course_views VALUES (210, 44, 50, '2025-06-18 18:53:03.284672');
INSERT INTO public.course_views VALUES (211, 44, 51, '2025-06-18 18:54:18.483363');
INSERT INTO public.course_views VALUES (212, 44, 52, '2025-06-18 18:54:58.564237');
INSERT INTO public.course_views VALUES (213, 44, 52, '2025-06-18 18:54:59.378646');
INSERT INTO public.course_views VALUES (214, 44, 52, '2025-06-18 18:54:59.950732');
INSERT INTO public.course_views VALUES (215, 44, 52, '2025-06-18 18:57:30.776147');
INSERT INTO public.course_views VALUES (216, 44, 52, '2025-06-18 18:59:53.663518');
INSERT INTO public.course_views VALUES (217, 44, 41, '2025-06-18 19:03:46.321044');
INSERT INTO public.course_views VALUES (218, 44, 41, '2025-06-18 19:03:47.208539');
INSERT INTO public.course_views VALUES (219, 44, 41, '2025-06-18 19:03:47.845085');
INSERT INTO public.course_views VALUES (220, 44, 43, '2025-06-18 19:06:22.993441');
INSERT INTO public.course_views VALUES (221, 44, 43, '2025-06-18 19:06:23.51898');
INSERT INTO public.course_views VALUES (222, 44, 43, '2025-06-18 19:06:24.104553');
INSERT INTO public.course_views VALUES (223, 44, 51, '2025-06-18 19:10:01.07209');
INSERT INTO public.course_views VALUES (224, 44, 51, '2025-06-18 19:10:38.279875');
INSERT INTO public.course_views VALUES (225, 44, 51, '2025-06-18 19:10:46.553446');
INSERT INTO public.course_views VALUES (226, 44, 51, '2025-06-18 19:10:55.833566');
INSERT INTO public.course_views VALUES (227, 44, 51, '2025-06-18 19:11:02.055938');
INSERT INTO public.course_views VALUES (228, 44, 51, '2025-06-18 19:32:56.688534');


--
-- TOC entry 3719 (class 0 OID 57682)
-- Dependencies: 231
-- Data for Name: create_passes; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.create_passes VALUES (32, 38, true, NULL);
INSERT INTO public.create_passes VALUES (33, 38, true, NULL);
INSERT INTO public.create_passes VALUES (34, 38, true, NULL);
INSERT INTO public.create_passes VALUES (35, 38, true, NULL);
INSERT INTO public.create_passes VALUES (32, 40, false, '2025-06-15 16:16:57.452928');
INSERT INTO public.create_passes VALUES (36, 38, true, NULL);
INSERT INTO public.create_passes VALUES (36, 40, false, '2025-06-15 16:37:48.282181');
INSERT INTO public.create_passes VALUES (36, 41, false, '2025-06-15 16:39:49.30061');
INSERT INTO public.create_passes VALUES (37, 38, true, NULL);
INSERT INTO public.create_passes VALUES (37, 41, false, '2025-06-15 16:41:45.170758');
INSERT INTO public.create_passes VALUES (32, 41, false, '2025-06-15 16:44:16.927286');
INSERT INTO public.create_passes VALUES (32, 42, false, '2025-06-18 14:52:19.311245');
INSERT INTO public.create_passes VALUES (37, 43, false, NULL);
INSERT INTO public.create_passes VALUES (34, 43, false, NULL);
INSERT INTO public.create_passes VALUES (35, 43, false, NULL);
INSERT INTO public.create_passes VALUES (38, 36, true, NULL);
INSERT INTO public.create_passes VALUES (32, 43, false, '2025-06-18 15:04:02.066856');
INSERT INTO public.create_passes VALUES (38, 43, false, NULL);
INSERT INTO public.create_passes VALUES (35, 41, false, NULL);
INSERT INTO public.create_passes VALUES (41, 38, true, NULL);
INSERT INTO public.create_passes VALUES (44, 50, true, NULL);
INSERT INTO public.create_passes VALUES (44, 51, false, '2025-06-18 18:48:32.584502');
INSERT INTO public.create_passes VALUES (44, 52, false, NULL);
INSERT INTO public.create_passes VALUES (44, 41, false, NULL);
INSERT INTO public.create_passes VALUES (44, 43, false, NULL);


--
-- TOC entry 3720 (class 0 OID 57686)
-- Dependencies: 232
-- Data for Name: feedback; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.feedback VALUES (17, 32, 'Отличный курс', '2025-06-18', '5', 42, 'approved');
INSERT INTO public.feedback VALUES (16, 32, 'Отличный курс, все понравилось!', '2025-06-15', '5', 40, 'pending');


--
-- TOC entry 3722 (class 0 OID 57692)
-- Dependencies: 234
-- Data for Name: lessons; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.lessons VALUES (38, 34, NULL, 'AS', 'new');
INSERT INTO public.lessons VALUES (41, 35, NULL, 'Урок 1. Основы', 'new');
INSERT INTO public.lessons VALUES (42, 35, NULL, 'Урок 2. Циклы', 'new');
INSERT INTO public.lessons VALUES (43, 32, NULL, 'as', 'new');
INSERT INTO public.lessons VALUES (44, 36, NULL, 'Урок 1. Основы', 'new');
INSERT INTO public.lessons VALUES (45, 37, NULL, 'Переменные', 'new');
INSERT INTO public.lessons VALUES (46, 38, NULL, 'Урок 1. Основы', 'new');
INSERT INTO public.lessons VALUES (47, 41, NULL, 'Урок 1. Основы', 'new');
INSERT INTO public.lessons VALUES (49, 44, NULL, 'Урок 1. Основы', 'new');


--
-- TOC entry 3724 (class 0 OID 57698)
-- Dependencies: 236
-- Data for Name: material; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.material VALUES ('684eef7a1c874                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 82, 'materials/quixotesoulasas@gmail.com/Урок 1. Основы/Переменные_82.pdf', NULL);
INSERT INTO public.material VALUES ('684eefa4011a5                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 83, 'materials/quixotesoulasas@gmail.com/Урок 1. Основы/Ввод-вывод данных_83.pdf', NULL);
INSERT INTO public.material VALUES ('684eeff16dd2e                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 85, 'materials/quixotesoulasas@gmail.com/Урок 2. Циклы/Условные операторы_85.pdf', NULL);
INSERT INTO public.material VALUES ('684ef0090534e                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 86, NULL, 'https://ya.ru/');
INSERT INTO public.material VALUES ('684ef1e3d52e5                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 88, NULL, 'https://ya.ru/');
INSERT INTO public.material VALUES ('684ef57a69262                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 89, 'materials/quixotesoulasas@gmail.com/Урок 1. Основы/Тестовый шаг_89.pdf', NULL);
INSERT INTO public.material VALUES ('684ef78bc5bd7                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 91, NULL, 'https://ya.ru/');
INSERT INTO public.material VALUES ('6852db112157c                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 93, 'materials/quixotesoulasas@gmail.com/Урок 1. Основы/Переменные_93.pdf', NULL);
INSERT INTO public.material VALUES ('6852db2b5941a                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 94, 'materials/quixotesoulasas@gmail.com/Урок 1. Основы/Ввод-вывод данных_94.pdf', NULL);
INSERT INTO public.material VALUES ('6852fa8c69883                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 96, 'materials/quixotesoulasas@gmail.com/Урок 1. Основы/Синтаксис_96.pdf', NULL);
INSERT INTO public.material VALUES ('6853076eb5138                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 100, 'materials/maxrox1903@vk.com/Урок 1. Основы/Синтаксис PHP_100.pdf', NULL);
INSERT INTO public.material VALUES ('6853077d03824                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           ', 101, NULL, 'https://vkvideo.ru/video-16108331_456254498');


--
-- TOC entry 3725 (class 0 OID 57703)
-- Dependencies: 237
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.questions VALUES (76, 33, 'Что такое переменная?', '0', 'single', NULL);
INSERT INTO public.questions VALUES (77, 33, 'Выведите hello world', '', 'code', NULL);
INSERT INTO public.questions VALUES (78, 34, 'фыфы', '0', 'single', NULL);
INSERT INTO public.questions VALUES (79, 31, 'Что такое переменная?', '0', 'single', NULL);
INSERT INTO public.questions VALUES (80, 31, 'Какие функции сортируют массив по значению?', '0,1', 'multi', NULL);
INSERT INTO public.questions VALUES (81, 31, 'Сопоставьте функции PHP с их назначением', '', 'match', NULL);
INSERT INTO public.questions VALUES (82, 31, 'Суммируйте элементы массива', '', 'code', NULL);
INSERT INTO public.questions VALUES (83, 35, 'Что такое переменная?', '2', 'single', NULL);
INSERT INTO public.questions VALUES (84, 35, 'Сопоставьте функции php с их назначением', '', 'match', NULL);
INSERT INTO public.questions VALUES (91, 37, 'Что такое переменная?', '0', 'single', NULL);
INSERT INTO public.questions VALUES (92, 37, 'Какие два способа являются корректными для получения данных из POST-запроса в PHP?', '0,1', 'multi', NULL);
INSERT INTO public.questions VALUES (93, 37, 'Сопоставьте функции PHP с их назначением', '', 'match', NULL);
INSERT INTO public.questions VALUES (94, 37, 'Напишите функцию, которая будет складывать элементы массива', '', 'code', NULL);


--
-- TOC entry 3727 (class 0 OID 57709)
-- Dependencies: 239
-- Data for Name: results; Type: TABLE DATA; Schema: public; Owner: pguser
--



--
-- TOC entry 3729 (class 0 OID 57713)
-- Dependencies: 241
-- Data for Name: stat; Type: TABLE DATA; Schema: public; Owner: pguser
--



--
-- TOC entry 3731 (class 0 OID 57717)
-- Dependencies: 243
-- Data for Name: steps; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.steps VALUES (82, 41, 'Переменные', 'not_started', 'material');
INSERT INTO public.steps VALUES (83, 41, 'Ввод-вывод данных', 'not_started', 'material');
INSERT INTO public.steps VALUES (84, 41, 'Тест по Уроку 1', 'not_started', 'test');
INSERT INTO public.steps VALUES (85, 42, 'Условные операторы', 'not_started', 'material');
INSERT INTO public.steps VALUES (86, 42, 'Циклы for и while', 'not_started', 'material');
INSERT INTO public.steps VALUES (87, 42, 'Итоговый тест по Уроку 2', 'not_started', 'test');
INSERT INTO public.steps VALUES (88, 43, '1', 'not_started', 'material');
INSERT INTO public.steps VALUES (89, 44, 'Тестовый шаг', 'not_started', 'material');
INSERT INTO public.steps VALUES (90, 44, 'Тест', 'not_started', 'test');
INSERT INTO public.steps VALUES (91, 45, 'шаг', 'not_started', 'material');
INSERT INTO public.steps VALUES (92, 45, 'фы', 'not_started', 'test');
INSERT INTO public.steps VALUES (93, 47, 'Переменные', 'not_started', 'material');
INSERT INTO public.steps VALUES (94, 47, 'Ввод-вывод данных', 'not_started', 'material');
INSERT INTO public.steps VALUES (95, 47, 'Тест по Уроку 1', 'not_started', 'test');
INSERT INTO public.steps VALUES (96, 47, 'Синтаксис', 'not_started', 'material');
INSERT INTO public.steps VALUES (100, 49, 'Синтаксис PHP', 'not_started', 'material');
INSERT INTO public.steps VALUES (101, 49, 'Массивы и функции PHP', 'not_started', 'material');
INSERT INTO public.steps VALUES (102, 49, 'Итоговый тест', 'not_started', 'test');


--
-- TOC entry 3733 (class 0 OID 57725)
-- Dependencies: 245
-- Data for Name: student_analytics; Type: TABLE DATA; Schema: public; Owner: pguser
--



--
-- TOC entry 3734 (class 0 OID 57733)
-- Dependencies: 246
-- Data for Name: student_test_settings; Type: TABLE DATA; Schema: public; Owner: pguser
--



--
-- TOC entry 3735 (class 0 OID 57737)
-- Dependencies: 247
-- Data for Name: tags; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.tags VALUES (31, 'PHP');
INSERT INTO public.tags VALUES (32, 'Программирование');
INSERT INTO public.tags VALUES (33, 'фы');
INSERT INTO public.tags VALUES (34, 'AS');
INSERT INTO public.tags VALUES (35, 'Python');
INSERT INTO public.tags VALUES (36, 'Программированиен');
INSERT INTO public.tags VALUES (37, 'C++');
INSERT INTO public.tags VALUES (38, 'highload');


--
-- TOC entry 3737 (class 0 OID 57741)
-- Dependencies: 249
-- Data for Name: test_answers; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.test_answers VALUES (316, 132, 76, 1, true, '2025-06-15 16:37:02.900612', NULL, NULL);
INSERT INTO public.test_answers VALUES (317, 133, 76, 1, true, '2025-06-15 16:38:49.45986', NULL, NULL);
INSERT INTO public.test_answers VALUES (318, 133, 77, NULL, false, '2025-06-15 16:38:49.46591', '<?php
echo "poka"
?>', 'НЕПРАВИЛЬНО: вывод программы — ''poka'', а не ''hello world''.');
INSERT INTO public.test_answers VALUES (319, 134, 78, 5, true, '2025-06-18 15:10:36.571474', NULL, NULL);
INSERT INTO public.test_answers VALUES (352, 143, 91, 48, true, '2025-06-18 19:02:22.957718', NULL, NULL);
INSERT INTO public.test_answers VALUES (353, 143, 92, 52, false, '2025-06-18 19:02:22.964619', '["0"]', NULL);
INSERT INTO public.test_answers VALUES (354, 143, 93, NULL, false, '2025-06-18 19:02:22.967254', '["0","2","1","1"]', NULL);
INSERT INTO public.test_answers VALUES (355, 143, 94, NULL, false, '2025-06-18 19:02:22.97599', '<?php
function sumArray(array $array): int {
фывфывфыв
}

// Пример использования функции
$numbers = [1, 2, 3, 4, 5];
echo sumArray($numbers); // ожидаем вывод: 15
?>', 'НЕПРАВИЛЬНО: код содержит синтаксическую ошибку и не выполняет задачу сложения элементов массива.');
INSERT INTO public.test_answers VALUES (356, 144, 91, NULL, false, '2025-06-18 19:03:31.88876', NULL, NULL);
INSERT INTO public.test_answers VALUES (357, 144, 92, NULL, false, '2025-06-18 19:03:31.890802', 'null', NULL);
INSERT INTO public.test_answers VALUES (358, 144, 93, NULL, false, '2025-06-18 19:03:31.892321', 'null', NULL);
INSERT INTO public.test_answers VALUES (359, 144, 94, NULL, false, '2025-06-18 19:03:31.894356', NULL, '');
INSERT INTO public.test_answers VALUES (360, 145, 91, 48, true, '2025-06-18 19:07:08.325118', NULL, NULL);
INSERT INTO public.test_answers VALUES (361, 145, 92, 55, false, '2025-06-18 19:07:08.327553', '["3","4"]', NULL);
INSERT INTO public.test_answers VALUES (362, 145, 93, NULL, false, '2025-06-18 19:07:08.329085', '["1","2","3","1"]', NULL);
INSERT INTO public.test_answers VALUES (363, 145, 94, NULL, false, '2025-06-18 19:07:08.331208', '<?php
function sumArray(array $array): int {
    return 0;
}

// Пример использования функции
$numbers = [1, 2, 3, 4, 5];
echo "15"; // ожидаем вывод: 15
?>', 'НЕПРАВИЛЬНО: функция всегда возвращает 0, не суммируя элементы массива.');
INSERT INTO public.test_answers VALUES (364, 146, 91, 48, true, '2025-06-18 19:09:27.883942', NULL, NULL);
INSERT INTO public.test_answers VALUES (365, 146, 92, 53, true, '2025-06-18 19:09:27.886307', '["1","0"]', NULL);
INSERT INTO public.test_answers VALUES (366, 146, 93, NULL, true, '2025-06-18 19:09:27.88803', '["0","1","2","3"]', NULL);
INSERT INTO public.test_answers VALUES (367, 146, 94, NULL, true, '2025-06-18 19:09:27.890224', '<?php
function sumArray(array $array): int {
    $sum = 0;
    foreach ($array as $number) {
        $sum += $number;
    }
    return $sum;
}

// Пример использования функции
$numbers = [1, 2, 3, 4, 5];
echo sumArray($numbers); // Выведет: 15
?>', 'ПРАВИЛЬНО: функция корректно суммирует элементы массива.');
INSERT INTO public.test_answers VALUES (368, 147, 91, 48, true, '2025-06-18 19:10:29.426292', NULL, NULL);
INSERT INTO public.test_answers VALUES (369, 147, 92, 53, true, '2025-06-18 19:10:29.428783', '["1","0"]', NULL);
INSERT INTO public.test_answers VALUES (370, 147, 93, NULL, true, '2025-06-18 19:10:29.430324', '["0","1","2","3"]', NULL);
INSERT INTO public.test_answers VALUES (371, 147, 94, NULL, true, '2025-06-18 19:10:29.432407', '<?php
function sumArray(array $array): int {
    $sum = 0;
    foreach ($array as $number) {
        $sum += $number;
    }
    return $sum;
}

// Пример использования функции
$numbers = [1, 2, 3, 4, 5];
echo sumArray($numbers); // Выведет: 15
?>', 'ПРАВИЛЬНО: функция корректно суммирует элементы массива.');


--
-- TOC entry 3739 (class 0 OID 57748)
-- Dependencies: 251
-- Data for Name: test_attempts; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.test_attempts VALUES (132, 33, 40, '2025-06-15 16:37:02.878722', '2025-06-15 16:37:02.878722', 1, 1, 'completed');
INSERT INTO public.test_attempts VALUES (133, 33, 41, '2025-06-15 16:38:49.420051', '2025-06-15 16:38:49.420051', 1, 2, 'completed');
INSERT INTO public.test_attempts VALUES (134, 34, 43, '2025-06-18 15:10:36.56539', '2025-06-18 15:10:36.56539', 1, 1, 'completed');
INSERT INTO public.test_attempts VALUES (143, 37, 52, '2025-06-18 19:02:22.942817', '2025-06-18 19:02:22.942817', 1, 4, 'completed');
INSERT INTO public.test_attempts VALUES (144, 37, 52, '2025-06-18 19:03:31.884812', '2025-06-18 19:03:31.884812', 0, 4, 'completed');
INSERT INTO public.test_attempts VALUES (145, 37, 43, '2025-06-18 19:07:08.286599', '2025-06-18 19:07:08.286599', 1, 4, 'completed');
INSERT INTO public.test_attempts VALUES (146, 37, 43, '2025-06-18 19:09:27.845398', '2025-06-18 19:09:27.845398', 4, 4, 'completed');
INSERT INTO public.test_attempts VALUES (147, 37, 51, '2025-06-18 19:10:29.388932', '2025-06-18 19:10:29.388932', 4, 4, 'completed');


--
-- TOC entry 3741 (class 0 OID 57754)
-- Dependencies: 253
-- Data for Name: test_grade_levels; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.test_grade_levels VALUES (9, 37, 0, 59, 'Не пройдено', '#ff0000');
INSERT INTO public.test_grade_levels VALUES (10, 37, 60, 74, 'Удовлетворительно', '#ffa500');
INSERT INTO public.test_grade_levels VALUES (11, 37, 75, 89, 'Хорошо', '#2ecc40');
INSERT INTO public.test_grade_levels VALUES (12, 37, 90, 100, 'Отлично', '#0e6eb8');


--
-- TOC entry 3743 (class 0 OID 57762)
-- Dependencies: 255
-- Data for Name: tests; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.tests VALUES (31, 84, 'Новый тест', '', 70, 3, 0, true, false);
INSERT INTO public.tests VALUES (32, 87, 'Новый тест', '', 70, 3, 0, true, false);
INSERT INTO public.tests VALUES (33, 90, 'Новый тест', '', 70, 3, 0, true, false);
INSERT INTO public.tests VALUES (34, 92, 'Новый тест', '', 70, 3, 0, true, false);
INSERT INTO public.tests VALUES (35, 95, 'Новый тест', '', 70, 3, 0, true, false);
INSERT INTO public.tests VALUES (37, 102, 'Новый тест', '', 50, 2, 0, true, false);


--
-- TOC entry 3745 (class 0 OID 57775)
-- Dependencies: 257
-- Data for Name: user_material_progress; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.user_material_progress VALUES (40, 88, '2025-06-15 16:16:56.292951');
INSERT INTO public.user_material_progress VALUES (40, 89, '2025-06-15 16:36:43.962818');
INSERT INTO public.user_material_progress VALUES (41, 89, '2025-06-15 16:38:23.116809');
INSERT INTO public.user_material_progress VALUES (41, 91, '2025-06-15 16:41:43.914308');
INSERT INTO public.user_material_progress VALUES (41, 88, '2025-06-15 16:44:16.157036');
INSERT INTO public.user_material_progress VALUES (42, 88, '2025-06-18 14:52:18.517491');
INSERT INTO public.user_material_progress VALUES (43, 82, '2025-06-18 14:58:49.755499');
INSERT INTO public.user_material_progress VALUES (43, 83, '2025-06-18 14:59:05.421475');
INSERT INTO public.user_material_progress VALUES (43, 88, '2025-06-18 15:04:01.705854');
INSERT INTO public.user_material_progress VALUES (43, 91, '2025-06-18 15:09:45.416019');
INSERT INTO public.user_material_progress VALUES (51, 100, '2025-06-18 18:40:56.64704');
INSERT INTO public.user_material_progress VALUES (51, 101, '2025-06-18 18:41:04.019537');
INSERT INTO public.user_material_progress VALUES (52, 100, '2025-06-18 18:55:01.258083');
INSERT INTO public.user_material_progress VALUES (52, 101, '2025-06-18 18:55:02.040806');
INSERT INTO public.user_material_progress VALUES (41, 100, '2025-06-18 19:03:48.941537');
INSERT INTO public.user_material_progress VALUES (41, 101, '2025-06-18 19:03:49.608914');
INSERT INTO public.user_material_progress VALUES (43, 100, '2025-06-18 19:06:25.372286');
INSERT INTO public.user_material_progress VALUES (43, 101, '2025-06-18 19:06:26.341174');


--
-- TOC entry 3746 (class 0 OID 57779)
-- Dependencies: 258
-- Data for Name: user_tag_interests; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.user_tag_interests VALUES (41, 32, 2.5);
INSERT INTO public.user_tag_interests VALUES (41, 31, 2.5);
INSERT INTO public.user_tag_interests VALUES (41, 36, 1.5);
INSERT INTO public.user_tag_interests VALUES (41, 35, 1.5);
INSERT INTO public.user_tag_interests VALUES (43, 36, 1.5);
INSERT INTO public.user_tag_interests VALUES (43, 32, 2);
INSERT INTO public.user_tag_interests VALUES (43, 31, 3);
INSERT INTO public.user_tag_interests VALUES (43, 35, 1.5);
INSERT INTO public.user_tag_interests VALUES (43, 37, 1.5);
INSERT INTO public.user_tag_interests VALUES (40, 32, 2);
INSERT INTO public.user_tag_interests VALUES (40, 31, 1.5);
INSERT INTO public.user_tag_interests VALUES (42, 32, 1.5);
INSERT INTO public.user_tag_interests VALUES (42, 31, 1.5);
INSERT INTO public.user_tag_interests VALUES (43, 38, 1.5);
INSERT INTO public.user_tag_interests VALUES (50, 32, 1.5);
INSERT INTO public.user_tag_interests VALUES (38, 32, 2.5);
INSERT INTO public.user_tag_interests VALUES (38, 36, 1.5);
INSERT INTO public.user_tag_interests VALUES (38, 31, 3);
INSERT INTO public.user_tag_interests VALUES (38, 35, 1.5);
INSERT INTO public.user_tag_interests VALUES (38, 37, 1.5);
INSERT INTO public.user_tag_interests VALUES (50, 31, 1.5);
INSERT INTO public.user_tag_interests VALUES (52, 32, 1.5);
INSERT INTO public.user_tag_interests VALUES (52, 31, 1.5);
INSERT INTO public.user_tag_interests VALUES (36, 37, 1.5);
INSERT INTO public.user_tag_interests VALUES (36, 38, 1.5);
INSERT INTO public.user_tag_interests VALUES (51, 32, 1.5);
INSERT INTO public.user_tag_interests VALUES (51, 31, 1.5);


--
-- TOC entry 3747 (class 0 OID 57783)
-- Dependencies: 259
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: pguser
--

INSERT INTO public.users VALUES (36, 'Болдырев Максим Романович', '2003-03-19', '', 'admin', '', 0, 'quixotesoul@gmail.com', '$2y$12$RoV53nS2I8ANMofHTogzBOTaT9.Ddn8aSMg3wFin3DqS5E2jTreuW', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (37, 'Иванов Иван Иванович', '1999-07-20', '', 'teacher', '', 0, 'yaz678@bk.ru', '$2y$12$puvDk6SX4Gdvl01Fn82w0OJKmXqeUNwNHpGdvQ/ykgXcQIvjdhusC', 'uploads/teachers/criminal/criminal_684ee4b0c4587_3_3.jpg', 'approved', '', NULL, 'uploads/teachers/passport/passport_684ee4b0c457d_Приказ КС.pdf', 'uploads/teachers/diploma/diploma_684ee4b0c4586_zwOdxUdaFxY.jpg');
INSERT INTO public.users VALUES (39, 'Albert Robertson', '2003-03-19', '', 'student', '', 0, 'quixotesasasasoul@gmail.com', '$2y$12$lVTJtoZuTSrp16itvp3gKeNt6z9T7HRzRVoytBZh1TpGuh24muZay', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (51, 'Иванов Иван Иванович', '2003-03-19', '', 'student', '', 0, 'test-test@mail.ru', '$2y$12$geoEdtQOXCywIpJCqvoKTOIXRmn9fcKWVJIwfsqzADe93r0idsRva', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (40, 'Albert Robertson', '2003-03-19', '', 'student', '', 0, 'rock@maiaalala.ru', '$2y$12$0AJCp0GrvceR0m1PsMgGHerEfLyBWBSqSZn2tlfBVN/GXB3gIyi5u', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (41, 'Max Boldyrev', '2003-03-19', '', 'student', '', 0, 'maxrox1904@gmail.com', '$2y$12$2IZfa9wLSJCTX/m1wGH2pOKiB6AUrH6SGxoUmJmX9zu62h/thCmrG', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (42, 'Болдырев Максим Романович', '2003-03-19', '', 'student', '', 0, 'yaz678@bkbkbk.ru', '$2y$12$6M734zLG2fhW8KzhRUOiBOHXrhwPZu53uT6wil.Nxd0OliUGcmD8C', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (43, 'Иванов Иван Ивановичя', '2003-03-19', '', 'student', '', 0, 'test@bkbkbkb.ru', '$2y$12$j1qzM3phz5DDQC6VEgxkQ.wSkfRLiHu..I7dQuZknlp0yQaAlZ3q.', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (38, 'Преподавалов Преподаватель Преподавалович', '2003-03-19', '', 'teacher', '', 0, 'quixotesoulasas@gmail.com', '$2y$12$nKzGhv/9I7DFs9DGOmQoTe0d4UyX86UvYpKqw8kbARVV2NM11eIH2', 'uploads/teachers/criminal/criminal_684ee5c14475a_1_1.jpg', 'approved', '', NULL, 'uploads/teachers/passport/passport_684ee5c144752_3_3.jpg', 'uploads/teachers/diploma/diploma_684ee5c144759_2_2.jpg');
INSERT INTO public.users VALUES (44, 'Иванов Иван Ивановичя', '2003-03-19', '', 'teacher', '', 0, 'test-example-mail@mail.ru', '$2y$12$vEVUyLmSwh7rkkPLET0FE.9Z0TZTCwqHotOK7xxczikJNEUQuWrny', 'uploads/teachers/criminal/criminal_6852e142184bc_пользователь.jpg', 'pending', NULL, NULL, 'uploads/teachers/passport/passport_6852e142184b4_физ тесты.jpg', 'uploads/teachers/diploma/diploma_6852e142184bb_вся.jpg');
INSERT INTO public.users VALUES (45, 'Dimi Junior', '2003-03-19', '', 'student', '', 0, 'sdgfdf@bklkkk.ru', '$2y$12$Xo8ClC4rR255XkkAjoMtieJFExN7ORiTVbtF1OTp41nAJFBvfhTo6', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (52, 'Иванов Иван Иванович', '2003-03-12', '', 'student', '', 0, 'AKSMAKSM@BK.RU', '$2y$12$rnpYy3CLtDmcbYsUrt1E1uiieoXTDWN5drS2HBG5yU.9HmPD6FqWK', NULL, 'approved', NULL, NULL, NULL, NULL);
INSERT INTO public.users VALUES (50, 'Болдырев Максим Романович', '2003-03-19', '', 'teacher', '', 0, 'maxrox1903@vk.com', '$2y$12$XJEsX8gZ8W2uprK9RnsGy.lBjy2iaKgrzBH3RAejWJf6GLiFvTdm.', 'uploads/teachers/criminal/criminal_68530715ce1cc_заглушка 3.jpg', 'approved', '', NULL, 'uploads/teachers/passport/passport_68530715ce1c5_заглушка 1.jpg', 'uploads/teachers/diploma/diploma_68530715ce1cb_заглушка 2.jpg');


--
-- TOC entry 3781 (class 0 OID 0)
-- Dependencies: 218
-- Name: answer_options_id_option_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answer_options_id_option_seq', 60, true);


--
-- TOC entry 3782 (class 0 OID 0)
-- Dependencies: 220
-- Name: answers_id_answer_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.answers_id_answer_seq', 1, false);


--
-- TOC entry 3783 (class 0 OID 0)
-- Dependencies: 222
-- Name: certificates_id_certificate_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.certificates_id_certificate_seq', 1, true);


--
-- TOC entry 3784 (class 0 OID 0)
-- Dependencies: 224
-- Name: code_tasks_id_ct_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.code_tasks_id_ct_seq', 17, true);


--
-- TOC entry 3785 (class 0 OID 0)
-- Dependencies: 226
-- Name: course_id_course_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.course_id_course_seq', 44, true);


--
-- TOC entry 3786 (class 0 OID 0)
-- Dependencies: 230
-- Name: course_views_id_view_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.course_views_id_view_seq', 228, true);


--
-- TOC entry 3787 (class 0 OID 0)
-- Dependencies: 233
-- Name: feedback_id_feedback_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.feedback_id_feedback_seq', 18, true);


--
-- TOC entry 3788 (class 0 OID 0)
-- Dependencies: 235
-- Name: lessons_id_lesson_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.lessons_id_lesson_seq', 49, true);


--
-- TOC entry 3789 (class 0 OID 0)
-- Dependencies: 238
-- Name: questions_id_question_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.questions_id_question_seq', 94, true);


--
-- TOC entry 3790 (class 0 OID 0)
-- Dependencies: 240
-- Name: results_id_result_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.results_id_result_seq', 31, true);


--
-- TOC entry 3791 (class 0 OID 0)
-- Dependencies: 242
-- Name: stat_id_stat_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.stat_id_stat_seq', 1, false);


--
-- TOC entry 3792 (class 0 OID 0)
-- Dependencies: 244
-- Name: steps_id_step_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.steps_id_step_seq', 102, true);


--
-- TOC entry 3793 (class 0 OID 0)
-- Dependencies: 248
-- Name: tags_id_tag_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.tags_id_tag_seq', 38, true);


--
-- TOC entry 3794 (class 0 OID 0)
-- Dependencies: 250
-- Name: test_answers_id_answer_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_answers_id_answer_seq', 371, true);


--
-- TOC entry 3795 (class 0 OID 0)
-- Dependencies: 252
-- Name: test_attempts_id_attempt_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_attempts_id_attempt_seq', 147, true);


--
-- TOC entry 3796 (class 0 OID 0)
-- Dependencies: 254
-- Name: test_grade_levels_id_level_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.test_grade_levels_id_level_seq', 12, true);


--
-- TOC entry 3797 (class 0 OID 0)
-- Dependencies: 256
-- Name: tests_id_test_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.tests_id_test_seq', 37, true);


--
-- TOC entry 3798 (class 0 OID 0)
-- Dependencies: 260
-- Name: users_id_user_seq; Type: SEQUENCE SET; Schema: public; Owner: pguser
--

SELECT pg_catalog.setval('public.users_id_user_seq', 52, true);


--
-- TOC entry 3436 (class 2606 OID 57810)
-- Name: certificates certificates_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_pkey PRIMARY KEY (id_certificate);


--
-- TOC entry 3446 (class 2606 OID 57812)
-- Name: course_statistics course_statistics_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_statistics
    ADD CONSTRAINT course_statistics_pkey PRIMARY KEY (id_course);


--
-- TOC entry 3448 (class 2606 OID 57814)
-- Name: course_tags course_tags_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_pkey PRIMARY KEY (id_course, id_tag);


--
-- TOC entry 3450 (class 2606 OID 57816)
-- Name: course_views course_views_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_pkey PRIMARY KEY (id_view);


--
-- TOC entry 3429 (class 2606 OID 57818)
-- Name: answer_options pk_answer_options; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT pk_answer_options PRIMARY KEY (id_option);


--
-- TOC entry 3434 (class 2606 OID 57820)
-- Name: answers pk_answers; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT pk_answers PRIMARY KEY (id_answer);


--
-- TOC entry 3441 (class 2606 OID 57822)
-- Name: code_tasks pk_code_tasks; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT pk_code_tasks PRIMARY KEY (id_ct);


--
-- TOC entry 3444 (class 2606 OID 57824)
-- Name: course pk_course; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course
    ADD CONSTRAINT pk_course PRIMARY KEY (id_course);


--
-- TOC entry 3456 (class 2606 OID 57826)
-- Name: create_passes pk_create_passes; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT pk_create_passes PRIMARY KEY (id_course, id_user);


--
-- TOC entry 3460 (class 2606 OID 57828)
-- Name: feedback pk_feedback; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT pk_feedback PRIMARY KEY (id_feedback);


--
-- TOC entry 3464 (class 2606 OID 57830)
-- Name: lessons pk_lessons; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT pk_lessons PRIMARY KEY (id_lesson);


--
-- TOC entry 3469 (class 2606 OID 57832)
-- Name: material pk_material; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT pk_material PRIMARY KEY (id_material);


--
-- TOC entry 3472 (class 2606 OID 57834)
-- Name: questions pk_questions; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT pk_questions PRIMARY KEY (id_question);


--
-- TOC entry 3476 (class 2606 OID 57836)
-- Name: results pk_results; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT pk_results PRIMARY KEY (id_result);


--
-- TOC entry 3483 (class 2606 OID 57838)
-- Name: stat pk_stat; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT pk_stat PRIMARY KEY (id_stat);


--
-- TOC entry 3487 (class 2606 OID 57840)
-- Name: steps pk_steps; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT pk_steps PRIMARY KEY (id_step);


--
-- TOC entry 3510 (class 2606 OID 57842)
-- Name: tests pk_tests; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT pk_tests PRIMARY KEY (id_test);


--
-- TOC entry 3517 (class 2606 OID 57844)
-- Name: users pk_users; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT pk_users PRIMARY KEY (id_user);


--
-- TOC entry 3490 (class 2606 OID 57846)
-- Name: student_analytics student_analytics_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_pkey PRIMARY KEY (id_user, id_course);


--
-- TOC entry 3492 (class 2606 OID 57848)
-- Name: student_test_settings student_test_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_pkey PRIMARY KEY (id_user, id_test);


--
-- TOC entry 3494 (class 2606 OID 57850)
-- Name: tags tags_name_tag_key; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_name_tag_key UNIQUE (name_tag);


--
-- TOC entry 3496 (class 2606 OID 57852)
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id_tag);


--
-- TOC entry 3499 (class 2606 OID 57854)
-- Name: test_answers test_answers_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_pkey PRIMARY KEY (id_answer);


--
-- TOC entry 3502 (class 2606 OID 57856)
-- Name: test_attempts test_attempts_id_test_id_user_start_time_key; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_test_id_user_start_time_key UNIQUE (id_test, id_user, start_time);


--
-- TOC entry 3504 (class 2606 OID 57858)
-- Name: test_attempts test_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_pkey PRIMARY KEY (id_attempt);


--
-- TOC entry 3507 (class 2606 OID 57860)
-- Name: test_grade_levels test_grade_levels_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels
    ADD CONSTRAINT test_grade_levels_pkey PRIMARY KEY (id_level);


--
-- TOC entry 3513 (class 2606 OID 57862)
-- Name: user_material_progress user_material_progress_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_pkey PRIMARY KEY (id_user, id_step);


--
-- TOC entry 3515 (class 2606 OID 57864)
-- Name: user_tag_interests user_tag_interests_pkey; Type: CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_pkey PRIMARY KEY (id_user, id_tag);


--
-- TOC entry 3485 (class 1259 OID 57865)
-- Name: also_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX also_include_fk ON public.steps USING btree (id_lesson);


--
-- TOC entry 3426 (class 1259 OID 57866)
-- Name: answer_options_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answer_options_pk ON public.answer_options USING btree (id_option);


--
-- TOC entry 3430 (class 1259 OID 57867)
-- Name: answers_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX answers_pk ON public.answers USING btree (id_answer);


--
-- TOC entry 3431 (class 1259 OID 57868)
-- Name: asnwers_to_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX asnwers_to_fk ON public.answers USING btree (id_user);


--
-- TOC entry 3432 (class 1259 OID 57869)
-- Name: assume_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX assume_fk ON public.answers USING btree (id_question);


--
-- TOC entry 3437 (class 1259 OID 57870)
-- Name: code_tasks_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX code_tasks_pk ON public.code_tasks USING btree (id_ct);


--
-- TOC entry 3479 (class 1259 OID 57871)
-- Name: counts_from_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX counts_from_fk ON public.stat USING btree (id_result);


--
-- TOC entry 3442 (class 1259 OID 57872)
-- Name: course_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX course_pk ON public.course USING btree (id_course);


--
-- TOC entry 3451 (class 1259 OID 57873)
-- Name: create_passes2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes2_fk ON public.create_passes USING btree (id_user);


--
-- TOC entry 3452 (class 1259 OID 57874)
-- Name: create_passes_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX create_passes_fk ON public.create_passes USING btree (id_course);


--
-- TOC entry 3453 (class 1259 OID 57875)
-- Name: create_passes_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX create_passes_pk ON public.create_passes USING btree (id_course, id_user);


--
-- TOC entry 3457 (class 1259 OID 57876)
-- Name: feedback_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX feedback_pk ON public.feedback USING btree (id_feedback);


--
-- TOC entry 3480 (class 1259 OID 57877)
-- Name: goes_into_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX goes_into_fk ON public.stat USING btree (id_course);


--
-- TOC entry 3474 (class 1259 OID 57878)
-- Name: goes_to_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX goes_to_fk ON public.results USING btree (id_answer);


--
-- TOC entry 3458 (class 1259 OID 57879)
-- Name: has_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_fk ON public.feedback USING btree (id_course);


--
-- TOC entry 3481 (class 1259 OID 57880)
-- Name: has_in_courses_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX has_in_courses_fk ON public.stat USING btree (id_user);


--
-- TOC entry 3438 (class 1259 OID 57881)
-- Name: idx_code_tasks_language; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_code_tasks_language ON public.code_tasks USING btree (language);


--
-- TOC entry 3454 (class 1259 OID 57882)
-- Name: idx_create_passes_date_complete; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_create_passes_date_complete ON public.create_passes USING btree (date_complete);


--
-- TOC entry 3497 (class 1259 OID 57883)
-- Name: idx_test_answers_attempt; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_answers_attempt ON public.test_answers USING btree (id_attempt);


--
-- TOC entry 3500 (class 1259 OID 57884)
-- Name: idx_test_attempts_complete_time; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_attempts_complete_time ON public.test_attempts USING btree (id_test, id_user, end_time);


--
-- TOC entry 3505 (class 1259 OID 57885)
-- Name: idx_test_grade_levels; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX idx_test_grade_levels ON public.test_grade_levels USING btree (id_test);


--
-- TOC entry 3461 (class 1259 OID 57886)
-- Name: include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX include_fk ON public.lessons USING btree (id_course);


--
-- TOC entry 3462 (class 1259 OID 57887)
-- Name: lessons_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX lessons_pk ON public.lessons USING btree (id_lesson);


--
-- TOC entry 3466 (class 1259 OID 57888)
-- Name: material_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX material_pk ON public.material USING btree (id_material);


--
-- TOC entry 3508 (class 1259 OID 57889)
-- Name: may_also_include2_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_also_include2_fk ON public.tests USING btree (id_step);


--
-- TOC entry 3467 (class 1259 OID 57890)
-- Name: may_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX may_include_fk ON public.material USING btree (id_step);


--
-- TOC entry 3470 (class 1259 OID 57891)
-- Name: mean_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX mean_fk ON public.questions USING btree (id_test);


--
-- TOC entry 3439 (class 1259 OID 57892)
-- Name: might_include_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX might_include_fk ON public.code_tasks USING btree (id_question);


--
-- TOC entry 3427 (class 1259 OID 57893)
-- Name: must_have_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX must_have_fk ON public.answer_options USING btree (id_question);


--
-- TOC entry 3465 (class 1259 OID 57894)
-- Name: procent_pass_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX procent_pass_fk ON public.lessons USING btree (id_stat);


--
-- TOC entry 3473 (class 1259 OID 57895)
-- Name: questions_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX questions_pk ON public.questions USING btree (id_question);


--
-- TOC entry 3477 (class 1259 OID 57896)
-- Name: results_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX results_pk ON public.results USING btree (id_result);


--
-- TOC entry 3484 (class 1259 OID 57897)
-- Name: stat_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX stat_pk ON public.stat USING btree (id_stat);


--
-- TOC entry 3478 (class 1259 OID 57898)
-- Name: stats_in_fk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE INDEX stats_in_fk ON public.results USING btree (id_test);


--
-- TOC entry 3488 (class 1259 OID 57899)
-- Name: steps_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX steps_pk ON public.steps USING btree (id_step);


--
-- TOC entry 3511 (class 1259 OID 57900)
-- Name: tests_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX tests_pk ON public.tests USING btree (id_test);


--
-- TOC entry 3518 (class 1259 OID 57901)
-- Name: users_pk; Type: INDEX; Schema: public; Owner: pguser
--

CREATE UNIQUE INDEX users_pk ON public.users USING btree (id_user);


--
-- TOC entry 3522 (class 2606 OID 57902)
-- Name: certificates certificates_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3523 (class 2606 OID 57907)
-- Name: certificates certificates_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.certificates
    ADD CONSTRAINT certificates_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 3525 (class 2606 OID 57912)
-- Name: course_statistics course_statistics_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_statistics
    ADD CONSTRAINT course_statistics_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- TOC entry 3526 (class 2606 OID 57917)
-- Name: course_tags course_tags_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- TOC entry 3527 (class 2606 OID 57922)
-- Name: course_tags course_tags_id_tag_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_tags
    ADD CONSTRAINT course_tags_id_tag_fkey FOREIGN KEY (id_tag) REFERENCES public.tags(id_tag) ON DELETE CASCADE;


--
-- TOC entry 3528 (class 2606 OID 57927)
-- Name: course_views course_views_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- TOC entry 3529 (class 2606 OID 57932)
-- Name: course_views course_views_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.course_views
    ADD CONSTRAINT course_views_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 3519 (class 2606 OID 57937)
-- Name: answer_options fk_answer_o_must_have_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answer_options
    ADD CONSTRAINT fk_answer_o_must_have_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3520 (class 2606 OID 57942)
-- Name: answers fk_answers_asnwers_t_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT fk_answers_asnwers_t_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3521 (class 2606 OID 57947)
-- Name: answers fk_answers_assume_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT fk_answers_assume_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3524 (class 2606 OID 57952)
-- Name: code_tasks fk_code_tas_might_inc_question; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.code_tasks
    ADD CONSTRAINT fk_code_tas_might_inc_question FOREIGN KEY (id_question) REFERENCES public.questions(id_question) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3530 (class 2606 OID 57957)
-- Name: create_passes fk_create_p_create_pa_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3531 (class 2606 OID 57962)
-- Name: create_passes fk_create_p_create_pa_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.create_passes
    ADD CONSTRAINT fk_create_p_create_pa_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3532 (class 2606 OID 57967)
-- Name: feedback fk_feedback_has_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_has_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3533 (class 2606 OID 57972)
-- Name: feedback fk_feedback_user; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.feedback
    ADD CONSTRAINT fk_feedback_user FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3534 (class 2606 OID 57977)
-- Name: lessons fk_lessons_include_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_include_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3535 (class 2606 OID 57982)
-- Name: lessons fk_lessons_procent_p_stat; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.lessons
    ADD CONSTRAINT fk_lessons_procent_p_stat FOREIGN KEY (id_stat) REFERENCES public.stat(id_stat) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3536 (class 2606 OID 57987)
-- Name: material fk_material_may_inclu_steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.material
    ADD CONSTRAINT fk_material_may_inclu_steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3537 (class 2606 OID 57992)
-- Name: questions fk_question_mean_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT fk_question_mean_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3538 (class 2606 OID 57997)
-- Name: results fk_results_goes_to_answers; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT fk_results_goes_to_answers FOREIGN KEY (id_answer) REFERENCES public.answers(id_answer) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3539 (class 2606 OID 58002)
-- Name: results fk_results_stats_in_tests; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT fk_results_stats_in_tests FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3541 (class 2606 OID 58007)
-- Name: stat fk_stat_counts_fr_results; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_counts_fr_results FOREIGN KEY (id_result) REFERENCES public.results(id_result) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3542 (class 2606 OID 58012)
-- Name: stat fk_stat_goes_into_course; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_goes_into_course FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3543 (class 2606 OID 58017)
-- Name: stat fk_stat_has_in_co_users; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.stat
    ADD CONSTRAINT fk_stat_has_in_co_users FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3544 (class 2606 OID 58022)
-- Name: steps fk_steps_also_incl_lessons; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.steps
    ADD CONSTRAINT fk_steps_also_incl_lessons FOREIGN KEY (id_lesson) REFERENCES public.lessons(id_lesson) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3555 (class 2606 OID 58027)
-- Name: tests fk_tests_may_also__steps; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.tests
    ADD CONSTRAINT fk_tests_may_also__steps FOREIGN KEY (id_step) REFERENCES public.steps(id_step) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 3540 (class 2606 OID 58032)
-- Name: results results_answer_fk; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.results
    ADD CONSTRAINT results_answer_fk FOREIGN KEY (id_answer) REFERENCES public.answers(id_answer) ON DELETE CASCADE;


--
-- TOC entry 3545 (class 2606 OID 58037)
-- Name: student_analytics student_analytics_id_course_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_id_course_fkey FOREIGN KEY (id_course) REFERENCES public.course(id_course) ON DELETE CASCADE;


--
-- TOC entry 3546 (class 2606 OID 58042)
-- Name: student_analytics student_analytics_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_analytics
    ADD CONSTRAINT student_analytics_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 3547 (class 2606 OID 58047)
-- Name: student_test_settings student_test_settings_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON DELETE CASCADE;


--
-- TOC entry 3548 (class 2606 OID 58052)
-- Name: student_test_settings student_test_settings_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.student_test_settings
    ADD CONSTRAINT student_test_settings_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 3549 (class 2606 OID 58057)
-- Name: test_answers test_answers_id_attempt_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_attempt_fkey FOREIGN KEY (id_attempt) REFERENCES public.test_attempts(id_attempt);


--
-- TOC entry 3550 (class 2606 OID 58062)
-- Name: test_answers test_answers_id_question_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_question_fkey FOREIGN KEY (id_question) REFERENCES public.questions(id_question);


--
-- TOC entry 3551 (class 2606 OID 58067)
-- Name: test_answers test_answers_id_selected_option_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_answers
    ADD CONSTRAINT test_answers_id_selected_option_fkey FOREIGN KEY (id_selected_option) REFERENCES public.answer_options(id_option);


--
-- TOC entry 3552 (class 2606 OID 58072)
-- Name: test_attempts test_attempts_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test);


--
-- TOC entry 3553 (class 2606 OID 58077)
-- Name: test_attempts test_attempts_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_attempts
    ADD CONSTRAINT test_attempts_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3554 (class 2606 OID 58082)
-- Name: test_grade_levels test_grade_levels_id_test_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.test_grade_levels
    ADD CONSTRAINT test_grade_levels_id_test_fkey FOREIGN KEY (id_test) REFERENCES public.tests(id_test) ON DELETE CASCADE;


--
-- TOC entry 3556 (class 2606 OID 58087)
-- Name: user_material_progress user_material_progress_id_step_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_id_step_fkey FOREIGN KEY (id_step) REFERENCES public.steps(id_step);


--
-- TOC entry 3557 (class 2606 OID 58092)
-- Name: user_material_progress user_material_progress_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_material_progress
    ADD CONSTRAINT user_material_progress_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user);


--
-- TOC entry 3558 (class 2606 OID 58097)
-- Name: user_tag_interests user_tag_interests_id_tag_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_id_tag_fkey FOREIGN KEY (id_tag) REFERENCES public.tags(id_tag) ON DELETE CASCADE;


--
-- TOC entry 3559 (class 2606 OID 58102)
-- Name: user_tag_interests user_tag_interests_id_user_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pguser
--

ALTER TABLE ONLY public.user_tag_interests
    ADD CONSTRAINT user_tag_interests_id_user_fkey FOREIGN KEY (id_user) REFERENCES public.users(id_user) ON DELETE CASCADE;


--
-- TOC entry 3755 (class 0 OID 0)
-- Dependencies: 5
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pguser
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;


-- Completed on 2025-06-18 22:37:05

--
-- PostgreSQL database dump complete
--

