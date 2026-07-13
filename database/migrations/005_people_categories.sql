-- ── Migration 005: People-focused photo prompt categories ───────
-- Replaces the old topic categories (Writing, Coding, …) with
-- people/photo categories (Men, Women, Couple, …).
-- Run this in phpMyAdmin → SQL on your promptshare database.
--
-- NOTE: existing prompts keep their data but become "uncategorized"
-- (fk_prompts_category is ON DELETE SET NULL). Re-assign them from
-- the prompt edit form or Admin → Prompts.

DELETE FROM categories;
ALTER TABLE categories AUTO_INCREMENT = 1;

INSERT INTO categories (name, slug, icon, color, sort_order) VALUES
('Men',             'men',         'bi-person-standing',       'blue',   1),
('Women',           'women',       'bi-person-standing-dress', 'pink',   2),
('Couple',          'couple',      'bi-heart-fill',            'red',    3),
('Kids',            'kids',        'bi-balloon-heart',         'orange', 4),
('Family',          'family',      'bi-people-fill',           'green',  5),
('Wedding',         'wedding',     'bi-gem',                   'violet', 6),
('Traditional',     'traditional', 'bi-flower1',               'teal',   7),
('Festival',        'festival',    'bi-stars',                 'indigo', 8),
('Group & Friends', 'friends',     'bi-people',                'cyan',   9),
('Other',           'other',       'bi-grid',                  'gray',   10);
