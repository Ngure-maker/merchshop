<?php
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $_SESSION['error_message'] = 'Please fill in all required fields.';
        header('Location: contact.php');
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = 'Please enter a valid email address.';
        header('Location: contact.php');
        exit;
    }
    
    // Here you would typically:
    // 1. Save to database
    // 2. Send email notification
    // 3. Add to newsletter if requested
    
    // For now, we'll just show success message
    $_SESSION['success_message'] = 'Thank you for contacting us! We will get back to you within 24 hours.';
    header('Location: contact.php');
    exit;
}
?>
