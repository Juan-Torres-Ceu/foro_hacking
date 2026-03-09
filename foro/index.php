<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Security-Policy: default-src 'self' https://cdn.tailwindcss.com https://fonts.googleapis.com https://fonts.gstatic.com; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;");

// Conexión BD
$conexion = new mysqli('db', 'foro', 'foro', 'db');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$PEPPER = "pon_aqui_un_pepper_secreto_y_largo";

function generarTokenApi($longitud = 32) {
    return bin2hex(random_bytes($longitud));
}

$pantalla = isset($_GET['pantalla']) ? $_GET['pantalla'] : 'login';

// BORRADO DE MENSAJES (solo admin)
if (isset($_GET['delete_id']) && isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    $id = (int)$_GET['delete_id'];

    $res = $conexion->query("SELECT archivo FROM mensajes WHERE id = $id");
    if ($res && $res->num_rows) {
        $row = $res->fetch_assoc();
        if (!empty($row['archivo']) && file_exists($row['archivo'])) {
            @unlink($row['archivo']);
        }
    }
    $conexion->query("DELETE FROM mensajes WHERE id = $id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// REGISTRO
if (isset($_POST['reg_nick']) && isset($_POST['reg_pass'])) {
    $nick = $conexion->real_escape_string($_POST['reg_nick']);
    $pass = $_POST['reg_pass'];

    // Hash seguro con pepper
    $peppered  = hash_hmac("sha256", $pass, $PEPPER);
    $pass_hash = password_hash($peppered, PASSWORD_DEFAULT);

    // Generar token API para este usuario
    $api_token = generarTokenApi(32);

    // Comprobar si el nick ya existe
    $exists = $conexion->query("SELECT id FROM usuarios WHERE nick='$nick'");
    if ($exists && $exists->num_rows) {
        $error    = "Ese usuario ya existe.";
        $pantalla = 'register';
    } else {
        // Crear usuario normal con rol 'user' y api_token
        $sql = "
            INSERT INTO usuarios (nick, password, rol, api_token)
            VALUES ('$nick', '$pass_hash', 'user', '$api_token')
        ";
        $conexion->query($sql);

        if ($conexion->error) {
            $error    = "Error al registrar usuario: " . $conexion->error;
            $pantalla = 'register';
        } else {
            $success  = "Usuario registrado. Ahora puedes iniciar sesión.";
            $pantalla = 'login';
        }
    }
}

// LOGIN
if (isset($_POST['login_nick']) && isset($_POST['login_pass'])) {
    $nick = $conexion->real_escape_string($_POST['login_nick']);
    $pass = $_POST['login_pass'];

    $res = $conexion->query("SELECT id, password, rol FROM usuarios WHERE nick='$nick'");
    if ($res && $res->num_rows) {
        $user = $res->fetch_assoc();
        $peppered = hash_hmac("sha256", $pass, $PEPPER);
        if (password_verify($peppered, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nick']       = $nick;
            $_SESSION['rol']        = $user['rol'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "Usuario no existe.";
    }
}

// LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// MENSAJE + SUBIDA DE ARCHIVO
if (isset($_POST['msg']) && isset($_SESSION['usuario_id'])) {
    $usuario_id = (int)$_SESSION['usuario_id'];
    $msg = $conexion->real_escape_string($_POST['msg']);
    $nombre_archivo = null;
    $tipo_archivo   = null;

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == UPLOAD_ERR_OK) {
        $tmp      = $_FILES['archivo']['tmp_name'];
        $original = basename($_FILES['archivo']['name']);
        $ext      = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','gif','mp4','webm','mov'];

        if (in_array($ext, $permitidos)) {
            if (!is_dir('uploads')) mkdir('uploads');
            $destino = "uploads/" . uniqid() . "_" . preg_replace('/[^a-zA-Z0-9_.-]/', '', $original);
            move_uploaded_file($tmp, $destino);
            $nombre_archivo = $destino;
            $tipo_archivo   = in_array($ext, ['mp4','webm','mov']) ? "video" : "imagen";
        }
    }

    $sql = "INSERT INTO mensajes (usuario_id, mensaje, archivo, tipo) VALUES ($usuario_id, '$msg', " .
           ($nombre_archivo ? "'$nombre_archivo'" : "NULL") . ", " .
           ($tipo_archivo   ? "'$tipo_archivo'"   : "NULL") . ")";
    $conexion->query($sql);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// clase de fondo distinta para admin
$bodyClass = "bg-[#0b0b0c]";
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    $bodyClass = "bg-red-950";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>
    <?php
      if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
          echo "Foro Hacking - ADMIN";
      } else {
          echo "Foro Hacking";
      }
    ?>
  </title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: "Inter", sans-serif; }
    .glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
  </style>
</head>
<body class="<?php echo $bodyClass; ?> text-gray-200 min-h-screen">
<div class="max-w-lg mx-auto py-12 px-4">
  <h1 class="text-center text-4xl font-extrabold mb-10 tracking-tight
             bg-gradient-to-r from-indigo-400 to-fuchsia-500 bg-clip-text text-transparent select-none">
    <?php
      if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
          echo "Foro Hacking (Administrador)";
      } else {
          echo "Foro Hacking";
      }
    ?>
  </h1>

  <?php if (!isset($_SESSION['usuario_id'])): ?>
    <?php if ($pantalla == 'login'): ?>
      <?php if (isset($error)): ?>
        <div class="glass p-3 mb-4 text-red-300 border border-red-500/40 rounded-md text-center"><?php echo $error; ?></div>
      <?php endif; ?>
      <?php if (isset($success)): ?>
        <div class="glass p-3 mb-4 text-green-300 border border-green-500/40 rounded-md text-center"><?php echo $success; ?></div>
      <?php endif; ?>
      <form method="POST" class="glass p-6 rounded-xl space-y-5 shadow-xl">
        <h2 class="text-xl font-semibold text-center text-gray-100">Iniciar sesión</h2>
        <input name="login_nick" placeholder="Usuario"
          class="w-full p-3 rounded-md bg-white/5 border border-white/10 text-gray-200 placeholder-gray-400
                 focus:outline-none focus:border-fuchsia-500"/>
        <input type="password" name="login_pass" placeholder="Contraseña"
          class="w-full p-3 rounded-md bg-white/5 border border-white/10 text-gray-200 placeholder-gray-400
                 focus:outline-none focus:border-indigo-500"/>
        <button class="w-full py-3 rounded-md bg-gradient-to-r from-indigo-500 to-fuchsia-500 font-semibold text-white shadow-lg hover:opacity-90 transition">
          Entrar
        </button>
        <p class="text-center text-sm text-gray-400">
          ¿No tienes cuenta?
          <a href="?pantalla=register" class="text-fuchsia-400 hover:text-fuchsia-200 underline">Regístrate</a>
        </p>
      </form>
    <?php elseif ($pantalla == 'register'): ?>
      <?php if (isset($error)): ?>
        <div class="glass p-3 mb-4 text-red-300 border border-red-500/40 rounded-md text-center"><?php echo $error; ?></div>
      <?php endif; ?>
      <form method="POST" class="glass p-6 rounded-xl shadow-xl space-y-5">
        <h2 class="text-xl font-semibold text-center text-gray-100">Crear cuenta</h2>
        <input name="reg_nick" placeholder="Usuario"
          class="w-full p-3 rounded-md bg-white/5 border border-white/10 text-gray-200 placeholder-gray-400
                 focus:outline-none focus:border-fuchsia-500"/>
        <input type="password" name="reg_pass" placeholder="Contraseña"
          class="w-full p-3 rounded-md bg-white/5 border border-white/10 text-gray-200 placeholder-gray-400
                 focus:outline-none focus:border-indigo-500"/>
        <button class="w-full py-3 rounded-md bg-gradient-to-r from-fuchsia-500 to-purple-600 font-semibold text-white shadow-lg hover:opacity-90 transition">
          Registrar
        </button>
        <p class="text-center text-sm text-gray-400">
          ¿Ya tienes cuenta?
          <a href="?pantalla=login" class="text-indigo-400 hover:text-indigo-200 underline">Inicia sesión</a>
        </p>
      </form>
    <?php endif; ?>
  <?php else: ?>
    <div class="glass flex justify-between items-center px-4 py-2 mb-6 rounded-md border border-white/10">
      <span class="text-gray-300">
        Bienvenido
        <span class="text-indigo-400 font-semibold">
          <?php echo htmlspecialchars($_SESSION['nick']); ?>
        </span>
        <?php if ($_SESSION['rol'] === 'admin'): ?>
          <span class="ml-2 px-2 py-1 text-xs rounded bg-red-600 text-white font-bold">ADMIN</span>
        <?php endif; ?>
      </span>
      <a href="?logout=1" class="text-fuchsia-300 hover:text-fuchsia-100 font-semibold">Salir</a>
    </div>
    <form method="POST" enctype="multipart/form-data" class="glass p-6 rounded-xl shadow-xl space-y-4 border border-white/10">
      <h2 class="text-xl font-semibold text-center text-gray-100">Nuevo mensaje</h2>
      <textarea name="msg" placeholder="Comparte payloads, ideas o exploits..."
        class="w-full p-3 rounded-md bg-white/5 border border-white/10 text-gray-200 placeholder-gray-400
               focus:outline-none focus:border-indigo-500" required></textarea>
      <input type="file" name="archivo" accept="image/*,video/*"
        class="w-full p-2 rounded bg-white/10 border border-fuchsia-500 text-gray-200"/>
      <button class="w-full py-3 rounded-md bg-gradient-to-r from-fuchsia-500 to-purple-600 font-semibold text-white shadow-lg hover:opacity-90 transition">
        Publicar
      </button>
    </form>
  <?php endif; ?>

  <div id="mensajes" class="mt-10 space-y-4"></div>
</div>

<script>
let ultimoIdMensaje = 0;

if ("Notification" in window && Notification.permission === "default") {
  Notification.requestPermission();
}

function cargarMensajes() {
  fetch('mensajes.php')
    .then(res => res.text())
    .then(html => {
      document.getElementById('mensajes').innerHTML = html;

      const primerMensaje = document.querySelector('#mensajes > div');
      if (primerMensaje) {
        const nuevoId = primerMensaje.getAttribute('data-id');

        if (nuevoId && nuevoId != ultimoIdMensaje && ultimoIdMensaje !== 0 && document.hidden) {
          const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBypz0PLWhjMGHGq+7+OZSA0PWw==');
          audio.play().catch(() => {});

          if ("Notification" in window && Notification.permission === "granted") {
            const nick  = primerMensaje.querySelector('.font-semibold').textContent;
            const texto = primerMensaje.querySelector('.leading-relaxed').textContent.substring(0, 50);

            new Notification('💀 Nuevo mensaje en Foro Hacking', {
              body: nick + ': ' + texto + '...',
              icon: 'https://cdn-icons-png.flaticon.com/512/6124/6124991.png'
            });
          }
        }

        ultimoIdMensaje = nuevoId;
      }
    });
}

cargarMensajes();
setInterval(cargarMensajes, 5000);
</script>
</body>
</html>
