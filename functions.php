<?php
// functions.php
session_start([
    'cookie_httponly' => true,
    // 'cookie_secure' => true, // enable if using HTTPS
    'cookie_samesite' => 'Lax',
]);

// Path to token store (selector -> [username, hashed_validator, expires])
define('TOKEN_STORE', __DIR__ . '/tokens.json');

// Ensure token store exists
if (!file_exists(TOKEN_STORE)) {
    file_put_contents(TOKEN_STORE, json_encode([]));
}

function secure_session_start() {
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = time();
    }
}

function end_session_and_cookies() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    // Remove remember-me cookie
    setcookie('rememberme', '', time() - 3600, "/", "", false, true);
}

function set_remember_me_cookie($username, $days = 30) {
    $selector = bin2hex(random_bytes(8));
    $validator = bin2hex(random_bytes(32));
    $expires = time() + (86400 * $days);

    $tokens = json_decode(file_get_contents(TOKEN_STORE), true);
    $tokens[$selector] = [
        'username' => $username,
        'validator_hash' => password_hash($validator, PASSWORD_DEFAULT),
        'expires' => $expires
    ];
    file_put_contents(TOKEN_STORE, json_encode($tokens));

    $cookie_value = $selector . ':' . $validator;
    setcookie('rememberme', $cookie_value, $expires, "/", "", false, true);
}

function clear_remember_me_token($selector) {
    $tokens = json_decode(file_get_contents(TOKEN_STORE), true);
    if (isset($tokens[$selector])) {
        unset($tokens[$selector]);
        file_put_contents(TOKEN_STORE, json_encode($tokens));
    }
}

function validate_remember_me() {
    if (empty($_COOKIE['rememberme'])) return false;
    $parts = explode(':', $_COOKIE['rememberme']);
    if (count($parts) !== 2) return false;

    list($selector, $validator) = $parts;
    $tokens = json_decode(file_get_contents(TOKEN_STORE), true);
    if (!isset($tokens[$selector])) return false;

    $entry = $tokens[$selector];
    if ($entry['expires'] < time()) {
        clear_remember_me_token($selector);
        return false;
    }

    if (password_verify($validator, $entry['validator_hash'])) {
        $_SESSION['user'] = $entry['username'];
        clear_remember_me_token($selector);
        set_remember_me_cookie($entry['username']);
        session_regenerate_id(true);
        return true;
    } else {
        clear_remember_me_token($selector);
        return false;
    }
}
