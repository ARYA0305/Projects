CREATE DATABASE IF NOT EXISTS ipl_analysis
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ipl_analysis;

DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS matches;

CREATE TABLE matches (
    match_id        INT PRIMARY KEY,
    season          VARCHAR(10),
    city            VARCHAR(50),
    match_date      DATE,
    match_type      VARCHAR(20),
    player_of_match VARCHAR(60),
    venue           VARCHAR(120),
    team1           VARCHAR(50),
    team2           VARCHAR(50),
    toss_winner     VARCHAR(50),
    toss_decision   VARCHAR(10),
    winner          VARCHAR(50),
    result          VARCHAR(20),
    result_margin   FLOAT,
    target_runs     FLOAT,
    target_overs    FLOAT,
    super_over      VARCHAR(5),
    method          VARCHAR(20),
    umpire1         VARCHAR(60),
    umpire2         VARCHAR(60),

    INDEX idx_season (season),
    INDEX idx_venue (venue),
    INDEX idx_winner (winner)
) ENGINE=InnoDB;

CREATE TABLE deliveries (
    delivery_id      BIGINT AUTO_INCREMENT PRIMARY KEY,
    match_id         INT NOT NULL,
    inning           TINYINT,
    batting_team     VARCHAR(50),
    bowling_team     VARCHAR(50),
    over_no          TINYINT,
    ball_no          TINYINT,
    batter           VARCHAR(60),
    non_striker      VARCHAR(60),
    bowler           VARCHAR(60),
    batsman_runs     TINYINT,
    extra_runs       TINYINT,
    total_runs       TINYINT,
    extras_type      VARCHAR(20),
    is_wicket        TINYINT,
    player_dismissed VARCHAR(60),
    dismissal_kind   VARCHAR(30),
    fielder          VARCHAR(60),

    CONSTRAINT fk_match
        FOREIGN KEY (match_id) REFERENCES matches(match_id)
        ON DELETE CASCADE,

    INDEX idx_match (match_id),
    INDEX idx_batter (batter),
    INDEX idx_bowler (bowler)
) ENGINE=InnoDB;
