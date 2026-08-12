<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\VoteType;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['title', 'type', 'description'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    /**
     * Existe só para o estado do card: fora do contexto web, `auth()->id()` é
     * nulo e a relação devolve nulo sem erro.
     */
    public function voteFromCurrentUser(): HasOne
    {
        return $this->hasOne(Vote::class)->where('user_id', auth()->id());
    }

    /**
     * Existe só para o estado do card — ver `voteFromCurrentUser()`.
     */
    public function followFromCurrentUser(): HasOne
    {
        return $this->hasOne(Follow::class)->where('user_id', auth()->id());
    }

    #[Scope]
    protected function open(Builder $query): void
    {
        $query->where('status', PostStatus::Open);
    }

    /**
     * Carrega o que o card de publicação (`x-post-card`) precisa para
     * renderizar: autor, voto/acompanhamento do usuário atual e as
     * contagens de recomendações, não recomendações e seguidores.
     */
    #[Scope]
    protected function withInteractionCounts(Builder $query): void
    {
        $query->with(['user:id,name', 'voteFromCurrentUser', 'followFromCurrentUser'])
            ->withCount([
                'votes as recommendations_count' => fn (Builder $query) => $query->where('type', VoteType::Recommend),
                'votes as not_recommendations_count' => fn (Builder $query) => $query->where('type', VoteType::NotRecommend),
                'follows as followers_count',
            ]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'status' => PostStatus::class,
            'closed_at' => 'datetime',
        ];
    }
}
