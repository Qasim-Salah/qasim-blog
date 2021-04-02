<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\UserResource;
use App\Models\User as UserModel;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use GeneralTrait;

    public function register(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['required', 'numeric', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'image' => ['nullable|mimes:jpg,jpeg,png,gif|max:20000'],
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            $code = $this->returnCodeAccordingToInput($validation);
            return $this->returnValidationError($code, $validation);
        }

        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['email_verified_at'] = Carbon::now();
        $data['mobile'] = $request->mobile;
        $data['image'] = $request->image;
        $data['password'] = bcrypt($request->password);
        $data['status'] = 1;

        $user = UserModel::create($data);
        if ($user)
            return $this->returnData('user', new UserResource($user));
        return $this->returnError('E001', 'Unauthorized');


    }

    public function login(Request $request)
    {
        $rules = ["email" => "required", "password" => "required"];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $code = $this->returnCodeAccordingToInput($validator);
            return $this->returnValidationError($code, $validator);
        }
        //login

        $credentials = $request->only(['email', 'password']);
        $user = Auth::attempt($credentials);
        if (!$user)
            return $this->returnError('E001', 'Unauthorized');

        $token = Auth::user();
        $token->token = $token->createToken('access_Token')->accessToken;
        if ($token)
            return $this->returnData('user', $token);

        return $this->returnError('E001', 'Unauthorized');

    }


}
