<?php 

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Helper;
use Illuminate\Database\Eloquent\Model;

trait Sluggable 
{
    public function setSlugAttribute(string $value): void 
    {
        $this->attributes['slug'] = Helper::slug($value);
    }

    /**
     * Возвращаем модель по ее slug
     * 
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected static function findBySlug(string $slug): Model|null
    {
        return self::whereSlug(Helper::slug($slug))->first();
    }
}