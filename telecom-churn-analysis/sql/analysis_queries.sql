-- ============================================================
-- Business-question SQL queries & views
-- Run AFTER Python has loaded data into `customers`
-- These VIEWS are what you'll connect Power BI to.
-- ============================================================
USE telecom_churn_db;

-- 1) Overall churn rate
SELECT
    COUNT(*)                                             AS total_customers,
    SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END)        AS churned_customers,
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS churn_rate_pct
FROM customers;

-- 2) Churn rate by contract type
CREATE OR REPLACE VIEW vw_churn_by_contract AS
SELECT
    contract,
    COUNT(*) AS total_customers,
    SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) AS churned,
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS churn_rate_pct
FROM customers
GROUP BY contract;

-- 3) Churn rate by internet service type
CREATE OR REPLACE VIEW vw_churn_by_internet AS
SELECT
    internet_service,
    COUNT(*) AS total_customers,
    SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) AS churned,
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS churn_rate_pct
FROM customers
GROUP BY internet_service;

-- 4) Churn rate by tenure bucket (loyalty stage)
CREATE OR REPLACE VIEW vw_churn_by_tenure AS
SELECT
    tenure_bucket,
    COUNT(*) AS total_customers,
    SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) AS churned,
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS churn_rate_pct
FROM customers
GROUP BY tenure_bucket
ORDER BY MIN(tenure_months);

-- 5) Churn rate by payment method
CREATE OR REPLACE VIEW vw_churn_by_payment AS
SELECT
    payment_method,
    COUNT(*) AS total_customers,
    SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) AS churned,
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS churn_rate_pct
FROM customers
GROUP BY payment_method;

-- 6) Revenue lost to churn (monthly charges of churned customers)
CREATE OR REPLACE VIEW vw_revenue_lost AS
SELECT
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN monthly_charges ELSE 0 END), 2) AS monthly_revenue_lost,
    ROUND(SUM(monthly_charges), 2) AS total_monthly_revenue,
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN monthly_charges ELSE 0 END) * 100.0
          / SUM(monthly_charges), 2) AS pct_revenue_lost
FROM customers;

-- 7) Average monthly charges: churned vs retained
SELECT
    churn,
    ROUND(AVG(monthly_charges), 2) AS avg_monthly_charges,
    ROUND(AVG(tenure_months), 1)   AS avg_tenure_months
FROM customers
GROUP BY churn;

-- 8) Churn by combination of contract + senior citizen (segment drill-down example)
CREATE OR REPLACE VIEW vw_churn_by_segment AS
SELECT
    contract,
    senior_citizen,
    COUNT(*) AS total_customers,
    SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) AS churned,
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS churn_rate_pct
FROM customers
GROUP BY contract, senior_citizen;

-- 9) Top churn-risk services combination (e.g., no tech support + no online security)
SELECT
    tech_support,
    online_security,
    COUNT(*) AS total_customers,
    SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) AS churned,
    ROUND(SUM(CASE WHEN churn = 'Yes' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) AS churn_rate_pct
FROM customers
GROUP BY tech_support, online_security
ORDER BY churn_rate_pct DESC;

-- 10) Ranking contract types by churn rate using a window function
SELECT
    contract,
    churn_rate_pct,
    RANK() OVER (ORDER BY churn_rate_pct DESC) AS churn_risk_rank
FROM vw_churn_by_contract;

-- These vw_* views are the ones you should connect Power BI to directly.
