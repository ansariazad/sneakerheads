<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "Contact Us - Sneakerheads";
require_once 'includes/header.php';

// Process contact form submission
$message = '';
$messageType = '';
$name = $email = $subject = $message_content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    // Define sanitize_input function if it doesn't exist
    if (!function_exists('sanitize_input')) {
        function sanitize_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
    }
    
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message_content = sanitize_input($_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        // In a real application, you would send an email here
        // For now, we'll just simulate success
        $message = 'Thank you for your message! We will get back to you soon.';
        $messageType = 'success';
        
        // Reset form fields after successful submission
        $name = $email = $subject = $message_content = '';
    }
}
?>

<div class="content-wrapper">
    <div class="container">
        <!-- Contact Hero Section -->
        <div class="contact-hero">
            <div class="contact-hero-content">
                <h1>Contact Us</h1>
                <p>We'd love to hear from you! Reach out with any questions, feedback, or inquiries.</p>
            </div>
        </div>

        <!-- Contact Information Section -->
        <section class="contact-section">
            <div class="contact-info-grid">
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h3>Visit Us</h3>
                    <p>Sneakerheads HQ</p>
                    <p>123 Sneaker Street, Koramangala</p>
                    <p>Bangalore, Karnataka 560034</p>
                </div>
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <h3>Call Us</h3>
                    <p>Customer Support: +91 9876543210</p>
                    <p>Seller Support: +91 9876543211</p>
                    <p>Mon-Sat: 10:00 AM - 7:00 PM</p>
                </div>
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email Us</h3>
                    <p>General Inquiries: info@sneakerheads.com</p>
                    <p>Support: support@sneakerheads.com</p>
                    <p>Careers: careers@sneakerheads.com</p>
                </div>
                <div class="contact-info-card">
                    <div class="contact-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Follow Us</h3>
                    <div class="contact-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Form Section -->
        <section class="contact-form-section">
            <div class="contact-grid">
                <div class="contact-form-container">
                    <h2>Send Us a Message</h2>
                    
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?>" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; background-color: <?php echo $messageType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $messageType === 'success' ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $messageType === 'success' ? '#c3e6cb' : '#f5c6cb'; ?>;">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="contact.php" method="POST" class="contact-form" data-validate>
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" required value="<?php echo isset($name) ? $name : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email *</label>
                            <input type="email" id="email" name="email" required value="<?php echo isset($email) ? $email : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <input type="text" id="subject" name="subject" required value="<?php echo isset($subject) ? $subject : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" rows="6" required><?php echo isset($message_content) ? $message_content : ''; ?></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="contact_submit" class="btn">Send Message</button>
                        </div>
                    </form>
                </div>
                <div class="contact-map">
                    <h2>Find Us</h2>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3888.5831950181!2d77.6307655!3d12.9344756!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae14494a5a3e51%3A0x6c8aef6fb75a2a5c!2sKoramangala%2C%20Bengaluru%2C%20Karnataka!5e0!3m2!1sen!2sin!4v1648456891702!5m2!1sen!2sin" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Teaser -->
        <section class="faq-teaser">
            <div class="faq-teaser-content">
                <h2>Have Questions?</h2>
                <p>Check out our frequently asked questions for quick answers to common inquiries.</p>
                <a href="faq.php" class="btn">View FAQs</a>
            </div>
        </section>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

