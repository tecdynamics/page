<?php

namespace Tec\Page\Tables;

use Tec\Base\Enums\BaseStatusEnum;
use Tec\Page\Models\Page;
use Tec\Table\Abstracts\TableAbstract;
use Tec\Table\Actions\DeleteAction;
use Tec\Table\Actions\DublicateAction;
use Tec\Table\Actions\EditAction;
use Tec\Table\BulkActions\DeleteBulkAction;
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
                'name' => [
                    'title' => trans('core/base::tables.name'),
                    'type' => 'text',
                    'validate' => 'required|max:120',
                ],
                'status' => [
                    'title' => trans('core/base::tables.status'),
                    'type' => 'customSelect',
                    'choices' => BaseStatusEnum::labels(),
                    'validate' => 'required|' . Rule::in(BaseStatusEnum::values()),
                ],
                'template' => [
                    'title' => trans('core/base::tables.template'),
                    'type' => 'customSelect',
                    'choices' => get_page_templates(),
                    'validate' => 'required',
                ],
                'has_breadcrumb' => [
                    'title' => 'Has Breadcrumb',
                    'type' => 'customSelect',
                    'choices' => [1=>'Yes',0=>'No'],
                    'validate' => 'false',
                ],
                'is_restricted' => [
                    'title' => 'Is Restricted',
                    'type' => 'customSelect',
                    'choices' =>  [1=>'Yes',0=>'No'],
                    'validate' => 'false',
                ],
                'created_at' => [
                    'title' => trans('core/base::tables.created_at'),
                    'type' => 'datePicker',
                ],
            ])
            ->queryUsing(function (Builder $query) {
                $query->select([
                    'id',
                    'name',
                    'template',
                    'created_at',
                    'status',
                ]);
            })
            ->onAjax(function (): JsonResponse {
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
