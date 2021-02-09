<?php

namespace Pvtl\VoyagerPages\Http\Controllers;

use View;
use Pvtl\VoyagerPages\Page;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class PageController extends VoyagerBaseController
{
    /**
     * This is the module's view path that can be overriden
     */
    protected $viewPath = 'voyager-pages';

    /**
     * Route: Gets a single page and passes data to a view
     *
     * @param string $slug
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPage($slug = 'home')
    {
        $page = Page::where(['slug' => $slug, 'status' => 'ACTIVE'])->firstOrFail();

        View::composer('*', function($view) use ($page) {
            View::share('page', $page);
        });

        return view("{$this->viewPath}::modules.pages.default", [
            'page' => $page,
        ]);
    }
}
