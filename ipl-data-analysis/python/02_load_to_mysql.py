import pandas as pd
from sqlalchemy import create_engine
from pathlib import Path

DB_USER = "root"
DB_PASSWORD = ""
DB_HOST = "localhost"
DB_PORT = 3307
DB_NAME = "ipl_analysis"

engine = create_engine(
    f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}"
)

PROCESSED_DIR = Path("data/processed")

def load_table(csv_path: Path, table_name: str, chunksize: int = 5000):
    df = pd.read_csv(csv_path)
    df.to_sql(
        table_name,
        con=engine,
        if_exists="append",
        index=False,
        chunksize=chunksize,
        method="multi",
    )
    print(f"Loaded {len(df)} rows into `{table_name}`")

if __name__ == "__main__":
    load_table(PROCESSED_DIR / "matches_clean.csv", "matches")
    load_table(PROCESSED_DIR / "deliveries_clean.csv", "deliveries")
    print("Done. Data is now queryable in MySQL.")
