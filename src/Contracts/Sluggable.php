<?php 

namespace dmitryrogolev\Is\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Добавляет модели функционал, облегчающий работу с аттрибутом "slug".
 */
interface Sluggable 
{
    /**
     * Возвращает имя аттрибута "slug".
     *
     * @return string
     */
    public function getSlugKey(): string;

    /**
     * Изменяет имя аттрибута "slug".
     *
     * @param string $key
     * @return static
     */
    public function setSlugKey(string $key): static;

    /**
     * Возвращает значение аттрибута "slug".
     *
     * @return string
     */
    public function getSlug(): string;

    /**
     * Изменяет значение аттрибута "slug".
     *
     * @param mixed $value
     * @return void
     */
    public function setSlug($value): void;

    /**
     * При записи аттрибута "slug" приводит его к формату slug'а.
     *
     * @param mixed $value
     * @return void
     */
    public function setSlugAttribute($value): void;

    /**
     * Возвращает модель по его slug'у.
     * 
     * @param mixed $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findBySlug($slug): Model|null;
}
