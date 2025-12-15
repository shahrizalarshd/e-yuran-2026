<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * Map Laravel locale to Carbon locale
     */
    private array $carbonLocaleMap = [
        'bm' => 'ms',  // Bahasa Malaysia -> Malay
        'en' => 'en',  // English -> English
    ];

    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, ['bm', 'en'])) {
            abort(400);
        }

        // Update session
        $request->session()->put('locale', $locale);

        // Update user preference if authenticated
        if (auth()->check()) {
            auth()->user()->update(['language_preference' => $locale]);
        }

        // Set Laravel locale
        App::setLocale($locale);

        // Set Carbon locale for date formatting
        $carbonLocale = $this->carbonLocaleMap[$locale] ?? 'ms';
        Carbon::setLocale($carbonLocale);

        return back();
    }
}

