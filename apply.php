<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/database.php';

$page_title = "Apply Now | " . SITE_NAME;

/*
|--------------------------------------------------------------------------
| GET JOB ID
|--------------------------------------------------------------------------
*/

$job_id = isset($_GET['job']) ? (int) $_GET['job'] : 0;

if ($job_id <= 0) {
    header("Location: career.php#open-positions");
    exit;
}

/*
|--------------------------------------------------------------------------
| GET JOB
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        slug,
        job_type,
        location,
        description
    FROM jobs
    WHERE id = ?
      AND status = 'active'
    LIMIT 1
");

$stmt->execute([$job_id]);

$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    header("Location: career.php#open-positions");
    exit;
}

/*
|--------------------------------------------------------------------------
| FORM VARIABLES
|--------------------------------------------------------------------------
*/

$errors = [];

$full_name = '';
$email = '';
$phone = '';
$education = '';
$experience = '';
$address = '';
$computer_available = '';
$laptop_available = '';
$message = '';

/*
|--------------------------------------------------------------------------
| VERCEL BLOB CONFIG
|--------------------------------------------------------------------------
|
| The Blob store is PUBLIC.
|
| Required Vercel Environment Variable:
|
| BLOB_READ_WRITE_TOKEN
|
*/

function getBlobToken(): string
{
    $token = getenv('BLOB_READ_WRITE_TOKEN');

    if (!$token) {
        throw new RuntimeException(
            'BLOB_READ_WRITE_TOKEN is not configured in Vercel.'
        );
    }

    return trim($token);
}

/*
|--------------------------------------------------------------------------
| UPLOAD TO VERCEL BLOB
|--------------------------------------------------------------------------
*/

function uploadToVercelBlob(
    array $file,
    string $pathname,
    string $contentType
): string {

    $token = getBlobToken();

    if (
        !isset($file['tmp_name']) ||
        !is_uploaded_file($file['tmp_name'])
    ) {
        throw new RuntimeException(
            'Uploaded file is invalid.'
        );
    }

    $fileContents = file_get_contents(
        $file['tmp_name']
    );

    if ($fileContents === false) {
        throw new RuntimeException(
            'Unable to read uploaded file.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VERCEL BLOB REST API
    |--------------------------------------------------------------------------
    */

    $url =
        'https://blob.vercel-storage.com/' .
        ltrim($pathname, '/');

    $ch = curl_init();

    if ($ch === false) {
        throw new RuntimeException(
            'Unable to initialize CURL.'
        );
    }

    curl_setopt_array($ch, [

        CURLOPT_URL => $url,

        CURLOPT_CUSTOMREQUEST => 'PUT',

        CURLOPT_POSTFIELDS => $fileContents,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_FOLLOWLOCATION => true,

        CURLOPT_HEADER => false,

        CURLOPT_HTTPHEADER => [

            'Authorization: Bearer ' . $token,

            'x-api-version: 7',

            'Content-Type: ' . $contentType,

            'x-content-type: ' . $contentType

        ],

        CURLOPT_CONNECTTIMEOUT => 20,

        CURLOPT_TIMEOUT => 120,

        CURLOPT_SSL_VERIFYPEER => true,

        CURLOPT_SSL_VERIFYHOST => 2

    ]);

    $response = curl_exec($ch);

    if ($response === false) {

        $curlError = curl_error($ch);

        curl_close($ch);

        throw new RuntimeException(
            'Vercel Blob CURL error: ' . $curlError
        );
    }

    $httpCode = (int) curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    /*
    |--------------------------------------------------------------------------
    | DEBUG HTTP ERROR
    |--------------------------------------------------------------------------
    */

    if ($httpCode < 200 || $httpCode >= 300) {

        throw new RuntimeException(
            'Vercel Blob upload failed. HTTP ' .
            $httpCode .
            '. Response: ' .
            substr($response, 0, 1000)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PARSE RESPONSE
    |--------------------------------------------------------------------------
    */

    $json = json_decode(
        $response,
        true
    );

    if (
        !is_array($json) ||
        empty($json['url'])
    ) {

        throw new RuntimeException(
            'Vercel Blob returned an invalid response: ' .
            substr($response, 0, 1000)
        );
    }

    return (string) $json['url'];
}

/*
|--------------------------------------------------------------------------
| DELETE VERCEL BLOB
|--------------------------------------------------------------------------
*/

function deleteVercelBlob(string $url): void
{
    if ($url === '') {
        return;
    }

    try {
        $token = getBlobToken();
    } catch (Throwable $e) {
        return;
    }

    $ch = curl_init();

    if ($ch === false) {
        return;
    }

    curl_setopt_array($ch, [

        CURLOPT_URL => $url,

        CURLOPT_CUSTOMREQUEST => 'DELETE',

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => [

            'Authorization: Bearer ' . $token,

            'x-api-version: 7'

        ],

        CURLOPT_CONNECTTIMEOUT => 10,

        CURLOPT_TIMEOUT => 30,

        CURLOPT_SSL_VERIFYPEER => true,

        CURLOPT_SSL_VERIFYHOST => 2

    ]);

    curl_exec($ch);

    curl_close($ch);
}

/*
|--------------------------------------------------------------------------
| FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | GET FORM DATA
    |--------------------------------------------------------------------------
    */

    $full_name = trim(
        $_POST['full_name'] ?? ''
    );

    $email = trim(
        $_POST['email'] ?? ''
    );

    $phone = trim(
        $_POST['phone'] ?? ''
    );

    $education = trim(
        $_POST['education'] ?? ''
    );

    $experience = trim(
        $_POST['experience'] ?? ''
    );

    $address = trim(
        $_POST['address'] ?? ''
    );

    $computer_available =
        $_POST['computer_available'] ?? '';

    $laptop_available =
        $_POST['laptop_available'] ?? '';

    $message = trim(
        $_POST['message'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | BASIC VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($full_name === '') {
        $errors[] =
            'Please enter your full name.';
    }

    if ($email === '') {

        $errors[] =
            'Please enter your email address.';

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $errors[] =
            'Please enter a valid email address.';
    }

    if ($phone === '') {
        $errors[] =
            'Please enter your phone number.';
    }

    if ($education === '') {
        $errors[] =
            'Please enter your educational qualification.';
    }

    if ($address === '') {
        $errors[] =
            'Please enter your address.';
    }

    if (
        !in_array(
            $computer_available,
            ['yes', 'no'],
            true
        )
    ) {

        $errors[] =
            'Please select whether you have access to a computer.';
    }

    if (
        !in_array(
            $laptop_available,
            ['yes', 'no'],
            true
        )
    ) {

        $errors[] =
            'Please select whether you have access to a laptop.';
    }

    /*
    |--------------------------------------------------------------------------
    | PHOTO VALIDATION
    |--------------------------------------------------------------------------
    */

    $photo = $_FILES['photo'] ?? null;

    $allowedPhotoTypes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $photoMime = '';

    if (
        !$photo ||
        !isset($photo['error']) ||
        $photo['error'] === UPLOAD_ERR_NO_FILE
    ) {

        $errors[] =
            'Please upload your photo.';

    } elseif (
        $photo['error'] !== UPLOAD_ERR_OK
    ) {

        $errors[] =
            'There was a problem uploading your photo.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | PHOTO SIZE
        |--------------------------------------------------------------------------
        */

        if (
            (int) $photo['size'] >
            500 * 1024
        ) {

            $errors[] =
                'Photo size must not exceed 500KB.';
        }

        /*
        |--------------------------------------------------------------------------
        | PHOTO MIME
        |--------------------------------------------------------------------------
        */

        if (
            isset($photo['tmp_name']) &&
            is_uploaded_file($photo['tmp_name'])
        ) {

            $finfo = new finfo(
                FILEINFO_MIME_TYPE
            );

            $photoMime =
                $finfo->file(
                    $photo['tmp_name']
                ) ?: '';
        }

        if (
            !isset(
                $allowedPhotoTypes[$photoMime]
            )
        ) {

            $errors[] =
                'Photo must be JPG, PNG or WEBP.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CV VALIDATION
    |--------------------------------------------------------------------------
    */

    $cv = $_FILES['cv'] ?? null;

    $allowedCvExtensions = [
        'pdf',
        'doc',
        'docx'
    ];

    $cvExtension = '';

    if (
        !$cv ||
        !isset($cv['error']) ||
        $cv['error'] === UPLOAD_ERR_NO_FILE
    ) {

        $errors[] =
            'Please upload your CV.';

    } elseif (
        $cv['error'] !== UPLOAD_ERR_OK
    ) {

        $errors[] =
            'There was a problem uploading your CV.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | CV SIZE
        |--------------------------------------------------------------------------
        */

        if (
            (int) $cv['size'] >
            5 * 1024 * 1024
        ) {

            $errors[] =
                'CV size must not exceed 5MB.';
        }

        /*
        |--------------------------------------------------------------------------
        | CV EXTENSION
        |--------------------------------------------------------------------------
        */

        $cvExtension = strtolower(
            pathinfo(
                $cv['name'] ?? '',
                PATHINFO_EXTENSION
            )
        );

        if (
            !in_array(
                $cvExtension,
                $allowedCvExtensions,
                true
            )
        ) {

            $errors[] =
                'CV must be PDF, DOC or DOCX.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD + DATABASE
    |--------------------------------------------------------------------------
    */

    $photoUrl = null;
    $cvUrl = null;

    if (empty($errors)) {

        try {

            /*
            |--------------------------------------------------------------------------
            | PHOTO EXTENSION
            |--------------------------------------------------------------------------
            */

            $photoExtension =
                $allowedPhotoTypes[$photoMime];

            /*
            |--------------------------------------------------------------------------
            | PHOTO PATH
            |--------------------------------------------------------------------------
            */

            $photoPath =
                'career/photos/' .
                date('Y/m/') .
                'job-' .
                $job_id .
                '-' .
                bin2hex(
                    random_bytes(16)
                ) .
                '.' .
                $photoExtension;

            /*
            |--------------------------------------------------------------------------
            | UPLOAD PHOTO
            |--------------------------------------------------------------------------
            */

            $photoUrl =
                uploadToVercelBlob(
                    $photo,
                    $photoPath,
                    $photoMime
                );

            /*
            |--------------------------------------------------------------------------
            | CV CONTENT TYPE
            |--------------------------------------------------------------------------
            */

            switch ($cvExtension) {

                case 'pdf':

                    $cvMime =
                        'application/pdf';

                    break;

                case 'doc':

                    $cvMime =
                        'application/msword';

                    break;

                case 'docx':

                    $cvMime =
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

                    break;

                default:

                    $cvMime =
                        'application/octet-stream';
            }

            /*
            |--------------------------------------------------------------------------
            | CV PATH
            |--------------------------------------------------------------------------
            */

            $cvPath =
                'career/cvs/' .
                date('Y/m/') .
                'job-' .
                $job_id .
                '-' .
                bin2hex(
                    random_bytes(16)
                ) .
                '.' .
                $cvExtension;

            /*
            |--------------------------------------------------------------------------
            | UPLOAD CV
            |--------------------------------------------------------------------------
            */

            $cvUrl =
                uploadToVercelBlob(
                    $cv,
                    $cvPath,
                    $cvMime
                );

            /*
            |--------------------------------------------------------------------------
            | DATABASE INSERT
            |--------------------------------------------------------------------------
            */

            $insert = $pdo->prepare("
                INSERT INTO career_applications (
                    job_id,
                    full_name,
                    email,
                    phone,
                    photo_file,
                    education,
                    experience,
                    address,
                    computer_available,
                    laptop_available,
                    message,
                    cv_file,
                    status
                )
                VALUES (
                    :job_id,
                    :full_name,
                    :email,
                    :phone,
                    :photo_file,
                    :education,
                    :experience,
                    :address,
                    :computer_available,
                    :laptop_available,
                    :message,
                    :cv_file,
                    'pending'
                )
            ");

            $insert->execute([

                ':job_id' =>
                    $job_id,

                ':full_name' =>
                    $full_name,

                ':email' =>
                    $email,

                ':phone' =>
                    $phone,

                ':photo_file' =>
                    $photoUrl,

                ':education' =>
                    $education,

                ':experience' =>
                    $experience,

                ':address' =>
                    $address,

                ':computer_available' =>
                    $computer_available,

                ':laptop_available' =>
                    $laptop_available,

                ':message' =>
                    $message,

                ':cv_file' =>
                    $cvUrl

            ]);

            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            header(
                'Location: apply.php?job=' .
                $job_id .
                '&success=1'
            );

            exit;

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | DELETE PHOTO IF CV/DB FAILED
            |--------------------------------------------------------------------------
            */

            if ($photoUrl !== null) {

                deleteVercelBlob(
                    $photoUrl
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE CV IF DB FAILED
            |--------------------------------------------------------------------------
            */

            if ($cvUrl !== null) {

                deleteVercelBlob(
                    $cvUrl
                );
            }

            /*
            |--------------------------------------------------------------------------
            | LOG REAL ERROR
            |--------------------------------------------------------------------------
            */

            error_log(
                'Career application error: ' .
                $e->getMessage()
            );

            /*
            |--------------------------------------------------------------------------
            | SHOW REAL ERROR TEMPORARILY
            |--------------------------------------------------------------------------
            |
            | This is useful while fixing production.
            |
            */

            $errors[] =
                'Application failed: ' .
                $e->getMessage();
        }
    }
}

/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include __DIR__ . '/includes/header.php';

?>

<!-- =========================================================
     APPLY HERO
========================================================= -->

<section class="career-apply-hero dark-section">

    <div class="career-apply-glow glow-one"></div>

    <div class="career-apply-glow glow-two"></div>

    <div class="container">

        <div class="career-apply-hero-content">

            <span class="section-label">
                CAREER APPLICATION
            </span>

            <h1>
                Apply for

                <span>
                    <?php
                    echo escape($job['title']);
                    ?>
                </span>
            </h1>

            <p>
                Complete the application form below to apply for this
                position at Hello Biz IT.
            </p>

        </div>

    </div>

</section>

<!-- =========================================================
     APPLICATION SECTION
========================================================= -->

<section class="career-apply-section section-padding">

    <div class="container">

        <?php if (isset($_GET['success'])): ?>

            <div class="application-success">

                <div class="success-icon">
                    ✓
                </div>

                <h2>
                    Application Submitted Successfully
                </h2>

                <p>
                    Thank you for applying to Hello Biz IT.
                    Our team will review your application and contact
                    you if your profile is shortlisted.
                </p>

                <a
                    href="career.php#open-positions"
                    class="btn btn-primary"
                >
                    Back to Careers
                    <span>→</span>
                </a>

            </div>

        <?php else: ?>

            <div class="career-apply-layout">

                <!-- JOB SUMMARY -->

                <aside class="career-apply-job-card">

                    <span class="section-label">
                        SELECTED POSITION
                    </span>

                    <div class="apply-job-icon">
                        ✦
                    </div>

                    <h2>
                        <?php
                        echo escape(
                            $job['title']
                        );
                        ?>
                    </h2>

                    <p>
                        <?php
                        echo escape(
                            $job['description']
                        );
                        ?>
                    </p>

                    <div class="apply-job-meta">

                        <div>

                            <span>
                                JOB TYPE
                            </span>

                            <strong>
                                <?php
                                echo escape(
                                    $job['job_type']
                                );
                                ?>
                            </strong>

                        </div>

                        <div>

                            <span>
                                LOCATION
                            </span>

                            <strong>
                                <?php
                                echo escape(
                                    $job['location']
                                );
                                ?>
                            </strong>

                        </div>

                    </div>

                </aside>

                <!-- FORM -->

                <div class="career-application-card">

                    <div class="application-card-heading">

                        <span class="section-label">
                            APPLICATION FORM
                        </span>

                        <h2>
                            Tell Us About Yourself.
                        </h2>

                        <p>
                            Please provide accurate information so our
                            recruitment team can review your application.
                        </p>

                    </div>

                    <?php if (!empty($errors)): ?>

                        <div class="application-errors">

                            <strong>
                                Please correct the following:
                            </strong>

                            <ul>

                                <?php foreach ($errors as $error): ?>

                                    <li>
                                        <?php
                                        echo escape($error);
                                        ?>
                                    </li>

                                <?php endforeach; ?>

                            </ul>

                        </div>

                    <?php endif; ?>

                    <form
                        action="apply.php?job=<?php echo (int) $job_id; ?>"
                        method="POST"
                        enctype="multipart/form-data"
                        class="career-application-form"
                    >

                        <!-- PERSONAL INFORMATION -->

                        <div class="form-section-title">

                            <span>
                                01
                            </span>

                            Personal Information

                        </div>

                        <div class="form-grid">

                            <div class="form-group">

                                <label for="full_name">
                                    Full Name
                                    <span>*</span>
                                </label>

                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    value="<?php echo escape($full_name); ?>"
                                    placeholder="Enter your full name"
                                    maxlength="150"
                                    required
                                >

                            </div>

                            <div class="form-group">

                                <label for="email">
                                    Email Address
                                    <span>*</span>
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?php echo escape($email); ?>"
                                    placeholder="you@example.com"
                                    maxlength="190"
                                    required
                                >

                            </div>

                            <div class="form-group">

                                <label for="phone">
                                    Phone Number
                                    <span>*</span>
                                </label>

                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    value="<?php echo escape($phone); ?>"
                                    placeholder="+880 1XXXXXXXXX"
                                    maxlength="30"
                                    required
                                >

                            </div>

                            <div class="form-group">

                                <label for="education">
                                    Education
                                    <span>*</span>
                                </label>

                                <input
                                    type="text"
                                    id="education"
                                    name="education"
                                    value="<?php echo escape($education); ?>"
                                    placeholder="e.g. B.Sc. in Computer Science"
                                    maxlength="255"
                                    required
                                >

                            </div>

                        </div>

                        <!-- PHOTO -->

                        <div class="form-group">

                            <label for="photo">
                                Profile Photo
                                <span>*</span>
                            </label>

                            <input
                                type="file"
                                id="photo"
                                name="photo"
                                accept=".jpg,.jpeg,.png,.webp"
                                required
                            >

                            <small>
                                JPG, PNG or WEBP · Maximum 500KB
                            </small>

                        </div>

                        <!-- EXPERIENCE -->

                        <div class="form-group">

                            <label for="experience">
                                Work Experience
                            </label>

                            <textarea
                                id="experience"
                                name="experience"
                                rows="4"
                                maxlength="3000"
                                placeholder="Describe your previous work experience..."
                            ><?php
                            echo escape($experience);
                            ?></textarea>

                        </div>

                        <!-- ADDRESS -->

                        <div class="form-group">

                            <label for="address">
                                Address
                                <span>*</span>
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="3"
                                maxlength="1000"
                                placeholder="Enter your current address"
                                required
                            ><?php
                            echo escape($address);
                            ?></textarea>

                        </div>

                        <!-- EQUIPMENT -->

                        <div class="form-section-title">

                            <span>
                                02
                            </span>

                            Equipment Availability

                        </div>

                        <div class="equipment-question">

                            <label>
                                Do you have access to a working computer?
                                <span>*</span>
                            </label>

                            <div class="application-radio-group">

                                <label class="application-radio-option">

                                    <input
                                        type="radio"
                                        name="computer_available"
                                        value="yes"
                                        <?php
                                        echo $computer_available === 'yes'
                                            ? 'checked'
                                            : '';
                                        ?>
                                        required
                                    >

                                    <span>
                                        Yes, I have a computer
                                    </span>

                                </label>

                                <label class="application-radio-option">

                                    <input
                                        type="radio"
                                        name="computer_available"
                                        value="no"
                                        <?php
                                        echo $computer_available === 'no'
                                            ? 'checked'
                                            : '';
                                        ?>
                                    >

                                    <span>
                                        No, I don't have a computer
                                    </span>

                                </label>

                            </div>

                        </div>

                        <div class="equipment-question">

                            <label>
                                Do you have access to a laptop?
                                <span>*</span>
                            </label>

                            <div class="application-radio-group">

                                <label class="application-radio-option">

                                    <input
                                        type="radio"
                                        name="laptop_available"
                                        value="yes"
                                        <?php
                                        echo $laptop_available === 'yes'
                                            ? 'checked'
                                            : '';
                                        ?>
                                        required
                                    >

                                    <span>
                                        Yes, I have a laptop
                                    </span>

                                </label>

                                <label class="application-radio-option">

                                    <input
                                        type="radio"
                                        name="laptop_available"
                                        value="no"
                                        <?php
                                        echo $laptop_available === 'no'
                                            ? 'checked'
                                            : '';
                                        ?>
                                    >

                                    <span>
                                        No, I don't have a laptop
                                    </span>

                                </label>

                            </div>

                        </div>

                        <!-- MESSAGE -->

                        <div class="form-section-title">

                            <span>
                                03
                            </span>

                            Additional Information

                        </div>

                        <div class="form-group">

                            <label for="message">
                                Cover Message
                            </label>

                            <textarea
                                id="message"
                                name="message"
                                rows="6"
                                maxlength="5000"
                                placeholder="Tell us why you are interested in this position..."
                            ><?php
                            echo escape($message);
                            ?></textarea>

                        </div>

                        <!-- CV -->

                        <div class="form-group">

                            <label for="cv">
                                Resume / CV
                                <span>*</span>
                            </label>

                            <input
                                type="file"
                                id="cv"
                                name="cv"
                                accept=".pdf,.doc,.docx"
                                required
                            >

                            <small>
                                PDF, DOC or DOCX · Maximum 5MB
                            </small>

                        </div>

                        <!-- SUBMIT -->

                        <div class="application-submit">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Submit Application
                                <span>→</span>
                            </button>

                            <p>
                                By submitting this application, you confirm
                                that the information provided is accurate.
                            </p>

                        </div>

                    </form>

                </div>

            </div>

        <?php endif; ?>

    </div>

</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
