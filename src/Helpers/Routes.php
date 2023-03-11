<?php

namespace Pvtl\VoyagerPages\Helpers;

use LaravelLocalization;
use Pvtl\VoyagerPages\Page;
use TCG\Voyager\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Request;

class Routes
{
    /**
     * Dynamically register pages.
     */
    public static function registerPageRoutes()
    {
        // Prevents error before our migration has run
        if (!Schema::hasTable('pages')) {
            return;
        }

        // Which Page Controller shall we use to display the page? Page Blocks or standard page?
        $pageController = '\Pvtl\VoyagerPages\Http\Controllers\PageController';

        if (class_exists('\Pvtl\VoyagerFrontend\Http\Controllers\PageController')) {
            $pageController = '\Pvtl\VoyagerFrontend\Http\Controllers\PageController';
        }

        if (class_exists('\Pvtl\VoyagerPageBlocks\Providers\PageBlocksServiceProvider')) {
            $pageController = '\Pvtl\VoyagerPageBlocks\Http\Controllers\PageController';
        }

        $default_locale = config('app.locale');

        // Get all page slugs (note it's cached for 5mins)
        $routes = Cache::remember('page/slugs_detailed', 5, function() use ($default_locale) {
            $pages = Page::whereNull('site')->select(['id', 'slug', 'route_name'])->get();
            $translations = Translation::where('table_name', 'pages')
                ->where('column_name', 'slug')
                ->select('foreign_key', 'locale', 'value')
                ->get();

            $routes = [];
            foreach ($pages as &$page) {
                $page_id = $page->getKey();
                $page_translations = $translations->where('foreign_key', $page_id);

                $tmp_route = collect([$default_locale => $page->slug]);
                foreach ($page_translations as &$translation) {
                    $tmp_route[$translation->locale] = $translation->value;
                }

                $routes[$page->route_name] = $tmp_route;
            }

            return collect($routes);
        });

        // When the current URI is known to be a page slug, let it be a route
        foreach ($routes as $route_name => &$route) {
            foreach ($route as $route_locale => &$route_slug) {
                $_route_slug = $route_slug == 'home' ? '/' : $route_slug;
                $_slug = \in_array($_route_slug, ['/', '', $route_locale, "{$route_locale}/"]) ? '/' : $_route_slug;

                Route::group(['prefix' => LaravelLocalization::setLocale()], function() use ($_slug, $route_slug, $route_name, $pageController) {
                    Route::get($_slug, "$pageController@getPage")->middleware('web')
                        // ->middleware(['localeSessionRedirect', 'localizationRedirect', 'localeViewPath'])
                        ->name($route_name);
                });
            }
        }

        // Fix for unit tests
        // tests don't see generated routes from database
        Route::getRoutes()->refreshNameLookups();
    }
}
