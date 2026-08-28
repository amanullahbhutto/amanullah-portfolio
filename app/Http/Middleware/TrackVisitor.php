<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackVisitor
{
    private const COOKIE_NAME = 'portfolio_visitor_id';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        $visitorId = $this->visitorId($request);
        $sessionId = $this->sessionId($request);
        $projectId = $this->projectId($request);
        $path = '/'.ltrim($request->path(), '/');
        $visitKey = $projectId ? 'project:'.$projectId : 'path:'.$path;

        if ($this->alreadyTracked($request, $visitorId, $sessionId, $visitKey)) {
            $this->queueVisitorCookie($request, $visitorId);

            return $response;
        }

        $details = $this->parseUserAgent((string) $request->userAgent());

        try {
            VisitorLog::query()->create([
                'user_id' => $request->user()?->id,
                'project_id' => $projectId,
                'visitor_id' => $visitorId,
                'session_id' => $sessionId,
                'visit_key' => $visitKey,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'browser' => $details['browser'],
                'platform' => $details['platform'],
                'device_type' => $details['device_type'],
                'route_name' => $request->route()?->getName(),
                'path' => $path,
                'full_url' => $request->fullUrl(),
                'referrer' => $request->headers->get('referer'),
                'visited_at' => now(),
            ]);

            $this->markTracked($request, $visitKey);
        } catch (Throwable $exception) {
            report($exception);
            $this->markTracked($request, $visitKey);
        }

        $this->queueVisitorCookie($request, $visitorId);

        return $response;
    }

    private function queueVisitorCookie(Request $request, string $visitorId): void
    {
        if ($request->cookie(self::COOKIE_NAME) !== $visitorId) {
            cookie()->queue(cookie(
                self::COOKIE_NAME,
                $visitorId,
                60 * 24 * 365,
                null,
                null,
                false,
                true,
                false,
                'Lax'
            ));
        }
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $request->expectsJson() || $response->getStatusCode() >= 400) {
            return false;
        }

        return ! $request->is(
            'admin*',
            'login',
            'register',
            'logout',
            'assets*',
            'storage*',
            'vendor*',
            'favicon.ico',
            'robots.txt',
            'up'
        );
    }

    private function visitorId(Request $request): string
    {
        $visitorId = $request->cookie(self::COOKIE_NAME);

        if (is_string($visitorId) && preg_match('/^[A-Za-z0-9-]{20,64}$/', $visitorId) === 1) {
            return $visitorId;
        }

        return (string) Str::uuid();
    }

    private function sessionId(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        return $request->session()->getId();
    }

    private function projectId(Request $request): ?int
    {
        $project = $request->route('project');

        return $project instanceof Project ? (int) $project->getKey() : null;
    }

    private function alreadyTracked(Request $request, string $visitorId, ?string $sessionId, string $visitKey): bool
    {
        if ($request->hasSession()) {
            $trackedVisitKeys = $request->session()->get('tracked_visit_keys', []);

            if (in_array($visitKey, $trackedVisitKeys, true)) {
                return true;
            }
        }

        return VisitorLog::query()
            ->where('visit_key', $visitKey)
            ->where(function ($query) use ($visitorId, $sessionId): void {
                $query->where('visitor_id', $visitorId);

                if ($sessionId !== null) {
                    $query->orWhere('session_id', $sessionId);
                }
            })
            ->exists();
    }

    private function markTracked(Request $request, string $visitKey): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $trackedVisitKeys = $request->session()->get('tracked_visit_keys', []);
        $trackedVisitKeys[] = $visitKey;

        $request->session()->put(
            'tracked_visit_keys',
            array_slice(array_values(array_unique($trackedVisitKeys)), -200)
        );
    }

    /**
     * A small parser is enough for admin-facing analytics without adding a package.
     *
     * @return array{browser: string, platform: string, device_type: string}
     */
    private function parseUserAgent(string $userAgent): array
    {
        $agent = strtolower($userAgent);

        return [
            'browser' => $this->browserName($agent),
            'platform' => $this->platformName($agent),
            'device_type' => $this->deviceType($agent),
        ];
    }

    private function browserName(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'edg/') || str_contains($agent, 'edge/') => 'Microsoft Edge',
            str_contains($agent, 'opr/') || str_contains($agent, 'opera') => 'Opera',
            str_contains($agent, 'chrome/') || str_contains($agent, 'crios/') => 'Chrome',
            str_contains($agent, 'firefox/') || str_contains($agent, 'fxios/') => 'Firefox',
            str_contains($agent, 'safari/') => 'Safari',
            str_contains($agent, 'msie') || str_contains($agent, 'trident/') => 'Internet Explorer',
            default => 'Unknown',
        };
    }

    private function platformName(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'windows') => 'Windows',
            str_contains($agent, 'android') => 'Android',
            str_contains($agent, 'iphone') || str_contains($agent, 'ipad') => 'iOS',
            str_contains($agent, 'mac os') || str_contains($agent, 'macintosh') => 'macOS',
            str_contains($agent, 'linux') => 'Linux',
            default => 'Unknown',
        };
    }

    private function deviceType(string $agent): string
    {
        return match (true) {
            str_contains($agent, 'bot') || str_contains($agent, 'crawler') || str_contains($agent, 'spider') => 'Bot',
            str_contains($agent, 'ipad') || str_contains($agent, 'tablet') => 'Tablet',
            str_contains($agent, 'mobile') || str_contains($agent, 'iphone') || str_contains($agent, 'android') => 'Mobile',
            default => 'Desktop',
        };
    }
}
