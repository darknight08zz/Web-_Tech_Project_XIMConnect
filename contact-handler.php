<?php
// contact-handler.php

function ensureFilePermissions() {
    $xmlFile = 'contacts.xml';
    $directory = dirname($xmlFile);

    // Ensure directory exists
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }

    // Set directory permissions
    chmod($directory, 0755);

    // Create file if it doesn't exist
    if (!file_exists($xmlFile)) {
        file_put_contents($xmlFile, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<contacts>\n</contacts>");
    }

    // Set file permissions
    chmod($xmlFile, 0644);
}

// Sanitize input for XML
function sanitizeXmlInput($input) {
    // Remove any non-printable characters
    $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);
    
    // Convert special characters to XML entities
    return htmlspecialchars($input, ENT_XML1, 'UTF-8');
}

// Add contact to XML
function addContactToXml($name, $email, $message) {
    ensureFilePermissions();

    $xmlFile = 'contacts.xml';
    
    // Load XML file
    $xml = simplexml_load_file($xmlFile);
    
    // Create new contact entry
    $contact = $xml->addChild('contact');
    $contact->addChild('id', uniqid());
    $contact->addChild('name', sanitizeXmlInput($name));
    $contact->addChild('email', sanitizeXmlInput($email));
    $contact->addChild('message', sanitizeXmlInput($message));
    $contact->addChild('timestamp', date('c'));
    
    // Save XML file
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    
    return $dom->save($xmlFile) !== false;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and validate inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Simple validation
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    // Respond to submission
    if (empty($errors)) {
        try {
            if (addContactToXml($name, $email, $message)) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Message submitted successfully!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Failed to save message'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'An unexpected error occurred'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => implode(', ', $errors)
        ]);
    }
    
    exit;
}
