<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function fetchUsers(Request $request)
    {
        $query = User::leftJoin('customers', 'users.id', '=', 'customers.user_id')
            ->select('users.*', 'customers.fname', 'customers.lname', 'customers.contact', 'customers.address');
        
        if ($request->has('search')) {
            $searchValue = $request->input('search');
            $query->where(function($q) use ($searchValue) {
                $q->where('users.name', 'like', "%{$searchValue}%")
                  ->orWhere('users.email', 'like', "%{$searchValue}%")
                  ->orWhere('customers.fname', 'like', "%{$searchValue}%")
                  ->orWhere('customers.lname', 'like', "%{$searchValue}%");
            });
        }
    
        $totalRecords = $query->count();
        $filteredRecords = $totalRecords;
    
        $users = $query->skip($request->input('start'))
                       ->take($request->input('length'))
                       ->get();
    
        $formattedUsers = $users->map(function($user) {
            return [
                'id' => $user->id,
                'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
                'name' => $user->name,
                'email' => $user->email,
                'active_status' => $user->active_status,
                'role' => $user->role,
                'fname' => $user->fname,
                'lname' => $user->lname,
                'contact' => $user->contact,
                'address' => $user->address,
            ];
        });
    
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedUsers,
        ]);
    }

    public function getEditUserData($id)
    {
        try {
            $user = User::with('customer')->findOrFail($id);

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->customer->fname,
                'last_name' => $user->customer->lname,
                'email' => $user->email,
                'contact' => $user->customer->contact,
                'address' => $user->customer->address,
                'active_status' => $user->active_status,
                'role' => $user->role,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => 'An error occurred while fetching the user data.',
                    'details' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function updateUserData(Request $request)
{
    // Add logging to capture the input request
    \Log::info('Update User Data Request: ', $request->all());

    $request->validate([
        'active_status' => 'required|in:1,0',
        'role' => 'required|in:admin,customer',
    ]);

    DB::beginTransaction();

    try {
        $user = User::findOrFail($request->id);

        // Update user data
        $user->active_status = $request->active_status == 1 ? 1 : 0;
        $user->role = $request->role;

        $user->save();

        DB::commit();

        return response()->json([
            'success' => 'User updated successfully'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        // Log the error details
        \Log::error('Error updating user data: ', ['error' => $e->getMessage()]);
        return response()->json([
            'error' => [
                'message' => 'An error occurred while updating the user.',
                'details' => $e->getMessage()
            ]
        ], 500);
    }
}

    
    
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        DB::beginTransaction();

        try {
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => [
                    'message' => 'An error occurred while deleting the user.',
                    'details' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($request->id ?? 'NULL') . ',id',
            'password' => $request->id ? 'nullable|string|min:6' : 'required|string|min:6',
            'active_status' => 'boolean',
            'profile_image' => 'nullable|image|max:2048',
            'contact' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $user = $request->id ? User::find($request->id) : new User();

            // Update the user data
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->active_status = $request->active_status ? 1 : 0;
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $profileImagePath = $profileImage->store('profile_images', 'public');
                $user->profile_image = $profileImagePath;
            }

            $user->save();

            $customer = $user->customer ?: new Customer();
            $customer->user_id = $user->id;
            $customer->fname = $request->first_name;
            $customer->lname = $request->last_name;
            $customer->contact = $request->contact;
            $customer->address = $request->address;

            $customer->save();

            DB::commit();

            return response()->json(['success' => 'User saved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => [
                    'message' => 'An error occurred while saving the user.',
                    'details' => $e->getMessage()
                ]
            ], 500);
        }
    }
}