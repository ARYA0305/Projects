# IPL Data Analysis — End-to-End Project
### Kaggle → Python → MySQL → Power BI

## 1. What this project does
Analyzes 17+ seasons of IPL cricket data (2008–present) to answer questions like:
- Which teams/players perform best, and where?
- Does winning the toss actually help you win the match?
- How do powerplay vs death-over strategies affect outcomes?
- Which venues favor batting or bowling?

## 2. Dataset (Kaggle)
Recommended dataset: **"IPL Complete Dataset (2008–2024)"** by patrickb1912
https://www.kaggle.com/datasets/patrickb1912/ipl-complete-dataset-20082020

It ships two CSVs:
- **matches.csv** — one row per match (teams, venue, toss, winner, margin, POTM, umpires)
- **deliveries.csv** — one row per ball bowled (batsman, bowler, runs, extras, wickets) — this is the big one, ~2.6 lakh+ rows

### How to get it
```bash
pip install kaggle
# place your kaggle.json API token in ~/.kaggle/kaggle.json (from kaggle.com/settings)
kaggle datasets download -d patrickb1912/ipl-complete-dataset-20082020
unzip ipl-complete-dataset-20082020.zip -d data/raw/
```
(Or just download manually from the Kaggle page and drop the two CSVs into `data/raw/`.)

## 3. Project structure
```
ipl_project/
├── data/
│   ├── raw/            # original Kaggle CSVs go here
│   └── processed/      # cleaned CSVs (output of step 1)
├── sql/
│   ├── 01_schema.sql          # creates database + tables
│   └── 02_analysis_queries.sql # 15 analytical queries
├── python/
│   ├── 01_clean_data.py       # cleaning & transformation
│   └── 02_load_to_mysql.py    # pushes cleaned data into MySQL
├── powerbi/
│   └── powerbi_guide.md       # data model, DAX measures, dashboard layout
├── requirements.txt
└── README.md
```

Power BI is the only visualization layer in this version — matplotlib/seaborn
aren't needed and are left out of `requirements.txt`. Python's job here is
strictly ETL (clean + load), nothing more.

## 4. Pipeline — what each part does

### Step 1 — Clean (`python/01_clean_data.py`)
- Loads raw CSVs with pandas
- Fixes inconsistent team names (e.g., "Delhi Daredevils" → "Delhi Capitals", "Rising Pune Supergiant" duplicates)
- Parses dates, handles nulls (e.g., no-result matches, super overs)
- Deduplicates rows
- Saves clean CSVs to `data/processed/`

### Step 2 — Load (`python/02_load_to_mysql.py`)
- Connects to MySQL using SQLAlchemy + PyMySQL
- Pushes `matches` and `deliveries` dataframes into MySQL tables
- Uses chunked inserts (deliveries.csv is large — 250k+ rows)

### Step 3 — Schema (`sql/01_schema.sql`)
- Defines proper types, primary/foreign keys, and indexes
- `matches` (parent) ← `deliveries` (child, FK on match_id)
- Indexes on `batting_team`, `bowler`, `match_id` since these are the most-filtered columns

### Step 4 — Analysis (`sql/02_analysis_queries.sql`)
- 15 ready-to-run analytical queries: top run scorers, best bowling economy, toss-win vs match-win correlation, venue-wise scoring, powerplay/death-over splits, team head-to-head records, season trends

### Step 5 — Power BI (`powerbi/powerbi_guide.md`)
- Connects Power BI directly to MySQL (live or import mode)
- Builds a star-schema data model
- DAX measures for strike rate, economy rate, win %
- Dashboard page layout (Overview, Batting, Bowling, Teams, Venues)

## 5. Tools/versions used
- Python 3.10+, pandas, SQLAlchemy, PyMySQL, kaggle
- MySQL 8.0
- Power BI Desktop (Windows) with MySQL Connector/ODBC 8.0
