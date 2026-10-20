"""
Telecom Customer Churn - Data Cleaning & Exploratory Data Analysis (EDA)
--------------------------------------------------------------------------
Role in the project:
  1. Read the raw CSV
  2. Clean it (missing values, wrong types, whitespace, duplicates)
  3. Engineer a couple of helper columns (tenure_bucket)
  4. Save EDA charts to disk (for your own understanding / report)
  5. Load the CLEAN data into MySQL, ready for the SQL queries and Power BI

No machine learning here — this is pure data engineering + descriptive analysis.

Install requirements first:
    pip install pandas numpy matplotlib seaborn sqlalchemy pymysql
"""

import os
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from sqlalchemy import create_engine

RAW_CSV_PATH = "telecom_churn.csv"
EDA_OUTPUT_DIR = "eda_outputs"

MYSQL_USER = "root"
MYSQL_PASSWORD = ""
MYSQL_HOST = "localhost"
MYSQL_PORT = 3307
MYSQL_DB = "telecom_churn_db"

os.makedirs(EDA_OUTPUT_DIR, exist_ok=True)
sns.set_style("whitegrid")

df = pd.read_csv(RAW_CSV_PATH)
print(f"Raw shape: {df.shape}")

# 2a. Standardize column names to snake_case matching MySQL schema
df.columns = [c.strip() for c in df.columns]
rename_map = {
    "customerID": "customer_id",
    "gender": "gender",
    "SeniorCitizen": "senior_citizen",
    "Partner": "partner",
    "Dependents": "dependents",
    "tenure": "tenure_months",
    "PhoneService": "phone_service",
    "MultipleLines": "multiple_lines",
    "InternetService": "internet_service",
    "OnlineSecurity": "online_security",
    "OnlineBackup": "online_backup",
    "DeviceProtection": "device_protection",
    "TechSupport": "tech_support",
    "StreamingTV": "streaming_tv",
    "StreamingMovies": "streaming_movies",
    "Contract": "contract",
    "PaperlessBilling": "paperless_billing",
    "PaymentMethod": "payment_method",
    "MonthlyCharges": "monthly_charges",
    "TotalCharges": "total_charges",
    "Churn": "churn",
    "TenureBucket": "tenure_bucket"
}
df = df.rename(columns=rename_map)

# 2b. TotalCharges sometimes has blank strings for new customers -> convert to numeric
df["total_charges"] = pd.to_numeric(df["total_charges"], errors="coerce")

# 2c. Handle missing values
missing_before = df.isnull().sum()
print("\nMissing values before cleaning:\n", missing_before[missing_before > 0])

# Customers with missing total_charges are usually brand new (tenure = 0) -> fill with 0
df["total_charges"] = df["total_charges"].fillna(0)

# 2d. Remove duplicate customer records, if any
df = df.drop_duplicates(subset="customer_id")

# 2e. Strip whitespace from all text/object columns
obj_cols = df.select_dtypes(include="object").columns
for col in obj_cols:
    df[col] = df[col].astype(str).str.strip()

# 2f. Ensure correct data types
df["senior_citizen"] = df["senior_citizen"].astype(int)
df["tenure_months"] = df["tenure_months"].astype(int)
df["monthly_charges"] = df["monthly_charges"].astype(float)

# 2g. Feature engineering: tenure buckets for easier grouping in SQL/Power BI
def bucket_tenure(months):
    if months <= 12:
        return "0-12"
    elif months <= 24:
        return "13-24"
    elif months <= 48:
        return "25-48"
    elif months <= 60:
        return "49-60"
    else:
        return "61+"

df["tenure_bucket"] = df["tenure_months"].apply(bucket_tenure)

print(f"\nClean shape: {df.shape}")

# ---------------------------------------------------------------
# 3. EXPLORATORY DATA ANALYSIS (EDA) — save charts for reference
# ---------------------------------------------------------------

# 3a. Overall churn distribution
plt.figure(figsize=(5, 4))
sns.countplot(data=df, x="churn")
plt.title("Overall Churn Distribution")
plt.savefig(f"{EDA_OUTPUT_DIR}/01_churn_distribution.png", bbox_inches="tight")
plt.close()

# 3b. Churn rate by contract type
plt.figure(figsize=(6, 4))
sns.countplot(data=df, x="contract", hue="churn")
plt.title("Churn by Contract Type")
plt.xticks(rotation=15)
plt.savefig(f"{EDA_OUTPUT_DIR}/02_churn_by_contract.png", bbox_inches="tight")
plt.close()

# 3c. Monthly charges distribution: churned vs retained
plt.figure(figsize=(6, 4))
sns.boxplot(data=df, x="churn", y="monthly_charges")
plt.title("Monthly Charges: Churned vs Retained")
plt.savefig(f"{EDA_OUTPUT_DIR}/03_monthly_charges_boxplot.png", bbox_inches="tight")
plt.close()

# 3d. Churn rate by tenure bucket
plt.figure(figsize=(6, 4))
sns.countplot(data=df, x="tenure_bucket", hue="churn",
              order=["0-12", "13-24", "25-48", "49-60", "61+"])
plt.title("Churn by Tenure Bucket")
plt.savefig(f"{EDA_OUTPUT_DIR}/04_churn_by_tenure_bucket.png", bbox_inches="tight")
plt.close()

# 3e. Correlation heatmap of numeric fields
plt.figure(figsize=(5, 4))
numeric_df = df[["tenure_months", "monthly_charges", "total_charges", "senior_citizen"]]
sns.heatmap(numeric_df.corr(), annot=True, cmap="coolwarm")
plt.title("Correlation Heatmap")
plt.savefig(f"{EDA_OUTPUT_DIR}/05_correlation_heatmap.png", bbox_inches="tight")
plt.close()

print(f"\nEDA charts saved to '{EDA_OUTPUT_DIR}/'")

# ---------------------------------------------------------------
# 4. LOAD CLEAN DATA INTO MYSQL
# ---------------------------------------------------------------
connection_str = (
    f"mysql+pymysql://{MYSQL_USER}:{MYSQL_PASSWORD}@{MYSQL_HOST}:{MYSQL_PORT}/{MYSQL_DB}"
)
engine = create_engine(connection_str)

df.to_sql("customers", con=engine, if_exists="replace", index=False, chunksize=1000)
print(f"\nLoaded {len(df)} rows into MySQL table `{MYSQL_DB}.customers`")

# Also save the cleaned data as a CSV backup, useful if MySQL isn't reachable
df.to_csv("../telecom_churn_clean.csv", index=False)
print("Clean CSV backup saved to ../telecom_churn_clean.csv")
