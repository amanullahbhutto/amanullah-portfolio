<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\DateOfBirth;
use App\Models\Experience;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\User;
use App\Models\VisitorLog;
use App\Mail\ContactMessageReceived;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_homepage_loads_seeded_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Amanullah')
            ->assertSee('Blog Management System');
    }

    public function test_public_mobile_nav_closes_when_clicking_outside(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="mainNav"', false)
            ->assertSee('data-bs-target="#mainNav"', false);

        $script = File::get(public_path('assets/js/app.js'));

        $this->assertStringContainsString("const mainNav = document.getElementById('mainNav');", $script);
        $this->assertStringContainsString("navbarToggler?.contains(event.target)", $script);
        $this->assertStringContainsString('hideMainNav();', $script);
    }

    public function test_contact_form_creates_an_unread_message(): void
    {
        Mail::fake();

        $response = $this->post('/contact', [
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'phone' => '03000000000',
            'subject' => 'Laravel project',
            'message' => 'I would like to discuss a Laravel web application.',
            'website' => '',
        ]);

        $response
            ->assertSessionHas('success', 'Thank you for submitting the form. Amanullah will contact you as soon as possible.')
            ->assertSessionHas('flash_duration', 4000)
            ->assertSessionHas('flash_variant', 'contact-success-popup');

        $this->assertDatabaseHas('contact_messages', ['email' => 'client@example.com', 'read_at' => null]);

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $this->actingAs($admin)
            ->get('/admin/messages?q=client@example.com')
            ->assertOk()
            ->assertSee('client@example.com')
            ->assertSee('Laravel project');

        Mail::assertSent(ContactMessageReceived::class, function (ContactMessageReceived $mail): bool {
            $html = $mail->render();

            return $mail->hasTo('aman.ullah.csc@gmail.com')
                && $mail->contactMessage->email === 'client@example.com'
                && $mail->contactMessage->subject === 'Laravel project'
                && str_contains($html, 'AMANULLAH')
                && str_contains($html, 'Open in Admin Inbox')
                && str_contains($html, '#ff6b2c');
        });
    }

    public function test_public_phone_numbers_use_whatsapp_and_secondary_number_is_hidden(): void
    {
        $whatsappUrl = 'https://wa.me/923183588065';

        $this->get('/')
            ->assertOk()
            ->assertSee($whatsappUrl, false)
            ->assertSee('bi-whatsapp', false)
            ->assertSee('03183588065')
            ->assertDontSee('03482042872');

        $this->get('/contact')
            ->assertOk()
            ->assertSee($whatsappUrl, false)
            ->assertSee('03183588065')
            ->assertDontSee('03482042872')
            ->assertDontSee('tel:03183588065', false);
    }

    public function test_public_email_links_open_gmail_compose(): void
    {
        $gmailUrl = 'https://mail.google.com/mail/?view=cm&amp;fs=1&amp;to=aman.ullah.csc%40gmail.com';

        $this->get('/')
            ->assertOk()
            ->assertSee($gmailUrl, false)
            ->assertSee('gmail-link', false)
            ->assertSee('aman.ullah.csc@gmail.com')
            ->assertDontSee('mailto:aman.ullah.csc@gmail.com', false);

        $this->get('/contact')
            ->assertOk()
            ->assertSee($gmailUrl, false)
            ->assertSee('aman.ullah.csc@gmail.com')
            ->assertDontSee('mailto:aman.ullah.csc@gmail.com', false);

        $this->get('/about')
            ->assertOk()
            ->assertSee($gmailUrl, false)
            ->assertSee('aman.ullah.csc@gmail.com');
    }

    public function test_contact_success_renders_four_second_popup(): void
    {
        Mail::fake();

        $this->followingRedirects()
            ->post('/contact', [
                'name' => 'Popup Client',
                'email' => 'popup-client@example.com',
                'phone' => '03000000000',
                'subject' => 'Popup design',
                'message' => 'Please confirm the popup design after contact form submit.',
                'website' => '',
            ])
            ->assertOk()
            ->assertSee('Thank you for submitting the form. Amanullah will contact you as soon as possible.')
            ->assertSee('Message received')
            ->assertSee('contact-success-popup', false)
            ->assertSee('data-flash-duration="4000"', false)
            ->assertSee('data-flash-close', false);
    }

    public function test_admin_can_open_dashboard_and_read_message(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $message = ContactMessage::query()->create([
            'name' => 'Client',
            'email' => 'client@example.com',
            'subject' => 'New work',
            'message' => 'Please contact me about a project.',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Date of Birth');
        $this->actingAs($admin)
            ->get('/admin/messages/'.$message->id)
            ->assertOk()
            ->assertSee('https://mail.google.com/mail/?view=cm&amp;fs=1&amp;to=client%40example.com', false)
            ->assertSee('su=Re%3A%20New%20work', false)
            ->assertSee('Reply by email')
            ->assertDontSee('mailto:client@example.com', false);
        $this->assertNotNull($message->fresh()->read_at);
    }

    public function test_registration_assigns_default_user_role_without_admin_access(): void
    {
        $this->post('/register', [
            'name' => 'Default User',
            'email' => 'default-user@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])
            ->assertRedirect('/')
            ->assertSessionHas('success');

        $user = User::query()->where('email', 'default-user@example.com')->firstOrFail();

        $this->assertTrue(Role::query()->whereIn('name', ['user', 'User'])->exists());
        $this->assertTrue($user->hasRole('user') || $user->hasRole('User'));
        $this->assertFalse($user->hasRole('editor'));
        $this->assertFalse($user->can('view dashboard'));

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(403);
    }

    public function test_public_page_visits_are_tracked_and_visible_in_admin(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $project = Project::query()->published()->firstOrFail();
        $projectPath = '/projects/'.$project->slug;

        $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36')
            ->withHeader('Referer', 'https://example.com')
            ->get($projectPath)
            ->assertOk();

        $this->get($projectPath)->assertOk();
        $this->get('/about')->assertOk();
        $this->get('/about')->assertOk();

        $this->assertDatabaseHas('visitor_logs', [
            'path' => $projectPath,
            'route_name' => 'projects.show',
            'project_id' => $project->id,
            'visit_key' => 'project:'.$project->id,
            'browser' => 'Chrome',
            'platform' => 'Windows',
            'device_type' => 'Desktop',
        ]);
        $this->assertDatabaseHas('visitor_logs', ['path' => '/about']);
        $this->assertSame(2, VisitorLog::query()->count());

        $this->actingAs($admin)
            ->get('/admin/visitors?q='.$project->slug)
            ->assertOk()
            ->assertSee('Visitor Analytics')
            ->assertSee($projectPath)
            ->assertSee('Guest visitor')
            ->assertSee('Chrome / Windows');

        $this->actingAs($admin)
            ->get('/admin/content/projects')
            ->assertOk()
            ->assertSee('Total Views')
            ->assertSee('1 views');

        $this->assertSame(2, VisitorLog::query()->count());
    }

    public function test_same_visitor_cookie_does_not_create_duplicate_visit_records(): void
    {
        $project = Project::query()->published()->firstOrFail();
        $projectPath = '/projects/'.$project->slug;
        $visitorCookie = 'repeat-visitor-1234567890';

        $this->withCookie('portfolio_visitor_id', $visitorCookie)
            ->get($projectPath)
            ->assertOk();
        $this->flushSession();
        $this->withCookie('portfolio_visitor_id', $visitorCookie)
            ->get($projectPath)
            ->assertOk();

        $this->assertSame(1, VisitorLog::query()
            ->where('visitor_id', $visitorCookie)
            ->where('visit_key', 'project:'.$project->id)
            ->count());

        $this->withCookie('portfolio_visitor_id', $visitorCookie)
            ->get('/about')
            ->assertOk();
        $this->flushSession();
        $this->withCookie('portfolio_visitor_id', $visitorCookie)
            ->get('/about')
            ->assertOk();

        $this->assertSame(2, VisitorLog::query()->where('visitor_id', $visitorCookie)->count());
    }

    public function test_admin_content_listing_can_be_searched(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/content/projects?q=Doctor')
            ->assertOk()
            ->assertSee('Doctor Appointment System')
            ->assertDontSee('Blog Management System');
    }

    public function test_admin_messages_listing_can_be_searched(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        ContactMessage::query()->create([
            'name' => 'Search Client',
            'email' => 'search-client@example.com',
            'subject' => 'Website retainer',
            'message' => 'Please send monthly support details.',
        ]);
        ContactMessage::query()->create([
            'name' => 'Other Client',
            'email' => 'other-client@example.com',
            'subject' => 'Mobile app',
            'message' => 'I need a small app.',
        ]);

        $this->actingAs($admin)
            ->get('/admin/messages?q=retainer')
            ->assertOk()
            ->assertSee('search-client@example.com')
            ->assertDontSee('other-client@example.com');
    }

    public function test_admin_users_listing_can_be_searched(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $searchableUser = User::factory()->create([
            'name' => 'Searchable Editor',
            'email' => 'searchable-editor@example.com',
        ]);
        $searchableUser->assignRole('editor');
        $otherUser = User::factory()->create([
            'name' => 'Quiet Editor',
            'email' => 'quiet-editor@example.com',
        ]);
        $otherUser->assignRole('editor');

        $this->actingAs($admin)
            ->get('/admin/users?q=searchable')
            ->assertOk()
            ->assertSee('searchable-editor@example.com')
            ->assertDontSee('quiet-editor@example.com');
    }

    public function test_admin_can_create_show_update_and_delete_user(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'CRUD Editor',
                'email' => 'crud-editor@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'role' => 'editor',
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $user = User::query()->where('email', 'crud-editor@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/users/'.$user->id)
            ->assertOk()
            ->assertSee('CRUD Editor')
            ->assertSee('Role permissions');

        $this->actingAs($admin)
            ->put('/admin/users/'.$user->id, [
                'name' => 'Updated CRUD Editor',
                'email' => 'crud-editor@example.com',
                'role' => 'editor',
            ])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->delete('/admin/users/'.$user->id)
            ->assertRedirect('/admin/users')
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['email' => 'crud-editor@example.com']);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $editorRole = Role::query()->where('name', 'editor')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('Spatie roles')
            ->assertSee('Add role');

        $this->actingAs($admin)
            ->get('/admin/roles/'.$editorRole->id.'/edit')
            ->assertOk()
            ->assertSee('Editor role')
            ->assertSee('view message');

        $this->actingAs($admin)
            ->put('/admin/roles/'.$editorRole->id, [
                'name' => 'editor',
                'permissions' => ['view dashboard', 'view profile'],
            ])
            ->assertRedirect('/admin/roles')
            ->assertSessionHas('success');

        $editorRole->refresh();

        $this->assertTrue($editorRole->hasPermissionTo('view dashboard'));
        $this->assertTrue($editorRole->hasPermissionTo('view profile'));
        $this->assertFalse($editorRole->hasPermissionTo('view message'));
    }

    public function test_admin_can_create_permission_and_role_with_crud_permissions(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/permissions', ['name' => 'view report'])
            ->assertRedirect('/admin/permissions')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('permissions', ['name' => 'view report', 'guard_name' => 'web']);

        $this->actingAs($admin)
            ->post('/admin/roles', [
                'name' => 'report manager',
                'permissions' => ['view dashboard', 'view report'],
            ])
            ->assertRedirect('/admin/roles')
            ->assertSessionHas('success');

        $role = Role::query()->where('name', 'report manager')->firstOrFail();

        $this->assertTrue($role->hasPermissionTo('view report'));

        $this->actingAs($admin)
            ->get('/admin/permissions/'.Permission::query()->where('name', 'view report')->value('id'))
            ->assertOk()
            ->assertSee('view report');
    }

    public function test_flash_messages_render_as_global_toasts(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->withSession(['success' => 'Permission created successfully.'])
            ->get('/admin/permissions')
            ->assertOk()
            ->assertSee('flash-toast-viewport', false)
            ->assertSee('data-flash-toast', false)
            ->assertSee('Permission created successfully.');
    }

    public function test_date_of_births_are_sorted_by_next_birthday_and_show_countdown(): void
    {
        Carbon::setTestNow('2026-08-15 09:00:00');

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        DateOfBirth::query()->delete();

        DateOfBirth::query()->create([
            'name' => 'Passed Birthday',
            'father_name' => 'Late Father',
            'start_date' => '2000-08-10',
        ]);
        DateOfBirth::query()->create([
            'name' => 'Near Birthday',
            'father_name' => 'Near Father',
            'start_date' => '2000-08-20',
        ]);
        DateOfBirth::query()->create([
            'name' => 'Later Birthday',
            'father_name' => 'Later Father',
            'start_date' => '2000-09-15',
        ]);

        $this->actingAs($admin)
            ->get('/admin/date-of-births')
            ->assertOk()
            ->assertSee('Father Name')
            ->assertSee('All')
            ->assertSee('Filter by father name', false)
            ->assertSee('Next Birthday')
            ->assertSee('0 Months, 5 Days')
            ->assertSee('Next: Aug 20, 2026')
            ->assertSee('data-dob-crud', false)
            ->assertSee('data-dob-open', false)
            ->assertSee('DD/MM/YYYY example 25/3/2008')
            ->assertSeeInOrder([
                'Near Birthday',
                'Later Birthday',
                'Passed Birthday',
            ]);

        Carbon::setTestNow();
    }

    public function test_date_of_births_can_be_filtered_by_father_name(): void
    {
        Carbon::setTestNow('2026-08-15 09:00:00');

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        DateOfBirth::query()->delete();

        DateOfBirth::query()->create([
            'name' => 'Filtered Child',
            'father_name' => 'Filter Father',
            'start_date' => '2000-08-20',
        ]);
        DateOfBirth::query()->create([
            'name' => 'Other Child',
            'father_name' => 'Other Father',
            'start_date' => '2000-08-21',
        ]);

        $this->actingAs($admin)
            ->get('/admin/date-of-births?father_name=Filter%20Father')
            ->assertOk()
            ->assertSee('Father: Filter Father')
            ->assertSee('Filtered Child')
            ->assertSee('Filter Father')
            ->assertDontSee('Other Child');

        Carbon::setTestNow();
    }

    public function test_date_of_births_per_page_selector_controls_list_size(): void
    {
        Carbon::setTestNow('2026-08-15 09:00:00');

        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        DateOfBirth::query()->delete();

        foreach (range(1, 60) as $number) {
            DateOfBirth::query()->create([
                'name' => 'Child '.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
                'father_name' => 'Page Father',
                'start_date' => '2000-08-20',
            ]);
        }

        $this->actingAs($admin)
            ->get('/admin/date-of-births')
            ->assertOk()
            ->assertSee('Records per page', false)
            ->assertSee('10 / page')
            ->assertSee('20 / page')
            ->assertSee('50 / page')
            ->assertSee('100 / page')
            ->assertSee('Showing <strong>1-50</strong> of <strong>60</strong>', false)
            ->assertSee('Child 050')
            ->assertDontSee('Child 051');

        $this->actingAs($admin)
            ->get('/admin/date-of-births?per_page=10')
            ->assertOk()
            ->assertSee('Showing <strong>1-10</strong> of <strong>60</strong>', false)
            ->assertSee('Child 010')
            ->assertDontSee('Child 011');

        $this->actingAs($admin)
            ->get('/admin/date-of-births?per_page=100')
            ->assertOk()
            ->assertSee('Showing <strong>1-60</strong> of <strong>60</strong>', false)
            ->assertSee('Child 051')
            ->assertSee('Child 060');

        Carbon::setTestNow();
    }

    public function test_date_of_birth_ajax_crud_accepts_slash_date_format(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->postJson('/admin/date-of-births', [
                'name' => 'Ajax Birthday',
                'father_name' => 'Ajax Father',
                'start_date' => '25/3/2008',
            ])
            ->assertCreated()
            ->assertJsonPath('record.name', 'Ajax Birthday')
            ->assertJsonPath('record.start_date', '25/3/2008');

        $record = DateOfBirth::query()->where('name', 'Ajax Birthday')->firstOrFail();

        $this->assertDatabaseHas('date_of_births', [
            'id' => $record->id,
            'father_name' => 'Ajax Father',
        ]);
        $this->assertSame('2008-03-25', $record->start_date->toDateString());

        $this->actingAs($admin)
            ->putJson('/admin/date-of-births/'.$record->id, [
                'name' => 'Updated Ajax Birthday',
                'father_name' => 'Updated Father',
                'start_date' => '1/4/2009',
            ])
            ->assertOk()
            ->assertJsonPath('record.name', 'Updated Ajax Birthday')
            ->assertJsonPath('record.start_date', '1/4/2009');

        $this->assertDatabaseHas('date_of_births', [
            'id' => $record->id,
            'name' => 'Updated Ajax Birthday',
            'father_name' => 'Updated Father',
        ]);
        $this->assertSame('2009-04-01', $record->fresh()->start_date->toDateString());

        $this->actingAs($admin)
            ->deleteJson('/admin/date-of-births/'.$record->id)
            ->assertOk()
            ->assertJsonPath('message', 'Date of birth record deleted successfully.');

        $this->assertDatabaseMissing('date_of_births', [
            'id' => $record->id,
        ]);
    }

    public function test_homepage_experience_stat_can_use_manual_or_experience_dates(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $profile = Profile::query()->firstOrFail();
        Experience::query()->delete();
        Experience::query()->create([
            'company' => 'First Company',
            'position' => 'Developer',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
            'summary' => 'One year role.',
        ]);
        Experience::query()->create([
            'company' => 'Second Company',
            'position' => 'Developer',
            'start_date' => '2021-02-01',
            'end_date' => '2021-08-01',
            'summary' => 'Six month role.',
        ]);

        $profile->update(['years_experience' => 9, 'use_experience_dates' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('9y')
            ->assertDontSee('9y 0m');

        $this->actingAs($admin)
            ->get('/admin/content/experiences')
            ->assertOk()
            ->assertSee('Experience Off')
            ->assertSee('Homepage manual years show hoga')
            ->assertSee('1y 0m')
            ->assertSee('0y 6m');

        $this->actingAs($admin)
            ->from('/admin/content/experiences')
            ->patch('/admin/profile/experience-source', [
                'use_experience_dates' => '1',
            ])
            ->assertRedirect('/admin/content/experiences')
            ->assertSessionHas('success');

        $this->assertTrue($profile->fresh()->use_experience_dates);

        $this->actingAs($admin)
            ->get('/admin/content/experiences')
            ->assertOk()
            ->assertSee('Experience On')
            ->assertSee('Homepage dates se total show hoga');

        $this->actingAs($admin)
            ->get('/admin/profile')
            ->assertOk()
            ->assertSee('Years of experience')
            ->assertDontSee('Use experience records on homepage');

        $this->get('/')
            ->assertOk()
            ->assertSee('1y 6m');

        Experience::query()->delete();
        Experience::query()->create([
            'company' => 'Exact Year Company',
            'position' => 'Developer',
            'start_date' => '2020-01-01',
            'end_date' => '2021-01-01',
            'summary' => 'One exact year role.',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('1y 0m');
    }

    public function test_profile_uploads_are_saved_to_public_assets_and_render_on_homepage(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $profile = Profile::query()->firstOrFail();

        $this->actingAs($admin)
            ->put('/admin/profile', [
                'full_name' => $profile->full_name,
                'headline' => $profile->headline,
                'short_bio' => $profile->short_bio,
                'long_bio' => $profile->long_bio,
                'email' => $profile->email,
                'availability' => $profile->availability,
                'years_experience' => $profile->years_experience,
                'project_count' => $profile->project_count,
                'happy_clients' => $profile->happy_clients,
                'languages' => implode(', ', $profile->languages ?? []),
                'profile_image' => UploadedFile::fake()->image('profile-photo.jpg', 600, 700),
                'cv_file' => UploadedFile::fake()->create('amanullah-cv.pdf', 64, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $profile->refresh();

        $this->assertStringStartsWith('assets/images/profile_image-', $profile->profile_image);
        $this->assertStringStartsWith('assets/images/cv_file-', $profile->cv_file);
        $this->assertTrue(File::isFile(public_path($profile->profile_image)));
        $this->assertTrue(File::isFile(public_path($profile->cv_file)));

        $this->get('/')
            ->assertOk()
            ->assertSee($profile->profile_image)
            ->assertSee($profile->cv_file);

        $this->actingAs($admin)
            ->get('/admin/profile')
            ->assertOk()
            ->assertSee($profile->profile_image)
            ->assertSee('Saved in public/assets/images');

        File::delete([
            public_path($profile->profile_image),
            public_path($profile->cv_file),
        ]);
    }

    public function test_project_images_upload_to_public_assets_and_render_as_a_gallery(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/content/projects', [
                'title' => 'Image Preview Project',
                'slug' => 'image-preview-project',
                'project_type' => 'full_development',
                'excerpt' => 'A project created to verify image upload previews.',
                'description' => 'Project image should be saved and visible on edit.',
                'technologies' => 'Laravel, PHP',
                'project_url' => 'https://example.com',
                'github_url' => 'https://github.com/example/project',
                'project_images' => [
                    UploadedFile::fake()->image('project-preview.png', 800, 500),
                    UploadedFile::fake()->image('project-dashboard.webp', 900, 520),
                ],
                'is_published' => '1',
            ])
            ->assertRedirect('/admin/content/projects')
            ->assertSessionHas('success');

        $project = Project::query()->where('slug', 'image-preview-project')->firstOrFail();
        $projectImages = $project->images;

        $this->assertStringStartsWith('assets/images/projects/project-', $project->image);
        $this->assertCount(2, $projectImages);
        $this->assertTrue(File::isFile(public_path($project->image)));
        $this->assertTrue(File::isFile(public_path($projectImages[1])));

        $this->actingAs($admin)
            ->get('/admin/content/projects/'.$project->id.'/edit')
            ->assertOk()
            ->assertSee($project->image_url, false)
            ->assertSee($project->image_urls[1], false)
            ->assertSee('name="project_images[]"', false)
            ->assertSee('name="delete_images[]"', false);

        $this->actingAs($admin)
            ->get('/admin/content/projects')
            ->assertOk()
            ->assertSee('content-thumb has-image', false)
            ->assertSee($project->image_url, false);

        $this->get('/projects/image-preview-project')
            ->assertOk()
            ->assertSee('project-detail-gallery', false)
            ->assertSee('project-gallery-grid', false)
            ->assertDontSee('project-gallery-main', false)
            ->assertSee($project->image_url, false)
            ->assertSee($project->image_urls[1], false);

        $deletedImage = $projectImages[0];

        $this->actingAs($admin)
            ->put('/admin/content/projects/'.$project->id, [
                'title' => $project->title,
                'slug' => $project->slug,
                'project_type' => 'full_development',
                'excerpt' => $project->excerpt,
                'description' => $project->description,
                'technologies' => implode(', ', $project->technologies ?? []),
                'project_url' => $project->project_url,
                'github_url' => $project->github_url,
                'delete_images' => [$deletedImage],
                'is_published' => '1',
            ])
            ->assertRedirect('/admin/content/projects')
            ->assertSessionHas('success');

        $project->refresh();

        $this->assertFalse(File::isFile(public_path($deletedImage)));
        $this->assertCount(1, $project->images);
        $this->assertSame($projectImages[1], $project->image);

        File::delete(public_path($projectImages[1]));
    }

    public function test_project_type_crud_and_public_badges_support_full_development_and_enhancement(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        // 1. Validation error on invalid project_type
        $this->actingAs($admin)
            ->from('/admin/content/projects/create')
            ->post('/admin/content/projects', [
                'title' => 'Invalid Type Project',
                'slug' => 'invalid-type-project',
                'project_type' => 'some_custom_unsupported_type',
                'excerpt' => 'An excerpt text.',
                'description' => 'A detailed description.',
            ])
            ->assertRedirect('/admin/content/projects/create')
            ->assertSessionHasErrors('project_type');

        // 2. Create Modification / Enhancement project
        $this->actingAs($admin)
            ->post('/admin/content/projects', [
                'title' => 'Legacy System Refactor',
                'slug' => 'legacy-system-refactor',
                'project_type' => 'modification_enhancement',
                'excerpt' => 'Enhancing and modifying an existing web codebase.',
                'description' => 'Worked on existing project with bug fixes and feature additions.',
                'technologies' => 'Laravel, MySQL, Vue',
                'is_published' => '1',
            ])
            ->assertRedirect('/admin/content/projects')
            ->assertSessionHas('success');

        $project = Project::query()->where('slug', 'legacy-system-refactor')->firstOrFail();
        $this->assertSame('modification_enhancement', $project->project_type);
        $this->assertSame('Modification / Enhancement', $project->project_type_label);
        $this->assertSame('Pehle se bane project par kaam kiya', $project->project_type_description);

        // 3. Admin edit screen displays the type options and preselection
        $this->actingAs($admin)
            ->get('/admin/content/projects/'.$project->id.'/edit')
            ->assertOk()
            ->assertSee('value="modification_enhancement"', false)
            ->assertSee('value="full_development"', false)
            ->assertSee('Pehle se bane project par kaam kiya')
            ->assertSee('Start se khud banaya');

        // 4. Admin index table shows the Type column badge and description
        $this->actingAs($admin)
            ->get('/admin/content/projects?q=legacy-system-refactor')
            ->assertOk()
            ->assertSee('Modification / Enhancement')
            ->assertSee('Pehle se bane project par kaam kiya');

        // 5. Public project listing and detail pages show badge & scope
        $this->get('/projects')
            ->assertOk()
            ->assertSee('Legacy System Refactor')
            ->assertSee('Modification / Enhancement');

        $this->get('/projects/legacy-system-refactor')
            ->assertOk()
            ->assertSee('Legacy System Refactor')
            ->assertSee('Modification / Enhancement')
            ->assertSee('Pehle se bane project par kaam kiya');

        // 6. Update to Full Development
        $this->actingAs($admin)
            ->put('/admin/content/projects/'.$project->id, [
                'title' => 'Legacy System Refactor Rebuilt',
                'slug' => 'legacy-system-refactor',
                'project_type' => 'full_development',
                'excerpt' => 'Now rewritten completely from scratch.',
                'description' => 'Rebuilt from the ground up.',
                'technologies' => 'Laravel, React',
                'is_published' => '1',
            ])
            ->assertRedirect('/admin/content/projects')
            ->assertSessionHas('success');

        $project->refresh();
        $this->assertSame('full_development', $project->project_type);
        $this->assertSame('Full Development', $project->project_type_label);
        $this->assertSame('Start se khud banaya', $project->project_type_description);

        $this->get('/projects/legacy-system-refactor')
            ->assertOk()
            ->assertSee('Full Development')
            ->assertSee('Start se khud banaya');
    }

    public function test_blog_image_upload_is_saved_to_public_assets_and_visible_in_admin_forms(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/content/posts', [
                'title' => 'Blog Image Preview',
                'slug' => 'blog-image-preview',
                'excerpt' => 'A post created to verify blog image uploads.',
                'content' => 'Blog image should be saved under public assets and visible on edit.',
                'image' => UploadedFile::fake()->image('blog-preview.jpg', 1000, 600),
                'meta_title' => 'Blog Image Preview',
                'meta_description' => 'Testing blog image upload path.',
                'published_at' => now()->format('Y-m-d H:i:s'),
                'is_published' => '1',
            ])
            ->assertRedirect('/admin/content/posts')
            ->assertSessionHas('success');

        $post = Post::query()->where('slug', 'blog-image-preview')->firstOrFail();

        $this->assertStringStartsWith('assets/images/blogs/blog-', $post->image);
        $this->assertTrue(File::isFile(public_path($post->image)));

        $this->actingAs($admin)
            ->get('/admin/content/posts/'.$post->id.'/edit')
            ->assertOk()
            ->assertSee($post->image_url, false)
            ->assertSee('Current image is saved in public/assets')
            ->assertSee('data-image-input="#contentImagePreview"', false);

        $this->actingAs($admin)
            ->get('/admin/content/posts')
            ->assertOk()
            ->assertSee('content-thumb has-image', false)
            ->assertSee($post->image_url, false);

        File::delete(public_path($post->image));
    }

    public function test_admin_maintenance_page_and_artisan_commands_execution(): void
    {
        $admin = User::query()->where('email', 'admin@gmail.com')->firstOrFail();
        $user = User::query()->where('email', '!=', 'admin@gmail.com')->first();
        if (! $user) {
            $user = User::factory()->create();
        }

        // 1. Unprivileged user gets 403 Access Denied
        $this->actingAs($user)
            ->get('/admin/maintenance')
            ->assertStatus(403);

        // 2. Admin can view maintenance dashboard
        $this->actingAs($admin)
            ->get('/admin/maintenance')
            ->assertOk()
            ->assertSee('System Diagnostics')
            ->assertSee('php artisan migrate --force')
            ->assertSee('php artisan optimize:clear')
            ->assertSee('php artisan cache:clear')
            ->assertSee('php artisan config:clear')
            ->assertSee('php artisan route:clear')
            ->assertSee('php artisan view:clear');

        // 3. Admin can execute optimize_clear
        $this->actingAs($admin)
            ->from('/admin/maintenance')
            ->post('/admin/maintenance/run', [
                'command' => 'optimize_clear',
            ])
            ->assertRedirect('/admin/maintenance')
            ->assertSessionHas('success')
            ->assertSessionHas('artisan_output');

        // 4. Admin can execute view_clear
        $this->actingAs($admin)
            ->from('/admin/maintenance')
            ->post('/admin/maintenance/run', [
                'command' => 'view_clear',
            ])
            ->assertRedirect('/admin/maintenance')
            ->assertSessionHas('success')
            ->assertSessionHas('artisan_output');

        // 5. Admin can execute migrate
        $this->actingAs($admin)
            ->from('/admin/maintenance')
            ->post('/admin/maintenance/run', [
                'command' => 'migrate',
            ])
            ->assertRedirect('/admin/maintenance')
            ->assertSessionHas('success');
    }
}
