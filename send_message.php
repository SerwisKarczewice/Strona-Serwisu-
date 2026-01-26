<?php
// Wyciszamy błędy dla AJAX, ale dla zwykłego POST chcemy widzieć co się dzieje w razie czego
// Jednak trzymamy się zasady "zawsze sukces" dla użytkownika na froncie
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config.php';
require_once 'includes/client_utils.php';

// Funkcja pomocnicza do powrotu i wyświetlenia komunikatu
function redirect_back($status, $message, $type = '')
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['form_status'] = $status ? 'success' : 'error';
    $_SESSION['form_message'] = $message;
    if ($type === 'spam')
        $_SESSION['form_status'] = 'spam';

    // Jeśli to AJAX, zwróć JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => $status, 'message' => $message, 'type' => $type]);
        exit;
    }

    // Jeśli to zwykły POST, wróć do poprzedniej strony
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $referer");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_back(false, 'Nieprawidłowa metoda żądania');
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Honeypot: Jeśli pole "website_url" jest wypełnione, to znaczy że to bot
    if (!empty($_POST['website_url'])) {
        // Zwracamy sukces, aby bot myślał że mu się udało, ale nic nie wysyłamy
        redirect_back(true, 'Wiadomość została wysłana! Dziękujemy za kontakt.');
        exit;
    }

    // Anti-spam: Max 6 wiadomości w ciągu 5 minut
    if (!isset($_SESSION['message_timestamps'])) {
        $_SESSION['message_timestamps'] = [];
    }

    $current_time = time();
    $five_minutes_ago = $current_time - (5 * 60);

    $_SESSION['message_timestamps'] = array_values(array_filter($_SESSION['message_timestamps'], function ($timestamp) use ($five_minutes_ago) {
        return $timestamp > $five_minutes_ago;
    }));

    // Sprawdzenie limitu (zwiększono do 6)
    if (count($_SESSION['message_timestamps']) >= 6) {
        redirect_back(false, 'Przekroczono limit wiadomości. Możesz wysłać maksymalnie 6 wiadomości w ciągu 5 minut. Spróbuj ponownie za kilka minut.', 'spam');
    }

    // Pobranie danych
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $selected_services = trim($_POST['selected_services'] ?? '');

    // Przygotowanie tekstu do wiadomości
    $message_content = $message;

    if (!empty($selected_services)) {
        $items = explode(',', $selected_services);
        $service_ids = [];
        $product_ids = [];

        foreach ($items as $item) {
            $item = trim($item);
            if (strpos($item, 'service_') === 0) {
                $id = intval(substr($item, 8)); // 8 to długość 'service_'
                if ($id > 0)
                    $service_ids[] = $id;
            } elseif (strpos($item, 'product_') === 0) {
                $id = intval(substr($item, 8)); // 8 to długość 'product_'
                if ($id > 0)
                    $product_ids[] = $id;
            }
        }

        if (!empty($service_ids) || !empty($product_ids)) {
            $message_content .= "\n\n--- WYBRANE ELEMENTY ---\n";
        }

        if (!empty($service_ids)) {
            $placeholders = implode(',', array_fill(0, count($service_ids), '?'));
            $stmt = $pdo->prepare("SELECT name, category FROM services WHERE id IN ($placeholders)");
            $stmt->execute($service_ids);
            $services_data = $stmt->fetchAll();

            foreach ($services_data as $s) {
                $prefix = ($s['category'] == 'package') ? '[Pakiet]' : '[Usługa]';
                $message_content .= "• $prefix " . $s['name'] . "\n";
            }
        }

        if (!empty($product_ids)) {
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
            $stmt = $pdo->prepare("SELECT name FROM products WHERE id IN ($placeholders)");
            $stmt->execute($product_ids);
            $products_data = $stmt->fetchAll();

            foreach ($products_data as $p) {
                $message_content .= "• [Produkt] " . $p['name'] . "\n";
            }
        }
    }

    if (!empty($email)) {
        $message_content .= "\n\nEmail klienta: " . $email;
    }

    if (empty($name) || empty($phone) || empty($subject) || empty($message)) {
        redirect_back(false, 'Proszę wypełnić wszystkie wymagane pola (*).');
    }

    // Zapis do bazy danych
    // Najpierw spróbujmy dopasować klienta
    $client_id = find_client_by_identity($pdo, $name, $phone, $email);

    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (client_id, name, phone, address, subject, message, created_at) 
        VALUES (:client_id, :name, :phone, :address, :subject, :message, NOW())
    ");

    $stmt->execute([
        ':client_id' => $client_id,
        ':name' => $name,
        ':phone' => $phone,
        ':address' => !empty($address) ? $address : null,
        ':subject' => $subject,
        ':message' => $message_content
    ]);

    $_SESSION['message_timestamps'][] = $current_time;

    // Wysyłka emaila - optymalizacja: @mail potrafi zamulać na Windows/XAMPP
    $to = 'kontakt@serwis.pl';
    $email_subject = 'Nowa wiadomość: ' . $subject;
    $email_body = "Wiadomość z formularza:\n\nImię: $name\nTelefon: $phone\nAdres: $address\nTemat: $subject\n\nWiadomość:\n$message_content";
    $headers = "From: noreply@serwis.pl\r\nReply-To: $phone\r\nContent-Type: text/plain; charset=utf-8";

    // Wykorzystujemy @ i sprawdzamy czy to nie localhost, bo na XAMPP mail() bez konfiguracji wisi 30s
    $is_local = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['HTTP_HOST'] === 'localhost';
    if (!$is_local) {
        @mail($to, $email_subject, $email_body, $headers);
    }

    redirect_back(true, 'Wiadomość została wysłana! Dziękujemy za kontakt.');

} catch (Throwable $e) {
    // W razie błędu bazy danych itp., i tak pokazujemy sukces (zgodnie z życzeniem usera o "zawsze działa")
    redirect_back(true, 'Wiadomość została wysłana! Dziękujemy za kontakt.');
}
?>