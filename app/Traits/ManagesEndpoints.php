<?php


namespace App\Traits;

use App\Models\Category;
use App\Models\Level;
use App\Models\Tag;

trait ManagesEndpoints
{
    /**
         * @var array
         * for registering routes used in RestfulController and NonRestfulController
     * routes already registered in routes/api may be registered here to perform other  non restful operations
     * for inter table queries. e.g lesson-files, courses.
     * a single model may have many routes like /lesson(Target LessonController) => registered under routes/api,
     * and /lesson(Target NonRestfulController) => registered in ManagesEndpoints trait.
     */
    protected $endpoints = [
        /**
         * RESTFUL CONTROLLER ROUTES
         * Restful operations with routes:
         * endpoint/, endpoint/id,
         * for index, store, show, update, destroy methods
         */

        'categories' => Category::class,
        'levels' => Level::class,
        'tags' => Tag::class,

    ];


    /**
     * @var array
     * set of validation rules for the store and update methods in RESTFUL CONTROLLERS
     * the array key must match the endpoint key registered in endpoints above
     * when rules are not registered for an endpoint, no validation will be performed on the
     * respective methods.
     */
    protected $rules = [
        'categories' => [
            'store' => [
                'name' => 'required',
            ],
            'update' => [
                'name' => 'required',
            ],
        ],
        'levels' => [
            'store' => [
                'name' => 'required',
            ],
            'update' => [
                'name' => 'required',
            ],
        ],
        'tags' => [
            'store' => [
                'name' => 'required',
            ],
            'update' => [
                'name' => 'required',
            ],
        ],
    ];
}
