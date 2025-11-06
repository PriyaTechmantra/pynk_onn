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
        //  Validation
        $request->validate([
            'ase' => 'required|array',
            'name' => 'required|string|min:2|max:255',
            'bussiness_name' => 'required|string|min:2|max:255',
            'distributor_id' => 'required|array',
            'owner_name' => 'required|string|max:255',
            'gst_no' => 'nullable|string|max:255',
            'contact' => 'required|integer|digits:10',
            'whatsapp' => 'nullable|integer|digits:10',
            'email' => 'nullable|email',
            'date_of_birth' => 'nullable|date',
            'date_of_anniversary' => 'nullable|date',
            'address' => 'required|string|max:500',
            'area' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'pin' => 'required|integer|digits:6',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000000',
            'pan' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10000000',
        ]);

        // Collect ASE + Distributor data
        $aseEmployees = Employee::whereIn('id', $request->ase)->get();
        if ($aseEmployees->isEmpty()) {
            return back()->with('error', 'Please change distributor. No ASE found as user.');
        }

        $aseIds = $aseEmployees->pluck('id')->toArray();
        $aseNames = $aseEmployees->pluck('name')->toArray();
        $distributorIds = $request->distributor_id;

        // Fetch team hierarchy based on ASE + Distributor
        $teams = Team::where(function ($query) use ($aseIds) {
                foreach ($aseIds as $aseId) {
                    $query->orWhereRaw("FIND_IN_SET(?, ase_id)", [$aseId]);
                }
            })
            ->where(function ($query) use ($distributorIds) {
                foreach ($distributorIds as $distId) {
                    $query->orWhereRaw("FIND_IN_SET(?, distributor_id)", [$distId]);
                }
            })
            ->orderBy('id', 'ASC')
            ->groupBy('distributor_id')
            ->get();

        $vpIds = $teams->pluck('vp_id')->filter()->unique()->implode(',');
        $rsmIds = $teams->pluck('rsm_id')->filter()->unique()->implode(',');
        $asmIds = $teams->pluck('asm_id')->filter()->unique()->implode(',');

        //  Update Store details
        $store = Store::findOrFail($id);

        // If name changed, regenerate slug
        if ($store->name !== $request->name) {
            $slug = Str::slug($request->name, '-');
            $exists = Store::where('slug', $slug)->where('id', '!=', $id)->exists();
            if ($exists) {
                $slug .= '-' . time();
            }
            $store->slug = $slug;
        }

        // Update fields
        $store->fill([
            'user_id' => implode(',', $request->ase),
            'gst_no' => $request->gst_no,
            'name' => $request->name,
            'bussiness_name' => $request->bussiness_name,
            'retailer_list_occ_id' => $request->retailer_list_of_occ_id ?? null,
            'store_OCC_number' => $request->store_OCC_number ?? null,
            'owner_name' => $request->owner_name,
            'owner_lname' => $request->owner_lname,
            'contact' => $request->contact,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'date_of_birth' => $request->date_of_birth,
            'date_of_anniversary' => $request->date_of_anniversary,
            'address' => $request->address,
            'area_id' => $request->area,
            'pan_no' => $request->pan_no,
            'state_id' => $request->state,
            'city' => $request->city,
            'pin' => $request->pin,
            'contact_person' => $request->contact_person,
            'contact_person_lname' => $request->contact_person_lname,
            'contact_person_phone' => $request->contact_person_phone,
            'contact_person_whatsapp' => $request->contact_person_whatsapp,
            'contact_person_date_of_birth' => $request->contact_person_date_of_birth,
            'contact_person_date_of_anniversary' => $request->contact_person_date_of_anniversary,
        ]);

        // Handle image uploads
        if ($request->hasFile('image')) {
            $imageName = mt_rand() . '.' . $request->image->extension();
            $uploadPath = 'public/uploads/store';
            $request->image->move($uploadPath, $imageName);
            $store->image = $uploadPath . '/' . $imageName;
        }

        if ($request->hasFile('pan')) {
            $panName = mt_rand() . '.' . $request->pan->extension();
            $uploadPath = 'public/uploads/retailer/document';
            $request->pan->move($uploadPath, $panName);
            $store->pan = $uploadPath . '/' . $panName;
        }

        $store->updated_at = now();
        $store->save();

        // 6. Update Retailer OCC team mapping
        if (!empty($request->retailer_list_of_occ_id)) {
            $retailerListOfOcc = Team::find($request->retailer_list_of_occ_id);
            if ($retailerListOfOcc) {
                $retailerListOfOcc->fill([
                    'vp_id' => $vpIds,
                    'rsm_id' => $request->rsm ?? $rsmIds,
                    'asm_id' => $request->asm ?? $asmIds,
                    'ase_id' => implode(',', array_filter($aseIds)),
                    'distributor_id' => implode(',', $distributorIds),
                    'state_id' => $request->state,
                    'area_id' => $request->area,
                    'status' => '1',
                    'is_deleted' => '0',
                ]);
                $retailerListOfOcc->save();
            }
        }

        // Redirect with success
        return redirect()->back()->with('success', 'Store information updated successfully.');
    }


}
