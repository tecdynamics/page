<?php

namespace Tec\Page\Providers;

use Tec\Base\Facades\DashboardMenu;
use Tec\Base\Supports\ServiceProvider;
use Tec\Base\Traits\LoadAndPublishDataTrait;
use Tec\Page\Http\Middleware\IsRestrictedMiddleware;
use Tec\Page\Models\Page;
use Tec\Page\Repositories\Eloquent\PageRepository;
use Tec\Page\Repositories\Interfaces\PageInterface;
use Tec\Shortcode\View\View;
use Tec\Theme\Facades\AdminBar;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\View as ViewFacade;


class PageServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('packages/page')
            ->loadHelpers();
    }

    public function boot(): void
    {
        $this->app->bind(PageInterface::class, function () {
            return new PageRepository(new Page());
        });

        $this
            ->loadAndPublishConfigurations(['permissions', 'general'])
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->loadMigrations();

        $this->app['events']->listen(RouteMatched::class, function () {
            DashboardMenu::registerItem([
                'id' => 'cms-core-page',
                'priority' => 2,
                'parent_id' => null,
                'name' => 'packages/page::pages.menu_name',
                'icon' => 'fa fa-book',
                'url' => route('pages.index'),
                'permissions' => ['pages.index'],
            ]);

            if (function_exists('admin_bar')) {
                AdminBar::registerLink(
                    trans('packages/page::pages.menu_name'),
                    route('pages.create'),
                    'add-new',
                    'pages.create'
                );
            }
        });

        if (function_exists('shortcode')) {
            ViewFacade::composer(['packages/page::themes.page'], function (View $view) {
                $view->withShortcodes();
            });
        }

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
            $router = $this->app['router'];
            $router->pushMiddlewareToGroup('web', IsRestrictedMiddleware::class);
        });

        $this->app->register(EventServiceProvider::class);
    }
}
