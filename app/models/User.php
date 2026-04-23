<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.name AS role_name FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.name AS role_name FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.name AS role_name FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $username = $this->generateUsername($name);

        $stmt = $this->db->prepare(
            'INSERT INTO users (name, username, email, password_hash, role_id, created_at)
             VALUES (:name, :username, :email, :password_hash, 1, NOW())'
        );
        $stmt->execute([
            'name'          => $name,
            'username'      => $username,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT u.id, u.name, u.username, u.email, r.name AS role_name, u.created_at
             FROM users u JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC'
        )->fetchAll();
    }

    public function stats(int $userId): array
    {
        $row = $this->db->prepare(
            'SELECT
                (SELECT COUNT(*) FROM prompts   WHERE user_id = :id AND status_id = 2)         AS prompt_count,
                (SELECT COUNT(*) FROM likes l   JOIN prompts p ON p.id = l.prompt_id WHERE p.user_id = :id) AS likes_received,
                (SELECT COUNT(*) FROM saves s   JOIN prompts p ON p.id = s.prompt_id WHERE p.user_id = :id) AS saves_received,
                (SELECT COUNT(*) FROM views v   JOIN prompts p ON p.id = v.prompt_id WHERE p.user_id = :id) AS views_received'
        );
        $row->execute(['id' => $userId]);
        return $row->fetch() ?: ['prompt_count' => 0, 'likes_received' => 0, 'saves_received' => 0, 'views_received' => 0];
    }

    private function generateUsername(string $name): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($name)));
        $base = trim($base, '_');
        if ($base === '') {
            $base = 'user';
        }

        $candidate = $base;
        $i = 1;
        while ($this->usernameExists($candidate)) {
            $candidate = $base . '_' . $i++;
        }
        return $candidate;
    }

    private function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        return (bool) $stmt->fetch();
    }
}
