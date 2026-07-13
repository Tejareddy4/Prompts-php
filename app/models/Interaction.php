<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Interaction extends Model
{
    public function toggleLike(int $promptId, int $userId): array
    {
        $stmt = $this->db->prepare('SELECT id FROM likes WHERE prompt_id = :promptId AND user_id = :userId');
        $stmt->execute(compact('promptId', 'userId'));
        $exists = $stmt->fetch();

        if ($exists) {
            $delete = $this->db->prepare('DELETE FROM likes WHERE id = :id');
            $delete->execute(['id' => $exists['id']]);
            $liked = false;
        } else {
            $insert = $this->db->prepare('INSERT INTO likes (prompt_id, user_id, created_at) VALUES (:promptId, :userId, NOW())');
            $insert->execute(compact('promptId', 'userId'));
            $liked = true;
        }

        return ['liked' => $liked, 'count' => $this->count('likes', $promptId)];
    }

    public function toggleSave(int $promptId, int $userId): array
    {
        $stmt = $this->db->prepare('SELECT id FROM saves WHERE prompt_id = :promptId AND user_id = :userId');
        $stmt->execute(compact('promptId', 'userId'));
        $exists = $stmt->fetch();

        if ($exists) {
            $delete = $this->db->prepare('DELETE FROM saves WHERE id = :id');
            $delete->execute(['id' => $exists['id']]);
            $saved = false;
        } else {
            $insert = $this->db->prepare('INSERT INTO saves (prompt_id, user_id, created_at) VALUES (:promptId, :userId, NOW())');
            $insert->execute(compact('promptId', 'userId'));
            $saved = true;
        }

        return ['saved' => $saved, 'count' => $this->count('saves', $promptId)];
    }

    public function addCopy(int $promptId, ?int $userId): int
    {
        $stmt = $this->db->prepare('INSERT INTO copies (prompt_id, user_id, created_at) VALUES (:promptId, :userId, NOW())');
        $stmt->execute(['promptId' => $promptId, 'userId' => $userId]);
        return $this->count('copies', $promptId);
    }

    public function addView(int $promptId, string $sessionHash, ?int $userId): int
    {
        $check = $this->db->prepare('SELECT id FROM views WHERE prompt_id = :promptId AND session_hash = :sessionHash LIMIT 1');
        $check->execute(compact('promptId', 'sessionHash'));
        if (!$check->fetch()) {
            $stmt = $this->db->prepare('INSERT INTO views (prompt_id, user_id, session_hash, created_at) VALUES (:promptId, :userId, :sessionHash, NOW())');
            $stmt->execute(['promptId' => $promptId, 'userId' => $userId, 'sessionHash' => $sessionHash]);
        }
        return $this->count('views', $promptId);
    }

    /**
     * The user's most-visited categories, weighted by how strong each signal is
     * (save 3 > like/copy 2 > view 1). Returns [category_id => weight 0..1],
     * strongest first — the input for "For You" feed personalization.
     */
    public function topCategories(int $userId, int $limit = 3): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.category_id, SUM(i.w) AS weight
             FROM (
                 SELECT prompt_id, 1 AS w FROM views  WHERE user_id = :uid_v
                 UNION ALL SELECT prompt_id, 2 FROM likes  WHERE user_id = :uid_l
                 UNION ALL SELECT prompt_id, 3 FROM saves  WHERE user_id = :uid_s
                 UNION ALL SELECT prompt_id, 2 FROM copies WHERE user_id = :uid_c
             ) i
             JOIN prompts p ON p.id = i.prompt_id
             WHERE p.category_id IS NOT NULL
             GROUP BY p.category_id
             ORDER BY weight DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':uid_v', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':uid_l', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':uid_s', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':uid_c', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!$rows) {
            return [];
        }
        $max = (float) $rows[0]['weight'];
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['category_id']] = round((float) $row['weight'] / $max, 3);
        }
        return $map;
    }

    private function count(string $table, int $promptId): int
    {
        static $allowed = ['likes', 'saves', 'copies', 'views'];
        if (!in_array($table, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid interaction table: {$table}");
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE prompt_id = :promptId");
        $stmt->execute(['promptId' => $promptId]);
        return (int) $stmt->fetchColumn();
    }
}
