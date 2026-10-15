<?php
declare(strict_types=1);

session_start([
  'cookie_httponly' => true,
  'cookie_samesite' => 'Lax',
  'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);

require_once __DIR__ . '/DB/database.php';

$contactServices = [
  'Web development',
  'Custom software',
  'Cross-platform apps',
  'Web hosting',
  'Cybersecurity',
  'SEO or web management',
  'Hardware or networking',
  'IT technician support',
  'Something else',
];
$formData = array_fill_keys(['name', 'email', 'company', 'service', 'message'], '');
$contactStatus = $_SESSION['contact_form_status'] ?? '';
$contactStatusType = $_SESSION['contact_form_status_type'] ?? 'info';
$formData = $_SESSION['contact_form_values'] ?? $formData;
unset($_SESSION['contact_form_status'], $_SESSION['contact_form_status_type'], $_SESSION['contact_form_values']);

function contact_escape(string $value): string
{
  return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function contact_post_value(string $key): string
{
  $value = $_POST[$key] ?? '';
  return is_string($value) ? trim($value) : '';
}

function redirect_contact_form(string $status, string $type, array $values): void
{
  $_SESSION['contact_form_status'] = $status;
  $_SESSION['contact_form_status_type'] = $type;
  $_SESSION['contact_form_values'] = $values;
  header('Location: contact.php#contact-form', true, 303);
  exit;
}

if (!isset($_SESSION['contact_form_token'])) {
  $_SESSION['contact_form_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach (array_keys($formData) as $field) {
    $formData[$field] = contact_post_value($field);
  }

  $postedToken = contact_post_value('csrf_token');
  if (!hash_equals($_SESSION['contact_form_token'], $postedToken)) {
    redirect_contact_form('Your session expired. Please review the form and try again.', 'error', $formData);
  }

  if (contact_post_value('website') !== '') {
    $_SESSION['contact_form_token'] = bin2hex(random_bytes(32));
    redirect_contact_form('Thanks for contacting Cach3Flow.', 'success', array_fill_keys(array_keys($formData), ''));
  }

  $errors = [];
  if ($formData['name'] === '' || strlen($formData['name']) > 150) {
    $errors[] = 'Enter your name (up to 150 characters).';
  }
  if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Enter a valid email address.';
  }
  if (strlen($formData['company']) > 150) {
    $errors[] = 'Company name must be 150 characters or fewer.';
  }
  if (!in_array($formData['service'], $contactServices, true)) {
    $errors[] = 'Choose a service from the list.';
  }
  if ($formData['message'] === '' || strlen($formData['message']) > 10000) {
    $errors[] = 'Enter a message of up to 10,000 characters.';
  }

  if ($errors !== []) {
    redirect_contact_form(implode(' ', $errors), 'error', $formData);
  }

  try {
    $pdo = cach3flow_pdo();
    $statement = $pdo->prepare(
      'INSERT INTO `Client_Details` (`Email`, `Name`, `Company`, `What_ you_need_help_with?`, `How_can_we_help`) VALUES (?, ?, ?, ?, ?)'
    );
    $statement->execute([
      $formData['email'],
      $formData['name'],
      $formData['company'] !== '' ? $formData['company'] : null,
      $formData['service'],
      $formData['message'],
    ]);

    $_SESSION['contact_form_token'] = bin2hex(random_bytes(32));
    redirect_contact_form('Thanks for contacting Cach3Flow. Your message has been received.', 'success', array_fill_keys(array_keys($formData), ''));
  } catch (Throwable $error) {
    error_log('Cach3Flow contact form database insert failed: ' . $error->getMessage());
    redirect_contact_form('We could not save your message. Please email hello@cach3flow.com.', 'error', $formData);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact Cach3Flow | Software &amp; IT Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Darker+Grotesque:wght@500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="Style/style.css" />
  </head>
  <body>
    <header class="topbar">
      <div class="container nav">
        <div class="brand" aria-label="Cach3Flow brand">
          <span class="brand-mark">&lt;/&gt;</span>
          <span class="brand-name">Cach3Flow</span>
        </div>
        <nav class="nav-links" aria-label="Main navigation">
          <a href="index.php">Home</a>
          <a href="about.php">About us</a>
          <a href="services.php">Services</a>
          <a href="portfolio.php">Portfolio</a>
          <a href="it-support.php">IT support</a>
          <a href="contact.php" aria-current="page">Contact</a>
        </nav>
        <a class="btn btn-primary" href="contact.php" aria-current="page">Contact us</a>
        <details class="mobile-menu">
          <summary aria-label="Toggle navigation menu" title="Menu"><span class="mobile-menu-icon" aria-hidden="true"></span></summary>
          <nav class="mobile-menu-panel" aria-label="Mobile navigation">
            <a href="index.php">Home</a>
            <a href="about.php">About us</a>
            <a href="services.php">Services</a>
            <a href="portfolio.php">Portfolio</a>
            <a href="it-support.php">IT support</a>
            <a href="contact.php" aria-current="page">Contact</a>
          </nav>
        </details>
      </div>
    </header>

    <main>
      <section class="page-hero">
        <div class="container page-hero-layout">
          <div class="page-hero-content">
            <div class="eyebrow" aria-hidden="true"></div>
            <p class="page-kicker">Contact Cach3Flow</p>
            <h1>What can we help you build or solve?</h1>
            <p>Tell us about your software project, website, IT setup, or technical issue. We’ll use the details to understand what kind of support you need.</p>
          </div>
          <figure class="page-hero-image">
            <img src="Images/Cross-platform-apps.png" alt="A team building software for mobile and desktop devices" />
          </figure>
        </div>
      </section>

      <section class="section contact-section">
        <div class="container contact-layout">
          <div class="contact-form-wrap">
            <h2 class="page-section-title">Send us a message</h2>
            <p class="page-copy">Share a few details and we’ll have what we need to start the conversation.</p>
            <form class="contact-form" id="contact-form" action="contact.php#contact-form" method="post">
              <input type="hidden" name="csrf_token" value="<?= contact_escape($_SESSION['contact_form_token']) ?>" />
              <div class="form-honeypot" aria-hidden="true">
                <label for="contact-website">Leave this field empty</label>
                <input id="contact-website" name="website" tabindex="-1" autocomplete="off" />
              </div>
              <div class="form-field">
                <label for="contact-name">Name</label>
                <input id="contact-name" name="name" autocomplete="name" maxlength="150" value="<?= contact_escape($formData['name']) ?>" required />
              </div>
              <div class="form-field">
                <label for="contact-email">Email</label>
                <input id="contact-email" name="email" type="email" autocomplete="email" maxlength="254" value="<?= contact_escape($formData['email']) ?>" required />
              </div>
              <div class="form-field">
                <label for="contact-company">Company <span>(optional)</span></label>
                <input id="contact-company" name="company" autocomplete="organization" maxlength="150" value="<?= contact_escape($formData['company']) ?>" />
              </div>
              <div class="form-field">
                <label for="contact-service">What do you need help with?</label>
                <select id="contact-service" name="service" required>
                  <option value="" selected disabled>Select a service</option>
                  <?php foreach ($contactServices as $service): ?>
                    <option value="<?= contact_escape($service) ?>" <?= $formData['service'] === $service ? 'selected' : '' ?>><?= contact_escape($service) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field form-field-wide">
                <label for="contact-message">How can we help?</label>
                <textarea id="contact-message" name="message" rows="6" maxlength="10000" required><?= contact_escape($formData['message']) ?></textarea>
              </div>
              <div class="form-actions">
                <button class="btn btn-primary" type="submit">Send message</button>
                <p id="contact-form-status" class="form-status form-status-<?= contact_escape($contactStatusType) ?>" role="status" aria-live="polite"><?= contact_escape($contactStatus) ?></p>
              </div>
            </form>
          </div>

          <aside class="contact-aside">
            <h2 class="page-section-title">Prefer email?</h2>
            <p class="page-copy">You can reach us directly at:</p>
            <a class="contact-email" href="mailto:hello@cach3flow.com">hello@cach3flow.com</a>
            <div class="contact-aside-support">
              <h3>Need IT assistance?</h3>
              <p>For support requests, include the affected device, website, software, or network.</p>
              <a href="it-support.php">View IT support</a>
            </div>
          </aside>
        </div>
      </section>
    </main>

    <footer>
      <div class="container">
        <div class="footer-grid">
          <div class="footer-brand">
            <div class="footer-brand-mark" aria-label="Cach3Flow brand">
              <span class="brand-mark">&lt;/&gt;</span>
              <span class="brand-name">Cach3Flow</span>
            </div>
            <p>Software development and dependable IT services for the technology your business relies on.</p>
          </div>
          <div class="footer-links">
            <h4>Company</h4>
            <a href="about.php">About us</a>
            <a href="services.php">Services</a>
            <a href="portfolio.php">Portfolio</a>
            <a href="it-support.php">IT support</a>
            <a href="contact.php">Contact</a>
          </div>
          <div class="footer-links">
            <h4>Services</h4>
            <a href="services.php#web-software">Web &amp; software</a>
            <a href="services.php#applications">Cross-platform apps</a>
            <a href="services.php#hosting-security">Hosting, security &amp; SEO</a>
            <a href="it-support.php#hardware-networking">Hardware &amp; networking</a>
          </div>
          <div class="footer-cta">
            <h4>Get in touch</h4>
            <p><a href="mailto:hello@cach3flow.com">hello@cach3flow.com</a></p>
            <p>Tell us what support you need.</p>
          </div>
        </div>
        <div class="footer-bottom">
          <span>© 2026 Cach3Flow</span>
          <span>Software, web, and IT services</span>
        </div>
      </div>
    </footer>
  </body>
</html>
