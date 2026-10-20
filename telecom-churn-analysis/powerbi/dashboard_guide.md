# Power BI Dashboard Guide — Telecom Churn Analysis

Power BI's role here: connect to the MySQL views you already built, define a few DAX
measures, and lay out an interactive report for business stakeholders.

---

## 1. Connect Power BI to MySQL

1. Install the **MySQL Connector/NET** (required by Power BI to talk to MySQL) — download
   from MySQL's official site if you don't already have it.
2. In Power BI Desktop: `Get Data` → `More...` → search **MySQL database**.
3. Enter:
   - Server: `localhost:3306` (or your server address)
   - Database: `telecom_churn_db`
4. Choose **Import** mode (simplest for a project like this; use DirectQuery only if the
   data is huge or needs to always be live).
5. In the Navigator window, select the views you created:
   - `customers` (base table, optional — mainly for row-level detail/tooltips)
   - `vw_churn_by_contract`
   - `vw_churn_by_internet`
   - `vw_churn_by_tenure`
   - `vw_churn_by_payment`
   - `vw_revenue_lost`
   - `vw_churn_by_segment`
6. Click **Load**, then **Transform Data** (Power Query) if you need any last renaming —
   ideally this isn't needed since Python/MySQL already did the cleaning.

---

## 2. Data Model
Since most of the heavy aggregation was already done in SQL views, the Power BI model is
simple — mostly independent summary tables plus the detailed `customers` table. If you
want cross-filtering between visuals built on different views, create relationships (or
just build all visuals directly off the `customers` table and let Power BI do the
aggregating — either approach works for this dataset size).

**Recommended approach for a learning project:** build visuals directly from the
`customers` table using Power BI's own aggregation + DAX measures below. This gives you
full slicer/filter interactivity across every visual.

---

## 3. Key DAX Measures to Create

```DAX
Total Customers = COUNTROWS(customers)

Churned Customers =
CALCULATE(COUNTROWS(customers), customers[churn] = "Yes")

Churn Rate % =
DIVIDE([Churned Customers], [Total Customers], 0) * 100

Retained Customers = [Total Customers] - [Churned Customers]

Monthly Revenue Lost =
CALCULATE(SUM(customers[monthly_charges]), customers[churn] = "Yes")

Total Monthly Revenue = SUM(customers[monthly_charges])

Revenue Lost % = DIVIDE([Monthly Revenue Lost], [Total Monthly Revenue], 0) * 100

Avg Tenure (Churned) =
CALCULATE(AVERAGE(customers[tenure_months]), customers[churn] = "Yes")

Avg Monthly Charges (Churned) =
CALCULATE(AVERAGE(customers[monthly_charges]), customers[churn] = "Yes")
```

---

## 4. Suggested Dashboard Layout (2 pages)

### Page 1 — Executive Overview
- **KPI cards** (top row): Total Customers, Churn Rate %, Monthly Revenue Lost, Revenue Lost %
- **Donut chart**: Churned vs Retained customers
- **Bar chart**: Churn Rate % by Contract type (Month-to-month usually stands out)
- **Bar chart**: Churn Rate % by Internet Service
- **Line/column chart**: Churn Rate % by Tenure Bucket (shows churn is highest in early months)
- **Slicers**: Contract, Internet Service, Senior Citizen, Payment Method

### Page 2 — Deep Dive / Segments
- **Matrix/table**: Contract × Senior Citizen churn rate (from `vw_churn_by_segment`)
- **Bar chart**: Churn Rate % by Payment Method (Electronic check often stands out)
- **Box/scatter or clustered bar**: Monthly Charges comparison, Churned vs Retained
- **Table**: Top at-risk customer segments (e.g., no Tech Support + no Online Security)
- **Tooltip page (optional)**: individual customer detail on hover

---

## 5. Formatting Tips
- Use a consistent color for "Churned" (e.g., red/orange) and "Retained" (e.g., teal/green)
  across every visual on the report — this is the single most important consistency rule.
- Add a report-level title and a short subtitle stating the data period / source.
- Use **Tooltips** and **Drillthrough** so users can click a contract-type bar and drill
  into a customer-level table.
- Publish to Power BI Service and set up a **scheduled refresh** against MySQL if the data
  updates regularly (requires an on-premises data gateway for a local MySQL server).

---

## 6. What This Page Is NOT
No churn prediction/ML model is built here — everything is **descriptive and diagnostic**:
"what is the churn rate, and where/why does it concentrate" — not "which specific customer
will churn next." That would be a separate ML-based extension of this project.
