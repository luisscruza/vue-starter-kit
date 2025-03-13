<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $quote = Inspiring::quotes()->random();
        $parts = is_string($quote) ? str($quote)->explode('-') : [];
        $message = $parts[0] ?? '';
        $author = $parts[1] ?? '';

        // @phpstan-ignore-next-line
        return array_merge(
            parent::share($request),
            [
                'name' => config('app.name'),
                'quote' => ['message' => trim($message), 'author' => trim($author)],
                'auth' => [
                    'user' => $request->user(),
                    'teams' => $request->user()->teams ?? null,
                    'currentTeam' => $request->user()->currentTeam ?? null,
                ],
                'ziggy' => array_merge(
                    (new Ziggy)->toArray(),
                    ['location' => $request->url()]
                ),
            ]
        );
    }
}
