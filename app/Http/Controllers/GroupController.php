<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Events\RemoveUserInGroup;
use App\Events\AddUserInGroup;
use Illuminate\Support\Facades\Crypt;
use App\Events\NewMessageGroup;

class GroupController extends Controller
{
    /**
     * Создание новой группы.
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $group = new Group();
        $group->name = $request->name;
        $group->created_by = $user->id;
        $group->save();

        // Автоматически добавляем создателя в группу
        $group->users()->attach($user->id);

        return response()->json(['message' => 'Группа создана', 'group' => $group]);
    }

    /**
     * Добавление пользователя в группу.
     */
    public function addUser(Request $request, $groupId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $group = Group::findOrFail($groupId);
        $user = User::findOrFail($request->user_id);

        // Проверка, что текущий пользователь является участником группы
        if (!Auth::user()->groups->contains($group)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Проверка на наличие пользователя в группе
        if ($group->users->contains($user)) {
            return response()->json(['message' => 'Пользователь уже в группе'], 400);
        }

        $group->users()->attach($user->id);

        broadcast(new AddUserInGroup($user->id, $group->name))->toOthers();

        //$this->groupNotice($groupId, $user->name ,$isAdded = true, $isRemoved = false);

        return response()->json(['message' => 'Пользователь добавлен в группу']);
    }

    /**
     * Получение списка всех групп пользователя.
     */
    public function index()
    {
        $user = Auth::user();
        $groups = $user->groups;

        return response()->json($groups);
    }

    /**
     * Получение списка пользователей конкрентной группы.
     */
    public function getGroupUsers($groupId)
    {
        $user = Auth::user();
        $group = Group::with('users')->findOrFail($groupId);
        return response()->json($group->users);
    }

    /**
     * Удаление пользователя из группы.
     */
    public function removeUser(Request $request, $groupId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $group = Group::findOrFail($groupId);
        $user = User::findOrFail($request->user_id);

        // Проверка, что текущий пользователь является создателем группы
        if (Auth::id() !== $group->created_by) {
            return response()->json(['message' => 'Пользователь не является создателем группы'], 403);
        } 
        else if ($request->user_id == $group->created_by) { // Если создатель группы пытается удалить себя
            return response()->json(['message' => 'Вы не можете удалить себя из группы, пока не удалите группу'], 403);
        }

        // Удаление пользователя из группы
        $group->users()->detach($user->id);

        broadcast(new RemoveUserInGroup($user->id, $groupId, $group->name))->toOthers();

        //$this->groupNotice($groupId, $user->name, $isAdded = false, $isRemoved = true);

        return response()->json(['message' => 'Пользователь был удалён из группы']);
    }

    public function leaveGroup(Request $request, $groupId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $group = Group::findOrFail($groupId);
        $user = User::findOrFail($request->user_id);

        // Проверка, что текущий пользователь НЕ является создателем группы
        if (Auth::id() == $group->created_by) {
            return response()->json(['message' => 'Вы не можете удалить себя из группы, пока не удалите группу'], 403);
        }

        // Удаление пользователя из группы
        $group->users()->detach($user->id);

        return response()->json(['message' => 'Вы покинули группу']);
    }

    /**
     * Удаление группы.
     */
    public function deleteGroup($groupId)
    {
        $group = Group::findOrFail($groupId);

        // Проверка, что текущий пользователь является создателем группы
        if (Auth::id() !== $group->created_by) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Удаление группы и всех связей с пользователями
        $group->users()->detach();
        $group->delete();

        return response()->json(['message' => 'Группа удалена']);
    }

    /*public function groupNotice ($groupId, $userName ,$isAdded, $isRemoved)
    {
        if ($isAdded == true) {
            $user = User::findOrFail(Auth::id());
            // Шифруем текст сообщения
            $encryptedText = "Пользователь {$userName} был добавлен в группу.";
            $encryptedText = Crypt::encryptString($encryptedText);
            $message = $user->sentMessagesToGroup()->create([
                'from_name' => $user->name,
                'group_id' => $groupId,
                'text' => $encryptedText
            ]);

            broadcast(new NewMessageGroup($message))->toOthers();
        } else if ($isRemoved == true) {
            $user = User::findOrFail(Auth::id());
            // Шифруем текст сообщения
            $encryptedText = "Пользователь {$userName} был удалён из группы.";
            $encryptedText = Crypt::encryptString($encryptedText);
            $message = $user->sentMessagesToGroup()->create([
                'from_name' => $user->name,
                'group_id' => $groupId,
                'text' => $encryptedText
            ]);

            // Отправляем событие о новом сообщении
            broadcast(new NewMessageGroup($message))->toOthers();
        }
    }*/
}
