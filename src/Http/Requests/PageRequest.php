<?php

namespace Tec\Page\Http\Requests;

use Tec\Base\Enums\BaseStatusEnum;
use Tec\Page\Supports\Template;
use Tec\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PageRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'        => 'required|max:120',
            'description' => 'max:400',
            'content'     => 'required',
            'template'    => Rule::in(array_keys(Template::getPageTemplates())),
            'status'      => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
