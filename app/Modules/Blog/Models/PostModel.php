<?php

namespace App\Modules\Blog\Models;

use App\Core\Models\BaseModel;

class PostModel extends BaseModel
{
    protected $table         = 'posts';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title', 'slug', 'lang', 'excerpt', 'content',
        'category_id', 'media_id', 'status', 'published_at',
        'meta_title', 'meta_description',
    ];

    public function getPublishedByLang(string $lang, int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;

        return $this->select('posts.*, categories.name as category_name')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.lang', $lang)
            ->where('posts.status', 'published')
            ->where('posts.deleted_at IS NULL', null, false)
            ->orderBy('posts.published_at', 'DESC')
            ->limit($perPage, $offset)
            ->findAll();
    }

    public function countPublishedByLang(string $lang): int
    {
        return (int) $this->where('lang', $lang)
            ->where('status', 'published')
            ->where('deleted_at IS NULL', null, false)
            ->countAllResults();
    }

    public function findPublishedBySlug(string $slug, string $lang): ?array
    {
        return $this->select('posts.*, categories.name as category_name')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.slug', $slug)
            ->where('posts.lang', $lang)
            ->where('posts.status', 'published')
            ->where('posts.deleted_at IS NULL', null, false)
            ->first();
    }

    public function getAdjacent(int $id, string $lang): array
    {
        $current = $this->find($id);
        if (! $current) {
            return ['prev' => null, 'next' => null];
        }

        $prev = $this->where('lang', $lang)
            ->where('status', 'published')
            ->where('deleted_at IS NULL', null, false)
            ->where('published_at <', $current['published_at'])
            ->orderBy('published_at', 'DESC')
            ->first();

        $next = $this->where('lang', $lang)
            ->where('status', 'published')
            ->where('deleted_at IS NULL', null, false)
            ->where('published_at >', $current['published_at'])
            ->orderBy('published_at', 'ASC')
            ->first();

        return ['prev' => $prev, 'next' => $next];
    }

    public function getAllForAdmin(): array
    {
        return $this->select('posts.*, categories.name as category_name')
            ->join('categories', 'categories.id = posts.category_id', 'left')
            ->where('posts.deleted_at IS NULL', null, false)
            ->orderBy('posts.created_at', 'DESC')
            ->findAll();
    }
}
