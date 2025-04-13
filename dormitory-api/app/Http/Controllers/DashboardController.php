<?php

namespace App\Http\Controllers;

// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Room;
use App\Models\Payment;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->role === 'admin') {
            return $this->adminDashboard();
        } elseif ($user->role === 'staff') {
            return $this->staffDashboard($user);
        } else {
            return $this->tenantDashboard($user);
        }
    }

    protected function adminDashboard()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $availableRooms = Room::where('status', 'vacant')->count();
        
        $overduePayments = Payment::where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->with(['tenant.user', 'room'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();
            
        $pendingMaintenance = MaintenanceRequest::where('status', 'pending')
            ->with(['room', 'tenant.user'])
            ->orderBy('priority', 'desc')
            ->limit(5)
            ->get();
            
        $recentPayments = Payment::where('status', 'paid')
            ->with(['tenant.user', 'room'])
            ->orderBy('paid_date', 'desc')
            ->limit(5)
            ->get();
            
        return response()->json([
            'stats' => [
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'available_rooms' => $availableRooms,
            ],
            'overdue_payments' => $overduePayments,
            'pending_maintenance' => $pendingMaintenance,
            'recent_payments' => $recentPayments,
        ]);
    }

    protected function staffDashboard($user)
    {
        $assignedMaintenance = MaintenanceRequest::where('assigned_to', $user->id)
            ->where('status', '!=', 'resolved')
            ->with(['room', 'tenant.user'])
            ->orderBy('priority', 'desc')
            ->get();
            
        $overduePayments = Payment::where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->with(['tenant.user', 'room'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();
            
        $recentPayments = Payment::where('status', 'paid')
            ->with(['tenant.user', 'room'])
            ->orderBy('paid_date', 'desc')
            ->limit(5)
            ->get();
            
        return response()->json([
            'assigned_maintenance' => $assignedMaintenance,
            'overdue_payments' => $overduePayments,
            'recent_payments' => $recentPayments,
        ]);
    }

    protected function tenantDashboard($user)
    {
        $tenant = $user->tenant()->with(['room', 'payments', 'maintenanceRequests'])->first();
        
        $upcomingPayment = $tenant->payments()
            ->where('status', 'unpaid')
            ->orderBy('due_date')
            ->first();
            
        $activeMaintenance = $tenant->maintenanceRequests()
            ->where('status', '!=', 'resolved')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'tenant' => $tenant,
            'upcoming_payment' => $upcomingPayment,
            'active_maintenance' => $activeMaintenance,
        ]);
    }
}