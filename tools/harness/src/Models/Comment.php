<?php
/**
 * fnlla - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */


declare(strict_types=1);

namespace App\Models;

use Fnlla\Orm\Model;
use Fnlla\Orm\Relations\BelongsTo;

final class Comment extends Model
{
    protected string $table = 'comments';

    protected array $fillable = [
        'post_id',
        'body',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
