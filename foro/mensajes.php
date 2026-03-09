<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conexion = new mysqli('db', 'foro', 'foro', 'db');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$res = $conexion->query("
    SELECT mensajes.*, usuarios.nick
    FROM mensajes
    JOIN usuarios ON mensajes.usuario_id = usuarios.id
    ORDER BY mensajes.id DESC
");
while ($row = $res->fetch_assoc()):
?>
  <div class="glass p-4 rounded-xl border border-white/10 shadow-md flex justify-between items-start"
       data-id="<?php echo $row['id']; ?>">

    <div class="flex-1 mr-3">
      <div class="flex justify-between items-center mb-2">
        <span class="font-semibold text-fuchsia-300">@<?php echo htmlspecialchars($row['nick']); ?></span>
        <span class="text-xs text-gray-400"><?php echo $row['fecha']; ?></span>
      </div>
      <p class="text-gray-200 leading-relaxed">
        <?php echo nl2br(htmlspecialchars($row['mensaje'])); ?>
      </p>

      <?php if (!empty($row['archivo']) && $row['tipo'] === 'imagen'): ?>
        <img src="<?php echo htmlspecialchars($row['archivo']); ?>"
             class="rounded mt-2 max-w-xs border border-fuchsia-500">
      <?php elseif (!empty($row['archivo']) && $row['tipo'] === 'video'): ?>
        <video src="<?php echo htmlspecialchars($row['archivo']); ?>" controls
               class="rounded mt-2 max-w-xs border border-indigo-500"></video>
      <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
      <div class="flex flex-col items-end">
        <a href="index.php?delete_id=<?php echo $row['id']; ?>"
           class="text-xs px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700"
           onclick="return confirm('¿Seguro que quieres borrar este mensaje?');">
          Borrar
        </a>
      </div>
    <?php endif; ?>

  </div>
<?php endwhile; ?>
