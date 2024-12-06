<?php

namespace Tec\Page\Forms;

use Tec\Base\Forms\FieldOptions\ContentFieldOption;
use Tec\Base\Forms\FieldOptions\DescriptionFieldOption;
use Tec\Base\Forms\FieldOptions\NameFieldOption;
use Tec\Base\Forms\FieldOptions\SelectFieldOption;
use Tec\Base\Forms\FieldOptions\StatusFieldOption;
use Tec\Base\Forms\Fields\EditorField;
use Tec\Base\Forms\Fields\MediaImageField;
use Tec\Base\Forms\Fields\SelectField;
use Tec\Base\Forms\Fields\TextareaField;
use Tec\Base\Forms\Fields\TextField;
use Tec\Base\Forms\FormAbstract;
use Tec\Page\Http\Requests\PageRequest;
use Tec\Page\Models\Page;
use Tec\Page\Supports\Template;

class PageForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(Page::class)
            ->setValidatorClass(PageRequest::class)
            ->hasTabs()
            ->add('name', TextField::class, NameFieldOption::make()->maxLength(120)->required()->toArray())
            ->add('description', TextareaField::class, DescriptionFieldOption::make()->toArray())
            ->add('content', EditorField::class, ContentFieldOption::make()->allowedShortcodes()->toArray())
            ->add('status', SelectField::class, StatusFieldOption::make()->toArray())
            ->when(Template::getPageTemplates(), function (PageForm $form, array $templates) {
                return $form
                    ->add(
                        'template',
                        SelectField::class,
                        SelectFieldOption::make()
                            ->label(trans('core/base::forms.template'))
                            ->required()
                            ->choices($templates)
                            ->toArray()
                    );
            })
            ->add('image', MediaImageField::class)
            ->setBreakFieldPoint('status');
    }
}
