CREATE TABLE IF NOT EXISTS public.urls
        (
            id bigint  PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            name character varying(500) UNIQUE NOT NULL,
            created_at timestamp with time zone NOT NULL
        
        )
