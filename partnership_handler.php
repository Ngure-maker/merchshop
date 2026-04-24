<?php
// Handle partnership inquiry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = filter_var($_POST['company_name'], FILTER_SANITIZE_STRING);
    $partnership_type = filter_var($_POST['partnership_type'], FILTER_SANITIZE_STRING);
    $contact_person = filter_var($_POST['contact_person'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    
    // Validate required fields
    if (empty($company_name) || empty($partnership_type) || empty($contact_person) || empty($email) || empty($phone) || empty($message)) {
        $_SESSION['partnership_error'] = 'Please fill in all required fields.';
        header('Location: partners.php');
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['partnership_error'] = 'Please enter a valid email address.';
        header('Location: partners.php');
        exit;
    }
    
    // Here you would typically:
    // 1. Save to database
    // 2. Send email notification
    // 3. Add to newsletter if requested
    
    $_SESSION['partnership_success'] = 'Thank you for your partnership inquiry! Our team will review your proposal and get back to you within 3 business days.';
    header('Location: partners.php');
    exit;
}
?>
