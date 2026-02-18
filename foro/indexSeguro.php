<?php
session_start();

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función para validar CSRF en cada POST
function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die("Solicitud no válida (CSRF).");
        }
    }
}

header("Content-Security-Policy: "
    . "default-src 'self' https://cdn.tailwindcss.com https://fonts.googleapis.com https://fonts.gstatic.com; "
    . "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
    . "font-src 'self' https://fonts.gstatic.com;");

$conexion = new mysqli("localhost", "foro", "foro", "foro_hacking");
$PEPPER = "pon_aquí_un_pepper_secreto_y_largo";
$pantalla = isset($_GET['pantalla']) ? $_GET['pantalla'] : 'login';

// REGISTRO
if (isset($_POST['reg_nick']) && isset($_POST['reg_pass'])) {
    $nick = $_POST['reg_nick'];
    $pass = $_POST['reg_pass'];

    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nick = ?");
    $stmt->bind_param("s", $nick);
    $stmt->execute();
    $exists = $stmt->get_result();

    if ($exists->num_rows) {
        $error = "Ese usuario ya existe.";
        $pantalla = 'register';
    } else {
        $peppered = hash_hmac("sha256", $pass, $PEPPER);
        $pass_hash = password_hash($peppered, PASSWORD_DEFAULT);

        $stmt_insert = $conexion->prepare(
            "INSERT INTO usuarios (nick, password) VALUES (?, ?)"
        );
        $stmt_insert->bind_param("ss", $nick, $pass_hash);
        $stmt_insert->execute();

        $success = "Usuario registrado. Ahora puedes iniciar sesión.";
        $pantalla = 'login';
    }
}

// LOGIN
if (isset($_POST['login_nick']) && isset($_POST['login_pass'])) {
    $nick = $_POST['login_nick'];
    $pass = $_POST['login_pass'];

    $stmt = $conexion->prepare("SELECT id, password FROM usuarios WHERE nick = ?");
    $stmt->bind_param("s", $nick);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows) {
        $user = $res->fetch_assoc();
        $peppered = hash_hmac("sha256", $pass, $PEPPER);

        if (password_verify($peppered, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['nick'] = $nick;
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
    check_csrf();

    $usuario_id = (int)$_SESSION['usuario_id'];
    $msg = $_POST['msg']; // no hace falta real_escape_string aquí
    $nombre_archivo = null;
    $tipo_archivo = null;

    // SUBIDA DE ARCHIVO SEGURA
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == UPLOAD_ERR_OK) {
        $tmp = $_FILES['archivo']['tmp_name'];
        $original = basename($_FILES['archivo']['name']);
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        $ext_permitidas = ['jpg','jpeg','png','gif','mp4','webm','mov'];
        if (in_array($ext, $ext_permitidas)) {

            // Comprobar MIME real del archivo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmp);
            finfo_close($finfo);

            $mime_permitidos = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'video/mp4'  => 'mp4',
                'video/webm' => 'webm',
                'video/quicktime' => 'mov'
            ];

            if (array_key_exists($mime, $mime_permitidos)) {
                $extension = $mime_permitidos[$mime];

                // Tamaño máximo 10 MB
                $max_size = 10 * 1024 * 1024;
                if ($_FILES['archivo']['size'] <= $max_size) {

                    if (!is_dir('uploads')) {
                        mkdir('uploads', 0755, true);
                    }

                    // Nombre totalmente aleatorio
                    $nombre_nuevo = bin2hex(random_bytes(16)) . '.' . $extension;
                    $destino = "uploads/" . $nombre_nuevo;

                    if (move_uploaded_file($tmp, $destino)) {
                        $nombre_archivo = $destino;
                        $tipo_archivo = in_array($extension, ['mp4','webm','mov']) ? "video" : "imagen";
                    }
                }
            }
        }
    }

    // INSERT CON PREPARED STATEMENT
    $stmt = $conexion->prepare(
        "INSERT INTO mensajes (usuario_id, mensaje, archivo, tipo)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("isss", $usuario_id, $msg, $nombre_archivo, $tipo_archivo);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Foro Hacking</title>
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
<body class="bg-[#0b0b0c] text-gray-200 min-h-screen">
<div class="max-w-lg mx-auto py-12 px-4">
  <h1 class="text-center text-4xl font-extrabold mb-10 tracking-tight
             bg-gradient-to-r from-indigo-400 to-fuchsia-500 bg-clip-text text-transparent select-none">
    Foro Hacking
  </h1>

  <?php if (!isset($_SESSION['usuario_id'])): ?>
    <?php if ($pantalla == 'login'): ?>
      <?php if (isset($error)): ?>
        <div class="glass p-3 mb-4 text-red-300 border border-red-500/40 rounded-md text-center">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>
      <?php if (isset($success)): ?>
        <div class="glass p-3 mb-4 text-green-300 border border-green-500/40 rounded-md text-center">
          <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="glass p-6 rounded-xl space-y-5 shadow-xl">
        <h2 class="text-xl font-semibold text-center text-gray-100">Iniciar sesión</h2>

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

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
        <div class="glass p-3 mb-4 text-red-300 border border-red-500/40 rounded-md text-center">
          <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="glass p-6 rounded-xl shadow-xl space-y-5">
        <h2 class="text-xl font-semibold text-center text-gray-100">Crear cuenta</h2>

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

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
          <?php echo htmlspecialchars($_SESSION['nick'], ENT_QUOTES, 'UTF-8'); ?>
        </span>
      </span>
      <a href="?logout=1" class="text-fuchsia-300 hover:text-fuchsia-100 font-semibold">Salir</a>
    </div>

    <form method="POST" enctype="multipart/form-data" class="glass p-6 rounded-xl shadow-xl space-y-4 border border-white/10">
      <h2 class="text-xl font-semibold text-center text-gray-100">Nuevo mensaje</h2>

      <input type="hidden" name="csrf_token"
             value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

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

  <!-- Zona para los mensajes (AJAX) -->
  <div id="mensajes" class="mt-10 space-y-4"></div>
</div>

<!-- JavaScript AJAX para refrescar solo los mensajes -->
<script>
let ultimoIdMensaje = 0;

// Pedir permiso de notificaciones al cargar
if ("Notification" in window && Notification.permission === "default") {
  Notification.requestPermission();
}

function cargarMensajes() {
  fetch('mensajes.php')
    .then(function(res){
      return res.text();
    })
    .then(function(html){
      document.getElementById('mensajes').innerHTML = html;

      const primerMensaje = document.querySelector('#mensajes > div');
      if (primerMensaje) {
        const nuevoId = primerMensaje.getAttribute('data-id');

        if (nuevoId && nuevoId != ultimoIdMensaje && ultimoIdMensaje !== 0 && document.hidden) {
          const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBypz0PLWhjMGHGq+7+OZSA0PWw==');
          audio.play().catch(() => {});

          if ("Notification" in window && Notification.permission === "granted") {
            const nick = primerMensaje.querySelector('.font-semibold').textContent;
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
setInterval(cargarMensajes, 5000); // cada 5 segundos
</script>
</body>
</html>