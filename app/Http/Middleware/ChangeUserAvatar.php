<?php

namespace App\Http\Middleware;

use App\Exceptions\MessageResponseBody;
use App\Models\StorageTask;
use App\Models\User;
use App\Models\UserProfileSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChangeUserAvatar
{
    /**
     * 修改用户头像中间件入口.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $storage_task_id = $request->input('storage_task_id');
        if (!$storage_task_id) {
            return $next($request);
        }

        return $this->storageTaskExiste($storage_task_id, $request, $next);
    }

    /**
     * 先查储存任务是否存在.
     *
     * @param int|string $storage_task_id 任务ID
     * @param Request    $request
     * @param Closure    $next
     *
     * @return mixed
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function storageTaskExiste($storage_task_id, Request $request, Closure $next)
    {
        $task = StorageTask::find($storage_task_id);
        $task->load('storage');
        if (!$task) {
            return app(MessageResponseBody::class, [
                'code' => 2000,
            ]);
        }

        $user = $request->attributes->get('user');

        // 开启事务.
        DB::beginTransaction();

        return $this->userProfileExiste($user, $task, function () use ($request, $next) {
            $response = $next($request);
            if ($response instanceof MessageResponseBody) {
                if (!$response->getBody()['status']) {
                    DB::rollBack();

                    return $response;
                }

                DB::commit();

                return $response;
            }
        });
    }

    /**
     * 检查用户拓展字段是否存在.
     *
     * @param User        $user 用户模型
     * @param StorageTask $task 任务模型
     * @param Closure     $next
     *
     * @return mixed
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function userProfileExiste(User $user, StorageTask $task, Closure $next)
    {
        $profile = UserProfileSetting::where('profile', 'avatar')->first();
        if (!$profile) {
            return app(MessageResponseBody::class, [
                'code'    => 1017,
                'message' => '系统错误',
            ]);
        }

        return $this->linkStorage($user, $task, $profile, $next);
    }

    /**
     * 插入储存link.
     *
     * @param User               $user    用户模型
     * @param StorageTask        $task    储存任务模型
     * @param UserProfileSetting $profile 用户字段模型
     * @param Closure            $next
     *
     * @return mixed
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function linkStorage(User $user, StorageTask $task, UserProfileSetting $profile, Closure $next)
    {
        $storage = $task->storage;
        if (!$storage) {
            return app(MessageResponseBody::class, [
                'code' => 2004,
            ]);
        }

        $user->storages()->sync([$storage->id], false);
        $task->delete();

        return $this->setUserProfile($user, $profile->id, $storage->id, $next);
    }

    /**
     * 保存用户头像信息.
     *
     * @param User    $user      用户模型
     * @param int     $profileId 字段id
     * @param int     $storageId 储存ID
     * @param Closure $next
     *
     * @return mixed
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function setUserProfile(User $user, int $profileId, int $storageId, Closure $next)
    {
        $data = [
            $profileId => $storageId,
        ];
        $user->syncData($data);

        return $next();
    }
}