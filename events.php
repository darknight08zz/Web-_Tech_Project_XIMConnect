<?php
// Function to load and parse XML file
function loadEvents($eventType = 'all') {
    $events = [];

    // Determine which XML files to load
    if ($eventType === 'upcoming') {
        $xmlFiles = ['upcoming-events.xml'];
    } elseif ($eventType === 'past') {
        $xmlFiles = ['past-events.xml'];
    } else {
        $xmlFiles = ['upcoming-events.xml', 'past-events.xml'];
    }

    // Parse XML files
    foreach ($xmlFiles as $xmlFile) {
        if (file_exists($xmlFile)) {
            $xmlDoc = simplexml_load_file($xmlFile);
            
            foreach ($xmlDoc->event as $event) {
                $events[] = [
                    'id' => (int)$event->id,
                    'title' => (string)$event->title,
                    'date' => (string)$event->date,
                    'location' => (string)$event->location,
                    'description' => (string)$event->description,
                    'image' => (string)$event->image,
                    'department' => (string)$event->department
                ];
            }
        }
    }

    return $events;
}

// Function to format date
function formatDate($dateString) {
    $timestamp = strtotime($dateString);
    return date('l, F j, Y \a\t h:i A', $timestamp);
}

// Function to create event card HTML
function createEventCard($event, $isPast = false) {
    $pastClass = $isPast ? 'past-event' : '';
    $formattedDate = formatDate($event['date']);
    
    return "
        <div class='event-card {$pastClass}'>
            <div class='event-img' style='background-image: url(\"{$event['image']}\")'></div>
            <div class='event-info'>
                <div class='event-date'>{$formattedDate}</div>
                <h3 class='event-title'>{$event['title']}</h3>
                <div class='event-location'>📍 {$event['location']}</div>
                <p class='event-desc'>{$event['description']}</p>
                <a href='event-details.php?id={$event['id']}' class='event-link'>Learn More</a>
            </div>
        </div>
    ";
}

// Function to populate homepage events
function populateHomeEvents($events) {
    $now = new DateTime();
    $upcomingEvents = [];
    $pastEvents = [];

    foreach ($events as $event) {
        $eventDate = new DateTime($event['date']);
        if ($eventDate > $now) {
            $upcomingEvents[] = $event;
        } else {
            $pastEvents[] = $event;
        }
    }

    // Sort upcoming events by date (nearest first)
    usort($upcomingEvents, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });

    // Sort past events by date (most recent first)
    usort($pastEvents, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // Limit to 3 events
    $upcomingEvents = array_slice($upcomingEvents, 0, 3);
    $pastEvents = array_slice($pastEvents, 0, 3);

    // Output upcoming events
    $upcomingGrid = '';
    if (!empty($upcomingEvents)) {
        foreach ($upcomingEvents as $event) {
            $upcomingGrid .= createEventCard($event, false);
        }
    } else {
        $upcomingGrid = '<p>No upcoming events at this time. Check back soon!</p>';
    }

    // Output past events
    $pastGrid = '';
    if (!empty($pastEvents)) {
        foreach ($pastEvents as $event) {
            $pastGrid .= createEventCard($event, true);
        }
    } else {
        $pastGrid = '<p>No past events to display.</p>';
    }

    return [
        'upcomingGrid' => $upcomingGrid,
        'pastGrid' => $pastGrid
    ];
}

// Function to populate all upcoming events
function populateAllUpcomingEvents($events) {
    // Sort upcoming events by date (nearest first)
    usort($events, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });

    $eventsGrid = '';
    if (!empty($events)) {
        foreach ($events as $event) {
            $eventsGrid .= createEventCard($event, false);
        }
    } else {
        $eventsGrid = '<p>No upcoming events at this time. Check back soon!</p>';
    }

    return $eventsGrid;
}

// Function to populate all past events
function populateAllPastEvents($events) {
    // Sort past events by date (most recent first)
    usort($events, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    $eventsGrid = '';
    if (!empty($events)) {
        foreach ($events as $event) {
            $eventsGrid .= createEventCard($event, true);
        }
    } else {
        $eventsGrid = '<p>No past events to display.</p>';
    }

    return $eventsGrid;
}

// Function to populate department events
function populateDepartmentEvents($events, $department) {
    $now = new DateTime();
    $departmentEvents = array_filter($events, function($event) use ($department) {
        return $event['department'] === $department;
    });

    $upcomingEvents = array_filter($departmentEvents, function($event) use ($now) {
        return new DateTime($event['date']) > $now;
    });

    $pastEvents = array_filter($departmentEvents, function($event) use ($now) {
        return new DateTime($event['date']) < $now;
    });

    // Sort upcoming events by date (nearest first)
    usort($upcomingEvents, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });

    // Sort past events by date (most recent first)
    usort($pastEvents, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // Populate upcoming events
    $upcomingGrid = '';
    if (!empty($upcomingEvents)) {
        foreach ($upcomingEvents as $event) {
            $upcomingGrid .= createEventCard($event, false);
        }
    } else {
        $upcomingGrid = "<p>No upcoming events for {$department} at this time. Check back soon!</p>";
    }

    // Populate past events
    $pastGrid = '';
    if (!empty($pastEvents)) {
        foreach ($pastEvents as $event) {
            $pastGrid .= createEventCard($event, true);
        }
    } else {
        $pastGrid = "<p>No past events for {$department} to display.</p>";
    }

    return [
        'upcomingGrid' => $upcomingGrid,
        'pastGrid' => $pastGrid,
        'departmentHeading' => "{$department} Department Events"
    ];
}

// Function to get event details
function getEventDetails($events, $eventId) {
    foreach ($events as $event) {
        if ($event['id'] == $eventId) {
            return [
                'title' => $event['title'],
                'venue' => $event['location'],
                'dateOnly' => date('l, F j, Y', strtotime($event['date'])),
                'timeOnly' => date('h:i A', strtotime($event['date'])),
                'department' => $event['department'],
                'description' => $event['description'],
                'image' => $event['image']
            ];
        }
    }
    return null;
}
?>