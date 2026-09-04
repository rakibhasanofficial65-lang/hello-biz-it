<?php

$page_title = "Contact Us | Hello Biz IT";

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/database.php';

$success = '';
$error = '';

$name = '';
$email = '';
$phone = '';
$service = '';
$subject = '';
$message = '';

/*
|--------------------------------------------------------------------------
| CONTACT FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($name === '') {

        $error = 'Please enter your full name.';

    } elseif ($email === '') {

        $error = 'Please enter your email address.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif ($service === '') {

        $error = 'Please select a service.';

    } elseif ($message === '') {

        $error = 'Please enter your message.';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Insert Contact Message
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                INSERT INTO contact_messages
                (
                    name,
                    email,
                    phone,
                    service,
                    subject,
                    message,
                    status
                )
                VALUES
                (
                    :name,
                    :email,
                    :phone,
                    :service,
                    :subject,
                    :message,
                    'unread'
                )
            ");

            $stmt->execute([
                ':name'    => $name,
                ':email'   => $email,
                ':phone'   => $phone,
                ':service' => $service,
                ':subject' => $subject,
                ':message' => $message
            ]);

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            $success = 'Thank you! Your message has been sent successfully. Our team will get back to you soon.';
            /*
            |--------------------------------------------------------------------------
            | Clear Form
            |--------------------------------------------------------------------------
            */

            $name = '';
            $email = '';
            $phone = '';
            $service = '';
            $subject = '';
            $message = '';

        } catch (PDOException $e) {

            $error = 'Something went wrong while sending your message. Please try again later.';

            /*
            | For development only.
            | Do not show database errors to visitors.
            */

            error_log($e->getMessage());
        }
    }
}

?>

<?php include __DIR__ . '/includes/header.php'; ?>


<!-- =========================================================
     CONTACT HERO
========================================================= -->

<section class="contact-hero-section section-padding dark-section">

    <div class="container">

        <div class="contact-hero-grid">

            <div class="contact-hero-content reveal">

                <span class="section-label">
                    CONTACT HELLO BIZ IT
                </span>

                <h1>
                    Let’s Talk About
                    <span>Your Business.</span>
                </h1>

                <p>
                    Have a project, business idea, or digital challenge?
                    Let's discuss how technology, creativity and digital
                    solutions can help your business grow.
                </p>

                <div class="contact-hero-buttons">

                    <a href="#contact-form" class="btn btn-primary">
                        Send A Message
                        <span>→</span>
                    </a>

                    <a
                        href="https://wa.me/8801560042479"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-outline"
                    >
                        WhatsApp Us
                    </a>

                </div>

            </div>


            <div class="contact-hero-visual reveal">

                <div class="contact-orbit orbit-one"></div>

                <div class="contact-orbit orbit-two"></div>

                <div class="contact-core">

                    <span>HB</span>

                    <small>
                        CONNECT
                    </small>

                </div>


                <div class="contact-floating-card card-message">

                    <span>✉</span>

                    <strong>
                        Let's Talk
                    </strong>

                </div>


                <div class="contact-floating-card card-growth">

                    <span>↗</span>

                    <strong>
                        Grow Together
                    </strong>

                </div>


                <div class="contact-dot dot-one"></div>

                <div class="contact-dot dot-two"></div>

                <div class="contact-dot dot-three"></div>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     CONTACT INFORMATION
========================================================= -->

<section class="contact-info-section section-padding">

    <div class="container">

        <div class="section-heading centered reveal">

            <span class="section-label">
                GET IN TOUCH
            </span>

            <h2>
                We're Here To Help You Move Forward.
            </h2>

            <p>
                Reach out to us through your preferred contact method.
                Our team will be happy to discuss your requirements.
            </p>

        </div>


        <div class="contact-info-grid">


            <!-- Phone -->

            <a
                href="tel:01560042479"
                class="contact-info-card reveal"
            >

                <div class="contact-info-icon">
                    ☎
                </div>

                <div>

                    <span>
                        Call Us
                    </span>

                    <strong>
                        015600-42479
                    </strong>

                    <small>
                        Let's discuss your project
                    </small>

                </div>

            </a>



            <!-- Email -->

            <a
                href="mailto:hellobizit@gmail.com"
                class="contact-info-card reveal"
            >

                <div class="contact-info-icon">
                    ✉
                </div>

                <div>

                    <span>
                        Email Us
                    </span>

                    <strong>
                        hellobizit@gmail.com
                    </strong>

                    <small>
                        Send us your requirements
                    </small>

                </div>

            </a>



            <!-- WhatsApp -->

            <a
                href="https://wa.me/8801560042479"
                target="_blank"
                rel="noopener noreferrer"
                class="contact-info-card reveal"
            >

                <div class="contact-info-icon">
                    ◉
                </div>

                <div>

                    <span>
                        WhatsApp
                    </span>

                    <strong>
                        015600-42479
                    </strong>

                    <small>
                        Message us directly
                    </small>

                </div>

            </a>



            <!-- Location -->

            <div class="contact-info-card reveal">

                <div class="contact-info-icon">
                    ⌖
                </div>

                <div>

                    <span>
                        Visit Our Office
                    </span>

                    <strong>
                      3rd Floor, Hello Biz It, Burail College Road, Kalir Bazar, Fulchari, Gaibandha, Bangladesh
                    </strong>

                    <small>
                        Office Based
                    </small>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     WHY CONTACT US
========================================================= -->

<section class="contact-services-section dark-section section-padding">

    <div class="container">

        <div class="section-heading centered reveal">

            <span class="section-label">
                HOW WE CAN HELP
            </span>

            <h2>
                One Conversation Can Start Something Great.
            </h2>

            <p>
                Whether you need technology, marketing, data or creative
                support, we're ready to understand your business needs.
            </p>

        </div>


        <div class="contact-service-grid">


            <!-- Technology -->

            <div class="contact-service-card reveal">

                <span class="service-card-number">
                    01
                </span>

                <div class="contact-service-icon">
                    &lt;/&gt;
                </div>

                <h3>
                    Technology
                </h3>

                <p>
                    Websites, software and digital solutions built around
                    your business requirements.
                </p>

            </div>



            <!-- Digital Growth -->

            <div class="contact-service-card reveal">

                <span class="service-card-number">
                    02
                </span>

                <div class="contact-service-icon">
                    ↗
                </div>

                <h3>
                    Digital Growth
                </h3>

                <p>
                    Digital marketing and business promotion strategies
                    designed to support growth.
                </p>

            </div>



            <!-- Data -->

            <div class="contact-service-card reveal">

                <span class="service-card-number">
                    03
                </span>

                <div class="contact-service-icon">
                    ◫
                </div>

                <h3>
                    Data
                </h3>

                <p>
                    Data management, processing and analytics solutions
                    that help businesses make better decisions.
                </p>

            </div>



            <!-- Creative -->

            <div class="contact-service-card reveal">

                <span class="service-card-number">
                    04
                </span>

                <div class="contact-service-icon">
                    ✦
                </div>

                <h3>
                    Creative Design
                </h3>

                <p>
                    Branding, graphics and creative digital design that
                    helps your business stand out.
                </p>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     CONTACT FORM + OFFICE LOCATION
========================================================= -->

<section
    class="contact-main-section section-padding"
    id="contact-form"
>

    <div class="container">

        <div class="contact-main-grid">


            <!-- =================================================
                 CONTACT FORM
            ================================================== -->

            <div class="contact-form-container reveal">

                <div class="section-heading">

                    <span class="section-label">
                        SEND US A MESSAGE
                    </span>

                    <h2>
                        Tell Us About Your Project.
                    </h2>

                    <p>
                        Fill out the form below and share your requirements.
                        We'll use your information to understand how we can
                        help.
                    </p>

                </div>


                <!-- Success Message -->

                <?php if ($success !== ''): ?>

                    <div class="form-success-message">

                        <strong>
                            ✓ Message Sent Successfully
                        </strong>

                        <p>
                            <?php echo escape($success); ?>
                        </p>

                    </div>

                <?php endif; ?>


                <!-- Error Message -->

                <?php if ($error !== ''): ?>

                    <div class="form-error-message">

                        <strong>
                            ⚠ Please Check
                        </strong>

                        <p>
                            <?php echo escape($error); ?>
                        </p>

                    </div>

                <?php endif; ?>


                <!-- Form -->

                <form
                    class="contact-form"
                    action="<?php echo escape($_SERVER['PHP_SELF']); ?>#contact-form"
                    method="POST"
                >


                    <!-- Name + Email -->

                    <div class="form-row">

                        <div class="form-group">

                            <label for="contact_name">

                                Full Name

                                <span>
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                id="contact_name"
                                name="name"
                                placeholder="Enter your full name"
                                required
                                maxlength="100"
                                value="<?php echo escape($name); ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="contact_email">

                                Email Address

                                <span>
                                    *
                                </span>

                            </label>

                            <input
                                type="email"
                                id="contact_email"
                                name="email"
                                placeholder="Enter your email"
                                required
                                maxlength="150"
                                value="<?php echo escape($email); ?>"
                            >

                        </div>

                    </div>



                    <!-- Phone + Service -->

                    <div class="form-row">

                        <div class="form-group">

                            <label for="contact_phone">

                                Phone Number

                            </label>

                            <input
                                type="tel"
                                id="contact_phone"
                                name="phone"
                                placeholder="Enter your phone number"
                                maxlength="30"
                                value="<?php echo escape($phone); ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="contact_service">

                                Service

                                <span>
                                    *
                                </span>

                            </label>

                            <select
                                id="contact_service"
                                name="service"
                                required
                            >

                                <option value="">
                                    Select a service
                                </option>

                                <option
                                    value="Web & Software Development"
                                    <?php echo ($service === 'Web & Software Development') ? 'selected' : ''; ?>
                                >
                                    Web & Software Development
                                </option>

                                <option
                                    value="Digital Marketing & Business Growth"
                                    <?php echo ($service === 'Digital Marketing & Business Growth') ? 'selected' : ''; ?>
                                >
                                    Digital Marketing & Business Growth
                                </option>

                                <option
                                    value="Social Media Marketing"
                                    <?php echo ($service === 'Social Media Marketing') ? 'selected' : ''; ?>
                                >
                                    Social Media Marketing
                                </option>

                                <option
                                    value="Data & Analytics Solutions"
                                    <?php echo ($service === 'Data & Analytics Solutions') ? 'selected' : ''; ?>
                                >
                                    Data & Analytics Solutions
                                </option>

                                <option
                                    value="Graphic, Creative & Art Design"
                                    <?php echo ($service === 'Graphic, Creative & Art Design') ? 'selected' : ''; ?>
                                >
                                    Graphic, Creative & Art Design
                                </option>

                                <option
                                    value="Architecture & Digital Design"
                                    <?php echo ($service === 'Architecture & Digital Design') ? 'selected' : ''; ?>
                                >
                                    Architecture & Digital Design
                                </option>

                                <option
                                    value="General Inquiry"
                                    <?php echo ($service === 'General Inquiry') ? 'selected' : ''; ?>
                                >
                                    General Inquiry
                                </option>

                            </select>

                        </div>

                    </div>



                    <!-- Subject -->

                    <div class="form-group">

                        <label for="contact_subject">

                            Subject

                        </label>

                        <input
                            type="text"
                            id="contact_subject"
                            name="subject"
                            placeholder="What would you like to discuss?"
                            maxlength="200"
                            value="<?php echo escape($subject); ?>"
                        >

                    </div>



                    <!-- Message -->

                    <div class="form-group">

                        <label for="contact_message">

                            Message

                            <span>
                                *
                            </span>

                        </label>

                        <textarea
                            id="contact_message"
                            name="message"
                            rows="6"
                            placeholder="Tell us about your project, business or requirements..."
                            required
                            maxlength="5000"
                        ><?php echo escape($message); ?></textarea>

                    </div>



                    <!-- Submit -->

                    <button
                        type="submit"
                        class="btn btn-primary contact-submit-btn"
                    >

                        Send Message

                        <span>
                            →
                        </span>

                    </button>

                </form>

            </div>



            <!-- =================================================
                 OFFICE LOCATION
            ================================================== -->

            <div class="contact-location-container reveal">

                <div class="location-card">

                    <span class="section-label">
                        OUR OFFICE
                    </span>

                    <h3>
                        Come &amp; Talk With Us.
                    </h3>

                    <p>
                        Visit our office or contact us online to discuss
                        your business requirements.
                    </p>


                    <div class="location-details">


                        <!-- Address -->

                        <div class="location-detail">

                            <span class="location-icon">
                                ⌖
                            </span>

                            <div>

                                <small>
                                    Office Address
                                </small>

                                <strong>

                                    3rd Floor, Hello Biz IT,<br>

                                    Burail College Road, Kalir Bazar,<br>

                                    Fulchari, Gaibandha-5760,<br>

                                    Bangladesh

                                </strong>

                            </div>

                        </div>



                        <!-- Map -->

                        <div class="location-detail">

                            <span class="location-icon">
                                ✦
                            </span>

                            <div>

                                <small>
                                    Map Location
                                </small>

                                <strong>
                                    7HFJ+WWR, Burail
                                </strong>

                            </div>

                        </div>



                        <!-- Phone -->

                        <div class="location-detail">

                            <span class="location-icon">
                                ☎
                            </span>

                            <div>

                                <small>
                                    Phone
                                </small>

                                <strong>
                                    015600-42479
                                </strong>

                            </div>

                        </div>



                        <!-- Email -->

                        <div class="location-detail">

                            <span class="location-icon">
                                ✉
                            </span>

                            <div>

                                <small>
                                    Email
                                </small>

                                <strong>
                                    hellobizit@gmail.com
                                </strong>

                            </div>

                        </div>

                    </div>



                    <!-- Google Maps -->

                    <a
                        href="https://www.google.com/maps/search/?api=1&query=Leather+Store+BD%2C+Burail%2C+Fulchari%2C+Gaibandha%2C+Bangladesh"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="location-map-button"
                    >

                        Open Location In Google Maps

                        <span>
                            ↗
                        </span>

                    </a>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =========================================================
     FINAL CTA
========================================================= -->

<section
    class="contact-final-cta section-padding dark-section"
>

    <div class="container">

        <div class="final-cta-box reveal">

            <div>

                <span class="section-label">
                    READY TO GET STARTED?
                </span>

                <h2>
                    Ready To Grow Your Business?
                </h2>

                <p>
                    Let's turn your ideas into technology, digital growth
                    and creative solutions.
                </p>

            </div>


            <div class="final-cta-actions">

                <a
                    href="tel:01560042479"
                    class="btn btn-primary"
                >
                    Call Us
                    <span>→</span>
                </a>


                <a
                    href="https://wa.me/8801560042479"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-outline"
                >
                    WhatsApp
                </a>

            </div>

        </div>

    </div>

</section>



<?php include __DIR__ . '/includes/footer.php'; ?>