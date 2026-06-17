<?php
require_once 'config/db.php';

$success = $error = '';
$ack_no = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDB();
    $isAnon = isset($_POST['is_anonymous']) ? 1 : 0;

    // Sanitize inputs
    $student_name    = $isAnon ? null : trim(htmlspecialchars($_POST['student_name'] ?? ''));
    $register_number = $isAnon ? null : trim(htmlspecialchars($_POST['register_number'] ?? ''));
    $department      = trim(htmlspecialchars($_POST['department'] ?? ''));
    $year            = trim(htmlspecialchars($_POST['year'] ?? ''));
    $section         = strtoupper(trim(htmlspecialchars($_POST['section'] ?? '')));
    $email           = $isAnon ? null : trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $faculty_name    = trim(htmlspecialchars($_POST['faculty_name'] ?? ''));
    $subject_name    = trim(htmlspecialchars($_POST['subject_name'] ?? ''));
    $strengths       = trim(htmlspecialchars($_POST['strengths'] ?? ''));
    $improvements    = trim(htmlspecialchars($_POST['improvements'] ?? ''));
    $feedback        = trim(htmlspecialchars($_POST['feedback'] ?? ''));
    $suggestions     = trim(htmlspecialchars($_POST['suggestions'] ?? ''));

    // Ratings
    $teaching_quality     = (int)($_POST['teaching_quality'] ?? 0);
    $subject_knowledge    = (int)($_POST['subject_knowledge'] ?? 0);
    $communication_skills = (int)($_POST['communication_skills'] ?? 0);
    $doubt_clarification  = (int)($_POST['doubt_clarification'] ?? 0);
    $classroom_interaction = (int)($_POST['classroom_interaction'] ?? 0);
    $punctuality          = (int)($_POST['punctuality'] ?? 0);

    // Server-side validation
    $errors = [];
    if (!$isAnon) {
        if (empty($student_name)) $errors[] = 'Student name is required.';
        if (empty($register_number) || !preg_match('/^[0-9A-Za-z]{4,15}$/', $register_number))
            $errors[] = 'Valid register number is required.';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Valid email is required.';
    }
    if (empty($department)) $errors[] = 'Department is required.';
    if (empty($year)) $errors[] = 'Year is required.';
    if (empty($section)) $errors[] = 'Section is required.';
    if (empty($faculty_name)) $errors[] = 'Faculty name is required.';
    if (empty($subject_name)) $errors[] = 'Subject name is required.';
    foreach ([$teaching_quality, $subject_knowledge, $communication_skills,
              $doubt_clarification, $classroom_interaction, $punctuality] as $r) {
        if ($r < 1 || $r > 5) { $errors[] = 'All ratings are required (1-5).'; break; }
    }

    if (empty($errors)) {
        $ack = generateAckNo();
        // Ensure unique
        $check = $conn->prepare("SELECT id FROM feedbacks WHERE acknowledgement_no=?");
        $check->bind_param('s', $ack);
        $check->execute();
        while ($check->get_result()->num_rows > 0) { $ack = generateAckNo(); }

        $stmt = $conn->prepare("INSERT INTO feedbacks 
            (acknowledgement_no,student_name,register_number,department,year,section,email,
             faculty_name,subject_name,teaching_quality,subject_knowledge,communication_skills,
             doubt_clarification,classroom_interaction,punctuality,strengths,improvements,
             feedback,suggestions,is_anonymous) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssssssiiiiiissssi',
            $ack, $student_name, $register_number, $department, $year, $section, $email,
            $faculty_name, $subject_name, $teaching_quality, $subject_knowledge,
            $communication_skills, $doubt_clarification, $classroom_interaction,
            $punctuality, $strengths, $improvements, $feedback, $suggestions, $isAnon);

        if ($stmt->execute()) {
            $success = 'Feedback submitted successfully!';
            $ack_no = $ack;
        } else {
            $error = 'Error saving feedback. Please try again.';
        }
        $stmt->close();
    } else {
        $error = implode('<br>', $errors);
    }
    $conn->close();
}

$departments = ['AI & Data Science','Computer Science','Information Technology',
                'Electronics & Communication','Mechanical','Civil','Electrical','MBA','MCA'];
$years = ['1st','2nd','3rd','4th'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Feedback Management System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <i class="fas fa-graduation-cap"></i> FeedbackPro
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <button class="theme-toggle" id="themeToggle">🌙 Dark</button>
      <a href="admin/login.php" class="btn btn-sm" style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);">
        <i class="fas fa-lock me-1"></i> Admin
      </a>
    </div>
  </div>
</nav>

<!-- HERO -->
<div class="hero-section">
  <div class="container text-center position-relative">
    <div class="hero-badge">🎓 B.Tech AI & Data Science</div>
    <h1>Student Feedback<br>Management System</h1>
    <p class="mt-3">Share your feedback to help improve teaching quality and academic excellence</p>
    <div class="mt-4">
      <a href="#feedbackForm" class="btn btn-light btn-lg rounded-pill px-4 me-2" style="color:var(--primary);font-weight:700;">
        <i class="fas fa-comment-dots me-2"></i> Submit Feedback
      </a>
    </div>
  </div>
</div>

<div class="container my-5">

<?php if ($success && $ack_no): ?>
<!-- SUCCESS / ACKNOWLEDGEMENT -->
<div class="ack-box mb-5">
  <div style="font-size:3rem;">✅</div>
  <h3 style="color:var(--success);font-weight:800;margin:12px 0;">Feedback Submitted Successfully!</h3>
  <p style="color:#555;">Your acknowledgement number is:</p>
  <div class="ack-number"><?= e($ack_no) ?></div>
  <p style="color:#555;margin-top:8px;">Please save this number for future reference.</p>
  <a href="index.php" class="btn btn-primary-custom mt-3">
    <i class="fas fa-plus me-2"></i> Submit Another Feedback
  </a>
</div>

<?php else: ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show alert-auto-dismiss" role="alert">
  <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- FEEDBACK FORM -->
<form id="feedbackForm" method="POST" action="index.php#feedbackForm" novalidate>

<!-- Anonymous Option -->
<div class="form-section mb-4">
  <div class="section-header"><i class="fas fa-user-secret"></i> Submission Type</div>
  <div class="p-4">
    <div class="form-check form-switch d-flex align-items-center gap-3">
      <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" 
             style="width:52px;height:28px;" <?= isset($_POST['is_anonymous']) ? 'checked' : '' ?>>
      <label class="form-check-label fw-600" for="is_anonymous" style="font-size:1rem;">
        <strong>Submit Anonymously</strong>
        <small class="text-muted d-block">Your identity will not be recorded</small>
      </label>
    </div>
  </div>
</div>

<!-- Personal Information -->
<div class="form-section mb-4" id="personalFields" <?= isset($_POST['is_anonymous']) ? 'style="display:none"' : '' ?>>
  <div class="section-header"><i class="fas fa-user-graduate"></i> Student Information</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label required">Student Name</label>
        <input type="text" class="form-control" id="student_name" name="student_name" 
               value="<?= e($_POST['student_name'] ?? '') ?>" placeholder="Enter your full name">
      </div>
      <div class="col-md-6">
        <label class="form-label required">Register Number</label>
        <input type="text" class="form-control" id="register_number" name="register_number" 
               value="<?= e($_POST['register_number'] ?? '') ?>" placeholder="e.g. 22AD001">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email Address</label>
        <input type="email" class="form-control" id="email" name="email" 
               value="<?= e($_POST['email'] ?? '') ?>" placeholder="student@college.edu">
      </div>
    </div>
  </div>
</div>

<!-- Academic Details -->
<div class="form-section mb-4">
  <div class="section-header"><i class="fas fa-university"></i> Academic Details</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label required">Department</label>
        <select class="form-select" id="department" name="department">
          <option value="">-- Select Department --</option>
          <?php foreach ($departments as $d): ?>
          <option value="<?= e($d) ?>" <?= ($_POST['department'] ?? '') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label required">Year</label>
        <select class="form-select" id="year" name="year">
          <option value="">-- Year --</option>
          <?php foreach ($years as $y): ?>
          <option value="<?= e($y) ?>" <?= ($_POST['year'] ?? '') === $y ? 'selected' : '' ?>><?= e($y) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label required">Section</label>
        <input type="text" class="form-control" id="section" name="section" maxlength="3"
               value="<?= e($_POST['section'] ?? '') ?>" placeholder="A / B / C">
      </div>
    </div>
  </div>
</div>

<!-- Faculty & Subject -->
<div class="form-section mb-4">
  <div class="section-header"><i class="fas fa-chalkboard-teacher"></i> Faculty & Subject Details</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label required">Faculty Name</label>
        <input type="text" class="form-control" id="faculty_name" name="faculty_name" 
               value="<?= e($_POST['faculty_name'] ?? '') ?>" placeholder="Enter faculty name">
      </div>
      <div class="col-md-6">
        <label class="form-label required">Subject Name</label>
        <input type="text" class="form-control" id="subject_name" name="subject_name" 
               value="<?= e($_POST['subject_name'] ?? '') ?>" placeholder="Enter subject name">
      </div>
    </div>
  </div>
</div>

<!-- Star Ratings -->
<div class="form-section mb-4">
  <div class="section-header"><i class="fas fa-star"></i> Performance Ratings (1–5 Stars)</div>
  <div class="p-4">
    <?php
    $ratingCriteria = [
        ['key' => 'teaching_quality',      'label' => 'Teaching Quality',       'icon' => 'fas fa-book-open'],
        ['key' => 'subject_knowledge',     'label' => 'Subject Knowledge',      'icon' => 'fas fa-brain'],
        ['key' => 'communication_skills',  'label' => 'Communication Skills',   'icon' => 'fas fa-comments'],
        ['key' => 'doubt_clarification',   'label' => 'Doubt Clarification',    'icon' => 'fas fa-question-circle'],
        ['key' => 'classroom_interaction', 'label' => 'Classroom Interaction',  'icon' => 'fas fa-users'],
        ['key' => 'punctuality',           'label' => 'Punctuality',            'icon' => 'fas fa-clock'],
    ];
    foreach ($ratingCriteria as $rc): ?>
    <div class="row align-items-center mb-3 pb-3" style="border-bottom:1px solid var(--border);">
      <div class="col-md-4">
        <label class="form-label mb-0 required">
          <i class="<?= $rc['icon'] ?> me-2 text-primary"></i><?= $rc['label'] ?>
        </label>
      </div>
      <div class="col-md-8">
        <div class="star-rating" id="<?= $rc['key'] ?>_group">
          <?php for ($i = 5; $i >= 1; $i--): 
            $checked = ($_POST[$rc['key']] ?? '') == $i ? 'checked' : ''; ?>
          <input type="radio" name="<?= $rc['key'] ?>" id="<?= $rc['key'] ?>_<?= $i ?>" value="<?= $i ?>" <?= $checked ?>>
          <label for="<?= $rc['key'] ?>_<?= $i ?>" title="<?= $i ?> Star">★</label>
          <?php endfor; ?>
        </div>
        <div class="rating-error" id="<?= $rc['key'] ?>_error" style="color:red;font-size:0.8rem;"></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Additional Feedback -->
<div class="form-section mb-4">
  <div class="section-header"><i class="fas fa-pen-to-square"></i> Detailed Feedback</div>
  <div class="p-4">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Strengths of Faculty</label>
        <textarea class="form-control" name="strengths" rows="3" 
          placeholder="What does this faculty do exceptionally well?"><?= e($_POST['strengths'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Areas for Improvement</label>
        <textarea class="form-control" name="improvements" rows="3" 
          placeholder="What could be improved?"><?= e($_POST['improvements'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Overall Feedback</label>
        <textarea class="form-control" name="feedback" rows="3" 
          placeholder="Share your overall experience..."><?= e($_POST['feedback'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Suggestions</label>
        <textarea class="form-control" name="suggestions" rows="3" 
          placeholder="Any suggestions for the department?"><?= e($_POST['suggestions'] ?? '') ?></textarea>
      </div>
    </div>
  </div>
</div>

<!-- Buttons -->
<div class="d-flex gap-3 justify-content-center mb-5">
  <button type="submit" class="btn-primary-custom" style="padding:14px 48px;">
    <i class="fas fa-paper-plane me-2"></i> Submit Feedback
  </button>
  <button type="reset" class="btn-accent" onclick="clearErrors()" style="padding:14px 40px;">
    <i class="fas fa-rotate-left me-2"></i> Reset Form
  </button>
</div>

</form>
<?php endif; ?>
</div>

<!-- FOOTER -->
<footer class="footer-custom">
  <div class="container">
    <div class="row">
      <div class="col-md-4 mb-4">
        <h5><i class="fas fa-graduation-cap me-2"></i>FeedbackPro</h5>
        <p style="font-size:0.9rem;">Student Feedback Management System for academic excellence and continuous improvement.</p>
      </div>
      <div class="col-md-4 mb-4">
        <h5>Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="index.php"><i class="fas fa-chevron-right me-1"></i> Submit Feedback</a></li>
          <li><a href="admin/login.php"><i class="fas fa-chevron-right me-1"></i> Admin Panel</a></li>
        </ul>
      </div>
      <div class="col-md-4 mb-4">
        <h5>Project Info</h5>
        <p style="font-size:0.9rem;">Built with PHP, MySQL, Bootstrap 5 & Chart.js<br>
        B.Tech AI & Data Science Portfolio Project</p>
      </div>
    </div>
    <div class="footer-bottom text-center">
      <p>&copy; <?= date('Y') ?> Student Feedback Management System. Designed for AI & Data Science Portfolio.</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
