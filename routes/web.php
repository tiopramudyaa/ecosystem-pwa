<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('lite_api.auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->whereNumber('id')->name('tickets.show');
    Route::patch('/tickets/{id}/status', [TicketController::class, 'updateStatus'])->whereNumber('id')->name('tickets.status');
    Route::post('/tickets/{id}/messages', [TicketController::class, 'storeMessage'])->whereNumber('id')->name('tickets.messages.store');
    Route::get('/tickets/{id}/messages/poll', [TicketController::class, 'pollMessages'])->whereNumber('id')->name('tickets.messages.poll');
    Route::get('/employees/mentionable', [TicketController::class, 'mentionable'])->name('employees.mentionable');
    Route::post('/tickets/{id}/messages/{messageId}/internal-note', [TicketController::class, 'updateNote'])->whereNumber(['id', 'messageId'])->name('tickets.messages.note.update');
    Route::delete('/tickets/{id}/messages/{messageId}/internal-note', [TicketController::class, 'destroyNote'])->whereNumber(['id', 'messageId'])->name('tickets.messages.note.destroy');
    Route::get('/tickets/attachments/{attachmentId}', [TicketController::class, 'attachment'])->whereNumber('attachmentId')->name('tickets.attachments.show');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::delete('/notifications/bulk-delete', [NotificationController::class, 'bulkDelete'])->name('notifications.bulk-delete');
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id')->name('notifications.read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->whereNumber('id')->name('notifications.destroy');

    Route::post('/push-subscriptions', [NotificationController::class, 'pushSubscribe'])->name('push-subscriptions.store');
});
