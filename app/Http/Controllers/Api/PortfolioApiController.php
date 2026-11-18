<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Service;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PortfolioApiController extends Controller
{
    /**
     * Get complete portfolio bootstrap data for React SPA
     */
    public function portfolio(): JsonResponse
    {
        $profile = Profile::query()->first();
        $experiences = Experience::query()->orderByDesc('is_current')->orderByDesc('start_date')->get();
        $experienceStat = $profile?->use_experience_dates
            ? Experience::formattedTotalDuration($experiences)
            : ($profile?->years_experience ?? 2).'y';

        $profileData = null;
        if ($profile) {
            $profileData = array_merge($profile->toArray(), [
                'profile_image_url' => $profile->profile_image_url,
                'cv_file_url' => $profile->cv_file_url,
                'whatsapp_url' => $profile->whatsapp_url,
                'gmail_compose_url' => $profile->gmail_compose_url,
            ]);
        }

        $formattedExperiences = $experiences->map(function ($exp) {
            return [
                'id' => $exp->id,
                'company' => $exp->company,
                'position' => $exp->position,
                'location' => $exp->location,
                'start_date' => $exp->start_date ? $exp->start_date->format('M Y') : null,
                'end_date' => $exp->end_date ? $exp->end_date->format('M Y') : null,
                'is_current' => (bool) $exp->is_current,
                'date_range' => ($exp->start_date ? $exp->start_date->format('M Y') : '') . ' - ' . ($exp->is_current ? 'Present' : ($exp->end_date ? $exp->end_date->format('M Y') : '')),
                'summary' => $exp->summary,
                'bullets' => $exp->bullets ?? [],
            ];
        });

        $projects = Project::query()->published()->latest()->limit(6)->get()->map(function ($p) {
            return $this->formatProject($p);
        });

        $posts = Post::query()->published()->latest('published_at')->limit(3)->get()->map(function ($post) {
            return $this->formatPost($post);
        });

        return response()->json([
            'profile' => $profileData,
            'experience_stat' => $experienceStat,
            'services' => Service::query()->active()->orderBy('sort_order')->get(),
            'projects' => $projects,
            'posts' => $posts,
            'experiences' => $formattedExperiences,
            'educations' => Education::query()->orderByDesc('end_year')->get(),
            'skills' => Skill::query()->active()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Get active services
     */
    public function services(): JsonResponse
    {
        $services = Service::query()->active()->orderBy('sort_order')->get();

        return response()->json([
            'services' => $services,
        ]);
    }

    /**
     * Get published projects (with optional category / tag filter)
     */
    public function projects(Request $request): JsonResponse
    {
        $query = Project::query()->published()->latest();

        if ($request->filled('type')) {
            $query->where('project_type', $request->query('type'));
        }

        $projects = $query->get()->map(fn ($p) => $this->formatProject($p));

        return response()->json([
            'projects' => $projects,
        ]);
    }

    /**
     * Get single project details by slug
     */
    public function project(string $slug): JsonResponse
    {
        $project = Project::query()->where('slug', $slug)->first();

        if (! $project || ! $project->is_published) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        // Previous and Next projects
        $prev = Project::query()->published()->where('id', '<', $project->id)->orderByDesc('id')->first();
        $next = Project::query()->published()->where('id', '>', $project->id)->orderBy('id')->first();

        return response()->json([
            'project' => $this->formatProject($project, true),
            'prev' => $prev ? ['slug' => $prev->slug, 'title' => $prev->title] : null,
            'next' => $next ? ['slug' => $next->slug, 'title' => $next->title] : null,
        ]);
    }

    /**
     * Get published blog posts
     */
    public function posts(): JsonResponse
    {
        $posts = Post::query()->published()->latest('published_at')->get()->map(fn ($p) => $this->formatPost($p));

        return response()->json([
            'posts' => $posts,
        ]);
    }

    /**
     * Get single post by slug
     */
    public function post(string $slug): JsonResponse
    {
        $post = Post::query()->where('slug', $slug)->first();

        if (! $post || ! $post->is_published || ($post->published_at && $post->published_at->isFuture())) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        return response()->json([
            'post' => $this->formatPost($post, true),
        ]);
    }

    /**
     * Handle contact form submission
     */
    public function submitContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        if (!empty($validated['website'])) {
            $validated['message'] .= "\n\nWebsite: " . strip_tags((string) $validated['website']);
        }
        unset($validated['website']);
        $validated['ip_address'] = $request->ip();

        $message = ContactMessage::query()->create($validated);

        $recipient = config('portfolio.contact_notification_email') ?: 'aman.ullah.csc@gmail.com';
        $sent = false;
        $mailError = null;

        if (config('mail.default') === 'mail') {
            try {
                $sent = $this->sendViaPhpMail($recipient, $message);
                if (! $sent) {
                    $mailError = 'PHP mail() function returned false. Please check server sendmail configuration.';
                }
            } catch (Throwable $e) {
                report($e);
                $mailError = $e->getMessage();
            }
        } else {
            try {
                Mail::to($recipient)->send(new ContactMessageReceived($message));
                $sent = true;
            } catch (Throwable $exception) {
                report($exception);
                $mailError = $exception->getMessage();
            }

            if (! $sent) {
                try {
                    $sent = $this->sendViaPhpMail($recipient, $message);
                } catch (Throwable $fallbackException) {
                    report($fallbackException);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for submitting the form. Amanullah will contact you as soon as possible.',
            'mail_sent' => $sent,
            'mail_note' => $mailError,
        ]);
    }

    protected function formatProject(Project $project, bool $full = false): array
    {
        $data = [
            'id' => $project->id,
            'title' => $project->title,
            'slug' => $project->slug,
            'excerpt' => $project->excerpt,
            'project_type' => $project->project_type ?? 'full_development',
            'project_type_label' => $project->project_type_label,
            'project_type_badge_class' => $project->project_type_badge_class,
            'project_type_description' => $project->project_type_description,
            'is_featured' => (bool) $project->is_featured,
            'image_url' => $project->image_url,
            'image_urls' => $project->image_urls,
            'technologies' => $project->technologies ?? [],
            'project_url' => $project->project_url,
            'github_url' => $project->github_url ?? null,
            'created_at' => $project->created_at ? $project->created_at->format('M d, Y') : null,
        ];

        if ($full) {
            $data['description'] = $project->description;
            $data['client'] = $project->client;
            $data['role'] = $project->role;
            $data['started_at'] = $project->started_at ? $project->started_at->format('M Y') : null;
            $data['completed_at'] = $project->completed_at ? $project->completed_at->format('M Y') : null;
        }

        return $data;
    }

    protected function formatPost(Post $post, bool $full = false): array
    {
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'image_url' => $post->image_url,
            'published_at' => ($post->published_at ?? $post->created_at)->format('M d, Y'),
            'published_at_full' => ($post->published_at ?? $post->created_at)->format('F d, Y'),
        ];

        if ($full) {
            $data['content'] = $post->content;
            $data['meta_title'] = $post->meta_title ?: $post->title;
            $data['meta_description'] = $post->meta_description ?: $post->excerpt;
        }

        return $data;
    }

    protected function sendViaPhpMail(string $recipient, ContactMessage $message): bool
    {
        if (! function_exists('mail')) {
            return false;
        }

        $subject = 'New portfolio contact: ' . $message->subject;
        $body = view('emails.contact-message-received', ['contactMessage' => $message])->render();

        $fromAddress = config('mail.from.address') ?: 'amanullah@triplewtools.com';
        $fromName = config('mail.from.name') ?: 'Amanullah Portfolio';

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $fromAddress . '>',
            'Reply-To: ' . $message->name . ' <' . $message->email . '>',
            'X-Mailer: PHP/' . phpversion(),
        ];

        return (bool) @mail($recipient, $subject, $body, implode("\r\n", $headers), "-f{$fromAddress}");
    }
}
