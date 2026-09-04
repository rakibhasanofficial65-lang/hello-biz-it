<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/database.php';

$stmt = $pdo->query("
    SELECT id, title, slug, job_type, location, description
    FROM jobs
    WHERE status = 'active'
    ORDER BY id ASC
");

$jobs = $stmt->fetchAll();

$page_title = "Careers | " . SITE_NAME;

include __DIR__ . '/includes/header.php';

?>

<!-- ==========================================
     CAREER HERO
========================================== -->
<section class="career-hero-section dark-section section-padding">

    <div class="career-hero-glow glow-one"></div>
    <div class="career-hero-glow glow-two"></div>

    <div class="container">

        <div class="career-hero-content reveal">

            <span class="section-label">CAREER AT HELLO BIZ IT</span>

            <h1>
                Build Your Career.
                <span>Grow With Us.</span>
            </h1>

            <p>
                Join Hello Biz IT and work with technology, digital
                marketing, data and creative solutions while developing
                your skills and career.
            </p>

            <div class="career-hero-buttons">

                <a href="#open-positions" class="btn btn-primary">
                    Explore Positions <span>→</span>
                </a>

                <a href="#career-info" class="btn btn-outline">
                    Career Information
                </a>

            </div>

        </div>

    </div>

</section>


<!-- ==========================================
     CAREER INTRO
========================================== -->
<section class="career-intro-section section-padding" id="career-info">

    <div class="container">

        <div class="section-heading centered reveal">

            <span class="section-label">JOIN OUR TEAM</span>

            <h2>
                Work. Learn. Grow.
            </h2>

            <p>
                We are looking for motivated people who are ready to learn,
                take responsibility and contribute to meaningful digital
                projects.
            </p>

        </div>


        <div class="career-info-grid">

            <!-- Requirements -->
            <div class="career-info-card reveal">

                <div class="career-info-icon">✓</div>

                <h3>General Requirements</h3>

                <ul>
                    <li>Responsible and punctual</li>
                    <li>Willingness to learn new skills</li>
                    <li>Good communication and teamwork</li>
                    <li>Ability to follow instructions</li>
                    <li>Ability to meet deadlines</li>
                    <li>Basic computer knowledge</li>
                    <li>Previous experience is preferred</li>
                </ul>

            </div>


            <!-- Shift -->
            <div class="career-info-card reveal">

                <div class="career-info-icon">◷</div>

                <h3>Working Shifts</h3>

                <div class="shift-box">

                    <span class="shift-badge evening">
                        Evening Shift
                    </span>

                    <strong>
                        5:00 PM – 9:00 PM
                    </strong>

                    <small>
                        Half-Time · 4 Hours
                    </small>

                </div>


                <div class="shift-box">

                    <span class="shift-badge night">
                        Night Shift
                    </span>

                    <strong>
                        9:30 PM – 8:00 AM
                    </strong>

                    <small>
                        Break time available
                    </small>

                </div>

            </div>


            <!-- Equipment -->
            <div class="career-info-card reveal">

                <div class="career-info-icon">⌨</div>

                <h3>Equipment</h3>

                <p>
                    Candidates should have access to a working PC or laptop
                    suitable for their assigned position.
                </p>

                <div class="equipment-note">
                    <strong>PC / Laptop</strong>
                    <span>Required for assigned work</span>
                </div>

            </div>


            <!-- Schedule -->
            <div class="career-info-card reveal">

                <div class="career-info-icon">▣</div>

                <h3>Work Schedule</h3>

                <p>
                    Our work schedule is designed to provide a structured
                    working environment while maintaining reasonable
                    time flexibility.
                </p>

                <div class="schedule-highlight">

                    <strong>4 Days</strong>

                    <span>
                        Monthly Holiday
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ==========================================
     OPEN POSITIONS
========================================== -->
<section
    class="career-jobs-section dark-section section-padding"
    id="open-positions"
>

    <div class="container">

        <div class="section-heading centered reveal">

            <span class="section-label">
                OPEN POSITIONS
            </span>

            <h2>
                Find Your Next Opportunity.
            </h2>

            <p>
                Explore our current career opportunities and find a role
                that matches your skills and interests.
            </p>

        </div>


        <?php if (!empty($jobs)): ?>

            <div class="career-jobs-grid">

                <?php foreach ($jobs as $index => $job): ?>

                    <article class="career-job-card reveal">

                        <div class="job-card-top">

                            <span class="job-number">
                                <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>
                            </span>

                            <span class="job-status">
                                Open Position
                            </span>

                        </div>


                        <div class="job-icon">
                            ✦
                        </div>


                        <h3>
                            <?php echo escape($job['title']); ?>
                        </h3>


                        <p class="job-description">
                            <?php echo escape($job['description']); ?>
                        </p>


                        <div class="job-meta">

                            <span>
                                <strong>Type</strong>
                                <?php echo escape($job['job_type']); ?>
                            </span>

                            <span>
                                <strong>Location</strong>
                                <?php echo escape($job['location']); ?>
                            </span>

                        </div>


                        <a
                            href="apply.php?job=<?php echo (int) $job['id']; ?>"
                            class="job-apply-button"
                        >
                            View Details & Apply
                            <span>→</span>
                        </a>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="no-jobs-message">

                <div>✦</div>

                <h3>
                    No Current Openings
                </h3>

                <p>
                    Please check back later for new career opportunities.
                </p>

            </div>

        <?php endif; ?>

    </div>

</section>


<!-- ==========================================
     CAREER CTA
========================================== -->
<section class="career-final-cta section-padding">

    <div class="container">

        <div class="career-cta-box reveal">

            <div>

                <span class="section-label">
                    READY TO JOIN?
                </span>

                <h2>
                    Start Your Journey With Hello Biz IT.
                </h2>

                <p>
                    Find a position that matches your skills and take
                    the next step in your career.
                </p>

            </div>


            <a
                href="#open-positions"
                class="btn btn-primary"
            >
                View Open Positions
                <span>→</span>
            </a>

        </div>

    </div>

</section>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const jobCards = document.querySelectorAll('.career-job-card');

    jobCards.forEach(function (card) {

        card.addEventListener('click', function (event) {

            jobCards.forEach(function (item) {
                item.classList.remove('is-active');
            });

            card.classList.add('is-active');

        });

    });

});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
