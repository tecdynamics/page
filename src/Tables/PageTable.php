<?php

namespace Tec\Page\Tables;

use Illuminate\Support\Facades\Auth;
use Tec\Base\Enums\BaseStatusEnum;
use Tec\Base\Facades\BaseHelper;
use Tec\Base\Facades\Html;
use Tec\Page\Models\Page;
use Tec\Table\Abstracts\TableAbstract;
use Tec\Table\Actions\DeleteAction;
use Tec\Table\Actions\DublicateAction;
use Tec\Table\Actions\EditAction;
use Tec\Table\BulkActions\DeleteBulkAction;
use Tec\Table\BulkChanges\CreatedAtBulkChange;
use Tec\Table\BulkChanges\NameBulkChange;
use Tec\Table\BulkChanges\SelectBulkChange;
use Tec\Table\BulkChanges\StatusBulkChange;
use Tec\Table\Columns\Column;
use Tec\Table\Columns\CreatedAtColumn;
use Tec\Table\Columns\IdColumn;
use Tec\Table\Columns\NameColumn;
use Tec\Table\Columns\StatusColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PageTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Page::class)
            ->addActions([
                DublicateAction::make()->route('pages.duplicatepage'),
                EditAction::make()->route('pages.edit'),
                DeleteAction::make()->route('pages.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('pages.edit'),
                Column::make('template')
                    ->title(trans('core/base::tables.template'))
                    ->alignStart(),
                CreatedAtColumn::make(),
                StatusColumn::make(),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('pages.destroy'),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
                SelectBulkChange::make()
                    ->name('template')
                    ->title(trans('core/base::tables.template'))
                    ->choices(fn () => get_page_templates())
                    ->validate(['required', Rule::in(array_keys(get_page_templates()))]),
                StatusBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->queryUsing(function (Builder $query) {
                $query->select([
                    'id',
                    'name',
                    'template',
                    'created_at',
                    'status',
                ]);
            })->onAjax(function (): JsonResponse {
                return $this->toJson(
                    $this
                        ->table
                        ->eloquent($this->query())
                        ->editColumn('template', function (Page $item) {
                            static $pageTemplates;

                            $pageTemplates ??= get_page_templates();

                            return Arr::get($pageTemplates, $item->template ?: 'default');
                        })
                );
            });
    }


    public function buttons(): array
    {
        return $this->addCreateButton(route('pages.create'), 'pages.create');
    }
}
