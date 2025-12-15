<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Map Laravel locale to Carbon locale
     */
    private array $carbonLocaleMap = [
        'bm' => 'ms',  // Bahasa Malaysia -> Malay
        'en' => 'en',  // English -> English
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'bm'; // Default locale

        // Check session first
        if ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }
        // Then check authenticated user preference
        elseif ($request->user() && $request->user()->language_preference) {
            $locale = $request->user()->language_preference;
            $request->session()->put('locale', $locale);
        }

        // Set Laravel locale
        App::setLocale($locale);

        // Set Carbon locale for date formatting (translatedFormat)
        $carbonLocale = $this->carbonLocaleMap[$locale] ?? 'ms';
        Carbon::setLocale($carbonLocale);

        return $next($request);
    }
}

