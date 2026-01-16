<?php
// Wyciszamy wszelkie błędy i powiadomienia, aby nie psuły formatu JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Nieprawidłowa metoda żądania']);
    exit;
}

try {
    // Rozpocznij sesję jeśli jeszcze nie istnieje (config.php już to robi, ale dla pewności)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Anti-spam: Max 3 wiadomości w ciągu 5 minut
    if (!isset($_SESSION['message_timestamps'])) {
        $_SESSION['message_timestamps'] = [];
    }

    $current_time = time();
    $five_minutes_ago = $current_time - (5 * 60);

    // Czyszczenie starych timestampów
    $_SESSION['message_timestamps'] = array_values(array_filter($_SESSION['message_timestamps'], function ($timestamp) use ($five_minutes_ago) {
        return $timestamp > $five_minutes_ago;
    }));

    // Sprawdzenie limitu
    if (count($_SESSION['message_timestamps']) >= 3) {
        echo json_encode([
            'success' => false,
            'type' => 'spam',
            'message' => 'Przekroczono limit wiadomości. Możesz wysłać maksymalnie 3 wiadomości w ciągu 5 minut. Spróbuj ponownie później.'
        ]);
        exit;
    }

    // Pobranie danych
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Zapis do bazy danych
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, phone, address, subject, message, created_at) 
        VALUES (:name, :phone, :address, :subject, :message, NOW())
    ");

    $stmt->execute([
        ':name' => $name,
        ':phone' => $phone,
        ':address' => !empty($address) ? $address : null,
        ':subject' => $subject,
        ':message' => $message
    ]);

    // Dodanie timestampu do sesji (anti-spam)
    $_SESSION['message_timestamps'][] = $current_time;

    // Wysyłka emaila
    $to = 'kontakt@serwis.pl';
    $email_subject = 'Nowa wiadomość z formularza: ' . $subject;
    $email_body = "Nowa wiadomość z formularza kontaktowego:\n\nImię: $name\nTelefon: $phone\nAdres: $address\nTemat: $subject\n\nWiadomość:\n$message";
    $headers = "From: noreply@serwis.pl\r\n";

    @mail($to, $email_subject, $email_body, $headers);

    // Zawsze sukces dla użytkownika
    echo json_encode([
        'success' => true,
        'message' => 'Wiadomość została wysłana bez problemu! Dziękujemy za kontakt.'
    ]);

} catch (Throwable $e) {
    // Nawet jeśli wystąpi błąd (np. brak kolumny w bazie), zwracamy sukces zgodnie z prośbą
    echo json_encode([
        'success' => true,
        'message' => 'Wiadomość została wysłana bez problemu! Dziękujemy za kontakt.'
    ]);
}
?>