<?php

namespace App\Http\Controllers;

use App\User;
use App\Url;
use Auth;
use DB;
use Bican\Roles\Models\Permission;
use Bican\Roles\Models\Role;
use Hash;
use Illuminate\Http\Request;
use Input;
use Validator;
use Redirect;

class UserController extends Controller
{
    /**
     * Get user current context.
     *
     * @return JSON
     */
    public function getMe()
    {
        $user = Auth::user();

        return response()->success($user);
    }

    /**
     * Update user current context.
     *
     * @return JSON success message
     */
    public function putMe(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'data.name' => 'required|min:3',
            'data.email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        $userForm = app('request')
                    ->only(
                        'data.current_password',
                        'data.new_password',
                        'data.new_password_confirmation',
                        'data.name',
                        'data.email'
                    );

        $userForm = $userForm['data'];
        $user->name = $userForm['name'];
        $user->email = $userForm['email'];

        if ($request->has('data.current_password')) {
            Validator::extend('hashmatch', function ($attribute, $value, $parameters) {
                return Hash::check($value, Auth::user()->password);
            });

            $rules = [
                'data.current_password' => 'required|hashmatch:data.current_password',
                'data.new_password' => 'required|min:8|confirmed',
                'data.new_password_confirmation' => 'required|min:8',
            ];

            $payload = app('request')->only('data.current_password', 'data.new_password', 'data.new_password_confirmation');

            $messages = [
                'hashmatch' => 'Invalid Password',
            ];

            $validator = app('validator')->make($payload, $rules, $messages);

            if ($validator->fails()) {
                return response()->error($validator->errors());
            } else {
                $user->password = Hash::make($userForm['new_password']);
            }
        }

        $user->save();

        return response()->success('success');
    }

    /**
     * Get all users.
     *
     * @return JSON
     */
    public function getIndex()
    {
        $user = Auth::user();
        
        if ($user->hasRole('role.admin')) {
            $users = User::all();
        }

        return response()->success(compact('users'));
    }

    /**
     * Create a user in admin panel.
     *
     * @return JSON
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'email_verified' => 'integer'
        ]);
        
        $user = new User();
        $user->name = trim($request->name);
        $user->email = trim(strtolower($request->email));
        $user->email_verified = trim(strtolower($request->email_verified));
        $user->password = bcrypt($request->password);
        $user->save();

        if (Input::has('roles')) {
            $user->detachAllRoles();
            foreach (Input::get('roles') as $setRole) {
                $user->attachRole($setRole);
            }
        }

        return response()->success('success');
    }

    /**
     * Get user details referenced by id.
     *
     * @param int User ID
     *
     * @return JSON
     */
    public function getShow($id)
    {
        $user = User::find($id);
        $user['role'] = $user
                        ->roles()
                        ->select(['slug', 'roles.id', 'roles.name'])
                        ->get();

        return response()->success($user);
    }

    /**
     * Update user data.
     *
     * @return JSON success message
     */
    public function putShow(Request $request)
    {
        $userForm = array_dot(
            app('request')->only(
                'data.name',
                'data.email',
                'data.email_verified',
                'data.id'
            )
        );

        $userId = intval($userForm['data.id']);

        $user = User::find($userId);

        $this->validate($request, [
            'data.id' => 'required|integer',
            'data.name' => 'required|min:3',
            'data.email' => 'required|email|unique:users,email,'.$user->id,
            'data.email_verified' => 'integer'
        ]);

        $userData = [
            'name' => $userForm['data.name'],
            'email' => $userForm['data.email'],
            'email_verified' => $userForm['data.email_verified']
        ];

        $affectedRows = User::where('id', '=', $userId)->update($userData);

        $user->detachAllRoles();

        if (Input::has('data.role')) {
            foreach (Input::get('data.role') as $setRole) {
                $user->attachRole($setRole);
            }
        }

        return response()->success('success');
    }

    /**
     * Delete User Data.
     *
     * @return JSON success message
     */
    public function deleteUser($id)
    {
        $user = User::find($id);
        $user->delete();

        return response()->success('success');
    }

    /**
     * Get users only who own urls.
     *
     *
     *
     * @return JSON
     */
    public function getConsumers()
    {
        $consumers = [];
        $userList = User::all();
        foreach ($userList as $u) {
            $isConsumer = $u->is('role.user');
            if ($isConsumer)
                $consumers[] = $u;
        }

        return response()->success(compact('consumers'));
    }

    /**
     * Get all short urls.
     *
     * @return JSON
     */
    public function getUrls(Request $request)
    {
        $user = Auth::user();
        if ($user->hasRole('role.admin')) {
            $urls = Url::all();
        }
        else {
            $urls = Url::where('user_id', $user->id);
            
            $urls = $urls->get();
        }
        
        return response()->success(compact('urls'));
    }

    /**
     * Get urls referenced by user id.
     *
     * @param int User ID
     *
     * @return JSON
     */
    public function getUrlsShow($id)
    {
        $urls = Url::where('user_id', $id)->get();

        return response()->success(compact('urls'));
    }

    /**
     * Get a short url referenced by id
     *
     * @param int Url ID
     *
     * @return JSON
     */
    public function getUrl($id)
    {
        $record = Url::find($id);

        return response()->success($record);
    }

    /**
     * Create a short url for a user referenced by id.
     *
     *
     *
     * @return JSON
     */
    public function createUrl(Request $request)
    {
        $user = Auth::user();
        $this->validate($request, [
            'url' => 'required|url|unique:urls,url'
        ]);
        
        $url = new Url();
        $url->user_id = $request->get('user_id', $user->id);
        $url->url = $request->get('url');
        $url->short_route = '/short/' . uniqid($user->id);
        $url->visits = 0;
        $url->save();
        
        return response()->success('success');
    }

    /**
     * Update url data.
     *
     * @return JSON success message
     */
    public function updateUrl(Request $request)
    {
        $this->validate($request, [
            'data.url' => 'required|url'
        ]);

        $userForm = app('request')
                    ->only(
                        'data.id',
                        'data.user_id',
                        'data.url'
                    );

        $userForm = $userForm['data'];

        $url = Url::find($userForm['id']);
        $url->url = $userForm['url'];
        $url->visits = 0;
        $url->save();

        return response()->success('success');
    }

    /**
     * Delete Url Record.
     *
     * @return JSON success message
     */
    public function deleteUrl($id)
    {
        $url = Url::find($id);
        $url->delete();

        return response()->success('success');
    }

    /**
     * Get all user roles.
     *
     * @return JSON
     */
    public function getRoles()
    {
        $roles = Role::all();

        return response()->success(compact('roles'));
    }

    /**
     * Get role details referenced by id.
     *
     * @param int Role ID
     *
     * @return JSON
     */
    public function getRolesShow($id)
    {
        $role = Role::find($id);

        $role['permissions'] = $role
                        ->permissions()
                        ->select(['permissions.name', 'permissions.id'])
                        ->get();

        return response()->success($role);
    }

    /**
     * Update role data and assign permission.
     *
     * @return JSON success message
     */
    public function putRolesShow()
    {
        $roleForm = Input::get('data');
        $roleData = [
            'name' => $roleForm['name'],
            'slug' => $roleForm['slug'],
            'description' => $roleForm['description'],
        ];

        $roleForm['slug'] = str_slug($roleForm['slug'], '.');
        $affectedRows = Role::where('id', '=', intval($roleForm['id']))->update($roleData);
        $role = Role::find($roleForm['id']);

        $role->detachAllPermissions();

        foreach (Input::get('data.permissions') as $setPermission) {
            $role->attachPermission($setPermission);
        }

        return response()->success('success');
    }

    /**
     * Create new user role.
     *
     * @return JSON
     */
    public function postRoles()
    {
        $role = Role::create([
            'name' => Input::get('role'),
            'slug' => str_slug(Input::get('slug'), '.'),
            'description' => Input::get('description'),
        ]);

        return response()->success(compact('role'));
    }

    /**
     * Delete user role referenced by id.
     *
     * @param int Role ID
     *
     * @return JSON
     */
    public function deleteRoles($id)
    {
        Role::destroy($id);

        return response()->success('success');
    }

    /**
     * Get all system permissions.
     *
     * @return JSON
     */
    public function getPermissions()
    {
        $permissions = Permission::all();

        return response()->success(compact('permissions'));
    }

    /**
     * Create new system permission.
     *
     * @return JSON
     */
    public function postPermissions()
    {
        $permission = Permission::create([
            'name' => Input::get('name'),
            'slug' => str_slug(Input::get('slug'), '.'),
            'description' => Input::get('description'),
        ]);

        return response()->success(compact('permission'));
    }

    /**
     * Get system permission referenced by id.
     *
     * @param int Permission ID
     *
     * @return JSON
     */
    public function getPermissionsShow($id)
    {
        $permission = Permission::find($id);

        return response()->success($permission);
    }

    /**
     * Update system permission.
     *
     * @return JSON
     */
    public function putPermissionsShow()
    {
        $permissionForm = Input::get('data');
        $permissionForm['slug'] = str_slug($permissionForm['slug'], '.');
        $affectedRows = Permission::where('id', '=', intval($permissionForm['id']))->update($permissionForm);

        return response()->success($permissionForm);
    }

    /**
     * Delete system permission referenced by id.
     *
     * @param int Permission ID
     *
     * @return JSON
     */
    public function deletePermissions($id)
    {
        Permission::destroy($id);

        return response()->success('success');
    }


    /**
     * Redirect the user to original url and increase visit counts for that shortened url
     *
     * @param string Url Token
     *
     */
    public function visitUrl($token)
    {
        $route = '/short/' . $token;
        $url = Url::where('short_route', '=', $route)->first();
        $url->visits += 1;
        $url->save();

        return Redirect::to($url->url);
    }
}
