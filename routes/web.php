<?php

use App\Http\Controllers\ContactsController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\GroupMessageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*Route::get('/', function () {
    return view('auth/login');
}); Old logic */

Route::get('/', function () {
    if (Route::has('login')) {
        if (Auth::check()) {
            return redirect('/home');
        } else {
            return view('auth/login');
        }
    } else if (Route::has('register')) {
        return view('auth/register');
    }
});

Auth::routes();

//Главная страница
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Группировка маршрутов чата и контактов для аутентифицированных пользователей
Route::middleware('auth')->group(function () {
    /* Чат */
        // Получить диалог
        Route::get('/conversation/{id}', [MessageController::class, 'getMessagesFor']);

        // Отправить сообщение
        Route::post('/conversation/send', [MessageController::class, 'send']);

        // Редактирование сообщения
        Route::patch('/conversation/message/{messageId}/edit', [MessageController::class, 'editMessage']);

        // Удаление сообщения
        Route::delete('/conversation/message/{messageId}/delete', [MessageController::class, 'deleteMessage']);

        // Прочитать сообщение
        Route::post('/conversation/{id}/read', [MessageController::class, 'markAsRead']);
    /* --- */

    /* Чат группы*/
        // Получить диалог
        Route::get('/conversation/group/{groupId}', [GroupMessageController::class, 'getMessagesForGroup']);

        // Отправить сообщение
        Route::post('/conversation/group/send', [GroupMessageController::class, 'sendGroup']);

        // Редактирование сообщения
        Route::patch('/conversation/group/message/{messageId}/edit', [GroupMessageController::class, 'editMessageInGroup']);

        // Удаление сообщения
        Route::delete('/conversation/group/message/{messageId}/delete', [GroupMessageController::class, 'deleteMessageInGroup']);
    /* --- */

    /* Контакты */
        // Получить контакты
        Route::get('/contacts', [ContactsController::class, 'get']);

        // Найти контакт
        Route::post('/contacts/search', [ContactsController::class, 'search']);

        // Удалить контакт из списка сохранённых
        Route::delete('/contacts/{contact}', [ContactsController::class, 'removeContact']);
    /* --- */

    /* Группы */
        // Создать группу
        Route::post('/groups/create', [GroupController::class, 'create']);

        // Удаление группы
        Route::delete('/groups/{group}', [GroupController::class, 'deleteGroup']);

        // Добавить пользователя в группу
        Route::post('/groups/{group}/add-user', [GroupController::class, 'addUser']);

        // Удаление пользователя из группы
        Route::delete('/groups/{group}/remove-user', [GroupController::class, 'removeUser']);

        // Получить список групп для пользователя
        Route::get('/groups', [GroupController::class, 'index']);

        // Получить список пользователей группы
        Route::get('/groups/{group}/users', [GroupController::class, 'getGroupUsers']);

        // Выйти из группы
        Route::delete('/groups/{group}/leave', [GroupController::class, 'leaveGroup']);
    /* --- */
});