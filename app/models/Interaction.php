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

    private function count(string $table, int $promptId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE prompt_id = :promptId");
        $stmt->execute(['promptId' => $promptId]);
        return (int) $stmt->fetchColumn();
    }
}
