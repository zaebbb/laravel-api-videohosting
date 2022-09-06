<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use App\Models\User;
use App\Models\Videos;
use Illuminate\Http\Request;

class CommentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allComments = Comments::all();

        return response([
            "status" => true,
            "comments" => $allComments
        ], 200)
            ->setStatusCode(200, "comments found");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $token = $request->header("authorization");
        $search_user = User::where("bearer_token", "=", $token)->get();

        if(empty($token) || count($search_user) == 0){
            return response([
                "status" => false,
                "message" => "Вы не авторизованы"
            ], 403)
                ->setStatusCode(403, "unauthorized");
        }

        $search_vide = Videos::find($id);
        if($search_vide === null) {
            return response([
                "status" => false,
                "message" => "video not found"
            ], 404)
                ->setStatusCode(404, "video not found");
        }

        $author_id = $search_user[0]->id;
        $comment = $request->comment;

        if($comment === null || empty($comment)){
            return response([
                "status" => false,
                "message" => "comment required"
            ], 400)
                ->setStatusCode(400, "error validation");
        }

        $create_comment = Comments::create([
            "author" => $author_id,
            "comment" => $comment,
            "video_id" => $id
        ]);

        return response([
            "status" => true,
            "comment_id" => $create_comment->id
        ], 201)
            ->setStatusCode(201, "comment created");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comments  $comments
     * @return \Illuminate\Http\Response
     */
    public function show(Comments $comments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comments  $comments
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comments $comments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comments  $comments
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comments $comments)
    {
        //
    }
}
