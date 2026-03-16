<?php

namespace App\Http\Controllers\Communication;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Services\CommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    public function __construct(private CommunicationService $commService) {}

    /**
     * Inbox — list all threads for the current user.
     * Superadmin sees ALL threads in audit mode.
     */
    public function inbox()
    {
        $user = Auth::user();

        if ($user->role === 'superadmin') {
            // Audit mode: all root threads
            $threads = Message::threads()
                              ->with(['sender', 'receiver', 'replies.sender'])
                              ->orderByDesc('updated_at')
                              ->paginate(30);
            $isAudit = true;
        } else {
            $threads = $this->commService->getInboxForUser($user);
            $isAudit = false;
        }

        // Sidebar: users the current user can message (empty for students)
        $messageableUsers = $this->commService->getMessageableUsers($user);

        return view('communication.messaging.inbox', compact('threads', 'isAudit', 'messageableUsers'));
    }

    /**
     * Show a single thread with all replies.
     */
    public function thread(int $id)
    {
        $user = Auth::user();
        
        // Find the specific message first (could be a reply)
        $message = Message::find($id);
        abort_if(!$message, 404, 'Pesan tidak ditemukan.');
        
        // Always operate on the thread root for the view
        $rootId = $message->parent_id ?? $message->id;
        $thread = $this->commService->getThread($rootId);
        
        abort_if(!$thread, 404, 'Percakapan tidak ditemukan.');
        
        // Authorization: Participant check or Administrative role
        // We use != for type-safe loose comparison (handling string/int IDs)
        if ($user->role !== 'superadmin') {
            $isParticipant = ($thread->sender_id == $user->id || $thread->receiver_id == $user->id);
            abort_if(!$isParticipant, 403, 'Anda tidak memiliki akses ke percakapan ini.');
        }

        // Mark as read
        $this->commService->markThreadRead($user, $rootId);

        $canReply = Gate::check('reply', $thread);

        return view('communication.messaging.thread', compact('thread', 'canReply'));
    }

    /**
     * Send a new message (or reply).
     * - New thread: receiver_ids[] (array) — creates one private thread per recipient
     * - Reply: receiver_id (single) + parent_id — appends to existing thread
     * Students cannot initiate (parent_id must be present if role=student).
     */
    public function send(Request $request)
    {
        $user = Auth::user();

        // Reply to an existing thread (single receiver)
        if ($request->filled('parent_id')) {
            $validated = $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'body'        => 'required|string|max:5000',
                'parent_id'   => 'required|exists:messages,id',
            ]);

            // Students can only reply
            if ($user->role === 'student' && empty($validated['parent_id'])) {
                abort(403, 'Siswa tidak dapat memulai percakapan baru.');
            }

            $receiver = User::findOrFail($validated['receiver_id']);
            if ($receiver->role === 'student' && $user->role === 'student') {
                abort(403, 'Siswa tidak dapat mengirim pesan ke siswa lain.');
            }

            $message = $this->commService->sendMessage(
                sender: $user,
                receiverId: $validated['receiver_id'],
                body: $validated['body'],
                parentId: $validated['parent_id'],
            );

            return redirect()->route('communication.messages.thread', $validated['parent_id'])
                             ->with('success', 'Pesan terkirim.');
        }

        // New thread — multi-recipient
        $validated = $request->validate([
            'receiver_ids'   => 'required|array|min:1',
            'receiver_ids.*' => 'required|exists:users,id',
            'body'           => 'required|string|max:5000',
        ]);

        // Students cannot initiate new threads
        if ($user->role === 'student') {
            abort(403, 'Siswa tidak dapat memulai percakapan baru.');
        }

        $lastMessage = null;
        foreach ($validated['receiver_ids'] as $receiverId) {
            $receiver = User::findOrFail($receiverId);

            // Skip if student trying to message another student
            if ($receiver->role === 'student' && $user->role === 'student') {
                continue;
            }

            $lastMessage = $this->commService->sendMessage(
                sender: $user,
                receiverId: $receiverId,
                body: $validated['body'],
                parentId: null,
            );
        }

        $count = count($validated['receiver_ids']);
        $successMsg = $count === 1
            ? 'Pesan terkirim.'
            : "Pesan berhasil dikirim ke {$count} siswa.";

        // For single recipient, go directly to thread; for multiple, stay at inbox
        if ($count === 1 && $lastMessage) {
            return redirect()->route('communication.messages.thread', $lastMessage->id)
                             ->with('success', $successMsg);
        }

        return redirect()->route('communication.messages.inbox')
                         ->with('success', $successMsg);
    }

    /**
     * Mark all messages in a thread as read for the current user.
     */
    public function markRead(int $rootId)
    {
        $this->commService->markThreadRead(Auth::user(), $rootId);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Soft-delete a single message.
     * Superadmin can delete any; sender can delete their own.
     * If the root message is deleted, redirect to inbox.
     */
    public function deleteMessage(Message $message)
    {
        Gate::authorize('deleteMessage', $message);

        $parentId = $message->parent_id;

        $message->delete(); // soft delete (preserves audit trail)

        if ($parentId) {
            // It was a reply — go back to the thread
            return redirect()->route('communication.messages.thread', $parentId)
                             ->with('success', 'Pesan dihapus.');
        }

        // Root message deleted — go to inbox
        return redirect()->route('communication.messages.inbox')
                         ->with('success', 'Percakapan dihapus.');
    }

    /**
     * Delete an entire thread (root + all replies) — soft delete.
     * Must be called with the ROOT message ID.
     */
    public function deleteThread(Message $message)
    {
        Gate::authorize('deleteThread', $message);

        // Only allow on root messages (no parent)
        abort_if($message->parent_id !== null, 400, 'Hanya root thread yang dapat dihapus.');

        // Soft-delete all replies first, then the root
        Message::where('parent_id', $message->id)->delete();
        $message->delete();

        return redirect()->route('communication.messages.inbox')
                         ->with('success', 'Percakapan beserta seluruh balasan berhasil dihapus.');
    }
}
