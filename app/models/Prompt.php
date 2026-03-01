<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Prompt extends Model
{
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO prompts (user_id, title, slug, description, prompt_text, image_path, status_id, created_at, updated_at) VALUES (:user_id, :title, :slug, :description, :prompt_text, :image_path, 1, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function paginateApproved(int $limit, int $offset, ?int $userId = null, array $filters = []): array
    {
        $sql = 'SELECT p.*, u.name AS author,
                    (SELECT COUNT(*) FROM likes l WHERE l.prompt_id = p.id) AS likes_count,
                    (SELECT COUNT(*) FROM saves s WHERE s.prompt_id = p.id) AS saves_count,
                    (SELECT COUNT(*) FROM copies c WHERE c.prompt_id = p.id) AS copies_count,
                    (SELECT COUNT(*) FROM views v WHERE v.prompt_id = p.id) AS views_count';
        if ($userId) {
            $sql .= ', EXISTS(SELECT 1 FROM likes l2 WHERE l2.prompt_id = p.id AND l2.user_id = :user_id) AS is_liked,
                     EXISTS(SELECT 1 FROM saves s2 WHERE s2.prompt_id = p.id AND s2.user_id = :user_id) AS is_saved';
        }
        $sql .= ' FROM prompts p JOIN users u ON u.id = p.user_id WHERE p.status_id = 2';

        $params = [];
        if (!empty($filters['q'])) {
            $sql .= ' AND (p.title LIKE :search OR p.description LIKE :search OR p.prompt_text LIKE :search)';
            $params['search'] = '%' . $filters['q'] . '%';
        }

        $sortBy = $filters['sort'] ?? 'newest';
        $orderBy = match ($sortBy) {
            'most_liked' => 'likes_count DESC, p.created_at DESC',
            'most_saved' => 'saves_count DESC, p.created_at DESC',
            'most_viewed' => 'views_count DESC, p.created_at DESC',
            default => 'p.created_at DESC',
        };

        $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        if ($userId) {
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        }
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug, ?int $userId = null): ?array
    {
        $sql = 'SELECT p.*, u.name AS author,
            (SELECT COUNT(*) FROM likes l WHERE l.prompt_id = p.id) AS likes_count,
            (SELECT COUNT(*) FROM saves s WHERE s.prompt_id = p.id) AS saves_count,
            (SELECT COUNT(*) FROM copies c WHERE c.prompt_id = p.id) AS copies_count,
            (SELECT COUNT(*) FROM views v WHERE v.prompt_id = p.id) AS views_count';
        if ($userId) {
            $sql .= ', EXISTS(SELECT 1 FROM likes l2 WHERE l2.prompt_id = p.id AND l2.user_id = :user_id) AS is_liked,
                     EXISTS(SELECT 1 FROM saves s2 WHERE s2.prompt_id = p.id AND s2.user_id = :user_id) AS is_saved';
        }
        $sql .= ' FROM prompts p JOIN users u ON u.id = p.user_id WHERE p.slug = :slug AND p.status_id = 2 LIMIT 1';
        $stmt = $this->db->prepare($sql);
        if ($userId) {
            $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        }
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        return $stmt->fetch() ?: null;
    }

    public function findByStatus(int $statusId): array
    {
        $stmt = $this->db->prepare('SELECT p.*, u.name AS author FROM prompts p JOIN users u ON u.id = p.user_id WHERE p.status_id = :status_id ORDER BY p.created_at DESC');
        $stmt->execute(['status_id' => $statusId]);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $promptId, int $statusId): void
    {
        $stmt = $this->db->prepare('UPDATE prompts SET status_id = :status_id, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['status_id' => $statusId, 'id' => $promptId]);
    }

    public function delete(int $promptId): void
    {
        $stmt = $this->db->prepare('DELETE FROM prompts WHERE id = :id');
        $stmt->execute(['id' => $promptId]);
    }

    public function userPrompts(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT p.*, (SELECT COUNT(*) FROM likes l WHERE l.prompt_id = p.id) AS likes_count, (SELECT COUNT(*) FROM saves s WHERE s.prompt_id = p.id) AS saves_count, (SELECT COUNT(*) FROM copies c WHERE c.prompt_id = p.id) AS copies_count, (SELECT COUNT(*) FROM views v WHERE v.prompt_id = p.id) AS views_count FROM prompts p WHERE p.user_id = :user_id ORDER BY p.created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function userSaved(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT p.* FROM prompts p JOIN saves s ON s.prompt_id = p.id WHERE s.user_id = :user_id ORDER BY s.created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function userLiked(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT p.* FROM prompts p JOIN likes l ON l.prompt_id = p.id WHERE l.user_id = :user_id ORDER BY l.created_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function analytics(): array
    {
        return [
            'total_prompts' => (int) $this->db->query('SELECT COUNT(*) FROM prompts')->fetchColumn(),
            'approved_prompts' => (int) $this->db->query('SELECT COUNT(*) FROM prompts WHERE status_id = 2')->fetchColumn(),
            'pending_prompts' => (int) $this->db->query('SELECT COUNT(*) FROM prompts WHERE status_id = 1')->fetchColumn(),
            'total_views' => (int) $this->db->query('SELECT COUNT(*) FROM views')->fetchColumn(),
            'total_likes' => (int) $this->db->query('SELECT COUNT(*) FROM likes')->fetchColumn(),
        ];
    }
}
