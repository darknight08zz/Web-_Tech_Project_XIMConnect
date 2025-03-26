<?php
require_once 'events.php';

// Load all events
$events = loadEvents('all');

// Get event ID from URL
$eventId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Get event details
$eventDetails = $eventId ? getEventDetails($events, $eventId) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $eventDetails ? $eventDetails['title'] . ' - XIMConnect' : 'Event Not Found'; ?></title>
    <!-- Add Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="event-details.css">
</head>
<body>
    <header>
        <div class="header-left">
            <a href="index.html"><img src="https://upload.wikimedia.org/wikipedia/en/b/bb/XIM_University_Logo.png" alt="XIM University Logo"></a>
            <h1>XIMConnect</h1>
        </div>
        <div class="header-right">
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="upcoming-events.php">Upcoming Events</a>
                <a href="past-events.php">Past Events</a>
                <button onclick="window.location.href='admin-login.php'" class="admin-login-btn">Admin Login</button>
            </nav>
        </div>
    </header>

    <a href="index.php" class="back-button">← Back to Events</a>

    <?php if ($eventDetails): ?>
        <div class="event-detail-container">
            <img src="<?php echo htmlspecialchars($eventDetails['image']); ?>" alt="<?php echo htmlspecialchars($eventDetails['title']); ?>" class="event-image" id="eventImage">
            <h1 class="event-title" id="eventTitle"><?php echo htmlspecialchars($eventDetails['title']); ?></h1>
            <div class="event-info">
                <span class="event-info-label">Venue:</span> <span id="eventVenue"><?php echo htmlspecialchars($eventDetails['venue']); ?></span>
                <span class="event-info-label">Date:</span> <span id="eventDate"><?php echo htmlspecialchars($eventDetails['dateOnly']); ?></span>
                <span class="event-info-label">Time:</span> <span id="eventTime"><?php echo htmlspecialchars($eventDetails['timeOnly']); ?></span>
                <span class="event-info-label">Department:</span> <span id="eventPlace"><?php echo htmlspecialchars($eventDetails['department']); ?> Department</span>
            </div>
            <div class="event-description" id="eventDescription"><?php echo htmlspecialchars($eventDetails['description']); ?></div>
        </div>
    <?php else: ?>
        <div class="event-detail-container">
            <h1 class="event-title">Event Not Found</h1>
            <div class="event-description">
                <p>The requested event could not be found. Please check the event ID or return to the homepage.</p>
                <a href="index.php" class="back-button">Return to Homepage</a>
            </div>
        </div>
    <?php endif; ?>

    <footer>
        <div class="footer-container">
            <div class="footer-contact-form">
                <h2>Contact Us</h2>
                <p>Feel free to contact us any time. We will get back to you as soon as we can!</p>
                <form id="contact-form" action="contact-handler.php" method="post">
                    <input type="text" name="name" id="contact-name" placeholder="Name" required>
                    <input type="email" name="email" id="contact-email" placeholder="Email" required>
                    <textarea name="message" id="contact-message" placeholder="Message" rows="4" required></textarea>
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
    <script src="contact-script.js"></script>
</body>
</html>