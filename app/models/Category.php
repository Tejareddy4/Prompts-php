<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM categories ORDER BY sort_order ASC')->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** Categories with a live count of approved prompts, for browse cards / tabs. */
    public function withCounts(): array
    {
        return $this->db->query(
            'SELECT c.*, (
                SELECT COUNT(*) FROM prompts p WHERE p.category_id = c.id AND p.status_id = 2
             ) AS prompt_count
             FROM categories c
             ORDER BY c.sort_order ASC'
        )->fetchAll();
    }

    /** Categories with a count of ALL prompts (any status), for the admin manager. */
    public function withTotalCounts(): array
    {
        return $this->db->query(
            'SELECT c.*, (
                SELECT COUNT(*) FROM prompts p WHERE p.category_id = c.id
             ) AS prompt_count
             FROM categories c
             ORDER BY c.sort_order ASC'
        )->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, slug, icon, color, sort_order)
             VALUES (:name, :slug, :icon, :color, :sort_order)'
        );
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare(
            'UPDATE categories
             SET name = :name, slug = :slug, icon = :icon, color = :color, sort_order = :sort_order
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    /** Prompts in this category get category_id = NULL (fk is ON DELETE SET NULL). */
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE slug = :slug';
        if ($exceptId !== null) {
            $sql .= ' AND id != :id';
        }
        $stmt = $this->db->prepare($sql);
        $params = ['slug' => $slug];
        if ($exceptId !== null) {
            $params['id'] = $exceptId;
        }
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function nextSortOrder(): int
    {
        return (int) $this->db->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM categories')->fetchColumn();
    }
}
