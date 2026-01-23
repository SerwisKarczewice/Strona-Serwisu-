<?php
require_once '../config.php';

try {
    // 1. Add client_id to contact_messages if not exists
    $columns = $pdo->query("SHOW COLUMNS FROM contact_messages LIKE 'client_id'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE contact_messages ADD COLUMN client_id INT(11) DEFAULT NULL AFTER id");
        $pdo->exec("ALTER TABLE contact_messages ADD INDEX idx_client (client_id)");
        $pdo->exec("ALTER TABLE contact_messages ADD CONSTRAINT fk_message_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL");
        echo "Added client_id to contact_messages.<br>";
    } else {
        echo "Column client_id already exists in contact_messages.<br>";
    }

    // 2. Create client_solutions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS client_solutions (
        id INT(11) NOT NULL AUTO_INCREMENT,
        client_id INT(11) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT DEFAULT NULL,
        items_json LONGTEXT NOT NULL, -- Stores JSON array of {type, id, name, price}
        total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status ENUM('new', 'sent', 'accepted', 'rejected') DEFAULT 'new',
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_client (client_id),
        KEY idx_status (status),
        CONSTRAINT fk_solution_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Created client_solutions table.<br>";

    // 2.5 Add message_id to client_solutions if not exists
    $columns = $pdo->query("SHOW COLUMNS FROM client_solutions LIKE 'message_id'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE client_solutions ADD COLUMN message_id INT(11) DEFAULT NULL AFTER client_id");
        $pdo->exec("ALTER TABLE client_solutions ADD INDEX idx_message (message_id)");
        $pdo->exec("ALTER TABLE client_solutions ADD CONSTRAINT fk_solution_message FOREIGN KEY (message_id) REFERENCES contact_messages(id) ON DELETE SET NULL");
        echo "Added message_id to client_solutions.<br>";
    }

    // 2.5 Add message_id to client_solutions if not exists
    $columns = $pdo->query("SHOW COLUMNS FROM client_solutions LIKE 'message_id'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE client_solutions ADD COLUMN message_id INT(11) DEFAULT NULL AFTER client_id");
        $pdo->exec("ALTER TABLE client_solutions ADD INDEX idx_message (message_id)");
        $pdo->exec("ALTER TABLE client_solutions ADD CONSTRAINT fk_solution_message FOREIGN KEY (message_id) REFERENCES contact_messages(id) ON DELETE SET NULL");
        echo "Added message_id to client_solutions.<br>";
    }

    // 3. Add notes to clients if not exists (optional helpful column)
    $columns = $pdo->query("SHOW COLUMNS FROM clients LIKE 'notes'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE clients ADD COLUMN notes TEXT DEFAULT NULL");
        echo "Added notes to clients.<br>";
    }

    echo "<h3>Database migration completed successfully!</h3>";
    echo "<a href='index.php'>Back to Dashboard</a>";

} catch (PDOException $e) {
    echo "<h3>Error during migration:</h3>";
    echo $e->getMessage();
}
?>