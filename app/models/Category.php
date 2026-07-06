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
}
