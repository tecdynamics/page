<?php

namespace Tec\Page\Providers;

use Tec\Base\Facades\DashboardMenu;
use Tec\Base\Supports\ServiceProvider;
use Tec\Base\Traits\LoadAndPublishDataTrait;
use Tec\Page\Models\Page;
use Tec\Page\Repositories\Eloquent\PageRepository;
use Tec\Page\Repositories\Interfaces\PageInterface;
use Tec\Shortcode\View\View;
use Tec\Theme\Events\RenderingAdminBar;
use Tec\Theme\Facades\AdminBar;
use Illuminate\Support\Facades\View as ViewFacade;

/**
 * @since 02/07/2016 09:50 AM
 */
class PageServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this->app->bind(PageInterface::class, function () {
            return new PageRepository(new Page());
        });

        $this
            ->setNamespace('packages/page')
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadHelpers()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadMigrations();

        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-core-page',
                    'priority' => 2,
                    'name' => 'packages/page::pages.menu_name',
                    'icon' => 'ti ti-notebook',
                    'route' => 'pages.index',
                ]);
        });

        $this->app['events']->listen(RenderingAdminBar::class, function () {
            AdminBar::registerLink(
                trans('packages/page::pages.menu_name'),
                route('pages.create'),
                'add-new',
                'pages.create'
            );
        });

        if (function_exists('shortcode')) {
            ViewFacade::composer(['packages/page::themes.page'], function (View $view) {
                $view->withShortcodes();
            });
        }

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });

        $this->app->register(EventServiceProvider::class);
    }
}
