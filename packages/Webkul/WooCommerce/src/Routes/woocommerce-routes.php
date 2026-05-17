<?php

use Illuminate\Support\Facades\Route;
use Webkul\WooCommerce\Http\Controllers\CredentialController;
use Webkul\WooCommerce\Http\Controllers\ImportMappingController;
use Webkul\WooCommerce\Http\Controllers\MappingController;
use Webkul\WooCommerce\Http\Controllers\OptionController;
use Webkul\WooCommerce\Http\Controllers\SettingController;

/**
 * WooCommerce routes.
 */
Route::group(['middleware' => ['web', 'admin', 'tenant'], 'prefix' => config('app.admin_url')], function () {
    Route::prefix('woocommerce')->group(function () {

        Route::controller(CredentialController::class)->prefix('credentials')->group(function () {
            Route::get('', 'index')->name('admin.woocommerce.credentials.index');
            Route::post('create', 'store')->name('admin.woocommerce.credentials.store');
            Route::get('edit/{id}', 'edit')->name('admin.woocommerce.credentials.edit');
            Route::put('update/{id}', 'update')->name('admin.woocommerce.credentials.update');
            Route::delete('delete/{id}', 'destroy')->name('admin.woocommerce.credentials.delete');
        });

        Route::prefix('export')->group(function () {
            Route::controller(SettingController::class)->prefix('settings')->group(function () {
                Route::get('{id}', 'index')->name('admin.woocommerce.settings');
                Route::post('create', 'store')->name('admin.woocommerce.export-settings.create');
            });
            Route::controller(MappingController::class)->prefix('mapping')->group(function () {
                Route::get('{id}', 'index')->name('admin.woocommerce.export-mappings');
                Route::post('create', 'store')->name('admin.woocommerce.export-mappings.create');
            });
        });

        Route::prefix('import')->group(function () {
            Route::controller(ImportMappingController::class)->prefix('mapping')->group(function () {
                Route::get('{id}', 'index')->name('admin.woocommerce.import-mappings');
                Route::post('create', 'store')->name('admin.woocommerce.import-mappings.create');
            });
        });

        Route::controller(OptionController::class)->group(function () {
            Route::get('get-attribute', 'listAttributes')->name('admin.woocommerce.get-attribute');
            Route::get('get-image-attribute', 'listImageAttributes')->name('admin.woocommerce.get-image-attribute');
            Route::get('get-gallery-attribute', 'listGalleryAttributes')->name('admin.woocommerce.get-gallery-attribute');
            Route::get('get-woocommerce-credentials', 'listWooCommerceCredential')->name('admin.woocommerce.credential.fetch-all');
            Route::get('get-woocommerce-channel', 'listChannel')->name('admin.woocommerce.channel.fetch-all');
            Route::get('get-woocommerce-currency', 'listCurrency')->name('admin.woocommerce.currency.fetch-all');
            Route::get('get-woocommerce-locale', 'listLocale')->name('admin.woocommerce.locale.fetch-all');
            Route::get('get-woocommerce-attrGroup', 'listAttributeGroup')->name('admin.woocommerce.attribute-group.fetch-all');
            Route::get('get-woocommerce-family', 'listWooCommerceFamily')->name('admin.woocommerce.get-all-family-variants');
        });

    });
});
