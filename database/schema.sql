CREATE TABLE IF NOT EXISTS seen_matches (
                                            match_id BIGINT UNSIGNED PRIMARY KEY,
                                            seen_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);