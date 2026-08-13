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
     * Contagens de recomendações, não recomendações e acompanhamentos.
     */
    #[Scope]
    protected function withInteractionCounts(Builder $query): void
    {
        $query->withCount([
            'votes as recommendations_count' => fn (Builder $query) => $query->where('type', VoteType::Recommend),
            'votes as not_recommendations_count' => fn (Builder $query) => $query->where('type', VoteType::NotRecommend),
            'follows as followers_count',
        ]);
    }

    /**
     * O que o card de publicação (`x-post-card`) precisa além das contagens:
     * autor e o voto/acompanhamento do usuário atual.
     */
    #[Scope]
    protected function withCardRelations(Builder $query): void
    {
        $query->with(['user:id,name', 'voteFromCurrentUser', 'followFromCurrentUser']);
    }

    /**
     * Diz à view se o botão de excluir pode ficar habilitado. Repete a
     * comparação do `PostService::delete` em vez de olhar `followers_count`:
     * a regra é "interação de outro usuário", e ela precisa continuar correta
     * se o autor voltar a poder votar na própria publicação.
     */
    #[Scope]
    protected function withThirdPartyInteraction(Builder $query): void
    {
        $query->withExists([
            'votes as has_third_party_votes' => fn (Builder $query) => $query->whereColumn('votes.user_id', '!=', 'posts.user_id'),
            'follows as has_third_party_follows' => fn (Builder $query) => $query->whereColumn('follows.user_id', '!=', 'posts.user_id'),
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
