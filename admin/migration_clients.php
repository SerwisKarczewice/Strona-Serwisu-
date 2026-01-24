<?php
require_once '../config.php';

try {
    // 1. Add client_id to contact_messages if not exists
    $columns = $pdo->query("SHOW COLUMNS FROM contact_messages LIKE 'client_id'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE contact_messages ADD COLUMN client_id INT(11) DEFAULT NULL AFTER id");
        $pdo->exec("ALTER TABLE contact_messages ADD INDEX idx_client (client_id)");
        $pdo->exec("ALTER TABLE contact_messages ADD CONSTRAINT fk_message_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL");
        if (!defined('SILENT_MIGRATION'))
            echo "Added client_id to contact_messages.<br>";
    } else {
        if (!defined('SILENT_MIGRATION'))
            echo "Column client_id already exists in contact_messages.<br>";
    }

    // 2. Create client_solutions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS client_solutions (
        id INT(11) NOT NULL AUTO_INCREMENT,
        client_id INT(11) NOT NULL,
        message_id INT(11) DEFAULT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT DEFAULT NULL,
        items_json LONGTEXT NOT NULL DEFAULT '[]',
        total_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        status ENUM('new', 'sent', 'accepted', 'rejected') DEFAULT 'new',
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT NULL,
        PRIMARY KEY (id),
        KEY idx_client (client_id),
        KEY idx_status (status),
        KEY idx_message (message_id),
        CONSTRAINT fk_solution_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
        CONSTRAINT fk_solution_message FOREIGN KEY (message_id) REFERENCES contact_messages(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Add description column if it doesn't exist
    $desc_columns = $pdo->query("SHOW COLUMNS FROM client_solutions LIKE 'description'")->fetchAll();
    if (empty($desc_columns)) {
        $pdo->exec("ALTER TABLE client_solutions ADD COLUMN description TEXT DEFAULT NULL AFTER title");
        if (!defined('SILENT_MIGRATION'))
            echo "Added description to client_solutions.<br>";
    }
    
    if (!defined('SILENT_MIGRATION'))
        echo "Created/verified client_solutions table.<br>";

    // 3. Add notes to clients if not exists (optional helpful column)
    $columns = $pdo->query("SHOW COLUMNS FROM clients LIKE 'notes'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE clients ADD COLUMN notes TEXT DEFAULT NULL");
        if (!defined('SILENT_MIGRATION'))
            echo "Added notes to clients.<br>";
    }

    if (!defined('SILENT_MIGRATION')) {
        echo "<h3>Database migration completed successfully!</h3>";
        echo "<a href='index.php'>Back to Dashboard</a>";
    }

} catch (PDOException $e) {
    echo "<h3>Error during migration:</h3>";
    echo $e->getMessage();
}
?>