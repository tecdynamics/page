<?php

namespace Tec\Page\Http\Controllers;

use Tec\Page\Models\Page;
use Tec\Page\Services\PageService;
use Tec\Theme\Events\RenderingSingleEvent;
use Illuminate\Routing\Controller;
use Response;
use SlugHelper;
use Theme;

class PublicController extends Controller
{
    /**
     * @param string $slug
     * @param PageService $pageService
     * @return \Illuminate\Http\RedirectResponse|Response
     */
    public function getPage($slug, PageService $pageService)
    {
        $slug = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Page::class));

        if (!$slug) {
            abort(404);
        }

        $data = $pageService->handleFrontRoutes($slug);

        if (isset($data['slug']) && $data['slug'] !== $slug->key) {
            return redirect()->to(url(SlugHelper::getPrefix(Page::class) . '/' . $data['slug']));
        }

        event(new RenderingSingleEvent($slug));

        return Theme::scope($data['view'], $data['data'], $data['default_view'])->render();
    }
}
