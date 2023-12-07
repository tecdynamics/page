<?php

namespace Tec\Page\Http\Controllers;

use Tec\Page\Models\Page;
use Tec\Page\Services\PageService;
use Tec\Slug\Facades\SlugHelper;
use Tec\Theme\Events\RenderingSingleEvent;
use Tec\Theme\Facades\Theme;
use Illuminate\Routing\Controller;

class PublicController extends Controller
{
    public function getPage(string $slug, PageService $pageService)
    {
        $slug = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Page::class));

        if (! $slug) {
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
