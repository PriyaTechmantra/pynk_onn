<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\UserNoOrderReason;

class DistributorController extends Controller
{
    public function note(Request $request)
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

        $userIds = UserNoOrderReason::pluck('user_id')->unique();
        $distributorIds = UserNoOrderReason::pluck('distributor_id')->unique();

        $users = \App\Models\Employee::whereIn('id', $userIds)->where('status', 1)->get(['id', 'name']);
        $distributors = \App\Models\Distributor::whereIn('id', $distributorIds)->get(['id', 'name']);

        $data = $query->latest('id')
            ->with(['user' => function ($q) {
                $q->where('status', 1);
            }, 'distributor'])
            ->paginate(25);

        return view('distributor.note', compact('data', 'users', 'distributors'));
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

        $data = $query->with(['user', 'distributor'])
                    ->latest('id')
                    ->get();

        $filename = 'distributor_notes_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['User Name', 'Distributor Name', 'Comment', 'Date'];

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