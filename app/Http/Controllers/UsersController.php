<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use Validator;

use App\User;
use App\Locale;
use App\MultiLang;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use \Illuminate\Support\Facades\Mail;
use App\ActivationCode;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller

{
    
    
    public function __construct()
    
    {
        
        $this->middleware('auth:api', [
            'only' => [
                'update',
                'updateAvatar',
                'getUser'
            ]
        ]);
        
    }
    
    /**
    
    * Display a listing of the resource.
    
    *
    
    * @return \Illuminate\Http\Response
    
    */
    
    public function authenticate(Request $request)
    
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'name' => 'require',
            'password' => 'required'
        ]);
        
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = 'User for this email is not exist';
            $data['data'] = null;
            return response()->json($data);
        }
        $data = [];
        if(Hash::check($request->input('password'), $user->password)){
            
            $accessToken = base64_encode(self::quickRandom(40));
            
            $user->access_token = $accessToken;
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            
            $data['meta']['code'] = 200;
            $data['meta']['message'] = 'successful';
            $data['data'] = $user;
            
            return response()->json($data);
            
        }else{
            $data['meta']['code'] = 401;
            $data['meta']['message'] = 'Password is invalid.';
            $data['data'] = null;
            return response()->json($data);
            
        }
        
    }
    
    /**
     * 
     * Refresh token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $data = [];

        if ($request->header('Authorization')) {
            $key = explode(' ',$request->header('Authorization'));
            $user = User::where('access_token', $key[1])->first();
        } else {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = 'Access token is required';
            $data['data'] = null;
            return response()->json($data, 401);
        }
        
        if(empty($user)){
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "Access token is not valid";
            $data['data'] = null;
            return response()->json($data, 401);
        }
        $accessToken = base64_encode(self::quickRandom(40));
        
        $user->access_token = $accessToken;
        $user->save();
        
        $data['meta']['code'] = 200;
        $data['meta']['message'] = 'successful';
        $data['data']['access_token'] = $accessToken;
        
        return response()->json($data);
    }
    
    /**
     * 
     * @param Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);
        
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        
        $input = $request->all();
        $password = Hash::make($request->input('password'));
        $user = User::create($input);
        $user->password = $password;
        $accessToken = base64_encode(self::quickRandom(40));
        $user->access_token = $accessToken;
        $user->save();
        $data['meta']['code'] = 200;
        $data['meta']['message'] = 'Register successful';
        $data['data'] = $user;
        return response()->json($data);
    }
    
    /**
     * Senf activation code
     * @param Request $request
     * @param string $email
     */
    public function forgotPassword(Request $request)
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);
        
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        $email = $request->get('email');
        $user = User::where('email', $request->input('email'))->first();
        if(!$user){
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "The user with email {$email} doesn't exist";
            $data['data'] = null;
            return response()->json($data);
        }
        $activationCode = $this->generateRandomString();
        ActivationCode::create([
            'user_id' => $user->id,
            'code' => $activationCode,
            'duration' => ActivationCode::DETAULT_DURATION,
            'status' => ActivationCode::STATUS_UNVERIFIED
        ]);
        $subject = "FeetApp - Activation Code";
        $mailData = [
            'name' => $user->name,
            'activation_code' => $activationCode
        ];
        try {
            Mail::send('email_activation_code', $mailData, function($message) use ($subject, $email, $user)
            {
                $message->to($email, $user->name)->subject($subject);
            });
        } catch (\Exception $e) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "Unable send Activation Code to $email";
            $data['data'] = null;
            return response()->json($data);
        }

        $data['meta']['code'] = 200;
        $data['meta']['message'] = "Successful";
        $data['data']['to'] = $email;
        $data['data']['subject'] = $subject;
        $data['data']['activation_code'] = $activationCode;
        return response()->json($data);
    }
    
    protected function generateRandomString($length = 6) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    /**
     * Reset the user password 
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'activation_code' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);
        
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        $email = $request->get('email');
        $user = User::where('email', $email)->first();
        if(!$user){
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "The user with email {$email} doesn't exist";
            $data['data'] = null;
            return response()->json($data);
        }
        $activation = ActivationCode::where('user_id', $user->id)
            ->where('code', $request->get('activation_code'))
            ->first();
        if(!$activation){
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "Activation code does not exist";
            $data['data'] = null;
            return response()->json($data);
        }
        if ($activation->status == ActivationCode::STATUS_VERIFIED) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "Activation code has been used";
            $data['data'] = null;
            return response()->json($data);
        }
        $created = $activation->created_at;
        $differenceTime = $this->differenceTimeInMinutes($created, new \DateTime());
        if ($differenceTime > $activation->duration) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "Your activation code was expired!";
            $data['data'] = null;
            return response()->json($data);
        }
        $password = Hash::make($request->get('password'));
        $user->password = $password;
        $user->save();
        $activation->status = ActivationCode::STATUS_VERIFIED;
        $activation->save();
        $data['meta']['code'] = 200;
        $data['meta']['message'] = 'Reset password successful';
        $data['data'] = $user;
        return response()->json($data);
    }
    
    /**
     * Get the diffirence time in minutes
     * 
     * @param \DateTime $fromTime
     * @param \DateTime $toTime
     */
    protected function differenceTimeInMinutes(\DateTime $startDatetime, \DateTime $toDatetime)
    {
        $sinceStart = $startDatetime->diff($toDatetime);
        $minutes = $sinceStart->days * 24 * 60;
        $minutes += $sinceStart->h * 60;
        $minutes += $sinceStart->i;
        
        return $minutes;
    }
    
    /**
     * Update user info
     * 
     * @param Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request) 
    {
        $data = [];
        $currentUser = Auth::user();
        $user = User::find($currentUser->id);
        if(!$user){
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "The user with doesn't exist";
            $data['data'] = null;
            return response()->json($data, 401);
        }
        $input = $request->all();

        if (!empty($input['birthday'])) {
            $input['birthday'] = date('Y-m-d', $input['birthday']);
        }
        unset($input['address']);
        unset($input['bio']);
        unset($input['access_token']);
        unset($input['facebook_access_token']);
        unset($input['google_jwt_token']);
        foreach ($input as $key => $value) {
            if ($user->isFillable($key)) {
                $user->{$key} = $value;
            }  
        }
        unset($user->userid);
        $user->save();
        $localeIso = $request->header('locale');
        if (empty($localeIso)) {
            $localeIso = 'en';
        }
        $locale = Locale::where('language_iso', $localeIso)->first();
        $mlData = [];
        $bio = $request->get('bio');
        if (!empty($bio)) {
            $mlData[$locale->id]['bio'] = $bio;
            $user->bio = $bio;
        }
        
        $address = $request->get('address');
        if (!empty($address)) {
            $mlData[$locale->id]['address'] = $address;
            $user->address = $address;
        }
        
        if (!empty($mlData)) {
            $multiLangModel = new MultiLang();
            $multiLangModel->updateMultiLang($mlData, $user->id, 'User');
        }
        
        $data['meta']['code'] = 200;
        $data['meta']['message'] = 'Update user infomation successful';
        $data['data'] = $user;
        return response()->json($data);
   
    }
    
    /**
     * Update user avatar
     *
     * @param Request $request
     * @param string $userId
     *
     * @return \Illuminate\Http\Response
     */
    public function updateAvatar(Request $request)
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'avatar' => 'required'
        ]);
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        $currentUser = Auth::user();
        $user = User::find($currentUser->id);
        if (!$user) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = "The user with {$id} doesn't exist";
            $data['data'] = null;
            return response()->json($data, 401);
        }
        $avatar = $request->get('avatar');
        $user->avatar = $avatar;
        
        $user->save();
        
        $data['meta']['code'] = 200;
        $data['meta']['message'] = 'Update avatar successful';
        $data['data'] = $user;
        return response()->json($data);
    }
    
    /**
     * Login social
     * @param Request $request
     */
    public function loginSocial(Request $request)
    {
        $data = [];
        $validator = Validator::make($request->all(), [
            'social_access_token' => 'required',
            'type' => 'required'
        ]);
        
        if ($validator->fails()) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = $validator->errors()->first();
            $data['data'] = null;
            return response()->json($data);
        }
        $type = $request->get('type');
        if ($type == 'facebook') {
            return $this->loginFacebook($request);
        } elseif ($type == 'google') {
            return $this->loginGoogle($request);
        }
    }
    
    /**
     * Login Facebook
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected  function loginFacebook(Request $request)
    {
        $data = [];
        $fb = new Facebook([
            'app_id' => '1838708706273329',
            'app_secret' => '5c4e41d1fb7ec4e28b45b29f020e4385',
            'default_graph_version' => 'v2.10',
        ]);
        
        try {
            $fbAccessToken = $request->get('social_access_token');
            $response = $fb->get('/me', $fbAccessToken);
        } catch(FacebookResponseException $e) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = 'Facebook Graph returned an error: ' . $e->getMessage();
            $data['data'] = null;
            return response()->json($data, 401);
        } catch(FacebookSDKException $e) {
            $data['meta']['code'] = 401;
            $data['meta']['message'] = 'Facebook SDK returned an error: ' . $e->getMessage();
            $data['data'] = null;
            return response()->json($data, 401);
        }
        
        $fbUser = $response->getGraphUser();
        $fbId = $fbUser->getId();
        $email = $fbUser->getEmail();
        if (empty($email)) {
            $email = $fbId.'@facebook.com';
        }
        $name = $fbUser->getName();
        //Check account is existed
        $userExisted = User::where('email', $email)->first();
        if($userExisted){
            if (!empty($name)) {
                $userExisted->name = $name;
                $userExisted->avatar = "https://graph.facebook.com/$fbId/picture?type=large&width=720&height=720";
                $userExisted->save();
            }
            $data['meta']['code'] = 200;
            $data['meta']['message'] = "Login via facebook successful";
            $data['data'] = $userExisted;
            return response()->json($data);
        }
        
        $user = User::create([
            'email' => $email,
            'name' => $name,
            'avatar' => "https://graph.facebook.com/$fbId/picture?type=large&width=720&height=720",
            'login_from' => User::LOGIN_FROM_FACEBOOK,
            'facebook_id' => $fbId
        ]);
        $password = Hash::make(User::DEFAULT_PASS_SOCIAL_LOGIN);
        $user->password = $password;
        $accessToken = base64_encode(self::quickRandom(40));
        $user->access_token = $accessToken;
        $user->facebook_access_token = $fbAccessToken;
        $user->save();
        $data['meta']['code'] = 200;
        $data['meta']['message'] = "Login via facebook successful";
        $data['data'] = $user;
        return response()->json($data);
    }
    
    /**
     * Login Google
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function loginGoogle(Request $request)
    {
        $data = [];
        $jsonWebToken = $request->get('social_access_token');
        try {
            $client = new \Google_Client(['client_id' => '850474009029-jrt990mm8lbe9rpcmafg6q0lhkhnm8ue.apps.googleusercontent.com']);  // Specify the CLIENT_ID of the app that accesses the backend
            $payload = $client->verifyIdToken($jsonWebToken);
            if ($payload) {
                $email = $payload['email'];
               
            } else {
                $client = new \Google_Client(['client_id' => '850474009029-kuntgruc19sfkosamsh9ai0lgsukilb1.apps.googleusercontent.com']);  // Specify the CLIENT_ID of the app that accesses the backend
                $payload = $client->verifyIdToken($jsonWebToken);
                if ($payload) {
                    $email = $payload['email'];
                } else {
                    $data['meta']['code'] = 200;
                    $data['meta']['message'] = "Invalid token";
                    $data['data'] = null;
                    return response()->json($data);
                }
            }
            $userExisted = User::where('email', $email)->first();
            if($userExisted){
                $userExisted->avatar = $payload['picture'];
                $userExisted->save();
                $data['meta']['code'] = 200;
                $data['meta']['message'] = "Login via google successful";
                $data['data'] = $userExisted;
                return response()->json($data);
            }
            
            $user = User::create([
                'email' => $payload['email'],
                'name' => $payload['name'],
                'avatar' => $payload['picture'],
                'login_from' => User::LOGIN_FROM_GOOGLE
            ]);
            $password = Hash::make(User::DEFAULT_PASS_SOCIAL_LOGIN);
            $user->password = $password;
            $accessToken = base64_encode(self::quickRandom(40));
            $user->access_token = $accessToken;
            $user->google_jwt_token = $jsonWebToken;
            $user->save();
            $data['meta']['code'] = 200;
            $data['meta']['message'] = "Login via google successful";
           
            $data['data'] = $user;
            return response()->json($data);
        } catch (\Exception $e) {
            $data['meta']['code'] = 500;
            $data['meta']['message'] = $e->getMessage();
            $data['data'] = null;
            return response()->json($data);
        }
       
    }
    
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getUser(Request $request, $id)
    {
        $data = [];
        $currentUser = Auth::user();
        if (empty($id)) {
            $id = $currentUser->id;
        }
        $user = User::find($id);
        
        if ($user) {
            $data['meta']['code'] = 200;
            $data['meta']['message'] = 'successful';
            if ($user->id != $currentUser->id) {
                unset($user->access_token);
                unset($user->last_login);
                unset($user->status);
                unset($user->deleted);
                unset($user->created_at);
                unset($user->updated_at);
                unset($user->login_from);
                unset($user->facebook_id);
                unset($user->facebook_access_token);
                unset($user->google_jwt_token);
            }
            $data['data'] = $user;
            return response()->json($data);
        }else{
            $data['meta']['code'] = 401;
            $data['meta']['message'] = 'Cannot find user!';
            $data['data'] = null;
            return response()->json($data, 401);
        }
    }
    
    /**
     * Generate quick token
     * @param int $length
     * @return string
     */
    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    
}

?>