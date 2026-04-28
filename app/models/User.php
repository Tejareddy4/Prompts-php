<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByGoogleId(string $googleId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.name AS role_name
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.google_id = :google_id LIMIT 1'
        );
        $stmt->execute(['google_id' => $googleId]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role_id, created_at)
             VALUES (:name, :email, :password_hash, 1, NOW())'
        );
        $stmt->execute([
            'name'          => $name,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function createFromGoogle(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, google_id, avatar_url, role_id, created_at)
             VALUES (:name, :email, :google_id, :avatar_url, 1, NOW())'
        );
        $stmt->execute([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'google_id'  => $data['google_id'],
            'avatar_url' => $data['avatar_url'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateGoogleId(int $userId, string $googleId, ?string $avatarUrl): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET google_id = :google_id,
             avatar_url = COALESCE(avatar_url, :avatar_url)
             WHERE id = :id'
        );
        $stmt->execute(['google_id' => $googleId, 'avatar_url' => $avatarUrl, 'id' => $userId]);
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT u.id, u.name, u.email, u.avatar_url, u.google_id,
                    u.is_banned, u.created_at, r.name AS role_name,
                    (SELECT COUNT(*) FROM prompts p WHERE p.user_id = u.id) AS prompt_count
             FROM users u
             JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC'
        )->fetchAll();
    }

    public function stats(): array
    {
        return [
            'total'        => (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'today'        => (int) $this->db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
            'this_week'    => (int) $this->db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
            'google_oauth' => (int) $this->db->query("SELECT COUNT(*) FROM users WHERE google_id IS NOT NULL")->fetchColumn(),
        ];
    }

    public function ban(int $userId, bool $ban): void
    {
        $stmt = $this->db->prepare('UPDATE users SET is_banned = :ban WHERE id = :id');
        $stmt->execute(['ban' => $ban ? 1 : 0, 'id' => $userId]);
    }

    public function setRole(int $userId, int $roleId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET role_id = :role_id WHERE id = :id');
        $stmt->execute(['role_id' => $roleId, 'id' => $userId]);
    }

    public function delete(int $userId): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public function recentSignups(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.name, u.email, u.avatar_url, u.google_id, u.created_at, r.name AS role_name
             FROM users u JOIN roles r ON r.id = u.role_id
             ORDER BY u.created_at DESC LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function growthByDay(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT DATE(created_at) AS day, COUNT(*) AS count
             FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY DATE(created_at)
             ORDER BY day ASC'
        );
        $stmt->bindValue(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
