CREATE TABLE IF NOT EXISTS public.urls
(
    id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    name character varying(500) UNIQUE NOT NULL,
    created_at timestamp NOT NULL
);

CREATE TABLE IF NOT EXISTS public.url_checks
(
    id bigint PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    url_id bigint REFERENCES urls (id) NOT NULL,
    status_code int,
    h1 character varying(250),
    title character varying(250),
    description character varying(1000),
    created_at timestamp NOT NULL
);

CREATE OR REPLACE VIEW public.last_check_dates AS
(
    SELECT
        c.url_id,
        MAX(c.created_at) AS created_at
    FROM url_checks AS c
    GROUP BY
        c.url_id
);

CREATE OR REPLACE VIEW public.last_checks AS
(
    SELECT
        c.url_id,
        c.status_code
    FROM url_checks AS c INNER JOIN last_check_dates ON
        c.url_id = last_check_dates.url_id
        AND c.created_at = last_check_dates.created_at
);
