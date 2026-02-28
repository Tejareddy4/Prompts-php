<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role_id, created_at) VALUES (:name, :email, :password_hash, 1, NOW())');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query('SELECT u.id, u.name, u.email, r.name AS role_name, u.created_at FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.created_at DESC')->fetchAll();
    }
}
