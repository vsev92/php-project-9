CREATE TABLE IF NOT EXISTS public.urls
        (
            id bigint  PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            name character varying(500) UNIQUE NOT NULL,
            created_at timestamp  NOT NULL
        
        );
        
CREATE TABLE IF NOT EXISTS public.url_checks
        (
            id bigint  PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            url_id bigint REFERENCES urls(id) NOT NULL,
            status_code int,
            h1 character varying(250),
            title character varying(250),
            description character varying(1000),
            created_at timestamp  NOT NULL
       
        
        )
