-- ── Migration 004: Prompt categories ────────────────────────────
-- Run this in phpMyAdmin → SQL on your promptshare database

CREATE TABLE IF NOT EXISTS categories (
  id         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(60) NOT NULL,
  slug       VARCHAR(60) NOT NULL UNIQUE,
  icon       VARCHAR(40) NOT NULL DEFAULT 'bi-stars',
  color      VARCHAR(20) NOT NULL DEFAULT 'violet',
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0
);

INSERT INTO categories (name, slug, icon, color, sort_order) VALUES
('Writing & Content',       'writing',       'bi-pencil-square',  'violet', 1),
('Coding & Development',    'coding',        'bi-code-slash',     'blue',   2),
('Image & Art Generation',  'image-art',     'bi-palette',        'pink',   3),
('Marketing & SEO',         'marketing',     'bi-megaphone',      'orange', 4),
('Business & Productivity', 'business',      'bi-briefcase',      'green',  5),
('Education & Learning',    'education',     'bi-mortarboard',    'cyan',   6),
('Chatbot Personas',        'personas',      'bi-person-badge',   'red',    7),
('Video & Audio',           'video-audio',   'bi-camera-reels',   'indigo', 8),
('Data & Analysis',         'data-analysis', 'bi-bar-chart-line', 'teal',   9),
('Other',                   'other',         'bi-grid',           'gray',   10)
ON DUPLICATE KEY UPDATE name = VALUES(name);

ALTER TABLE prompts
  ADD COLUMN category_id TINYINT UNSIGNED NULL AFTER user_id;

ALTER TABLE prompts
  ADD CONSTRAINT fk_prompts_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  ADD INDEX idx_prompts_category (category_id, status_id);
