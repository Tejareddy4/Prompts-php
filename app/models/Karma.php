<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Two-tier karma, computed live from the interaction tables (no migration).
 *
 * PUBLIC karma — community reputation shown on the profile:
 *   +10 per approved prompt
 *   +3  per like received (self-likes excluded)
 *   +5  per save received
 *   +2  per copy received
 *   +1  per 10 views received
 *   +1  per like given to other creators (curation, capped at +50)
 *   +25 per featured prompt
 *
 * PRIVATE karma — trust score 0–100, visible to admins only:
 *   50 baseline
 *   +2 per approved prompt (capped at +20)
 *   -8 per rejected prompt (capped at -40)
 *   +10 approval ratio ≥ 80% over 5+ reviewed submissions
 *   -10 approval ratio < 40% over 3+ reviewed submissions
 *   -10 account younger than 7 days · +10 older than 90 days
 *   -2  per self-like on own prompts (capped at -20)
 *   -10 more than 10 prompts sitting in the pending queue (flooding)
 *   =0  banned accounts
 */
class Karma extends Model
{
    public const LEVELS = [
        [1000, 'Legend',       'pink'],
        [500,  'Expert',       'orange'],
        [200,  'Rising Star',  'violet'],
        [50,   'Contributor',  'blue'],
        [0,    'Newcomer',     'gray'],
    ];

    public const BANDS = [
        [60, 'Trusted',   'green'],
        [30, 'Watch',     'orange'],
        [0,  'High risk', 'red'],
    ];

    public function forUser(int $userId): ?array
    {
        $all = $this->compute($userId);
        return $all[$userId] ?? null;
    }

    /** Map of user_id => karma, for the admin users table. */
    public function forAllUsers(): array
    {
        return $this->compute(null);
    }

    private function compute(?int $onlyUserId): array
    {
        $where  = $onlyUserId !== null ? 'WHERE u.id = :uid' : '';
        $params = $onlyUserId !== null ? ['uid' => $onlyUserId] : [];

        $stmt = $this->db->prepare(
            "SELECT u.id,
                    COALESCE(u.is_banned, 0)                                    AS is_banned,
                    DATEDIFF(NOW(), u.created_at)                               AS account_days,
                    COALESCE(SUM(p.status_id = 2), 0)                           AS approved,
                    COALESCE(SUM(p.status_id = 3), 0)                           AS rejected,
                    COALESCE(SUM(p.status_id = 1), 0)                           AS pending,
                    COALESCE(SUM(p.status_id = 2 AND COALESCE(p.is_featured,0) = 1), 0) AS featured
             FROM users u
             LEFT JOIN prompts p ON p.user_id = u.id
             {$where}
             GROUP BY u.id"
        );
        $stmt->execute($params);
        $base = [];
        foreach ($stmt->fetchAll() as $row) {
            $base[(int) $row['id']] = $row;
        }
        if (!$base) {
            return [];
        }

        $received  = $this->receivedCounts($onlyUserId);
        $given     = $this->groupedCount('SELECT l.user_id AS uid, COUNT(*) AS n FROM likes l JOIN prompts p ON p.id = l.prompt_id WHERE l.user_id != p.user_id', $onlyUserId, 'l.user_id');
        $selfLikes = $this->groupedCount('SELECT p.user_id AS uid, COUNT(*) AS n FROM likes l JOIN prompts p ON p.id = l.prompt_id WHERE l.user_id = p.user_id', $onlyUserId, 'p.user_id');

        $result = [];
        foreach ($base as $id => $row) {
            $r = $received[$id] ?? ['likes' => 0, 'saves' => 0, 'copies' => 0, 'views' => 0];
            $result[$id] = [
                'public'  => $this->publicScore($row, $r, $given[$id] ?? 0),
                'private' => $this->privateScore($row, $selfLikes[$id] ?? 0),
            ];
        }
        return $result;
    }

    private function receivedCounts(?int $onlyUserId): array
    {
        $tables = [
            'likes'  => 'SELECT p.user_id AS uid, COUNT(*) AS n FROM likes t  JOIN prompts p ON p.id = t.prompt_id WHERE t.user_id != p.user_id',
            'saves'  => 'SELECT p.user_id AS uid, COUNT(*) AS n FROM saves t  JOIN prompts p ON p.id = t.prompt_id WHERE 1=1',
            'copies' => 'SELECT p.user_id AS uid, COUNT(*) AS n FROM copies t JOIN prompts p ON p.id = t.prompt_id WHERE 1=1',
            'views'  => 'SELECT p.user_id AS uid, COUNT(*) AS n FROM views t  JOIN prompts p ON p.id = t.prompt_id WHERE 1=1',
        ];
        $map = [];
        foreach ($tables as $key => $sql) {
            foreach ($this->groupedCount($sql, $onlyUserId, 'p.user_id') as $uid => $n) {
                $map[$uid][$key] = $n;
            }
        }
        return array_map(
            fn($r) => $r + ['likes' => 0, 'saves' => 0, 'copies' => 0, 'views' => 0],
            $map
        );
    }

    private function groupedCount(string $baseSql, ?int $onlyUserId, string $uidColumn): array
    {
        $sql    = $baseSql . ($onlyUserId !== null ? " AND {$uidColumn} = :uid" : '') . " GROUP BY {$uidColumn}";
        $stmt   = $this->db->prepare($sql);
        $stmt->execute($onlyUserId !== null ? ['uid' => $onlyUserId] : []);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['uid']] = (int) $row['n'];
        }
        return $map;
    }

    private function publicScore(array $row, array $received, int $likesGiven): array
    {
        $breakdown = [
            'Approved prompts (+10 each)'        => (int) $row['approved'] * 10,
            'Likes received (+3 each)'           => (int) $received['likes'] * 3,
            'Saves received (+5 each)'           => (int) $received['saves'] * 5,
            'Copies received (+2 each)'          => (int) $received['copies'] * 2,
            'Views received (+1 per 10)'         => intdiv((int) $received['views'], 10),
            'Likes given / curation (max +50)'   => min($likesGiven, 50),
            'Featured prompts (+25 each)'        => (int) $row['featured'] * 25,
        ];
        $score = array_sum($breakdown);

        [$level, $color] = ['Newcomer', 'gray'];
        foreach (self::LEVELS as [$min, $name, $c]) {
            if ($score >= $min) { [$level, $color] = [$name, $c]; break; }
        }

        return ['score' => $score, 'level' => $level, 'color' => $color, 'breakdown' => $breakdown];
    }

    private function privateScore(array $row, int $selfLikes): array
    {
        $approved = (int) $row['approved'];
        $rejected = (int) $row['rejected'];
        $pending  = (int) $row['pending'];
        $days     = max(0, (int) $row['account_days']);
        $reviewed = $approved + $rejected;

        $breakdown = ['Baseline' => 50];
        $breakdown['Approved prompts (+2, max +20)'] = min($approved * 2, 20);
        $breakdown['Rejected prompts (-8, max -40)'] = -min($rejected * 8, 40);

        if ($reviewed >= 5 && $approved / $reviewed >= 0.8) {
            $breakdown['Approval ratio ≥ 80% (+10)'] = 10;
        } elseif ($reviewed >= 3 && $approved / $reviewed < 0.4) {
            $breakdown['Approval ratio < 40% (-10)'] = -10;
        }

        if ($days < 7) {
            $breakdown['New account < 7 days (-10)'] = -10;
        } elseif ($days > 90) {
            $breakdown['Established account > 90 days (+10)'] = 10;
        }

        if ($selfLikes > 0) {
            $breakdown['Self-likes (-2, max -20)'] = -min($selfLikes * 2, 20);
        }
        if ($pending > 10) {
            $breakdown['Pending queue flooding (-10)'] = -10;
        }

        $score = max(0, min(100, array_sum($breakdown)));
        if ((int) $row['is_banned'] === 1) {
            $breakdown['Banned (score forced to 0)'] = 0;
            $score = 0;
        }

        [$band, $color] = ['High risk', 'red'];
        foreach (self::BANDS as [$min, $name, $c]) {
            if ($score >= $min) { [$band, $color] = [$name, $c]; break; }
        }

        return ['score' => $score, 'band' => $band, 'color' => $color, 'breakdown' => $breakdown];
    }
}
