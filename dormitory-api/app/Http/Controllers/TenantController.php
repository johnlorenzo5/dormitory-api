<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with(['user', 'room']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->user()->role === 'staff') {
            // Staff can only see tenants they manage (if you implement this relationship)
            $query->whereHas('room', function($q) use ($request) {
                $q->where('assigned_staff', $request->user()->id);
            });
        }

        return response()->json($query->paginate(10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'emergency_contact' => 'required|string',
            'emergency_phone' => 'required|string',
            'check_in_date' => 'required|date',
        ]);

        // Generate temporary password
        $tempPassword = Str::random(8);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'temp_password' => Hash::make($tempPassword),
            'role' => 'tenant',
        ]);

        // Create tenant
        $tenant = Tenant::create([
            'user_id' => $user->id,
            'phone' => $request->phone,
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
            'check_in_date' => $request->check_in_date,
        ]);

        // Send email with temporary password (implement your email service)
        // Mail::to($user->email)->send(new TenantCreatedMail($user->email, $tempPassword));

        return response()->json([
            'tenant' => $tenant->load('user', 'room'),
            'temp_password' => $tempPassword, // Only return in development
        ], 201);
    }

    public function show(Tenant $tenant)
    {
        return response()->json($tenant->load('user', 'room', 'payments', 'maintenanceRequests'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,'.$tenant->user_id,
            'phone' => 'sometimes|string',
            'emergency_contact' => 'sometimes|string',
            'emergency_phone' => 'sometimes|string',
        ]);

        if ($request->has('name') || $request->has('email')) {
            $tenant->user->update($request->only(['name', 'email']));
        }

        $tenant->update($request->only([
            'phone', 'emergency_contact', 'emergency_phone'
        ]));

        return response()->json($tenant->load('user', 'room'));
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->user->delete();
        return response()->json(['message' => 'Tenant deleted successfully']);
    }

    public function assignRoom(Request $request, Tenant $tenant)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        $room = Room::find($request->room_id);

        if ($room->status !== 'vacant') {
            return response()->json(['message' => 'Room is not available'], 400);
        }

        $tenant->update(['room_id' => $request->room_id]);
        $room->update(['status' => 'occupied']);

        return response()->json($tenant->load('room'));
    }

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['status' => 'suspended']);
        return response()->json($tenant);
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['status' => 'active']);
        return response()->json($tenant);
    }
}