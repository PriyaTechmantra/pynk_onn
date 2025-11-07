<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Distributor;
use App\Models\User;
use App\Models\Employee;
use App\Models\State;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Auth;
class RetailerUserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        /**
         * STEP 1: Determine which brands this user can see
         */
        $userBrands = DB::table('user_permission_categories')
            ->where('user_id', $user->id)
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

        /**
         * STEP 2: Base query — join teams table for distributor relation
         */
        $query = Store::select('stores.*', 'teams.distributor_id')
            ->join('teams', 'teams.store_id', '=', 'stores.id');

        /**
         * STEP 3: Brand filter
         */
        if ($request->filled('brand')) {
            $query->where(function ($q) use ($request) {
                if ($request->brand == 3) {
                    $q->whereIn('stores.brand', [1, 2, 3]);
                } else {
                    $q->where('stores.brand', $request->brand)
                        ->orWhere('stores.brand', 3);
                }
            });
        } else {
            // Apply user's brand permission if not manually filtered
            if (!empty($brandsToShow)) {
                $query->where(function ($q) use ($brandsToShow) {
                    if (in_array(3, $brandsToShow)) {
                        $q->whereIn('stores.brand', [1, 2, 3]);
                    } else {
                        $q->whereIn('stores.brand', array_merge($brandsToShow, [3]));
                    }
                });
            }
        }

        /**
         * STEP 4: Date range filter
         */
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $from = date('Y-m-d 00:00:00', strtotime($request->date_from));
            $to   = date('Y-m-d 23:59:59', strtotime($request->date_to));
            $query->whereBetween('stores.created_at', [$from, $to]);
        }

        /**
         * STEP 5: Distributor, ASE, State, Area filters
         */
        if ($request->filled('distributor')) {
            $query->whereRaw("find_in_set(?, teams.distributor_id)", [$request->distributor]);
        }

        if ($request->filled('ase')) {
            $query->whereRaw("find_in_set(?, stores.user_id)", [$request->ase]);
        }

        if ($request->filled('state')) {
            $query->where('stores.state_id', $request->state);
        }

        if ($request->filled('area')) {
            $query->where('stores.area_id', $request->area);
        }

        /**
         * STEP 6: Status filter
         */
        if ($request->filled('status_id')) {
            $query->where('stores.status', $request->status_id === 'active' ? 1 : 0);
        }

        /**
         * STEP 7: Keyword search
         */
       if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('stores.name', 'like', "%$keyword%")
                ->orWhere('stores.unique_code', 'like', "%$keyword%")
                ->orWhere('stores.contact', 'like', "%$keyword%");
            });
        }

        /**
         * STEP 8: Execute query
         */
        $data = $query->where('stores.is_deleted', 0)
            ->orderBy('stores.id', 'desc')
            ->paginate(25);

        /**
         * STEP 9: Dropdown data
         */
        $allASEs = Employee::whereIn('brand', $brandsToShow)
            ->where('type', 4)
            ->whereNotNull('name')
            ->groupBy('name')
            ->orderBy('name')
            ->with('stateDetail')
            ->get();

        $allDistributors = Distributor::whereIn('brand', $brandsToShow)
            ->whereNotNull('name')
            ->groupBy('name')
            ->orderBy('name')
            ->with('states')
            ->get();

        $state = State::groupBy('name')->orderBy('name')->get();
        $inactiveStore = Store::where('status', 0)->groupBy('name')->get();

        /**
         * STEP 10: Return view
         */
        return view('reward.user.index', compact('data', 'allASEs', 'allDistributors', 'state', 'request', 'inactiveStore'));
    }

    public function status(Request $request, $id)
    {
        $storeData = State::findOrFail($id);

        $status = ($storeData->status == 1) ? 0 : 1;
        $storeData->status = $status;
        $storeData->save();

        if ($storeData) {
            return redirect()->back()->with('success','Status Updated');
            // return redirect()->route('admin.user.list');
        } else {
            return redirect()->route('admin.reward.retailer.user.index')->withInput($request->all());
        }
    }
    public function show(Request $request,string $id)
    {
        $data=Store::findOrfail($id);
        return view('reward.user.view', compact('data','request'));
    }

    public function edit(Request $request,string $id)
    {
        $data=Store::findOrfail($id);
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
        $allASEs = Employee::whereIn('brand',$brandsToShow)->where('type',4)->where('name', '!=', null)->groupBy('name')->orderBy('name')->with('stateDetail')->get();
        
        $allDistributors = Distributor::whereIn('brand',$brandsToShow)->where('name', '!=', null)->groupBy('name')->orderBy('name')->with('states')->get();
        $state = State::groupBy('name')->orderBy('name')->get();
        return view('reward.user.edit', compact('data','request','allASEs','allDistributors','state'));
    }

	public function update(Request $request, string $id)
    {
        $request->validate([
            'owner_name'        => 'required|string|max:255',
            'shop_name'         => 'required|string|min:2|max:255',
            'shop_address'      => 'required|string|max:500',
            'contact'           => 'required|digits:10',
            'whatsapp_contact'  => 'nullable|digits:10',
            'state'             => 'required|integer',
            'area'              => 'nullable|integer',
            'brand'             => 'nullable|integer|in:1,2,3',
            'aadhar'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
            'pan'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
            'gst'               => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000',
        ]);

        $store = Store::findOrFail($id);

        if ($store->name !== $request->shop_name) {
            $slug = Str::slug($request->shop_name, '-');
            $exists = Store::where('slug', $slug)->where('id', '!=', $id)->exists();
            if ($exists) {
                $slug .= '-' . time();
            }
            $store->slug = $slug;
        }

        $store->owner_name   = $request->owner_name;
        $store->name         = $request->shop_name;
        $store->address      = $request->shop_address;
        $store->contact      = $request->contact;
        $store->whatsapp     = $request->whatsapp_contact;
        $store->state_id     = $request->state;
        $store->area_id      = $request->area;
        $store->brand        = $request->brand;


          $upload_path = "public/uploads/new-store/";

        if (isset($request['aadhar'])) {
            $image = $request['aadhar'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $store->aadhar = $upload_path . $uploadedImage;
        }
        if (isset($request['pan'])) {
            $image = $request['pan'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $store->pan = $upload_path . $uploadedImage;
        }
        if (isset($request['gst'])) {
            $image = $request['gst'];
            $imageName = time() . "." . $image->getClientOriginalName();
            $image->move($upload_path, $imageName);
            $uploadedImage = $imageName;
            $store->gst = $upload_path . $uploadedImage;
        }

        $store->updated_at = now();
        $store->save();

        return redirect()
            ->route('reward.retailer.user.index')
            ->with('success', 'Store information updated successfully.');
    }
   

    public function destroy($id)
    {
        $data = Store::find($id);

        if (!$data) {
            return redirect()->route('reward.retailer.user.index')
                ->with('error', 'Store not found.');
        }

        // Soft delete (mark as deleted)
        $data->is_deleted = 1;
        $data->save();

        return redirect()->route('reward.retailer.user.index')
            ->with('success', 'Store deleted successfully.');
    }

    public function exportCSV(Request $request)
    {
        // dd($request->all());
        $query = Store::query(); // Example model

        // Apply filters if present
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }
        if ($request->filled('state')) {
            $query->where('state_id', $request->state);
        }
        if ($request->filled('distributor')) {
            $query->where('distributor_id', $request->distributor);
        }

        // Fetch data
        $data = $query->get();

        // Prepare CSV
        $csvData = [];
        foreach ($data as $item) {
            $csvData[] = [
                'Store Name' => $item->name,
                'Contact' => $item->contact,
                'Distributor' => $item->name,
                'Date' => $item->created_at->format('Y-m-d'),
                // Add more fields as needed
            ];
        }

        // CSV Headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stores.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Open output stream
        $handle = fopen('php://output', 'w');
        fputcsv($handle, array_keys($csvData[0])); // Write header row

        // Write rows
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return response()->stream(
            function () {
                // Output the data
            },
            200,
            $headers
        );
    }


}
