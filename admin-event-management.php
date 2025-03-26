<?php
// Load events for management
function loadEventsForManagement() {
    $events = [

    ];
    
    return $events;
}

// Add new event
function addEvent($eventData) {

    $requiredFields = ['title', 'date', 'time', 'location', 'department', 'description'];
    
    foreach ($requiredFields as $field) {
        if (empty($eventData[$field])) {
            return ['success' => false, 'message' => "Missing required field: $field"];
        }
    }
    
    // Generate unique ID (in real scenario, this would be database-generated)
    $eventData['id'] = time();

    return ['success' => true, 'message' => 'Event added successfully', 'event' => $eventData];
}

// Delete event
function deleteEvent($eventId) {

    return ['success' => true, 'message' => "Event $eventId deleted"];
}

// Move event to past
function moveEventToPast($eventId) {

    return ['success' => true, 'message' => "Event $eventId moved to past events"];
}
?>
