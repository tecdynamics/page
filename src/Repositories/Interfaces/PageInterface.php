<?php

namespace Tec\Page\Repositories\Interfaces;

use Tec\Support\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PageInterface extends RepositoryInterface
{
    public function getDataSiteMap(): Collection;

    public function whereIn(array $array, array $select = []): Collection;

    public function getSearch(?string $query, int $limit = 10): Collection|LengthAwarePaginator;

    public function getAllPages(bool $active = true): Collection;
}
