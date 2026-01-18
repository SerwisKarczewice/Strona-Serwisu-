<?php


function get_site_visits()
{
    $counter_file = __DIR__ . '/../admin/counter.txt';

    // Create file if it doesn't exist
    if (!file_exists($counter_file)) {
        file_put_contents($counter_file, '0');
    }

    $count = (int) file_get_contents($counter_file);
    return $count;
}

function increment_site_visits()
{
    $counter_file = __DIR__ . '/../admin/counter.txt';

    // Create file if it doesn't exist
    if (!file_exists($counter_file)) {
        file_put_contents($counter_file, '0');
    }

    // Check if user has already visited in this session
    if (!isset($_SESSION['has_visited'])) {
        $count = (int) file_get_contents($counter_file);
        $count++;
        file_put_contents($counter_file, $count);
        $_SESSION['has_visited'] = true;
    }
}
?>