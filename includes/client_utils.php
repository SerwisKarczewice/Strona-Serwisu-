<?php
/**
 * Utility functions for client management and deduplication
 */

/**
 * Normalizes phone number by removing non-digit characters
 * @param string $phone
 * @return string
 */
function normalize_phone($phone) {
    return preg_replace('/[\s\-\(\)\+]+/', '', $phone);
}

/**
 * Finds a client by phone, email, or name
 * @param PDO $pdo
 * @param string $name
 * @param string $phone
 * @param string $email
 * @return int|null Client ID if found, otherwise null
 */
function find_client_by_identity($pdo, $name = '', $phone = '', $email = '') {
    // 1. Try by phone
    if (!empty($phone)) {
        $phone_norm = normalize_phone($phone);
        if (!empty($phone_norm)) {
            // Compare with normalized DB values
            $sql = "SELECT id FROM clients 
                    WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ? 
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$phone_norm]);
            $id = $stmt->fetchColumn();
            if ($id) return $id;
        }
    }

    // 2. Try by email
    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $stmt->execute([trim($email)]);
        $id = $stmt->fetchColumn();
        if ($id) return $id;
    }

    // 3. Try by exact name (if provided)
    if (!empty($name)) {
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $stmt->execute([trim($name)]);
        $id = $stmt->fetchColumn();
        if ($id) return $id;
    }

    return null;
}

/**
 * Links a message to a client
 * @param PDO $pdo
 * @param int $message_id
 * @param int $client_id
 * @return bool
 */
function link_message_to_client($pdo, $message_id, $client_id) {
    $stmt = $pdo->prepare("UPDATE contact_messages SET client_id = ? WHERE id = ?");
    return $stmt->execute([$client_id, $message_id]);
}
