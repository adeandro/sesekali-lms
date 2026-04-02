<?php
/**
 * Lightweight Battle Arena Status Endpoint
 * Bypass Laravel bootstrap — pure PHP + PDO
 * Memory: ~2-3MB vs ~25MB Laravel request
 */

// Hanya terima request AJAX
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    http_response_code(403);
    exit;
}

// Baca session Laravel dari cookie
$sessionId = $_COOKIE['laravel_session'] ?? null;
if (!$sessionId) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$roomId = (int) ($_GET['room'] ?? 0);
if (!$roomId) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid room']);
    exit;
}

$appDir = dirname(__DIR__);

try {
    // ── 1. Koneksi DB + Validasi session ─────────
    // SESSION_DRIVER=database — baca dari tabel sessions
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;'
        . 'dbname=almabru2_sesekali_lms;charset=utf8mb4',
        'almabru2_sesekali_lms',
        '4lm4brurc0nn3ct',
        [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT    => 3,
            PDO::ATTR_PERSISTENT => false,
        ]
    );

    $stmt = $pdo->prepare(
        'SELECT user_id, payload FROM sessions
         WHERE id = ?
         LIMIT 1'
    );
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    $userId = null;

    if ($session && $session['user_id']) {
        // user_id langsung tersedia di kolom sessions
        $userId = (int) $session['user_id'];
    } elseif ($session && $session['payload']) {
        // Fallback: parse dari payload jika user_id null
        $decoded = base64_decode($session['payload']);
        if ($decoded && preg_match(
            '/"login_web_[^"]*";i:(\d+)/',
            $decoded, $m
        )) {
            $userId = (int) $m[1];
        }
    }

    if (!$userId) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Session invalid']);
        exit;
    }

    // ── 2. Ambil status room dari file cache ─────
    // Reuse cache file Laravel (format: sha1 key)
    $cacheKey  = 'battle_room_status_' . $roomId;
    $sha1Key   = sha1($cacheKey);
    $cacheFile = $appDir . '/storage/framework/cache/data/'
        . substr($sha1Key, 0, 2) . '/'
        . substr($sha1Key, 2, 2) . '/'
        . $sha1Key;

    $status = null;

    // Baca cache jika ada dan belum expired (30 detik)
    if (file_exists($cacheFile)) {
        $raw     = file_get_contents($cacheFile);
        $expiry  = unpack('N', substr($raw, 0, 4))[1];
        if (time() < $expiry) {
            $payload = @unserialize(substr($raw, 4));
            if ($payload && isset($payload['data'])) {
                $status = @unserialize($payload['data']);
            }
        }
    }

    // Cache miss atau expired → ambil dari DB
    if (!$status) {
        $stmt = $pdo->prepare(
            'SELECT status FROM battle_rooms
             WHERE id = ? AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute([$roomId]);
        $status = $stmt->fetchColumn() ?: 'unknown';

        // Tulis ke cache (TTL 30 detik)
        // Format sama dengan Laravel file cache
        $expiry    = time() + 30;
        $payload   = serialize(['data' => serialize($status)]);
        $cacheData = pack('N', $expiry) . $payload;
        @mkdir(dirname($cacheFile), 0755, true);
        @file_put_contents($cacheFile, $cacheData, LOCK_EX);
    }

    // ── 4. Ambil participant_id ──────────────────
    // Cache participant_id per user (jarang berubah)
    $pKey     = 'bp_id_' . $userId . '_' . $roomId;
    $sha1P    = sha1($pKey);
    $pFile    = $appDir . '/storage/framework/cache/data/'
        . substr($sha1P, 0, 2) . '/'
        . substr($sha1P, 2, 2) . '/'
        . $sha1P;

    $participantId = null;

    if (file_exists($pFile)) {
        $raw    = file_get_contents($pFile);
        $expiry = unpack('N', substr($raw, 0, 4))[1];
        if (time() < $expiry) {
            $payload = @unserialize(substr($raw, 4));
            if ($payload && isset($payload['data'])) {
                $participantId = @unserialize($payload['data']);
            }
        }
    }

    if (!$participantId) {
        $stmt = $pdo->prepare(
            'SELECT id FROM battle_participants
             WHERE battle_room_id = ? AND user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$roomId, $userId]);
        $participantId = $stmt->fetchColumn() ?: null;

        // Cache participant_id selama 5 menit
        if ($participantId) {
            $expiry    = time() + 300;
            $payload   = serialize(['data' => serialize($participantId)]);
            $cacheData = pack('N', $expiry) . $payload;
            @mkdir(dirname($pFile), 0755, true);
            @file_put_contents($pFile, $cacheData, LOCK_EX);
        }
    }

    // ── 5. Throttle last_seen_at ─────────────────
    // Update last_seen_at max 1x per 10 detik per user
    // Gunakan file lock sederhana di /tmp
    $lockFile = sys_get_temp_dir()
        . '/arena_seen_' . $userId . '_' . $roomId;

    if (!file_exists($lockFile)
        || (time() - filemtime($lockFile)) > 10
    ) {
        if ($participantId) {
            $stmt = $pdo->prepare(
                'UPDATE battle_participants
                 SET last_seen_at = NOW()
                 WHERE id = ?'
            );
            $stmt->execute([$participantId]);
        }
        @file_put_contents($lockFile, '1');
    }

    // ── 6. Response ──────────────────────────────
    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache');
    echo json_encode([
        'status'         => $status,
        'participant_id' => $participantId,
    ]);

} catch (PDOException $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'DB error']);
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Server error']);
}
