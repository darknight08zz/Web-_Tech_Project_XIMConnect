<?php
require_once 'admin-auth.php';

// Check login status
checkLogin();

// XML file paths
define('UPCOMING_EVENTS_FILE', 'upcoming-events.xml');
define('PAST_EVENTS_FILE', 'past-events.xml');

// Function to load events from XML
function loadEventsFromXML($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    
    $xml = simplexml_load_file($filename);
    $events = [];
    
    if ($xml && $xml->event) {
        foreach ($xml->event as $eventXml) {
            $event = [
                'id' => (string)$eventXml->id,
                'title' => (string)$eventXml->title,
                'date' => (string)$eventXml->date,
                'time' => (string)$eventXml->time,
                'location' => (string)$eventXml->location,
                'department' => (string)$eventXml->department,
                'description' => (string)$eventXml->description,
                'image' => (string)$eventXml->image
            ];
            $events[] = $event;
        }
    }
    
    return $events;
}

// Function to save events to XML
function saveEventsToXML($filename, $events) {
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><events></events>');
    
    foreach ($events as $event) {
        $eventNode = $xml->addChild('event');
        $eventNode->addChild('id', htmlspecialchars($event['id']));
        $eventNode->addChild('title', htmlspecialchars($event['title']));
        $eventNode->addChild('date', htmlspecialchars($event['date']));
        $eventNode->addChild('time', htmlspecialchars($event['time']));
        $eventNode->addChild('location', htmlspecialchars($event['location']));
        $eventNode->addChild('department', htmlspecialchars($event['department']));
        $eventNode->addChild('description', htmlspecialchars($event['description']));
        $eventNode->addChild('image', htmlspecialchars($event['image']));
    }
    
    return $xml->asXML($filename);
}

// Function to generate unique event ID
function generateEventId() {
    // Load existing events
    $upcomingEvents = loadEventsFromXML(UPCOMING_EVENTS_FILE);
    $pastEvents = loadEventsFromXML(PAST_EVENTS_FILE);
    
    // Combine all events
    $allEvents = array_merge($upcomingEvents, $pastEvents);
    
    // If no events exist, start from 1
    if (empty($allEvents)) {
        return 'event_1';
    }
    
    // Extract numeric IDs and find the maximum
    $maxId = 0;
    foreach ($allEvents as $event) {
        // Extract numeric part from event ID
        $numericId = intval(str_replace('event_', '', $event['id']));
        $maxId = max($maxId, $numericId);
    }
    
    // Return next sequential ID
    return ($maxId + 1);
}

// Handle form submissions
$formMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_event':
                // Prepare event data
                $eventData = [
                    'id' => generateEventId(),
                    'title' => $_POST['eventTitle'],
                    'date' => $_POST['eventDate'],
                    'time' => $_POST['eventTime'],
                    'location' => $_POST['eventLocation'],
                    'department' => $_POST['eventDepartment'],
                    'description' => $_POST['eventDescription']
                ];

                // Handle image upload
                if (isset($_FILES['eventImage']) && $_FILES['eventImage']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = './images/';
                    $uploadFile = $uploadDir . basename($_FILES['eventImage']['name']);
                    if (move_uploaded_file($_FILES['eventImage']['tmp_name'], $uploadFile)) {
                        $eventData['image'] = $uploadFile;
                    }
                }

                // Load existing events
                $upcomingEvents = loadEventsFromXML(UPCOMING_EVENTS_FILE);
                
                // Add new event
                $upcomingEvents[] = $eventData;
                
                // Save updated events
                if (saveEventsToXML(UPCOMING_EVENTS_FILE, $upcomingEvents)) {
                    $formMessage = "Event added successfully!";
                } else {
                    $formMessage = "Error adding event.";
                }
                break;

            case 'delete_event':
                $eventId = $_POST['id'];
                $isUpcoming = isset($_POST['upcoming']) ? true : false;
                
                $filename = $isUpcoming ? UPCOMING_EVENTS_FILE : PAST_EVENTS_FILE;
                $events = loadEventsFromXML($filename);
                
                // Remove the event
                $events = array_filter($events, function($event) use ($eventId) {
                    return $event['id'] !== $eventId;
                });
                
                // Save updated events
                if (saveEventsToXML($filename, $events)) {
                    $formMessage = "Event deleted successfully!";
                } else {
                    $formMessage = "Error deleting event.";
                }
                break;

            case 'move_event':
                $eventId = $_POST['id'];
                
                // Load upcoming events
                $upcomingEvents = loadEventsFromXML(UPCOMING_EVENTS_FILE);
                
                // Find the event to move
                $eventToMove = null;
                $updatedUpcomingEvents = array_filter($upcomingEvents, function($event) use ($eventId, &$eventToMove) {
                    if ($event['id'] === $eventId) {
                        $eventToMove = $event;
                        return false;
                    }
                    return true;
                });
                
                // Load past events
                $pastEvents = loadEventsFromXML(PAST_EVENTS_FILE);
                
                // Add to past events
                if ($eventToMove) {
                    $pastEvents[] = $eventToMove;
                }
                
                // Save updated events
                saveEventsToXML(UPCOMING_EVENTS_FILE, $updatedUpcomingEvents);
                saveEventsToXML(PAST_EVENTS_FILE, $pastEvents);
                
                $formMessage = "Event moved to past events!";
                break;
        }
    }
}

// Load events
$upcomingEvents = loadEventsFromXML(UPCOMING_EVENTS_FILE);
$pastEvents = loadEventsFromXML(PAST_EVENTS_FILE);

// Sort events
usort($upcomingEvents, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});
usort($pastEvents, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Get admin username from session
$adminUsername = isset($_SESSION['adminUsername']) ? $_SESSION['adminUsername'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - XIMConnect</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header>
        <div class="header-left">
            <img src="https://upload.wikimedia.org/wikipedia/en/b/bb/XIM_University_Logo.png" alt="XIM University Logo">
            <h1>XIMConnect</h1>
        </div>
        <div class="header-right">
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="upcoming-events.php">Upcoming Events</a>
                <a href="past-events.php">Past Events</a>
                <form method="post" action="index.php" style="display:inline;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </nav>
        </div>
    </header>

    <div class="admin-dashboard">
        <div class="admin-header">
            <h2>Admin Dashboard</h2>
            <div id="adminInfo">Welcome, <span id="adminName"><?php echo htmlspecialchars($adminUsername); ?></span></div>
        </div>

        <?php if ($formMessage): ?>
            <div class="form-message <?php echo strpos($formMessage, 'Error') !== false ? 'error-message' : 'success-message'; ?>">
                <?php echo htmlspecialchars($formMessage); ?>
            </div>
        <?php endif; ?>

        <div class="admin-tabs">
            <div class="admin-tab active" data-tab="manageUpcoming">Manage Upcoming Events</div>
            <div class="admin-tab" data-tab="managePast">Manage Past Events</div>
            <div class="admin-tab" data-tab="addEvent">Add New Event</div>
        </div>

        <div id="manageUpcoming" class="tab-content active">
            <h3>Upcoming Events</h3>
            <table id="upcomingEventsTable" class="events-table">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($upcomingEvents as $event): ?>
                <tr>
                    <td><?php echo htmlspecialchars($event['id']); ?></td>
                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                    <td><?php echo htmlspecialchars(date('F j, Y', strtotime($event['date']))); ?></td>
                    <td><?php echo htmlspecialchars($event['department']); ?></td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="action" value="delete_event">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
                            <input type="hidden" name="upcoming" value="1">
                            <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this event?')">Delete</button>
                        </form>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="action" value="move_event">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
                            <button type="submit" class="action-btn move-btn" onclick="return confirm('Move this event to past events?')">Move to Past</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div id="managePast" class="tab-content">
            <h3>Past Events</h3>
            <table id="pastEventsTable" class="events-table">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($pastEvents as $event): ?>
                <tr>
                    <td><?php echo htmlspecialchars($event['id']); ?></td>
                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                    <td><?php echo htmlspecialchars(date('F j, Y', strtotime($event['date']))); ?></td>
                    <td><?php echo htmlspecialchars($event['department']); ?></td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="action" value="delete_event">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($event['id']); ?>">
                            <button type="submit" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this event?')">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div id="addEvent" class="tab-content">
            <h3>Add New Event</h3>
            <form id="addEventForm" class="add-event-form" method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_event">
                <div class="form-group">
                    <label for="eventTitle">Event Title</label>
                    <input type="text" id="eventTitle" name="eventTitle" required>
                </div>
                <div class="form-group">
                    <label for="eventDate">Event Date</label>
                    <input type="date" id="eventDate" name="eventDate" required>
                </div>
                <div class="form-group">
                    <label for="eventTime">Event Time</label>
                    <input type="time" id="eventTime" name="eventTime" required>
                </div>
                <div class="form-group">
                    <label for="eventLocation">Location</label>
                    <input type="text" id="eventLocation" name="eventLocation" required>
                </div>
                <div class="form-group">
                    <label for="eventDepartment">Department</label>
                    <select id="eventDepartment" name="eventDepartment" required>
                        <option value="">Select Department</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Business">Business</option>
                        <option value="Law">Law</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="eventDescription">Event Description</label>
                    <textarea id="eventDescription" name="eventDescription" required></textarea>
                </div>
                <div class="form-group">
                    <label for="eventImage">Image</label>
                    <input type="file" id="eventImage" name="eventImage" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="submit-btn">Add Event</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <!-- Footer content from admin-dashboard.html -->
        <div class="footer-container">
            <div class="footer-contact-form">
                <h2>Contact Us</h2>
                <p>Feel free to contact us any time. We will get back to you as soon as we can!</p>
                <form id="contact-form">
                    <input type="text" id="contact-name" placeholder="Name" required>
                    <input type="email" id="contact-email" placeholder="Email" required>
                    <textarea id="contact-message" placeholder="Message" rows="4" required></textarea>
                    <button type="submit" class="send-btn">SEND</button>
                </form>
            </div>
            <div class="footer-contact-info">
                <h3>Info</h3>
                <div class="footer-contact-info-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <span>events@ximuniversity.edu</span>
                </div>
                <div class="footer-contact-info-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span>+91 0644567890</span>
                </div>
                <div class="footer-contact-info-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 6-8 10-8 10s-8-4-8-10a8 8 0 1 1 16 0Z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>XIM University,Jatni,Odisha-752050</span>
                </div>
                <div class="footer-contact-info-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>09:00 - 18:00</span>
                </div>
            </div>
            <div class="footer-section">
                <h3>Connect with XIMConnect</h3>
                <p>Stay updated with our latest events!</p>
            </div>
        </div>
    </footer>

    <script src="admin-dashboard.js"></script>
</body>
</html>