<?php
// seed_report_data.php
require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();

// Clear old test data if needed or update existing
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE portfolio_visits;");
$pdo->exec("TRUNCATE TABLE resume;");
$pdo->exec("TRUNCATE TABLE portfolio_sections;");
$pdo->exec("TRUNCATE TABLE portfolios;");
$pdo->exec("TRUNCATE TABLE users;");
$pdo->exec("TRUNCATE TABLE admins;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

// Seed Admin
$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO admins (admin_id, username, email, password) VALUES (1, 'admin', 'admin@portfolioforge.com', ?)");
$stmt->execute([$adminPass]);

// Seed Main Demo User
$userPass = password_hash('password123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (user_id, full_name, username, email, password, status) VALUES (1, 'John Doe', 'john_doe', 'john@example.com', ?, 'active')");
$stmt->execute([$userPass]);

// Seed Secondary User (for admin management view)
$stmt = $pdo->prepare("INSERT INTO users (user_id, full_name, username, email, password, status) VALUES (2, 'Jane Smith', 'jane_smith', 'jane@example.com', ?, 'active')");
$stmt->execute([$userPass]);

// Seed Portfolio for John Doe
$stmt = $pdo->prepare("
    INSERT INTO portfolios (portfolio_id, user_id, template_id, title, portfolio_slug, status, accent_color, font_family, show_profile_image, show_email, show_phone, show_location)
    VALUES (1, 1, 1, 'John Doe - Senior Full Stack Developer', 'john-doe', 'published', '#2563eb', 'Inter, sans-serif', 1, 1, 1, 1)
");
$stmt->execute();

// Seed Portfolio for Jane Smith
$stmt = $pdo->prepare("
    INSERT INTO portfolios (portfolio_id, user_id, template_id, title, portfolio_slug, status, accent_color, font_family, show_profile_image, show_email, show_phone, show_location)
    VALUES (2, 2, 2, 'Jane Smith - UI/UX Designer', 'jane-smith', 'published', '#ec4899', 'Roboto, sans-serif', 1, 1, 1, 1)
");
$stmt->execute();

// Seed Portfolio Sections for John Doe
$sections = [
    [
        'type' => 'about',
        'title' => 'About Me',
        'content' => json_encode(['content' => 'Passionate Full Stack Developer with 3+ years of experience building scalable web applications using PHP, MySQL, JavaScript, and modern web standards. Dedicated to writing clean code and crafting seamless user experiences.']),
        'order' => 1
    ],
    [
        'type' => 'education',
        'title' => 'Education',
        'content' => json_encode([
            'degree' => 'Bachelor of Computer Applications (BCA)',
            'institution' => 'Tribhuvan University',
            'year' => '2021 - 2025',
            'details' => 'Specialized in Web Technologies, Database Management Systems, and Software Engineering.'
        ]),
        'order' => 2
    ],
    [
        'type' => 'skills',
        'title' => 'Technical Skills',
        'content' => json_encode([
            'skills' => ['PHP 8+', 'MySQL / MariaDB', 'JavaScript (ES6+)', 'HTML5 & CSS3', 'RESTful APIs', 'Git & GitHub', 'Docker', 'Linux Administration']
        ]),
        'order' => 3
    ],
    [
        'type' => 'projects',
        'title' => 'Featured Projects',
        'content' => json_encode([
            'projects' => [
                [
                    'name' => 'Portfolio Forge',
                    'description' => 'A dynamic, database-driven web application for creating, customizing, and publishing professional portfolios without code.',
                    'tech' => 'PHP, MySQL, Vanilla JS, CSS3',
                    'link' => 'https://github.com/example/portfolio-forge'
                ],
                [
                    'name' => 'E-Commerce Platform',
                    'description' => 'Full-featured online store with payment gateway integration, order tracking, and dynamic admin panel.',
                    'tech' => 'PHP, MySQL, Payment API',
                    'link' => 'https://github.com/example/ecommerce'
                ]
            ]
        ]),
        'order' => 4
    ],
    [
        'type' => 'experience',
        'title' => 'Work Experience',
        'content' => json_encode([
            'experiences' => [
                [
                    'role' => 'Junior Web Developer',
                    'company' => 'Tech Solutions Inc.',
                    'duration' => '2023 - Present',
                    'description' => 'Developed responsive backend services and optimized SQL queries, reducing page load time by 35%.'
                ]
            ]
        ]),
        'order' => 5
    ]
];

$stmt = $pdo->prepare("INSERT INTO portfolio_sections (portfolio_id, section_type, title, content, display_order, is_visible) VALUES (1, ?, ?, ?, ?, 1)");
foreach ($sections as $s) {
    $stmt->execute([$s['type'], $s['title'], $s['content'], $s['order']]);
}

// Seed Resume Record
$resumeDir = __DIR__ . '/uploads/resumes';
if (!file_exists($resumeDir)) {
    mkdir($resumeDir, 0777, true);
}
file_put_contents($resumeDir . '/john_doe_resume.pdf', "%PDF-1.4 ... Sample Resume Content for John Doe ...");

$stmt = $pdo->prepare("INSERT INTO resume (portfolio_id, file_name, file_path, public_download_enabled) VALUES (1, 'John_Doe_Resume.pdf', 'uploads/resumes/john_doe_resume.pdf', 1)");
$stmt->execute();

// Seed Visits for Analytics
$stmt = $pdo->prepare("INSERT INTO portfolio_visits (portfolio_id, visited_at) VALUES (1, ?)");
$now = time();
for ($i = 0; $i < 25; $i++) {
    $visitTime = date('Y-m-d H:i:s', $now - rand(0, 86400 * 7));
    $stmt->execute([$visitTime]);
}

echo "Database seeded successfully!\n";
