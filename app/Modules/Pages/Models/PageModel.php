<?php

namespace App\Modules\Pages\Models;

use App\Core\Models\BaseModel;

class PageModel extends BaseModel
{
    protected $table         = 'pages';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'title', 'slug', 'lang', 'content',
        'meta_title', 'meta_description', 'status', 'sort_order', 'media_id',
    ];

    public function findBySlugAndLang(string $slug, string $lang): ?array
    {
        return $this->where('slug', $slug)
            ->where('lang', $lang)
            ->where('deleted_at IS NULL', null, false)
            ->first();
    }

    public function getPublishedByLang(string $lang): array
    {
        return $this->where('lang', $lang)
            ->where('status', 'published')
            ->where('deleted_at IS NULL', null, false)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('title', 'ASC')
            ->findAll();
    }

    public function getAllForAdmin(): array
    {
        return $this->where('deleted_at IS NULL', null, false)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('lang', 'ASC')
            ->findAll();
    }
}
