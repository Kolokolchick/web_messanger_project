<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\User;
use App\Events\NewContact;
use App\Events\NewMessage;
use App\Events\MessageEdited;
use App\Events\MessageDeleted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class MessageController extends Controller
{
    //получение сообщений в историю переписки
    public function getMessagesFor($id)
    {
        $user = Auth::user();

        // Отметить как прочитанные все сообщения от выбранного контакта
        $user->receivedMessages()->where('from', $id)->update(['read' => true]);

        // Получить все сообщения между аутентифицированным пользователем и выбранным пользователем
        $messages = Message::where(function($q) use ($user, $id) {
            $q->where('from', $user->id)->where('to', $id);
        })->orWhere(function($q) use ($user, $id) {
            $q->where('from', $id)->where('to', $user->id);
        })->get();

        // Расшифровать текст каждого сообщения
        foreach ($messages as $message) {
            $message->text = Crypt::decryptString($message->text);
        }

        return response()->json($messages);
    }

    //отправка сообщения
    public function send(Request $request)
    {
        $user = Auth::user();

        // Определите ключ для счетчика сообщений пользователя
        $cacheKey = 'message_count_' . $user->id;

        // Проверьте, не превысил ли пользователь лимит отправки сообщений
        if (Cache::has($cacheKey)) {
            $messageCount = Cache::get($cacheKey);
            $messageLimit = 10; // Установите лимит на количество сообщений
            if ($messageCount >= $messageLimit) {
                return response()->json(['error' => 'вы превысили лимит отправки сообщений. Попробуйте позже.'], 403);
            }
        }

        $validatedData = $request->validate([
            'contact_id' => 'required|exists:users,id',
            'text' => 'required|string',
        ]);

        // Шифруем текст сообщения
        $encryptedText = Crypt::encryptString($validatedData['text']);

        $message = $user->sentMessages()->create([
            'to' => $validatedData['contact_id'],
            'text' => $encryptedText, // Сохраняем зашифрованный текст в базе данных
        ]);

        // Расшифровываем текст перед отправкой на фронтенд
        $message->text = Crypt::decryptString($message->text);

        // Проверяем, существует ли ключ для счетчика и последнее время сброса
        if (!Cache::has($cacheKey) || !Cache::has('last_reset_' . $user->id)) {
            // Устанавливаем счетчик в 1 и фиксируем время сброса
            Cache::put($cacheKey, 1, Carbon::now()->addMinutes(1));
            Cache::put('last_reset_' . $user->id, Carbon::now(), Carbon::now()->addMinutes(1));
        } else {
            // Получаем время последнего сброса
            $lastReset = Cache::get('last_reset_' . $user->id);
            // Проверяем, прошла ли минута с момента последнего сброса
            if (Carbon::now()->diffInMinutes($lastReset) >= 1) {
                // Сбрасываем счетчик и обновляем время сброса
                Cache::put($cacheKey, 1, Carbon::now()->addMinutes(1));
                Cache::put('last_reset_' . $user->id, Carbon::now(), Carbon::now()->addMinutes(1));
            } else {
                // Увеличиваем счетчик отправленных сообщений пользователя
                Cache::increment($cacheKey);
            }
        }

        // Добавляем контакт в список контактов отправителя
        $user->contacts()->syncWithoutDetaching([$validatedData['contact_id']]);

        // Добавляем контакт отправителя в список контактов получателя
        $contact = User::find($validatedData['contact_id']);
        $contact->contacts()->syncWithoutDetaching([$user->id]);

        // Отправляем событие о новом контакте для получателя
        broadcast(new NewContact($user, $contact->id))->toOthers();

        // Отправляем событие о новом сообщении
        broadcast(new NewMessage($message))->toOthers();

        return response()->json($message);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();

        // Отметить как прочитанные все сообщения от выбранного контакта
        $user->receivedMessages()->where('from', $id)->update(['read' => true]);

        return response()->json(['message' => 'Сообщения прочитаны']);
    }

    //редактирование сообщения
    public function editMessage(Request $request, $messageId)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'text' => 'required|string',
        ]);
    
        $message = Message::where('id', $messageId)
            ->where('from', $user->id)
            ->first();
    
        if (!$message) {
            return response()->json(['message' => 'Сообщение не найдено или у вас нет разрешения на его редактирование'], 404);
        }

        // Шифруем новый текст сообщения
        $encryptedText = Crypt::encryptString($validatedData['text']);
    
        $message->update(['text' => $encryptedText]); // Обновляем запись в базе данных с зашифрованным текстом

        // Расшифровываем текст перед отправкой на фронтенд
        $message->text = Crypt::decryptString($message->text);

        broadcast(new MessageEdited($message))->toOthers();
    
        return response()->json($message);
    }
    
    //удаление сообщения
    public function deleteMessage($messageId)
    {
        $user = Auth::user();
    
        $message = Message::where('id', $messageId)
                        ->where('from', $user->id)
                        ->first();
    
        if (!$message) {
            return response()->json(['message' => 'Сообщение не найдено или у вас нет разрешения на его удаление'], 404);
        }
    
        broadcast(new MessageDeleted($message))->toOthers();
    
        $message->delete();
    
        return response()->json(['message' => 'Сообщение удалено']);
    }
}
