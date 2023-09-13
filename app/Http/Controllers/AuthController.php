<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\teaPost;
use Validator;


class AuthController extends Controller
{
/**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'البريد الالكتروني أو كلمة المرور غير صحيحة!'], 401);
        }
        return $this->createNewToken($token);
    }
    /**
     * Register a User.
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,30',
            'email' => 'required|string|email|max:100|unique:users',
            'user_image'=> "required|image|mimes:jpeg,png,jpg|max:2048",
            'password' => 'required|string|confirmed|min:6',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $requestUser = $request->all();
        
        if ($request->hasFile('user_image')) {
            
            $image = $request->file('user_image');
            
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            $image->storeAs('public/users_images', $imageName); // Store the image in the storage/app/public/images directory;
            
            $requestUser["user_image"] = 'storage/users/images/' . $imageName;;
            // return $requestUser["user_image"];
        } else {
            // Handle the case where no image was provided
            return response()->json(['message' => 'لم يتم تحميل أي صورة'], 400);
        }


        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password),"user_image"=>$requestUser["user_image"]],
                    
                ));

        $token = auth()->login($user);
        

        return response()->json([
            'token' => $token,
            'user' => $user,
            'message' => 'تم تسجيل المستخدم بنجاح',
        ], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'تم تسجيل خروج المستخدم بنجاح']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        
        $teaPostsCount = user::withCount('teaPosts')->get()->find(auth()->id());
        $commentsCount = user::withCount('comments')->get()->find(auth()->id());
        
        $response = [
            "user"=>auth()->user(),
            'tea_posts_count' => $teaPostsCount->tea_posts_count,
            'comments_count' => $commentsCount->comments_count,
        ];

        return response()->json($response);
        
    }

    
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
