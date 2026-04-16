<?php
/**
 * Finella - AI-assisted PHP framework.
 * (c) TechAyo.co.uk
 * Proprietary License
 */


declare(strict_types=1);

namespace App\Models;

use Finella\Orm\Model;
use Finella\Orm\Relations\HasMany;

/**
 * @property int $id
 * @property array<int, Comment>|null $comments
 */
final class Post extends Model
{
    protected string $table = 'posts';

    protected array $fillable = [
        'title',
        'body',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}
