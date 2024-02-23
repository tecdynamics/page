<?php

namespace Tec\Page\Models;

use Tec\ACL\Models\User;
use Tec\Base\Casts\SafeContent;
use Tec\Base\Enums\BaseStatusEnum;
use Tec\Base\Models\BaseModel;
use Tec\Base\Traits\EnumCastable;
use Tec\Revision\RevisionableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends BaseModel
{
    use RevisionableTrait;

    protected $table = 'pages';

    protected bool $revisionEnabled = true;

    protected bool $revisionCleanup = true;

    protected int $historyLimit = 20;

    protected array $dontKeepRevisionOf = ['content'];

    protected $fillable = [
        'name',
        'content',
        'image',
        'template',
        'description',
        'status',
        'user_id',
        'has_breadcrumb',
        'extra_config',
        'is_restricted',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
        'template' => SafeContent::class,
        'extra_config' => SafeContent::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }
}
