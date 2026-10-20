CREATE DATABASE IF NOT EXISTS telecom_churn_db;
USE telecom_churn_db;
DROP TABLE IF EXISTS customers;
CREATE TABLE customers (
    customer_id         VARCHAR(20)  PRIMARY KEY,
    gender               VARCHAR(10),
    senior_citizen       TINYINT(1),
    partner              VARCHAR(5),
    dependents           VARCHAR(5),
    tenure_months         INT,
    phone_service         VARCHAR(5),
    multiple_lines        VARCHAR(30),
    internet_service      VARCHAR(20),
    online_security       VARCHAR(30),
    online_backup         VARCHAR(30),
    device_protection     VARCHAR(30),
    tech_support          VARCHAR(30),
    streaming_tv           VARCHAR(30),
    streaming_movies      VARCHAR(30),
    contract              VARCHAR(20),
    paperless_billing     VARCHAR(5),
    payment_method        VARCHAR(40),
    monthly_charges       DECIMAL(8,2),
    total_charges         DECIMAL(10,2),
    churn                 VARCHAR(5),
    tenure_bucket         VARCHAR(20)
);

CREATE INDEX idx_contract ON customers(contract);
CREATE INDEX idx_churn ON customers(churn);
CREATE INDEX idx_internet ON customers(internet_service);
CREATE INDEX idx_tenure_bucket ON customers(tenure_bucket);
