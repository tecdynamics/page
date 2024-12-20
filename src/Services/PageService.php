<?php

namespace Tec\Page\Services;

use Tec\Base\Enums\BaseStatusEnum;
use Tec\Base\Facades\BaseHelper;
use Tec\Base\Supports\RepositoryHelper;
use Tec\Media\Facades\RvMedia;
use Tec\Page\Models\Page;
use Tec\SeoHelper\Facades\SeoHelper;
use Tec\Slug\Models\Slug;
use Tec\Theme\Facades\Theme;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class PageService
{
    public function handleFrontRoutes(Slug|array $slug): Slug|array
    {
        if (! $slug instanceof Slug) {
            return $slug;
        }

        $condition = [
            'id' => $slug->reference_id,
            'status' => BaseStatusEnum::PUBLISHED,
        ];

        if (Auth::guard()->check() && request()->input('preview')) {
            Arr::forget($condition, 'status');
        }

        if ($slug->reference_type !== Page::class) {
            return $slug;
        }

        $page = Page::query()
            ->where($condition)
            ->with('slugable');

        $page = RepositoryHelper::applyBeforeExecuteQuery($page, new Page(), true)->first();

        if (empty($page)) {
            if ($slug->reference_id == BaseHelper::getHomepageId()) {
                return [];
            }

            abort(404);
        }

        if (! BaseHelper::isHomepage($page->getKey())) {
            SeoHelper::setTitle($page->name)
                ->setDescription($page->description);
        } else {
            $siteTitle = theme_option('seo_title') ?: theme_option('site_title');
            $seoDescription = theme_option('seo_description');

            SeoHelper::setTitle($siteTitle)
                ->setDescription($seoDescription);
        }

        if ($page->image) {
            SeoHelper::openGraph()->setImage(RvMedia::getImageUrl($page->image));
        }

        SeoHelper::openGraph()->setUrl($page->url);
        SeoHelper::openGraph()->setType('article');

        SeoHelper::meta()->setUrl($page->url);

        if ($page->template) {
            Theme::uses(Theme::getThemeName())
                ->layout($page->template);
        }

        if (function_exists('admin_bar')) {
            admin_bar()
                ->registerLink(
                    trans('packages/page::pages.edit_this_page'),
                    route('pages.edit', $page->getKey()),
                    null,
                    'pages.edit'
                );
        }

        if (function_exists('shortcode')) {
            shortcode()->getCompiler()->setEditLink(route('pages.edit', $page->getKey()), 'pages.edit');
        }

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, PAGE_MODULE_SCREEN_NAME, $page);

        Theme::breadcrumb()->add($page->name, $page->url);

        return [
            'view' => 'page',
            'default_view' => 'packages/page::themes.page',
            'data' => compact('page'),
            'slug' => $page->slug,
        ];
    }
}
