--
-- PostgreSQL database dump
--


-- Dumped from database version 16.14
-- Dumped by pg_dump version 16.14

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
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
-- Name: accounting_entries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.accounting_entries (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    journal_id bigint NOT NULL,
    period_id bigint NOT NULL,
    entry_number character varying(30),
    reference character varying(100),
    entry_date date NOT NULL,
    description text NOT NULL,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    is_locked boolean DEFAULT false NOT NULL,
    reversal_of_id bigint,
    reversed_by_id bigint,
    validated_by bigint,
    validated_at timestamp(0) without time zone,
    cancelled_by bigint,
    cancelled_at timestamp(0) without time zone,
    attachment_path text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.accounting_entries OWNER TO postgres;

--
-- Name: accounting_entries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.accounting_entries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.accounting_entries_id_seq OWNER TO postgres;

--
-- Name: accounting_entries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.accounting_entries_id_seq OWNED BY public.accounting_entries.id;


--
-- Name: accounting_entry_lines; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.accounting_entry_lines (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    entry_id bigint NOT NULL,
    account_id bigint NOT NULL,
    description text,
    debit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    credit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    third_party_type character varying(30),
    third_party_id bigint,
    lettering_id bigint,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT chk_debit_credit CHECK (((debit >= (0)::numeric) AND (credit >= (0)::numeric) AND ((debit > (0)::numeric) OR (credit > (0)::numeric)) AND (NOT ((debit > (0)::numeric) AND (credit > (0)::numeric)))))
);


ALTER TABLE public.accounting_entry_lines OWNER TO postgres;

--
-- Name: accounting_entry_lines_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.accounting_entry_lines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.accounting_entry_lines_id_seq OWNER TO postgres;

--
-- Name: accounting_entry_lines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.accounting_entry_lines_id_seq OWNED BY public.accounting_entry_lines.id;


--
-- Name: accounts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.accounts (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    chart_account_id bigint NOT NULL,
    parent_id bigint,
    number character varying(20) NOT NULL,
    name character varying(255) NOT NULL,
    class_number smallint NOT NULL,
    type character varying(30) NOT NULL,
    category character varying(50),
    is_active boolean DEFAULT true NOT NULL,
    is_reconcilable boolean DEFAULT false NOT NULL,
    is_auxiliary boolean DEFAULT false NOT NULL,
    is_cash_account boolean DEFAULT false NOT NULL,
    is_bank_account boolean DEFAULT false NOT NULL,
    is_tax_account boolean DEFAULT false NOT NULL,
    default_tax_id bigint,
    opening_balance numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    description text,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.accounts OWNER TO postgres;

--
-- Name: accounts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.accounts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.accounts_id_seq OWNER TO postgres;

--
-- Name: accounts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.accounts_id_seq OWNED BY public.accounts.id;


--
-- Name: activity_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.activity_log (
    id bigint NOT NULL,
    log_name character varying(255),
    description text NOT NULL,
    subject_type character varying(255),
    subject_id bigint,
    causer_type character varying(255),
    causer_id bigint,
    properties json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    event character varying(255),
    batch_uuid uuid
);


ALTER TABLE public.activity_log OWNER TO postgres;

--
-- Name: activity_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.activity_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activity_log_id_seq OWNER TO postgres;

--
-- Name: activity_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.activity_log_id_seq OWNED BY public.activity_log.id;


--
-- Name: asset_depreciations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.asset_depreciations (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    asset_id bigint NOT NULL,
    period character varying(7) NOT NULL,
    depreciation_date date NOT NULL,
    amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    accumulated numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    net_book_value numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    accounting_entry_id bigint,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.asset_depreciations OWNER TO postgres;

--
-- Name: asset_depreciations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.asset_depreciations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.asset_depreciations_id_seq OWNER TO postgres;

--
-- Name: asset_depreciations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.asset_depreciations_id_seq OWNED BY public.asset_depreciations.id;


--
-- Name: assets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.assets (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    code character varying(20) NOT NULL,
    name character varying(255) NOT NULL,
    acquisition_date date NOT NULL,
    acquisition_cost numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    residual_value numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    useful_life_months integer DEFAULT 60 NOT NULL,
    depreciation_method character varying(20) DEFAULT 'linear'::character varying NOT NULL,
    account_asset character varying(20),
    account_depreciation character varying(20),
    account_expense character varying(20),
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.assets OWNER TO postgres;

--
-- Name: assets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.assets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.assets_id_seq OWNER TO postgres;

--
-- Name: assets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.assets_id_seq OWNED BY public.assets.id;


--
-- Name: bank_statement_lines; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bank_statement_lines (
    id bigint NOT NULL,
    bank_statement_id bigint NOT NULL,
    transaction_date date NOT NULL,
    reference character varying(255),
    description character varying(255),
    debit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    credit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    matched_journal_item_id bigint,
    status character varying(20) DEFAULT 'unmatched'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bank_statement_lines OWNER TO postgres;

--
-- Name: bank_statement_lines_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bank_statement_lines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bank_statement_lines_id_seq OWNER TO postgres;

--
-- Name: bank_statement_lines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bank_statement_lines_id_seq OWNED BY public.bank_statement_lines.id;


--
-- Name: bank_statements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bank_statements (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    account_id bigint,
    period_start date NOT NULL,
    period_end date NOT NULL,
    opening_balance numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    closing_balance numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.bank_statements OWNER TO postgres;

--
-- Name: bank_statements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bank_statements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bank_statements_id_seq OWNER TO postgres;

--
-- Name: bank_statements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bank_statements_id_seq OWNED BY public.bank_statements.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: chart_accounts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chart_accounts (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    standard character varying(255) DEFAULT 'SYSCOHADA'::character varying NOT NULL,
    version character varying(255) DEFAULT '2024'::character varying NOT NULL,
    is_default boolean DEFAULT false NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.chart_accounts OWNER TO postgres;

--
-- Name: chart_accounts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.chart_accounts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chart_accounts_id_seq OWNER TO postgres;

--
-- Name: chart_accounts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.chart_accounts_id_seq OWNED BY public.chart_accounts.id;


--
-- Name: clients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.clients (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    code character varying(20) NOT NULL,
    name character varying(255) NOT NULL,
    contact_name character varying(255),
    email character varying(255),
    phone character varying(255),
    address text,
    tax_number character varying(255),
    account_number character varying(20),
    payment_terms character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.clients OWNER TO postgres;

--
-- Name: clients_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.clients_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clients_id_seq OWNER TO postgres;

--
-- Name: clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.clients_id_seq OWNED BY public.clients.id;


--
-- Name: companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.companies (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    short_name character varying(255),
    logo_path character varying(255),
    address text,
    phone character varying(30),
    email character varying(255),
    rccm character varying(100),
    ncc character varying(100),
    tax_id character varying(100),
    social_id character varying(100),
    currency character varying(3) DEFAULT 'XOF'::character varying NOT NULL,
    timezone character varying(255) DEFAULT 'Africa/Abidjan'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    suspended_at timestamp(0) without time zone,
    archived_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    is_blocked boolean DEFAULT false NOT NULL,
    blocked_at timestamp(0) without time zone
);


ALTER TABLE public.companies OWNER TO postgres;

--
-- Name: companies_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.companies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.companies_id_seq OWNER TO postgres;

--
-- Name: companies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.companies_id_seq OWNED BY public.companies.id;


--
-- Name: company_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.company_user (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    user_id bigint NOT NULL,
    role character varying(255) DEFAULT 'employee'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.company_user OWNER TO postgres;

--
-- Name: company_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.company_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.company_user_id_seq OWNER TO postgres;

--
-- Name: company_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.company_user_id_seq OWNED BY public.company_user.id;


--
-- Name: contract_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.contract_types (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(30),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.contract_types OWNER TO postgres;

--
-- Name: contract_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.contract_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.contract_types_id_seq OWNER TO postgres;

--
-- Name: contract_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.contract_types_id_seq OWNED BY public.contract_types.id;


--
-- Name: customer_payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.customer_payments (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    client_id bigint NOT NULL,
    sales_invoice_id bigint,
    reference character varying(255) NOT NULL,
    payment_date date NOT NULL,
    payment_method character varying(20) DEFAULT 'bank'::character varying NOT NULL,
    amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    accounting_entry_id bigint,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.customer_payments OWNER TO postgres;

--
-- Name: customer_payments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.customer_payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.customer_payments_id_seq OWNER TO postgres;

--
-- Name: customer_payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.customer_payments_id_seq OWNED BY public.customer_payments.id;


--
-- Name: departments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.departments (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    code character varying(30) NOT NULL,
    name character varying(255) NOT NULL,
    parent_id bigint,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.departments OWNER TO postgres;

--
-- Name: departments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.departments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.departments_id_seq OWNER TO postgres;

--
-- Name: departments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.departments_id_seq OWNED BY public.departments.id;


--
-- Name: employee_contracts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee_contracts (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    employee_id bigint NOT NULL,
    contract_type_id bigint NOT NULL,
    contract_number character varying(255),
    start_date date NOT NULL,
    end_date date,
    trial_period_end_date date,
    working_hours_per_week numeric(5,2),
    base_salary numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.employee_contracts OWNER TO postgres;

--
-- Name: employee_contracts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.employee_contracts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.employee_contracts_id_seq OWNER TO postgres;

--
-- Name: employee_contracts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.employee_contracts_id_seq OWNED BY public.employee_contracts.id;


--
-- Name: employee_documents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employee_documents (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    employee_id bigint NOT NULL,
    uploaded_by bigint,
    document_type character varying(50) NOT NULL,
    name character varying(255) NOT NULL,
    file_path character varying(255),
    issued_at date,
    expires_at date,
    status character varying(20) DEFAULT 'valid'::character varying NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.employee_documents OWNER TO postgres;

--
-- Name: employee_documents_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.employee_documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.employee_documents_id_seq OWNER TO postgres;

--
-- Name: employee_documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.employee_documents_id_seq OWNED BY public.employee_documents.id;


--
-- Name: employees; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.employees (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    user_id bigint,
    matricule character varying(30) NOT NULL,
    last_name character varying(255) NOT NULL,
    first_name character varying(255) NOT NULL,
    birth_date date,
    birth_place character varying(255),
    sex character varying(10),
    nationality character varying(255),
    id_card_number character varying(255),
    cnps_number character varying(255),
    tax_id character varying(255),
    address text,
    phone character varying(30),
    email character varying(255),
    marital_status character varying(30),
    dependents_count integer DEFAULT 0 NOT NULL,
    hire_date date NOT NULL,
    seniority_date date,
    department_id bigint,
    position_id bigint,
    superior_id bigint,
    professional_category character varying(255),
    collective_agreement character varying(255),
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    exit_date date,
    exit_reason character varying(255),
    bank_name character varying(255),
    bank_account character varying(255),
    mobile_money character varying(255),
    payment_method character varying(30) DEFAULT 'bank'::character varying NOT NULL,
    payment_currency character varying(3) DEFAULT 'XOF'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.employees OWNER TO postgres;

--
-- Name: employees_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.employees_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.employees_id_seq OWNER TO postgres;

--
-- Name: employees_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.employees_id_seq OWNED BY public.employees.id;


--
-- Name: exchange_rates; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.exchange_rates (
    id bigint NOT NULL,
    company_id bigint,
    currency_code character varying(10) NOT NULL,
    currency_name character varying(255),
    rate_to_base numeric(18,6) DEFAULT '1'::numeric NOT NULL,
    effective_from date NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.exchange_rates OWNER TO postgres;

--
-- Name: exchange_rates_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.exchange_rates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.exchange_rates_id_seq OWNER TO postgres;

--
-- Name: exchange_rates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.exchange_rates_id_seq OWNED BY public.exchange_rates.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: fiscal_deadlines; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fiscal_deadlines (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    type character varying(50) NOT NULL,
    name character varying(255) NOT NULL,
    due_date date NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    related_declaration_id bigint,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.fiscal_deadlines OWNER TO postgres;

--
-- Name: fiscal_deadlines_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fiscal_deadlines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fiscal_deadlines_id_seq OWNER TO postgres;

--
-- Name: fiscal_deadlines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fiscal_deadlines_id_seq OWNED BY public.fiscal_deadlines.id;


--
-- Name: fiscal_years; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fiscal_years (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    is_locked boolean DEFAULT false NOT NULL,
    closing_notes text,
    closed_by bigint,
    closed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.fiscal_years OWNER TO postgres;

--
-- Name: fiscal_years_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fiscal_years_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fiscal_years_id_seq OWNER TO postgres;

--
-- Name: fiscal_years_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fiscal_years_id_seq OWNED BY public.fiscal_years.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: journal_entries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.journal_entries (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    journal_id bigint NOT NULL,
    entry_date date NOT NULL,
    reference character varying(255),
    description text,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    source_type character varying(255),
    source_id bigint,
    total_debit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_credit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.journal_entries OWNER TO postgres;

--
-- Name: journal_entries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.journal_entries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.journal_entries_id_seq OWNER TO postgres;

--
-- Name: journal_entries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.journal_entries_id_seq OWNED BY public.journal_entries.id;


--
-- Name: journal_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.journal_items (
    id bigint NOT NULL,
    journal_entry_id bigint NOT NULL,
    account_id bigint NOT NULL,
    debit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    credit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.journal_items OWNER TO postgres;

--
-- Name: journal_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.journal_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.journal_items_id_seq OWNER TO postgres;

--
-- Name: journal_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.journal_items_id_seq OWNED BY public.journal_items.id;


--
-- Name: journals; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.journals (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    code character varying(10) NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(30) NOT NULL,
    default_account_id bigint,
    next_number_pattern character varying(255),
    next_number bigint DEFAULT '1'::bigint NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    requires_attachment boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.journals OWNER TO postgres;

--
-- Name: journals_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.journals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.journals_id_seq OWNER TO postgres;

--
-- Name: journals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.journals_id_seq OWNED BY public.journals.id;


--
-- Name: leaves; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.leaves (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    employee_id bigint NOT NULL,
    leave_type character varying(30) DEFAULT 'annual'::character varying NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    days_count integer DEFAULT 0 NOT NULL,
    reason text,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    approved_by bigint,
    approved_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.leaves OWNER TO postgres;

--
-- Name: leaves_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.leaves_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.leaves_id_seq OWNER TO postgres;

--
-- Name: leaves_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.leaves_id_seq OWNED BY public.leaves.id;


--
-- Name: letterings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.letterings (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    code character varying(20) NOT NULL,
    total_debit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_credit numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    is_balanced boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.letterings OWNER TO postgres;

--
-- Name: letterings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.letterings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.letterings_id_seq OWNER TO postgres;

--
-- Name: letterings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.letterings_id_seq OWNED BY public.letterings.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO postgres;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO postgres;

--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: pay_item_rates; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pay_item_rates (
    id bigint NOT NULL,
    pay_item_id bigint NOT NULL,
    rate numeric(10,4),
    fixed_amount numeric(18,2),
    ceiling numeric(18,2),
    effective_from date NOT NULL,
    effective_until date,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pay_item_rates OWNER TO postgres;

--
-- Name: pay_item_rates_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pay_item_rates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pay_item_rates_id_seq OWNER TO postgres;

--
-- Name: pay_item_rates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pay_item_rates_id_seq OWNED BY public.pay_item_rates.id;


--
-- Name: pay_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pay_items (
    id bigint NOT NULL,
    company_id bigint,
    code character varying(30) NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(30) NOT NULL,
    calculation_method character varying(30) DEFAULT 'fixed'::character varying NOT NULL,
    base_type character varying(30),
    is_taxable boolean DEFAULT false NOT NULL,
    is_subject_to_contributions boolean DEFAULT false NOT NULL,
    is_visible_on_payslip boolean DEFAULT true NOT NULL,
    display_order integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pay_items OWNER TO postgres;

--
-- Name: pay_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pay_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pay_items_id_seq OWNER TO postgres;

--
-- Name: pay_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pay_items_id_seq OWNED BY public.pay_items.id;


--
-- Name: pay_runs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pay_runs (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    reference character varying(50),
    period_start date NOT NULL,
    period_end date NOT NULL,
    payment_date date,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    is_locked boolean DEFAULT false NOT NULL,
    validated_by bigint,
    validated_at timestamp(0) without time zone,
    locked_by bigint,
    locked_at timestamp(0) without time zone,
    accounting_entry_id bigint,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pay_runs OWNER TO postgres;

--
-- Name: pay_runs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pay_runs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.pay_runs_id_seq OWNER TO postgres;

--
-- Name: pay_runs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pay_runs_id_seq OWNED BY public.pay_runs.id;


--
-- Name: payroll_variables; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payroll_variables (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    employee_id bigint NOT NULL,
    pay_run_id bigint,
    pay_item_id bigint NOT NULL,
    amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    quantity numeric(10,2),
    effective_date date NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.payroll_variables OWNER TO postgres;

--
-- Name: payroll_variables_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payroll_variables_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payroll_variables_id_seq OWNER TO postgres;

--
-- Name: payroll_variables_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payroll_variables_id_seq OWNED BY public.payroll_variables.id;


--
-- Name: payslip_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payslip_items (
    id bigint NOT NULL,
    payslip_id bigint NOT NULL,
    pay_item_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(30) NOT NULL,
    base_amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    rate numeric(10,4),
    amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    is_earning boolean DEFAULT true NOT NULL,
    display_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.payslip_items OWNER TO postgres;

--
-- Name: payslip_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payslip_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payslip_items_id_seq OWNER TO postgres;

--
-- Name: payslip_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payslip_items_id_seq OWNED BY public.payslip_items.id;


--
-- Name: payslip_lines; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payslip_lines (
    id bigint NOT NULL,
    payslip_id bigint NOT NULL,
    pay_item_id bigint,
    code character varying(30) NOT NULL,
    label character varying(255) NOT NULL,
    type character varying(30) NOT NULL,
    base_amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    rate numeric(10,4),
    amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    employer_amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    is_visible boolean DEFAULT true NOT NULL,
    display_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.payslip_lines OWNER TO postgres;

--
-- Name: payslip_lines_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payslip_lines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payslip_lines_id_seq OWNER TO postgres;

--
-- Name: payslip_lines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payslip_lines_id_seq OWNED BY public.payslip_lines.id;


--
-- Name: payslips; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payslips (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    pay_run_id bigint NOT NULL,
    employee_id bigint NOT NULL,
    slip_number character varying(50),
    base_salary numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    gross_salary numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_earnings numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_deductions numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_employee_contributions numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_employer_contributions numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    taxable_income numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    income_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    net_salary numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    calculation_snapshot json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    employer_contributions numeric(18,2) DEFAULT '0'::numeric NOT NULL
);


ALTER TABLE public.payslips OWNER TO postgres;

--
-- Name: payslips_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payslips_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payslips_id_seq OWNER TO postgres;

--
-- Name: payslips_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payslips_id_seq OWNED BY public.payslips.id;


--
-- Name: periods; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.periods (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    fiscal_year_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    number smallint NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    status character varying(20) DEFAULT 'open'::character varying NOT NULL,
    is_locked boolean DEFAULT false NOT NULL,
    locked_by bigint,
    locked_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.periods OWNER TO postgres;

--
-- Name: periods_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.periods_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.periods_id_seq OWNER TO postgres;

--
-- Name: periods_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.periods_id_seq OWNED BY public.periods.id;


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: plans; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.plans (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    max_users integer DEFAULT 1 NOT NULL,
    max_employees integer DEFAULT 0 NOT NULL,
    modules json,
    price numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    trial_days integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.plans OWNER TO postgres;

--
-- Name: plans_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.plans_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.plans_id_seq OWNER TO postgres;

--
-- Name: plans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.plans_id_seq OWNED BY public.plans.id;


--
-- Name: positions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.positions (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    code character varying(30) NOT NULL,
    name character varying(255) NOT NULL,
    department_id bigint,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.positions OWNER TO postgres;

--
-- Name: positions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.positions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.positions_id_seq OWNER TO postgres;

--
-- Name: positions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.positions_id_seq OWNED BY public.positions.id;


--
-- Name: purchase_invoice_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchase_invoice_items (
    id bigint NOT NULL,
    purchase_invoice_id bigint NOT NULL,
    account_id bigint,
    description character varying(255) NOT NULL,
    quantity numeric(14,3) DEFAULT '1'::numeric NOT NULL,
    unit_price numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    tax_rate numeric(5,2) DEFAULT '18'::numeric NOT NULL,
    total_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_ttc numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.purchase_invoice_items OWNER TO postgres;

--
-- Name: purchase_invoice_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchase_invoice_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.purchase_invoice_items_id_seq OWNER TO postgres;

--
-- Name: purchase_invoice_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchase_invoice_items_id_seq OWNED BY public.purchase_invoice_items.id;


--
-- Name: purchase_invoices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchase_invoices (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    purchase_order_id bigint,
    reference character varying(255) NOT NULL,
    supplier_invoice_number character varying(255),
    invoice_date date NOT NULL,
    due_date date,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    total_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_ttc numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    amount_paid numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    accounting_entry_id bigint,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.purchase_invoices OWNER TO postgres;

--
-- Name: purchase_invoices_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchase_invoices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.purchase_invoices_id_seq OWNER TO postgres;

--
-- Name: purchase_invoices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchase_invoices_id_seq OWNED BY public.purchase_invoices.id;


--
-- Name: purchase_order_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchase_order_items (
    id bigint NOT NULL,
    purchase_order_id bigint NOT NULL,
    description character varying(255) NOT NULL,
    quantity numeric(14,3) DEFAULT '1'::numeric NOT NULL,
    unit_price numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    tax_rate numeric(5,2) DEFAULT '18'::numeric NOT NULL,
    total_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_ttc numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.purchase_order_items OWNER TO postgres;

--
-- Name: purchase_order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchase_order_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.purchase_order_items_id_seq OWNER TO postgres;

--
-- Name: purchase_order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchase_order_items_id_seq OWNED BY public.purchase_order_items.id;


--
-- Name: purchase_orders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchase_orders (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    reference character varying(255) NOT NULL,
    order_date date NOT NULL,
    expected_date date,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    total_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_ttc numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.purchase_orders OWNER TO postgres;

--
-- Name: purchase_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchase_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.purchase_orders_id_seq OWNER TO postgres;

--
-- Name: purchase_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchase_orders_id_seq OWNED BY public.purchase_orders.id;


--
-- Name: report_exports; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.report_exports (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    user_id bigint,
    report_type character varying(50) NOT NULL,
    format character varying(20) DEFAULT 'csv'::character varying NOT NULL,
    filters json,
    file_path character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.report_exports OWNER TO postgres;

--
-- Name: report_exports_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.report_exports_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.report_exports_id_seq OWNER TO postgres;

--
-- Name: report_exports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.report_exports_id_seq OWNED BY public.report_exports.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sales_invoice_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sales_invoice_items (
    id bigint NOT NULL,
    sales_invoice_id bigint NOT NULL,
    account_id bigint,
    description character varying(255) NOT NULL,
    quantity numeric(14,3) DEFAULT '1'::numeric NOT NULL,
    unit_price numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    tax_rate numeric(5,2) DEFAULT '18'::numeric NOT NULL,
    total_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_ttc numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sales_invoice_items OWNER TO postgres;

--
-- Name: sales_invoice_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sales_invoice_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sales_invoice_items_id_seq OWNER TO postgres;

--
-- Name: sales_invoice_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sales_invoice_items_id_seq OWNED BY public.sales_invoice_items.id;


--
-- Name: sales_invoices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sales_invoices (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    client_id bigint NOT NULL,
    sales_order_id bigint,
    reference character varying(255) NOT NULL,
    invoice_date date NOT NULL,
    due_date date,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    total_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_ttc numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    amount_paid numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    accounting_entry_id bigint,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sales_invoices OWNER TO postgres;

--
-- Name: sales_invoices_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sales_invoices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sales_invoices_id_seq OWNER TO postgres;

--
-- Name: sales_invoices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sales_invoices_id_seq OWNED BY public.sales_invoices.id;


--
-- Name: sales_order_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sales_order_items (
    id bigint NOT NULL,
    sales_order_id bigint NOT NULL,
    description character varying(255) NOT NULL,
    quantity numeric(14,3) DEFAULT '1'::numeric NOT NULL,
    unit_price numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    tax_rate numeric(5,2) DEFAULT '18'::numeric NOT NULL,
    total_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_ttc numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sales_order_items OWNER TO postgres;

--
-- Name: sales_order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sales_order_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sales_order_items_id_seq OWNER TO postgres;

--
-- Name: sales_order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sales_order_items_id_seq OWNED BY public.sales_order_items.id;


--
-- Name: sales_orders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sales_orders (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    client_id bigint NOT NULL,
    reference character varying(255) NOT NULL,
    order_date date NOT NULL,
    validity_date date,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    total_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_ttc numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sales_orders OWNER TO postgres;

--
-- Name: sales_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sales_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sales_orders_id_seq OWNER TO postgres;

--
-- Name: sales_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sales_orders_id_seq OWNED BY public.sales_orders.id;


--
-- Name: sequence_numbers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sequence_numbers (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    code character varying(50) NOT NULL,
    name character varying(255) NOT NULL,
    prefix character varying(20) DEFAULT ''::character varying NOT NULL,
    next_number bigint DEFAULT '1'::bigint NOT NULL,
    format character varying(60) DEFAULT '{prefix}-{year}-{number:04}'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sequence_numbers OWNER TO postgres;

--
-- Name: sequence_numbers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sequence_numbers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sequence_numbers_id_seq OWNER TO postgres;

--
-- Name: sequence_numbers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sequence_numbers_id_seq OWNED BY public.sequence_numbers.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    "group" character varying(50) DEFAULT 'general'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.settings OWNER TO postgres;

--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.settings_id_seq OWNER TO postgres;

--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: social_contribution_rates; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.social_contribution_rates (
    id bigint NOT NULL,
    social_contribution_id bigint NOT NULL,
    employee_rate numeric(8,4) DEFAULT '0'::numeric NOT NULL,
    employer_rate numeric(8,4) DEFAULT '0'::numeric NOT NULL,
    ceiling numeric(18,2),
    effective_from date NOT NULL,
    effective_until date,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.social_contribution_rates OWNER TO postgres;

--
-- Name: social_contribution_rates_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.social_contribution_rates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.social_contribution_rates_id_seq OWNER TO postgres;

--
-- Name: social_contribution_rates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.social_contribution_rates_id_seq OWNED BY public.social_contribution_rates.id;


--
-- Name: social_contributions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.social_contributions (
    id bigint NOT NULL,
    code character varying(30) NOT NULL,
    name character varying(255) NOT NULL,
    organism character varying(50) DEFAULT 'CNPS'::character varying NOT NULL,
    employee_account_code character varying(20),
    employer_account_code character varying(20),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.social_contributions OWNER TO postgres;

--
-- Name: social_contributions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.social_contributions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.social_contributions_id_seq OWNER TO postgres;

--
-- Name: social_contributions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.social_contributions_id_seq OWNED BY public.social_contributions.id;


--
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.subscriptions (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    plan_id bigint NOT NULL,
    status character varying(255) DEFAULT 'trial'::character varying NOT NULL,
    starts_at timestamp(0) without time zone,
    ends_at timestamp(0) without time zone,
    trial_ends_at timestamp(0) without time zone,
    cancelled_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.subscriptions OWNER TO postgres;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.subscriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.subscriptions_id_seq OWNER TO postgres;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.subscriptions_id_seq OWNED BY public.subscriptions.id;


--
-- Name: supplier_payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.supplier_payments (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    purchase_invoice_id bigint,
    reference character varying(255) NOT NULL,
    payment_date date NOT NULL,
    payment_method character varying(20) DEFAULT 'bank'::character varying NOT NULL,
    amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    accounting_entry_id bigint,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.supplier_payments OWNER TO postgres;

--
-- Name: supplier_payments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.supplier_payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.supplier_payments_id_seq OWNER TO postgres;

--
-- Name: supplier_payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.supplier_payments_id_seq OWNED BY public.supplier_payments.id;


--
-- Name: suppliers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.suppliers (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    code character varying(20) NOT NULL,
    name character varying(255) NOT NULL,
    contact_name character varying(255),
    email character varying(255),
    phone character varying(255),
    address text,
    tax_number character varying(255),
    account_number character varying(20),
    payment_terms character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.suppliers OWNER TO postgres;

--
-- Name: suppliers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.suppliers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.suppliers_id_seq OWNER TO postgres;

--
-- Name: suppliers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.suppliers_id_seq OWNED BY public.suppliers.id;


--
-- Name: system_telemetry; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.system_telemetry (
    id bigint NOT NULL,
    install_id character varying(64) NOT NULL,
    payload json NOT NULL,
    recorded_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.system_telemetry OWNER TO postgres;

--
-- Name: system_telemetry_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.system_telemetry_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.system_telemetry_id_seq OWNER TO postgres;

--
-- Name: system_telemetry_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.system_telemetry_id_seq OWNED BY public.system_telemetry.id;


--
-- Name: tax_declarations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tax_declarations (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    type character varying(20) NOT NULL,
    reference character varying(50),
    period character varying(20) NOT NULL,
    due_date date NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    base_amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    tax_amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    penalty_amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    filed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.tax_declarations OWNER TO postgres;

--
-- Name: tax_declarations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tax_declarations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tax_declarations_id_seq OWNER TO postgres;

--
-- Name: tax_declarations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tax_declarations_id_seq OWNED BY public.tax_declarations.id;


--
-- Name: tax_rates; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tax_rates (
    id bigint NOT NULL,
    tax_id bigint NOT NULL,
    rate numeric(8,4) NOT NULL,
    effective_from date NOT NULL,
    effective_until date,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.tax_rates OWNER TO postgres;

--
-- Name: tax_rates_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tax_rates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tax_rates_id_seq OWNER TO postgres;

--
-- Name: tax_rates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tax_rates_id_seq OWNED BY public.tax_rates.id;


--
-- Name: taxes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.taxes (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(20) NOT NULL,
    type character varying(30) NOT NULL,
    scope character varying(20) DEFAULT 'both'::character varying NOT NULL,
    sales_account_id bigint,
    purchase_account_id bigint,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.taxes OWNER TO postgres;

--
-- Name: taxes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.taxes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.taxes_id_seq OWNER TO postgres;

--
-- Name: taxes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.taxes_id_seq OWNED BY public.taxes.id;


--
-- Name: telemetry_events; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.telemetry_events (
    id bigint NOT NULL,
    event_name character varying(100) NOT NULL,
    user_id bigint,
    session_id character varying(64),
    metadata json,
    occurred_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.telemetry_events OWNER TO postgres;

--
-- Name: telemetry_events_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.telemetry_events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.telemetry_events_id_seq OWNER TO postgres;

--
-- Name: telemetry_events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.telemetry_events_id_seq OWNED BY public.telemetry_events.id;


--
-- Name: telemetry_sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.telemetry_sessions (
    id bigint NOT NULL,
    user_id bigint,
    session_id character varying(64) NOT NULL,
    ip_address character varying(45),
    country character varying(2),
    city character varying(100),
    device_type character varying(20),
    browser character varying(50),
    os character varying(50),
    started_at timestamp(0) without time zone NOT NULL,
    ended_at timestamp(0) without time zone,
    duration_seconds integer,
    pages_viewed integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.telemetry_sessions OWNER TO postgres;

--
-- Name: telemetry_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.telemetry_sessions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.telemetry_sessions_id_seq OWNER TO postgres;

--
-- Name: telemetry_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.telemetry_sessions_id_seq OWNED BY public.telemetry_sessions.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    avatar_url character varying(255),
    last_seen_at timestamp(0) without time zone,
    login_count integer DEFAULT 0 NOT NULL,
    first_login_at timestamp(0) without time zone,
    last_login_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vat_declaration_lines; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vat_declaration_lines (
    id bigint NOT NULL,
    vat_declaration_id bigint NOT NULL,
    tax_id bigint,
    type character varying(30) NOT NULL,
    description character varying(255) NOT NULL,
    base_amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    tax_rate numeric(8,4) DEFAULT '0'::numeric NOT NULL,
    tax_amount numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.vat_declaration_lines OWNER TO postgres;

--
-- Name: vat_declaration_lines_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vat_declaration_lines_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vat_declaration_lines_id_seq OWNER TO postgres;

--
-- Name: vat_declaration_lines_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vat_declaration_lines_id_seq OWNED BY public.vat_declaration_lines.id;


--
-- Name: vat_declarations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vat_declarations (
    id bigint NOT NULL,
    company_id bigint NOT NULL,
    period_id bigint NOT NULL,
    reference character varying(50),
    name character varying(255) NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    due_date date,
    total_sales_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_vat_collected numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_purchases_ht numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    total_vat_deductible numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    vat_credit_previous numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    vat_to_pay numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    vat_credit_to_carry numeric(18,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    is_locked boolean DEFAULT false NOT NULL,
    validated_by bigint,
    validated_at timestamp(0) without time zone,
    accounting_entry_id bigint,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.vat_declarations OWNER TO postgres;

--
-- Name: vat_declarations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vat_declarations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vat_declarations_id_seq OWNER TO postgres;

--
-- Name: vat_declarations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vat_declarations_id_seq OWNED BY public.vat_declarations.id;


--
-- Name: accounting_entries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries ALTER COLUMN id SET DEFAULT nextval('public.accounting_entries_id_seq'::regclass);


--
-- Name: accounting_entry_lines id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entry_lines ALTER COLUMN id SET DEFAULT nextval('public.accounting_entry_lines_id_seq'::regclass);


--
-- Name: accounts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounts ALTER COLUMN id SET DEFAULT nextval('public.accounts_id_seq'::regclass);


--
-- Name: activity_log id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log ALTER COLUMN id SET DEFAULT nextval('public.activity_log_id_seq'::regclass);


--
-- Name: asset_depreciations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asset_depreciations ALTER COLUMN id SET DEFAULT nextval('public.asset_depreciations_id_seq'::regclass);


--
-- Name: assets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assets ALTER COLUMN id SET DEFAULT nextval('public.assets_id_seq'::regclass);


--
-- Name: bank_statement_lines id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bank_statement_lines ALTER COLUMN id SET DEFAULT nextval('public.bank_statement_lines_id_seq'::regclass);


--
-- Name: bank_statements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bank_statements ALTER COLUMN id SET DEFAULT nextval('public.bank_statements_id_seq'::regclass);


--
-- Name: chart_accounts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chart_accounts ALTER COLUMN id SET DEFAULT nextval('public.chart_accounts_id_seq'::regclass);


--
-- Name: clients id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients ALTER COLUMN id SET DEFAULT nextval('public.clients_id_seq'::regclass);


--
-- Name: companies id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies ALTER COLUMN id SET DEFAULT nextval('public.companies_id_seq'::regclass);


--
-- Name: company_user id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_user ALTER COLUMN id SET DEFAULT nextval('public.company_user_id_seq'::regclass);


--
-- Name: contract_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contract_types ALTER COLUMN id SET DEFAULT nextval('public.contract_types_id_seq'::regclass);


--
-- Name: customer_payments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customer_payments ALTER COLUMN id SET DEFAULT nextval('public.customer_payments_id_seq'::regclass);


--
-- Name: departments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments ALTER COLUMN id SET DEFAULT nextval('public.departments_id_seq'::regclass);


--
-- Name: employee_contracts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_contracts ALTER COLUMN id SET DEFAULT nextval('public.employee_contracts_id_seq'::regclass);


--
-- Name: employee_documents id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_documents ALTER COLUMN id SET DEFAULT nextval('public.employee_documents_id_seq'::regclass);


--
-- Name: employees id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees ALTER COLUMN id SET DEFAULT nextval('public.employees_id_seq'::regclass);


--
-- Name: exchange_rates id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exchange_rates ALTER COLUMN id SET DEFAULT nextval('public.exchange_rates_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: fiscal_deadlines id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_deadlines ALTER COLUMN id SET DEFAULT nextval('public.fiscal_deadlines_id_seq'::regclass);


--
-- Name: fiscal_years id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_years ALTER COLUMN id SET DEFAULT nextval('public.fiscal_years_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: journal_entries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journal_entries ALTER COLUMN id SET DEFAULT nextval('public.journal_entries_id_seq'::regclass);


--
-- Name: journal_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journal_items ALTER COLUMN id SET DEFAULT nextval('public.journal_items_id_seq'::regclass);


--
-- Name: journals id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journals ALTER COLUMN id SET DEFAULT nextval('public.journals_id_seq'::regclass);


--
-- Name: leaves id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leaves ALTER COLUMN id SET DEFAULT nextval('public.leaves_id_seq'::regclass);


--
-- Name: letterings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.letterings ALTER COLUMN id SET DEFAULT nextval('public.letterings_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: pay_item_rates id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_item_rates ALTER COLUMN id SET DEFAULT nextval('public.pay_item_rates_id_seq'::regclass);


--
-- Name: pay_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_items ALTER COLUMN id SET DEFAULT nextval('public.pay_items_id_seq'::regclass);


--
-- Name: pay_runs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_runs ALTER COLUMN id SET DEFAULT nextval('public.pay_runs_id_seq'::regclass);


--
-- Name: payroll_variables id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payroll_variables ALTER COLUMN id SET DEFAULT nextval('public.payroll_variables_id_seq'::regclass);


--
-- Name: payslip_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslip_items ALTER COLUMN id SET DEFAULT nextval('public.payslip_items_id_seq'::regclass);


--
-- Name: payslip_lines id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslip_lines ALTER COLUMN id SET DEFAULT nextval('public.payslip_lines_id_seq'::regclass);


--
-- Name: payslips id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslips ALTER COLUMN id SET DEFAULT nextval('public.payslips_id_seq'::regclass);


--
-- Name: periods id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.periods ALTER COLUMN id SET DEFAULT nextval('public.periods_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: plans id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plans ALTER COLUMN id SET DEFAULT nextval('public.plans_id_seq'::regclass);


--
-- Name: positions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.positions ALTER COLUMN id SET DEFAULT nextval('public.positions_id_seq'::regclass);


--
-- Name: purchase_invoice_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoice_items ALTER COLUMN id SET DEFAULT nextval('public.purchase_invoice_items_id_seq'::regclass);


--
-- Name: purchase_invoices id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoices ALTER COLUMN id SET DEFAULT nextval('public.purchase_invoices_id_seq'::regclass);


--
-- Name: purchase_order_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_items ALTER COLUMN id SET DEFAULT nextval('public.purchase_order_items_id_seq'::regclass);


--
-- Name: purchase_orders id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders ALTER COLUMN id SET DEFAULT nextval('public.purchase_orders_id_seq'::regclass);


--
-- Name: report_exports id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_exports ALTER COLUMN id SET DEFAULT nextval('public.report_exports_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: sales_invoice_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoice_items ALTER COLUMN id SET DEFAULT nextval('public.sales_invoice_items_id_seq'::regclass);


--
-- Name: sales_invoices id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoices ALTER COLUMN id SET DEFAULT nextval('public.sales_invoices_id_seq'::regclass);


--
-- Name: sales_order_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_order_items ALTER COLUMN id SET DEFAULT nextval('public.sales_order_items_id_seq'::regclass);


--
-- Name: sales_orders id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_orders ALTER COLUMN id SET DEFAULT nextval('public.sales_orders_id_seq'::regclass);


--
-- Name: sequence_numbers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sequence_numbers ALTER COLUMN id SET DEFAULT nextval('public.sequence_numbers_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Name: social_contribution_rates id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_contribution_rates ALTER COLUMN id SET DEFAULT nextval('public.social_contribution_rates_id_seq'::regclass);


--
-- Name: social_contributions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_contributions ALTER COLUMN id SET DEFAULT nextval('public.social_contributions_id_seq'::regclass);


--
-- Name: subscriptions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions ALTER COLUMN id SET DEFAULT nextval('public.subscriptions_id_seq'::regclass);


--
-- Name: supplier_payments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_payments ALTER COLUMN id SET DEFAULT nextval('public.supplier_payments_id_seq'::regclass);


--
-- Name: suppliers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers ALTER COLUMN id SET DEFAULT nextval('public.suppliers_id_seq'::regclass);


--
-- Name: system_telemetry id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_telemetry ALTER COLUMN id SET DEFAULT nextval('public.system_telemetry_id_seq'::regclass);


--
-- Name: tax_declarations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_declarations ALTER COLUMN id SET DEFAULT nextval('public.tax_declarations_id_seq'::regclass);


--
-- Name: tax_rates id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_rates ALTER COLUMN id SET DEFAULT nextval('public.tax_rates_id_seq'::regclass);


--
-- Name: taxes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taxes ALTER COLUMN id SET DEFAULT nextval('public.taxes_id_seq'::regclass);


--
-- Name: telemetry_events id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telemetry_events ALTER COLUMN id SET DEFAULT nextval('public.telemetry_events_id_seq'::regclass);


--
-- Name: telemetry_sessions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telemetry_sessions ALTER COLUMN id SET DEFAULT nextval('public.telemetry_sessions_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vat_declaration_lines id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declaration_lines ALTER COLUMN id SET DEFAULT nextval('public.vat_declaration_lines_id_seq'::regclass);


--
-- Name: vat_declarations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declarations ALTER COLUMN id SET DEFAULT nextval('public.vat_declarations_id_seq'::regclass);


--
-- Data for Name: accounting_entries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.accounting_entries (id, company_id, journal_id, period_id, entry_number, reference, entry_date, description, status, is_locked, reversal_of_id, reversed_by_id, validated_by, validated_at, cancelled_by, cancelled_at, attachment_path, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: accounting_entry_lines; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.accounting_entry_lines (id, company_id, entry_id, account_id, description, debit, credit, third_party_type, third_party_id, lettering_id, sort_order, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: accounts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.accounts (id, company_id, chart_account_id, parent_id, number, name, class_number, type, category, is_active, is_reconcilable, is_auxiliary, is_cash_account, is_bank_account, is_tax_account, default_tax_id, opening_balance, description, sort_order, created_at, updated_at, deleted_at) FROM stdin;
1	7	1	\N	661100	Rémunérations du personnel	6	expense	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:17:00	2026-08-08 16:17:00	\N
2	7	1	\N	664100	Charges sociales patronales	6	expense	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:17:00	2026-08-08 16:17:00	\N
3	7	1	\N	421100	Personnel - Rémunérations dues	4	liability	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:17:00	2026-08-08 16:17:00	\N
4	7	1	\N	433100	CNPS et autres organismes sociaux	4	liability	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:17:01	2026-08-08 16:17:01	\N
5	7	1	\N	442100	État - Impôts sur salaires	4	liability	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:17:01	2026-08-08 16:17:01	\N
6	7	1	\N	411100	Clients	4	asset	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:21:28	2026-08-08 16:21:28	\N
7	7	1	\N	401100	Fournisseurs	4	liability	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:21:28	2026-08-08 16:21:28	\N
8	7	1	\N	701100	Ventes de produits finis	7	revenue	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:21:28	2026-08-08 16:21:28	\N
9	7	1	\N	601100	Achats de matières premières	6	expense	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:21:28	2026-08-08 16:21:28	\N
10	7	1	\N	521100	Banque	5	bank	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:21:28	2026-08-08 16:21:28	\N
11	7	1	\N	443100	TVA Facturée	4	liability	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:21:28	2026-08-08 16:21:28	\N
12	7	1	\N	445100	TVA Déductible	4	asset	\N	t	f	f	f	f	f	\N	0.00	\N	0	2026-08-08 16:21:28	2026-08-08 16:21:28	\N
\.


--
-- Data for Name: activity_log; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.activity_log (id, log_name, description, subject_type, subject_id, causer_type, causer_id, properties, created_at, updated_at, event, batch_uuid) FROM stdin;
4	default	Employé created	App\\Modules\\Hr\\Models\\Employee	3	App\\Models\\User	5	{"attributes":{"matricule":"EMP-2026-0001","last_name":"DIABATE","first_name":"Ibrahim","status":"active","department_id":1,"position_id":1,"hire_date":"2025-01-01T00:00:00.000000Z","exit_date":null}}	2026-08-08 16:01:16	2026-08-08 16:01:16	created	\N
5	default	Création employé	App\\Modules\\Hr\\Models\\Employee	3	App\\Models\\User	5	{"matricule":"EMP-2026-0001"}	2026-08-08 16:01:16	2026-08-08 16:01:16	\N	\N
6	default	created	App\\Modules\\Hr\\Models\\EmployeeContract	1	App\\Models\\User	5	{"attributes":{"contract_number":"CTR-TEST-2026","start_date":"2025-01-01T00:00:00.000000Z","end_date":null,"base_salary":"850000.00","status":"active"}}	2026-08-08 16:01:16	2026-08-08 16:01:16	created	\N
7	default	Ajout contrat employé	App\\Modules\\Hr\\Models\\EmployeeContract	1	App\\Models\\User	5	{"employee_matricule":"EMP-2026-0001"}	2026-08-08 16:01:16	2026-08-08 16:01:16	\N	\N
8	default	Validation période de paie	App\\Modules\\Payroll\\Models\\PayRun	3	App\\Models\\User	5	[]	2026-08-09 04:36:39	2026-08-09 04:36:39	\N	\N
\.


--
-- Data for Name: asset_depreciations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.asset_depreciations (id, company_id, asset_id, period, depreciation_date, amount, accumulated, net_book_value, accounting_entry_id, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: assets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.assets (id, company_id, code, name, acquisition_date, acquisition_cost, residual_value, useful_life_months, depreciation_method, account_asset, account_depreciation, account_expense, status, created_at, updated_at) FROM stdin;
1	7	IMM-001	Véhicule Toyota Hilux	2023-05-10	0.00	0.00	60	linear	\N	\N	\N	in_use	2026-08-09 00:16:02	2026-08-09 00:16:02
2	7	IMM-002	Parc informatique (10 postes)	2024-01-15	0.00	0.00	60	linear	\N	\N	\N	in_use	2026-08-09 00:16:02	2026-08-09 00:16:02
3	7	IMM-003	Mobilier de bureau	2023-08-20	0.00	0.00	60	linear	\N	\N	\N	in_use	2026-08-09 00:16:02	2026-08-09 00:16:02
\.


--
-- Data for Name: bank_statement_lines; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bank_statement_lines (id, bank_statement_id, transaction_date, reference, description, debit, credit, matched_journal_item_id, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: bank_statements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bank_statements (id, company_id, account_id, period_start, period_end, opening_balance, closing_balance, status, created_at, updated_at) FROM stdin;
1	7	10	2026-08-01	2026-08-08	250000015555555.00	0.00	draft	2026-08-08 23:46:43	2026-08-08 23:46:43
2	7	10	2026-08-01	2026-08-08	250000015555555.00	0.00	draft	2026-08-08 23:46:49	2026-08-08 23:46:49
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: chart_accounts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chart_accounts (id, company_id, name, slug, standard, version, is_default, is_active, created_at, updated_at) FROM stdin;
1	7	Plan SYSCOHADA Standard	syscohada-7	SYSCOHADA	2024	t	t	2026-08-08 16:17:00	2026-08-08 16:17:00
\.


--
-- Data for Name: clients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.clients (id, company_id, code, name, contact_name, email, phone, address, tax_number, account_number, payment_terms, is_active, created_at, updated_at) FROM stdin;
1	7	CLI-001	SOCIÉTÉ IVOIRIENNE DE DISTRIBUTION	\N	contact@sid-ci.com	+225 27 20 21 22 23	\N	\N	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
2	7	CLI-002	GROUPE ATLANTIQUE CI	\N	info@groupe-atlantique.0	+225 27 21 30 40 50	\N	\N	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
3	7	CLI-003	PHARMACIE DU PLATEAU	\N	pharma.plateau@gmail.com	+225 27 20 31 41 51	\N	\N	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
4	7	CLI-004	TRANSPORTS BOUAKÉ SARL	\N	transports.bouake@yahoo.fr	+225 25 60 11 22 33	\N	\N	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
17	8	TEST0011	TEST	01245525455	\N	\N	\N	\N	411001	\N	t	2026-08-09 02:54:57	2026-08-09 02:54:57
\.


--
-- Data for Name: companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.companies (id, name, slug, short_name, logo_path, address, phone, email, rccm, ncc, tax_id, social_id, currency, timezone, is_active, suspended_at, archived_at, created_at, updated_at, deleted_at, is_blocked, blocked_at) FROM stdin;
8	FIDUCIA CONSULTING	fiducia-consulting-1786230728	\N	\N	\N	\N	\N	\N	\N	\N	\N	XOF	Africa/Abidjan	t	\N	\N	2026-08-08 23:12:08	2026-08-08 23:12:08	\N	f	\N
7	FIDUCIA AFRICA Conseil & Finance	fiducia-africa	FIDUCIA	images/company-logo.png	\N	\N	\N	\N	\N	\N	\N	XOF	Africa/Abidjan	t	\N	\N	2026-08-08 14:51:45	2026-08-09 01:42:56	\N	f	\N
\.


--
-- Data for Name: company_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.company_user (id, company_id, user_id, role, is_active, created_at, updated_at) FROM stdin;
3	7	5	admin	t	2026-08-08 14:51:45	2026-08-08 23:16:57
4	8	5	admin	t	2026-08-08 23:12:08	2026-08-08 23:16:57
5	7	6	admin	t	2026-08-09 00:16:00	2026-08-09 00:16:00
6	8	6	admin	t	2026-08-09 00:16:00	2026-08-09 00:16:00
7	7	7	admin	t	2026-08-09 00:16:01	2026-08-09 00:16:01
8	8	7	admin	t	2026-08-09 00:16:01	2026-08-09 00:16:01
9	7	8	admin	t	2026-08-09 00:16:01	2026-08-09 00:16:01
10	8	8	admin	t	2026-08-09 00:16:01	2026-08-09 00:16:01
11	8	9	admin	t	2026-08-09 23:51:58	2026-08-09 23:51:58
\.


--
-- Data for Name: contract_types; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.contract_types (id, name, code, is_active, created_at, updated_at) FROM stdin;
1	CDI	CDI	t	2026-08-08 16:01:16	2026-08-08 16:01:16
\.


--
-- Data for Name: customer_payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.customer_payments (id, company_id, client_id, sales_invoice_id, reference, payment_date, payment_method, amount, accounting_entry_id, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: departments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.departments (id, company_id, code, name, parent_id, is_active, created_at, updated_at) FROM stdin;
1	7	TEST	Département Test	\N	t	2026-08-08 16:01:16	2026-08-08 16:01:16
2	7	DG	Direction Générale	\N	t	2026-08-09 00:16:01	2026-08-09 00:16:01
3	7	CF	Comptabilité & Finance	\N	t	2026-08-09 00:16:01	2026-08-09 00:16:01
4	7	RH	Ressources Humaines	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
5	7	CM	Commercial & Marketing	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
6	7	IT	Informatique & Digital	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
7	8	DG	Direction Générale	\N	t	2026-08-09 00:16:03	2026-08-09 00:16:03
8	8	CF	Comptabilité & Finance	\N	t	2026-08-09 00:16:03	2026-08-09 00:16:03
9	8	RH	Ressources Humaines	\N	t	2026-08-09 00:16:03	2026-08-09 00:16:03
10	8	CM	Commercial & Marketing	\N	t	2026-08-09 00:16:03	2026-08-09 00:16:03
11	8	IT	Informatique & Digital	\N	t	2026-08-09 00:16:03	2026-08-09 00:16:03
\.


--
-- Data for Name: employee_contracts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.employee_contracts (id, company_id, employee_id, contract_type_id, contract_number, start_date, end_date, trial_period_end_date, working_hours_per_week, base_salary, status, created_at, updated_at) FROM stdin;
1	7	3	1	CTR-TEST-2026	2025-01-01	\N	\N	\N	850000.00	active	2026-08-08 16:01:16	2026-08-08 16:01:16
\.


--
-- Data for Name: employee_documents; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.employee_documents (id, company_id, employee_id, uploaded_by, document_type, name, file_path, issued_at, expires_at, status, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: employees; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.employees (id, company_id, user_id, matricule, last_name, first_name, birth_date, birth_place, sex, nationality, id_card_number, cnps_number, tax_id, address, phone, email, marital_status, dependents_count, hire_date, seniority_date, department_id, position_id, superior_id, professional_category, collective_agreement, status, exit_date, exit_reason, bank_name, bank_account, mobile_money, payment_method, payment_currency, created_at, updated_at, deleted_at) FROM stdin;
3	7	\N	EMP-2026-0001	DIABATE	Ibrahim	\N	\N	\N	\N	\N	\N	\N	\N	\N	i.diabate+1786204876@test.ci	\N	0	2025-01-01	2025-01-01	1	1	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-08 16:01:16	2026-08-08 16:01:16	\N
4	7	\N	EMP-001	KOUASSI	Jean-Baptiste	\N	\N	\N	\N	\N	\N	\N	\N	+225 07 01 02 03 04	jb.kouassi@fiducia-africa.com	\N	0	2019-03-15	\N	2	2	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:02	2026-08-09 00:16:02	\N
5	7	\N	EMP-002	KONÉ	Awa	\N	\N	\N	\N	\N	\N	\N	\N	+225 07 05 06 07 08	awa.kone@fiducia-africa.com	\N	0	2020-06-01	\N	3	3	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:02	2026-08-09 00:16:02	\N
6	7	\N	EMP-003	TRAORÉ	Moussa	\N	\N	\N	\N	\N	\N	\N	\N	+225 05 09 10 11 12	moussa.traore@fiducia-africa.com	\N	0	2019-09-10	\N	4	5	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:02	2026-08-09 00:16:02	\N
7	7	\N	EMP-004	DIABATÉ	Fatou	\N	\N	\N	\N	\N	\N	\N	\N	+225 05 13 14 15 16	fatou.diabate@fiducia-africa.com	\N	0	2021-02-14	\N	5	6	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:02	2026-08-09 00:16:02	\N
8	7	\N	EMP-005	YAO	N'Guessan	\N	\N	\N	\N	\N	\N	\N	\N	+225 07 17 18 19 20	nguessan.yao@fiducia-africa.com	\N	0	2022-01-05	\N	6	7	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:02	2026-08-09 00:16:02	\N
9	7	\N	EMP-006	BAMBA	Mariam	\N	\N	\N	\N	\N	\N	\N	\N	+225 05 21 22 23 24	mariam.bamba@fiducia-africa.com	\N	0	2022-08-22	\N	2	8	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:02	2026-08-09 00:16:02	\N
10	7	\N	EMP-007	OUATTARA	Ibrahim	\N	\N	\N	\N	\N	\N	\N	\N	+225 07 25 26 27 28	ibrahim.ouattara@fiducia-africa.com	\N	0	2023-04-03	\N	5	6	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:02	2026-08-09 00:16:02	\N
11	7	\N	EMP-008	COULIBALY	Aminata	\N	\N	\N	\N	\N	\N	\N	\N	+225 05 29 30 31 32	aminata.coulibaly@fiducia-africa.com	\N	0	2023-10-16	\N	3	4	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:02	2026-08-09 00:16:02	\N
12	8	\N	EMP-001	KOUASSI	Jean-Baptiste	\N	\N	\N	\N	\N	\N	\N	\N	+225 07 01 02 03 04	jb.kouassi@fiducia-consulting.com	\N	0	2019-03-15	\N	7	9	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:04	2026-08-09 00:16:04	\N
13	8	\N	EMP-002	KONÉ	Awa	\N	\N	\N	\N	\N	\N	\N	\N	+225 07 05 06 07 08	awa.kone@fiducia-consulting.com	\N	0	2020-06-01	\N	8	10	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:04	2026-08-09 00:16:04	\N
14	8	\N	EMP-003	TRAORÉ	Moussa	\N	\N	\N	\N	\N	\N	\N	\N	+225 05 09 10 11 12	moussa.traore@fiducia-consulting.com	\N	0	2019-09-10	\N	9	12	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:04	2026-08-09 00:16:04	\N
15	8	\N	EMP-004	DIABATÉ	Fatou	\N	\N	\N	\N	\N	\N	\N	\N	+225 05 13 14 15 16	fatou.diabate@fiducia-consulting.com	\N	0	2021-02-14	\N	10	13	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:04	2026-08-09 00:16:04	\N
16	8	\N	EMP-005	YAO	N'Guessan	\N	\N	\N	\N	\N	\N	\N	\N	+225 07 17 18 19 20	nguessan.yao@fiducia-consulting.com	\N	0	2022-01-05	\N	11	14	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:04	2026-08-09 00:16:04	\N
17	8	\N	EMP-006	BAMBA	Mariam	\N	\N	\N	\N	\N	\N	\N	\N	+225 05 21 22 23 24	mariam.bamba@fiducia-consulting.com	\N	0	2022-08-22	\N	7	15	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:04	2026-08-09 00:16:04	\N
18	8	\N	EMP-007	OUATTARA	Ibrahim	\N	\N	\N	\N	\N	\N	\N	\N	+225 07 25 26 27 28	ibrahim.ouattara@fiducia-consulting.com	\N	0	2023-04-03	\N	10	13	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:04	2026-08-09 00:16:04	\N
19	8	\N	EMP-008	COULIBALY	Aminata	\N	\N	\N	\N	\N	\N	\N	\N	+225 05 29 30 31 32	aminata.coulibaly@fiducia-consulting.com	\N	0	2023-10-16	\N	8	11	\N	\N	\N	active	\N	\N	\N	\N	\N	bank	XOF	2026-08-09 00:16:04	2026-08-09 00:16:04	\N
\.


--
-- Data for Name: exchange_rates; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.exchange_rates (id, company_id, currency_code, currency_name, rate_to_base, effective_from, is_active, created_at, updated_at) FROM stdin;
1	7	EUR	Euro	655.957000	2026-01-01	t	2026-08-09 00:16:02	2026-08-09 00:16:02
2	7	USD	Dollar US	605.500000	2026-01-01	t	2026-08-09 00:16:03	2026-08-09 00:16:03
3	7	GBP	Livre sterling	765.250000	2026-01-01	t	2026-08-09 00:16:03	2026-08-09 00:16:03
4	7	CNY	Yuan chinois	83.750000	2026-01-01	t	2026-08-09 00:16:03	2026-08-09 00:16:03
5	8	EUR	Euro	655.957000	2026-01-01	t	2026-08-09 00:16:04	2026-08-09 00:16:04
6	8	USD	Dollar US	605.500000	2026-01-01	t	2026-08-09 00:16:04	2026-08-09 00:16:04
7	8	GBP	Livre sterling	765.250000	2026-01-01	t	2026-08-09 00:16:04	2026-08-09 00:16:04
8	8	CNY	Yuan chinois	83.750000	2026-01-01	t	2026-08-09 00:16:04	2026-08-09 00:16:04
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: fiscal_deadlines; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fiscal_deadlines (id, company_id, type, name, due_date, status, related_declaration_id, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: fiscal_years; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fiscal_years (id, company_id, name, start_date, end_date, status, is_locked, closing_notes, closed_by, closed_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: journal_entries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.journal_entries (id, company_id, journal_id, entry_date, reference, description, status, source_type, source_id, total_debit, total_credit, created_at, updated_at) FROM stdin;
1	7	8	2026-08-08	FAC-2026-001	Vente de marchandises	posted	\N	\N	0.00	0.00	2026-08-08 16:23:14	2026-08-08 16:23:14
2	7	9	2026-08-08	FOR-2026-001	Achat de fournitures	posted	\N	\N	0.00	0.00	2026-08-08 16:23:14	2026-08-08 16:23:14
3	7	10	2026-08-08	ENC-2026-001	Règlement client FAC-2026-001	posted	\N	\N	0.00	0.00	2026-08-08 16:23:14	2026-08-08 16:23:14
4	7	8	2026-06-25	VE-2026-001	Facture client SID	posted	\N	\N	0.00	0.00	2026-08-09 00:16:03	2026-08-09 00:16:03
5	7	9	2026-07-02	AC-2026-001	Achat fournitures bureau	posted	\N	\N	0.00	0.00	2026-08-09 00:16:03	2026-08-09 00:16:03
6	7	10	2026-07-20	BQ-2026-001	Encaissement virement client	posted	\N	\N	0.00	0.00	2026-08-09 00:16:03	2026-08-09 00:16:03
7	7	11	2026-07-30	OD-2026-001	Dotation aux amortissements	posted	\N	\N	0.00	0.00	2026-08-09 00:16:03	2026-08-09 00:16:03
8	7	7	2026-08-04	PA-2026-08	Comptabilisation de la paie	posted	\N	\N	0.00	0.00	2026-08-09 00:16:03	2026-08-09 00:16:03
9	8	8	2026-06-25	VE-2026-001	Facture client SID	posted	\N	\N	0.00	0.00	2026-08-09 00:16:04	2026-08-09 00:16:04
10	8	9	2026-07-02	AC-2026-001	Achat fournitures bureau	posted	\N	\N	0.00	0.00	2026-08-09 00:16:04	2026-08-09 00:16:04
11	8	10	2026-07-20	BQ-2026-001	Encaissement virement client	posted	\N	\N	0.00	0.00	2026-08-09 00:16:05	2026-08-09 00:16:05
12	8	11	2026-07-30	OD-2026-001	Dotation aux amortissements	posted	\N	\N	0.00	0.00	2026-08-09 00:16:05	2026-08-09 00:16:05
13	8	7	2026-08-04	PA-2026-08	Comptabilisation de la paie	posted	\N	\N	0.00	0.00	2026-08-09 00:16:05	2026-08-09 00:16:05
\.


--
-- Data for Name: journal_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.journal_items (id, journal_entry_id, account_id, debit, credit, description, created_at, updated_at) FROM stdin;
1	1	6	1180000.00	0.00	\N	2026-08-08 16:23:14	2026-08-08 16:23:14
2	1	8	0.00	1000000.00	\N	2026-08-08 16:23:14	2026-08-08 16:23:14
3	1	11	0.00	180000.00	\N	2026-08-08 16:23:14	2026-08-08 16:23:14
4	2	9	500000.00	0.00	\N	2026-08-08 16:23:14	2026-08-08 16:23:14
5	2	12	90000.00	0.00	\N	2026-08-08 16:23:14	2026-08-08 16:23:14
6	2	7	0.00	590000.00	\N	2026-08-08 16:23:14	2026-08-08 16:23:14
7	3	10	1180000.00	0.00	\N	2026-08-08 16:23:14	2026-08-08 16:23:14
8	3	6	0.00	1180000.00	\N	2026-08-08 16:23:14	2026-08-08 16:23:14
9	4	6	2360000.00	0.00	Créance client SID	2026-08-09 00:16:03	2026-08-09 00:16:03
10	4	8	0.00	2000000.00	Ventes de produits	2026-08-09 00:16:03	2026-08-09 00:16:03
11	4	11	0.00	360000.00	TVA facturée 18%	2026-08-09 00:16:03	2026-08-09 00:16:03
12	5	9	850000.00	0.00	Achats	2026-08-09 00:16:03	2026-08-09 00:16:03
13	5	12	153000.00	0.00	TVA déductible	2026-08-09 00:16:03	2026-08-09 00:16:03
14	5	7	0.00	1003000.00	Dette fournisseur	2026-08-09 00:16:03	2026-08-09 00:16:03
15	6	10	1180000.00	0.00	Virement reçu	2026-08-09 00:16:03	2026-08-09 00:16:03
16	6	6	0.00	1180000.00	Règlement client	2026-08-09 00:16:03	2026-08-09 00:16:03
19	8	1	5000000.00	0.00	Rémunérations	2026-08-09 00:16:03	2026-08-09 00:16:03
20	8	2	775000.00	0.00	Charges patronales	2026-08-09 00:16:03	2026-08-09 00:16:03
21	8	3	0.00	4450000.00	Salaires nets à payer	2026-08-09 00:16:03	2026-08-09 00:16:03
23	8	5	0.00	425000.00	Impôts à reverser	2026-08-09 00:16:03	2026-08-09 00:16:03
24	9	6	2360000.00	0.00	Créance client SID	2026-08-09 00:16:04	2026-08-09 00:16:04
25	9	8	0.00	2000000.00	Ventes de produits	2026-08-09 00:16:04	2026-08-09 00:16:04
26	9	11	0.00	360000.00	TVA facturée 18%	2026-08-09 00:16:04	2026-08-09 00:16:04
27	10	9	850000.00	0.00	Achats	2026-08-09 00:16:04	2026-08-09 00:16:04
28	10	12	153000.00	0.00	TVA déductible	2026-08-09 00:16:05	2026-08-09 00:16:05
29	10	7	0.00	1003000.00	Dette fournisseur	2026-08-09 00:16:05	2026-08-09 00:16:05
30	11	10	1180000.00	0.00	Virement reçu	2026-08-09 00:16:05	2026-08-09 00:16:05
31	11	6	0.00	1180000.00	Règlement client	2026-08-09 00:16:05	2026-08-09 00:16:05
34	13	1	5000000.00	0.00	Rémunérations	2026-08-09 00:16:05	2026-08-09 00:16:05
35	13	2	775000.00	0.00	Charges patronales	2026-08-09 00:16:05	2026-08-09 00:16:05
36	13	3	0.00	4450000.00	Salaires nets à payer	2026-08-09 00:16:05	2026-08-09 00:16:05
38	13	5	0.00	425000.00	Impôts à reverser	2026-08-09 00:16:05	2026-08-09 00:16:05
\.


--
-- Data for Name: journals; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.journals (id, company_id, code, name, type, default_account_id, next_number_pattern, next_number, is_active, requires_attachment, created_at, updated_at) FROM stdin;
7	7	PA	Journal de Paie	payroll	\N	\N	1	t	f	2026-08-08 16:17:01	2026-08-08 16:17:01
8	7	VE	Ventes	sale	\N	\N	1	t	f	2026-08-08 16:21:28	2026-08-08 16:21:28
9	7	AC	Achats	purchase	\N	\N	1	t	f	2026-08-08 16:21:28	2026-08-08 16:21:28
10	7	BQ	Banque	bank	\N	\N	1	t	f	2026-08-08 16:21:28	2026-08-08 16:21:28
11	7	OD	Opérations Diverses	misc	\N	\N	1	t	f	2026-08-09 00:16:01	2026-08-09 00:16:01
12	8	AC	Journal des Achats	purchase	\N	\N	1	t	f	2026-08-09 00:16:03	2026-08-09 00:16:03
13	8	VE	Journal des Ventes	sales	\N	\N	1	t	f	2026-08-09 00:16:03	2026-08-09 00:16:03
14	8	BQ	Journal de Banque	bank	\N	\N	1	t	f	2026-08-09 00:16:03	2026-08-09 00:16:03
15	8	OD	Opérations Diverses	misc	\N	\N	1	t	f	2026-08-09 00:16:03	2026-08-09 00:16:03
16	8	PA	Journal de Paie	payroll	\N	\N	1	t	f	2026-08-09 00:16:03	2026-08-09 00:16:03
17	8	VTES	Ventes	sale	\N	\N	1	t	f	2026-08-09 02:59:07	2026-08-09 02:59:07
\.


--
-- Data for Name: leaves; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.leaves (id, company_id, employee_id, leave_type, start_date, end_date, days_count, reason, status, approved_by, approved_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: letterings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.letterings (id, company_id, code, total_debit, total_credit, is_balanced, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_01_01_000101_create_companies_table	1
5	2026_01_01_000102_create_company_user_table	1
6	2026_01_01_000103_create_plans_table	1
7	2026_01_01_000104_create_subscriptions_table	1
8	2026_01_01_100001_create_fiscal_years_table	1
9	2026_01_01_100002_create_periods_table	1
10	2026_01_01_100003_create_chart_accounts_table	1
11	2026_01_01_100004_create_accounts_table	1
12	2026_01_01_100005_create_journals_table	1
13	2026_01_01_100006_create_taxes_table	1
14	2026_01_01_200001_create_letterings_table	1
15	2026_01_01_200002_create_accounting_entries_table	1
16	2026_01_01_200003_create_accounting_entry_lines_table	1
17	2026_01_01_300001_create_departments_table	1
18	2026_01_01_300002_create_positions_table	1
19	2026_01_01_300003_create_contract_types_table	1
20	2026_01_01_300004_create_employees_table	1
21	2026_01_01_300005_create_employee_contracts_table	1
22	2026_01_01_300006_create_employee_documents_table	1
23	2026_01_01_400001_create_pay_items_table	1
24	2026_01_01_400002_create_pay_item_rates_table	1
25	2026_01_01_400003_create_social_contributions_table	1
26	2026_01_01_400004_create_pay_runs_table	1
27	2026_01_01_400005_create_payslips_table	1
28	2026_01_01_400006_create_payslip_lines_table	1
29	2026_01_01_400007_create_payroll_variables_table	1
30	2026_01_01_400008_make_payslip_lines_pay_item_nullable	1
31	2026_01_01_500001_create_vat_declarations_table	1
32	2026_01_01_500002_create_vat_declaration_lines_table	1
33	2026_01_01_500003_create_fiscal_deadlines_table	1
34	2026_01_01_600001_create_report_exports_table	1
35	2026_08_08_130428_create_permission_tables	1
36	2026_08_08_130431_create_activity_log_table	1
37	2026_08_08_130432_add_event_column_to_activity_log_table	1
38	2026_08_08_130433_add_batch_uuid_column_to_activity_log_table	1
39	2026_01_01_400006_create_payslip_items_table	2
40	2026_01_01_100007_create_journal_entries_table	3
41	2026_01_01_100008_create_journal_items_table	3
42	2026_01_01_600001_create_tax_declarations_table	3
43	2026_01_01_400011_add_missing_columns_to_payslips_table	4
44	2026_01_01_100020_ensure_accounting_crud_columns	5
45	2026_01_01_500001_create_purchasing_tables	6
46	2026_01_01_600001_create_sales_tables	6
47	2026_01_01_700001_create_assets_tables	6
48	2026_01_01_800001_create_treasury_tables	6
49	2026_01_01_900001_create_exchange_rates_table	6
50	2026_01_01_950001_add_avatar_and_company_user_table	7
51	2026_01_01_970001_create_settings_tables	8
52	2026_01_01_999998_create_system_telemetry_table	9
53	2026_01_01_999997_create_telemetry_sessions_table	10
54	2026_01_01_999996_add_block_to_companies	11
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
28	App\\Models\\User	5
37	App\\Models\\User	9
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: pay_item_rates; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pay_item_rates (id, pay_item_id, rate, fixed_amount, ceiling, effective_from, effective_until, is_active, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: pay_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pay_items (id, company_id, code, name, type, calculation_method, base_type, is_taxable, is_subject_to_contributions, is_visible_on_payslip, display_order, is_active, created_at, updated_at) FROM stdin;
1	7	SAL_BASE	Salaire de base	earning	fixed	\N	f	f	t	0	t	2026-08-09 00:16:02	2026-08-09 00:16:02
2	7	PRIME_ANC	Prime d'ancienneté	earning	percentage	\N	f	f	t	0	t	2026-08-09 00:16:02	2026-08-09 00:16:02
3	7	PRIME_TRANS	Prime de transport	earning	fixed	\N	f	f	t	0	t	2026-08-09 00:16:02	2026-08-09 00:16:02
4	7	HS_25	Heures supplémentaires 25%	earning	percentage	\N	f	f	t	0	t	2026-08-09 00:16:02	2026-08-09 00:16:02
\.


--
-- Data for Name: pay_runs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.pay_runs (id, company_id, name, reference, period_start, period_end, payment_date, status, is_locked, validated_by, validated_at, locked_by, locked_at, accounting_entry_id, notes, created_at, updated_at) FROM stdin;
1	7	Paie Août 2026	PAIE-2026-08	2026-08-01	2026-08-31	\N	draft	f	\N	\N	\N	\N	\N	\N	2026-08-08 15:58:58	2026-08-08 15:58:58
2	7	Paie Août 2026	PAIE-2026-08	2026-08-01	2026-08-31	\N	draft	f	\N	\N	\N	\N	\N	\N	2026-08-08 16:01:16	2026-08-08 16:01:16
3	8	Paie August 2026	PAIE-2026-08	2026-08-01	2026-08-31	2026-08-31	validated	f	5	2026-08-09 04:36:39	\N	\N	\N	\N	2026-08-09 00:16:04	2026-08-09 04:36:39
\.


--
-- Data for Name: payroll_variables; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payroll_variables (id, company_id, employee_id, pay_run_id, pay_item_id, amount, quantity, effective_date, description, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: payslip_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payslip_items (id, payslip_id, pay_item_id, name, type, base_amount, rate, amount, is_earning, display_order, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: payslip_lines; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payslip_lines (id, payslip_id, pay_item_id, code, label, type, base_amount, rate, amount, employer_amount, is_visible, display_order, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: payslips; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payslips (id, company_id, pay_run_id, employee_id, slip_number, base_salary, gross_salary, total_earnings, total_deductions, total_employee_contributions, total_employer_contributions, taxable_income, income_tax, net_salary, status, calculation_snapshot, created_at, updated_at, employer_contributions) FROM stdin;
15	8	3	18	BUL-202608-015	500000.00	585000.00	585000.00	52870.00	24863.00	90383.00	560137.00	28007.00	532130.00	calculated	\N	2026-08-09 00:21:48	2026-08-09 11:07:43	90383.00
16	8	3	16	BUL-202608-016	600000.00	690000.00	690000.00	62359.00	29325.00	106605.00	660675.00	33034.00	627641.00	calculated	\N	2026-08-09 00:21:48	2026-08-09 11:07:43	106605.00
17	8	3	17	BUL-202608-017	350000.00	427500.00	427500.00	38636.00	18169.00	66049.00	409331.00	20467.00	388864.00	calculated	\N	2026-08-09 00:21:48	2026-08-09 11:07:43	66049.00
10	8	3	12	BUL-202608-010	1500000.00	1635000.00	1635000.00	130200.00	51000.00	210413.00	1584000.00	79200.00	1504800.00	calculated	\N	2026-08-09 00:21:47	2026-08-09 11:07:43	210413.00
11	8	3	13	BUL-202608-011	650000.00	742500.00	742500.00	67103.00	31556.00	114717.00	710944.00	35547.00	675397.00	calculated	\N	2026-08-09 00:21:47	2026-08-09 11:07:43	114717.00
12	8	3	19	BUL-202608-012	480000.00	564000.00	564000.00	50972.00	23970.00	87138.00	540030.00	27002.00	513028.00	calculated	\N	2026-08-09 00:21:48	2026-08-09 11:07:44	87138.00
13	8	3	14	BUL-202608-013	700000.00	795000.00	795000.00	71849.00	33788.00	122828.00	761212.00	38061.00	723151.00	calculated	\N	2026-08-09 00:21:48	2026-08-09 11:07:44	122828.00
14	8	3	15	BUL-202608-014	550000.00	637500.00	637500.00	57614.00	27094.00	98494.00	610406.00	30520.00	579886.00	calculated	\N	2026-08-09 00:21:48	2026-08-09 11:07:44	98494.00
1	7	1	3	BUL-202608-001	850000.00	952500.00	952500.00	86082.00	40481.00	147162.00	912019.00	45601.00	866418.00	calculated	\N	2026-08-09 00:21:46	2026-08-09 11:07:44	147162.00
2	7	1	4	BUL-202608-002	1500000.00	1635000.00	1635000.00	130200.00	51000.00	210413.00	1584000.00	79200.00	1504800.00	calculated	\N	2026-08-09 00:21:46	2026-08-09 11:07:44	210413.00
3	7	1	5	BUL-202608-003	650000.00	742500.00	742500.00	67103.00	31556.00	114717.00	710944.00	35547.00	675397.00	calculated	\N	2026-08-09 00:21:46	2026-08-09 11:07:45	114717.00
4	7	1	11	BUL-202608-004	480000.00	564000.00	564000.00	50972.00	23970.00	87138.00	540030.00	27002.00	513028.00	calculated	\N	2026-08-09 00:21:46	2026-08-09 11:07:45	87138.00
5	7	1	6	BUL-202608-005	700000.00	795000.00	795000.00	71849.00	33788.00	122828.00	761212.00	38061.00	723151.00	calculated	\N	2026-08-09 00:21:46	2026-08-09 11:07:45	122828.00
6	7	1	7	BUL-202608-006	550000.00	637500.00	637500.00	57614.00	27094.00	98494.00	610406.00	30520.00	579886.00	calculated	\N	2026-08-09 00:21:46	2026-08-09 11:07:45	98494.00
7	7	1	10	BUL-202608-007	500000.00	585000.00	585000.00	52870.00	24863.00	90383.00	560137.00	28007.00	532130.00	calculated	\N	2026-08-09 00:21:46	2026-08-09 11:07:45	90383.00
8	7	1	8	BUL-202608-008	600000.00	690000.00	690000.00	62359.00	29325.00	106605.00	660675.00	33034.00	627641.00	calculated	\N	2026-08-09 00:21:47	2026-08-09 11:07:45	106605.00
9	7	1	9	BUL-202608-009	350000.00	427500.00	427500.00	38636.00	18169.00	66049.00	409331.00	20467.00	388864.00	calculated	\N	2026-08-09 00:21:47	2026-08-09 11:07:45	66049.00
\.


--
-- Data for Name: periods; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.periods (id, company_id, fiscal_year_id, name, number, start_date, end_date, status, is_locked, locked_by, locked_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: plans; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.plans (id, name, slug, max_users, max_employees, modules, price, trial_days, is_active, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: positions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.positions (id, company_id, code, name, department_id, is_active, created_at, updated_at) FROM stdin;
1	7	TEST	Poste Test	1	t	2026-08-08 16:01:16	2026-08-08 16:01:16
2	7	DIR-01	Directeur Général	2	t	2026-08-09 00:16:02	2026-08-09 00:16:02
3	7	CMP-01	Comptable	3	t	2026-08-09 00:16:02	2026-08-09 00:16:02
4	7	AID-01	Aide-comptable	3	t	2026-08-09 00:16:02	2026-08-09 00:16:02
5	7	RH-01	Responsable RH	4	t	2026-08-09 00:16:02	2026-08-09 00:16:02
6	7	COM-01	Commercial	5	t	2026-08-09 00:16:02	2026-08-09 00:16:02
7	7	DEV-01	Développeur	6	t	2026-08-09 00:16:02	2026-08-09 00:16:02
8	7	AST-01	Assistant(e) de direction	2	t	2026-08-09 00:16:02	2026-08-09 00:16:02
9	8	DIR-01	Directeur Général	7	t	2026-08-09 00:16:03	2026-08-09 00:16:03
10	8	CMP-01	Comptable	8	t	2026-08-09 00:16:03	2026-08-09 00:16:03
11	8	AID-01	Aide-comptable	8	t	2026-08-09 00:16:03	2026-08-09 00:16:03
12	8	RH-01	Responsable RH	9	t	2026-08-09 00:16:03	2026-08-09 00:16:03
13	8	COM-01	Commercial	10	t	2026-08-09 00:16:04	2026-08-09 00:16:04
14	8	DEV-01	Développeur	11	t	2026-08-09 00:16:04	2026-08-09 00:16:04
15	8	AST-01	Assistant(e) de direction	7	t	2026-08-09 00:16:04	2026-08-09 00:16:04
\.


--
-- Data for Name: purchase_invoice_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchase_invoice_items (id, purchase_invoice_id, account_id, description, quantity, unit_price, tax_rate, total_ht, total_tax, total_ttc, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: purchase_invoices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchase_invoices (id, company_id, supplier_id, purchase_order_id, reference, supplier_invoice_number, invoice_date, due_date, status, total_ht, total_tax, total_ttc, amount_paid, accounting_entry_id, notes, created_at, updated_at) FROM stdin;
4	7	1	\N	FAF-2026-0001	\N	2026-06-15	2026-07-15	paid	850000.00	0.00	1003000.00	0.00	\N	\N	2026-08-09 00:21:47	2026-08-09 00:21:47
5	7	2	\N	FAF-2026-0002	\N	2026-07-10	2026-08-09	pending	1200000.00	0.00	1416000.00	0.00	\N	\N	2026-08-09 00:21:47	2026-08-09 00:21:47
6	7	3	\N	FAF-2026-0003	\N	2026-07-30	2026-08-29	pending	450000.00	0.00	531000.00	0.00	\N	\N	2026-08-09 00:21:47	2026-08-09 00:21:47
\.


--
-- Data for Name: purchase_order_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchase_order_items (id, purchase_order_id, description, quantity, unit_price, tax_rate, total_ht, total_tax, total_ttc, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: purchase_orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchase_orders (id, company_id, supplier_id, reference, order_date, expected_date, status, total_ht, total_tax, total_ttc, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: report_exports; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.report_exports (id, company_id, user_id, report_type, format, filters, file_path, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
28	super-admin	web	2026-08-08 15:02:37	2026-08-08 15:02:37
29	admin-company	web	2026-08-08 15:02:37	2026-08-08 15:02:37
30	accountant	web	2026-08-08 15:02:37	2026-08-08 15:02:37
31	hr-manager	web	2026-08-08 15:02:37	2026-08-08 15:02:37
32	payroll-manager	web	2026-08-08 15:02:37	2026-08-08 15:02:37
33	tax-manager	web	2026-08-08 15:02:37	2026-08-08 15:02:37
34	auditor	web	2026-08-08 15:02:37	2026-08-08 15:02:37
35	manager	web	2026-08-08 15:02:37	2026-08-08 15:02:37
36	employee	web	2026-08-08 15:02:37	2026-08-08 15:02:37
37	admin	web	2026-08-09 23:51:58	2026-08-09 23:51:58
\.


--
-- Data for Name: sales_invoice_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sales_invoice_items (id, sales_invoice_id, account_id, description, quantity, unit_price, tax_rate, total_ht, total_tax, total_ttc, created_at, updated_at) FROM stdin;
1	11	\N	TEST	10.000	5000.00	18.00	50000.00	9000.00	59000.00	2026-08-09 02:58:46	2026-08-09 02:58:46
\.


--
-- Data for Name: sales_invoices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sales_invoices (id, company_id, client_id, sales_order_id, reference, invoice_date, due_date, status, total_ht, total_tax, total_ttc, amount_paid, accounting_entry_id, notes, created_at, updated_at) FROM stdin;
6	7	1	\N	FAC-2026-0001	2026-05-26	2026-06-25	paid	2500000.00	0.00	2950000.00	0.00	\N	\N	2026-08-09 00:21:47	2026-08-09 00:21:47
7	7	2	\N	FAC-2026-0002	2026-06-10	2026-07-10	paid	1800000.00	0.00	2124000.00	0.00	\N	\N	2026-08-09 00:21:47	2026-08-09 00:21:47
8	7	3	\N	FAC-2026-0003	2026-06-30	2026-07-30	pending	950000.00	0.00	1121000.00	0.00	\N	\N	2026-08-09 00:21:47	2026-08-09 00:21:47
9	7	1	\N	FAC-2026-0004	2026-07-20	2026-08-19	pending	3200000.00	0.00	3776000.00	0.00	\N	\N	2026-08-09 00:21:47	2026-08-09 00:21:47
10	7	4	\N	FAC-2026-0005	2026-08-04	2026-09-03	pending	1450000.00	0.00	1711000.00	0.00	\N	\N	2026-08-09 00:21:47	2026-08-09 00:21:47
11	8	17	\N	FV-2026-0001	2026-08-09	\N	draft	50000.00	9000.00	59000.00	0.00	\N	\N	2026-08-09 02:58:45	2026-08-09 02:58:46
\.


--
-- Data for Name: sales_order_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sales_order_items (id, sales_order_id, description, quantity, unit_price, tax_rate, total_ht, total_tax, total_ttc, created_at, updated_at) FROM stdin;
1	1	TEST	10.000	500.00	18.00	5000.00	900.00	5900.00	2026-08-09 02:58:13	2026-08-09 02:58:13
\.


--
-- Data for Name: sales_orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sales_orders (id, company_id, client_id, reference, order_date, validity_date, status, total_ht, total_tax, total_ttc, notes, created_at, updated_at) FROM stdin;
1	8	17	DEV-2026-0001	2026-08-09	2026-08-09	draft	5000.00	900.00	5900.00	\N	2026-08-09 02:58:13	2026-08-09 02:58:13
\.


--
-- Data for Name: sequence_numbers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sequence_numbers (id, company_id, code, name, prefix, next_number, format, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
7fh3EuahLuCoxA8Ey06GrLPyXEtIsuy23WG49jAQ	5	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0	YTo1OntzOjY6Il90b2tlbiI7czo0MDoiOGRlOFBqSDZwaHppSkpQc1RDcWlMRzJEeENVdVNDUUdsM2daeGo0QyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9wYWllL2J1bGxldGlucy8xNy9hcGVyY3UiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O3M6MTc6ImFjdGl2ZV9jb21wYW55X2lkIjtpOjg7fQ==	1786279028
UMujYARqj3m3ojCl7X9sUN88SIXS4s4eQxPmI5h3	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0	YTo0OntzOjY6Il90b2tlbiI7czo0MDoicjVOMTVaczJnRXZKb0pneFJEbWhFTVBKUDQ3c3Nha0twN1NxdTJpYyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNjoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL3BhaWUvYnVsbGV0aW5zIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1786290150
y6uvTkkVr6xOjnO6sYiWTqDeXUsu5UXMdtQGnfOy	\N	127.0.0.1	curl/8.21.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMEc3WGZyeHpOUGd4dzBWalQzRDkxNUJzN0JxRTFISExEQVJUZGNIMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1786310250
WERhv3TRhFZHNDzsFnTliMOzh6nEQEah9q586nYt	\N	127.0.0.1	curl/8.21.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoiUWFwTEdwWjdRdjNtQThGODJyMnBRdmYxUXJkYTA3RENwOGVTR1d0ciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1786310491
904AHjAmJCItgkbHX4O7pNRaR0GniYbBeyMVHhVk	5	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0	YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRzBmOW1YQmlSUFg5YVpSaFR0aUN5azVpZmVjb0Vyd0ZDUzRNbWYxYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9wYWllL2J1bGxldGlucy8xNC9hcGVyY3UiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O3M6MTc6ImFjdGl2ZV9jb21wYW55X2lkIjtpOjg7fQ==	1786320160
\.


--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id, company_id, key, value, "group", created_at, updated_at) FROM stdin;
2	7	timezone	Africa/Abidjan	general	2026-08-08 23:46:11	2026-08-08 23:46:11
3	7	invoice_payment_days	30	general	2026-08-08 23:46:11	2026-08-08 23:46:11
1	7	language	en	general	2026-08-08 23:46:11	2026-08-09 00:27:28
4	8	language	en	general	2026-08-09 03:09:33	2026-08-09 03:09:33
5	8	timezone	Africa/Abidjan	general	2026-08-09 03:09:33	2026-08-09 03:09:33
6	8	invoice_payment_days	30	general	2026-08-09 03:09:33	2026-08-09 03:09:33
\.


--
-- Data for Name: social_contribution_rates; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.social_contribution_rates (id, social_contribution_id, employee_rate, employer_rate, ceiling, effective_from, effective_until, is_active, created_at, updated_at) FROM stdin;
1	1	4.8000	7.7000	500000.00	2020-01-01	\N	t	2026-08-08 15:58:57	2026-08-08 15:58:57
2	2	0.0000	5.0000	500000.00	2020-01-01	\N	t	2026-08-08 15:58:57	2026-08-08 15:58:57
3	3	0.0000	2.0000	500000.00	2020-01-01	\N	t	2026-08-08 15:58:57	2026-08-08 15:58:57
\.


--
-- Data for Name: social_contributions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.social_contributions (id, code, name, organism, employee_account_code, employer_account_code, is_active, created_at, updated_at) FROM stdin;
1	CNPS_RET	CNPS - Retraite	CNPS	\N	\N	t	2026-08-08 15:58:57	2026-08-08 15:58:57
2	CNPS_PREST	CNPS - Prestations Familiales	CNPS	\N	\N	t	2026-08-08 15:58:57	2026-08-08 15:58:57
3	CNPS_AT	CNPS - Accidents de Travail	CNPS	\N	\N	t	2026-08-08 15:58:57	2026-08-08 15:58:57
4	CNPS_RP	CNPS Retraite Plafonnée	CNPS	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
5	CNPS_PF	Prestations familiales	CNPS	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
6	IR_ITS	Impôt sur les salaires	État	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
\.


--
-- Data for Name: subscriptions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscriptions (id, company_id, plan_id, status, starts_at, ends_at, trial_ends_at, cancelled_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: supplier_payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.supplier_payments (id, company_id, supplier_id, purchase_invoice_id, reference, payment_date, payment_method, amount, accounting_entry_id, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: suppliers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.suppliers (id, company_id, code, name, contact_name, email, phone, address, tax_number, account_number, payment_terms, is_active, created_at, updated_at) FROM stdin;
1	7	SUP-001	FOURNITURES BUREAU PLUS	\N	contact@fbplus.ci	+225 27 22 44 55 66	\N	\N	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
2	7	SUP-002	CFAO TECHNOLOGY CI	\N	ventes@cfao.ci	+225 27 21 77 88 99	\N	\N	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
3	7	SUP-003	IMPRIMERIE VIE NOUVELLE	\N	ivn@imprimerie.ci	+225 27 20 12 34 56	\N	\N	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
4	7	SUP-004	CI ÉNERGIE SERVICES	\N	contact@ci-energie.com	+225 25 23 45 67 89	\N	\N	\N	\N	t	2026-08-09 00:16:02	2026-08-09 00:16:02
\.


--
-- Data for Name: system_telemetry; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.system_telemetry (id, install_id, payload, recorded_at, created_at, updated_at) FROM stdin;
1	5751873eece034f86598c64135081c96b60acf9160fb56186a8ab9545a01407a	{"install_id":"5751873eece034f86598c64135081c96b60acf9160fb56186a8ab9545a01407a","app_name":"FIDUCIA AFRIC","app_url":"http:\\/\\/localhost","version":"1.0.0","php":"8.3.33","laravel":"11.55.0","recorded_at":"2026-08-09T03:55:31+00:00","stats":{"users_total":4,"users_active_30d":0,"users_active_7d":0,"users_active_24h":0,"companies_total":2,"employees_total":17,"clients_total":5,"suppliers_total":4,"invoices_total":9,"payslips_total":17,"journal_entries":13,"sessions_today":0,"events_today":0}}	2026-08-09 03:55:32	2026-08-09 03:55:32	2026-08-09 03:55:32
2	5751873eece034f86598c64135081c96b60acf9160fb56186a8ab9545a01407a	{"install_id":"5751873eece034f86598c64135081c96b60acf9160fb56186a8ab9545a01407a","app_name":"FIDUCIA AFRIC","app_url":"http:\\/\\/localhost","version":"1.0.0","php":"8.3.33","laravel":"11.55.0","recorded_at":"2026-08-09T03:59:10+00:00","stats":{"users_total":4,"users_active_30d":0,"users_active_7d":0,"users_active_24h":0,"companies_total":2,"employees_total":17,"clients_total":5,"suppliers_total":4,"invoices_total":9,"payslips_total":17,"journal_entries":13,"sessions_today":0,"events_today":0}}	2026-08-09 03:59:10	2026-08-09 03:59:10	2026-08-09 03:59:10
\.


--
-- Data for Name: tax_declarations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tax_declarations (id, company_id, type, reference, period, due_date, status, base_amount, tax_amount, penalty_amount, notes, filed_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: tax_rates; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tax_rates (id, tax_id, rate, effective_from, effective_until, is_active, created_at, updated_at) FROM stdin;
1	1	18.0000	2020-01-01	\N	t	2026-08-08 17:42:53	2026-08-08 17:42:53
2	2	4.5000	2020-01-01	\N	t	2026-08-08 17:42:53	2026-08-08 17:42:53
3	3	27.0000	2020-01-01	\N	t	2026-08-08 17:42:53	2026-08-08 17:42:53
\.


--
-- Data for Name: taxes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.taxes (id, company_id, name, code, type, scope, sales_account_id, purchase_account_id, is_active, created_at, updated_at) FROM stdin;
1	7	TVA 18%	TVA_18	vat	both	\N	\N	t	2026-08-08 17:42:53	2026-08-08 17:42:53
2	7	Taxe sur salaires (CFP)	TS	other	both	\N	\N	t	2026-08-08 17:42:53	2026-08-08 17:42:53
3	7	Impôt sur les sociétés	IS	other	both	\N	\N	t	2026-08-08 17:42:53	2026-08-08 17:42:53
\.


--
-- Data for Name: telemetry_events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.telemetry_events (id, event_name, user_id, session_id, metadata, occurred_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: telemetry_sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.telemetry_sessions (id, user_id, session_id, ip_address, country, city, device_type, browser, os, started_at, ended_at, duration_seconds, pages_viewed, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, avatar_url, last_seen_at, login_count, first_login_at, last_login_at) FROM stdin;
5	Administrateur FIDUCIA	admin@fiducia-africa.local	\N	$2y$12$HmREOel2Qd91UC7WemOys..5gSEH1najY7MCakfd0WaLVtiP7PM3m	\N	2026-08-08 14:51:45	2026-08-08 14:51:45	\N	\N	0	\N	\N
6	Awa KONÉ	comptable@fiducia-africa.com	2026-08-09 00:16:00	$2y$12$EbTLKHcACgzYaellMlxcGeZiFwW.YYxqHl8IsiQ5ZMUZ2ZA9aC/0e	\N	2026-08-09 00:16:00	2026-08-09 00:16:00	\N	\N	0	\N	\N
7	Moussa TRAORÉ	rh@fiducia-africa.com	2026-08-09 00:16:01	$2y$12$9SRxhsk8xNHG5nWjb.JxFOLwkqbP89OGm4VI0dt.kRoz1wkjm8Heq	\N	2026-08-09 00:16:01	2026-08-09 00:16:01	\N	\N	0	\N	\N
8	Fatou DIABATÉ	commercial@fiducia-africa.com	2026-08-09 00:16:01	$2y$12$fJ..bpJTkK1gX9g.Abo0oO1ymgzbb73fjdKvP3TGsB28JQRAxskLS	\N	2026-08-09 00:16:01	2026-08-09 00:16:01	\N	\N	0	\N	\N
9	Admin FIDUCIA	nathanaelkouassi55@gmail.com	\N	$2y$12$JS3KAWaR4m5eZlx.40I8aeyI5yMjlyufHn.ShyZhmxhe5GbGS4vHK	\N	2026-08-09 23:51:58	2026-08-09 23:51:58	\N	\N	0	\N	\N
\.


--
-- Data for Name: vat_declaration_lines; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vat_declaration_lines (id, vat_declaration_id, tax_id, type, description, base_amount, tax_rate, tax_amount, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: vat_declarations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vat_declarations (id, company_id, period_id, reference, name, period_start, period_end, due_date, total_sales_ht, total_vat_collected, total_purchases_ht, total_vat_deductible, vat_credit_previous, vat_to_pay, vat_credit_to_carry, status, is_locked, validated_by, validated_at, accounting_entry_id, notes, created_at, updated_at) FROM stdin;
\.


--
-- Name: accounting_entries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.accounting_entries_id_seq', 1, true);


--
-- Name: accounting_entry_lines_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.accounting_entry_lines_id_seq', 1, false);


--
-- Name: accounts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.accounts_id_seq', 46, true);


--
-- Name: activity_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.activity_log_id_seq', 8, true);


--
-- Name: asset_depreciations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.asset_depreciations_id_seq', 1, false);


--
-- Name: assets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.assets_id_seq', 6, true);


--
-- Name: bank_statement_lines_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bank_statement_lines_id_seq', 1, false);


--
-- Name: bank_statements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bank_statements_id_seq', 2, true);


--
-- Name: chart_accounts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.chart_accounts_id_seq', 1, true);


--
-- Name: clients_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.clients_id_seq', 18, true);


--
-- Name: companies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.companies_id_seq', 8, true);


--
-- Name: company_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.company_user_id_seq', 11, true);


--
-- Name: contract_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.contract_types_id_seq', 1, true);


--
-- Name: customer_payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.customer_payments_id_seq', 1, false);


--
-- Name: departments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.departments_id_seq', 11, true);


--
-- Name: employee_contracts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.employee_contracts_id_seq', 17, true);


--
-- Name: employee_documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.employee_documents_id_seq', 1, false);


--
-- Name: employees_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.employees_id_seq', 19, true);


--
-- Name: exchange_rates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.exchange_rates_id_seq', 8, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: fiscal_deadlines_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fiscal_deadlines_id_seq', 6, true);


--
-- Name: fiscal_years_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fiscal_years_id_seq', 6, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: journal_entries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.journal_entries_id_seq', 13, true);


--
-- Name: journal_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.journal_items_id_seq', 38, true);


--
-- Name: journals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.journals_id_seq', 17, true);


--
-- Name: leaves_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.leaves_id_seq', 1, false);


--
-- Name: letterings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.letterings_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 54, true);


--
-- Name: pay_item_rates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pay_item_rates_id_seq', 1, false);


--
-- Name: pay_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pay_items_id_seq', 4, true);


--
-- Name: pay_runs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pay_runs_id_seq', 3, true);


--
-- Name: payroll_variables_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payroll_variables_id_seq', 1, false);


--
-- Name: payslip_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payslip_items_id_seq', 408, true);


--
-- Name: payslip_lines_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payslip_lines_id_seq', 1, false);


--
-- Name: payslips_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payslips_id_seq', 17, true);


--
-- Name: periods_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.periods_id_seq', 6, true);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 1, false);


--
-- Name: plans_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.plans_id_seq', 1, false);


--
-- Name: positions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.positions_id_seq', 15, true);


--
-- Name: purchase_invoice_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchase_invoice_items_id_seq', 1, false);


--
-- Name: purchase_invoices_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchase_invoices_id_seq', 6, true);


--
-- Name: purchase_order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchase_order_items_id_seq', 1, false);


--
-- Name: purchase_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchase_orders_id_seq', 1, false);


--
-- Name: report_exports_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.report_exports_id_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 37, true);


--
-- Name: sales_invoice_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sales_invoice_items_id_seq', 1, true);


--
-- Name: sales_invoices_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sales_invoices_id_seq', 11, true);


--
-- Name: sales_order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sales_order_items_id_seq', 1, true);


--
-- Name: sales_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sales_orders_id_seq', 1, true);


--
-- Name: sequence_numbers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sequence_numbers_id_seq', 1, false);


--
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.settings_id_seq', 6, true);


--
-- Name: social_contribution_rates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.social_contribution_rates_id_seq', 3, true);


--
-- Name: social_contributions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.social_contributions_id_seq', 6, true);


--
-- Name: subscriptions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.subscriptions_id_seq', 1, false);


--
-- Name: supplier_payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.supplier_payments_id_seq', 1, false);


--
-- Name: suppliers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.suppliers_id_seq', 14, true);


--
-- Name: system_telemetry_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.system_telemetry_id_seq', 2, true);


--
-- Name: tax_declarations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tax_declarations_id_seq', 1, false);


--
-- Name: tax_rates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tax_rates_id_seq', 3, true);


--
-- Name: taxes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.taxes_id_seq', 3, true);


--
-- Name: telemetry_events_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telemetry_events_id_seq', 1, false);


--
-- Name: telemetry_sessions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.telemetry_sessions_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 9, true);


--
-- Name: vat_declaration_lines_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vat_declaration_lines_id_seq', 1, false);


--
-- Name: vat_declarations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vat_declarations_id_seq', 4, true);


--
-- Name: accounting_entries accounting_entries_company_id_journal_id_entry_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_company_id_journal_id_entry_number_unique UNIQUE (company_id, journal_id, entry_number);


--
-- Name: accounting_entries accounting_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_pkey PRIMARY KEY (id);


--
-- Name: accounting_entry_lines accounting_entry_lines_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entry_lines
    ADD CONSTRAINT accounting_entry_lines_pkey PRIMARY KEY (id);


--
-- Name: accounts accounts_company_id_chart_account_id_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_company_id_chart_account_id_number_unique UNIQUE (company_id, chart_account_id, number);


--
-- Name: accounts accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_pkey PRIMARY KEY (id);


--
-- Name: activity_log activity_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log
    ADD CONSTRAINT activity_log_pkey PRIMARY KEY (id);


--
-- Name: asset_depreciations asset_depreciations_asset_id_period_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asset_depreciations
    ADD CONSTRAINT asset_depreciations_asset_id_period_unique UNIQUE (asset_id, period);


--
-- Name: asset_depreciations asset_depreciations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asset_depreciations
    ADD CONSTRAINT asset_depreciations_pkey PRIMARY KEY (id);


--
-- Name: assets assets_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_code_unique UNIQUE (code);


--
-- Name: assets assets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_pkey PRIMARY KEY (id);


--
-- Name: bank_statement_lines bank_statement_lines_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bank_statement_lines
    ADD CONSTRAINT bank_statement_lines_pkey PRIMARY KEY (id);


--
-- Name: bank_statements bank_statements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bank_statements
    ADD CONSTRAINT bank_statements_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: chart_accounts chart_accounts_company_id_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chart_accounts
    ADD CONSTRAINT chart_accounts_company_id_slug_unique UNIQUE (company_id, slug);


--
-- Name: chart_accounts chart_accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chart_accounts
    ADD CONSTRAINT chart_accounts_pkey PRIMARY KEY (id);


--
-- Name: clients clients_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_code_unique UNIQUE (code);


--
-- Name: clients clients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_pkey PRIMARY KEY (id);


--
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- Name: companies companies_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_slug_unique UNIQUE (slug);


--
-- Name: company_user company_user_company_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_user
    ADD CONSTRAINT company_user_company_id_user_id_unique UNIQUE (company_id, user_id);


--
-- Name: company_user company_user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_user
    ADD CONSTRAINT company_user_pkey PRIMARY KEY (id);


--
-- Name: contract_types contract_types_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contract_types
    ADD CONSTRAINT contract_types_name_unique UNIQUE (name);


--
-- Name: contract_types contract_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contract_types
    ADD CONSTRAINT contract_types_pkey PRIMARY KEY (id);


--
-- Name: customer_payments customer_payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customer_payments
    ADD CONSTRAINT customer_payments_pkey PRIMARY KEY (id);


--
-- Name: customer_payments customer_payments_reference_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customer_payments
    ADD CONSTRAINT customer_payments_reference_unique UNIQUE (reference);


--
-- Name: departments departments_company_id_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_company_id_code_unique UNIQUE (company_id, code);


--
-- Name: departments departments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_pkey PRIMARY KEY (id);


--
-- Name: employee_contracts employee_contracts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_contracts
    ADD CONSTRAINT employee_contracts_pkey PRIMARY KEY (id);


--
-- Name: employee_documents employee_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_documents
    ADD CONSTRAINT employee_documents_pkey PRIMARY KEY (id);


--
-- Name: employees employees_company_id_matricule_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_company_id_matricule_unique UNIQUE (company_id, matricule);


--
-- Name: employees employees_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_pkey PRIMARY KEY (id);


--
-- Name: exchange_rates exchange_rates_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exchange_rates
    ADD CONSTRAINT exchange_rates_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: fiscal_deadlines fiscal_deadlines_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_deadlines
    ADD CONSTRAINT fiscal_deadlines_pkey PRIMARY KEY (id);


--
-- Name: fiscal_years fiscal_years_company_id_end_date_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_years
    ADD CONSTRAINT fiscal_years_company_id_end_date_unique UNIQUE (company_id, end_date);


--
-- Name: fiscal_years fiscal_years_company_id_start_date_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_years
    ADD CONSTRAINT fiscal_years_company_id_start_date_unique UNIQUE (company_id, start_date);


--
-- Name: fiscal_years fiscal_years_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_years
    ADD CONSTRAINT fiscal_years_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: journal_entries journal_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journal_entries
    ADD CONSTRAINT journal_entries_pkey PRIMARY KEY (id);


--
-- Name: journal_items journal_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journal_items
    ADD CONSTRAINT journal_items_pkey PRIMARY KEY (id);


--
-- Name: journals journals_company_id_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journals
    ADD CONSTRAINT journals_company_id_code_unique UNIQUE (company_id, code);


--
-- Name: journals journals_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journals
    ADD CONSTRAINT journals_pkey PRIMARY KEY (id);


--
-- Name: leaves leaves_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leaves
    ADD CONSTRAINT leaves_pkey PRIMARY KEY (id);


--
-- Name: letterings letterings_company_id_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.letterings
    ADD CONSTRAINT letterings_company_id_code_unique UNIQUE (company_id, code);


--
-- Name: letterings letterings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.letterings
    ADD CONSTRAINT letterings_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: pay_item_rates pay_item_rates_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_item_rates
    ADD CONSTRAINT pay_item_rates_pkey PRIMARY KEY (id);


--
-- Name: pay_items pay_items_company_id_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_items
    ADD CONSTRAINT pay_items_company_id_code_unique UNIQUE (company_id, code);


--
-- Name: pay_items pay_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_items
    ADD CONSTRAINT pay_items_pkey PRIMARY KEY (id);


--
-- Name: pay_runs pay_runs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_runs
    ADD CONSTRAINT pay_runs_pkey PRIMARY KEY (id);


--
-- Name: payroll_variables payroll_variables_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payroll_variables
    ADD CONSTRAINT payroll_variables_pkey PRIMARY KEY (id);


--
-- Name: payslip_items payslip_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslip_items
    ADD CONSTRAINT payslip_items_pkey PRIMARY KEY (id);


--
-- Name: payslip_lines payslip_lines_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslip_lines
    ADD CONSTRAINT payslip_lines_pkey PRIMARY KEY (id);


--
-- Name: payslips payslips_pay_run_id_employee_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslips
    ADD CONSTRAINT payslips_pay_run_id_employee_id_unique UNIQUE (pay_run_id, employee_id);


--
-- Name: payslips payslips_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslips
    ADD CONSTRAINT payslips_pkey PRIMARY KEY (id);


--
-- Name: periods periods_fiscal_year_id_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.periods
    ADD CONSTRAINT periods_fiscal_year_id_number_unique UNIQUE (fiscal_year_id, number);


--
-- Name: periods periods_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.periods
    ADD CONSTRAINT periods_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: plans plans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plans
    ADD CONSTRAINT plans_pkey PRIMARY KEY (id);


--
-- Name: plans plans_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plans
    ADD CONSTRAINT plans_slug_unique UNIQUE (slug);


--
-- Name: positions positions_company_id_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.positions
    ADD CONSTRAINT positions_company_id_code_unique UNIQUE (company_id, code);


--
-- Name: positions positions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.positions
    ADD CONSTRAINT positions_pkey PRIMARY KEY (id);


--
-- Name: purchase_invoice_items purchase_invoice_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoice_items
    ADD CONSTRAINT purchase_invoice_items_pkey PRIMARY KEY (id);


--
-- Name: purchase_invoices purchase_invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoices
    ADD CONSTRAINT purchase_invoices_pkey PRIMARY KEY (id);


--
-- Name: purchase_invoices purchase_invoices_reference_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoices
    ADD CONSTRAINT purchase_invoices_reference_unique UNIQUE (reference);


--
-- Name: purchase_order_items purchase_order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_items
    ADD CONSTRAINT purchase_order_items_pkey PRIMARY KEY (id);


--
-- Name: purchase_orders purchase_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_pkey PRIMARY KEY (id);


--
-- Name: purchase_orders purchase_orders_reference_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_reference_unique UNIQUE (reference);


--
-- Name: report_exports report_exports_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_exports
    ADD CONSTRAINT report_exports_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sales_invoice_items sales_invoice_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoice_items
    ADD CONSTRAINT sales_invoice_items_pkey PRIMARY KEY (id);


--
-- Name: sales_invoices sales_invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoices
    ADD CONSTRAINT sales_invoices_pkey PRIMARY KEY (id);


--
-- Name: sales_invoices sales_invoices_reference_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoices
    ADD CONSTRAINT sales_invoices_reference_unique UNIQUE (reference);


--
-- Name: sales_order_items sales_order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_order_items
    ADD CONSTRAINT sales_order_items_pkey PRIMARY KEY (id);


--
-- Name: sales_orders sales_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT sales_orders_pkey PRIMARY KEY (id);


--
-- Name: sales_orders sales_orders_reference_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT sales_orders_reference_unique UNIQUE (reference);


--
-- Name: sequence_numbers sequence_numbers_company_id_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sequence_numbers
    ADD CONSTRAINT sequence_numbers_company_id_code_unique UNIQUE (company_id, code);


--
-- Name: sequence_numbers sequence_numbers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sequence_numbers
    ADD CONSTRAINT sequence_numbers_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: settings settings_company_id_key_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_company_id_key_unique UNIQUE (company_id, key);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: social_contribution_rates social_contribution_rates_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_contribution_rates
    ADD CONSTRAINT social_contribution_rates_pkey PRIMARY KEY (id);


--
-- Name: social_contributions social_contributions_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_contributions
    ADD CONSTRAINT social_contributions_code_unique UNIQUE (code);


--
-- Name: social_contributions social_contributions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_contributions
    ADD CONSTRAINT social_contributions_pkey PRIMARY KEY (id);


--
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- Name: supplier_payments supplier_payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_payments
    ADD CONSTRAINT supplier_payments_pkey PRIMARY KEY (id);


--
-- Name: supplier_payments supplier_payments_reference_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_payments
    ADD CONSTRAINT supplier_payments_reference_unique UNIQUE (reference);


--
-- Name: suppliers suppliers_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_code_unique UNIQUE (code);


--
-- Name: suppliers suppliers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_pkey PRIMARY KEY (id);


--
-- Name: system_telemetry system_telemetry_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_telemetry
    ADD CONSTRAINT system_telemetry_pkey PRIMARY KEY (id);


--
-- Name: tax_declarations tax_declarations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_declarations
    ADD CONSTRAINT tax_declarations_pkey PRIMARY KEY (id);


--
-- Name: tax_rates tax_rates_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_rates
    ADD CONSTRAINT tax_rates_pkey PRIMARY KEY (id);


--
-- Name: taxes taxes_company_id_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taxes
    ADD CONSTRAINT taxes_company_id_code_unique UNIQUE (company_id, code);


--
-- Name: taxes taxes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taxes
    ADD CONSTRAINT taxes_pkey PRIMARY KEY (id);


--
-- Name: telemetry_events telemetry_events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telemetry_events
    ADD CONSTRAINT telemetry_events_pkey PRIMARY KEY (id);


--
-- Name: telemetry_sessions telemetry_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telemetry_sessions
    ADD CONSTRAINT telemetry_sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vat_declaration_lines vat_declaration_lines_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declaration_lines
    ADD CONSTRAINT vat_declaration_lines_pkey PRIMARY KEY (id);


--
-- Name: vat_declarations vat_declarations_company_id_period_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declarations
    ADD CONSTRAINT vat_declarations_company_id_period_id_unique UNIQUE (company_id, period_id);


--
-- Name: vat_declarations vat_declarations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declarations
    ADD CONSTRAINT vat_declarations_pkey PRIMARY KEY (id);


--
-- Name: accounting_entries_company_id_entry_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounting_entries_company_id_entry_date_index ON public.accounting_entries USING btree (company_id, entry_date);


--
-- Name: accounting_entries_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounting_entries_company_id_status_index ON public.accounting_entries USING btree (company_id, status);


--
-- Name: accounting_entries_period_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounting_entries_period_id_index ON public.accounting_entries USING btree (period_id);


--
-- Name: accounting_entry_lines_company_id_account_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounting_entry_lines_company_id_account_id_index ON public.accounting_entry_lines USING btree (company_id, account_id);


--
-- Name: accounting_entry_lines_company_id_entry_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounting_entry_lines_company_id_entry_id_index ON public.accounting_entry_lines USING btree (company_id, entry_id);


--
-- Name: accounting_entry_lines_lettering_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounting_entry_lines_lettering_id_index ON public.accounting_entry_lines USING btree (lettering_id);


--
-- Name: accounting_entry_lines_third_party_type_third_party_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounting_entry_lines_third_party_type_third_party_id_index ON public.accounting_entry_lines USING btree (third_party_type, third_party_id);


--
-- Name: accounts_company_id_class_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounts_company_id_class_number_index ON public.accounts USING btree (company_id, class_number);


--
-- Name: accounts_company_id_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounts_company_id_type_index ON public.accounts USING btree (company_id, type);


--
-- Name: accounts_parent_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX accounts_parent_id_index ON public.accounts USING btree (parent_id);


--
-- Name: activity_log_log_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX activity_log_log_name_index ON public.activity_log USING btree (log_name);


--
-- Name: causer; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX causer ON public.activity_log USING btree (causer_type, causer_id);


--
-- Name: chart_accounts_company_id_is_default_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX chart_accounts_company_id_is_default_index ON public.chart_accounts USING btree (company_id, is_default);


--
-- Name: companies_is_blocked_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX companies_is_blocked_index ON public.companies USING btree (is_blocked);


--
-- Name: departments_company_id_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX departments_company_id_is_active_index ON public.departments USING btree (company_id, is_active);


--
-- Name: employee_contracts_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_contracts_company_id_status_index ON public.employee_contracts USING btree (company_id, status);


--
-- Name: employee_contracts_employee_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_contracts_employee_id_status_index ON public.employee_contracts USING btree (employee_id, status);


--
-- Name: employee_documents_company_id_document_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_documents_company_id_document_type_index ON public.employee_documents USING btree (company_id, document_type);


--
-- Name: employee_documents_employee_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employee_documents_employee_id_status_index ON public.employee_documents USING btree (employee_id, status);


--
-- Name: employees_company_id_department_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employees_company_id_department_id_index ON public.employees USING btree (company_id, department_id);


--
-- Name: employees_company_id_position_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employees_company_id_position_id_index ON public.employees USING btree (company_id, position_id);


--
-- Name: employees_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX employees_company_id_status_index ON public.employees USING btree (company_id, status);


--
-- Name: fiscal_deadlines_company_id_due_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fiscal_deadlines_company_id_due_date_index ON public.fiscal_deadlines USING btree (company_id, due_date);


--
-- Name: fiscal_deadlines_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fiscal_deadlines_company_id_status_index ON public.fiscal_deadlines USING btree (company_id, status);


--
-- Name: fiscal_years_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX fiscal_years_company_id_status_index ON public.fiscal_years USING btree (company_id, status);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: journal_entries_company_id_entry_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX journal_entries_company_id_entry_date_index ON public.journal_entries USING btree (company_id, entry_date);


--
-- Name: journal_entries_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX journal_entries_company_id_status_index ON public.journal_entries USING btree (company_id, status);


--
-- Name: journal_entries_source_type_source_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX journal_entries_source_type_source_id_index ON public.journal_entries USING btree (source_type, source_id);


--
-- Name: journal_items_account_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX journal_items_account_id_index ON public.journal_items USING btree (account_id);


--
-- Name: journal_items_journal_entry_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX journal_items_journal_entry_id_index ON public.journal_items USING btree (journal_entry_id);


--
-- Name: journals_company_id_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX journals_company_id_type_index ON public.journals USING btree (company_id, type);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: pay_item_rates_pay_item_id_effective_from_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX pay_item_rates_pay_item_id_effective_from_index ON public.pay_item_rates USING btree (pay_item_id, effective_from);


--
-- Name: pay_items_type_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX pay_items_type_is_active_index ON public.pay_items USING btree (type, is_active);


--
-- Name: pay_runs_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX pay_runs_company_id_status_index ON public.pay_runs USING btree (company_id, status);


--
-- Name: pay_runs_period_start_period_end_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX pay_runs_period_start_period_end_index ON public.pay_runs USING btree (period_start, period_end);


--
-- Name: payroll_variables_employee_id_effective_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX payroll_variables_employee_id_effective_date_index ON public.payroll_variables USING btree (employee_id, effective_date);


--
-- Name: payslip_items_payslip_id_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX payslip_items_payslip_id_type_index ON public.payslip_items USING btree (payslip_id, type);


--
-- Name: payslip_lines_payslip_id_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX payslip_lines_payslip_id_type_index ON public.payslip_lines USING btree (payslip_id, type);


--
-- Name: payslips_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX payslips_company_id_status_index ON public.payslips USING btree (company_id, status);


--
-- Name: periods_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX periods_company_id_status_index ON public.periods USING btree (company_id, status);


--
-- Name: periods_start_date_end_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX periods_start_date_end_date_index ON public.periods USING btree (start_date, end_date);


--
-- Name: positions_company_id_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX positions_company_id_is_active_index ON public.positions USING btree (company_id, is_active);


--
-- Name: report_exports_company_id_report_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX report_exports_company_id_report_type_index ON public.report_exports USING btree (company_id, report_type);


--
-- Name: report_exports_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX report_exports_user_id_index ON public.report_exports USING btree (user_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: social_contribution_rates_social_contribution_id_effective_from; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX social_contribution_rates_social_contribution_id_effective_from ON public.social_contribution_rates USING btree (social_contribution_id, effective_from);


--
-- Name: subject; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subject ON public.activity_log USING btree (subject_type, subject_id);


--
-- Name: subscriptions_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_company_id_status_index ON public.subscriptions USING btree (company_id, status);


--
-- Name: system_telemetry_install_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX system_telemetry_install_id_index ON public.system_telemetry USING btree (install_id);


--
-- Name: system_telemetry_recorded_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX system_telemetry_recorded_at_index ON public.system_telemetry USING btree (recorded_at);


--
-- Name: tax_declarations_company_id_type_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX tax_declarations_company_id_type_status_index ON public.tax_declarations USING btree (company_id, type, status);


--
-- Name: tax_declarations_due_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX tax_declarations_due_date_index ON public.tax_declarations USING btree (due_date);


--
-- Name: tax_rates_effective_from_effective_until_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX tax_rates_effective_from_effective_until_index ON public.tax_rates USING btree (effective_from, effective_until);


--
-- Name: tax_rates_tax_id_effective_from_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX tax_rates_tax_id_effective_from_index ON public.tax_rates USING btree (tax_id, effective_from);


--
-- Name: taxes_company_id_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX taxes_company_id_type_index ON public.taxes USING btree (company_id, type);


--
-- Name: telemetry_events_event_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX telemetry_events_event_name_index ON public.telemetry_events USING btree (event_name);


--
-- Name: telemetry_events_occurred_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX telemetry_events_occurred_at_index ON public.telemetry_events USING btree (occurred_at);


--
-- Name: telemetry_sessions_session_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX telemetry_sessions_session_id_index ON public.telemetry_sessions USING btree (session_id);


--
-- Name: telemetry_sessions_started_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX telemetry_sessions_started_at_index ON public.telemetry_sessions USING btree (started_at);


--
-- Name: users_last_seen_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_last_seen_at_index ON public.users USING btree (last_seen_at);


--
-- Name: vat_declaration_lines_vat_declaration_id_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX vat_declaration_lines_vat_declaration_id_type_index ON public.vat_declaration_lines USING btree (vat_declaration_id, type);


--
-- Name: vat_declarations_company_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX vat_declarations_company_id_status_index ON public.vat_declarations USING btree (company_id, status);


--
-- Name: vat_declarations_due_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX vat_declarations_due_date_index ON public.vat_declarations USING btree (due_date);


--
-- Name: accounting_entries accounting_entries_cancelled_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_cancelled_by_foreign FOREIGN KEY (cancelled_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: accounting_entries accounting_entries_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: accounting_entries accounting_entries_journal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_journal_id_foreign FOREIGN KEY (journal_id) REFERENCES public.journals(id) ON DELETE RESTRICT;


--
-- Name: accounting_entries accounting_entries_period_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_period_id_foreign FOREIGN KEY (period_id) REFERENCES public.periods(id) ON DELETE RESTRICT;


--
-- Name: accounting_entries accounting_entries_reversal_of_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_reversal_of_id_foreign FOREIGN KEY (reversal_of_id) REFERENCES public.accounting_entries(id) ON DELETE SET NULL;


--
-- Name: accounting_entries accounting_entries_reversed_by_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_reversed_by_id_foreign FOREIGN KEY (reversed_by_id) REFERENCES public.accounting_entries(id) ON DELETE SET NULL;


--
-- Name: accounting_entries accounting_entries_validated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entries
    ADD CONSTRAINT accounting_entries_validated_by_foreign FOREIGN KEY (validated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: accounting_entry_lines accounting_entry_lines_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entry_lines
    ADD CONSTRAINT accounting_entry_lines_account_id_foreign FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE RESTRICT;


--
-- Name: accounting_entry_lines accounting_entry_lines_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entry_lines
    ADD CONSTRAINT accounting_entry_lines_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: accounting_entry_lines accounting_entry_lines_entry_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entry_lines
    ADD CONSTRAINT accounting_entry_lines_entry_id_foreign FOREIGN KEY (entry_id) REFERENCES public.accounting_entries(id) ON DELETE CASCADE;


--
-- Name: accounting_entry_lines accounting_entry_lines_lettering_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounting_entry_lines
    ADD CONSTRAINT accounting_entry_lines_lettering_id_foreign FOREIGN KEY (lettering_id) REFERENCES public.letterings(id) ON DELETE SET NULL;


--
-- Name: accounts accounts_chart_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_chart_account_id_foreign FOREIGN KEY (chart_account_id) REFERENCES public.chart_accounts(id) ON DELETE CASCADE;


--
-- Name: accounts accounts_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: accounts accounts_parent_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.accounts
    ADD CONSTRAINT accounts_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: asset_depreciations asset_depreciations_asset_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asset_depreciations
    ADD CONSTRAINT asset_depreciations_asset_id_foreign FOREIGN KEY (asset_id) REFERENCES public.assets(id) ON DELETE CASCADE;


--
-- Name: asset_depreciations asset_depreciations_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asset_depreciations
    ADD CONSTRAINT asset_depreciations_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: assets assets_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.assets
    ADD CONSTRAINT assets_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: bank_statement_lines bank_statement_lines_bank_statement_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bank_statement_lines
    ADD CONSTRAINT bank_statement_lines_bank_statement_id_foreign FOREIGN KEY (bank_statement_id) REFERENCES public.bank_statements(id) ON DELETE CASCADE;


--
-- Name: bank_statements bank_statements_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bank_statements
    ADD CONSTRAINT bank_statements_account_id_foreign FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: bank_statements bank_statements_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bank_statements
    ADD CONSTRAINT bank_statements_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: chart_accounts chart_accounts_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chart_accounts
    ADD CONSTRAINT chart_accounts_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: clients clients_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: company_user company_user_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_user
    ADD CONSTRAINT company_user_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: company_user company_user_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_user
    ADD CONSTRAINT company_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: customer_payments customer_payments_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customer_payments
    ADD CONSTRAINT customer_payments_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: customer_payments customer_payments_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customer_payments
    ADD CONSTRAINT customer_payments_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: customer_payments customer_payments_sales_invoice_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customer_payments
    ADD CONSTRAINT customer_payments_sales_invoice_id_foreign FOREIGN KEY (sales_invoice_id) REFERENCES public.sales_invoices(id) ON DELETE SET NULL;


--
-- Name: departments departments_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: departments departments_parent_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.departments
    ADD CONSTRAINT departments_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES public.departments(id) ON DELETE SET NULL;


--
-- Name: employee_contracts employee_contracts_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_contracts
    ADD CONSTRAINT employee_contracts_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: employee_contracts employee_contracts_contract_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_contracts
    ADD CONSTRAINT employee_contracts_contract_type_id_foreign FOREIGN KEY (contract_type_id) REFERENCES public.contract_types(id) ON DELETE RESTRICT;


--
-- Name: employee_contracts employee_contracts_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_contracts
    ADD CONSTRAINT employee_contracts_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE CASCADE;


--
-- Name: employee_documents employee_documents_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_documents
    ADD CONSTRAINT employee_documents_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: employee_documents employee_documents_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_documents
    ADD CONSTRAINT employee_documents_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE CASCADE;


--
-- Name: employee_documents employee_documents_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employee_documents
    ADD CONSTRAINT employee_documents_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: employees employees_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: employees employees_department_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_department_id_foreign FOREIGN KEY (department_id) REFERENCES public.departments(id) ON DELETE SET NULL;


--
-- Name: employees employees_position_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_position_id_foreign FOREIGN KEY (position_id) REFERENCES public.positions(id) ON DELETE SET NULL;


--
-- Name: employees employees_superior_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_superior_id_foreign FOREIGN KEY (superior_id) REFERENCES public.employees(id) ON DELETE SET NULL;


--
-- Name: employees employees_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.employees
    ADD CONSTRAINT employees_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: exchange_rates exchange_rates_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exchange_rates
    ADD CONSTRAINT exchange_rates_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE SET NULL;


--
-- Name: fiscal_deadlines fiscal_deadlines_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_deadlines
    ADD CONSTRAINT fiscal_deadlines_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: fiscal_years fiscal_years_closed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_years
    ADD CONSTRAINT fiscal_years_closed_by_foreign FOREIGN KEY (closed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: fiscal_years fiscal_years_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fiscal_years
    ADD CONSTRAINT fiscal_years_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: journal_entries journal_entries_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journal_entries
    ADD CONSTRAINT journal_entries_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: journal_entries journal_entries_journal_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journal_entries
    ADD CONSTRAINT journal_entries_journal_id_foreign FOREIGN KEY (journal_id) REFERENCES public.journals(id) ON DELETE CASCADE;


--
-- Name: journal_items journal_items_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journal_items
    ADD CONSTRAINT journal_items_account_id_foreign FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE CASCADE;


--
-- Name: journal_items journal_items_journal_entry_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journal_items
    ADD CONSTRAINT journal_items_journal_entry_id_foreign FOREIGN KEY (journal_entry_id) REFERENCES public.journal_entries(id) ON DELETE CASCADE;


--
-- Name: journals journals_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journals
    ADD CONSTRAINT journals_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: journals journals_default_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.journals
    ADD CONSTRAINT journals_default_account_id_foreign FOREIGN KEY (default_account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: leaves leaves_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leaves
    ADD CONSTRAINT leaves_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: leaves leaves_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leaves
    ADD CONSTRAINT leaves_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: leaves leaves_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leaves
    ADD CONSTRAINT leaves_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE CASCADE;


--
-- Name: letterings letterings_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.letterings
    ADD CONSTRAINT letterings_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: pay_item_rates pay_item_rates_pay_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_item_rates
    ADD CONSTRAINT pay_item_rates_pay_item_id_foreign FOREIGN KEY (pay_item_id) REFERENCES public.pay_items(id) ON DELETE CASCADE;


--
-- Name: pay_items pay_items_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_items
    ADD CONSTRAINT pay_items_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: pay_runs pay_runs_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_runs
    ADD CONSTRAINT pay_runs_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: pay_runs pay_runs_locked_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_runs
    ADD CONSTRAINT pay_runs_locked_by_foreign FOREIGN KEY (locked_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: pay_runs pay_runs_validated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pay_runs
    ADD CONSTRAINT pay_runs_validated_by_foreign FOREIGN KEY (validated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: payroll_variables payroll_variables_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payroll_variables
    ADD CONSTRAINT payroll_variables_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: payroll_variables payroll_variables_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payroll_variables
    ADD CONSTRAINT payroll_variables_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE CASCADE;


--
-- Name: payroll_variables payroll_variables_pay_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payroll_variables
    ADD CONSTRAINT payroll_variables_pay_item_id_foreign FOREIGN KEY (pay_item_id) REFERENCES public.pay_items(id) ON DELETE RESTRICT;


--
-- Name: payroll_variables payroll_variables_pay_run_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payroll_variables
    ADD CONSTRAINT payroll_variables_pay_run_id_foreign FOREIGN KEY (pay_run_id) REFERENCES public.pay_runs(id) ON DELETE CASCADE;


--
-- Name: payslip_items payslip_items_pay_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslip_items
    ADD CONSTRAINT payslip_items_pay_item_id_foreign FOREIGN KEY (pay_item_id) REFERENCES public.pay_items(id) ON DELETE RESTRICT;


--
-- Name: payslip_items payslip_items_payslip_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslip_items
    ADD CONSTRAINT payslip_items_payslip_id_foreign FOREIGN KEY (payslip_id) REFERENCES public.payslips(id) ON DELETE CASCADE;


--
-- Name: payslip_lines payslip_lines_pay_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslip_lines
    ADD CONSTRAINT payslip_lines_pay_item_id_foreign FOREIGN KEY (pay_item_id) REFERENCES public.pay_items(id) ON DELETE RESTRICT;


--
-- Name: payslip_lines payslip_lines_payslip_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslip_lines
    ADD CONSTRAINT payslip_lines_payslip_id_foreign FOREIGN KEY (payslip_id) REFERENCES public.payslips(id) ON DELETE CASCADE;


--
-- Name: payslips payslips_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslips
    ADD CONSTRAINT payslips_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: payslips payslips_employee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslips
    ADD CONSTRAINT payslips_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES public.employees(id) ON DELETE RESTRICT;


--
-- Name: payslips payslips_pay_run_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payslips
    ADD CONSTRAINT payslips_pay_run_id_foreign FOREIGN KEY (pay_run_id) REFERENCES public.pay_runs(id) ON DELETE CASCADE;


--
-- Name: periods periods_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.periods
    ADD CONSTRAINT periods_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: periods periods_fiscal_year_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.periods
    ADD CONSTRAINT periods_fiscal_year_id_foreign FOREIGN KEY (fiscal_year_id) REFERENCES public.fiscal_years(id) ON DELETE CASCADE;


--
-- Name: periods periods_locked_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.periods
    ADD CONSTRAINT periods_locked_by_foreign FOREIGN KEY (locked_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: positions positions_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.positions
    ADD CONSTRAINT positions_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: positions positions_department_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.positions
    ADD CONSTRAINT positions_department_id_foreign FOREIGN KEY (department_id) REFERENCES public.departments(id) ON DELETE SET NULL;


--
-- Name: purchase_invoice_items purchase_invoice_items_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoice_items
    ADD CONSTRAINT purchase_invoice_items_account_id_foreign FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: purchase_invoice_items purchase_invoice_items_purchase_invoice_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoice_items
    ADD CONSTRAINT purchase_invoice_items_purchase_invoice_id_foreign FOREIGN KEY (purchase_invoice_id) REFERENCES public.purchase_invoices(id) ON DELETE CASCADE;


--
-- Name: purchase_invoices purchase_invoices_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoices
    ADD CONSTRAINT purchase_invoices_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: purchase_invoices purchase_invoices_purchase_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoices
    ADD CONSTRAINT purchase_invoices_purchase_order_id_foreign FOREIGN KEY (purchase_order_id) REFERENCES public.purchase_orders(id) ON DELETE SET NULL;


--
-- Name: purchase_invoices purchase_invoices_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_invoices
    ADD CONSTRAINT purchase_invoices_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON DELETE CASCADE;


--
-- Name: purchase_order_items purchase_order_items_purchase_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_items
    ADD CONSTRAINT purchase_order_items_purchase_order_id_foreign FOREIGN KEY (purchase_order_id) REFERENCES public.purchase_orders(id) ON DELETE CASCADE;


--
-- Name: purchase_orders purchase_orders_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: purchase_orders purchase_orders_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON DELETE CASCADE;


--
-- Name: report_exports report_exports_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_exports
    ADD CONSTRAINT report_exports_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: report_exports report_exports_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.report_exports
    ADD CONSTRAINT report_exports_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: sales_invoice_items sales_invoice_items_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoice_items
    ADD CONSTRAINT sales_invoice_items_account_id_foreign FOREIGN KEY (account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: sales_invoice_items sales_invoice_items_sales_invoice_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoice_items
    ADD CONSTRAINT sales_invoice_items_sales_invoice_id_foreign FOREIGN KEY (sales_invoice_id) REFERENCES public.sales_invoices(id) ON DELETE CASCADE;


--
-- Name: sales_invoices sales_invoices_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoices
    ADD CONSTRAINT sales_invoices_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: sales_invoices sales_invoices_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoices
    ADD CONSTRAINT sales_invoices_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: sales_invoices sales_invoices_sales_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_invoices
    ADD CONSTRAINT sales_invoices_sales_order_id_foreign FOREIGN KEY (sales_order_id) REFERENCES public.sales_orders(id) ON DELETE SET NULL;


--
-- Name: sales_order_items sales_order_items_sales_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_order_items
    ADD CONSTRAINT sales_order_items_sales_order_id_foreign FOREIGN KEY (sales_order_id) REFERENCES public.sales_orders(id) ON DELETE CASCADE;


--
-- Name: sales_orders sales_orders_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT sales_orders_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: sales_orders sales_orders_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT sales_orders_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: sequence_numbers sequence_numbers_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sequence_numbers
    ADD CONSTRAINT sequence_numbers_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: settings settings_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: social_contribution_rates social_contribution_rates_social_contribution_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.social_contribution_rates
    ADD CONSTRAINT social_contribution_rates_social_contribution_id_foreign FOREIGN KEY (social_contribution_id) REFERENCES public.social_contributions(id) ON DELETE CASCADE;


--
-- Name: subscriptions subscriptions_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: subscriptions subscriptions_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_plan_id_foreign FOREIGN KEY (plan_id) REFERENCES public.plans(id) ON DELETE RESTRICT;


--
-- Name: supplier_payments supplier_payments_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_payments
    ADD CONSTRAINT supplier_payments_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: supplier_payments supplier_payments_purchase_invoice_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_payments
    ADD CONSTRAINT supplier_payments_purchase_invoice_id_foreign FOREIGN KEY (purchase_invoice_id) REFERENCES public.purchase_invoices(id) ON DELETE SET NULL;


--
-- Name: supplier_payments supplier_payments_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_payments
    ADD CONSTRAINT supplier_payments_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON DELETE CASCADE;


--
-- Name: suppliers suppliers_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: tax_declarations tax_declarations_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_declarations
    ADD CONSTRAINT tax_declarations_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: tax_rates tax_rates_tax_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tax_rates
    ADD CONSTRAINT tax_rates_tax_id_foreign FOREIGN KEY (tax_id) REFERENCES public.taxes(id) ON DELETE CASCADE;


--
-- Name: taxes taxes_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taxes
    ADD CONSTRAINT taxes_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: taxes taxes_purchase_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taxes
    ADD CONSTRAINT taxes_purchase_account_id_foreign FOREIGN KEY (purchase_account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: taxes taxes_sales_account_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.taxes
    ADD CONSTRAINT taxes_sales_account_id_foreign FOREIGN KEY (sales_account_id) REFERENCES public.accounts(id) ON DELETE SET NULL;


--
-- Name: telemetry_events telemetry_events_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telemetry_events
    ADD CONSTRAINT telemetry_events_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: telemetry_sessions telemetry_sessions_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.telemetry_sessions
    ADD CONSTRAINT telemetry_sessions_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: vat_declaration_lines vat_declaration_lines_tax_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declaration_lines
    ADD CONSTRAINT vat_declaration_lines_tax_id_foreign FOREIGN KEY (tax_id) REFERENCES public.taxes(id) ON DELETE SET NULL;


--
-- Name: vat_declaration_lines vat_declaration_lines_vat_declaration_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declaration_lines
    ADD CONSTRAINT vat_declaration_lines_vat_declaration_id_foreign FOREIGN KEY (vat_declaration_id) REFERENCES public.vat_declarations(id) ON DELETE CASCADE;


--
-- Name: vat_declarations vat_declarations_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declarations
    ADD CONSTRAINT vat_declarations_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: vat_declarations vat_declarations_period_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declarations
    ADD CONSTRAINT vat_declarations_period_id_foreign FOREIGN KEY (period_id) REFERENCES public.periods(id) ON DELETE RESTRICT;


--
-- Name: vat_declarations vat_declarations_validated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vat_declarations
    ADD CONSTRAINT vat_declarations_validated_by_foreign FOREIGN KEY (validated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--


