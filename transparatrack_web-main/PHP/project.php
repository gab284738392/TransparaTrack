<?php
session_start();

require_once 'ADMIN PROFILE/db_connect.php'; // Provides the $pdo object

// --- DEFINE THE CORRECT WEB PATH ---
$base_web_path = '/transparatrack_web/PHP/ADMIN PROFILE/';

// Build header right-side HTML (login button or admin avatar)
$headerRightHtml = '<a href="Log-in_page/login.php" class="btn-login">Log in</a>';

// HEADER LOGIC: Check who is currently viewing the page (Session User)
if (isset($_SESSION['UserID'])) {
  try {
    $stmtUser = $pdo->prepare("SELECT ProfileImagePath, FullName FROM Users WHERE UserID = :id LIMIT 1");
    $stmtUser->execute([':id' => $_SESSION['UserID']]);
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $profilePath = $userRow['ProfileImagePath'] ?? '';
    if (!empty($profilePath)) {
      $imgSrc = $base_web_path . ltrim($profilePath, '/\\'); 
    } else {
      $imgSrc = $base_web_path . 'assets/profile.svg';
    }
    $headerRightHtml = '<a href="' . $base_web_path . 'adminprofile.php" class="admin-avatar"><img src="' . htmlspecialchars($imgSrc, ENT_QUOTES) . '" alt="Admin Profile"></a>';
  } catch (\Exception $e) {
    // leave default login link
  }
}

/* ---------------------------
 Helper function for dynamic filters
 --------------------------- */
function get_dynamic_filters($pdo, $field, $table = 'Projects') {
    $sql = "";
    if ($field === 'Year') {
        $sql = "SELECT DISTINCT YEAR(StartDate) as val FROM Projects WHERE StartDate IS NOT NULL ORDER BY val DESC";
    } elseif ($table === 'Departments') {
        $sql = "SELECT DISTINCT DeptName as val FROM Departments WHERE DeptName IS NOT NULL AND DeptName != '' ORDER BY val ASC";
    } else {
        $sql = "SELECT DISTINCT $field as val FROM $table WHERE $field IS NOT NULL AND $field != '' ORDER BY val ASC";
    }
    
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } catch (\PDOException $e) {
        error_log("Filter query failed: " . $e->getMessage());
        return []; 
    }
}
    
/* ---------------------------
  Route decision: list vs project detail
 --------------------------- */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project_detail = null;

if ($id > 0) {
    // 2. DB LOGIC: Fetch a single project for the detail view
    // CORRECTED JOIN: Using p.ProjectManagerID
    $sql = "SELECT p.*, b.AllocatedAmount, d.DeptName, u.FullName AS AuthorName
            FROM Projects p
            LEFT JOIN Budget b ON p.ProjectID = b.ProjectID
            LEFT JOIN ProjectDepartment pd ON p.ProjectID = pd.ProjectID
            LEFT JOIN Departments d ON pd.DeptID = d.DeptID
            LEFT JOIN Users u ON p.ProjectManagerID = u.UserID 
            WHERE p.ProjectID = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $project_detail = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ---------------------------
  If detail view: render full detail page
 --------------------------- */
if ($project_detail) {
    
    // Determine a "last updated" timestamp robustly:
    $lastUpdated = null;
    $possibleKeys = [
        'ActionTimestamp', 'LastUpdated', 'UpdatedAt', 'LastModified',
        'ModifiedDate', 'UpdatedOn', 'updated_at', 'modified_at', 'LastUpdate'
    ];
    foreach ($possibleKeys as $k) {
        if (!empty($project_detail[$k])) {
            $lastUpdated = $project_detail[$k];
            break;
        }
    }

    if ($lastUpdated === null) {
        $logTables = ['ProjectLog', 'ProjectHistory', 'Logs', 'AuditLog', 'ActivityLog', 'ProjectUpdates', 'Project_Audit'];
        foreach ($logTables as $lt) {
            try {
                $lsql = "SELECT ActionTimestamp FROM $lt WHERE ProjectID = :id ORDER BY ActionTimestamp DESC LIMIT 1";
                $lstmt = $pdo->prepare($lsql);
                $lstmt->execute([':id' => $id]);
                $res = $lstmt->fetchColumn();
                if ($res) {
                    $lastUpdated = $res;
                    break;
                }
            } catch (\PDOException $e) {
                continue;
            }
        }
    }

    if ($lastUpdated === null && !empty($project_detail['StartDate'])) {
        $lastUpdated = $project_detail['StartDate'];
    }

    $lastUpdatedFormatted = null;
    if (!empty($lastUpdated)) {
        $ts = strtotime($lastUpdated);
        if ($ts !== false) {
            $lastUpdatedFormatted = date('M j, Y g:i A', $ts);
        } else {
            $lastUpdatedFormatted = htmlspecialchars((string)$lastUpdated);
        }
    }

    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title><?php echo htmlspecialchars($project_detail['ProjectName']); ?> — TransparaTrack</title>
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <link rel="shortcut icon" href="assets/tplogo.svg">
      <link rel="stylesheet" href="/transparatrack_web/PHP/assets/style.css">
      <link rel="stylesheet" type="text/css" href="/transparatrack_web/PHP/assets/style.css">
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
      <style>
        :root {
          --brand-yellow: #F3B900;
          --brand-red: #AD2020;
          --muted-gray: #373131;
        }

        .back-link{
          display:inline-flex;
          align-items:center;
          gap:8px;
          color:#4149B7;
          text-decoration:none; 
          font-family:'Kodchasan',sans-serif;
          font-weight:600;
          transition: color .18s ease;
        }
        .back-link .label {
          position: relative;
          padding-bottom: 2px; 
          color: inherit;
        }
        .back-link .label::after {
          content: "";
          position: absolute;
          left: 0;
          right: 0;
          bottom: 0;
          height: 2px;
          background: currentColor;
          transform: scaleX(0);
          transform-origin: left;
          transition: transform .18s ease;
        }
        .back-link:hover .label::after,
        .back-link:focus .label::after,
        .back-link:focus-visible .label::after {
          transform: scaleX(1);
        }
        .back-link .arrow {
          display:inline-block;
          transition: transform .18s ease;
          color: inherit;
        }
        .back-link:hover .arrow,
        .back-link:focus .arrow {
          transform: translateX(-4px);
        }

        .page-inner {
          width: 100%;
          max-width: none;
          margin: 0 auto;
          padding: 0 36px;
          box-sizing: border-box;
        }
        .page-inner--spaced { margin: 28px auto; }

        .project-header { margin-bottom: 12px; }
        .project-separator {
          display:block;
          width:100%;
          max-width:1110px;
          height:2px;
          background:var(--brand-yellow);
          border-radius:2px;
          margin-top:6px;
          margin-bottom:20px;
        }

        .project-title {
          font-family: 'Kodchasan', sans-serif;
          font-size: 32px;
          color: var(--brand-red);
          font-weight: 700;
          margin: 0;
          line-height: 1.2;
          margin-top:100px;
        }

        .meta-updated-row {
          display:flex;
          gap:20px;
          align-items:flex-start;
          justify-content:space-between;
          margin: 8px 0 18px 0;
        }

        .detail-inner {
          padding-left: 40px;
          box-sizing: border-box;
          max-width: calc(100% - 40px);
        }

        .meta-list {
          display:flex;
          flex-direction:column;
          gap:10px;
          font-family:'Kodchasan';
          color:var(--muted-gray);
          width: 100%;
          box-sizing: border-box;
        }

        .meta-item {
          display:flex;
          gap:10px;
          align-items:center;
        }
        .meta-item img { width:18px; height:18px; object-fit:contain; margin-top:0; }
        .meta-text { display:flex; flex-direction:column; }
        .meta-label { font-size:13px; color:#666; }
        .meta-value { font-size:15px; color:#373131; font-weight:500; }

        .last-updated { font-size:13px; color:#666; text-align:right; min-width:180px; }

        .description { 
          margin-top:30px; 
          font-family: 'Inter', sans-serif; 
          color:#333; 
          line-height:1.6; 
          text-align: justify;
          text-justify: inter-word;
          -webkit-font-smoothing:antialiased;
          hyphens: auto;
          font-size: 15px; 
        }
        .description h3 {
          font-family: 'Kodchasan', sans-serif;
          font-size: 20px;
          margin: 0 0 8px 0;
          font-weight: 1000;
          color: #111;
        }

        .gallery {
          margin-top:22px;
          display:grid;
          gap:16px;
          width:100%;
        }

        .gallery .thumb {
          background:#D9D9D9;
          border-radius:8px;
          overflow:hidden;
          display:block;
        }
        .gallery .thumb img {
          width:100%;
          height:100%;
          object-fit:cover;
          display:block;
        }

        .gallery.count-1 { grid-template-columns: 1fr; }
        .gallery.count-1 .thumb { height: 520px; }
        .gallery.count-2 { grid-template-columns: repeat(2, 1fr); }
        .gallery.count-2 .thumb { height: 420px; }
        .gallery.count-3 { grid-template-columns: repeat(3, 1fr); }
        .gallery.count-3 .thumb { height: 320px; }
        .gallery.count-4 { grid-template-columns: repeat(2, 1fr); }
        .gallery.count-4 .thumb { height: 320px; }
        .gallery.count-5, .gallery.count-6 { grid-template-columns: repeat(3, 1fr); }
        .gallery.count-5 .thumb, .gallery.count-6 .thumb { height: 240px; }
        .gallery.count-more { grid-template-columns: repeat(3, 1fr); }
        .gallery.count-more .thumb { height: 220px; }

        .attachments { margin-top:25px; font-family: 'Inter', sans-serif; }
        .attachments h3 { font-family: 'Kodchasan', sans-serif; margin:40px 0 10px 0; font-size:20px; color:#111; font-weight:1000; }

        .attachment-list { list-style:none; padding:0; margin-bottom:100px; display:flex; flex-direction:column; gap:8px; }
        .attachment-list a { text-decoration:underline; text-decoration-thickness:1px; color:#111; font-weight:600; font-size:13px; display:inline-block; font-family: 'Inter', sans-serif; }

        .image-modal {
          display:none;
          position:fixed;
          z-index:1200;
          inset:0;
          align-items:center;
          justify-content:center;
          background: rgba(0,0,0,0.6);
        }
        .image-modal.open { display:flex; }
        .image-modal .modal-inner {
          position:relative;
          max-width: 95%;
          max-height: 95%;
          display:flex;
          align-items:center;
          justify-content:center;
        }
        .image-modal .modal-img {
          max-width: 100%;
          max-height: 100%;
          border-radius:6px;
          box-shadow: 0 10px 40px rgba(0,0,0,0.6);
        }
        .image-modal .modal-close,
        .image-modal .modal-prev,
        .image-modal .modal-next {
          position:absolute;
          background: rgba(255,255,255,0.9);
          border: none;
          color: #111;
          font-size:20px;
          padding:8px 12px;
          border-radius:6px;
          cursor:pointer;
        }
        .image-modal .modal-close { top:8px; right:8px; }
        .image-modal .modal-prev { left:8px; top:50%; transform:translateY(-50%); }
        .image-modal .modal-next { right:8px; top:50%; transform:translateY(-50%); }

        .header-top {
          display:flex;
          align-items:center;
          gap:12px;
          flex-wrap:wrap;
        }
        .title-wrapper { flex:1 1 auto; min-width:0; }
        .admin-icon-container { flex:0 0 auto; }

        @media (max-width: 1200px) {
          .gallery.count-1 .thumb { height:420px; }
          .gallery.count-2 .thumb { height:320px; }
          .gallery.count-3 .thumb { height:240px; }
          .gallery.count-4 .thumb { height:240px; }
          .gallery.count-5, .gallery.count-6 .thumb { height:180px; }
          .gallery.count-more .thumb { height:160px; }
        }

        @media (max-width: 980px) {
          .gallery.count-1 .thumb { height:360px; }
          .gallery.count-2 { grid-template-columns: repeat(2, 1fr); }
          .gallery.count-3 { grid-template-columns: repeat(2, 1fr); }
          .gallery.count-4 { grid-template-columns: repeat(2, 1fr); }
          .gallery.count-5, .gallery.count-6 { grid-template-columns: repeat(2, 1fr); }
          .gallery.count-more { grid-template-columns: repeat(2, 1fr); }
          .meta-updated-row { flex-direction:column; gap:12px; }
          .last-updated { text-align:left; min-width: auto; }
          .meta-list { max-width: none; }
          .page-inner { padding: 0 16px; }
          .detail-inner { padding-left: 16px; max-width: calc(100% - 16px); }
          .image-modal .modal-prev, .image-modal .modal-next { display:none; } 
        }

        @media (max-width: 640px) {
          .project-title { font-size: 24px; margin-top: 24px; }
          .project-separator { margin-bottom: 14px; }
          .detail-inner { padding-left: 8px; padding-right: 8px; }
          .description { font-size: 15px; }
          .gallery { gap: 10px; }
          .gallery.count-1 .thumb { height: 260px; }
          .gallery.count-2 .thumb, .gallery.count-3 .thumb, .gallery.count-4 .thumb { height: 180px; }
          .meta-item img { width:16px; height:16px; }
          .last-updated { text-align:left; font-size:13px; min-width: auto; }
        }
      </style>
    </head>
    <body>
    
      <?php
    
    echo '<div class="header-bar">';
        echo '<div class="header-top">';
            echo '<div class="icon-container">
                    <img src="/transparatrack_web/PHP/ADMIN PROFILE/assets/tplogo.svg" alt="TransparaTrack Logo">
                  </div>';
            echo '<div class="title-wrapper">';
                echo '<span class="header-title">TransparaTrack</span>';
                echo '<span class="header-subtitle">See the process, Start the progress</span>';
            echo '</div>';
            echo '<div class="admin-icon-container">';
                echo $headerRightHtml;
            echo '</div>';
        echo '</div>';
        
        echo '<div class="gradient-line"></div>';
        
        echo '<nav class="nav-container">';
        echo '  <ul>';
        echo '    <li><a href="/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/project.php">Projects</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/archive.php">Archive</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/history.php">History</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/about_us.php">About Us</a></li>';
        echo '  </ul>';
        echo '</nav>';
    echo '</div>';
      
      ?>

      <main class="page-container" style="grid-template-columns:1fr;">
        <div class="page-inner page-inner--spaced">

          <div style="margin-bottom:10px;">
            <a href="project.php" class="back-link">
              <span class="arrow" aria-hidden="true">&lt;</span>
              <span class="label">Back to Projects</span>
            </a>
          </div>

          <div class="project-header" role="heading" aria-level="1">
            <div class="project-title"><?php echo htmlspecialchars($project_detail['ProjectName'] ?: 'Untitled Project'); ?></div>
            <span class="project-separator" aria-hidden="true"></span>
          </div>

          <div class="detail-inner">

            <div class="meta-updated-row">
              <div class="meta-list" aria-label="Project metadata">
                <div class="meta-item">
                  <img src="media/project_date_icon.svg" alt="Date icon">
                  <div class="meta-text">
                    <span class="meta-label">Date</span>
                    <span class="meta-value"><?php echo !empty($project_detail['StartDate']) ? htmlspecialchars(date('F j, Y', strtotime($project_detail['StartDate']))) : 'N/A'; ?></span>
                  </div>
                </div>

                <div class="meta-item">
                  <img src="media/project_status_icon.svg" alt="Status icon">
                  <div class="meta-text">
                    <span class="meta-label">Status</span>
                    <span class="meta-value"><?php echo htmlspecialchars($project_detail['ProjectStatus'] ?? 'N/A'); ?></span>
                  </div>
                </div>

                <div class="meta-item">
                  <img src="media/project_budget_icon.svg" alt="Budget icon">
                  <div class="meta-text">
                    <span class="meta-label">Budget</span>
                    <span class="meta-value">
                      <?php
                        if (isset($project_detail['AllocatedAmount']) && $project_detail['AllocatedAmount'] !== null && $project_detail['AllocatedAmount'] !== '') {
                          echo '₱' . number_format((float)$project_detail['AllocatedAmount'], 2);
                        } else {
                          echo 'N/A';
                        }
                      ?>
                    </span>
                  </div>
                </div>

                <div class="meta-item">
                  <img src="media/project_category_icon.svg" alt="Category icon">
                  <div class="meta-text">
                    <span class="meta-label">Category</span>
                    <span class="meta-value"><?php echo htmlspecialchars($project_detail['ProjectType'] ?? 'N/A'); ?></span>
                  </div>
                </div>

                <div class="meta-item">
                  <img src="media/project_dept_icon.svg" alt="Department icon">
                  <div class="meta-text">
                    <span class="meta-label">Department</span>
                    <span class="meta-value"><?php echo htmlspecialchars($project_detail['DeptName'] ?? 'N/A'); ?></span>
                  </div>
                </div>

                <div class="meta-item">
                  <img src="/transparatrack_web/PHP/media/author.svg" alt="Author icon">
                  <div class="meta-text">
                    <span class="meta-label">Author</span>
                    <span class="meta-value"><?php echo htmlspecialchars($project_detail['AuthorName'] ?? 'N/A'); ?></span>
                  </div>
                </div>

                <div class="meta-item">
                  <img src="/transparatrack_web/PHP/media/project_update_icon.svg" alt="Update icon">
                  <div class="meta-text">
                    <span class="meta-label">Last Updated</span>
                    <span class="meta-value">
                      <?php
                        if ($lastUpdatedFormatted) {
                            echo htmlspecialchars($lastUpdatedFormatted);
                        } else {
                            echo 'N/A';
                        }
                      ?>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="description" role="region" aria-label="Project description">
              <h3>Description</h3>
              <div><?php echo nl2br(htmlspecialchars($project_detail['Description'] ?: 'No extended description available.')); ?></div>
            </div>

            <?php
              $detail_img_sql = "SELECT FilePath FROM Evidence WHERE ProjectID = :id AND EvidenceCategory = 'Photo' ORDER BY EvidenceID ASC";
              $img_stmt = $pdo->prepare($detail_img_sql);
              $img_stmt->execute([':id' => $project_detail['ProjectID']]);
              $images = $img_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

              $fullPaths = [];
              foreach ($images as $pimg) {
                  $fullPaths[] = $base_web_path . ltrim($pimg, '/\\');
              }

              $count = count($fullPaths);
              if ($count === 0) {
                  $galleryClass = 'count-0';
              } elseif ($count === 1) {
                  $galleryClass = 'count-1';
              } elseif ($count === 2) {
                  $galleryClass = 'count-2';
              } elseif ($count === 3) {
                  $galleryClass = 'count-3';
              } elseif ($count === 4) {
                  $galleryClass = 'count-4';
              } elseif ($count === 5) {
                  $galleryClass = 'count-5';
              } elseif ($count === 6) {
                  $galleryClass = 'count-6';
              } else {
                  $galleryClass = 'count-more';
              }
            ?>

            <div class="gallery <?php echo $galleryClass; ?>" role="region" aria-label="Project pictures">
              <?php
                if ($count > 0) {
                    $idx = 0;
                    foreach ($fullPaths as $fp) {
                        echo '<a class="thumb" href="' . htmlspecialchars($fp) . '" data-index="' . $idx . '" title="Open image" aria-label="Open image">';
                        echo '<img src="' . htmlspecialchars($fp) . '" alt="Project image">';
                        echo '</a>';
                        $idx++;
                    }
                } else {
                    for ($i = 0; $i < 6; $i++) {
                        echo '<div class="thumb" aria-hidden="true"></div>';
                    }
                }
              ?>
            </div>

            <div class="attachments" role="region" aria-label="Project attachments">
              <h3>Attachments</h3>
              <?php
                $attach_sql = "SELECT FilePath FROM Evidence WHERE ProjectID = :id AND (EvidenceCategory IS NULL OR EvidenceCategory <> 'Photo') ORDER BY EvidenceID ASC";
                $attach_stmt = $pdo->prepare($attach_sql);
                $attach_stmt->execute([':id' => $project_detail['ProjectID']]);
                $attachments = $attach_stmt->fetchAll(PDO::FETCH_COLUMN, 0);

                if ($attachments && count($attachments) > 0) {
                    echo '<ul class="attachment-list">';
                    foreach ($attachments as $attPath) {
                        $fullPath = $base_web_path . ltrim($attPath, '/\\');
                        $fileName = strtoupper(basename($attPath));
                        echo '<li><a href="' . htmlspecialchars($fullPath) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($fileName) . '</a></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<div style="color:#888;font-family:Inter, sans-serif;">No attachments available.</div>';
                }
              ?>
            </div>

          </div> </div>
      </main>

      <div id="imageModal" class="image-modal" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="modal-inner" role="document">
          <button class="modal-close" aria-label="Close image">&times;</button>
          <button class="modal-prev" aria-label="Previous image">&#10094;</button>
          <img class="modal-img" src="" alt="Full size project image">
          <button class="modal-next" aria-label="Next image">&#10095;</button>
        </div>
      </div>

      <script>
        (function(){
          var galleryImages = <?php echo json_encode($fullPaths, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
          var modal = document.getElementById('imageModal');
          var modalImg = modal.querySelector('.modal-img');
          var btnClose = modal.querySelector('.modal-close');
          var btnPrev = modal.querySelector('.modal-prev');
          var btnNext = modal.querySelector('.modal-next');
          var currentIndex = 0;

          function openModal(index) {
            if (!galleryImages || galleryImages.length === 0) return;
            currentIndex = (index + galleryImages.length) % galleryImages.length;
            modalImg.src = galleryImages[currentIndex];
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            preload((currentIndex+1) % galleryImages.length);
            preload((currentIndex-1+galleryImages.length) % galleryImages.length);
          }

          function closeModal() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            modalImg.src = '';
            document.body.style.overflow = '';
          }

          function showNext() {
            if (!galleryImages || galleryImages.length === 0) return;
            currentIndex = (currentIndex + 1) % galleryImages.length;
            modalImg.src = galleryImages[currentIndex];
            preload((currentIndex+1) % galleryImages.length);
          }

          function showPrev() {
            if (!galleryImages || galleryImages.length === 0) return;
            currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
            modalImg.src = galleryImages[currentIndex];
            preload((currentIndex-1+galleryImages.length) % galleryImages.length);
          }

          function preload(i){
            var img = new Image();
            img.src = galleryImages[i];
          }

          var thumbs = document.querySelectorAll('.gallery .thumb[data-index]');
          thumbs.forEach(function(a){
            a.addEventListener('click', function(ev){
              ev.preventDefault();
              var idx = parseInt(this.getAttribute('data-index'), 10);
              openModal(idx);
            });
          });

          btnClose.addEventListener('click', closeModal);
          btnNext.addEventListener('click', showNext);
          btnPrev.addEventListener('click', showPrev);

          modal.addEventListener('click', function(ev){
            if (ev.target === modal || ev.target.classList.contains('modal-inner')) { closeModal(); }
          });

          document.addEventListener('keydown', function(ev){
            if (modal.classList.contains('open')) {
              if (ev.key === 'Escape') { closeModal(); }
              else if (ev.key === 'ArrowRight') { showNext(); }
              else if (ev.key === 'ArrowLeft') { showPrev(); }
            }
          });

          (function addSwipe(node){
            var startX = 0, startY = 0;
            node.addEventListener('touchstart', function(e){
              var t = e.touches[0];
              startX = t.clientX; startY = t.clientY;
            }, {passive:true});
            node.addEventListener('touchend', function(e){
              var t = e.changedTouches[0];
              var dx = t.clientX - startX;
              var dy = t.clientY - startY;
              if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
                if (dx < 0) showNext(); else showPrev();
              }
            }, {passive:true});
          })(modal);

        })();
      </script>

<footer class="site-footer">
    <div class="footer-gradient-line"></div>
    <div class="footer-content-area">
        <div class="footer-column">
            <ul>
                <li><a href="http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>
                <li><a href="http://localhost/transparatrack_web/PHP/project.php">Projects</a></li>
                <li><a href="http://localhost/transparatrack_web/PHP/archive.php">Archive</a></li>
                <li><a href="http://localhost/transparatrack_web/PHP/history.php">History</a></li>
                <li><a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <ul>
                <li><a href="http://localhost/transparatrack_web/PHP/ADMIN%20PROFILE/adminprofile.php">Profile</a></li>
                <li><a href="http://localhost/transparatrack_web/PHP/Log-in_page/terms-conditions.php">Terms and Conditions</a></li>
                <li><a href="http://localhost/transparatrack_web/PHP/Log-in_page/privacy-policy.php">Privacy Policy</a></li>
                <li><a href="http://localhost/transparatrack_web/PHP/HELP/help.php">Help</a></li>
            </ul>
        </div>
        <div class="footer-logo">TransparaTrack</div>
    </div>
</footer>

    </body>
    </html>
    <?php
    exit;
}

/* ---------------------------
  Listing page
 --------------------------- */

// 4. DB LOGIC: Get dynamic filter options from DB 
$filter_years = get_dynamic_filters($pdo, 'Year');
$filter_status = get_dynamic_filters($pdo, 'ProjectStatus', 'Projects');
$filter_categories = get_dynamic_filters($pdo, 'ProjectType', 'Projects');
$filter_departments = get_dynamic_filters($pdo, 'DeptName', 'Departments');


/* -------------------------
  Read query params (filters)
 ------------------------- */
$title = isset($_GET['title']) ? trim($_GET['title']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$status_filters = [];
if (isset($_GET['status'])) {
    if (is_array($_GET['status'])) $status_filters = array_values(array_filter($_GET['status'], 'strlen'));
    else if ($_GET['status'] !== '') $status_filters = [$_GET['status']];
}

$category_filters = [];
if (isset($_GET['category'])) {
    if (is_array($_GET['category'])) $category_filters = array_values(array_filter($_GET['category'], 'strlen'));
    else if ($_GET['category'] !== '') $category_filters = [$_GET['category']];
}

$department_filters = [];
if (isset($_GET['department'])) {
    if (is_array($_GET['department'])) $department_filters = array_values(array_filter($_GET['department'], 'strlen'));
    else if ($_GET['department'] !== '') $department_filters = [$_GET['department']];
}

$budget_range = isset($_GET['budget_range']) ? $_GET['budget_range'] : '';
$budget_min = isset($_GET['budget_min']) ? trim($_GET['budget_min']) : '';
$budget_max = isset($_GET['budget_max']) ? trim($_GET['budget_max']) : '';
$year_select = isset($_GET['year']) ? trim($_GET['year']) : '';
$year_min = isset($_GET['year_min']) ? trim($_GET['year_min']) : '';
$year_max = isset($_GET['year_max']) ? trim($_GET['year_max']) : '';

$card_height_option = isset($_GET['card_height']) ? $_GET['card_height'] : 'auto';
$card_height_custom = isset($_GET['card_height_custom']) ? trim($_GET['card_height_custom']) : '';

/* sanitize and compute pixel value */
$card_px = null;
if ($card_height_option === 'short') {
    $card_px = 360;
} elseif ($card_height_option === 'medium') {
    $card_px = 480;
} elseif ($card_height_option === 'tall') {
    $card_px = 560;
} elseif ($card_height_option === 'custom') {
    $digits = preg_replace('/[^\d]/', '', $card_height_custom);
    if ($digits !== '') {
        $val = (int)$digits;
        if ($val < 120) $val = 120;
        if ($val > 2000) $val = 2000;
        $card_px = $val;
    } else {
        $card_px = 480;
    }
} else {
    $card_px = null;
}

function parse_budget_to_int($budgetStr) {
    if ($budgetStr === null || $budgetStr === '') return null;
    $digits = preg_replace('/[^\d]/', '', $budgetStr);
    if ($digits === '') return null;
    return (int)$digits;
}

/* -------------------------
  5. DB LOGIC: Apply filters by building a dynamic SQL query
 ------------------------- */
// CORRECTED JOIN: Using p.ProjectManagerID
$sql_base = "FROM Projects p
             LEFT JOIN Budget b ON p.ProjectID = b.ProjectID
             LEFT JOIN ProjectDepartment pd ON p.ProjectID = pd.ProjectID
             LEFT JOIN Departments d ON pd.DeptID = d.DeptID
             LEFT JOIN Users u ON p.ProjectManagerID = u.UserID";

// SELECT UPDATE: Get FullName as author_name
$select_base = "SELECT p.ProjectID as id, p.ProjectName as title, p.StartDate as date, p.ProjectStatus as status, 
                p.ProjectType as category, b.AllocatedAmount as budget_val, d.DeptName as department, u.FullName as author_name";

$conditions = [];
$params = [];

if ($title !== '') {
    $conditions[] = "p.ProjectName LIKE ?";
    $params[] = "%$title%";
}
$minYear = null; $maxYear = null;
if ($year_select === 'custom') {
    $ynMin = preg_replace('/[^\d]/', '', $year_min);
    $ynMax = preg_replace('/[^\d]/', '', $year_max);
    if ($ynMin !== '') $minYear = (int)$ynMin;
    if ($ynMax !== '') $maxYear = (int)$ynMax;
} elseif ($year_select !== '' && ctype_digit($year_select)) {
    $minYear = $maxYear = (int)$year_select;
}
if ($minYear !== null) { $conditions[] = "YEAR(p.StartDate) >= ?"; $params[] = $minYear; }
if ($maxYear !== null) { $conditions[] = "YEAR(p.StartDate) <= ?"; $params[] = $maxYear; }

if (!empty($status_filters)) {
    $placeholders = implode(',', array_fill(0, count($status_filters), '?'));
    $conditions[] = "p.ProjectStatus IN ($placeholders)";
    $params = array_merge($params, $status_filters);
}

if (!empty($category_filters)) {
    $placeholders = implode(',', array_fill(0, count($category_filters), '?'));
    $conditions[] = "p.ProjectType IN ($placeholders)";
    $params = array_merge($params, $category_filters);
}

if (!empty($department_filters)) {
    $placeholders = implode(',', array_fill(0, count($department_filters), '?'));
    $conditions[] = "d.DeptName IN ($placeholders)";
    $params = array_merge($params, $department_filters);
}

$minBudget = null; $maxBudget = null;
if ($budget_range === 'above50k') {
    $minBudget = 50000 + 1;
} elseif ($budget_range === 'below50k') {
    $maxBudget = 50000;
} elseif ($budget_range === 'custom') {
    $minParsed = parse_budget_to_int($budget_min);
    $maxParsed = parse_budget_to_int($budget_max);
    if ($minParsed !== null) $minBudget = $minParsed;
    if ($maxParsed !== null) $maxBudget = $maxParsed;
}
if ($minBudget !== null) { $conditions[] = "b.AllocatedAmount >= ?"; $params[] = $minBudget; }
if ($maxBudget !== null) { $conditions[] = "b.AllocatedAmount <= ?"; $params[] = $maxBudget; }

$where_sql = "";
if (!empty($conditions)) {
    $where_sql = " WHERE " . implode(" AND ", $conditions);
}

try {
    $count_sql = "SELECT COUNT(DISTINCT p.ProjectID) as total " . $sql_base . $where_sql;
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $totalRow = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_projects = (int)($totalRow['total'] ?? 0);
} catch (\PDOException $e) {
    error_log("Project count query failed: " . $e->getMessage());
    $total_projects = 0;
    $project_list_error = "Error counting projects: " . $e->getMessage();
}

// Pagination parameters
$perPage = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalPages = $total_projects > 0 ? (int)ceil($total_projects / $perPage) : 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order_sql = ($sort === 'oldest') ? " ORDER BY p.StartDate ASC" : " ORDER BY p.StartDate DESC";

$sql = $select_base . " " . $sql_base . $where_sql . " GROUP BY p.ProjectID" . $order_sql . " LIMIT ? OFFSET ?";

try {
    $stmt = $pdo->prepare($sql);
    $execParams = array_merge($params, [$perPage, $offset]);
    $stmt->execute($execParams);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Project list query failed: " . $e->getMessage());
    $projects = [];
    $project_list_error = "Error fetching projects: " . $e->getMessage();
}

function build_page_url($pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return 'project.php?' . htmlspecialchars(http_build_query($params));
}

/* ---------------------------
   Hard-coded options for checkboxes
-------------------------- */
$statusOptions = [
    "Not Started", "Ongoing", "Completed", "On Hold", "Delayed", "Cancelled",
];

$categoryOptions = [
    "Imprastruktura at Pampublikong Gawa",
    "Kalusugan, Nutrisyon, at Serbisyo Sosyal",
    "Kapayapaan, Kaayusan, at Pampublikong Kaligtasan",
    "Paghahanda at Pagtugon sa Sakuna",
    "Pamamahala sa Kapaligiran",
    "Pangkabuhayan at Pagpapaunlad ng Ekonomiya",
    "Kabataan at Pagpapaunlad ng Sports",
    "Pamamahala at Operasyon",
    "Other",
];

$categoryLabels = [
    "Imprastruktura at Pampublikong Gawa" => "Infrastructure",
    "Kalusugan, Nutrisyon, at Serbisyo Sosyal" => "Health & Social Services",
    "Kapayapaan, Kaayusan, at Pampublikong Kaligtasan" => "Peace, Order & Public Safety",
    "Paghahanda at Pagtugon sa Sakuna" => "Disaster Risk Reduction - DRRM",
    "Pamamahala sa Kapaligiran" => "Environmental Management",
    "Pangkabuhayan at Pagpapaunlad ng Ekonomiya" => "Livelihood & Economic Dev't",
    "Kabataan at Pagpapaunlad ng Sports" => "SK Projects",
    "Pamamahala at Operasyon" => "Governance & Admin",
    "Other" => "Other",
];

$departmentOptions = [
    "Punong Barangay", "Sangguniang Barangay", "Barangay Development Council (BDC)", "Barangay Peace and Order Committee (BPOC)",
    "Barangay Disaster Risk Reduction and Management Committee (BDRRMC)", "Barangay Anti-Drug Abuse Council (BADAC)",
    "Barangay Council for the Protection of Children (BCPC)", "Barangay Ecological Solid Waste Management Committee (BESWMC)",
    "Lupon Tagapamayapa (Barangay Justice System)", "Barangay Health Workers (BHWs)", "Barangay Public Safety Officers (BPSO)",
    "Committee on Health and Sanitation", "Committee on Livelihood and Cooperatives", "Committee on Infrastructure",
    "Committee on Rules and Ordinances", "Other",
];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Projects — TransparaTrack</title>
 
  <link rel="stylesheet" href="/transparatrack_web/PHP/assets/style.css"> 
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="shortcut icon" href="/transparatrack_web/PHP/assets/tplogo.svg">

  <style>
    :root {
      --brand-yellow: #F3B900;
      --brand-red: #AD2020;
      --muted-gray: #373131;
      --filter-option-gap: 10px;
    }

    .back-link{
      display:inline-flex;
      align-items:center;
      gap:8px;
      color:#4149B7;
      text-decoration:none; 
      font-family:'Kodchasan',sans-serif;
      font-weight:600;
      transition: color .18s ease;
    }
    .back-link .label {
      position: relative;
      padding-bottom: 2px; 
      color: inherit;
    }
    .back-link .label::after {
      content: "";
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: 2px;
      background: currentColor;
      transform: scaleX(0);
      transform-origin: left;
      transition: transform .18s ease;
    }
    .back-link:hover .label::after,
    .back-link:focus .label::after,
    .back-link:focus-visible .label::after {
      transform: scaleX(1);
    }
    .back-link .arrow {
      display:inline-block;
      transition: transform .18s ease;
      color: inherit;
    }
    .back-link:hover .arrow,
    .back-link:focus .arrow {
      transform: translateX(-4px);
    }

    body { background-color: #EBE9E9; }

    .page-inner {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 16px;
      box-sizing: border-box;
    }
    .page-inner--spaced { margin: 28px auto; }

    .select-full { width:100%; padding:8px; border-radius:6px; border:1px solid #e6e6e6; font-size:15px; }
    .inline-row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .inline-row input[type="text"]{ padding:6px 8px; border-radius:6px; border:1px solid #ddd; font-size:14px; }
    .small-note { font-size:13px; color:#666; margin-top:6px; }
    #year-custom-row, #budget-custom-row { display:none; margin-top:8px; gap:8px; align-items:center; flex-wrap:wrap; }
    .meta {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 14px;
      color: #333;
      font-family: 'Kodchasan', system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
    }
    .meta-icon { width: 18px; height: 18px; object-fit: contain; }
    .filters { width:280px; padding:16px; border-right:1px solid #f0f0f0; max-height: 2000px; overflow:auto; }
    .filters h3 { margin:0 0 8px 0; font-size:16px; }
    .filters .group { margin-bottom:14px; }
    .filters label { display:block; font-size:14px; margin-bottom:6px; }
    .filters .btn {
      display:inline-block;
      padding:8px 12px;
      border-radius:6px;
      background:#4149B7; 
      color:#fff;
      text-decoration:none;
      border:1px solid #4149B7;
      cursor:pointer;
    }
    .filters .reset { background:#eee; color:#333; border:1px solid #ddd; }
    .details { font-family: 'Inter', sans-serif; color:#333; margin-top:0; }

    .card-separator {
      display: block;
      width: 350px;
      height: 2px;
      background: var(--brand-yellow);
      border-radius: 2px;
      margin: 0 0 15px 0; 
      margin-left: -12px;
    }

    .cards-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      align-items: stretch;
      grid-auto-rows: 1fr;
    }

    .card {
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      display: flex;
      flex-direction: column;
      align-items: stretch;
      height: 100%;
    }

    .card .image {
      width: 100%;
      background-color: #e9e9e9;
      background-size: cover;
      background-position: center;
      flex: 0 0 260px;
      height: 260px;
      min-height: 120px;
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
    }

    .card .image img {
      display:block;
      width:100%;
      height:100%;
      object-fit:cover;
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
    }

    .card .title {
      font-family: 'Kodchasan', system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
      font-size: 20px;
      color: var(--brand-red);
      font-weight: 700;
      line-height: 1.1;
      padding-top: 12px;
      padding-bottom: 12px;
      margin-left: -12px;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 2;
      overflow: hidden;
      word-break: break-word;
      min-height: calc(2 * 1.0em + 2px); 
    }

    .card .card-body > div {
      margin-bottom: 0px; 
      min-height: 44px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      gap: 4px;
    }

    .card .card-body {
      padding: 0 12px 4px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      flex: 1 1 auto;
      min-width: 0;
      min-height: 0; 
    }

    .card .details {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      gap: 4px; 
      overflow: hidden;
      min-height: 0; 
      margin-top: 0;
      padding-top: 0;
      margin-bottom: 2px;  
      padding-bottom: 15px; 
    }

    .card .details .meta {
      margin: 0;                     
      align-items: center;
      gap: 6px;
      font-size: 14px;
    }

    .card .details .meta img.meta-icon {
      flex: 0 0 22px;
      width: 22px;
      height: 22px;
      display: inline-block;
    }
    .card .details .meta span { display: inline-block; }

    .card .actions {
      margin-top: auto;
      margin-bottom: 4px; 
      display: flex;
      gap: 8px;
      align-items: center;
      justify-content: center;
    }

    .card .view-project {
      display: inline-block;
      box-sizing: border-box;
      width: calc(100% + 24px);
      margin-left: -12px;
      margin-right: -12px;
      padding: 8px 12px;
      background: #AD2020;
      color: #fff;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      text-align: center;
      border-bottom-left-radius: 8px;
      border-bottom-right-radius: 8px;
      cursor:pointer;
    }

    .card .view-project { transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease; }
    .card .view-project:hover, .card .view-project:focus-visible { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(173, 32, 32, 0.18); text-decoration: none; }
    .card .view-project:active { transform: translateY(0); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12); }
    .card .view-project:focus-visible { outline: 3px solid rgba(173, 32, 32, 0.16); outline-offset: 2px; }

    .pager {
      margin-top: 18px;
      display:flex;
      gap:8px;
      align-items:center;
      flex-wrap:wrap;
      justify-content:flex-end;
      width: 100%;
    }
    .pager a, .pager span {
      display:inline-block;
      padding:6px 10px;
      border-radius:6px;
      text-decoration:none;
      color:#4149B7; 
      background: #ffffff;
      border:1px solid #e6e6e6;
    }
    .pager .active { background:#4149B7; color:#fff; border-color:#4149B7; }

    @media (max-width: 520px) {
      .pager { gap:6px; }
      .pager a, .pager span { padding:6px 8px; font-size:13px; }
      .filters { width: 100%; max-height:none; position: static; border-right: none; }
    }

    .filter-group-wrap { display:block; }
    .filter-checkbox-column label {
      display:flex;
      align-items:center;
      gap:10px;
      margin: var(--filter-option-gap) 0;
      font-size:14px;
    }
    .filter-checkbox-column input[type="checkbox"] { width: 16px; height: 16px; margin: 0; vertical-align: middle; flex: 0 0 16px; }

    .more-toggle, .less-toggle {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin: var(--filter-option-gap) 0;
      background: transparent;
      border: none;
      padding: 0;
      color: #4149B7;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      text-decoration: underline;
      text-underline-offset: 3px;
    }

    .more-list {
      max-height: 0;
      overflow: hidden;
      transition: max-height 260ms ease, opacity 180ms ease;
      opacity: 0;
      margin-top: 0;
      padding-left: 0;
    }
    .more-list.open {
      max-height: 1200px;
      opacity: 1;
      margin-top: calc(-1 * var(--filter-option-gap));
      margin-bottom: 0;
    }

    .page-container {
      display:flex;
      gap:20px;
      align-items:flex-start;
      justify-content:stretch;
    }
    .listing { flex:1; min-width:0; }

    @media (max-width: 980px) {
      .cards-grid { grid-template-columns: repeat(2, 1fr); grid-auto-rows: 1fr; }
      .filters { width: 100%; border-right: none; max-height: none; }
      .page-container { flex-direction: column; }
      .card { height: 520px; } 
      .card .image { height: 50%; flex: 0 0 260px; } 
      .card .card-body > div { min-height: 48px; } 
    }
    @media (max-width: 520px) {
      .cards-grid { grid-template-columns: 1fr; grid-auto-rows: auto; } 
      .card { height: auto; }
      .card .image { height: 120px; flex: 0 0 120px; }
      .card .card-body { padding: 12px; } 
      .card .title { font-size: 14px; height: calc(2 * 1.1em); max-height: calc(2 * 1.1em); }
      .card .card-body > div { min-height: auto; margin-bottom: 8px; } 
      .filters { padding: 12px; }
      .search-input { flex: 1 1 auto; min-width: 0; }
    }
  </style>

  <?php if ($card_px !== null): ?>
  <style>
    .cards-grid { grid-auto-rows: <?php echo intval($card_px); ?>px; }
    .card { height: <?php echo intval($card_px); ?>px !important; }
    .card .card-body { overflow: hidden; padding-bottom: 8px; }
    @media (max-width: 520px) {
      .cards-grid { grid-auto-rows: auto; }
      .card { height: auto !important; }
    }
  </style>
  <?php else: ?>
  <style>
    .cards-grid { grid-auto-rows: 1fr; }
    .card { height: 100%; }
  </style>
  <?php endif; ?>

</head>
<body>

 <?php
    
    echo '<div class="header-bar">';
        echo '<div class="header-top">';
            echo '<div class="icon-container">
                    <img src="/transparatrack_web/PHP/ADMIN PROFILE/assets/tplogo.svg" alt="TransparaTrack Logo">
                  </div>';
            echo '<div class="title-wrapper">';
                echo '<span class="header-title">TransparaTrack</span>';
                echo '<span class="header-subtitle">See the process, Start the progress</span>';
            echo '</div>';
            echo '<div class="admin-icon-container">';
                echo $headerRightHtml;
            echo '</div>';
        echo '</div>';
        
        echo '<div class="gradient-line"></div>';
        
        echo '<nav class="nav-container">';
        echo '  <ul>';
        echo '    <li><a href="/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/project.php">Projects</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/archive.php">Archive</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/history.php">History</a></li>';
        echo '    <li><a href="/transparatrack_web/PHP/about_us.php">About Us</a></li>';
        echo '  </ul>';
        echo '</nav>';
    echo '</div>';
    ?>

<div class="subheader-bar" role="banner" aria-hidden="false">
  <div class="subheader-title">
    <?php 
    $year_select = isset($_GET['year']) ? trim($_GET['year']) : '';
    $status_filters = isset($_GET['status']) ? (array)$_GET['status'] : [];
    $is_archive_view = !empty($year_select) && in_array('Completed', $status_filters);
    
    if ($is_archive_view) {
        echo 'Archive (' . htmlspecialchars($year_select) . ')';
    } else {
        echo 'Projects';
    }
    ?>
  </div>
</div>

<main class="page-container" style="display:flex;gap:20px;">
  <aside class="filters">
    <h2 class="title">Filter</h2>
    <form method="get" id="filters-form">
      <div class="group">
        <h3>Year</h3>
        <select id="year-select" name="year" class="select-full">
          <option value="">Any year</option>
          <?php foreach ($filter_years as $fy): ?>
            <option value="<?php echo htmlspecialchars($fy); ?>" <?php if ($year_select === (string)$fy) echo 'selected'; ?>>
              <?php echo htmlspecialchars($fy); ?>
            </option>
          <?php endforeach; ?>
          <option value="custom" <?php if ($year_select === 'custom') echo 'selected'; ?>>Custom range</option>
        </select>
        <div id="year-custom-row">
          <input type="text" name="year_min" placeholder="From (YYYY)" value="<?php echo htmlspecialchars($year_min); ?>">
          <input type="text" name="year_max" placeholder="To (YYYY)" value="<?php echo htmlspecialchars($year_max); ?>">
        </div>
      </div>

      <div class="group">
        <h3>Budget</h3>
        <select id="budget-range-select" name="budget_range" class="select-full">
          <option value="">Any budget</option>
          <option value="below50k" <?php if ($budget_range === 'below50k') echo 'selected'; ?>>Below ₱50,000</option>
          <option value="above50k" <?php if ($budget_range === 'above50k') echo 'selected'; ?>>Above ₱50,000</option>
          <option value="custom" <?php if ($budget_range === 'custom') echo 'selected'; ?>>Custom range</option>
        </select>
        <div id="budget-custom-row">
          <input type="text" name="budget_min" placeholder="Min budget" value="<?php echo htmlspecialchars($budget_min); ?>">
          <input type="text" name="budget_max" placeholder="Max budget" value="<?php echo htmlspecialchars($budget_max); ?>">
        </div>
      </div>

      <div class="group">
        <h3>Status</h3>
        <div class="filter-group-wrap">
          <div class="filter-checkbox-column compact-checkboxes" id="status-list">
            <?php foreach ($statusOptions as $idx => $opt): ?>
              <?php if ($idx < 3): ?>
                <label>
                  <input type="checkbox" name="status[]" value="<?php echo htmlspecialchars($opt); ?>" <?php if (in_array($opt, $status_filters, true)) echo 'checked'; ?>>
                  <?php echo htmlspecialchars($opt); ?>
                </label>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <?php if (count($statusOptions) > 3): ?>
            <button type="button" class="more-toggle" data-group="status" aria-expanded="false" aria-controls="status-more">More ▾</button>

            <div id="status-more" class="more-list" aria-hidden="true">
              <div class="filter-checkbox-column">
                <?php foreach ($statusOptions as $idx => $opt): ?>
                  <?php if ($idx >= 3): ?>
                    <label>
                      <input type="checkbox" name="status[]" value="<?php echo htmlspecialchars($opt); ?>" <?php if (in_array($opt, $status_filters, true)) echo 'checked'; ?>>
                      <?php echo htmlspecialchars($opt); ?>
                    </label>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>

              <div>
                <button type="button" class="less-toggle" data-group="status">Less ▴</button>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="group">
        <h3>Category</h3>
        <div class="filter-group-wrap">
          <div class="filter-checkbox-column compact-checkboxes" id="category-list">
            <?php foreach ($categoryOptions as $idx => $opt): ?>
              <?php if ($idx < 3): ?>
                <label>
                  <input type="checkbox" name="category[]" value="<?php echo htmlspecialchars($opt); ?>" <?php if (in_array($opt, $category_filters, true)) echo 'checked'; ?>>
                  <?php
                    $display = $opt;
                    if (!empty($categoryLabels[$opt])) $display .= ' (' . $categoryLabels[$opt] . ')';
                    echo htmlspecialchars($display);
                  ?>
                </label>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <?php if (count($categoryOptions) > 3): ?>
            <button type="button" class="more-toggle" data-group="category" aria-expanded="false" aria-controls="category-more">More ▾</button>

            <div id="category-more" class="more-list" aria-hidden="true">
              <div class="filter-checkbox-column">
                <?php foreach ($categoryOptions as $idx => $opt): ?>
                  <?php if ($idx >= 3): ?>
                    <label>
                      <input type="checkbox" name="category[]" value="<?php echo htmlspecialchars($opt); ?>" <?php if (in_array($opt, $category_filters, true)) echo 'checked'; ?>>
                      <?php
                        $display = $opt;
                        if (!empty($categoryLabels[$opt])) $display .= ' (' . $categoryLabels[$opt] . ')';
                        echo htmlspecialchars($display);
                      ?>
                    </label>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>

              <div>
                <button type="button" class="less-toggle" data-group="category">Less ▴</button>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="group">
        <h3>Department</h3>
        <div class="filter-group-wrap">
          <div class="filter-checkbox-column compact-checkboxes" id="department-list">
            <?php foreach ($departmentOptions as $idx => $opt): ?>
              <?php if ($idx < 3): ?>
                <label>
                  <input type="checkbox" name="department[]" value="<?php echo htmlspecialchars($opt); ?>" <?php if (in_array($opt, $department_filters, true)) echo 'checked'; ?>>
                  <?php echo htmlspecialchars($opt); ?>
                </label>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <?php if (count($departmentOptions) > 3): ?>
            <button type="button" class="more-toggle" data-group="department" aria-expanded="false" aria-controls="department-more">More ▾</button>

            <div id="department-more" class="more-list" aria-hidden="true">
              <div class="filter-checkbox-column">
                <?php foreach ($departmentOptions as $idx => $opt): ?>
                  <?php if ($idx >= 3): ?>
                    <label>
                      <input type="checkbox" name="department[]" value="<?php echo htmlspecialchars($opt); ?>" <?php if (in_array($opt, $department_filters, true)) echo 'checked'; ?>>
                      <?php echo htmlspecialchars($opt); ?>
                    </label>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>

              <div>
                <button type="button" class="less-toggle" data-group="department">Less ▴</button>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="group" style="display:flex;gap:8px;">
        <button type="submit" class="btn">Apply filters</button>
        <a href="project.php" class="btn reset">Reset</a>
      </div>
    </form>
  </aside>

  <section class="listing" style="flex:1;">
     <div class="listing-top">
       <form method="get" style="display:flex;gap:12px;flex:1;align-items:center;">
         <input type="hidden" name="card_height" id="top-card-height" value="<?php echo htmlspecialchars($card_height_option); ?>">
         <input type="hidden" name="card_height_custom" id="top-card-height-custom" value="<?php echo htmlspecialchars($card_height_custom); ?>">

         <input class="search-input" type="text" name="title" placeholder="Enter Project Title" value="<?php echo htmlspecialchars($title); ?>">
         <button type="submit" class="search-button">Search</button>
         <select name="sort" class="sort-select" onchange="this.form.submit()">
           <option value="newest" <?php if($sort==='newest') echo 'selected'; ?>>Newest</option>
           <option value="oldest" <?php if($sort==='oldest') echo 'selected'; ?>>Oldest</option>
         </select>
         </form>
     </div>

    <div class="cards-grid" aria-live="polite">
      <?php if (isset($project_list_error)): ?>
          <p style="color: red;"><?php echo htmlspecialchars($project_list_error); ?></p>
      <?php elseif (empty($projects)): ?>
        <p>No projects found matching your criteria.</p>
      <?php else: ?>
        <?php foreach ($projects as $p): ?>
          <article class="card" role="article" aria-labelledby="p-<?php echo $p['id']; ?>-title">
            <?php
                $list_img_sql = "SELECT FilePath FROM Evidence WHERE ProjectID = :id AND EvidenceCategory = 'Photo' ORDER BY EvidenceID ASC LIMIT 1";
                $list_img_stmt = $pdo->prepare($list_img_sql);
                $list_img_stmt->execute([':id' => $p['id']]);
                $listImagePath = $list_img_stmt->fetchColumn();
                
                if ($listImagePath) {
                    echo '<div class="image" style="background-image: url(\'' . htmlspecialchars($base_web_path . $listImagePath) . '\');"></div>';
                } else {
                    echo '<div class="image" aria-hidden="true"></div>'; 
                }
            ?>

            <div class="card-body">
              <div>
                <div class="title" id="p-<?php echo $p['id']; ?>-title"><?php echo htmlspecialchars($p['title']); ?></div>
                <span class="card-separator" aria-hidden="true"></span>
              </div>

              <div class="details" aria-label="Project details">
                <div class="meta">
                  <img src="media/project_date_icon.svg" alt="Date icon" class="meta-icon">
                  <span><?php echo date('F j, Y', strtotime($p['date'])); ?></span>
                </div>
                <div class="meta">
                  <img src="media/project_status_icon.svg" alt="Status icon" class="meta-icon">
                  <span><?php echo htmlspecialchars($p['status']); ?></span>
                </div>
                <div class="meta">
                  <img src="media/project_budget_icon.svg" alt="Budget icon" class="meta-icon">
                  <span><?php echo isset($p['budget_val']) ? '₱' . number_format($p['budget_val'], 2) : 'N/A'; ?></span>
                </div>
                <div class="meta">
                  <img src="media/project_category_icon.svg" alt="Category icon" class="meta-icon">
                  <span><?php echo htmlspecialchars($p['category']); ?></span>
                </div>
                <div class="meta">
                  <img src="media/project_dept_icon.svg" alt="Department icon" class="meta-icon">
                  <span><?php echo htmlspecialchars($p['department'] ?? 'N/A'); ?></span>
                </div>
                <div class="meta">
                  <img src="/transparatrack_web/PHP/media/author.svg" alt="Author icon" class="meta-icon">
                  <span><?php echo htmlspecialchars($p['author_name'] ?? 'N/A'); ?></span>
                </div>
              </div>

              <div class="actions">
                <a class="view-project" href="?id=<?php echo $p['id']; ?>" target="_blank" rel="noopener noreferrer">View Project</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="pager" aria-label="Pagination">
      <?php if ($totalPages > 1): ?>
        <?php if ($page > 1): ?>
          <a href="<?php echo build_page_url($page - 1); ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="active"><?php echo $i; ?></span>
          <?php else: ?>
            <a href="<?php echo build_page_url($i); ?>"><?php echo $i; ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <a href="<?php echo build_page_url($page + 1); ?>">Next &raquo;</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>

  </section>
</main>

<footer class="site-footer">
  <div class="footer-gradient-line"></div>
  <div class="footer-content-area">
    <div class="footer-column">
      <ul>
        <li><a href="http://localhost/transparatrack_web/PHP/HOMEPAGE/homepage.php">Home</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/project.php">Projects</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/archive.php">Archive</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/history.php">History</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/about_us.php">About Us</a></li>
      </ul>
    </div>
    <div class="footer-column">
      <ul>
        <li><a href="http://localhost/transparatrack_web/PHP/ADMIN%20PROFILE/adminprofile.php">Profile</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/Log-in_page/terms-conditions.php">Terms and Conditions</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/Log-in_page/privacy-policy.php">Privacy Policy</a></li>
        <li><a href="http://localhost/transparatrack_web/PHP/HELP/help.php">Help</a></li>
      </ul>
    </div>
    <div class="footer-logo">TransparaTrack</div>
  </div>
</footer>

<script>
(function(){
  var yearSelect = document.getElementById('year-select');
  var yearCustomRow = document.getElementById('year-custom-row');
  function syncYear() {
    if (!yearSelect || !yearCustomRow) return;
    yearCustomRow.style.display = (yearSelect.value === 'custom') ? 'flex' : 'none';
  }
  if (yearSelect) {
    yearSelect.addEventListener('change', syncYear);
    syncYear();
  }

  var budgetSel = document.getElementById('budget-range-select');
  var budgetRow = document.getElementById('budget-custom-row');
  function syncBudget() {
    if (!budgetSel || !budgetRow) return;
    budgetRow.style.display = (budgetSel.value === 'custom') ? 'flex' : 'none';
  }
  if (budgetSel) {
    budgetSel.addEventListener('change', syncBudget);
    syncBudget();
  }

  var cardHeightSel = document.getElementById('card-height-select');
  var cardHeightRow = document.getElementById('card-height-custom-row');
  function syncCardHeight() {
    if (!cardHeightSel || !cardHeightRow) return;
    cardHeightRow.style.display = (cardHeightSel.value === 'custom') ? 'flex' : 'none';
  }
  if (cardHeightSel) {
    cardHeightSel.addEventListener('change', syncCardHeight);
    syncCardHeight();
  }

  function applyCardHeight(option, customPx){
    var styleId = 'card-height-dynamic';
    var existing = document.getElementById(styleId);
    if (existing) existing.parentNode.removeChild(existing);

    if (!option || option === 'auto') return; 

    var px = null;
    if (option === 'short') px = 360;
    else if (option === 'medium') px = 480;
    else if (option === 'tall') px = 560;
    else if (option === 'custom') {
      var d = String(customPx || '').replace(/[^0-9]/g,'');
      if (d) px = Math.min(2000, Math.max(120, parseInt(d,10)));
      else px = 480;
    }

    if (px !== null) {
      var s = document.createElement('style');
      s.id = styleId;
      s.type = 'text/css';
      s.appendChild(document.createTextNode(
        '.cards-grid { grid-auto-rows: ' + px + 'px !important; }\\n' +
        '.card { height: ' + px + 'px !important; }\\n' +
        '.card .card-body { overflow: hidden; }\\n' +
        '@media (max-width:520px){ .cards-grid { grid-auto-rows: auto !important; } .card{ height:auto !important; } }'
      ));
      document.head.appendChild(s);
    }
  }

  var sel = document.getElementById('card-height-select');
  var customInput = document.querySelector('input[name="card_height_custom"]');
  if (sel) {
    sel.addEventListener('change', function(){
      applyCardHeight(sel.value, customInput && customInput.value);
      var topCard = document.getElementById('top-card-height');
      var topCardCustom = document.getElementById('top-card-height-custom');
      if (topCard) topCard.value = sel.value;
      if (topCardCustom && customInput) topCardCustom.value = customInput.value;
    });
    applyCardHeight(sel.value, customInput && customInput.value);
  }
  if (customInput){
    customInput.addEventListener('input', function(){
      if (sel && sel.value === 'custom') applyCardHeight('custom', customInput.value);
      var topCardCustom = document.getElementById('top-card-height-custom');
      if (topCardCustom) topCardCustom.value = customInput.value;
    });
  }

  function openGroup(group) {
    var topBtn = document.querySelector('.more-toggle[data-group="'+group+'"]');
    var more = document.getElementById(group + '-more');
    if (!more) return;
    more.classList.add('open');
    more.setAttribute('aria-hidden', 'false');
    if (topBtn) {
      topBtn.style.display = 'none';
      topBtn.setAttribute('aria-expanded', 'true');
    }
  }
  function closeGroup(group) {
    var topBtn = document.querySelector('.more-toggle[data-group="'+group+'"]');
    var more = document.getElementById(group + '-more');
    if (!more) return;
    more.classList.remove('open');
    more.setAttribute('aria-hidden', 'true');
    if (topBtn) {
      topBtn.style.display = '';
      topBtn.setAttribute('aria-expanded', 'false');
      topBtn.innerHTML = 'More ▾';
    }
  }

  document.querySelectorAll('.more-toggle').forEach(function(btn){
    var group = btn.getAttribute('data-group');
    btn.addEventListener('click', function(){
      openGroup(group);
    });
    btn.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        btn.click();
      }
    });
  });

  document.body.addEventListener('click', function(e){
    var less = e.target.closest('.less-toggle');
    if (!less) return;
    var group = less.getAttribute('data-group');
    closeGroup(group);
    var topBtn = document.querySelector('.more-toggle[data-group="'+group+'"]');
    if (topBtn) topBtn.focus();
  });

  document.body.addEventListener('keydown', function(e){
    if ((e.key === 'Enter' || e.key === ' ') && e.target && e.target.classList && e.target.classList.contains('less-toggle')) {
      e.preventDefault();
      e.target.click();
    }
  });

  document.addEventListener('click', function(e){
    var inside = e.target.closest('.filters');
    if (!inside) {
      document.querySelectorAll('.more-list.open').forEach(function(moreEl){
        var id = moreEl.id;
        var group = id && id.replace('-more','');
        closeGroup(group);
      });
    }
  });
})();
</script>
</body>
</html>