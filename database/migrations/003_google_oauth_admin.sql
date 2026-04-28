-- ═══════════════════════════════════════════════════════════
-- PromptShare migration — run these in order on your Hostinger
-- MySQL database via phpMyAdmin or SSH
-- ═══════════════════════════════════════════════════════════

-- 1. Add Google OAuth + profile fields to users
ALTER TABLE users
  ADD COLUMN avatar_url    VARCHAR(500) NULL  AFTER google_id,
  ADD COLUMN is_banned     TINYINT(1) NOT NULL DEFAULT 0 AFTER avatar_url;

-- If google_id column doesn't exist yet, add it:
-- ALTER TABLE users ADD COLUMN google_id VARCHAR(191) NULL UNIQUE AFTER password_hash;

-- 2. Add is_featured and visibility to prompts
ALTER TABLE prompts
  ADD COLUMN is_featured   TINYINT(1) NOT NULL DEFAULT 0 AFTER status_id,
  ADD COLUMN visibility    ENUM('public','private') NOT NULL DEFAULT 'public' AFTER is_featured,
  ADD COLUMN trending_score FLOAT NOT NULL DEFAULT 0 AFTER updated_at;

-- 3. Indexes for performance
ALTER TABLE prompts
  ADD INDEX idx_trending  (status_id, trending_score),
  ADD INDEX idx_visibility (visibility, status_id, created_at);

-- Add FULLTEXT index for search (if not already added)
-- ALTER TABLE prompts ADD FULLTEXT idx_search (title, description, prompt_text);

-- 4. Comments table (new)
CREATE TABLE IF NOT EXISTS comments (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  prompt_id  INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  parent_id  BIGINT UNSIGNED NULL,
  body       TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_prompt_comments (prompt_id, parent_id, created_at),
  CONSTRAINT fk_comments_prompt FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE,
  CONSTRAINT fk_comments_user   FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE
);

-- 5. Follows table (new)
CREATE TABLE IF NOT EXISTS follows (
  id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  follower_id  INT UNSIGNED NOT NULL,
  following_id INT UNSIGNED NOT NULL,
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_follow (follower_id, following_id),
  INDEX idx_follower  (follower_id),
  INDEX idx_following (following_id),
  CONSTRAINT fk_follows_follower  FOREIGN KEY (follower_id)  REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_follows_following FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Notifications table (new)
CREATE TABLE IF NOT EXISTS notifications (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  type       VARCHAR(50) NOT NULL,
  data       JSON NULL,
  read_at    TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_notifications (user_id, read_at),
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
