<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\MedicalLeave;
use App\Models\BusinessTrip;
use Illuminate\Http\Request;

class SupervisorRequestController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type');

        switch ($type) {

            case 'cuti':
                $data = LeaveRequest::with('employee')
                    ->where('type', 'cuti')
                    ->latest()
                    ->get();
                break;

            case 'izin':
                $data = LeaveRequest::with('employee')
                    ->where('type', 'izin')
                    ->latest()
                    ->get();
                break;

            case 'sakit':
                $data = MedicalLeave::with('employee')
                    ->latest()
                    ->get();
                break;

            case 'dinas_luar':
                $data = BusinessTrip::with('employee')
                    ->latest()
                    ->get();
                break;

            default:

                $data = collect();

                LeaveRequest::with('employee')
                    ->where('type', 'cuti')
                    ->get()
                    ->each(function ($item) use ($data) {
                        $item->request_type = 'cuti';
                        $data->push($item);
                    });

                LeaveRequest::with('employee')
                    ->where('type', 'izin')
                    ->get()
                    ->each(function ($item) use ($data) {
                        $item->request_type = 'izin';
                        $data->push($item);
                    });

                MedicalLeave::with('employee')
                    ->get()
                    ->each(function ($item) use ($data) {
                        $item->request_type = 'sakit';
                        $data->push($item);
                    });

                BusinessTrip::with('employee')
                    ->get()
                    ->each(function ($item) use ($data) {
                        $item->request_type = 'dinas_luar';
                        $data->push($item);
                    });

                $data = $data
                    ->sortByDesc('created_at')
                    ->values();

                break;
        }

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data
        ]);
    }


    public function statistics()
    {
        return response()->json([

            'success' => true,

            'statistics' => [

                'cuti' => LeaveRequest::where('type', 'cuti')->count(),

                'izin' => LeaveRequest::where('type', 'izin')->count(),

                'sakit' => MedicalLeave::count(),

                'dinas_luar' => BusinessTrip::count(),

                'pending' =>
                    LeaveRequest::where('status', 'pending')->count()
                    + MedicalLeave::where('status', 'pending')->count()
                    + BusinessTrip::where('status', 'pending')->count(),

                'approved' =>
                    LeaveRequest::where('status', 'approved')->count()
                    + MedicalLeave::where('status', 'approved')->count()
                    + BusinessTrip::where('status', 'approved')->count(),

                'rejected' =>
                    LeaveRequest::where('status', 'rejected')->count()
                    + MedicalLeave::where('status', 'rejected')->count()
                    + BusinessTrip::where('status', 'rejected')->count(),

                'total' =>
                    LeaveRequest::count()
                    + MedicalLeave::count()
                    + BusinessTrip::count(),
            ]
        ]);
    }


    public function show($type, $id)
    {
        switch ($type) {

            case 'cuti':
                $data = LeaveRequest::with('employee')
                    ->where('type', 'cuti')
                    ->findOrFail($id);
                break;

            case 'izin':
                $data = LeaveRequest::with('employee')
                    ->where('type', 'izin')
                    ->findOrFail($id);
                break;

            case 'sakit':
                $data = MedicalLeave::with('employee')
                    ->findOrFail($id);
                break;

            case 'dinas_luar':
                $data = BusinessTrip::with('employee')
                    ->findOrFail($id);
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis pengajuan tidak ditemukan.'
                ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}