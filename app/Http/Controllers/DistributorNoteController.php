<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\UserNoOrderReason;
use App\Models\Employee;
use App\Models\Distributor;

class DistributorNoteController extends Controller
{
    public function note(Request $request)
    {
        $query = UserNoOrderReason::query();

        if (!empty($request->term)) {
            $query->where('comment', 'LIKE', '%' . $request->term . '%');
        }
        if (!empty($request->brand_selection)) {
            $brand = $request->brand_selection;

            if ($brand == '1') {
                $query->whereIn('brand', [1, 3]);
            } elseif ($brand == '2') {
                $query->whereIn('brand', [2, 3]);
            } elseif ($brand == '3') {
                $query->where('brand', 3);
            }
        }

        if ($request->filled('user_name')) {
            $query->where('user_id', $request->user_name);
        }

        if ($request->filled('distributor_name')) {
            $query->where('distributor_id', $request->distributor_name);
        }

        // Filter by user_type
        if ($request->filled('user_type')) {
            $userIds = Employee::where('status', 1)
                ->where('is_deleted', 0)
                ->where('type', $request->user_type)
                ->pluck('id');

            $query->whereIn('user_id', $userIds);
        }

        $userIds = UserNoOrderReason::pluck('user_id')->unique();
        $distributorIds = UserNoOrderReason::pluck('distributor_id')->unique();

        $users = Employee::whereIn('id', $userIds)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get(['id', 'name']);

        $distributors = Distributor::whereIn('id', $distributorIds)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get(['id', 'name']);

        $data = $query->latest('id')
            ->with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'distributor' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                }
            ])
            ->paginate(25);

        // Get available user types for the dropdown
        $availableUserTypes = Employee::whereIn('id', $userIds)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->pluck('type')
            ->unique()
            ->toArray();

        return view('distributor.note', compact('data', 'users', 'distributors', 'availableUserTypes'));
    }


    public function noteCSV(Request $request)
    {
        $query = UserNoOrderReason::query();

        if (!empty($request->term)) {
            $query->where('comment', 'LIKE', '%' . $request->term . '%');
        }

        if (!empty($request->brand_selection)) {
            $brands = explode(',', $request->brand_selection);

            $query->where(function ($q) use ($brands) {
                foreach ($brands as $brand) {
                    switch ($brand) {
                        case '1':
                            $q->orWhereJsonContains('brand', '1')
                            ->orWhereJsonContains('brand', '3');
                            break;
                        case '2':
                            $q->orWhereJsonContains('brand', '2')
                            ->orWhereJsonContains('brand', '3');
                            break;
                        case '3':
                            $q->orWhere(function ($q2) {
                                $q2->whereJsonContains('brand', '1')
                                ->whereJsonContains('brand', '2');
                            })->orWhereJsonContains('brand', '3');
                            break;
                    }
                }
            });
        }

        if ($request->filled('user_name')) {
            $query->where('user_id', $request->user_name);
        }

        if ($request->filled('distributor_name')) {
            $query->where('distributor_id', $request->distributor_name);
        }
        if ($request->filled('user_type')) {
            $userIds = Employee::where('status', 1)
                ->where('is_deleted', 0)
                ->where('type', $request->user_type)
                ->pluck('id');

            $query->whereIn('user_id', $userIds);
        }

        $data = $query->latest('id')
            ->with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'distributor' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                }
            ])
            ->get();


        $filename = 'distributor_notes_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['User', 'Distributor', 'Comment', 'Date'];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $item) {
                fputcsv($file, [
                    optional($item->user)->name ?? '',
                    optional($item->distributor)->name ?? '',
                    $item->comment,
                    $item->date.' '.$item->time,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}