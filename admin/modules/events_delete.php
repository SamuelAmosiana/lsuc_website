<?php
// Delete Event Handler
$event_id = $_GET['id'] ?? '';

if (!$event_id) {
    header('Location: ?page=events');
    exit;
}

// Load events
$events_file = __DIR__ . '/../data/events.json';
$events = [];

if (file_exists($events_file)) {
    $events = json_decode(file_get_contents($events_file), true) ?: [];
}

// Find and delete event
$event_found = false;
$updated_events = array_filter($events, function($event) use ($event_id, &$event_found) {
    if ($event['id'] === $event_id) {
        $event_found = true;
        return false; // Remove this event
    }
    return true; // Keep other events
});

if ($event_found) {
    // Save updated events
    file_put_contents($events_file, json_encode(array_values($updated_events), JSON_PRETTY_PRINT));
    
    // Log activity
    logActivity('Deleted event', 'Event ID: ' . $event_id);
    
    // Redirect with success message
    header('Location: ?page=events&success=Event+deleted+successfully');
} else {
    // Event not found
    header('Location: ?page=events&error=Event+not+found');
}
exit;

function logActivity($action, $details = '') {
    $log_file = __DIR__ . '/../data/activity_log.json';
    $logs = [];
    
    if (file_exists($log_file)) {
        $logs = json_decode(file_get_contents($log_file), true) ?: [];
    }
    
    $logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'username' => $_SESSION['admin_username'] ?? 'Unknown',
        'action' => $action,
        'details' => $details,
        'status' => 'success',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    
    // Keep last 1000 logs
    $logs = array_slice($logs, -1000);
    
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}
?>
