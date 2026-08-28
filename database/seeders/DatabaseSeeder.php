<?php

namespace Database\Seeders;

use App\Models\Education;
use App\Models\Experience;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'view dashboard', 'dashboard.view',
            
            // Users CRUD
            'view user', 'create user', 'update user', 'delete user',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            
            // Roles CRUD
            'view role', 'create role', 'update role', 'delete role',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            
            // Permissions CRUD
            'view permission', 'create permission', 'update permission', 'delete permission',
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
            
            // Business & POS Modules CRUD
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'view products', 'create product', 'update product', 'delete product',
            'sales.view', 'sales.create', 'sales.edit', 'sales.delete',
            'view sales', 'create sale', 'update sale', 'delete sale',
            'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete',
            'view purchases', 'create purchase', 'update purchase', 'delete purchase',
            'reports.view', 'reports.create', 'reports.edit', 'reports.delete',
            'view reports',
            'settings.view', 'settings.create', 'settings.edit', 'settings.delete',
            'view settings', 'update settings',

            // Portfolio & Content CRUD
            'view profile', 'update profile',
            'view project', 'create project', 'update project', 'delete project',
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'view post', 'create post', 'update post', 'delete post',
            'posts.view', 'posts.create', 'posts.edit', 'posts.delete',
            'view service', 'create service', 'update service', 'delete service',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'view experience', 'create experience', 'update experience', 'delete experience',
            'experiences.view', 'experiences.create', 'experiences.edit', 'experiences.delete',
            'view education', 'create education', 'update education', 'delete education',
            'educations.view', 'educations.create', 'educations.edit', 'educations.delete',
            'view skill', 'create skill', 'update skill', 'delete skill',
            'skills.view', 'skills.create', 'skills.edit', 'skills.delete',
            'view date of birth', 'create date of birth', 'update date of birth', 'delete date of birth',
            'date_of_births.view', 'date_of_births.create', 'date_of_births.edit', 'date_of_births.delete',
            'view message', 'create message', 'update message', 'delete message',
            'messages.view', 'messages.edit', 'messages.delete',
            'view maintenance', 'run maintenance',
            'view visitors', 'visitors.view',
            
            // Finance & Investors CRUD
            'view investors', 'create investor', 'update investor', 'delete investor',
            'investors.view', 'investors.create', 'investors.edit', 'investors.delete',
            'view investments', 'create investment', 'update investment', 'delete investment',
            'investments.view', 'investments.create', 'investments.edit', 'investments.delete',
            'view profit sharing', 'confirm profit sharing', 'create profit sharing', 'update profit sharing', 'delete profit sharing',
            'profit_sharing.view', 'profit_sharing.create',
            'view profit payments', 'create profit payment', 'update profit payment', 'delete profit payment',
            'profit_payments.view', 'profit_payments.create',
            'view investment withdrawals', 'create investment withdrawal', 'update investment withdrawal', 'delete investment withdrawal',
            'investment_withdrawals.view', 'investment_withdrawals.create',
            'view investor reports', 'investor_reports.view',
            
            // Programs CRUD
            'view programs', 'create program', 'update program', 'delete program',
            'programs.view', 'programs.create', 'programs.edit', 'programs.delete',
            'view contributions', 'create contribution', 'update contribution', 'delete contribution',
            'contributions.view', 'contributions.create', 'contributions.edit', 'contributions.delete',
            'view expense categories', 'create expense category', 'update expense category', 'delete expense category',
            'expense_categories.view', 'expense_categories.create', 'expense_categories.edit', 'expense_categories.delete',
            'view program expenses', 'create program expense', 'update program expense', 'delete program expense',
            'program_expenses.view', 'program_expenses.create', 'program_expenses.edit', 'program_expenses.delete',
            'view program transactions', 'create program transaction', 'update program transaction', 'delete program transaction',
            'program_transactions.view',
            'view program reports', 'program_reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $legacyAdminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);
        $editorRole = Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        $legacyEditorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $legacyUserRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $allPermissions = Permission::where('guard_name', 'web')->get();
        $superAdminRole->syncPermissions($allPermissions);
        $adminRole->syncPermissions($allPermissions);
        $legacyAdminRole->syncPermissions($allPermissions);

        $managerRole->syncPermissions(
            Permission::whereIn('name', [
                'view dashboard', 'dashboard.view',
                'users.view', 'view user',
                'products.view', 'products.create', 'products.edit',
                'sales.view', 'sales.create',
                'purchases.view',
                'reports.view',
                'view profile', 'update profile',
                'view project', 'create project', 'update project',
                'view post', 'create post', 'update post',
                'view message', 'update message',
            ])->get()
        );

        $cashierRole->syncPermissions(
            Permission::whereIn('name', [
                'view dashboard', 'dashboard.view',
                'sales.view', 'sales.create',
                'products.view',
            ])->get()
        );

        $editorPermissions = Permission::whereIn('name', [
            'view dashboard', 'dashboard.view',
            'view profile', 'update profile',
            'view project', 'create project', 'update project', 'delete project',
            'view post', 'create post', 'update post', 'delete post',
            'view service', 'create service', 'update service', 'delete service',
            'view experience', 'create experience', 'update experience', 'delete experience',
            'view education', 'create education', 'update education', 'delete education',
            'view skill', 'create skill', 'update skill', 'delete skill',
            'view date of birth', 'create date of birth', 'update date of birth', 'delete date of birth',
            'view message',
        ])->get();
        $editorRole->syncPermissions($editorPermissions);
        $legacyEditorRole->syncPermissions($editorPermissions);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            ['name' => 'Super Administrator', 'password' => Hash::make('12345678')]
        );
        $admin->syncRoles([$superAdminRole, $legacyAdminRole]);

        Profile::query()->updateOrCreate(['id' => 1], [
            'full_name' => 'Amanullah',
            'headline' => 'PHP & Laravel Web Developer',
            'short_bio' => 'I build responsive, secure, and maintainable web applications with PHP, Laravel, CodeIgniter, MySQL, JavaScript, jQuery, AJAX, Bootstrap, and Tailwind CSS.',
            'long_bio' => 'I am a full-stack web developer focused on practical business applications. My experience covers object-oriented PHP, Laravel, CodeIgniter, MySQL, JavaScript, jQuery, AJAX, responsive front-end development, cPanel deployment, and FileZilla-based project management. I value clean code, dependable performance, secure development practices, and interfaces that work smoothly across desktop, tablet, and mobile devices.',
            'email' => 'aman.ullah.csc@gmail.com',
            'phone' => '03183588065',
            'phone_secondary' => null,
            'address' => 'DHA Phase 2, Main Korangi Road',
            'city' => 'Karachi',
            'country' => 'Pakistan',
            'availability' => 'Available for selected web projects',
            'use_experience_dates' => false,
            'years_experience' => 2,
            'project_count' => 12,
            'happy_clients' => 8,
            'languages' => ['English', 'Urdu', 'Sindhi'],
            'github_url' => null,
            'linkedin_url' => null,
        ]);

        $experiences = [
            [
                'company' => 'Global Dezign',
                'position' => 'PHP & Laravel Developer',
                'location' => 'Karachi',
                'start_date' => '2025-11-01',
                'end_date' => null,
                'is_current' => true,
                'summary' => 'Building real-world PHP and Laravel applications with a focus on performance, maintainability, and responsive user experiences.',
                'bullets' => [
                    'Develop responsive, high-performance applications using object-oriented PHP and Laravel.',
                    'Implement dynamic front-end features with HTML, CSS, JavaScript, AJAX, and jQuery.',
                    'Manage deployments and production updates using cPanel and FileZilla.',
                    'Use Laravel, Bootstrap, CodeIgniter, and MySQL to deliver scalable solutions.',
                ],
                'sort_order' => 1,
            ],
            [
                'company' => 'Vinspyre (Pvt.) Ltd',
                'position' => 'Full Stack Developer',
                'location' => 'Pakistan',
                'start_date' => '2024-10-01',
                'end_date' => '2025-10-31',
                'is_current' => false,
                'summary' => 'Handled front-end and back-end development across a diverse PHP technology stack to create responsive and scalable web solutions.',
                'bullets' => [
                    'Developed applications with PHP, OOP, Laravel, MySQL, JavaScript, and jQuery.',
                    'Built responsive interfaces using Bootstrap and Tailwind CSS.',
                    'Worked across both client-side and server-side application features.',
                ],
                'sort_order' => 2,
            ],
            [
                'company' => 'Hidaya Institute of Science & Technology',
                'position' => 'Web Developer Intern',
                'location' => 'Jamshoro',
                'start_date' => '2024-02-01',
                'end_date' => '2024-05-31',
                'is_current' => false,
                'summary' => 'Contributed to the development and maintenance of web applications while building practical experience with Laravel and core web technologies.',
                'bullets' => [
                    'Developed and maintained features using PHP, MySQL, HTML, CSS, and JavaScript.',
                    'Supported the functionality and performance of Laravel-based applications.',
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($experiences as $experience) {
            Experience::query()->updateOrCreate(
                ['company' => $experience['company'], 'position' => $experience['position']],
                $experience
            );
        }

        $educations = [
            ['institution' => 'University of Sindh, Jamshoro', 'degree' => 'Bachelor of Science', 'field' => 'Computer Science', 'start_year' => 2020, 'end_year' => 2023, 'description' => 'Completed a BS in Computer Science with a foundation in software engineering, databases, and web development.', 'sort_order' => 1],
            ['institution' => 'Government Boys Degree College Larkana', 'degree' => 'Intermediate', 'field' => null, 'start_year' => 2018, 'end_year' => 2019, 'description' => null, 'sort_order' => 2],
            ['institution' => 'Government Higher Secondary School Kehar', 'degree' => 'Matriculation', 'field' => null, 'start_year' => 2016, 'end_year' => 2018, 'description' => null, 'sort_order' => 3],
        ];

        foreach ($educations as $education) {
            Education::query()->updateOrCreate(['institution' => $education['institution'], 'degree' => $education['degree']], $education);
        }

        $skills = [
            ['PHP', 'Backend', 90], ['Laravel', 'Backend', 88], ['CodeIgniter', 'Backend', 78],
            ['OOP', 'Backend', 86], ['MySQL', 'Database', 86], ['JavaScript', 'Frontend', 82],
            ['jQuery', 'Frontend', 84], ['React.js', 'Frontend', 68], ['HTML', 'Frontend', 95],
            ['CSS', 'Frontend', 92], ['Bootstrap', 'Frontend', 92], ['Tailwind CSS', 'Frontend', 78],
        ];

        foreach ($skills as $index => [$name, $category, $proficiency]) {
            Skill::query()->updateOrCreate(['name' => $name], ['category' => $category, 'proficiency' => $proficiency, 'sort_order' => $index + 1, 'is_active' => true]);
        }

        $services = [
            ['Laravel Web Application Development', 'laravel-web-application-development', 'Custom, secure, and maintainable Laravel applications built around business requirements.', 'bi-boxes'],
            ['PHP Backend Development', 'php-backend-development', 'Reliable object-oriented PHP development, backend logic, authentication, and custom modules.', 'bi-code-slash'],
            ['Responsive Frontend Development', 'responsive-frontend-development', 'Mobile-first interfaces created with HTML, CSS, Bootstrap 5, JavaScript, jQuery, and AJAX.', 'bi-window-stack'],
            ['MySQL & API Integration', 'mysql-api-integration', 'Structured database work and clean integrations that keep application data accurate and useful.', 'bi-database-check'],
            ['CodeIgniter Development', 'codeigniter-development', 'Fast, practical CodeIgniter applications and improvements for existing PHP systems.', 'bi-lightning-charge'],
            ['Deployment & Maintenance', 'deployment-maintenance', 'cPanel deployment, FileZilla-based updates, issue resolution, and ongoing application maintenance.', 'bi-cloud-arrow-up'],
        ];

        foreach ($services as $index => [$title, $slug, $description, $icon]) {
            Service::query()->updateOrCreate(['slug' => $slug], ['title' => $title, 'short_description' => $description, 'icon' => $icon, 'sort_order' => $index + 1, 'is_active' => true]);
        }

        $projects = [
            [
                'title' => 'Blog Management System',
                'slug' => 'blog-management-system',
                'project_type' => Project::TYPE_FULL_DEVELOPMENT,
                'excerpt' => 'A responsive content management application with authentication, rich-text publishing, categories, tags, and real-time comments.',
                'description' => "The Blog Management System allows administrators to create, edit, organize, and publish blog posts through a clean interface. The application includes user authentication, category and tag management, a rich-text content workflow, and a comment system with AJAX-powered updates.\n\nThe interface is responsive across mobile and desktop devices, while the backend is structured to keep content administration clear and maintainable.",
                'technologies' => ['PHP', 'JavaScript', 'jQuery', 'AJAX', 'MySQL', 'Bootstrap'],
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Doctor Appointment System',
                'slug' => 'doctor-appointment-system',
                'project_type' => Project::TYPE_FULL_DEVELOPMENT,
                'excerpt' => 'A modern appointment platform that simplifies booking and management for patients and healthcare providers.',
                'description' => "The Doctor Appointment System is designed to make medical appointment booking easier for both patients and providers. It includes a responsive appointment flow, organised backend records, and a clear interface for managing booking information.\n\nThe project uses Laravel for application structure, PHP and MySQL for server-side processing, and Bootstrap 5 with JavaScript and jQuery for a responsive user experience.",
                'technologies' => ['Laravel', 'PHP', 'MySQL', 'Bootstrap 5', 'JavaScript', 'jQuery'],
                'is_featured' => true,
                'is_published' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Responsive E-Project',
                'slug' => 'responsive-eproject',
                'project_type' => Project::TYPE_MODIFICATION_ENHANCEMENT,
                'excerpt' => 'A clean and flexible Bootstrap 5 application foundation designed for expansion and future integrations.',
                'description' => "The E-Project is a modern responsive interface created with HTML, CSS, and Bootstrap 5. It uses Bootstrap's grid system and reusable components to maintain a consistent experience across screen sizes.\n\nIts modular layout offers a practical foundation for future web application features, backend connections, and custom business workflows.",
                'technologies' => ['HTML', 'CSS', 'Bootstrap 5'],
                'is_featured' => false,
                'is_published' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($projects as $project) {
            Project::query()->updateOrCreate(['slug' => $project['slug']], $project);
        }

        $posts = [
            [
                'title' => 'How Laravel Helps Build Maintainable Web Applications',
                'slug' => 'how-laravel-helps-build-maintainable-web-applications',
                'excerpt' => 'A practical look at the Laravel features that make business applications easier to structure, secure, test, and maintain.',
                'content' => "Laravel provides a clear structure for routing, database access, validation, authentication, and background jobs. That structure helps development teams keep business logic organised as an application grows.\n\nFor clients, maintainable code means future changes are easier to plan and less likely to introduce unexpected issues. Laravel also includes strong defaults for security, request validation, CSRF protection, password hashing, and database queries.\n\nA successful Laravel project still depends on clear requirements, thoughtful database design, testing, backups, and a controlled deployment process. The framework provides the foundation; careful engineering turns it into a dependable product.",
                'meta_title' => 'Laravel for Maintainable Web Applications',
                'meta_description' => 'Learn how Laravel supports secure, structured, and maintainable web application development for modern business projects.',
                'published_at' => now()->subDays(10),
                'is_published' => true,
            ],
            [
                'title' => 'Building Responsive Interfaces with Bootstrap 5',
                'slug' => 'building-responsive-interfaces-with-bootstrap-5',
                'excerpt' => 'Key practices for creating Bootstrap interfaces that remain clear, fast, and usable on phones, tablets, and desktops.',
                'content' => "Bootstrap 5 offers a practical grid, responsive utilities, accessible components, and consistent spacing rules. Used carefully, it can speed up development without making every website look the same.\n\nThe best approach starts mobile-first. Content hierarchy should remain clear on small screens, buttons should be easy to tap, and navigation should work with both keyboard and touch input. Custom CSS can then establish a unique visual system while Bootstrap handles reliable layout behaviour.\n\nPerformance also matters. Production projects should load only the assets they need, optimise images, and test layouts at real device widths before launch.",
                'meta_title' => 'Responsive Interfaces with Bootstrap 5',
                'meta_description' => 'Explore practical Bootstrap 5 techniques for fast, accessible, and responsive web interfaces across modern devices.',
                'published_at' => now()->subDays(6),
                'is_published' => true,
            ],
            [
                'title' => 'A Practical Checklist for PHP Website Deployment',
                'slug' => 'practical-checklist-for-php-website-deployment',
                'excerpt' => 'Important checks for safer PHP deployments using cPanel, FileZilla, environment settings, backups, and post-launch testing.',
                'content' => "A safe deployment begins before files are uploaded. Create a complete backup, confirm the PHP version, review environment variables, and test database migrations on staging.\n\nWhen using cPanel or FileZilla, protect environment files and keep the application document root pointed at the public directory. Production debugging should be disabled, file permissions should be limited, and the application key must remain private.\n\nAfter deployment, clear and rebuild application caches, test important forms and login flows, review logs, and confirm HTTPS is working. A documented rollback plan makes it possible to restore the previous version quickly if an unexpected issue appears.",
                'meta_title' => 'PHP Website Deployment Checklist',
                'meta_description' => 'Use this practical PHP deployment checklist for safer cPanel updates, backups, configuration, testing, and rollback planning.',
                'published_at' => now()->subDays(2),
                'is_published' => true,
            ],
        ];

        foreach ($posts as $post) {
            Post::query()->updateOrCreate(['slug' => $post['slug']], $post);
        }
    }
}
