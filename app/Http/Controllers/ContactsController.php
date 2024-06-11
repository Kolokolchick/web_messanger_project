<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContactsController extends Controller
{
    //получение контакта
    public function get()
    {
        $user = Auth::user();

        // Получаем контакты текущего пользователя
        $contacts = $user->contacts;

        // Получаем список ID контактов текущего пользователя
        $contactIds = $contacts->pluck('id');

        // Получаем коллекцию элементов, где sender_id - это пользователь, который отправил нам сообщение,
        // а messages_count - это количество непрочитанных сообщений от него
        $unreadIds = Message::select(\DB::raw('`from` as sender_id, count(`from`) as messages_count'))
            ->whereIn('from', $contactIds)
            ->where('to', $user->id)
            ->where('read', false)
            ->groupBy('from')
            ->get();

        // Добавляем ключ unread к каждому контакту с количеством непрочитанных сообщений
        $contacts = $contacts->map(function($contact) use ($unreadIds) {
            $contactUnread = $unreadIds->where('sender_id', $contact->id)->first();

            $contact->unread = $contactUnread ? $contactUnread->messages_count : 0;

            return $contact;
        });

        return response()->json($contacts);
    }

    //поиск контакта
    public function search(Request $request)
    {
        // Это предполагает использование локального скоупа в модели User
        $query = $request->input('query');

        $contacts = User::query()->search($query)->get();

        return response()->json($contacts);
    }

    // Удаление контакта из списка "добавленных контактов"
    public function removeContact($contactId)
    {
        Log::notice('removeContact',['$contactId'=>$contactId]);
        $user = Auth::user();

        // Удаляем контакт из списка
        $user->contacts()->detach($contactId);

        return response()->json(['message' => 'Контакт был удалён из списка']);
    }

}