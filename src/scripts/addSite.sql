CREATE TABLE IF NOT EXISTS public.sites
        (
            id bigint  PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
            url character varying(500) UNIQUE NOT NULL,
            "createdAt" timestamp with time zone NOT NULL
        
        )
