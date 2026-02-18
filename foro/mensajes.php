<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conexion = new mysqli('db', 'foro', 'foro', 'db');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$res = $conexion->query("SELECT mensajes.*, usuarios.nick FROM mensajes JOIN usuarios ON mensajes.usuario_id = usuarios.id ORDER BY mensajes.id DESC");
while ($row = $res->fetch_assoc()):
?>
  <div class="glass p-4 rounded-xl border border-white/10 shadow-md" data-id="<?php echo $row['id']; ?>">
    <div class="flex justify-between items-center mb-2">
      <span class="font-semibold text-fuchsia-300">@<?php echo htmlspecialchars($row['nick']); ?></span>
      <span class="text-xs text-gray-400"><?php echo $row['fecha']; ?></span>
    </div>
    <p class="text-gray-200 leading-relaxed">
      <?php echo nl2br($row['mensaje']); ?>
    </p>
    <?php if (!empty($row['archivo']) && $row['tipo'] == 'imagen'): ?>
      <img src="<?php echo htmlspecialchars($row['archivo']); ?>" class="rounded mt-2 max-w-xs border border-fuchsia-500">
    <?php elseif (!empty($row['archivo']) && $row['tipo'] == 'video'): ?>
      <video src="<?php echo htmlspecialchars($row['archivo']); ?>" controls class="rounded mt-2 max-w-xs border border-indigo-500"></video>
    <?php endif; ?>
  </div>
<?php endwhile; ?>
