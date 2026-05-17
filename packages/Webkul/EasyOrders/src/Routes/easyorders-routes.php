<?php

use Illuminate\Support\Facades\Route;
use Webkul\EasyOrders\Http\Controllers\CredentialController;
use Webkul\EasyOrders\Http\Controllers\ImportMappingController;
use Webkul\EasyOrders\Http\Controllers\MappingController;
use Webkul\EasyOrders\Http\Controllers\OptionController;
use Webkul\EasyOrders\Http\Controllers\SettingController;

/**
 * EasyOrders routes.
 */
Route::group(['middleware' => ['web', 'admin', 'tenant'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('easyorders')->group(function () {

        Route::controller(CredentialController::class)->prefix('credentials')->group(function () {
            Route::get('', 'index')->name('admin.easyorders.credentials.index');
            Route::post('create', 'store')->name('admin.easyorders.credentials.store');
            Route::get('edit/{id}', 'edit')->name('admin.easyorders.credentials.edit');
            Route::put('update/{id}', 'update')->name('admin.easyorders.credentials.update');
            Route::delete('delete/{id}', 'destroy')->name('admin.easyorders.credentials.delete');
        });

        Route::prefix('export')->group(function () {
            Route::controller(SettingController::class)->prefix('settings')->group(function () {
                Route::get('{id}', 'index')->name('admin.easyorders.settings');
                Route::post('create', 'store')->name('admin.easyorders.export-settings.create');
            });
            Route::controller(MappingController::class)->prefix('mapping')->group(function () {
                Route::get('{id}', 'index')->name('admin.easyorders.export-mappings');
                Route::post('create', 'store')->name('admin.easyorders.export-mappings.create');
            });
        });

        Route::prefix('import')->group(function () {
            Route::controller(ImportMappingController::class)->prefix('mapping')->group(function () {
                Route::get('{id}', 'index')->name('admin.easyorders.import-mappings');
                Route::post('create', 'store')->name('admin.easyorders.import-mappings.create');
            });
        });

        Route::controller(OptionController::class)->group(function () {
            Route::get('get-attribute', 'listAttributes')->name('admin.easyorders.get-attribute');
            Route::get('get-image-attribute', 'listImageAttributes')->name('admin.easyorders.get-image-attribute');
            Route::get('get-gallery-attribute', 'listGalleryAttributes')->name('admin.easyorders.get-gallery-attribute');
            Route::get('get-easyorders-credentials', 'listEasyOrdersCredential')->name('admin.easyorders.credential.fetch-all');
            Route::get('get-easyorders-channel', 'listChannel')->name('admin.easyorders.channel.fetch-all');
            Route::get('get-easyorders-currency', 'listCurrency')->name('admin.easyorders.currency.fetch-all');
            Route::get('get-easyorders-locale', 'listLocale')->name('admin.easyorders.locale.fetch-all');
            Route::get('get-easyorders-attrGroup', 'listAttributeGroup')->name('admin.easyorders.attribute-group.fetch-all');
            Route::get('get-easyorders-family', 'listEasyOrdersFamily')->name('admin.easyorders.get-all-family-variants');
        });

    });
});
