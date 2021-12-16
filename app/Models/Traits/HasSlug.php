<?php

namespace App\Models\Traits;

use Illuminate\Database\Query\Builder;
use function abort;
use Illuminate\Support\Str;


trait HasSlug
{
    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            $model->slug = self::makeUniqueSlug($model->name);
            return true;
        });
    }

    /**
     * @param $slug
     * @param $pattern
     *
     * @return Builder
     */
    public static function findBySlug($slug, $pattern = false)
    {
        if ($pattern) {
            return self::where('slug', 'LIKE', $slug);
        }

        return self::where('slug', $slug);
    }

    /**
     *
     * @param type $slug
     * @param type $pattern
     * @return Builder
     */
    public static function findBySlugOrFail($slug, $pattern = false)
    {
        $builder = self::findBySlug($slug, $pattern);
        $q = clone $builder;
        if (!$q->count()) {
            abort(404);
        }
        return $builder;
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @param $title
     *
     * @return string
     */
    public static function makeUniqueSlug($title, $old = null)
    {
        $slug = self::makeSlug($title);
        if ($slug !== $old) {
            $builder = self::findBySlug($slug . '%', true);
            //Count all, including trashed items for soft delete models
            try {
                $matches = $builder->withTrashed()->count();
            } catch (\Exception $e) {
                $matches = $builder->count();
            }

            if ($matches) {
                $temp = $slug . '-' . $matches;
                if (is_object(self::findBySlug($temp)->first())) {
                    $slug .= '-' . uniqid();
                } else {
                    $slug = $temp;
                }
            }
        }
        return $slug;
    }

    /**
     * @param $string
     *
     * @return string
     */
    public static function makeSlug($string)
    {
        $slug = Str::slug($string);

        /* UTF8 requires 3 bytes per character to store the string,
         * so in your case 20 + 500 characters = 20*3+500*3 = 1560 bytes which is
         * more than allowed 767 bytes. The limit for UTF8 is 767/3 = 255 characters,
         * for UTF8mb4 which uses 4 bytes per character it is 767/4 = 191 characters
         *
         * We offset 1 character to use 190 characters, just because we can
         *
         * Since we're hoping to carry 30 million users (and more), we can imagine everyone
         * chose the same slug
         * extimated_max_slug_occurrence = 30,000,000
         *
         * For suffix, it come with an hyphen and either a uniqid() or number of occurence
         * len_of_uniqid = strlen(uniqid()) = 13
         * len_of_ext_max_slug_occurrence = strlen(extimated_max_slug_occurrence) = 8
         * len_of_hyphen = strlen('-') = 1
         *
         * suffix_length = max(len_of_uniqid,len_of_ext_max_slug_occurrence) + len_of_hyphen = 14
         *
         * Therefore, allowed slug length (without suffix) is 190 - 14 = 176
         */

        return substr($slug, 0, 176);
    }
}
