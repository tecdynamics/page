<?php

namespace Tec\Page\Database\Traits;

use Tec\ACL\Models\User;
use Tec\Base\Facades\MetaBox;
use Tec\Page\Models\Page;
use Tec\Slug\Facades\SlugHelper;
use Illuminate\Support\Arr;

trait HasPageSeeder
{
    protected function getPageId(string $name): int|string
    {
        return Page::query()->where('name', $name)->value('id');
    }

    protected function createPages(array $pages): void
    {
        $userId = User::query()->value('id');

        foreach ($pages as $item) {
            $item['user_id'] = $userId;

            $page = Page::query()->create(Arr::except($item, 'metadata'));

            if (Arr::has($item, 'metadata')) {
                foreach ($item['metadata'] as $key => $value) {
                    MetaBox::saveMetaBoxData($page, $key, $value);
                }
            }

            SlugHelper::createSlug($page);
        }
    }

    protected function truncatePages(): void
    {
        Page::query()->truncate();
    }
}
