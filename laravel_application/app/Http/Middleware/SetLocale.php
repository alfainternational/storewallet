<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Priority order: URL parameter > Session > User preference > Browser > Default

        // 1. Check URL parameter
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if ($this->isValidLocale($locale)) {
                Session::put('locale', $locale);
                App::setLocale($locale);

                // Save to user profile if authenticated
                if (auth()->check() && auth()->user()->preferred_locale !== $locale) {
                    auth()->user()->update(['preferred_locale' => $locale]);
                }

                return $next($request);
            }
        }

        // 2. Check user preference (if authenticated)
        if (auth()->check() && auth()->user()->preferred_locale) {
            $locale = auth()->user()->preferred_locale;
            if ($this->isValidLocale($locale)) {
                Session::put('locale', $locale);
                App::setLocale($locale);
                return $next($request);
            }
        }

        // 3. Check session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            if ($this->isValidLocale($locale)) {
                App::setLocale($locale);
                return $next($request);
            }
        }

        // 4. Check browser language
        $browserLocale = $this->getBrowserLocale($request);
        if ($browserLocale && $this->isValidLocale($browserLocale)) {
            Session::put('locale', $browserLocale);
            App::setLocale($browserLocale);
            return $next($request);
        }

        // 5. Use default (Arabic)
        App::setLocale(config('app.locale', 'ar'));

        return $next($request);
    }

    /**
     * Check if locale is valid
     */
    protected function isValidLocale($locale)
    {
        return in_array($locale, config('app.available_locales', ['ar', 'en']));
    }

    /**
     * Get browser preferred language
     */
    protected function getBrowserLocale($request)
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        $languages = explode(',', $acceptLanguage);
        foreach ($languages as $language) {
            $lang = trim(explode(';', $language)[0]);
            $lang = substr($lang, 0, 2); // Get first 2 characters (ar, en, etc)

            if ($this->isValidLocale($lang)) {
                return $lang;
            }
        }

        return null;
    }
}
