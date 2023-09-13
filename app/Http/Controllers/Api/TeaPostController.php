<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeaPostRessource;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use App\Models\teaPost;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\User;



class TeaPostController extends Controller
{

    public function isUserAuthorized($message){
        $teaPostId = teaPost::find($id);
        $user = JWTAuth::parseToken()->authenticate();
        $userId = $user->id;

        if($teaPostId !== $userId){
            return response()->json([
                // "message" => "المستخدم لا يملك هذا المنشور ليقوم بتحديثه!",
                "message" => $message,
                "code"=>401
            ]);
        }
    }

    public function index(Request $request){
        
        $page = $request->query('page', 1); // Default page is 1 if not provided in the URL
    
        $limit = $request->input('limit', 10); // Default limit is 10 if not provided in the request
        
        $teaPosts = teaPost::paginate($limit, ['*'], 'page', $page);
        
        $teaPostsResponse = [];

        foreach ($teaPosts as $post) {
            
            $response = [
                'id' => $post->id,
                'teaName' => $post->tea_name,
                'imagePath' => $post->tea_image_path,
                'teaType' => $post->tea_type,
                'howToPrepareTea' => $post->how_to_prepare_tea,
                'teaInWaterTime' => $post->tea_in_water_time,
                'author' => $post->user,
                'commentsCount' =>$post->comments->count(),
                'recommended_wznh' => $post->recommended_wznh,
                'created_at' => $this->getTimeAgo($post->created_at),
            ];
        
            $teaPostsResponse[] = $response;
        }

        $response = [
            'data' => $teaPostsResponse,
            'status' => 200,
            'message' => 'Get all Tea Posts',
            'pagination' => [
                'total' => $teaPosts->total(),
                'per_page' => $teaPosts->perPage(),
                'current_page' => $teaPosts->currentPage(),
                'last_page' => $teaPosts->lastPage(),
                'from' => $teaPosts->firstItem(),
                'to' => $teaPosts->lastItem(),
            ],
        ];
        return response()->json($response);
    }

    


    public function show($id){

        // Retrieve the user's comments
        $user = teaPost::with("user")->find($id);
        $comments = teaPost::with("comments")->find($id);
        
        $teaPostId = teaPost::find($id);
        // $userId = user::find($id);
        
        
        if($teaPostId){
            

            $response = [
                'id' => $teaPostId->id,
                'teaName' => $teaPostId->tea_name,
                'imagePath' => $teaPostId->tea_image_path,
                'teaType' => $teaPostId->tea_type,
                'howToPrepareTea' => $teaPostId->how_to_prepare_tea,
                'teaInWaterTime' => $teaPostId->tea_in_water_time,
                'author' => $user->user,
                'comments'=>$comments->comments->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'user_id' => user::find($c->user_id),
                        'post_id' => $c->post_id,
                        'comment_detail' => $c->comment_detail,
                        'created_at' =>$this->getTimeAgo($c->created_at) ,
                    ];
                }),
                'recommended_wznh' => $teaPostId->recommended_wznh,
                'created_at' => $this->getTimeAgo($teaPostId->created_at) ,
            ];

            

            


            $array = [
                "data"=>$response,
                "stauts"=> 200,
                "message"=>"تم جلب بيانات منشور ".$user->user->name."."
            ];

            return response()->json($array);
        }


        $array = [
            "data"=>null,
            "stauts"=> 401,
            "message"=>"Not found id number ".$id." Tea Post"
        ];

        return response()->json($array);

    }


    public function store(Request $request){
        
        try {
            if (JWTAuth::parseToken()->check()) {
                // User is logged in with a valid JWT token
                $user = JWTAuth::parseToken()->authenticate();
                $userId = $user->id;

                

                $validator = Validator::make($request->all(), [
                    'tea_name' => 'required|string|min:2|max:30',
                    'tea_image_path' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                    'tea_type' => 'required|string|min:2|max:50',
                    'recommended_wznh' => 'required|string|min:2|max:250',
                    'how_to_prepare_tea' => 'required|string',
                ]);
        
                if ($validator->fails()) {
                    $array = [
                        "data"=>null,
                        "stauts"=> 400,
                        "message"=>$validator->errors()
                    ];
                    return response()->json($array);
                }

                $requestPost = $request->all();
                $requestPost["user_id"] = $userId;
                
                if ($request->hasFile('tea_image_path')) {
                    $image = $request->file('tea_image_path');
                    
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/posts_images', $imageName); // Store the image in the storage/app/public/images directory
                    
                    // Save the image path to the database
                    $requestPost["tea_image_path"] = 'storage/images/' . $imageName;
                    
                } else {
                    return response()->json(['message' => 'No image uploaded'], 400);
                }

                $teaPost = teaPost::create($requestPost);
                

                if($teaPost){

                    $response = [
                        'teaName' => $teaPost->tea_name,
                        'imagePath' => $teaPost->tea_image_path,
                        'teaType' => $teaPost->tea_type,
                        'howToPrepareTea' => $teaPost->how_to_prepare_tea,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name, // Replace with the actual user information you want to include
                            'email' => $user->email, // Replace with the actual user information you want to include
                            // Add more user attributes as needed
                        ],
                        'recommended_wznh' => $teaPost->recommended_wznh,
                        'created_at' => $this->getTimeAgo($teaPost->created_at),
                        'commentsCount' =>$teaPost->comments->count(),
                        'id' => $teaPost->id,
                    ];


                    $array = [
                        "data"=>$response,
                        "stauts"=> 201,
                        "message"=>"تم نشر المنشور بنجاح"
                    ];
        
                    return response()->json($array);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e], 401);
        }
        return response()->json(['message' => 'المستخدم غير مسجل الدخول ( غير مخول بإنشاء منشور )','code'=>401]);
    }


    public function update(Request $request,$id){
        
        try {
            if (JWTAuth::parseToken()->check()) {

                // $this->isUserAuthorized("المستخدم لا يملك هذا المنشور ليقوم بتحديثه!");
                $teaPostId = teaPost::find($id);
                $user = JWTAuth::parseToken()->authenticate();
                $userId = $user->id;

                
                if(empty($teaPostId->id)){
                    return response()->json([
                        "message" => "لا يوجد منشور!",
                        "code"=>401
                    ]);
                }

                if($teaPostId !== $userId){
                    return response()->json([
                        "message" => "المستخدم لا يملك هذا المنشور ليقوم بتحديثه!",
                        "code"=>401
                    ]);
                }
                

                $validator = Validator::make($request->all(), [
                    'tea_name' => 'required|string|min:2|max:100',
                    'tea_image_path' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                    'tea_type' => 'required|string|min:2|max:100',
                    'how_to_prepare_tea' => 'required|string',
                ]);

                if ($validator->fails()) {
                    
                    $array = [
                        "data"=>null,
                        "stauts"=> 400,
                        "message"=>$validator->errors()
                    ];
            
                    return response()->json($array);

                }

               

                if(!$teaPostId){
                    // if does not exist id
                    $array = [
                        "data"=>null,
                        "stauts"=> 401,
                        "message"=>"Not found id number ".$id." Tea Post"
                    ];

                    return response()->json($array);
                }


                $teaPostId->update($request->all());

                // if we find id
                if($teaPostId){
                    $array = [
                        "data"=>new TeaPostRessource($teaPostId),
                        "stauts"=>200,
                        "message"=>"تم تعديل المنشور بنجاح"
                    ];

                    return response()->json($array);
                }

            }
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e], 401);
        }
        return response()->json(['message' => 'المستخدم غير مسجل الدخول ( غير مخول بإنشاء منشور )','code'=>401]);
        


        
    }

    public function delete($id){
        try {
            if (JWTAuth::parseToken()->check()) {
                
                // $this->isUserAuthorized("المستخدم لا يملك هذا المنشور ليقوم بتحديثه!");
                $teaPostId = teaPost::find($id);
                $user = JWTAuth::parseToken()->authenticate();
                $userId = $user->id;
                
                // return [$teaPostId->id];

                if(empty($teaPostId->id)){
                    return response()->json([
                        "message" => "لا يوجد منشور!",
                        "code"=>401
                    ]);
                }

                if($teaPostId->user_id !== $userId){
                    return response()->json([
                        "message" => "المستخدم لا يملك هذا المنشور ليقوم بحذفه!",
                        "code"=>401
                    ]);
                }


                if(!$teaPostId){
                    // if does not exist id
                    $array = [
                        "data"=>null,
                        "stauts"=> 401,
                        "message"=>"Not found id number ".$id." Tea Post"
                    ];

                    return response()->json($array);
                }


                $teaPostId->delete($id);

                if($teaPostId){
                    $array = [
                        "data"=>new TeaPostRessource($teaPostId),
                        "stauts"=> 200,
                        "message"=>"The post deleted correctly"
                    ];

                    return response()->json($array);
                }

            }
        }
        catch (\Exception $e) {
            return response()->json(['message' => $e], 401);
        }
        
        return response()->json(['message' => 'المستخدم غير مسجل الدخول ( غير مخول بحذف منشور )','code'=>401]);
    }



    public function getTimeAgo($timestamp)
    {
        $carbonTimestamp = Carbon::parse($timestamp);
        return $carbonTimestamp->diffForHumans();
    }
    
}
