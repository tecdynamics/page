<?php

namespace Tec\Page\Supports;

use Tec\Base\Facades\BaseHelper;
use Tec\Theme\Facades\Theme;

class Template
{
    public static function registerPageTemplate(array $templates = []): void
    {
        $validTemplates = [];
        foreach ($templates as $key => $template) {
            if (in_array($key, self::getExistsTemplate())) {
                $validTemplates[$key] = $template;
            }
        }

        config([
            'packages.page.general.templates' => array_merge(
                config('packages.page.general.templates'),
                $validTemplates
            ),
        ]);
    }

    protected static function getExistsTemplate(): array
    {
        $themes = [
            Theme::getThemeName(),
        ];

        if (Theme::hasInheritTheme()) {
            $themes[] = Theme::getInheritTheme();
        }

        foreach ($themes as $theme) {
            $files = BaseHelper::scanFolder(theme_path($theme . DIRECTORY_SEPARATOR . config('packages.theme.general.containerDir.layout')));

            foreach ($files as $key => $file) {
                $files[$key] = str_replace('.blade.php', '', $file);
            }
        }

        return $files;
    }

    public static function getPageTemplates(): array
    {
        return (array) config('packages.page.general.templates', []);
    }
}
