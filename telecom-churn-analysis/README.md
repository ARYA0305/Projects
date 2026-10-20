# Telecom Customer Churn Analysis Project
### Stack: MySQL + Python + Power BI (No Machine Learning)

## 1. Project Goal
Analyze a telecom company's customer data to understand **why and where customers churn**
(cancel their service), using SQL for data storage/querying, Python for cleaning and
exploratory analysis, and Power BI for an interactive business dashboard.

---

## 2. Architecture — How the three tools connect

```
   CSV Data (raw)
        │
        ▼
 ┌─────────────┐        ┌──────────────────┐        ┌───────────────┐
 │   Python    │  --->  │      MySQL        │  --->  │   Power BI    │
 │ (cleaning,  │        │ (structured store, │        │ (dashboard,   │
 │  EDA, load) │  <---  │  SQL analysis)     │        │  visuals, KPIs)│
 └─────────────┘        └──────────────────┘        └───────────────┘
```

- **Python** reads the raw CSV, cleans it, and loads it into MySQL. It also does the first
  round of exploratory data analysis (EDA) with charts, before any dashboard is built.
- **MySQL** is the single source of truth — a relational database holding clean, structured
  customer data. All business questions (churn rate, revenue lost, churn by contract type,
  etc.) are answered here first using SQL queries/views.
- **Power BI** connects directly to MySQL (live or scheduled refresh) and turns those tables
  and views into an interactive dashboard for business users (no coding needed for them).

---

## 3. Role of Each Tool (in plain terms)

| Tool | Role in the project | Why it's used here |
|---|---|---|
| **MySQL** | Data warehouse layer. Stores the cleaned customer table, enforces data types/keys, and answers analytical questions via SQL (GROUP BY, JOINs, window functions, views). | Reliable, structured storage; fast aggregate queries; a "single source of truth" that Power BI can connect to directly instead of reading flat files. |
| **Python** | Data engineering + EDA layer. Loads raw data, handles missing values/duplicates/wrong types, engineers helpful columns (e.g., tenure buckets), pushes clean data into MySQL, and produces exploratory charts (churn distribution, correlations) to guide what the dashboard should highlight. | Best tool for messy real-world data cleaning and quick statistical/visual exploration before committing to a database schema or dashboard design. |
| **Power BI** | Presentation/BI layer. Connects to MySQL, builds DAX measures (Churn Rate, Revenue Lost, etc.), and presents everything as an interactive dashboard — filters, slicers, drill-downs — for non-technical stakeholders (e.g., marketing/retention teams). | Turns numbers into decisions. Business users can't run Python/SQL, but they can click filters on a dashboard. |

**Flow of responsibility:** Python cleans → MySQL stores & aggregates → Power BI visualizes & tells the story.

---

## 4. Dataset
Use the well-known **Telco Customer Churn** dataset (7,043 rows, one row per customer).
Columns include: `customerID, gender, SeniorCitizen, Partner, Dependents, tenure,
PhoneService, MultipleLines, InternetService, OnlineSecurity, OnlineBackup,
DeviceProtection, TechSupport, StreamingTV, StreamingMovies, Contract, PaperlessBilling,
PaymentMethod, MonthlyCharges, TotalCharges, Churn`.

Download it (search "Telco Customer Churn Kaggle" — it's a standard public dataset) and
place the CSV at `data/telecom_churn.csv`. The scripts below expect that path and those
column names; adjust if your CSV differs.

---

## 5. Folder Structure
```
telecom_churn_project/
├── README.md                      <- you are here
├── data/
│   └── telecom_churn.csv          <- put raw dataset here (you provide this)
├── python/
│   └── data_cleaning_eda.py       <- cleaning + EDA + loads data into MySQL
├── sql/
│   ├── schema.sql                 <- creates database + table + indexes
│   └── analysis_queries.sql       <- business-question SQL queries & views
└── powerbi/
    └── dashboard_guide.md         <- how to connect Power BI + build the dashboard
```

---

## 6. Step-by-Step: How to Run This Project

1. **Set up MySQL**
   - Install MySQL Server (or use XAMPP/WAMP if you want a GUI-friendly local setup).
   - Run `sql/schema.sql` in MySQL Workbench (or CLI) to create the database and table.

2. **Run the Python pipeline**
   - Put the raw CSV in `data/telecom_churn.csv`.
   - Install requirements: `pip install pandas numpy matplotlib seaborn sqlalchemy pymysql`
   - Edit the DB credentials at the top of `python/data_cleaning_eda.py`.
   - Run it: `python python/data_cleaning_eda.py`
   - This cleans the data, saves EDA charts to `python/eda_outputs/`, and loads the clean
     table into your MySQL database.

3. **Run the analysis queries**
   - Open `sql/analysis_queries.sql` in MySQL Workbench and run each query/view. These
     become the exact tables Power BI will read.

4. **Build the Power BI dashboard**
   - Follow `powerbi/dashboard_guide.md` — connect Power BI to MySQL using the MySQL
     connector, import the views, build DAX measures, and lay out the report pages.

---

## 7. What This Project Demonstrates
- Realistic 3-layer BI architecture (ETL → Warehouse → Visualization)
- SQL skills: joins, GROUP BY, CASE WHEN, window functions, views
- Python skills: pandas cleaning, matplotlib/seaborn EDA, SQLAlchemy DB writes
- Power BI skills: MySQL connectivity, DAX measures, interactive dashboard design
- Business communication: turning churn data into an actionable retention story
  (no machine learning / prediction — purely descriptive & diagnostic analysis)
