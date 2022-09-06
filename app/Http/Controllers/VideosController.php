<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Videos;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class VideosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
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

        $searchVideos = Videos::where("limit", "=", null)->get();
        if(count($searchVideos) === 0){
            return response([
                "status" => false,
                "message" => "Видео не найдены"
            ], 404)
                ->setStatusCode(404, "videos not found");
        }

        return response([
            "status" => true,
            "videos" => $searchVideos
        ], 200)
            ->setStatusCode(200, "videos found")
            ->header("authorization", $request->header("authorization"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
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

        $title_video = $request->title_video;
        $description_video = $request->description_video;
        $category_video = $request->category_video;
        $file_video = $request->hasFile("file_video");

        $errors = [];

        if($title_video == null || empty($title_video)) $errors["error_title"] = "Title required";
        if($description_video == null || empty($description_video)) $errors["error_description"] = "Description required";
        if($category_video == null || empty($category_video)) $errors["error_category"] = "Category required";

        if($file_video){

            if(!strpos($request->file("file_video")->getClientOriginalName(), ".mp4")){
                $errors["error_video_type"] = "You loaded not video";
            }
            if($request->file("file_video")->getSize() > 100 * 1024 * 1024){
                $errors["error_video_size"] = "You loaded big video";
            }

        } else $errors["error_video"] = "Video required";

        if(count($errors) !== 0){
            return response([
                "status" => 400,
                "errors" => $errors
            ], 400)
                ->setStatusCode(400, "errors validation");
        }

        $filename = Str::random(60) . ".mp4";
        $request->file("file_video")->move(public_path("videos"), $filename);

        $create_video = Videos::create([
            "title" => $title_video,
            "description" => $description_video,
            "category" => $category_video,
            "video" => $filename,
            "author_id" => $search_user[0]->id
        ]);

        return response([
            "status" => true,
            "video_id" => $create_video->id
        ], 201)
            ->setStatusCode(201, "video created")
            ->header("authorization", $token);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Videos  $videos
     * @return Response
     */
    public function show()
    {
        $videos = Videos::all();

        return response([
            "status" => true,
            "videos" => $videos
        ], 200)
            ->setStatusCode(200, "videos");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Videos  $videos
     * @return Response
     */
    public function update(Request $request, Videos $videos, $id)
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

        $search_video = Videos::find($id);

        if($search_video === null){
            return response([
                "status" => false,
                "message" => "Видео не найдено"
            ], 404)
                ->setStatusCode(404, "video not found");
        }

        $updateItem = $request->updateItem;

        if($updateItem === null || empty($updateItem)){
            return response([
                "status" => false,
                "message" => "Выберите опцию"
            ], 400)
                ->setStatusCode(400, "update required");
        }

        if($updateItem === "like"){
            $like = 1 + (int)($search_video->likes);
            $search_video->update([
                "likes" => $like
            ]);

            return response([
                "status" => true,
                "video" => $search_video
            ], 203)
                ->setStatusCode(203, "update successful");
        }

        if($updateItem === "dislike"){
            $like = 1 + (int)$search_video->dislikes;
            $search_video->update([
                "dislikes" => $like
            ]);

            return response([
                "status" => true,
                "video" => $search_video
            ], 203)
                ->setStatusCode(203, "update successful");
        }

        return response([
            "status" => false,
            "message" => "Выбранная вами опция не существует"
        ], 404)
            ->setStatusCode(404, "option not found");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Videos $videos
     * @param Request $request
     * @return Response
     */
    public function destroy(Videos $videos, Request $request, $id)
    {
        $token = $request->header("authorization");

        if(empty($token) || $token !== "admin_token"){
            return response([
                "status" => false,
                "message" => "Вы не администратор"
            ], 403)
                ->setStatusCode(403, "unadmin");
        }

        $limit = $request->limit;

        if($limit === null || empty($limit)){
            return response([
                "status" => false,
                "message" => "limit required"
            ], 400)
                ->setStatusCode(400, "error limit");
        }

        $search_video = Videos::find($id);

        if($search_video === null){
            return response([
                "status" => false,
                "message" => "Видео не найдено"
            ], 404)
                ->setStatusCode(404, "video not found");
        }

        if($limit === "violation" || $limit === "ban" || $limit === "darkban" || $limit === "empty"){
            if($limit === "empty"){
                $search_video->update([
                    "limit" => null
                ]);
            } else {
                $search_video->update([
                    "limit" => $limit
                ]);
            }


            return response([
                "status" => true,
                "video" => $search_video
            ], 203)
                ->setStatusCode(203, "limit succesfful");
        }

        return response([
            "status" => false,
            "message" => "limit not found"
        ], 404)
            ->setStatusCode(404, "limit not found");
    }
}
