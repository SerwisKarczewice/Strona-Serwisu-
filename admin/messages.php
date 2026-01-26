<?php
require_once '../config.php';
require_once '../includes/client_utils.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Backend Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'assign_client') {
        $msg_id = $_POST['msg_id'];
        $client_id = $_POST['client_id'];

        // If "create new" was selected
        if ($client_id === 'new') {
            $name = trim($_POST['new_client_name']);
            $phone = trim($_POST['new_client_phone']);

            // DEDUPLICATION LOOKUP
            $existing_client = find_client_by_identity($pdo, $name, $phone);

            if ($existing_client) {
                // Use existing client
                $client_id = $existing_client;
            } else {
                // Create new only if not found
                $stmt = $pdo->prepare("INSERT INTO clients (name, phone, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$name, $phone]);
                $client_id = $pdo->lastInsertId();
            }
        }

        // Update message linkage
        $stmt = $pdo->prepare("UPDATE contact_messages SET client_id = ? WHERE id = ?");
        $stmt->execute([$client_id, $msg_id]);

        header('Location: messages.php?success=assigned');
        exit;
    }
}

// Fetch messages with client info
$stmt = $pdo->query("SELECT m.*, c.name as client_name FROM contact_messages m LEFT JOIN clients c ON m.client_id = c.id ORDER BY m.created_at DESC");
$messages = $stmt->fetchAll();

// Fetch clients for dropdown
$clients = $pdo->query("SELECT id, name, phone FROM clients ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wiadomości - Panel Administracyjny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Wiadomości od Klientów</h1>
            </header>

            <div class="content-section full-width">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imię i Nazwisko</th>
                                <th>Telefon</th>
                                <th>Temat</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?php echo $msg['id']; ?></td>
                                    <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                    <td><a
                                            href="tel:<?php echo htmlspecialchars($msg['phone']); ?>"><?php echo htmlspecialchars($msg['phone']); ?></a>
                                    </td>
                                    <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($msg['created_at'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $msg['status']; ?>">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons" style="display: flex; gap: 8px; align-items: center;">
                                            <?php if (!empty($msg['client_id'])): ?>
                                                <a href="client_view.php?id=<?php echo $msg['client_id']; ?>" class="btn-icon"
                                                    title="Profil klienta" style="color: #3498db;">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                                <a href="solution_editor.php?message_id=<?php echo $msg['id']; ?>"
                                                    class="btn-icon" title="Oferta" style="color: #27ae60;">
                                                    <i class="fas fa-file-invoice-dollar"></i>
                                                </a>
                                            <?php else: ?>
                                                <?php
                                                // Check for potential matches (using Centralized Logic)
                                                $potential_client_id = find_client_by_identity($pdo, $msg['name'], $msg['phone']);
                                                $potential_match = ($potential_client_id !== null);
                                                ?>
                                                <button class="btn-icon"
                                                    style="background:none; border:none; color: <?php echo $potential_match ? '#e67e22' : '#2ecc71'; ?>; cursor:pointer;"
                                                    onclick="openAssignModal(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars(addslashes($msg['name'])); ?>', '<?php echo htmlspecialchars(addslashes($msg['phone'])); ?>')"
                                                    title="<?php echo $potential_match ? 'Znaleziono pasującego klienta - kliknij aby przypisać' : 'Przypisz do klienta'; ?>">
                                                    <i
                                                        class="fas <?php echo $potential_match ? 'fa-user-check' : 'fa-user-plus'; ?>"></i>
                                                </button>
                                            <?php endif; ?>

                                            <a href="view_message.php?id=<?php echo $msg['id']; ?>" class="btn-icon"
                                                title="Zobacz">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="delete_message.php?id=<?php echo $msg['id']; ?>"
                                                class="btn-icon delete" title="Usuń"
                                                onclick="return confirm('Czy na pewno chcesz usunąć tę wiadomość?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    </div>

    <!-- Assign Modal -->
    <div id="assignModal" class="modal"
        style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">
        <div class="modal-content"
            style="background-color:#fefefe; margin:15% auto; padding:20px; border:1px solid #888; width:80%; max-width:500px; border-radius:10px;">
            <span class="close" onclick="document.getElementById('assignModal').style.display='none'"
                style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>
            <h3 style="margin-top:0;">Przypisz wiadomość do klienta</h3>
            <form method="POST">
                <input type="hidden" name="action" value="assign_client">
                <input type="hidden" name="msg_id" id="assign_msg_id">
                <input type="hidden" name="new_client_name" id="new_client_name">
                <input type="hidden" name="new_client_phone" id="new_client_phone">

                <div style="margin: 15px 0;">
                    <label style="display:block; margin-bottom:5px;">Wybierz klienta:</label>
                    <select name="client_id" style="width:100%; padding:8px;">
                        <option value="new">-- Stwórz nowego klienta (z danych wiadomości) --</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Zapisz</button>
            </form>
        </div>
    </div>

    <script>
        function openAssignModal(id, name, phone) {
            document.getElementById('assignModal').style.display = 'block';
            document.getElementById('assign_msg_id').value = id;
            document.getElementById('new_client_name').value = name;
            document.getElementById('new_client_phone').value = phone;
        }
    </script>
</body>

</html>