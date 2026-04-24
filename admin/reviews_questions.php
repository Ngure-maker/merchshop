<?php
// Use centralized session management
require_once '../config/environment.php';

// Auto-detect environment and load appropriate configuration
$is_localhost = (($_SERVER['SERVER_NAME'] == 'localhost') || 
                (strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) ||
                (strpos($_SERVER['SERVER_NAME'], '192.168') !== false) ||
                (php_sapi_name() === 'cli'));

if ($is_localhost) {
    require_once '../includes/auth.php';
    require_once '../includes/db.php';
} else {
    require_once __DIR__ . '/../config/universal_database.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';
}

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

if (!$auth->isAdmin()) {
    $_SESSION['error'] = 'You do not have permission to access the admin panel.';
    header('Location: ../dashboard.php');
    exit();
}

$db = new DBHelper();

$flash_success = '';
$flash_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $response = trim((string)($_POST['admin_response'] ?? ''));

    if ($response === '') {
        $flash_error = 'Response cannot be empty.';
    } elseif ($action === 'reply_review') {
        $review_id = (int)($_POST['review_id'] ?? 0);
        if ($review_id > 0) {
            $db->query("UPDATE product_reviews SET admin_response = ?, updated_at = NOW() WHERE id = ?", [$response, $review_id]);
            $flash_success = 'Review response saved.';
        } else {
            $flash_error = 'Invalid review.';
        }
    } elseif ($action === 'reply_question') {
        $question_id = (int)($_POST['question_id'] ?? 0);
        if ($question_id > 0) {
            $db->query("UPDATE product_questions SET admin_response = ? WHERE id = ?", [$response, $question_id]);
            $flash_success = 'Question response saved.';
        } else {
            $flash_error = 'Invalid question.';
        }
    }
}

$reviews = $db->fetchAll(
    "SELECT r.*, p.name as product_name, u.full_name
     FROM product_reviews r
     INNER JOIN products p ON p.id = r.product_id
     LEFT JOIN users u ON u.id = r.user_id
     ORDER BY r.created_at DESC"
);

$questions = $db->fetchAll(
    "SELECT q.*, p.name as product_name, u.full_name
     FROM product_questions q
     INNER JOIN products p ON p.id = q.product_id
     LEFT JOIN users u ON u.id = q.user_id
     ORDER BY q.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews & Questions - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/zetech-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../views/admin_header.php'; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="pt-4 pb-3">
            <h2 class="mb-1">Reviews & Questions</h2>
            <p class="text-muted mb-0">Respond to customer reviews and product questions.</p>
        </div>

        <?php if ($flash_success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flash_success); ?></div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($flash_error); ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Product Reviews</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $review): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?php echo htmlspecialchars($review['product_name']); ?></strong>
                                        <span class="text-warning">
                                            <?php echo str_repeat('★', (int)($review['rating'] ?? 0)); ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($review['full_name'] ?? 'Customer'); ?> · <?php echo htmlspecialchars($review['created_at'] ?? ''); ?>
                                    </div>
                                    <?php if (!empty($review['title'])): ?>
                                        <div class="fw-semibold mt-1"><?php echo htmlspecialchars($review['title']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($review['review_text'])): ?>
                                        <p class="mb-2 text-muted"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                    <?php endif; ?>
                                    <form method="POST" action="" class="mt-2">
                                        <input type="hidden" name="action" value="reply_review">
                                        <input type="hidden" name="review_id" value="<?php echo (int)$review['id']; ?>">
                                        <label class="form-label">Admin response</label>
                                        <textarea class="form-control" name="admin_response" rows="2" placeholder="Write a response..."><?php echo htmlspecialchars($review['admin_response'] ?? ''); ?></textarea>
                                        <button type="submit" class="btn btn-primary btn-sm mt-2">Save Response</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted">No reviews yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Product Questions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($questions)): ?>
                            <?php foreach ($questions as $question): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <strong><?php echo htmlspecialchars($question['product_name']); ?></strong>
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($question['full_name'] ?? $question['guest_name'] ?? 'Customer'); ?> · <?php echo htmlspecialchars($question['created_at'] ?? ''); ?>
                                    </div>
                                    <p class="mb-2 text-muted"><?php echo nl2br(htmlspecialchars($question['question_text'] ?? '')); ?></p>
                                    <form method="POST" action="" class="mt-2">
                                        <input type="hidden" name="action" value="reply_question">
                                        <input type="hidden" name="question_id" value="<?php echo (int)$question['id']; ?>">
                                        <label class="form-label">Admin response</label>
                                        <textarea class="form-control" name="admin_response" rows="2" placeholder="Write a response..."><?php echo htmlspecialchars($question['admin_response'] ?? ''); ?></textarea>
                                        <button type="submit" class="btn btn-primary btn-sm mt-2">Save Response</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted">No questions yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../views/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
