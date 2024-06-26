<?php

namespace Tec\Page\Forms;

use Illuminate\Support\Facades\Request;
use Tec\Base\Enums\BaseStatusEnum;
use Tec\Base\Forms\FormAbstract;
use Tec\Page\Http\Requests\PageRequest;
use Tec\Page\Models\Page;

class PageForm extends FormAbstract
{
    public function buildForm(): void
    {
//	 dd($this->getModel()->is_restricted,$this->getModel());
        $this
            ->setupModel($this->getModel())
            ->setValidatorClass(PageRequest::class)
            ->hasTabs()
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('core/base::forms.name_placeholder'),
                    'data-counter' => 120,
                ],
            ])
            ->add('description', 'textarea', [
                'label' => trans('core/base::forms.description'),
                'attr' => [
                    'rows' => 4,
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'data-counter' => 400,
                ],
            ])
            ->add('content', 'editor', [
                'label' => trans('core/base::forms.content'),
                'attr' => [
                    'placeholder' => trans('core/base::forms.description_placeholder'),
                    'with-short-code' => true,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'required' => true,
                'choices' => BaseStatusEnum::labels(),
            ])
            ->add('template', 'customSelect', [
                'label' => trans('core/base::forms.template'),
                'required' => true,
                'choices' => get_page_templates(),
            ])
            ->add('has_breadcrumb', 'customSelect', [
                'label' => 'Has Breadcrumb',
                'required' => false,
								'default_value'=>(int)$this->getModel()->has_breadcrumb,
                'choices' =>[1=>'Yes',0=>'No'],
            ])
            ->add('is_restricted', 'customSelect', [
                'label' => 'Is Restricted',
                'required' => false,
								'default_value'=>((int)$this->getModel()->is_restricted>0)?'1':'0',
                'choices' => [1=>'Yes',0=>'No'],
            ])

            ->add('image', 'mediaImage')
            ->setBreakFieldPoint('status');
    }
}
