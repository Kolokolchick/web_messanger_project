<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupMessage;
use App\Models\User;
use App\Events\NewMessageGroup;
use App\Events\MessageEditedGroup;
use App\Events\MessageDeletedGroup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class GroupMessageController extends Controller
{
    //получение сообщений в историю переписки
    public function getMessagesForGroup($groupId)
    {
        $user = Auth::user();
        $userId = $user->id;

        $userIsMember = Group::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->where('id', $groupId)->exists();

        if ($userIsMember) {
            $groupMessage = GroupMessage::where('group_id', $groupId)->get();
        }

        // Расшифровать текст каждого сообщения
        foreach ($groupMessage as $message) {
            $message->text = Crypt::decryptString($message->text);
        }

        return response()->json($groupMessage);
    }

    //отправка сообщения
    public function sendGroup(Request $request)
    {
        $user = Auth::user();
    
        // Определите ключ для счетчика сообщений пользователя
        $cacheKey = 'group_message_count_' . $user->id;
    
        // Проверьте, не превысил ли пользователь лимит отправки сообщений
        if (Cache::has($cacheKey)) {
            $messageCount = Cache::get($cacheKey);
            $messageLimit = 10; // Установите лимит на количество сообщений
            if ($messageCount >= $messageLimit) {
                return response()->json(['error' => 'вы превысили лимит отправки сообщений. Попробуйте позже.'], 403);
            }
        }
    
        $validatedData = $request->validate([
            'group_id' => 'required|exists:groups,id',
            'text' => 'required|string',
        ]);
    
        // Шифруем текст сообщения
        $encryptedText = Crypt::encryptString($validatedData['text']);
    
        $message = $user->sentMessagesToGroup()->create([
            'group_id' => $validatedData['group_id'],
            'text' => $encryptedText,
            'from_name' => $user->name,
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
    
        // Отправляем событие о новом сообщении
        broadcast(new NewMessageGroup($message))->toOthers();
    
        return response()->json($message);
    }

    public function editMessageInGroup(Request $request, $messageId)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'text' => 'required|string',
        ]);
    
        $message = GroupMessage::where('id', $messageId)
            ->where('from', $user->id)
            ->first();
    
        if (!$message) {
            return response()->json(['message' => 'Сообщение не найдено или у вас нет разрешения на его редактирование'], 404);
        }

        // Шифруем новый текст сообщения
        $encryptedText = Crypt::encryptString($validatedData['text']);
    
        $message->update(['text' => $encryptedText]);

        // Расшифровываем текст перед отправкой на фронтенд
        $message->text = Crypt::decryptString($message->text);

        broadcast(new MessageEditedGroup($message))->toOthers();
    
        return response()->json($message);
    }
    
    //удаление сообщения
    public function deleteMessageInGroup($messageId)
    {
        $user = Auth::user();
    
        $message = GroupMessage::where('id', $messageId)
                        ->where('from', $user->id)
                        ->first();
    
        if (!$message) {
            return response()->json(['message' => 'Сообщение не найдено или у вас нет разрешения на его удаление'], 404);
        }
    
        broadcast(new MessageDeletedGroup($message))->toOthers();
    
        $message->delete();
    
        return response()->json(['message' => 'Сообщение удалено']);
    }

}
