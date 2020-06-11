# WordPress - Plugin for Collecting Implicit Feedback from User Navigation Data

It requires PHP 7.4, as finally we have typed properties with this PHP version.
I am sorry old fellas.

Also, it requires MariaDB >= 5.7 with support for ```JSON_EXTRACT```.

List top 10 sessions
```
SELECT A.id, B.session_id, B.count
FROM wp_2_coimf_Actions AS A JOIN (
    SELECT session_id, count(*) as count
    FROM wp_2_coimf_Actions
    GROUP BY session_id
    HAVING count(*) > 1
) AS B ON A.session_id = B.session_id
GROUP BY A.session_id
ORDER BY B.count DESC
LIMIT 10;
```