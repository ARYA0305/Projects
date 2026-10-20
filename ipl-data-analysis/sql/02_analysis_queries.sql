USE ipl_analysis;

SELECT batter, SUM(batsman_runs) AS total_runs
FROM deliveries
GROUP BY batter
ORDER BY total_runs DESC
LIMIT 10;

SELECT bowler, COUNT(*) AS wickets
FROM deliveries
WHERE is_wicket = 1
  AND dismissal_kind NOT IN ('run out', 'retired hurt', 'obstructing the field')
GROUP BY bowler
ORDER BY wickets DESC
LIMIT 10;

SELECT bowler,
       ROUND(SUM(total_runs - CASE WHEN extras_type = 'byes' OR extras_type = 'legbyes'
                                    THEN extra_runs ELSE 0 END) / (COUNT(*)/6), 2) AS economy,
       COUNT(*) AS balls_bowled
FROM deliveries
GROUP BY bowler
HAVING balls_bowled >= 500
ORDER BY economy ASC
LIMIT 10;

SELECT
    toss_decision,
    COUNT(*) AS total_matches,
    SUM(CASE WHEN toss_winner = winner THEN 1 ELSE 0 END) AS toss_winner_won,
    ROUND(100.0 * SUM(CASE WHEN toss_winner = winner THEN 1 ELSE 0 END) / COUNT(*), 2) AS win_pct
FROM matches
WHERE result = 'normal'
GROUP BY toss_decision;

SELECT winner AS team, COUNT(*) AS wins
FROM matches
WHERE winner IS NOT NULL
GROUP BY winner
ORDER BY wins DESC;

SELECT match_id, batting_team, inning, SUM(total_runs) AS innings_total
FROM deliveries
GROUP BY match_id, batting_team, inning
ORDER BY innings_total DESC
LIMIT 10;

SELECT m.venue,
       ROUND(AVG(inn.innings_total), 1) AS avg_first_innings_score,
       COUNT(*) AS matches_played
FROM (
    SELECT match_id, SUM(total_runs) AS innings_total
    FROM deliveries
    WHERE inning = 1
    GROUP BY match_id
) inn
JOIN matches m ON m.match_id = inn.match_id
GROUP BY m.venue
HAVING matches_played >= 5
ORDER BY avg_first_innings_score DESC;

SELECT batting_team,
       ROUND(SUM(CASE WHEN over_no BETWEEN 0 AND 5 THEN total_runs ELSE 0 END) /
             (SUM(CASE WHEN over_no BETWEEN 0 AND 5 THEN 1 ELSE 0 END)/6), 2) AS powerplay_run_rate,
       ROUND(SUM(CASE WHEN over_no BETWEEN 15 AND 19 THEN total_runs ELSE 0 END) /
             (SUM(CASE WHEN over_no BETWEEN 15 AND 19 THEN 1 ELSE 0 END)/6), 2) AS death_overs_run_rate
FROM deliveries
GROUP BY batting_team
ORDER BY powerplay_run_rate DESC;

SELECT
    SUM(CASE WHEN winner = 'Mumbai Indians' THEN 1 ELSE 0 END) AS mi_wins,
    SUM(CASE WHEN winner = 'Chennai Super Kings' THEN 1 ELSE 0 END) AS csk_wins,
    COUNT(*) AS total_matches
FROM matches
WHERE (team1 = 'Mumbai Indians' AND team2 = 'Chennai Super Kings')
   OR (team1 = 'Chennai Super Kings' AND team2 = 'Mumbai Indians');

SELECT player_of_match, COUNT(*) AS awards
FROM matches
WHERE player_of_match IS NOT NULL
GROUP BY player_of_match
ORDER BY awards DESC
LIMIT 10;

SELECT m.season, SUM(d.total_runs) AS total_runs, COUNT(DISTINCT d.match_id) AS matches
FROM deliveries d
JOIN matches m ON m.match_id = d.match_id
GROUP BY m.season
ORDER BY m.season;

SELECT batter,
       SUM(CASE WHEN batsman_runs = 6 THEN 1 ELSE 0 END) AS sixes,
       SUM(CASE WHEN batsman_runs = 4 THEN 1 ELSE 0 END) AS fours
FROM deliveries
GROUP BY batter
ORDER BY sixes DESC
LIMIT 10;

SELECT m.match_id, m.season, m.team1, m.team2, m.winner, m.target_runs
FROM matches m
WHERE m.result_type = 'wickets'
ORDER BY m.target_runs DESC
LIMIT 10;

SELECT dismissal_kind, COUNT(*) AS occurrences
FROM deliveries
WHERE is_wicket = 1
GROUP BY dismissal_kind
ORDER BY occurrences DESC;

SELECT bowler,
       ROUND(SUM(total_runs) / (COUNT(*)/6), 2) AS death_over_economy,
       COUNT(*) AS balls_bowled
FROM deliveries
WHERE over_no BETWEEN 15 AND 19
GROUP BY bowler
HAVING balls_bowled >= 120
ORDER BY death_over_economy ASC
LIMIT 10;
