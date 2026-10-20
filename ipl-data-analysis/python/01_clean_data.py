import pandas as pd
from pathlib import Path

RAW_DIR = Path("data/raw")
OUT_DIR = Path("data/processed")
OUT_DIR.mkdir(parents=True, exist_ok=True)

TEAM_NAME_MAP = {
    "Delhi Daredevils": "Delhi Capitals",
    "Deccan Chargers": "Sunrisers Hyderabad",
    "Kings XI Punjab": "Punjab Kings",
    "Rising Pune Supergiant": "Rising Pune Supergiants",
    "Royal Challengers Bangalore": "Royal Challengers Bengaluru",
}

def clean_matches(df: pd.DataFrame) -> pd.DataFrame:
    df = df.copy()

    for col in ["team1", "team2", "toss_winner", "winner"]:
        if col in df.columns:
            df[col] = df[col].replace(TEAM_NAME_MAP)

    df["date"] = pd.to_datetime(df["date"], errors="coerce").dt.date
    df.rename(columns={"date": "match_date", "id": "match_id"}, inplace=True)

    df["result"] = df["result"].fillna("no result")

    keep_cols = [
        "match_id", "season", "city", "match_date", "match_type",
        "player_of_match", "venue", "team1", "team2", "toss_winner",
        "toss_decision", "winner", "result", "result_margin",
        "target_runs", "target_overs", "super_over", "method",
        "umpire1", "umpire2",
    ]
    df = df[[c for c in keep_cols if c in df.columns]]

    df = df.drop_duplicates(subset=["match_id"])
    return df

def clean_deliveries(df: pd.DataFrame) -> pd.DataFrame:
    df = df.copy()

    for col in ["batting_team", "bowling_team"]:
        if col in df.columns:
            df[col] = df[col].replace(TEAM_NAME_MAP)

    df.rename(columns={"batsman": "batter"}, inplace=True)

    numeric_cols = ["batsman_runs", "extra_runs", "total_runs", "is_wicket"]
    for c in numeric_cols:
        if c in df.columns:
            df[c] = df[c].fillna(0).astype(int)

    keep_cols = [
        "match_id", "inning", "batting_team", "bowling_team", "over",
        "ball", "batter", "non_striker", "bowler", "batsman_runs",
        "extra_runs", "total_runs", "extras_type", "is_wicket",
        "player_dismissed", "dismissal_kind", "fielder",
    ]
    df = df[[c for c in keep_cols if c in df.columns]]
    df.rename(columns={"over": "over_no", "ball": "ball_no"}, inplace=True)

    df = df.drop_duplicates()
    return df

if __name__ == "__main__":
    matches = pd.read_csv(RAW_DIR / "matches.csv")
    deliveries = pd.read_csv(RAW_DIR / "deliveries.csv")

    matches_clean = clean_matches(matches)
    deliveries_clean = clean_deliveries(deliveries)

    matches_clean.to_csv(OUT_DIR / "matches_clean.csv", index=False)
    deliveries_clean.to_csv(OUT_DIR / "deliveries_clean.csv", index=False)

    print(f"Matches: {len(matches_clean)} rows cleaned -> {OUT_DIR/'matches_clean.csv'}")
    print(f"Columns: {list(matches_clean.columns)}")
    print(f"Deliveries: {len(deliveries_clean)} rows cleaned -> {OUT_DIR/'deliveries_clean.csv'}")
    print(f"Columns: {list(deliveries_clean.columns)}")
