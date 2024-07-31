SELECT
    p.date,
    SUM(p.quantity * pr.price) AS total_value
FROM
    products p
        JOIN
    price_log pr
    ON
                p.product_id = pr.product_id
            AND pr.date = (
            SELECT MAX(pr2.date)
            FROM price_log pr2
            WHERE pr2.product_id = pr.product_id
              AND pr2.date <= p.date
        )
WHERE
    p.date BETWEEN '2020-01-01' AND '2020-01-10'
GROUP BY
    p.date
ORDER BY
    p.date;