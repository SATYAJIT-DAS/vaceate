<?php

namespace Cypretex\Chat\Models;

use Cypretex\Chat\Models\Message;
use Cypretex\Chat\Models\Conversation;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageNotification extends \Cypretex\Chat\BaseModel {

    use SoftDeletes;

    protected $fillable = ['id', 'user_id', 'message_id', 'conversation_id'];
    protected $table = 'mc_message_notification';
    protected $dates = ['deleted_at'];

    /**
     * Creates a new notification.
     *
     * @param Message      $message
     * @param Conversation $conversation
     */
    public static function make(Message $message, Conversation $conversation) {
        self::createCustomNotifications($message, $conversation);
    }

    public function unReadNotifications($user) {
        return MessageNotification::where([
                    ['user_id', '=', $user->id],
                    ['is_seen', '=', 0]
                ])->get();
    }

    public static function createCustomNotifications($message, $conversation) {
        $notification = [];

        foreach ($conversation->users as $user) {
            $is_sender = ($message->user_id == $user->id) ? 1 : 0;

            $notification[] = [
                'id'=> \Ramsey\Uuid\Uuid::uuid4(),
                'user_id' => $user->id,
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'is_seen' => $is_sender,
                'is_sender' => $is_sender,
                'created_at' => $message->created_at,
            ];
        }

        self::insert($notification);
    }

    public function markAsRead() {
        $this->is_seen = 1;
        $this->update(['is_seen' => 1]);
        $this->save();
    }

}
