<?php

namespace Tec\Page\Tables;

use Tec\Page\Models\Page;
use Tec\Table\Abstracts\TableAbstract;
use Tec\Table\Actions\DeleteAction;
use Tec\Table\Actions\EditAction;
use Tec\Table\BulkActions\DeleteBulkAction;
use Tec\Table\BulkChanges\CreatedAtBulkChange;
use Tec\Table\BulkChanges\NameBulkChange;
use Tec\Table\BulkChanges\SelectBulkChange;
use Tec\Table\BulkChanges\StatusBulkChange;
use Tec\Table\Columns\CreatedAtColumn;
use Tec\Table\Columns\FormattedColumn;
use Tec\Table\Columns\IdColumn;
use Tec\Table\Columns\NameColumn;
use Tec\Table\Columns\StatusColumn;
use Tec\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class PageTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(Page::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('pages.create'))
            ->addActions([
                EditAction::make()->route('pages.edit'),
                DeleteAction::make()->route('pages.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                NameColumn::make()->route('pages.edit'),
                FormattedColumn::make('template')
                    ->title(trans('core/base::tables.template'))
                    ->alignStart()
                    ->getValueUsing(function (FormattedColumn $column) {
                        static $pageTemplates;

                        $pageTemplates ??= get_page_templates();

                        return Arr::get($pageTemplates, $column->getItem()->template ?: 'default');
                    }),
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
            });
    }
}
