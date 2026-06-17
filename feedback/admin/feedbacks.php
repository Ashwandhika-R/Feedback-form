<?php
require_once '../config/db.php';
requireAdminLogin();
$conn = getDB();

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM feedbacks WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: feedbacks.php?msg=deleted');
    exit();
}

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="feedbacks_' . date('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Ack No','Student Name','Reg No','Dept','Year','Section','Email','Faculty','Subject',
        'Teaching Quality','Subject Knowledge','Communication','Doubt Clarity','Class Interaction','Punctuality',
        'Avg Rating','Strengths','Improvements','Feedback','Suggestions','Anonymous','Submitted At']);
    $r = $conn->query("SELECT * FROM feedbacks ORDER BY submitted_at DESC");
    while ($row = $r->fetch_assoc()) {
        $avg = round(($row['teaching_quality']+$row['subject_knowledge']+$row['communication_skills']+$row['doubt_clarification']+$row['classroom_interaction']+$row['punctuality'])/6, 2);
        fputcsv($out, [$row['id'],$row['acknowledgement_no'],$row['student_name']??'Anonymous',
            $row['register_number']??'',$row['department'],$row['year'],$row['section'],$row['email']??'',
            $row['faculty_name'],$row['subject_name'],$row['teaching_quality'],$row['subject_knowledge'],
            $row['communication_skills'],$row['doubt_clarification'],$row['classroom_interaction'],
            $row['punctuality'],$avg,$row['strengths'],$row['improvements'],$row['feedback'],
            $row['suggestions'],$row['is_anonymous']?'Yes':'No',$row['submitted_at']]);
    }
    fclose($out);
    exit();
}

// Filters
$search  = trim($_GET['search'] ?? '');
$dept    = trim($_GET['dept'] ?? '');
$faculty = trim($_GET['faculty'] ?? '');
$subject = trim($_GET['subject'] ?? '');
$sort    = in_array($_GET['sort'] ?? '', ['asc','desc']) ? $_GET['sort'] : 'desc';

$where = ["1=1"];
$params = []; $types = '';
if ($search) { $s = "%$search%"; $where[] = "(student_name LIKE ? OR faculty_name LIKE ? OR subject_name LIKE ? OR acknowledgement_no LIKE ?)"; $params = array_merge($params, [$s,$s,$s,$s]); $types .= 'ssss'; }
if ($dept)   { $where[] = "department=?"; $params[] = $dept; $types .= 's'; }
if ($faculty){ $where[] = "faculty_name=?"; $params[] = $faculty; $types .= 's'; }
if ($subject){ $where[] = "subject_name=?"; $params[] = $subject; $types .= 's'; }
$whereStr = implode(' AND ', $where);

// Pagination
$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$countStmt = $conn->prepare("SELECT COUNT(*) as c FROM feedbacks WHERE $whereStr");
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['c'];
$totalPages = ceil($totalRows / $perPage);

$stmt = $conn->prepare("SELECT * FROM feedbacks WHERE $whereStr ORDER BY submitted_at $sort LIMIT $perPage OFFSET $offset");
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$feedbacks = $stmt->get_result();

// Filter options
$depts    = $conn->query("SELECT DISTINCT department FROM feedbacks ORDER BY department");
$faculties= $conn->query("SELECT DISTINCT faculty_name FROM feedbacks ORDER BY faculty_name");
$subjects = $conn->query("SELECT DISTINCT subject_name FROM feedbacks ORDER BY subject_name");
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Feedbacks – Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="admin-sidebar">
  <div class="sidebar-brand"><i class="fas fa-graduation-cap fa-lg"></i><div><div>FeedbackPro</div><div style="font-size:0.7rem;opacity:0.7;font-weight:400;">Admin Panel</div></div></div>
  <nav class="mt-3">
    <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="feedbacks.php" class="nav-link active"><i class="fas fa-list-alt"></i> Feedbacks</a>
    <a href="reports.php" class="nav-link"><i class="fas fa-chart-bar"></i> Reports & Analytics</a>
    <hr style="border-color:rgba(255,255,255,0.1);margin:12px 24px;">
    <a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> Feedback Form</a>
    <a href="logout.php" class="nav-link" style="color:rgba(255,100,100,0.85);"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </nav>
</div>

<div class="admin-content">
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm" id="sidebarToggle" style="border:1px solid var(--border);background:var(--card-bg);"><i class="fas fa-bars"></i></button>
      <div class="page-title">Manage Feedbacks</div>
    </div>
    <div class="d-flex gap-2">
      <button class="theme-toggle" id="themeToggle">🌙 Dark</button>
      <a href="?export=csv&<?= http_build_query(array_filter(['search'=>$search,'dept'=>$dept,'faculty'=>$faculty,'subject'=>$subject])) ?>" 
         class="btn btn-sm" style="background:#2e7d32;color:white;border-radius:50px;padding:6px 16px;">
        <i class="fas fa-download me-1"></i> CSV
      </a>
    </div>
  </div>

  <div class="admin-main">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
    <div class="alert alert-success alert-auto-dismiss"><i class="fas fa-check-circle me-2"></i>Feedback deleted successfully.</div>
    <?php endif; ?>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <form method="GET" action="feedbacks.php">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label fw-600" style="font-size:0.85rem;">Search</label>
            <input type="text" class="form-control form-control-sm" name="search" placeholder="Name, Faculty, Ack No..." value="<?= e($search) ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label fw-600" style="font-size:0.85rem;">Department</label>
            <select class="form-select form-select-sm" name="dept">
              <option value="">All</option>
              <?php while ($d = $depts->fetch_assoc()): ?>
              <option value="<?= e($d['department']) ?>" <?= $dept===$d['department']?'selected':'' ?>><?= e($d['department']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-600" style="font-size:0.85rem;">Faculty</label>
            <select class="form-select form-select-sm" name="faculty">
              <option value="">All</option>
              <?php while ($f = $faculties->fetch_assoc()): ?>
              <option value="<?= e($f['faculty_name']) ?>" <?= $faculty===$f['faculty_name']?'selected':'' ?>><?= e($f['faculty_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label fw-600" style="font-size:0.85rem;">Subject</label>
            <select class="form-select form-select-sm" name="subject">
              <option value="">All</option>
              <?php while ($s = $subjects->fetch_assoc()): ?>
              <option value="<?= e($s['subject_name']) ?>" <?= $subject===$s['subject_name']?'selected':'' ?>><?= e($s['subject_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-1">
            <label class="form-label fw-600" style="font-size:0.85rem;">Sort</label>
            <select class="form-select form-select-sm" name="sort">
              <option value="desc" <?= $sort==='desc'?'selected':'' ?>>Newest</option>
              <option value="asc" <?= $sort==='asc'?'selected':'' ?>>Oldest</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-sm w-100" style="background:var(--primary);color:white;border-radius:8px;padding:7px;">
              <i class="fas fa-filter me-1"></i> Filter
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- RESULTS INFO -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div style="color:var(--text-muted);font-size:0.9rem;">
        Showing <strong><?= min($offset+1, $totalRows) ?>–<?= min($offset+$perPage, $totalRows) ?></strong> of <strong><?= $totalRows ?></strong> results
      </div>
      <a href="feedbacks.php" class="btn btn-sm" style="border:1px solid var(--border);border-radius:50px;font-size:0.82rem;">
        <i class="fas fa-times me-1"></i> Clear Filters
      </a>
    </div>

    <!-- TABLE -->
    <div class="card">
      <div class="table-responsive">
        <table class="table-custom w-100">
          <thead>
            <tr>
              <th>#</th><th>Ack No</th><th>Student</th><th>Dept</th><th>Faculty</th>
              <th>Subject</th><th>Avg Rating</th><th>Date</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($feedbacks->num_rows === 0): ?>
            <tr><td colspan="9" class="text-center py-4" style="color:var(--text-muted);">
              <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No feedbacks found.
            </td></tr>
            <?php else: while ($fb = $feedbacks->fetch_assoc()):
              $avg = round(($fb['teaching_quality']+$fb['subject_knowledge']+$fb['communication_skills']+$fb['doubt_clarification']+$fb['classroom_interaction']+$fb['punctuality'])/6, 1);
            ?>
            <tr>
              <td style="font-weight:600;color:var(--text-muted);"><?= $fb['id'] ?></td>
              <td><code style="font-size:0.78rem;"><?= e($fb['acknowledgement_no']) ?></code></td>
              <td>
                <?php if ($fb['is_anonymous']): ?>
                  <span style="color:var(--text-muted);"><i class="fas fa-user-secret me-1"></i>Anonymous</span>
                <?php else: ?>
                  <div style="font-weight:600;"><?= e($fb['student_name'] ?? 'N/A') ?></div>
                  <div style="font-size:0.78rem;color:var(--text-muted);"><?= e($fb['register_number'] ?? '') ?></div>
                <?php endif; ?>
              </td>
              <td><span style="font-size:0.8rem;background:rgba(57,73,171,0.1);color:var(--primary);padding:3px 9px;border-radius:50px;"><?= e($fb['department']) ?></span></td>
              <td style="font-weight:500;"><?= e($fb['faculty_name']) ?></td>
              <td><?= e($fb['subject_name']) ?></td>
              <td><span class="badge-rating rating-<?= round($avg) ?>">★ <?= $avg ?></span></td>
              <td style="font-size:0.82rem;white-space:nowrap;"><?= date('d M Y', strtotime($fb['submitted_at'])) ?></td>
              <td>
                <button class="btn btn-sm" style="background:rgba(57,73,171,0.1);color:var(--primary);border-radius:6px;"
                        data-bs-toggle="modal" data-bs-target="#modal<?= $fb['id'] ?>">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm ms-1" style="background:rgba(198,40,40,0.1);color:#c62828;border-radius:6px;"
                        onclick="confirmDelete(<?= $fb['id'] ?>)">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>

            <!-- Modal -->
            <div class="modal fade" id="modal<?= $fb['id'] ?>" tabindex="-1">
              <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content" style="border-radius:16px;overflow:hidden;background:var(--card-bg);">
                  <div class="modal-header" style="background:linear-gradient(135deg,var(--primary),var(--primary-light));color:white;border:none;">
                    <h5 class="modal-title"><i class="fas fa-comment-dots me-2"></i>Feedback Details – <?= e($fb['acknowledgement_no']) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body p-4">
                    <div class="row g-3">
                      <div class="col-md-6"><strong>Student:</strong> <?= $fb['is_anonymous'] ? 'Anonymous' : e($fb['student_name'] ?? 'N/A') ?></div>
                      <div class="col-md-6"><strong>Register No:</strong> <?= e($fb['register_number'] ?? 'N/A') ?></div>
                      <div class="col-md-6"><strong>Department:</strong> <?= e($fb['department']) ?></div>
                      <div class="col-md-3"><strong>Year:</strong> <?= e($fb['year']) ?></div>
                      <div class="col-md-3"><strong>Section:</strong> <?= e($fb['section']) ?></div>
                      <div class="col-md-6"><strong>Faculty:</strong> <?= e($fb['faculty_name']) ?></div>
                      <div class="col-md-6"><strong>Subject:</strong> <?= e($fb['subject_name']) ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-700 mb-3" style="color:var(--primary);">Ratings</h6>
                    <div class="row g-2">
                      <?php $ratingFields = ['Teaching Quality'=>$fb['teaching_quality'],'Subject Knowledge'=>$fb['subject_knowledge'],'Communication Skills'=>$fb['communication_skills'],'Doubt Clarification'=>$fb['doubt_clarification'],'Classroom Interaction'=>$fb['classroom_interaction'],'Punctuality'=>$fb['punctuality']];
                      foreach ($ratingFields as $rk => $rv): ?>
                      <div class="col-md-4">
                        <div style="background:var(--light-bg);border-radius:8px;padding:10px 14px;">
                          <div style="font-size:0.8rem;color:var(--text-muted);"><?= $rk ?></div>
                          <div style="font-size:1.1rem;">
                            <?php for ($s=1;$s<=5;$s++) echo $s<=$rv ? '⭐' : '☆'; ?>
                            <span style="font-weight:700;color:var(--primary);margin-left:4px;"><?= $rv ?>/5</span>
                          </div>
                        </div>
                      </div>
                      <?php endforeach; ?>
                    </div>
                    <?php if ($fb['strengths']): ?><div class="mt-3"><strong>Strengths:</strong><p><?= e($fb['strengths']) ?></p></div><?php endif; ?>
                    <?php if ($fb['improvements']): ?><div class="mt-2"><strong>Improvements:</strong><p><?= e($fb['improvements']) ?></p></div><?php endif; ?>
                    <?php if ($fb['feedback']): ?><div class="mt-2"><strong>Overall Feedback:</strong><p><?= e($fb['feedback']) ?></p></div><?php endif; ?>
                    <?php if ($fb['suggestions']): ?><div class="mt-2"><strong>Suggestions:</strong><p><?= e($fb['suggestions']) ?></p></div><?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-4">
      <ul class="pagination justify-content-center">
        <?php for ($p = 1; $p <= $totalPages; $p++):
          $q = http_build_query(array_filter(['search'=>$search,'dept'=>$dept,'faculty'=>$faculty,'subject'=>$subject,'sort'=>$sort,'page'=>$p])); ?>
        <li class="page-item <?= $p===$page?'active':'' ?>">
          <a class="page-link" href="?<?= $q ?>" style="<?= $p===$page?'background:var(--primary);border-color:var(--primary);':'' ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
