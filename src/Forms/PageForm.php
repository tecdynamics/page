<?php

namespace Tec\Page\Forms;

use Tec\Base\Enums\BaseStatusEnum;
use Tec\Base\Forms\FormAbstract;
use Tec\Page\Http\Requests\PageRequest;
use Tec\Page\Models\Page;

class PageForm extends FormAbstract
{

    /**
     * @var string
     */
    protected $template = 'core/base::forms.form-tabs';

    /**
     * {@inheritDoc}
     */
    public function buildForm()
    {
        $this
            ->setupModel(new Page)
            ->setValidatorClass(PageRequest::class)
            ->withCustomFields()

            ->add('name', 'text', [
                'label'      => trans('core/base::forms.name'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'  => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label'      => trans('core/base::forms.description'),
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'rows'         => 4,
                    'placeholder'  => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 400,
                ],
            ])
            ->add('content', 'editor', [
                'label'      => trans('core/base::forms.content'),
                'label_attr' => ['class' => 'control-label required'],
                'attr'       => [
                    'placeholder'     => trans('core/base::forms.description_placeholder'),
                    'with-short-code' => true,
                    'with-style' => true,
                ],
            ])
            ->add('status', 'customSelect', [
                'label'      => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'choices'    => BaseStatusEnum::labels(),
            ])
            ->add('template', 'customSelect', [
                'label'      => trans('core/base::forms.template'),
                'label_attr' => ['class' => 'control-label required'],
                'choices'    => get_page_templates(),
            ])
            ->add('is_featured', 'onOff', [
                'label' => trans('core/base::forms.is_featured'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => false,
            ])
            ->add('has_breadcrumb', 'onOff', [
                'label' => trans('packages/page::pages.has_breadcrumb'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => true,
            ])
            ->add('is_restricted', 'onOff', [
                'label' => trans('packages/page::pages.is_restricted'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => false,
            ])
            ->add('image', 'mediaImage', [
                'label'      => trans('core/base::forms.image'),
                'label_attr' => ['class' => 'control-label'],
            ])
            ->setBreakFieldPoint('status');
    }
}
