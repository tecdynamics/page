<?php

namespace Tec\Page\Providers;

use Tec\Base\Facades\Html;
use Tec\Base\Supports\RepositoryHelper;
use Tec\Base\Supports\ServiceProvider;
use Tec\Dashboard\Supports\DashboardWidgetInstance;
use Tec\Media\Facades\RvMedia;
use Tec\Menu\Facades\Menu;
use Tec\Page\Models\Page;
use Tec\Page\Services\PageService;
use Tec\Slug\Models\Slug;
use Tec\Table\Columns\Column;
use Tec\Table\Columns\NameColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (defined('MENU_ACTION_SIDEBAR_OPTIONS')) {
            Menu::addMenuOptionModel(Page::class);
            add_action(MENU_ACTION_SIDEBAR_OPTIONS, [$this, 'registerMenuOptions'], 10);
        }

        add_filter(DASHBOARD_FILTER_ADMIN_LIST, [$this, 'addPageStatsWidget'], 15, 2);
        add_filter(BASE_FILTER_PUBLIC_SINGLE_DATA, [$this, 'handleSingleView'], 1);

        if (function_exists('theme_option')) {
            add_action(RENDERING_THEME_OPTIONS_PAGE, [$this, 'addThemeOptions'], 31);
        }

        if (defined('THEME_FRONT_HEADER')) {
            add_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, function ($screen, $page): void {
                add_filter(THEME_FRONT_HEADER, function (string|null $html) use ($page): string|null {
                    if (get_class($page) != Page::class) {
                        return $html;
                    }

                    $schema = [
                        '@context' => 'https://schema.org',
                        '@type' => 'Organization',
                        'name' => theme_option('site_title'),
                        'url' => $page->url,
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => RvMedia::getImageUrl(theme_option('logo')),
                        ],
                    ];

                    return $html . Html::tag('script', json_encode($schema), ['type' => 'application/ld+json'])
                            ->toHtml();
                }, 2);
            }, 2, 2);
        }

        add_filter(PAGE_FILTER_FRONT_PAGE_CONTENT, fn (string|null $html) => (string) $html, 1, 2);

        add_filter('table_name_column_data', [$this, 'appendPageName'], 2, 3);
    }

    public function appendPageName(string $value, Model $model, Column $column)
    {
        if ($column instanceof NameColumn && $model instanceof Page) {
            $value = apply_filters(PAGE_FILTER_PAGE_NAME_IN_ADMIN_LIST, $value, $model);
        }

        return $value;
    }

    public function addThemeOptions(): void
    {
        $pages = Page::query()
            ->wherePublished();

        $pages = RepositoryHelper::applyBeforeExecuteQuery($pages, new Page())
            ->pluck('name', 'id')
            ->all();

        theme_option()
            ->setSection([
                'title' => 'Page',
                'desc' => 'Theme options for Page',
                'id' => 'opt-text-subsection-page',
                'subsection' => true,
                'icon' => 'fa fa-book',
                'fields' => [
                    [
                        'id' => 'homepage_id',
                        'type' => 'customSelect',
                        'label' => trans('packages/page::pages.settings.show_on_front'),
                        'attributes' => [
                            'name' => 'homepage_id',
                            'list' => [0 => trans('packages/page::pages.settings.select')] + $pages,
                            'value' => '',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ],
                    ],
                    [
                        'id'         => '404_custom_page',
                        'type'       => 'customSelect',
                        'label'      => trans('packages/page::pages.404_page'),
                        'attributes' => [
                            'name'    => '404_custom_page',
                            'list'    => ['' => trans('packages/page::pages.settings.select')] + $pages,
                            'value'   => '',
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function registerMenuOptions(): void
    {
        if (Auth::guard()->user()->hasPermission('pages.index')) {
            Menu::registerMenuOptions(Page::class, trans('packages/page::pages.menu'));
        }
    }

    public function addPageStatsWidget(array $widgets, Collection $widgetSettings): array
    {
        $pages = Page::query()->wherePublished()->count();

        return (new DashboardWidgetInstance())
            ->setType('stats')
            ->setPermission('pages.index')
            ->setTitle(trans('packages/page::pages.pages'))
            ->setKey('widget_total_pages')
            ->setIcon('far fa-file-alt')
            ->setColor('#32c5d2')
            ->setStatsTotal($pages)
            ->setRoute(route('pages.index'))
            ->init($widgets, $widgetSettings);
    }

    public function handleSingleView(Slug|array $slug): Slug|array
    {
        return (new PageService())->handleFrontRoutes($slug);
    }
}
