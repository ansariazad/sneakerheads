<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "FAQ - Sneakerheads";
require_once 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="container">
        <!-- FAQ Hero Section -->
        <div class="faq-hero">
            <div class="faq-hero-content">
                <h1>Frequently Asked Questions</h1>
                <p>Find answers to the most common questions about Sneakerheads</p>
            </div>
        </div>

        <!-- FAQ Search Section -->
        <section class="faq-search-section">
            <div class="faq-search-container">
                <h2>How can we help you?</h2>
                <div class="faq-search-form">
                    <input type="text" id="faqSearch" placeholder="Search for questions...">
                    <button type="button" class="btn"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </section>

        <!-- FAQ Categories -->
        <section class="faq-categories">
            <div class="faq-category-tabs">
                <button class="faq-tab active" data-category="all">All</button>
                <button class="faq-tab" data-category="account">Account</button>
                <button class="faq-tab" data-category="buying">Buying</button>
                <button class="faq-tab" data-category="selling">Selling</button>
                <button class="faq-tab" data-category="shipping">Shipping</button>
                <button class="faq-tab" data-category="authentication">Authentication</button>
                <button class="faq-tab" data-category="payment">Payment</button>
            </div>
        </section>

        <!-- FAQ Content -->
        <section class="faq-content">
            <div class="faq-accordion">
                <!-- Account FAQs -->
                <div class="faq-category" data-category="account">
                    <h3 class="faq-category-title">Account & Registration</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How do I create an account on Sneakerheads?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Creating an account on Sneakerheads is simple:</p>
                            <ol>
                                <li>Click on the "Sign Up" button in the top right corner of the website</li>
                                <li>Fill in your details including name, email, and password</li>
                                <li>Verify your email address through the verification link sent to your inbox</li>
                                <li>Complete your profile by adding additional information</li>
                            </ol>
                            <p>Once registered, you can start buying or selling sneakers on our platform.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>Can I use Sneakerheads without creating an account?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>You can browse sneakers and view product details without an account. However, to make purchases, place bids, sell sneakers, or use features like wishlists and saved searches, you'll need to create an account.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How do I reset my password?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>To reset your password:</p>
                            <ol>
                                <li>Click on "Login" in the top right corner</li>
                                <li>Select "Forgot Password" below the login form</li>
                                <li>Enter your registered email address</li>
                                <li>Check your email for a password reset link</li>
                                <li>Follow the link and create a new password</li>
                            </ol>
                            <p>If you don't receive the email, check your spam folder or contact our support team.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Buying FAQs -->
                <div class="faq-category" data-category="buying">
                    <h3 class="faq-category-title">Buying Sneakers</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How do I know the sneakers I'm buying are authentic?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>At Sneakerheads, authenticity is our top priority. All sneakers sold on our platform go through a rigorous authentication process:</p>
                            <ul>
                                <li>Sellers submit detailed photos and documentation</li>
                                <li>Our expert authenticators verify each pair before it's listed</li>
                                <li>We use advanced techniques to spot counterfeits</li>
                                <li>Each authenticated pair receives our verification seal</li>
                            </ul>
                            <p>If you ever receive a pair that you believe is not authentic, contact us immediately for a full investigation and refund if necessary.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>What payment methods are accepted?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>We accept the following payment methods:</p>
                            <ul>
                                <li>UPI (Google Pay, PhonePe, Paytm, etc.)</li>
                                <li>Cash on Delivery (COD) for orders under ₹10,000</li>
                                <li>Credit/Debit Cards (coming soon)</li>
                                <li>Net Banking (coming soon)</li>
                            </ul>
                            <p>All online payments are processed securely through our payment gateway.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>Can I return sneakers if they don't fit?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>We understand that sizing can vary between brands. Our return policy for size issues is:</p>
                            <ul>
                                <li>Returns for size issues must be initiated within 48 hours of delivery</li>
                                <li>The sneakers must be unworn, with original tags and packaging</li>
                                <li>You can request a different size (subject to availability) or a refund</li>
                                <li>Return shipping is the buyer's responsibility unless otherwise stated</li>
                            </ul>
                            <p>Please note that limited edition or rare sneakers may have different return policies, which will be clearly stated on the product page.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Selling FAQs -->
                <div class="faq-category" data-category="selling">
                    <h3 class="faq-category-title">Selling Sneakers</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How do I list sneakers for sale?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>To list sneakers for sale:</p>
                            <ol>
                                <li>Log in to your account and go to your dashboard</li>
                                <li>Click on "Sell Sneakers" or "List New Item"</li>
                                <li>Fill in the details about your sneakers (brand, model, size, condition, etc.)</li>
                                <li>Upload clear photos from multiple angles</li>
                                <li>Upload proof of purchase if available</li>
                                <li>Set your price and shipping options</li>
                                <li>Submit for authentication review</li>
                            </ol>
                            <p>Once our team verifies your listing, it will be published on the marketplace.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>What fees does Sneakerheads charge sellers?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our fee structure is transparent and competitive:</p>
                            <ul>
                                <li>10% commission on the final sale price</li>
                                <li>Authentication fee of ₹500 per pair (waived for sellers with 10+ successful sales)</li>
                                <li>No listing fee or monthly subscription required</li>
                            </ul>
                            <p>Fees are automatically deducted from your sale proceeds, and you'll receive a detailed breakdown for each transaction.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>When and how do I get paid for my sales?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Payment processing works as follows:</p>
                            <ol>
                                <li>When your sneakers sell, the buyer makes payment to Sneakerheads</li>
                                <li>Once the buyer receives and confirms the sneakers (or after 48 hours of delivery with no issues reported), the payment is released</li>
                                <li>Funds are transferred to your registered bank account or UPI ID within 2-3 business days</li>
                            </ol>
                            <p>You can track all your sales and payment status in your seller dashboard.</p>
                        </div>
                    </div>
                </div>
                
                <!-- More FAQ categories would follow the same pattern -->
                
                <!-- Shipping FAQs -->
                <div class="faq-category" data-category="shipping">
                    <h3 class="faq-category-title">Shipping & Delivery</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How long does shipping take?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Shipping times vary based on your location:</p>
                            <ul>
                                <li>Metro cities: 2-4 business days</li>
                                <li>Tier 2 cities: 3-5 business days</li>
                                <li>Other locations: 5-7 business days</li>
                            </ul>
                            <p>Please note that these are estimates and actual delivery times may vary based on factors like weather conditions and local logistics.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>Do you ship internationally?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Currently, Sneakerheads only ships within India. We plan to expand to international shipping in the future, so stay tuned for updates!</p>
                        </div>
                    </div>
                </div>
                
                <!-- Authentication FAQs -->
                <div class="faq-category" data-category="authentication">
                    <h3 class="faq-category-title">Authentication Process</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How does your authentication process work?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our authentication process involves multiple steps:</p>
                            <ol>
                                <li>Initial digital verification of photos and documentation</li>
                                <li>Physical inspection by certified authenticators</li>
                                <li>Verification of materials, stitching, labels, and other brand-specific details</li>
                                <li>UV light inspection for hidden security features</li>
                                <li>Box and accessories verification</li>
                            </ol>
                            <p>Only after passing all these checks will a pair receive our authentication seal and be listed for sale.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Payment FAQs -->
                <div class="faq-category" data-category="payment">
                    <h3 class="faq-category-title">Payment & Refunds</h3>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>What happens if I want a refund?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Our refund policy is designed to be fair to both buyers and sellers:</p>
                            <ul>
                                <li>For authenticity issues: Full refund including shipping costs</li>
                                <li>For size/fit issues: Refund minus shipping costs (if returned within 48 hours)</li>
                                <li>For damaged items: Full refund or replacement, depending on availability</li>
                                <li>For buyer's remorse: 80% refund if returned in original condition within 24 hours</li>
                            </ul>
                            <p>To initiate a refund, contact our support team with your order details and reason for the refund.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <h4>How long do refunds take to process?</h4>
                            <span class="faq-toggle"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="faq-answer">
                            <p>Once we receive and inspect the returned item:</p>
                            <ul>
                                <li>UPI refunds: 1-3 business days</li>
                                <li>Bank transfers: 3-5 business days</li>
                                <li>COD refunds: 5-7 business days</li>
                            </ul>
                            <p>You'll receive email notifications at each stage of the refund process.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Still Have Questions Section -->
        <section class="faq-contact">
            <div class="faq-contact-content">
                <h2>Still Have Questions?</h2>
                <p>Our support team is here to help you with any other questions or concerns.</p>
                <div class="faq-contact-buttons">
                    <a href="contact.php" class="btn">Contact Us</a>
                    <a href="mailto:support@sneakerheads.com" class="btn btn-secondary">Email Support</a>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ Accordion functionality
    const faqQuestions = document.querySelectorAll('.faq-question');
    
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const icon = this.querySelector('.faq-toggle i');
            
            // Toggle the active class
            this.classList.toggle('active');
            
            // Toggle the answer visibility
            if (answer.style.maxHeight) {
                answer.style.maxHeight = null;
                icon.classList.remove('fa-minus');
                icon.classList.add('fa-plus');
            } else {
                answer.style.maxHeight = answer.scrollHeight + 'px';
                icon.classList.remove('fa-plus');
                icon.classList.add('fa-minus');
            }
        });
    });
    
    // FAQ Category Tabs
    const faqTabs = document.querySelectorAll('.faq-tab');
    const faqCategories = document.querySelectorAll('.faq-category');
    
    faqTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            faqTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            
            // Show/hide categories based on selection
            if (category === 'all') {
                faqCategories.forEach(cat => cat.style.display = 'block');
            } else {
                faqCategories.forEach(cat => {
                    if (cat.getAttribute('data-category') === category) {
                        cat.style.display = 'block';
                    } else {
                        cat.style.display = 'none';
                    }
                });
            }
        });
    });
    
    // FAQ Search functionality
    const faqSearch = document.getElementById('faqSearch');
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question h4').textContent.toLowerCase();
            const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
            
            if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                item.style.display = 'block';
                
                // Make sure the category is visible
                const category = item.closest('.faq-category');
                category.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show "No results" message if needed
        const visibleItems = document.querySelectorAll('.faq-item[style="display: block;"]');
        const noResultsMsg = document.querySelector('.no-results-message');
        
        if (visibleItems.length === 0 && searchTerm !== '') {
            if (!noResultsMsg) {
                const message = document.createElement('div');
                message.className = 'no-results-message';
                message.innerHTML = '<p>No results found. Please try a different search term or <a href="contact.php">contact us</a> with your question.</p>';
                document.querySelector('.faq-content').appendChild(message);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

