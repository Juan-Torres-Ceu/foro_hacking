<?php
// ===============================================
// pruebas_unitarias.php
// Script de pruebas unitarias manuales sin BD,
// compatible con Docker y GitHub Actions.
// ===============================================

// Función auxiliar para generar tokens de API (igual que en tu proyecto)
function generarTokenApi(int $bytes = 16): string {
    return bin2hex(random_bytes($bytes));
}

// Contadores de resultados
$ok = 0;
$fail = 0;

// Función de aserción básica
function assertTrue($cond, string $msg): void {
    global $ok, $fail;

    if ($cond) {
        echo "[OK] $msg\n";
        $ok++;
    } else {
        echo "[FAIL] $msg\n";
        $fail++;
    }
}

// ===============================================
// PRUEBAS UNITARIAS
// ===============================================

// 1. Hash + PEPPER
$pass   = 'miPassword123';
$pepper = 'MI_PEPPER_SECRETO';  // usa el mismo formato que en tu código real

$peppered = hash_hmac('sha256', $pass, $pepper);
$hash     = password_hash($peppered, PASSWORD_DEFAULT);

assertTrue($hash !== $pass, 'El hash no es igual al password en texto plano');

$pepperedLogin = hash_hmac('sha256', $pass, $pepper);
assertTrue(
    password_verify($pepperedLogin, $hash),
    'Verificación de password con PEPPER'
);

// 2. Token API
$token1 = generarTokenApi(16);
$token2 = generarTokenApi(16);

assertTrue(strlen($token1) === 32, 'Token de 16 bytes tiene 32 caracteres hex');
assertTrue($token1 !== $token2, 'Dos tokens generados son distintos');

// ===============================================
// RESUMEN
// ===============================================

echo "\nResumen: $ok OK, $fail FAIL\n";

if ($fail > 0) {
    exit(1); // hace fallar el job de GitHub Actions si algo falla
}
