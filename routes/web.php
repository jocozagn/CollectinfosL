<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CollaborationRequestController as AdminCollaborationController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvestigationController as AdminInvestigationController;
use App\Http\Controllers\Admin\NewsletterSubscriberController as AdminNewsletterController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PressRequestController as AdminPressRequestController;
use App\Http\Controllers\Admin\SiteProductController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SiteStatController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JournalistInvestigationController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PressController;
use Illuminate\Support\Facades\Route;

Route::post('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/nos-contenus', [ContentController::class, 'index'])->name('contents.index');
Route::get('/contenus/{slug}', [ContentController::class, 'show'])->name('contents.show');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/collaboration', [CollaborationController::class, 'index'])->name('collaboration');
Route::post('/collaboration', [CollaborationController::class, 'store'])->name('collaboration.store');

Route::get('/nos-produits', [PageController::class, 'products'])->name('products');
Route::get('/relations-presse', [PageController::class, 'press'])->name('press');
Route::post('/relations-presse', [PressController::class, 'store'])->name('press.store');
Route::get('/fact-checking', [PageController::class, 'factChecking'])->name('fact-checking');
Route::get('/panier', [CartController::class, 'index'])->name('cart');
Route::post('/panier/ajouter/{content:slug}', [CartController::class, 'add'])->name('cart.add');
Route::delete('/panier/{content:slug}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/panier/valider', [CartController::class, 'checkout'])->middleware('auth')->name('cart.checkout');

Route::get('/mon-compte', [AccountController::class, 'index'])->name('account');
Route::post('/mon-compte/connexion', [AccountController::class, 'login'])->middleware('guest')->name('account.login');
Route::post('/mon-compte/inscription', [AccountController::class, 'register'])->middleware('guest')->name('account.register');
Route::post('/mon-compte/deconnexion', [AccountController::class, 'logout'])->middleware('auth')->name('account.logout');
Route::post('/mon-compte/enquetes', [JournalistInvestigationController::class, 'store'])->middleware('auth')->name('account.investigations.store');

// Administration
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');

    Route::middleware('admin')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('contents', AdminContentController::class)->except(['show']);

        Route::get('messages', [AdminContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [AdminContactMessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [AdminContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('press-requests', [AdminPressRequestController::class, 'index'])->name('press-requests.index');
        Route::get('press-requests/{pressRequest}', [AdminPressRequestController::class, 'show'])->name('press-requests.show');
        Route::delete('press-requests/{pressRequest}', [AdminPressRequestController::class, 'destroy'])->name('press-requests.destroy');

        Route::get('collaboration', [AdminCollaborationController::class, 'index'])->name('collaboration.index');
        Route::get('collaboration/{collaboration}', [AdminCollaborationController::class, 'show'])->name('collaboration.show');
        Route::put('collaboration/{collaboration}', [AdminCollaborationController::class, 'update'])->name('collaboration.update');
        Route::delete('collaboration/{collaboration}', [AdminCollaborationController::class, 'destroy'])->name('collaboration.destroy');

        Route::get('newsletter', [AdminNewsletterController::class, 'index'])->name('newsletter.index');
        Route::get('newsletter/export', [AdminNewsletterController::class, 'export'])->name('newsletter.export');
        Route::delete('newsletter/{subscriber}', [AdminNewsletterController::class, 'destroy'])->name('newsletter.destroy');

        Route::resource('investigations', AdminInvestigationController::class)->except(['show']);

        Route::prefix('taxonomies/{kind}')->where(['kind' => 'categories|themes|types'])->name('taxonomies.')->group(function () {
            Route::get('/', [TaxonomyController::class, 'index'])->name('index');
            Route::get('/create', [TaxonomyController::class, 'create'])->name('create');
            Route::post('/', [TaxonomyController::class, 'store'])->name('store');
            Route::get('/{taxonomy}/edit', [TaxonomyController::class, 'edit'])->name('edit');
            Route::put('/{taxonomy}', [TaxonomyController::class, 'update'])->name('update');
            Route::delete('/{taxonomy}', [TaxonomyController::class, 'destroy'])->name('destroy');
        });

        Route::resource('site-stats', SiteStatController::class)->except(['show']);

        Route::resource('partners', PartnerController::class)->except(['show']);
        Route::resource('products', SiteProductController::class)->except(['show']);
        Route::get('settings/contact', [SiteSettingController::class, 'editContact'])->name('settings.contact');
        Route::put('settings/contact', [SiteSettingController::class, 'updateContact'])->name('settings.contact.update');
        Route::get('settings/press', [SiteSettingController::class, 'editPress'])->name('settings.press');
        Route::put('settings/press', [SiteSettingController::class, 'updatePress'])->name('settings.press.update');
        Route::get('settings/fact-checking', [SiteSettingController::class, 'editFactChecking'])->name('settings.fact-checking');
        Route::put('settings/fact-checking', [SiteSettingController::class, 'updateFactChecking'])->name('settings.fact-checking.update');
    });
});
