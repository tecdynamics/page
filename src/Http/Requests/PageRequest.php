<?php

namespace Tec\Page\Http\Requests;

use Tec\Base\Enums\BaseStatusEnum;
use Tec\Page\Supports\Template;
use Tec\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PageRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:400',
            'content' => 'nullable|string',
            'template' => Rule::in(array_keys(Template::getPageTemplates())),
            'status' => Rule::in(BaseStatusEnum::values()),
        ];
    }
}
