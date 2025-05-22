<?php
use App\Http\Controllers\AboutController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\TestimonioController;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomPackageController;
use App\Http\Controllers\EntrepreneurCategoryController;
use App\Http\Controllers\EntrepreneurController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomepageSettingController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

use Illuminate\Support\Facades\Route;

// Autenticación
Route::middleware([EnsureFrontendRequestsAreStateful::class])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/sanctum/csrf-cookie', function () {
        return response()->json(['message' => 'CSRF cookie']);
    });
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:sanctum')->get('/user/{id}/roles', [AuthController::class, 'getUserRole']);

// Tours
Route::apiResource('tours', TourController::class);

// Reservas
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'userReservations']);
    Route::get('/entrepreneur/{entrepreneurId}/reservations', [ReservationController::class, 'entrepreneurReservations']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
});
Route::get('/reservations/{id}', [ReservationController::class, 'show']);
Route::get('/reservations', [ReservationController::class, 'index']);
Route::get('reservations/count', [ReservationController::class, 'count']);
Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);

// Pagos
Route::middleware('auth:sanctum')->get('/payments/for-entrepreneur', [PaymentController::class, 'indexForEntrepreneur']);

Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);

// Página principal
Route::get('/home', [HomepageSettingController::class, 'active']);
Route::get('/home/public', [HomepageSettingController::class, 'public']);
Route::get('/home/all', [HomepageSettingController::class, 'index']);
Route::get('/home/active', [HomepageSettingController::class, 'active']);
Route::put('/home/{id}', [HomepageSettingController::class, 'update']);
Route::delete('/home/{id}', [HomepageSettingController::class, 'destroy']);
Route::post('/home/{id}/activate', [HomepageSettingController::class, 'activate']);
Route::post('/home', [HomepageSettingController::class, 'store']);
Route::post('/home/remove-image', [HomepageSettingController::class, 'removeImage']);
Route::put('/home/update', [HomepageSettingController::class, 'update']);
Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::get('/homepage-settings', [HomepageSettingController::class, 'show']);
    Route::post('/homepage-settings', [HomepageSettingController::class, 'update']);
});

// About
Route::get('/abouts', [AboutController::class, 'index']);
Route::post('/abouts', [AboutController::class, 'store']);
Route::put('/abouts/{id}', [AboutController::class, 'update']);
Route::delete('/abouts/{id}', [AboutController::class, 'destroy']);
Route::post('/abouts/{about}/activate', [AboutController::class, 'activate']);
Route::get('/abouts/active', [AboutController::class, 'active']);

// Contacto
Route::get('/contact', [ContactController::class, 'index']);
Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::post('/contact', [ContactController::class, 'store']);
    Route::put('/contact/{id}', [ContactController::class, 'update']);
});

// Galería
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('gallery', GalleryController::class);
});
Route::middleware(['auth:sanctum', 'role:super-admin'])->group(function () {
    Route::delete('/gallery/{id}', [GalleryController::class, 'destroy']);
});

// Lugares
Route::apiResource('places', PlaceController::class);
Route::get('places/count', [PlaceController::class, 'count']);

// Testimonios
Route::get('testimonios', [TestimonioController::class, 'index']);
Route::post('testimonios', [TestimonioController::class, 'store']);

// Experiencias
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('experiences', ExperienceController::class);
});
Route::get('experiences', [ExperienceController::class, 'index']);
Route::post('experiences', [ExperienceController::class, 'store']);
Route::put('experiences/{id}', [ExperienceController::class, 'update']);
Route::delete('experiences/{id}', [ExperienceController::class, 'destroy']);

// Emprendedores
Route::apiResource('entrepreneurs', EntrepreneurController::class);
Route::get('entrepreneurs/category/{categoryId}', [EntrepreneurController::class, 'byCategory']);
Route::get('entrepreneurs/{entrepreneur}/history', [EntrepreneurController::class, 'history']);
Route::get('/entrepreneurs/count', [EntrepreneurController::class, 'count']);
Route::put('/entrepreneurs/{entrepreneur}/toggle-status', [EntrepreneurController::class, 'toggleStatus']);
Route::get('/entrepreneurs/{entrepreneur_id}/categories', [EntrepreneurController::class, 'getCategories']);
Route::middleware('auth:sanctum')->post('/entrepreneurs', [EntrepreneurController::class, 'store']);
Route::middleware('auth:sanctum')->get('/entrepreneur/authenticated', [EntrepreneurController::class, 'showAuthenticatedEntrepreneur']);

// Asociaciones y Categorías
Route::apiResource('associations', AssociationController::class);
Route::get('/associations/count', [AssociationController::class, 'count'])->middleware('auth:sanctum');

Route::apiResource('categories', CategoryController::class);
Route::get('categories/count', [CategoryController::class, 'count']);

Route::apiResource('entrepreneur-categories', EntrepreneurCategoryController::class)
     ->only(['index', 'store', 'show', 'destroy']);

// Productos
Route::middleware('auth:sanctum')->group(function () {
    Route::get('products/my', [ProductController::class, 'myProducts']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::resource('products', ProductController::class)->only(['store', 'update', 'destroy']);
});

// Dashboard Admin
Route::middleware(['auth:sanctum', 'can:access-admin'])->get('/admin/dashboard-counts', [EntrepreneurController::class, 'counts']);


Route::get('/payments', [PaymentController::class, 'index']);
Route::post('/payments', [PaymentController::class, 'store']);
Route::get('/payments/{id}', [PaymentController::class, 'show']);
Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm']);
Route::post('/payments/{id}/reject', [PaymentController::class, 'reject']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json($request->user()); // ← SIN relaciones
});
Route::middleware('auth:sanctum')->put('/me/update', [AuthController::class, 'update']);

Route::get('/mi-ruta', function (Request $request) {
    $user = $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->get('/my-reservations', [ReservationController::class, 'userReservations']);
Route::middleware('auth:sanctum')->get('/entrepreneur/authenticated', [EntrepreneurController::class, 'showAuthenticatedEntrepreneur']);

Route::get('/entrepreneurs/{entrepreneur}', [EntrepreneurController::class, 'show']);
Route::get('entrepreneurs/{entrepreneur}/history', [EntrepreneurController::class, 'history']);

Route::get('/homepage-setting/active', [HomepageSettingController::class, 'active']);
Route::get('/about', [HomepageSettingController::class, 'active']);
Route::middleware('auth:sanctum')->get('/reservations/by-client', function () {
    return auth()->user()->reservations()->with('product')->get();
});

Route::post('/reservations/direct-sale', [ReservationController::class, 'directSale']);
Route::middleware(['auth:sanctum', 'role:super-admin|emprendedor'])->get('/users/search', [AuthController::class, 'search']);
Route::middleware('auth:sanctum')->get('/packages/my', [PackageController::class, 'myPackages']);
// ✅ Primero la ruta específica
Route::get('/users/search', [AuthController::class, 'search']);

// Luego la genérica
Route::get('/users/{id}', [AuthController::class, 'show']);
Route::get('/users/search', [AuthController::class, 'search']); // sin middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('packages', PackageController::class);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('custom-packages', [CustomPackageController::class, 'index']);
    Route::get('custom-packages/{id}', [CustomPackageController::class, 'show']);
    Route::post('custom-packages', [CustomPackageController::class, 'store']);
    Route::put('custom-packages/{id}', [CustomPackageController::class, 'update']);
    Route::delete('custom-packages/{id}', [CustomPackageController::class, 'destroy']);
});
Route::post('/reservations/direct-sale', [ReservationController::class, 'directSale']);
Route::get('/reservations/emprendedor/{id}', [ReservationController::class, 'entrepreneurReservations']);
Route::middleware('auth:sanctum')->get('/products', [ProductController::class, 'index']);
Route::put('/reservations/{id}', [ReservationController::class, 'update']);
Route::middleware('auth:sanctum')->get('/custom-packages', [CustomPackageController::class, 'index']);
