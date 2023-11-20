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
     * @param mixed $name
     * @return void
     */
    public function setSlugKey(mixed $keyName): void;

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
    public function setSlug(mixed $value): void;

    /**
     * При записи аттрибута "slug" приводит его к формату slug'а.
     *
     * @param mixed $value
     * @return void
     */
    public function setSlugAttribute(mixed $value): void;

    /**
     * Возвращает модель по его slug'у.
     * 
     * @param mixed $slug
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findBySlug(mixed $slug): Model|null;
}
