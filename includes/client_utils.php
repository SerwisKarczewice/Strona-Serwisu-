<?php
/**
 * Utility functions for client management and deduplication
 */

/**
 * Normalizes phone number by removing non-digit characters
 * @param string $phone
 * @return string
 */
function normalize_phone($phone)
{
    return preg_replace('/[\s\-\(\)\+]+/', '', $phone);
}

function find_client_by_identity($pdo, $name = '', $phone = '', $email = '')
{
    $phone_norm = !empty($phone) ? normalize_phone($phone) : '';
    $email = trim(strtolower($email));
    $name = trim(strtolower($name));

    // 1. CONFIDENT MATCH: Phone Number
    if ($phone_norm) {
        $sql = "SELECT id FROM clients 
                WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') = ? 
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$phone_norm]);
        $id = $stmt->fetchColumn();
        if ($id)
            return (int) $id;
    }

    // 2. CONFIDENT MATCH: Email
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM clients WHERE LOWER(email) = ? LIMIT 1");
        $stmt->execute([$email]);
        $id = $stmt->fetchColumn();
        if ($id)
            return (int) $id;
    }

    // 3. HEURISTIC MATCH: Name (with conflict check)
    if ($name && strlen($name) > 2) {
        // Find potential matches by name
        $stmt = $pdo->prepare("SELECT id, phone, email FROM clients WHERE LOWER(name) = ?");
        $stmt->execute([$name]);
        $potential_matches = $stmt->fetchAll();

        foreach ($potential_matches as $match) {
            $db_phone_norm = normalize_phone($match['phone']);
            $db_email = strtolower(trim($match['email']));

            // CONFLICT CHECK:
            // If the message has a phone but the DB record has a DIFFERENT phone, don't link automatically.
            if ($phone_norm && $db_phone_norm && $phone_norm !== $db_phone_norm) {
                continue;
            }

            // If the message has an email but the DB record has a DIFFERENT email, don't link automatically.
            if ($email && $db_email && $email !== $db_email) {
                continue;
            }

            // No obvious conflicts, we can assume it's the same person (or they didn't provide new info)
            return (int) $match['id'];
        }
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
function link_message_to_client($pdo, $message_id, $client_id)
{
    $stmt = $pdo->prepare("UPDATE contact_messages SET client_id = ? WHERE id = ?");
    return $stmt->execute([$client_id, $message_id]);
}
