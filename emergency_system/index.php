<?php
require_once 'db.php';
if (isset($_SESSION['user_id'])) {
  header("Location: user/dashboard.php");
  exit();
}
if (isset($_SESSION['admin_id'])) {
  header("Location: admin/dashboard.php");
  exit();
}
if (isset($_SESSION['police_id'])) {
  header("Location: police/dashboard.php");
  exit();
}
if (isset($_SESSION['ambulance_id'])) {
  header("Location: ambulance/dashboard.php");
  exit();
}
if (isset($_SESSION['hospital_id'])) {
  header("Location: hospital/dashboard.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Smart Emergency Response and Accident Assistance System</title>
  <link rel="stylesheet" href="assets/css/style.css?v=5.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>

<body>

  <nav class="navbar">
    <div class="nav-brand">
      <svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M7 18v-6a5 5 0 1 1 10 0v6" />
        <path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z" />
        <path d="M21 12h1" />
        <path d="M18.5 4.5 18 5" />
        <path d="M2 12h1" />
        <path d="M12 2v1" />
        <path d="m4.929 4.929.707.707" />
        <path d="M12 12v6" />
      </svg>
      SERAAS
    </div>
    <div class="nav-links">
      <button id="theme-toggle" class="btn btn-ghost btn-sm" aria-label="Toggle Theme" style="padding:6px;margin-right:8px;">
        <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="4" />
          <path d="M12 2v2" />
          <path d="M12 20v2" />
          <path d="m4.93 4.93 1.41 1.41" />
          <path d="m17.66 17.66 1.41 1.41" />
          <path d="M2 12h2" />
          <path d="M20 12h2" />
          <path d="m6.34 17.66-1.41 1.41" />
          <path d="m19.07 4.93-1.41 1.41" />
        </svg>
        <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
        </svg>
      </button>
      <a href="login.php" class="nav-link">Log In</a>
      <a href="register.php" class="btn btn-primary btn-sm">Register Free</a>
    </div>
  </nav>

  <!-- PROFESSIONAL HERO -->
  <section class="hero hero-pro">
    <div class="hero-bg-glow"></div>
    <div class="hero-container">
      <div class="hero-content">
        <div class="hero-badge-pro">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 8v4l3 3" />
          </svg>
          Live Emergency Response Platform
        </div>
        <h1><span class="text-gradient">Smart Emergency Response</span><br>and Accident Assistance System</h1>
        <h2 class="hero-tagline">Get Help in Seconds, Not Minutes.</h2>
        <p>An enterprise-grade dispatch platform connecting accident victims instantly with Police, Ambulance, and Hospital trauma centers through real-time GPS routing.</p>
        <div class="hero-actions">
          <a href="register.php" class="btn btn-primary btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
            </svg>
            Request Emergency Help
          </a>
          <a href="login.php" class="btn btn-ghost btn-lg">
            Responder Login
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14" />
              <path d="m12 5 7 7-7 7" />
            </svg>
          </a>
        </div>
      </div>
    </div>

    <!-- IMPACT STATS -->
    <div class="impact-stats">
      <div class="impact-stat">
        <div class="impact-num">98<span>%</span></div>
        <div class="impact-label">Faster Dispatch Time</div>
      </div>
      <div class="impact-stat">
        <div class="impact-num">3<span>s</span></div>
        <div class="impact-label">Average Response</div>
      </div>
      <div class="impact-stat">
        <div class="impact-num">24<span>/7</span></div>
        <div class="impact-label">Active Monitoring</div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section class="features-section">
    <div class="section-header">
      <h2>Intelligent Dispatch System</h2>
      <p>Our platform handles critical workflows automatically, ensuring the fastest possible response.</p>
    </div>
    <div class="features-grid">
      <div class="feature-card-pro">
        <div class="f-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
        </div>
        <h3>Precision GPS Routing</h3>
        <p>Automatically captures your exact coordinates down to the meter and shares them with the nearest available responder units.</p>
      </div>
      <div class="feature-card-pro">
        <div class="f-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
          </svg>
        </div>
        <h3>Instant Multi-Cast Alerts</h3>
        <p>A single tap triggers parallel notifications to Police, Ambulance, and Hospitals simultaneously via WebSocket technology.</p>
      </div>
      <div class="feature-card-pro">
        <div class="f-icon-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
          </svg>
        </div>
        <h3>Live Status Tracking</h3>
        <p>Total transparency for the victim. Watch your request move from Pending to Accepted, to Resolved in real-time.</p>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="steps-section steps-pro">
    <div class="section-header">
      <h2>Seamless Operation Pipeline</h2>
      <p>A highly optimized four-step pipeline from incident reporting to resolution.</p>
    </div>
    <div class="steps-container">
      <div class="step-card-pro">
        <div class="step-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
          </svg>
        </div>
        <h3>1. Account Registration</h3>
        <p>Secure identity verification establishing essential medical and contact profiles.</p>
      </div>
      <div class="step-connector"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m9 18 6-6-6-6" />
        </svg></div>
      <div class="step-card-pro">
        <div class="step-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
          </svg>
        </div>
        <h3>2. Trigger SOS</h3>
        <p>One-touch emergency beacon with automated geographic payload transmission.</p>
      </div>
      <div class="step-connector"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m9 18 6-6-6-6" />
        </svg></div>
      <div class="step-card-pro">
        <div class="step-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="16" height="16" x="4" y="4" rx="2" />
            <path d="M12 8v8" />
            <path d="M8 12h8" />
          </svg>
        </div>
        <h3>3. Active Dispatch</h3>
        <p>Centralized algorithm matches and dispatches the most optimal local responder.</p>
      </div>
      <div class="step-connector"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m9 18 6-6-6-6" />
        </svg></div>
      <div class="step-card-pro">
        <div class="step-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <path d="m9 11 3 3L22 4" />
          </svg>
        </div>
        <h3>4. Incident Resolved</h3>
        <p>Secure closure of incident with detailed digital logs for auditing.</p>
      </div>
    </div>
  </section>

  <!-- PORTALS -->
  <section class="portals-section portals-pro">
    <div class="section-header">
      <h2>Responder Ecosystem</h2>
      <p>Secure, role-based dashboards engineered for high-stress operational environments.</p>
    </div>
    <div class="responder-grid">
      <a href="admin/login.php" class="responder-card rc-admin" style="background-image: url('assets/img/admin-bg.png');">
        <div class="rc-content">
          <span class="rc-badge">Admin</span>
          <h3>System Administration</h3>
          <p>Comprehensive oversight, user management, and system auditing.</p>
        </div>
      </a>
      <a href="police/login.php" class="responder-card rc-police" style="background-image: url('assets/img/police-bg.png');">
        <div class="rc-content">
          <span class="rc-badge">Police</span>
          <h3>Law Enforcement</h3>
          <p>Immediate access to critical incidents requiring police intervention.</p>
        </div>
      </a>
      <a href="ambulance/login.php" class="responder-card rc-amb" style="background-image: url('assets/img/ambulance-bg.png');">
        <div class="rc-content">
          <span class="rc-badge">Ambulance</span>
          <h3>Medical Dispatch</h3>
          <p>Real-time routing for paramedic units to accident coordinates.</p>
        </div>
      </a>
      <a href="hospital/login.php" class="responder-card rc-hosp" style="background-image: url('assets/img/hospital-bg.png');">
        <div class="rc-content">
          <span class="rc-badge">Hospital</span>
          <h3>Trauma Center</h3>
          <p>Advanced warning system for ER preparation and capacity management.</p>
        </div>
      </a>
    </div>
  </section>

  <!-- PROFESSIONAL FOOTER -->
  <footer class="footer-pro">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="footer-logo">
          <svg class="brand-icon" style="width:24px;height:24px;color:var(--red);margin-right:2px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M7 18v-6a5 5 0 1 1 10 0v6" />
            <path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z" />
            <path d="M21 12h1" />
            <path d="M18.5 4.5 18 5" />
            <path d="M2 12h1" />
            <path d="M12 2v1" />
            <path d="m4.929 4.929.707.707" />
            <path d="M12 12v6" />
          </svg>
          SERAAS
        </div>
        <p>The standard in rapid emergency deployment and intelligent accident response management.</p>
        <div class="social-links">
          <a href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
            </svg></a>
          <a href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z" />
            </svg></a>
          <a href="#"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
            </svg></a>
        </div>
      </div>
      <div class="footer-links">
        <h4>Platform</h4>
        <ul>
          <li><a href="register.php">Citizen Portal</a></li>
          <li><a href="police/login.php">Law Enforcement</a></li>
          <li><a href="ambulance/login.php">Paramedics</a></li>
          <li><a href="hospital/login.php">Hospitals</a></li>
        </ul>
      </div>
      <div class="footer-links">
        <h4>Resources</h4>
        <ul>
          <li><a href="#">System Status</a></li>
          <li><a href="#">API Documentation</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
        </ul>
      </div>
      <div class="footer-contact">
        <h4>Contact Support</h4>
        <p><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
          </svg> +91 8123175981</p>
        <p><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
            <polyline points="22,6 12,13 2,6" />
          </svg> support@SERAAS.com</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Smart Emergency Response and Accident Assistance System. All rights reserved. &nbsp;|&nbsp; Enterprise Edition</p>
    </div>
  </footer>

  <!-- Back to Top Button -->
  <button id="backToTop" class="btn btn-primary" style="display:none; position:fixed; bottom:30px; right:30px; border-radius:50%; width:50px; height:50px; padding:0; z-index:1000; box-shadow:0 4px 15px rgba(0,0,0,0.3);" aria-label="Back to top">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin:auto; display:block;"><path d="m18 15-6-6-6 6"/></svg>
  </button>

  <script src="assets/js/app.js?v=2.1"></script>
  <script>
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        backToTop.style.display = 'block';
      } else {
        backToTop.style.display = 'none';
      }
    });
    backToTop.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  </script>
</body>

</html>
