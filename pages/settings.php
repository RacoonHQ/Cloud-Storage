<?php
require_once '../includes/config.php';
require_once '../includes/session_check.php';
require_once '../classes/User.php';

$user = new User($conn);
$userData = $user->getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account Settings - Cloud Drive</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
    <!-- Floating Info Button -->
    <a href="about.php" class="floating-info-btn" title="Tentang Cloud Drive">
        <i class="bi bi-info-circle"></i>
    </a>
<body>
<div class="settings-wrapper">
    <h3 class="fw-bold mb-3">Account settings</h3>
    <?php if (isset($_GET['success']) && $_GET['success'] === 'profile_updated'): ?>
        <div class="alert alert-success" role="alert">
            Profile updated successfully!
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'email_exists'): ?>
        <div class="alert alert-danger" role="alert">
            Email sudah digunakan user lain!
        </div>
    <?php endif; ?>
    <form action="../php/file/settings.php" method="POST" enctype="multipart/form-data">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img src="<?php echo !empty($userData['photo_path']) ? htmlspecialchars($userData['photo_path']) : '../assets/img/pp.jpg'; ?>" class="settings-photo" id="avatarPreview" alt="Profile Photo">
            <div>
                <div class="settings-label">Profile Photo</div>
                <div class="text-muted" style="font-size:0.95em;">Accepted file type: .png. Less than 1MB</div>
                <div class="mt-2">
                    <input type="file" name="photo" accept="image/*" class="form-control form-control-sm" style="max-width:200px;" onchange="previewAvatar(event)">
                </div>
            </div>
        </div>
        <div class="settings-divider"></div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="settings-label">First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="settings-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="settings-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="settings-label">Phone Number</label>
                <input type="text" name="phone_number" class="form-control" value="<?php echo htmlspecialchars($userData['phone_number'] ?? ''); ?>">
            </div>
            <div class="col-md-6">
                <label class="settings-label">Country</label>
                <select name="country" class="form-select">
                    <option value="">Select Country</option>
                    <option value="Indonesia" <?php if(($userData['country'] ?? '') == 'Indonesia') echo 'selected'; ?>>Indonesia</option>
                    <option value="India" <?php if(($userData['country'] ?? '') == 'India') echo 'selected'; ?>>India</option>
                    <option value="USA" <?php if(($userData['country'] ?? '') == 'USA') echo 'selected'; ?>>USA</option>
                    <!-- Tambahkan negara lain sesuai kebutuhan -->
                </select>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.history.back();">Cancel</button>
        </div>
    </form>
    <div class="settings-divider"></div>
    <div>
        <div class="fw-bold mb-1">Deactivate your account</div>
        <div class="text-muted mb-2" style="font-size:0.95em;">Details about your company account and password</div>
        <form action="../php/user/deactivate.php" method="POST" onsubmit="return confirm('Are you sure you want to deactivate your account?');">
            <button type="submit" class="btn settings-btn-danger">Deactivate</button>
        </form>
    </div>
</div>
<script>
function previewAvatar(event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('avatarPreview').src = URL.createObjectURL(file);
    }
}
</script>
</body>
</html>