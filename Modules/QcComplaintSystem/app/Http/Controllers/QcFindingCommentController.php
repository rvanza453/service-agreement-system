<?php

namespace Modules\QcComplaintSystem\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\QcComplaintSystem\Models\QcFinding;
use Modules\QcComplaintSystem\Models\QcFindingComment;

class QcFindingCommentController extends Controller
{
    public function store(Request $request, QcFinding $finding)
    {
        // Cek apakah user authorized
        $authId = (int) auth()->id();

        // Validasi input
        $validated = $request->validate([
            'content' => 'required|string|min:1|max:5000',
            'parent_comment_id' => 'nullable|exists:qc_finding_comments,id',
        ]);

        try {
            $comment = QcFindingComment::create([
                'qc_finding_id' => $finding->id,
                'user_id' => $authId,
                'parent_comment_id' => $validated['parent_comment_id'] ?? null,
                'content' => $validated['content'],
            ]);

            // Return html untuk di-append ke view
            return response()->json([
                'success' => true,
                'comment' => $this->formatCommentForResponse($comment),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan komentar',
            ], 500);
        }
    }

    // public function destroy(QcFinding $finding, QcFindingComment $comment)
    // {
    //     // Cek ownership - user hanya bisa delete komentarnya sendiri
    //     if ($comment->user_id !== (int) auth()->id()) {
    //         abort(403, 'Anda hanya dapat menghapus komentar Anda sendiri');
    //     }

    //     // Cek apakah comment belongs to finding
    //     if ($comment->qc_finding_id !== $finding->id) {
    //         abort(404);
    //     }

    //     try {
    //         $comment->delete();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Komentar berhasil dihapus',
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal menghapus komentar',
    //         ], 500);
    //     }
    // }

    public function show(QcFinding $finding)
    {
        // Load comments dengan user relationship
        $comments = $finding->mainComments()->with('user', 'replies.user')->get();

        return response()->json([
            'success' => true,
            'comments' => $comments->map(fn($c) => $this->formatCommentForResponse($c))->all(),
        ]);
    }

    private function formatCommentForResponse(QcFindingComment $comment): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'email' => $comment->user->email,
            ],
            'created_at' => $comment->created_at->format('Y-m-d H:i:s'),
            'created_at_human' => $comment->created_at->diffForHumans(),
            'is_author' => $comment->user_id === (int) auth()->id(),
            'replies' => $comment->replies()->with('user')->get()
                ->map(fn($r) => $this->formatCommentForResponse($r))->all(),
        ];
    }
}
