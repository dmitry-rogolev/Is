<?php 

namespace dmitryrogolev\Is\Traits;

use dmitryrogolev\Is\Helper;
use Illuminate\Database\Eloquent\Model;

/**
 * Добавляет модели функционал, облегчающий работу с аттрибутом "slug".
 */
trait HasSlug 
{
    /**
     * Имя аттрибута "slug".
     *
     * @var string
     */
    protected string $slugKey = 'slug';

    /**
     * Возвращает имя аттрибута "slug".
     *
     * @return string
     */
    public function getSlugKey(): string 
    {
        return $this->slugKey;
    }

    /**
     * Изменяет имя аттрибута "slug".
     *
     * @param string $key
     * @return static
     */
    public function setSlugKey(string $key): static 
    {
        $this->slugKey = $key;

        return $this;
    }

    /**
     * Возвращает значение аттрибута "slug".
     *
     * @return string
     */
    public function getSlug(): string 
    {
        return $this->attributes[$this->getSlugKey()];
    }

    /**
     * Изменяет значение аттрибута "slug".
     *
     * @param mixed $value
     * @return void
     */
    public function setSlug($value): void 
    {
        $this->attributes[$this->getSlugKey()] = Helper::slug($value);
    }

    /**
     * При записи аттрибута "slug" приводит его к формату slug'а.
     *
     * @param mixed $value
     * @return void
     */
    public function setSlugAttribute($value): void 
    {
        $this->attributes[$this->getSlugKey()] = Helper::slug($value);
    }

    /**
     * Возвращает модель по его slug'у.
     * 
     * @param mixed $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findBySlug($slug): Model|null
    {
        return static::where(app(static::class)->getSlugKey(), '=', Helper::slug($slug))->first();
    }
}
