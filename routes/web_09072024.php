<?php

use App\Enums\Manuscript\Action;
use App\Enums\Manuscript\Filter;
use App\Enums\Section;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\FormatterController;
use App\Http\Controllers\ProofreaderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManuscriptController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get(uri: '', action: 'showLogin')->name('index');
        Route::post(uri: '', action: 'login');

        Route::get(uri: 'register/{author}', action: 'showRegister')->name('invite')
            ->middleware('signed');

        Route::get(uri: 'register', action: 'showRegister')->name('register');
        Route::post(uri: 'register/{author?}', action: 'register');

        Route::prefix('password')->name('password.')->middleware('guest')->group(function () {
            Route::get(uri: '', action: 'showLinkRequest')->name('forgot');
            Route::post(uri: '', action: 'sendResetLink');

            Route::get(uri: '{token}', action: 'showReset')->name('reset');
            Route::post(uri: '{token}', action: 'reset');
        });
    });

    Route::middleware('auth')->group(function () {
        Route::get(uri: 'notice', action: 'notice')->name('notice');
        Route::post(uri: 'notice', action: 'resend')->name('resend')
            ->middleware('throttle:6,1');

        Route::post(uri: 'logout', action: 'logout')->name('logout');

        Route::post(uri: 'switch/{section}', action: 'switch')->name('switch')
            ->middleware('verified:auth.notice')
            ->whereIn('section', Section::values());
    });

    Route::get(uri: 'verify/{author}/{hash}', action: 'verify')->name('verify')
        ->middleware('signed');
});

Route::get(uri: '{manuscript}/{action}/{type}/{author}', action: [ManuscriptController::class, 'respondReviewInvite'])
    ->name('manuscripts.respond-review-invite')
    ->whereIn('action', array_column([Action::ACCEPT_REVIEW_INVITE, Action::DENY_REVIEW_INVITE], 'value'))
    ->middleware('signed');

Route::middleware('auth', 'verified:auth.notice')->group(function () {
    Route::controller(HomeController::class)->group(function () {
        Route::get(uri: '', action: 'index')->name('index');

        Route::get(uri: 'profile', action: 'profile')->name('profile');
        Route::post(uri: 'profile', action: 'updateProfile');
    });

    Route::prefix('manuscripts')->name('manuscripts.')->controller(ManuscriptController::class)->group(function () {
        Route::get(uri: '{filter?}', action: 'index')
            ->name('index')
            ->whereIn('filter', Filter::values());

        Route::get(uri: '{action}', action: 'getAction')->name('edit');
        Route::post(uri: '{action}', action: 'postAction');

        Route::get(uri: '{manuscript}/{action}', action: 'getAction')
            ->name('action')
            ->whereIn('action', Action::values());

        Route::post(uri: '{manuscript}/{action}', action: 'postAction')
            ->whereIn('action', Action::values());

        Route::get(uri: '{manuscript}/{type}/{path}', action: 'manuscriptFile')
            ->name('files');

        Route::get(uri: '{manuscript}/revisions/{revision}/{type}/{path}', action: 'revisionFile')
            ->name('revisions.files');

        Route::get(uri: '{manuscript}/revisions/{revision}/reviews/{review}/{type}/{path}', action: 'reviewFile')
            ->name('revisions.reviews.files');
    });

    Route::prefix('authors')->name('authors.')->controller(AuthorController::class)->group(function () {
        Route::get(uri: '', action: 'index')->name('index');

        Route::get(uri: 'create', action: 'edit')->name('create');
        Route::post(uri: 'create', action: 'update')->name('store');

        Route::get(uri: '{author}', action: 'edit')->name('edit');
        Route::post(uri: '{author}', action: 'update')->name('update');

        Route::get(uri: '{author}/permission', action: 'permission')->name('permission');
        Route::post(uri: '{author}/permission', action: 'updatePermission');
    });
    Route::prefix('formatter')->name('formatter.')->controller(FormatterController::class)->group(function () {
        Route::get(uri: '', action: 'index')->name('index');

        Route::get(uri: 'create', action: 'edit')->name('create');
        Route::post(uri: 'create', action: 'update')->name('store');

        Route::post(uri: '{formatter}', action: 'formatPaper')->name('format-paper');

        Route::get(uri: '{formatter}/permission', action: 'permission')->name('permission');
        Route::post(uri: '{formatter}/permission', action: 'updatePermission');
    });
    Route::prefix('proofreader')->name('proofreader.')->controller(ProofreaderController::class)->group(function () {
        Route::get(uri: '', action: 'index')->name('index');

        Route::get(uri: 'create', action: 'edit')->name('create');
        Route::post(uri: 'create', action: 'update')->name('store');

        Route::post(uri: '{proofreader}', action: 'proofreaderPaper')->name('proofreader-paper');

        Route::get(uri: '{proofreader}/permission', action: 'permission')->name('permission');
        Route::post(uri: '{proofreader}/permission', action: 'updatePermission');
    });

    Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
        Route::get(uri: '', action: 'index')->name('index');

        Route::get(uri: 'create', action: 'edit')->name('create');
        Route::post(uri: 'create', action: 'update')->name('store');

        Route::get(uri: '{role}', action: 'edit')->name('edit');
        Route::post(uri: '{role}', action: 'update')->name('update');
    });
});
