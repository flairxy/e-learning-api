<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


/** @var \Laravel\Lumen\Routing\Router $router */


//<--For testing purposes
$router->get('/mail-preview', function () {
    return (new \App\Notifications\ForgotPassword(2313232))
        ->toMail(factory(\App\Models\Auth\User::class)->make());
});
//<--For testing purposes


$router->get('/', 'ExampleController@ping');

$router->group(['namespace' => 'Auth'], function () use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@registerUser');
    // $router->post('/refresh-token', 'AuthController@refreshToken');
    $router->get('settings', 'AuthController@settings');
    $router->group(['prefix' => '/password'], function () use ($router) {
        $router->post('/forgot', 'PasswordController@sendResetEmail');
        $router->post('/reset', 'PasswordController@resetPassword');
    });
});

$router->group(['middleware' => 'auth'], function () use ($router) {

    $router->post('/logout', 'Auth\AuthController@logout');
    $router->group(['namespace' => 'Account'], function () use ($router) {

        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->get('/', 'UserController@fetchUser');
            $router->patch('/', 'UserController@updateUser');
            $router->patch('/password', 'UserController@updatePassword');
            $router->delete('/', 'UserController@deleteUser');
        });
    });
});

$router->group([], function () use ($router) {
    $router->group(['namespace' => 'General'], function () use ($router) {
        $router->get('courses', 'CourseController@index');
        $router->get('courses/featured', 'CourseController@featuredCourses');
        $router->post('courses/search', 'CourseController@search');
        $router->post('courses/categories', 'CourseController@courseByCategory');
        $router->post('course/show', 'CourseController@show');
        $router->post('course/contents', 'CourseController@contents');
        $router->post('course/instructor', 'CourseController@getInstructor');

        $router->group(['prefix' => 'categories'], function () use ($router) {
            $router->get('/', 'CategoryController@index');
            $router->get('courses', 'CategoryController@categoryCourses');
            $router->group(['middleware' => 'auth'], function () use ($router) {
                $router->post('create', 'CategoryController@store');
                $router->patch('update', 'CategoryController@update');
                $router->delete('delete/{id}', 'CategoryController@destroy');
            });
        });
        $router->group(['prefix' => 'levels'], function () use ($router) {
            $router->get('/', 'LevelController@index');
            $router->group(['middleware' => 'auth'], function () use ($router) {
                $router->post('create', 'LevelController@store');
                $router->patch('update', 'LevelController@update');
                $router->delete('delete/{id}', 'LevelController@destroy');
            });
        });
        $router->group(['prefix' => 'tags'], function () use ($router) {
            $router->get('/', 'TagController@index');
            $router->group(['middleware' => 'auth'], function () use ($router) {
                $router->post('create', 'TagController@store');
                $router->patch('update', 'TagController@update');
                $router->delete('delete/{id}', 'TagController@destroy');
            });
        });

        $router->group(['prefix' => 'meetings', 'middleware' => 'auth'], function () use ($router) {
            $router->get('{id}', 'MeetingController@get');
            $router->post('create', 'MeetingController@create');
            // $router->patch('update', 'MeetingController@update');
            $router->delete('delete/{id}', 'MeetingController@delete');
        });

        $router->group(['prefix' => 'notifications', 'middleware' => 'auth'], function () use ($router) {
            $router->post('/', 'NotificationsController@index');
            // $router->post('outbox', 'NotificationsController@sent');
            // $router->post('create', 'NotificationsController@store');
            $router->patch('update', 'NotificationsController@update');
            // $router->post('delete', 'NotificationsController@destroy');
        });
    });

    $router->group(['namespace' => 'Admin', 'middleware' => 'auth'], function () use ($router) {

        $router->group(['prefix' => 'admin'], function () use ($router) {
            $router->get('settings/index', 'SettingsController@index');
            $router->post('settings', 'SettingsController@settings');


            $router->group(['prefix' => 'courses'], function () use ($router) {
                $router->get('approved', 'CourseController@approved');
                $router->get('rejected', 'CourseController@rejected');
                $router->get('pending', 'CourseController@pending');
                $router->get('deleted', 'CourseController@deleted');
                $router->patch('revert', 'CourseController@revertDelete');
                $router->patch('update', 'CourseController@update');
                $router->delete('delete/{id}', 'CourseController@destroy');
                $router->post('test-delete', 'CourseController@deleteTest');
                $router->get('featured', 'CourseController@featuredCourses');
                $router->get('top', 'CourseController@topCourses');
                $router->post('featured/create', 'CourseController@addToFeatured');
                $router->post('featured/delete', 'CourseController@removeFromFeatured');
                $router->post('top/create', 'CourseController@addToTopRated');
                $router->post('top/delete', 'CourseController@removeFromTopRated');
            });

            $router->group(['prefix' => 'users'], function () use ($router) {
                $router->get('{role}/approved', 'UsersController@approved');
                $router->get('{role}/rejected', 'UsersController@rejected');
                $router->get('{role}/pending', 'UsersController@pending');
                $router->get('{role}/deleted', 'UsersController@deleted');
                $router->patch('revert', 'UsersController@revertDelete');
                $router->patch('update', 'UsersController@update');
                $router->post('search', 'UsersController@search');
                $router->delete('delete/{id}/{type}', 'UsersController@destroy');
            });
        });
    });

    $router->group(['namespace' => 'Instructor', 'middleware' => 'auth'], function () use ($router) {

        $router->group(['prefix' => 'instructor'], function () use ($router) {

            $router->group(['prefix' => 'course'], function () use ($router) {
                $router->post('/', 'CourseController@index');
                $router->post('create', 'CourseController@store');
                $router->post('show', 'CourseController@show');
                $router->patch('update', 'CourseController@update');
                $router->patch('files/update', 'CourseController@updateCourseFile');
                $router->delete('delete/{id}/{created_by}', 'CourseController@destroy');
            });

            $router->group(['prefix' => 'section'], function () use ($router) {
                $router->post('create', 'SectionController@store');
                $router->post('show', 'SectionController@show');
                $router->get('{id}/{section}', 'SectionController@getSection');
                $router->patch('update', 'SectionController@update');
                $router->delete('delete/{id}/{course}', 'SectionController@destroy');
            });
            $router->group(['prefix' => 'content'], function () use ($router) {
                $router->post('create', 'ContentController@store');
                $router->post('show', 'ContentController@show');
                $router->patch('update', 'ContentController@update');
                $router->delete('delete/{id}/{section}/{course}', 'ContentController@destroy');
                $router->post('store', 'ContentController@storeFiles');
                $router->post('store-now', 'ContentController@testStoreFiles');
            });

            $router->group(['prefix' => 'performance'], function () use ($router) {
                $router->post('overview', 'PerformanceController@overview');
                $router->post('students', 'PerformanceController@students');
                $router->post('reviews', 'PerformanceController@reviews');
            });
        });
    });

    $router->group(['namespace' => 'Student', 'middleware' => 'auth'], function () use ($router) {

        $router->group(['prefix' => 'student'], function () use ($router) {

            $router->group(['prefix' => 'course'], function () use ($router) {
                $router->post('buy', 'CourseController@callback');
                $router->post('/', 'CourseController@index');
                $router->post('show', 'CourseController@show');
                $router->post('review', 'CourseController@review');
            });
        });
    });
});
