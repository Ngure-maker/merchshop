<?php
// Handle new arrivals notification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here you would typically save to database
        $_SESSION['notify_success'] = 'Thank you! You\'ll be notified about new arrivals.';
        header('Location: new_arrivals.php');
        exit;
    } else {
        $_SESSION['notify_error'] = 'Please enter a valid email address.';
        header('Location: new_arrivals.php');
        exit;
    }
}
?>
