<?php
// Handle newsletter subscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here you would typically save to database
        // For now, we'll just redirect back with success message
        $_SESSION['newsletter_success'] = 'Thank you for subscribing! Check your email for exclusive offers.';
        header('Location: deals.php');
        exit;
    } else {
        $_SESSION['newsletter_error'] = 'Please enter a valid email address.';
        header('Location: deals.php');
        exit;
    }
}
?>
