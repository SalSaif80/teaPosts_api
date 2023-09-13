<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Comment;
class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $request;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $id)
    {
        
        try {
            if (JWTAuth::parseToken()->check()) {

                $user = JWTAuth::parseToken()->authenticate();
                $userId = $user->id;
                
                $validator = Validator::make($request->all(), [
                    'comment_detail' => 'required|string|min:3|max:100',
                    'user_id' => 'required',
                    'post_id' => 'required',
                ]);
        
                if ($validator->fails()) {
                    
                    $array = [
                        "data"=>null,
                        "stauts"=> 400,
                        "message"=>$validator->errors()
                    ];
            
                    return response()->json($array);
        
                }

                $requestComment = $request->all();
                $requestComment["user_id"] = $userId;
                $requestComment["post_id"] = (int)$id;
                
                $createComment = comment::create($requestComment);
       
                return response()->json(["data"=>$requestComment,'message' => "تم إرسال التعليق بنجاح","status"=> 201]);
            }
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e], 401);
        }
        return response()->json(['message' => 'المستخدم غير مسجل الدخول ( غير مخول بإنشاء تعليق )','code'=>401]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
