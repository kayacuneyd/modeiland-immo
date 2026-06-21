<?php

namespace App\Modules\Blog\Models;

use App\Core\Models\BaseModel;

class CategoryModel extends BaseModel
{
    protected $table         = 'categories';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['name', 'slug', 'lang'];

    public function getByLang(string $lang): array
    {
        return $this->where('lang', $lang)->orderBy('name', 'ASC')->findAll();
    }
}
