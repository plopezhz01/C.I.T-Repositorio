<?php
/*
Plugin Name: C.I.P Tickets (BD soporte)
Description: Gestiona tickets usando una base de datos externa llamada "soporte" (tablas cliente/administrador/ticket).
Version: 1.0
Author: ASIR2 | Grupo7
*/

if ( ! defined('ABSPATH') ) exit;

/**
 * Capability usada por el plugin (para admin1/admin2, etc.)
 * - Admin de WP la tendrá siempre.
 * - Puedes crear usuarios WP con rol "soporte_admin" para que accedan sin ser administradores totales.
 */
define('SOPORTE_CAP', 'soporte_manage');

/**
 * Credenciales BD externa (mejor en wp-config.php en real)
 */
if ( ! defined('SOPORTE_DB_NAME') ) define('SOPORTE_DB_NAME', 'soporte');
if ( ! defined('SOPORTE_DB_USER') ) define('SOPORTE_DB_USER', 'soporte_user');
if ( ! defined('SOPORTE_DB_PASS') ) define('SOPORTE_DB_PASS', 'Gruposiete');
if ( ! defined('SOPORTE_DB_HOST') ) define('SOPORTE_DB_HOST', 'localhost');

global $soporte_db;

/** ====== ACTIVACIÓN: rol/cap ====== */
register_activation_hook(__FILE__, function () {
    // Rol para “admin1/admin2…”
    add_role('soporte_user', 'C.I.P Tickets', [
        'read' => true,
        SOPORTE_CAP => true,
    ]);

    // Que el Administrador también pueda acceder
    if ( $role = get_role('administrator') ) {
        $role->add_cap(SOPORTE_CAP, true);
    }
});

/** ====== CONEXIÓN BD ====== */
function soporte_init_db_connection() {
    global $soporte_db;
    $soporte_db = new wpdb(
        SOPORTE_DB_USER,
        SOPORTE_DB_PASS,
        SOPORTE_DB_NAME,
        SOPORTE_DB_HOST
    );
}
add_action('plugins_loaded', 'soporte_init_db_connection');

/** ====== ESTILOS ADMIN ====== */
function soporte_admin_assets($hook) {
  $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';

  $allowed_pages = [
    'soporte-tickets',
    'soporte-archivados',
    'soporte-clientes',
    'soporte-ticket-edit',
  ];

  if ( ! in_array($page, $allowed_pages, true) ) return;

  wp_enqueue_style(
    'soporte-tickets-admin',
    plugin_dir_url(__FILE__) . 'assets/admin.css',
    [],
    '1.2'
  );
}


add_action('admin_enqueue_scripts', 'soporte_admin_assets');

/** ====== MENÚ ====== */
add_action('admin_menu', function () {
  add_menu_page(
  'C.I.P Tickets',
  'C.I.P Tickets',
  SOPORTE_CAP,
  'soporte-tickets', 
  'soporte_render_tickets',
  'dashicons-sos',
  26
);

  add_submenu_page(
    'soporte-tickets',
    'Tickets',
    'Tickets',
    SOPORTE_CAP,
    'soporte-tickets',
    'soporte_render_tickets'
  );

  add_submenu_page(
    'soporte-tickets',
    'Archivados',
    'Archivados',
    SOPORTE_CAP,
    'soporte-archivados',
    'soporte_render_archivados'
  );

  add_submenu_page(
    'soporte-tickets',
    'Clientes',
    'Clientes',
    SOPORTE_CAP,
    'soporte-clientes',
    'soporte_render_clientes'
  );

  add_submenu_page(
    null,
    'Editar ticket',
    'Editar ticket',
    SOPORTE_CAP,
    'soporte-ticket-edit',
    'soporte_render_ticket_edit'
  );
});


/** ====== HELPERS ====== */
function soporte_require_cap() {
    if ( ! current_user_can(SOPORTE_CAP) ) {
        wp_die('No tienes permisos para acceder a esta página.');
    }
}

function soporte_badge($value, $type) {
    $value = (string) ($value ?? '');
    $class = sanitize_key($value);
    return '<span class="soporte-badge soporte-badge--' . esc_attr($type) . ' soporte-badge--' . esc_attr($class) . '">' . esc_html($value) . '</span>';
}

function soporte_allowed_estado($estado) {
    $allowed = ['abierto','en_proceso','resuelto','cerrado'];
    return in_array($estado, $allowed, true) ? $estado : 'abierto';
}

function soporte_allowed_prioridad($prio) {
    $allowed = ['baja','media','alta','critica'];
    return in_array($prio, $allowed, true) ? $prio : 'media';
}

function soporte_sql_orderby($orderby_raw) {
  $orderby = sanitize_key($orderby_raw ?: 'fecha_creado');
  $map = [
    'id'           => 't.id_ticket',
    'estado'       => 't.estado',
    'nombre'       => 'c.nombre',
    'prioridad'    => 't.prioridad',
    'fecha_creado' => 't.fecha_creado',
  ];
  return $map[$orderby] ?? $map['fecha_creado'];
}

function soporte_sql_order($order_raw) {
  $order = strtoupper((string)$order_raw);
  return ($order === 'ASC') ? 'ASC' : 'DESC';
}

function soporte_sql_filter_where($filter, $table_alias_ticket = 't') {
  $filter = sanitize_key($filter ?: 'todo');
  // Ojo: si tu BD usa estado con espacios, adapta aquí.
  switch ($filter) {
    case 'resuelto':
      return "AND {$table_alias_ticket}.estado = 'resuelto'";
    case 'sin_asignar':
      return "AND ({$table_alias_ticket}.id_adm IS NULL OR {$table_alias_ticket}.id_adm = 0)";
    case 'eliminado':
      return "AND {$table_alias_ticket}.estado = 'eliminado'";
    default:
      return "";
  }
}

/** ====== PÁGINA PRINCIPAL: LISTA TICKETS ====== */
function soporte_render_tickets() {
  soporte_require_cap();
  global $soporte_db;

  echo '<div class="wrap soporte-wrap"><h1>Tickets</h1><div class="soporte-card">';

  if ( empty($soporte_db) || $soporte_db->get_var('SELECT 1') != 1 ) {
    echo '<div class="notice notice-error"><p>No se puede conectar a la BD soporte.</p></div></div></div>';
    return;
  }

  $s       = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
  $filter  = isset($_GET['filter']) ? sanitize_key($_GET['filter']) : 'todo';
  $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'fecha_creado';
  $order   = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

  // Barra superior
  echo '<form method="get" class="soporte-toolbar">';
  echo '<input type="hidden" name="page" value="soporte-tickets">';

  echo '<div class="soporte-toolbar-left">';
  echo '<input type="search" name="s" value="'.esc_attr($s).'" placeholder="Buscar por nombre...">';
  echo '</div>';

  echo '<div class="soporte-toolbar-right">';
  echo '<label>Filtro<br><select name="filter">
          <option value="todo" '.selected($filter,'todo',false).'>Todo</option>
          <option value="resuelto" '.selected($filter,'resuelto',false).'>Resuelto</option>
          <option value="sin_asignar" '.selected($filter,'sin_asignar',false).'>Sin asignar</option>
          <option value="eliminado" '.selected($filter,'eliminado',false).'>Eliminado</option>
        </select></label>';

  echo '<label>Ordenar por<br><select name="orderby">
          <option value="id" '.selected($orderby,'id',false).'>ID</option>
          <option value="estado" '.selected($orderby,'estado',false).'>Estado</option>
          <option value="nombre" '.selected($orderby,'nombre',false).'>Nombre</option>
          <option value="prioridad" '.selected($orderby,'prioridad',false).'>Prioridad</option>
          <option value="fecha_creado" '.selected($orderby,'fecha_creado',false).'>Fecha de creación</option>
        </select></label>';

  echo '<label>&nbsp;<br><select name="order">
          <option value="ASC" '.selected($order,'ASC',false).'>ASC</option>
          <option value="DESC" '.selected($order,'DESC',false).'>DESC</option>
        </select></label>';

  echo '<div class="soporte-toolbar-actions">';
  echo '<button class="button button-primary" type="submit">Aplicar</button> ';
  echo '<a class="button" href="'.esc_url(admin_url('admin.php?page=soporte-tickets')).'">Restablecer</a>';
  echo '</div>';

  echo '</div>';
  echo '</form>';

  // WHERE (búsqueda + filtros)
  $where = "WHERE t.estado <> 'cerrado'";
  $where .= " " . soporte_sql_filter_where($filter, 't');

  if ($s !== '') {
    $like = '%' . $soporte_db->esc_like($s) . '%';
    $where .= $soporte_db->prepare(
      " AND (c.nombre LIKE %s OR t.titulo LIKE %s OR t.descripcion LIKE %s)",
      $like, $like, $like
    );
  }

  $sql_orderby = soporte_sql_orderby($orderby);
  $sql_order   = soporte_sql_order($order);

  $tickets = $soporte_db->get_results("
    SELECT
      t.id_ticket,
      c.nombre AS cliente,
      a.nombre AS admin,
      t.titulo,
      t.descripcion,
      t.prioridad,
      t.estado,
      t.fecha_creado,
      t.fecha_resuelto
    FROM ticket t
    LEFT JOIN cliente c ON t.id_cliente = c.id_cliente
    LEFT JOIN administrador a ON t.id_adm = a.id_adm
    $where
    ORDER BY $sql_orderby $sql_order
    LIMIT 200
  ");

  // Acciones en bloque (Archivar)
  echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
  wp_nonce_field('soporte_tickets_bulk', 'soporte_nonce_bulk');
  echo '<input type="hidden" name="action" value="soporte_tickets_bulk">';

  echo '<div class="soporte-bulkbar">';
  echo '<select name="bulk_action">
          <option value="">Acciones en bloque</option>
          <option value="archive">Archivar</option>
        </select> ';
  echo '<button type="submit" class="button">Aplicar</button>';
  echo '</div>';

  echo '<table class="widefat fixed striped soporte-table">';
 echo '<thead><tr>
  <td class="manage-column column-cb check-column"><input type="checkbox" id="soporte-cb-all"></td>
  <th style="width:40px;">ID</th>
  <th style="width:60px;">Cliente</th>
  <th style="width:100px;">Admin</th>
  <th style="width:150px;">Titulo</th>
  <th style="width:250px;">Descripción</th>
  <th style="width:80px;">Prioridad</th>
  <th style="width:90px;">Estado</th>
  <th style="width:130px;">Creado</th>
  <th style="width:130px;">Resuelto</th>
</tr></thead><tbody>';


  if ( ! empty($tickets) ) {
  foreach ($tickets as $t) {
    $edit_url = admin_url('admin.php?page=soporte-ticket-edit&id_ticket=' . intval($t->id_ticket));

    // Fila clicable
    echo '<tr class="soporte-row-link" data-href="' . esc_url($edit_url) . '">';
    echo '<th scope="row" class="check-column"><input type="checkbox" name="ids[]" value="'.esc_attr($t->id_ticket).'"></th>';
    echo '<td>' . esc_html($t->id_ticket) . '</td>';
    echo '<td>' . esc_html($t->cliente ?? '') . '</td>';
    echo '<td>' . esc_html($t->admin ?? '') . '</td>';
    echo '<td>' . esc_html($t->titulo ?? '') . '</td>';
    echo '<td>' . esc_html($t->descripcion ?? '') . '</td>';
    echo '<td>' . soporte_badge($t->prioridad ?? '', 'prioridad') . '</td>';
    echo '<td>' . soporte_badge($t->estado ?? '', 'estado') . '</td>';
    echo '<td>' . esc_html($t->fecha_creado ?? '') . '</td>';
    echo '<td>' . esc_html($t->fecha_resuelto ?? '') . '</td>';
    echo '</tr>';
  }
} else {
  echo '<tr><td colspan="10">No hay tickets.</td></tr>';
}


  echo '</tbody></table>';
  echo '</form>';

  // Toggle checkbox “seleccionar todo”
  echo "<script>
    (function(){
      var all = document.getElementById('soporte-cb-all');
      if(!all) return;
      all.addEventListener('change', function(){
        document.querySelectorAll('.soporte-table tbody input[type=checkbox]').forEach(function(cb){
          cb.checked = all.checked;
        });
      });
    })();
  </script>";

  echo '</div></div>';

  echo "<script>
(function(){
  document.querySelectorAll('tr.soporte-row-link').forEach(function(row){
    row.addEventListener('click', function(e){
      if (e.target && (e.target.tagName === 'INPUT' || e.target.closest('.check-column'))) return;
      var url = row.getAttribute('data-href');
      if (url) window.location.href = url;
    });
  });
})();
</script>";
}

/** ====== PÁGINA: EDITAR TICKET ====== */
function soporte_render_ticket_edit() {
    soporte_require_cap();
    global $soporte_db;

    $id_ticket = isset($_GET['id_ticket']) ? absint($_GET['id_ticket']) : 0;
    if ( ! $id_ticket ) wp_die('Ticket inválido.');

    $ticket = $soporte_db->get_row(
        $soporte_db->prepare("SELECT * FROM ticket WHERE id_ticket = %d", $id_ticket)
    );
    if ( ! $ticket ) wp_die('Ticket no encontrado.');

    $admins = $soporte_db->get_results("SELECT id_adm, nombre, email FROM administrador ORDER BY nombre ASC");

    $msg = '';
    if ( isset($_GET['updated']) ) $msg = 'Cambios guardados.';
    if ( isset($_GET['closed']) ) $msg = 'Ticket cerrado.';

    echo '<div class="wrap soporte-wrap"><h1>Editar ticket #' . esc_html($ticket->id_ticket) . '</h1>';

    if ($msg) {
        echo '<div class="notice notice-success"><p>' . esc_html($msg) . '</p></div>';
    }

    echo '<div class="soporte-edit-grid">';

    // Panel principal
    echo '<div class="soporte-card">';
    echo '<h2>Detalle</h2>';
    echo '<p><strong>Titulo:</strong> ' . esc_html($ticket->titulo) . '</p>';
    echo '<p><strong>Descripción:</strong><br>' . nl2br(esc_html($ticket->descripcion ?? '')) . '</p>';
    echo '<p><strong>Creado:</strong> ' . esc_html($ticket->fecha_creado ?? '') . '</p>';
    echo '<p><strong>Resuelto:</strong> ' . esc_html($ticket->fecha_resuelto ?? '') . '</p>';
    echo '</div>';

    // Panel lateral (form)
    echo '<div class="soporte-card">';
    echo '<h2>Administrar</h2>';

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('soporte_update_ticket', 'soporte_nonce');

    echo '<input type="hidden" name="action" value="soporte_ticket_save">';
    echo '<input type="hidden" name="id_ticket" value="' . esc_attr($ticket->id_ticket) . '">';

    // Estado
    $estados = ['abierto'=>'abierto','en_proceso'=>'en proceso','resuelto'=>'resuelto','cerrado'=>'cerrado'];
    echo '<p><label><strong>Estado</strong></label><br><select name="estado">';
    foreach ($estados as $k => $label) {
        $sel = selected($ticket->estado, $k, false);
        echo '<option value="'.esc_attr($k).'" '.$sel.'>'.esc_html($label).'</option>';
    }
    echo '</select></p>';

    // Prioridad
    $prios = ['baja'=>'Baja','media'=>'Media','alta'=>'Alta','critica'=>'Crítica'];
    echo '<p><label><strong>Prioridad</strong></label><br><select name="prioridad">';
    foreach ($prios as $k => $label) {
        $sel = selected($ticket->prioridad, $k, false);
        echo '<option value="'.esc_attr($k).'" '.$sel.'>'.esc_html($label).'</option>';
    }
    echo '</select></p>';

    // Asignación
    echo '<p><label><strong>Asignar a admin</strong></label><br><select name="id_adm">';
    echo '<option value="0">(Sin asignar)</option>';
    foreach ( (array)$admins as $a ) {
        $sel = selected((int)$ticket->id_adm, (int)$a->id_adm, false);
        echo '<option value="'.esc_attr($a->id_adm).'" '.$sel.'>'.esc_html($a->nombre).' ('.esc_html($a->email).')</option>';
    }
    echo '</select></p>';

    // Observaciones
    echo '<p><label><strong>Observaciones</strong></label><br>';
    echo '<textarea name="observaciones" rows="6" style="width:100%;">' . esc_textarea($ticket->observaciones ?? '') . '</textarea></p>';

    echo '<p>';
    echo '<button type="submit" class="button button-primary">Guardar cambios</button> ';
    echo '</p>';
    echo '</form>';

    // Cerrar ticket (form aparte)
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:10px;">';
    wp_nonce_field('soporte_close_ticket', 'soporte_nonce_close');
    echo '<input type="hidden" name="action" value="soporte_ticket_close">';
    echo '<input type="hidden" name="id_ticket" value="' . esc_attr($ticket->id_ticket) . '">';
    echo '<button type="submit" class="button button-secondary" onclick="return confirm(\'¿Cerrar este ticket?\')">Cerrar ticket</button>';
    echo '</form>';

    echo '</div>'; // card lateral

    echo '</div>'; // grid
    echo '</div>'; // wrap
}

/** ====== HANDLER: GUARDAR TICKET ====== */
add_action('admin_post_soporte_ticket_save', function () {
    soporte_require_cap();
    check_admin_referer('soporte_update_ticket', 'soporte_nonce');

    global $soporte_db;

    $id_ticket  = absint($_POST['id_ticket'] ?? 0);
    $estado     = soporte_allowed_estado(sanitize_text_field($_POST['estado'] ?? 'abierto'));
    $prioridad  = soporte_allowed_prioridad(sanitize_key($_POST['prioridad'] ?? 'media'));
    $id_adm     = absint($_POST['id_adm'] ?? 0);
    $obs        = sanitize_textarea_field($_POST['observaciones'] ?? '');

    $soporte_db->update(
        'ticket',
        [
            'estado' => $estado,
            'prioridad' => $prioridad,
            'id_adm' => ($id_adm ?: null),
            'observaciones' => $obs,
        ],
        ['id_ticket' => $id_ticket],
        ['%s','%s','%d','%s'],
        ['%d']
    );

    wp_redirect(admin_url('admin.php?page=soporte-ticket-edit&id_ticket=' . $id_ticket . '&updated=1'));
    exit;
});

/** ====== HANDLER: CERRAR TICKET ====== */
add_action('admin_post_soporte_ticket_close', function () {
    soporte_require_cap();
    check_admin_referer('soporte_close_ticket', 'soporte_nonce_close');

    global $soporte_db;

    $id_ticket = absint($_POST['id_ticket'] ?? 0);

    $soporte_db->update(
        'ticket',
        [
            'estado' => 'cerrado',
            'fecha_resuelto' => current_time('mysql'),
        ],
        ['id_ticket' => $id_ticket],
        ['%s','%s'],
        ['%d']
    );

    wp_redirect(admin_url('admin.php?page=soporte-archivados'));
    exit;
});

/** ====== HANDLER: ARCHIVAR TICKET ====== */
add_action('admin_post_soporte_tickets_bulk', function () {
  soporte_require_cap();
  check_admin_referer('soporte_tickets_bulk', 'soporte_nonce_bulk');

  global $soporte_db;

  $action = sanitize_key($_POST['bulk_action'] ?? '');
  $ids = array_map('absint', (array)($_POST['ids'] ?? []));
  $ids = array_values(array_filter($ids));

  if (!$ids || $action !== 'archive') {
    wp_redirect(admin_url('admin.php?page=soporte-tickets'));
    exit;
  }

  foreach ($ids as $id_ticket) {
    $soporte_db->update(
      'ticket',
      [
        'estado' => 'cerrado',
        'fecha_resuelto' => current_time('mysql'),
      ],
      ['id_ticket' => $id_ticket],
      ['%s','%s'],
      ['%d']
    );
  }

  wp_redirect(admin_url('admin.php?page=soporte-archivados'));
  exit;
});

/** ====== PÁGINA: ARCHIVAR TICKET ====== */
function soporte_render_archivados() {
  soporte_require_cap();
  global $soporte_db;

  echo '<div class="wrap soporte-wrap"><h1>Archivados</h1><div class="soporte-card">';

  $s       = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
  $filter  = isset($_GET['filter']) ? sanitize_key($_GET['filter']) : 'todo';
  $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'fecha_creado';
  $order   = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

  echo '<form method="get" class="soporte-toolbar">';
  echo '<input type="hidden" name="page" value="soporte-archivados">';

  echo '<div class="soporte-toolbar-left">';
  echo '<input type="search" name="s" value="'.esc_attr($s).'" placeholder="Buscar por nombre...">';
  echo '</div>';

  echo '<div class="soporte-toolbar-right">';
  echo '<label>Filtro<br><select name="filter">
          <option value="todo" '.selected($filter,'todo',false).'>Todo</option>
          <option value="resuelto" '.selected($filter,'resuelto',false).'>Resuelto</option>
          <option value="sin_asignar" '.selected($filter,'sin_asignar',false).'>Sin asignar</option>
          <option value="cerrado" '.selected($filter,'cerrado',false).'>Cerrado</option>
          <option value="eliminado" '.selected($filter,'eliminado',false).'>Eliminado</option>
        </select></label>';

  echo '<label>Ordenar por<br><select name="orderby">
          <option value="id" '.selected($orderby,'id',false).'>ID</option>
          <option value="estado" '.selected($orderby,'estado',false).'>Estado</option>
          <option value="nombre" '.selected($orderby,'nombre',false).'>Nombre</option>
          <option value="prioridad" '.selected($orderby,'prioridad',false).'>Prioridad</option>
          <option value="fecha_creado" '.selected($orderby,'fecha_creado',false).'>Fecha de creación</option>
        </select></label>';

  echo '<label>&nbsp;<br><select name="order">
          <option value="ASC" '.selected($order,'ASC',false).'>ASC</option>
          <option value="DESC" '.selected($order,'DESC',false).'>DESC</option>
        </select></label>';

  echo '<div class="soporte-toolbar-actions">';
  echo '<button class="button button-primary" type="submit">Aplicar</button> ';
  echo '<a class="button" href="'.esc_url(admin_url('admin.php?page=soporte-archivados')).'">Restablecer</a>';
  echo '</div>';

  echo '</div>';
  echo '</form>';

  $where = "WHERE t.estado = 'cerrado'";
  if ($s !== '') {
    $like = '%' . $soporte_db->esc_like($s) . '%';
    $where .= $soporte_db->prepare(
      " AND (c.nombre LIKE %s OR t.titulo LIKE %s OR t.descripcion LIKE %s)",
      $like, $like, $like
    );
  }

  $sql_orderby = soporte_sql_orderby($orderby);
  $sql_order   = soporte_sql_order($order);

  $tickets = $soporte_db->get_results("
    SELECT
      t.id_ticket,
      c.nombre AS cliente,
      a.nombre AS admin,
      t.titulo,
      t.descripcion,
      t.prioridad,
      t.estado,
      t.fecha_creado,
      t.fecha_resuelto
    FROM ticket t
    LEFT JOIN cliente c ON t.id_cliente = c.id_cliente
    LEFT JOIN administrador a ON t.id_adm = a.id_adm
    $where
    ORDER BY $sql_orderby $sql_order
    LIMIT 200
  ");

  echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
  wp_nonce_field('soporte_archivados_bulk', 'soporte_nonce_arch');
  echo '<input type="hidden" name="action" value="soporte_archivados_bulk">';

  echo '<div class="soporte-bulkbar">';
  echo '<select name="bulk_action">
          <option value="">Acciones en bloque</option>
          <option value="delete_permanent">Eliminar permanente</option>
        </select> ';
  echo '<button type="submit" class="button">Aplicar</button>';
  echo '</div>';

  echo '<table class="widefat fixed striped soporte-table">';
  echo '<thead><tr>
  <td class="manage-column column-cb check-column"><input type="checkbox" id="soporte-cb-all"></td>
  <th style="width:40px;">ID</th>
  <th style="width:60px;">Cliente</th>
  <th style="width:100px;">Admin</th>
  <th style="width:150px;">Titulo</th>
  <th style="width:250px;">Descripción</th>
  <th style="width:80px;">Prioridad</th>
  <th style="width:90px;">Estado</th>
  <th style="width:130px;">Creado</th>
  <th style="width:130px;">Resuelto</th>
</tr></thead><tbody>';

  if ($tickets) {
    foreach ($tickets as $t) {
      echo '<tr>';
        echo '<th scope="row" class="check-column"><input type="checkbox" name="ids[]" value="'.esc_attr($t->id_ticket).'"></th>';
        echo '<td>' . esc_html($t->id_ticket) . '</td>';
        echo '<td>' . esc_html($t->cliente ?? '') . '</td>';
        echo '<td>' . esc_html($t->admin ?? '') . '</td>';
        echo '<td>' . esc_html($t->titulo ?? '') . '</td>';
        echo '<td>' . esc_html($t->descripcion ?? '') . '</td>';
        echo '<td>' . soporte_badge($t->prioridad ?? '', 'prioridad') . '</td>';
        echo '<td>' . soporte_badge($t->estado ?? '', 'estado') . '</td>';
        echo '<td>' . esc_html($t->fecha_creado ?? '') . '</td>';
        echo '<td>' . esc_html($t->fecha_resuelto ?? '') . '</td>';
      echo '</tr>';
    }
  } else {
    echo '<tr><td colspan="9">No hay tickets archivados.</td></tr>';
  }

  echo '</tbody></table>';
  echo '</form>';

  echo "<script>
    (function(){
      var all = document.getElementById('soporte-arch-cb-all');
      if(!all) return;
      all.addEventListener('change', function(){
        document.querySelectorAll('.soporte-table tbody input[type=checkbox]').forEach(function(cb){
          cb.checked = all.checked;
        });
      });
    })();
  </script>";

  echo '</div></div>';
}

/** ====== HANDLER: BORRAR TICKET ====== */
add_action('admin_post_soporte_archivados_bulk', function () {
  soporte_require_cap();
  check_admin_referer('soporte_archivados_bulk', 'soporte_nonce_arch');

  global $soporte_db;

  $action = sanitize_key($_POST['bulk_action'] ?? '');
  $ids = array_map('absint', (array)($_POST['ids'] ?? []));
  $ids = array_values(array_filter($ids));

  if (!$ids || $action !== 'delete_permanent') {
    wp_redirect(admin_url('admin.php?page=soporte-archivados'));
    exit;
  }

  $placeholders = implode(',', array_fill(0, count($ids), '%d'));
  $sql = "DELETE FROM ticket WHERE id_ticket IN ($placeholders)";
  $soporte_db->query($soporte_db->prepare($sql, ...$ids));

  wp_redirect(admin_url('admin.php?page=soporte-archivados'));
  exit;
});


/** ====== PÁGINA: CLIENTES (LISTAR + AÑADIR) ====== */
function soporte_render_clientes() {
    soporte_require_cap();
    global $soporte_db;

    $msg = '';
    if ( isset($_GET['client_created']) ) $msg = 'Cliente creado.';

    echo '<div class="wrap soporte-wrap"><h1>Clientes</h1>';

    if ($msg) {
        echo '<div class="notice notice-success"><p>' . esc_html($msg) . '</p></div>';
    }

    // Form alta
    echo '<div class="soporte-card">';
    echo '<h2>Añadir cliente</h2>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    wp_nonce_field('soporte_add_cliente', 'soporte_nonce_cliente');
    echo '<input type="hidden" name="action" value="soporte_cliente_add">';

    echo '<p><label>Nombre</label><br><input type="text" name="nombre" required style="width:320px;"></p>';
    echo '<p><label>Email</label><br><input type="email" name="email" required style="width:320px;"></p>';
    echo '<p><label>Teléfono</label><br><input type="text" name="telefono" style="width:200px;"></p>';
    echo '<p><label>Contraseña</label><br><input type="password" name="contrasena" required style="width:320px;"></p>';

    echo '<p><button type="submit" class="button button-primary">Crear cliente</button></p>';
    echo '</form>';
    echo '</div>';

    // Tabla clientes
    $clientes = $soporte_db->get_results("
        SELECT
            c.id_cliente, c.nombre, c.email, c.telefono,
            COUNT(t.id_ticket) AS num_tickets
        FROM cliente c
        LEFT JOIN ticket t ON t.id_cliente = c.id_cliente
        GROUP BY c.id_cliente
        ORDER BY c.id_cliente DESC
        LIMIT 200
    ");

    echo '<div class="soporte-card">';
    echo '<h2>Listado</h2>';
    echo '<table class="widefat fixed striped soporte-table">';
    echo '<thead><tr>
            <th style="width:70px;">ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th style="width:140px;">Teléfono</th>
            <th style="width:140px;">Nº Tickets</th>
          </tr></thead><tbody>';

    if ($clientes) {
        foreach ($clientes as $c) {
            echo '<tr>';
            echo '<td>' . esc_html($c->id_cliente) . '</td>';
            echo '<td>' . esc_html($c->nombre) . '</td>';
            echo '<td>' . esc_html($c->email) . '</td>';
            echo '<td>' . esc_html($c->telefono) . '</td>';
            echo '<td>' . esc_html($c->num_tickets) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">No hay clientes.</td></tr>';
    }

    echo '</tbody></table>';
    echo '</div>';

    echo '</div>';
}

/** ====== HANDLER: CREAR CLIENTE ====== */
add_action('admin_post_soporte_cliente_add', function () {
    soporte_require_cap();
    check_admin_referer('soporte_add_cliente', 'soporte_nonce_cliente');

    global $soporte_db;

    $nombre = sanitize_text_field($_POST['nombre'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $telefono = sanitize_text_field($_POST['telefono'] ?? '');
    $pass_plain = (string)($_POST['contrasena'] ?? '');

    if (!$nombre || !$email || !$pass_plain) {
        wp_die('Faltan campos obligatorios.');
    }

    // Hash de contraseña (para que no quede en claro en la BD)
    $contrasena = sanitize_text_field($pass_plain);

    $soporte_db->insert(
    'cliente',
    [
        'nombre' => $nombre,
        'contrasena' => $contrasena,
        'telefono' => $telefono,
        'email' => $email,
    ],
    ['%s','%s','%s','%s']
    );

    wp_redirect(admin_url('admin.php?page=soporte-clientes&client_created=1'));
    exit;
});
