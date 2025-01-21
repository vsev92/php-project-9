WITH last_check_dates as (
    SELECT c.url_id, MAX(c.created_at) as created_at
    FROM url_checks as c
	GROUP BY
	c.url_id
), 
last_checks as (
    SELECT c.url_id, c.status_code
    FROM url_checks as c INNER JOIN last_check_dates ON
    c.url_id = last_check_dates.url_id AND c.created_at = last_check_dates.created_at
)
	 SELECT u.id, u.name, u.created_at, lc.status_code
	 FROM urls as u INNER JOIN last_checks as lc 
	 ON u.id = lc.url_id

