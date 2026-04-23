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
        $sql = 'SELECT p.*, u.name AS author, u.username AS author_username,
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
            // Use FULLTEXT if index exists, fall back to LIKE otherwise
            $sql .= ' AND MATCH(p.title, p.description, p.prompt_text) AGAINST (:search IN BOOLEAN MODE)';
            $params['search'] = $this->fulltextQuery($filters['q']);
        }

        $sortBy = $filters['sort'] ?? 'newest';
        $orderBy = match ($sortBy) {
            'most_liked'  => 'likes_count DESC, p.created_at DESC',
            'most_saved'  => 'saves_count DESC, p.created_at DESC',
            'most_viewed' => 'views_count DESC, p.created_at DESC',
            'trending'    => 'p.trending_score DESC, p.created_at DESC',
            default       => 'p.created_at DESC',
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
        $sql = 'SELECT p.*, u.name AS author, u.username AS author_username,
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

    public function findByIdForUser(int $promptId, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM prompts WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $promptId, 'user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $promptId, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE prompts SET title = :title, description = :description, prompt_text = :prompt_text, image_path = :image_path, status_id = 1, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'],
            'prompt_text' => $data['prompt_text'],
            'image_path' => $data['image_path'],
            'id' => $promptId,
        ]);
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

    public function approvedByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*,
                (SELECT COUNT(*) FROM likes l   WHERE l.prompt_id = p.id) AS likes_count,
                (SELECT COUNT(*) FROM saves s   WHERE s.prompt_id = p.id) AS saves_count,
                (SELECT COUNT(*) FROM copies c  WHERE c.prompt_id = p.id) AS copies_count,
                (SELECT COUNT(*) FROM views v   WHERE v.prompt_id = p.id) AS views_count
             FROM prompts p
             WHERE p.user_id = :user_id AND p.status_id = 2
             ORDER BY p.created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function refreshTrendingScore(int $promptId): void
    {
        // Score = likes*3 + saves*2 + copies*1.5 + views*0.1, decaying by age in days
        $this->db->prepare(
            'UPDATE prompts SET trending_score = (
                (SELECT COUNT(*) FROM likes   WHERE prompt_id = :id) * 3   +
                (SELECT COUNT(*) FROM saves   WHERE prompt_id = :id) * 2   +
                (SELECT COUNT(*) FROM copies  WHERE prompt_id = :id) * 1.5 +
                (SELECT COUNT(*) FROM views   WHERE prompt_id = :id) * 0.1
             ) / POW(GREATEST(1, DATEDIFF(NOW(), created_at)), 0.8)
             WHERE id = :id'
        )->execute(['id' => $promptId]);
    }

    private function fulltextQuery(string $q): string
    {
        // Build a safe boolean mode query: each word gets a + prefix for AND logic
        $words = preg_split('/\s+/', trim($q));
        $safe  = array_map(fn($w) => '+' . preg_replace('/[^\w\-]/u', '', $w), $words);
        $query = implode(' ', array_filter($safe, fn($w) => strlen($w) > 1));
        return $query ?: '+' . preg_replace('/[^\w\-]/u', '', $q);
    }
}
