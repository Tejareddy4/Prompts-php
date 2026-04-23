-- ── Migration 002: FULLTEXT search, trending score, username ──────
-- Run this in phpMyAdmin → SQL on your promptshare database

-- 1. FULLTEXT index for fast search (replaces slow LIKE %...%)
ALTER TABLE prompts ADD FULLTEXT INDEX ft_prompts_search (title, description, prompt_text);

-- 2. Trending score column (pre-computed, updated by cron or on write)
ALTER TABLE prompts
  ADD COLUMN trending_score DECIMAL(10,4) NOT NULL DEFAULT 0.0000 AFTER updated_at;

ALTER TABLE prompts
  ADD INDEX idx_prompts_trending (trending_score DESC, status_id);

-- 3. Username column for public profiles
ALTER TABLE users
  ADD COLUMN username VARCHAR(50) NULL UNIQUE AFTER name;

ALTER TABLE users
  ADD INDEX idx_users_username (username);

-- 4. Backfill usernames for existing users (slugify name + id suffix)
UPDATE users
SET username = CONCAT(
  LOWER(REPLACE(REPLACE(TRIM(name), '  ', ' '), ' ', '_')),
  '_',
  id
)
WHERE username IS NULL;
