<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\DistributorMom;
use App\Models\Employee;
use App\Models\Distributor;
use Auth;
use DB;
use Hash;
class DistributorNoteController extends Controller
{
    public function note(Request $request)
    {
        $user = auth()->user();
        $userBrands = DB::table('user_permission_categories')
                ->where('user_id', Auth::id())
                ->pluck('brand')
                ->toArray();
        
            $brandsToShow = [];

            if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
                // Both brands access
                $brandsToShow = [1, 2, 3];
            } elseif (in_array(1, $userBrands)) {
                $brandsToShow = [1];
            } elseif (in_array(2, $userBrands)) {
                $brandsToShow = [2];
            }
        $query = DistributorMom::query();

        if (!empty($request->term)) {
            $query->where('comment', 'LIKE', '%' . $request->term . '%');
        }
        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('directory_mom.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('directory_mom.brand', $request->brand_selection)
                    ;
                }
            });
        } else {
            // if brand not selected — show according to user permission
            $userBrandPermissions = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            if (!empty($userBrandPermissions)) {
                $query->where(function ($q) use ($userBrandPermissions) {
                    if (in_array(3, $userBrandPermissions)) {
                        // user has both brand permission
                        $q->whereIn('directory_mom.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('directory_mom.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
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

        $userIds = DistributorMom::pluck('user_id')->unique();
        $distributorIds = DistributorMom::pluck('distributor_id')->unique();

        $users = Employee::where('type', 4)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get(['id', 'name']);

        $distributors = Distributor::where('status', 1)
            ->where('is_deleted', 0)
            ->get(['id', 'name']);

        $data = $query->latest('id')
            ->with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'distributors' => function ($q) {
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
        $user = auth()->user();
        $userBrands = DB::table('user_permission_categories')
                ->where('user_id', Auth::id())
                ->pluck('brand')
                ->toArray();
        
            $brandsToShow = [];

            if (in_array(3, $userBrands) || (in_array(1, $userBrands) && in_array(2, $userBrands))) {
                // Both brands access
                $brandsToShow = [1, 2, 3];
            } elseif (in_array(1, $userBrands)) {
                $brandsToShow = [1];
            } elseif (in_array(2, $userBrands)) {
                $brandsToShow = [2];
            }
        $query = DistributorMom::query();

        if (!empty($request->term)) {
            $query->where('comment', 'LIKE', '%' . $request->term . '%');
        }
        if ($request->filled('brand_selection')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand_selection == 3) {
                    // “Both” selected → show ONN (1), PYNK (2), and Both (3)
                    $q->whereIn('directory_mom.brand', [1, 2, 3]);
                } else {
                    // single brand selected → include that + both
                    $q->where('directory_mom.brand', $request->brand_selection)
                    ;
                }
            });
        } else {
            // if brand not selected — show according to user permission
            $userBrandPermissions = DB::table('user_permission_categories')
                ->where('user_id', $user->id)
                ->pluck('brand')
                ->toArray();

            if (!empty($userBrandPermissions)) {
                $query->where(function ($q) use ($userBrandPermissions) {
                    if (in_array(3, $userBrandPermissions)) {
                        // user has both brand permission
                        $q->whereIn('directory_mom.brand', [1, 2, 3]);
                    } else {
                        // user has limited brand(s)
                        $q->whereIn('directory_mom.brand', array_merge($userBrandPermissions, [3]));
                    }
                });
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

        $userIds = DistributorMom::pluck('user_id')->unique();
        $distributorIds = DistributorMom::pluck('distributor_id')->unique();

        $users = Employee::where('type', 4)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get(['id', 'name']);

        $distributors = Distributor::where('status', 1)
            ->where('is_deleted', 0)
            ->get(['id', 'name']);

        $data = $query->latest('id')
            ->with([
                'user' => function ($q) {
                    $q->where('status', 1)->where('is_deleted', 0);
                },
                'distributors' => function ($q) {
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
                    optional($item->distributors)->name ?? '',
                    $item->comment,
                     date('j M Y g:i A', strtotime($item->created_at)),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}