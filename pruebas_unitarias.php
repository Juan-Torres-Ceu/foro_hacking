<?php
require_once __DIR__ . '/index.php'; // ajusta al archivo donde tengas generarTokenApi, etc.

$ok = 0;
$fail = 0;

function assertTrue($cond, $msg) {
    global $ok, $fail;
    if ($cond) {
        echo "[OK] $msg\n";
        $ok++;
    } else {
        echo "[FAIL] $msg\n";
        $fail++;
    }
}

// -------- PRUEBAS UNITARIAS --------

// 1. Hash + PEPPER
$pass = 'miPassword123';
$pepper = 'MI_PEPPER_SECRETO';  // igual que en tu código

$peppered = hash_hmac('sha256', $pass, $pepper);
$hash = password_hash($peppered, PASSWORD_DEFAULT);

assertTrue($hash !== $pass, 'El hash no es igual al password en texto plano');

$pepperedLogin = hash_hmac('sha256', $pass, $pepper);
assertTrue(password_verify($pepperedLogin, $hash), 'Verificación de password con PEPPER');

// 2. Token API
$token1 = generarTokenApi(16);
$token2 = generarTokenApi(16);

assertTrue(strlen($token1) === 32, 'Token de 16 bytes tiene 32 caracteres hex');
assertTrue($token1 !== $token2, 'Dos tokens generados son distintos');

// -------- RESUMEN --------
echo "\nResumen: $ok OK, $fail FAIL\n";

if ($fail > 0) {
    exit(1);
}
