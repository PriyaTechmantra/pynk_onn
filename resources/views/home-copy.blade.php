{{-- @extends('layouts.app')

@section('content')
<div class="container">
     @can('onn daily dashboard')
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-danger">
                <div class="card-body">
                    <h4>VP <i class="fi fi-br-user"></i></h4>
                    <h2><a href="{{ url('employees',['type'=>1]) }}"> {{$data->vp->count()}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-info">
                <div class="card-body">
                    <h4>RSM <i class="fi fi-br-chart-histogram"></i></h4>
                    <h2><a href="{{ url('employees',['type'=>2]) }}"> {{$data->rsm->count()}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h4>ASM <i class="fi fi-br-cube"></i></h4>
                    <h2><a href="{{ url('employees',['type'=>3]) }}"> {{$data->asm->count()}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h4>ASE <i class="fi fi-br-cube"></i></h4>
                    <h2><a href="{{ url('employees',['type'=>4]) }}"> {{$data->ase->count()}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>Distributor <i class="fi fi-br-user"></i></h4>
                    <h2><a href="{{ url('distributors') }}"> {{$data->distributor->count()}}</a></h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>All Store <i class="fi fi-br-chart-histogram"></i></h4>
                    <h2><a href="{{ url('stores') }}">{{$data->allstore}}</a></h2>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>Active Store <i class="fi fi-br-chart-histogram"></i></h4>
                    <h2><a href="{{ url('stores',['status_id'=>'active']) }}">{{$data->store}}</a></h2>
                </div>
            </div>
        </div>
        

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>Today's Primary Order Value <i class="fi fi-br-chart-histogram"></i></h4>
                    {{-- <h2>&#8377; {{number_format($data->primary)}}</h2> --}}
                    <h2><a href="{{ url('primary/order/report',['date_from'=>date('Y-m-d'),'date_to'=>date('Y-m-d')]) }}">&#8377; {{number_format($data->primary)}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>Today Secondary Order Quantity <i class="fi fi-br-chart-histogram"></i></h4>
                    <h2><a href="{{ url('secondary/order/report',['date_from'=>date('Y-m-d'),'date_to'=>date('Y-m-d')]) }}">{{number_format($data->secondary)}}</a></h2>
                </div>
            </div>
        </div>
    </div>
	@php
           //$stateReportNameArray = $stateReportValueArray = [];
            $dayReportNameArray = $dayReportValueArray = [];
            foreach($dayStoreReport as $item){
            $dayReportNameArray[] = ($item->dayname == null) ? 'NA' : $item->dayname;
            $dayReportValueArray[]=($item->count == null) ? 0 : $item->count;
            }
			$monthReportNameArray = $monthReportValueArray = [];
            foreach($monthStoreReport as $item){
            $monthReportNameArray[] = ($item->monthname == null) ? 'NA' : $item->monthname;
            $monthReportValueArray[]=($item->count == null) ? 0 : $item->count;
            }
	        $stateReportNameArray = $stateReportValueArray = [];
            foreach($stateWiseReport as $item){
            $stateReportNameArray[] = ($item->name == null) ? 'NA' : $item->name;
            $stateReportValueArray[]=($item->count == null) ? 0 : $item->count;
            }
        @endphp
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <canvas id="dailyReportDiv" width="600" height="300"></canvas>
                    </div>
                </div>
            </div>
			<div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <canvas id="monthReportDiv" width="600" height="300"></canvas>
                    </div>
                </div>
            </div>
		</div>
	
		  <div class="row mt-4">
				<div class="col-md-6">
					<div class="card h-100">
						<div class="card-body">
							<a href="{{ url('dashboard/store/export/csv') }}" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
							<canvas id="myChart" width="600" height="600"></canvas>
						</div>
					</div>
				</div>
				<div class="col-md-6">
                <div class="card h-100" id="distributorCard" style="max-height: 680px;overflow:hidden">
                    <div class="card-body">
						
                        <h5 class="card-title">ASE wise retailer/store report</h5>
						<a href="{{ url('ase/store/export/csv',['keyword' =>$request->keyword]) }}" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
						<form class="row align-items-end justify-content-end" action="{{url('home')}}">
						
                        <div class="col-auto">
                            <input type="search" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Search by name" value="{{request()->input('keyword')}}" autocomplete="off">
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Filter
                                </button>

                               
                            </div>
                        </div>
                    </form>
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ASE Name</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($aseWiseReport as $aseKey => $item)
							   
                                    <tr>

                                        <td>

                                            <a href="{{url('stores',['ase'=>$item->id])}}"> {{ ($item->name == null) ? 'NA' : $item->name }} ({{$item->state_name}})</a>
                                        </td>
                                        <td> {{number_format($item->count)}}</td>
                                    </tr>
                                    @if($aseKey == 10)
                                    <tr>
                                        <td colspan="100%" class="text-end">
                                            <a href="javascript: void(0)" id="distributorShowMore">Show more</a>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <a href="{{url('stores')}}" class="btn btn-sm btn-danger float-right">View complete report</a>
                    </div>
                </div>
            </div>
			</div>
			
			<div class="row mt-4">
			    
                     
                        
                        <h5 class="card-title">Secondary Order Details</h5>
            			    @foreach($data->monthly_secondary as $month)
                            <div class="col-sm-6 col-lg-3">
                                <div class="card home__card">
                                    <div class="card-body">
                                        <h4>{{ $month->month_name }}</h4>
                                        <h2><a href="{{ url('order/filter/report/data', ['month' => $month->month_name,'year'=>$month->year_name]) }}">
                                            {{ $month->total_qty }}
                                        </a></h2>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            
                     
                    
                
			</div>
	<div class="row mt-4">
		<div class="col-md-12">
                <div class="card h-100" id="aseCard" style="max-height: 530px;overflow:hidden">
                    <div class="card-body">
                        <h5 class="card-title">Today's Inactive ASE report</h5>
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ASE Name</th>
                                    <th>Contact</th>
									<th>Area</th>
									<th>State</th>
									<th>ASM</th>
									<th>RSM</th>
									<th>VP</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($inactiveASE as $aseKey => $item)
							     @php
								   $salesTeam=\App\Models\Team::select('vp_id','rsm_id','asm_id')->where('ase_id', $item->id)->with('vp','rsm','asm','ase')->first();
								@endphp
                                    <tr>

                                        <td>

                                            <a href="{{url('employees',['ase'=>$item->name])}}"> {{ ($item->name == null) ? 'NA' : $item->name }}</a>
                                        </td>
                                        <td> {{$item->mobile ?? ''}}</td>
										 <td> {{$item->area->name ?? ''}}</td>
										 <td> {{$item->stateDetail->name ?? ''}}</td>
										<td> {{$salesTeam->asm->name ?? ''}}</td>
										<td> {{$salesTeam->rsm->name ?? ''}}</td>
										<td> {{$salesTeam->vp->name ?? ''}}</td>
                                    </tr>
                                    @if($aseKey == 5)
                                    <tr>
                                        <td colspan="100%" class="text-end">
                                            <a href="javascript: void(0)" id="aseShowMore">Show more</a>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
	</div>
    @endcan



    @can('pynk dashboard')
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-danger">
                <div class="card-body">
                    <h4>VP <i class="fi fi-br-user"></i></h4>
                    <h2><a href="{{ url('employees',['type'=>1]) }}"> {{$data->vp->count()}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-info">
                <div class="card-body">
                    <h4>RSM <i class="fi fi-br-chart-histogram"></i></h4>
                    <h2><a href="{{ url('employees',['type'=>2]) }}"> {{$data->rsm->count()}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h4>ASM <i class="fi fi-br-cube"></i></h4>
                    <h2><a href="{{ url('employees',['type'=>3]) }}"> {{$data->asm->count()}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h4>ASE <i class="fi fi-br-cube"></i></h4>
                    <h2><a href="{{ url('employees',['type'=>4]) }}"> {{$data->ase->count()}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>Distributor <i class="fi fi-br-user"></i></h4>
                    <h2><a href="{{ url('distributors') }}"> {{$data->distributor->count()}}</a></h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>All Store <i class="fi fi-br-chart-histogram"></i></h4>
                    <h2><a href="{{ url('stores') }}">{{$data->allstore}}</a></h2>
                </div>
            </div>
        </div>
        
        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>Active Store <i class="fi fi-br-chart-histogram"></i></h4>
                    <h2><a href="{{ url('stores',['status_id'=>'active']) }}">{{$data->store}}</a></h2>
                </div>
            </div>
        </div>
        

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>Today's Primary Order Value <i class="fi fi-br-chart-histogram"></i></h4>
                    {{-- <h2>&#8377; {{number_format($data->primary)}}</h2> --}}
                    <h2><a href="{{ url('primary/order/report',['date_from'=>date('Y-m-d'),'date_to'=>date('Y-m-d')]) }}">&#8377; {{number_format($data->primary)}}</a></h2>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-success">
                <div class="card-body">
                    <h4>Today Secondary Order Quantity <i class="fi fi-br-chart-histogram"></i></h4>
                    <h2><a href="{{ url('secondary/order/report',['date_from'=>date('Y-m-d'),'date_to'=>date('Y-m-d')]) }}">{{number_format($data->secondary)}}</a></h2>
                </div>
            </div>
        </div>
    </div>
	@php
           //$stateReportNameArray = $stateReportValueArray = [];
            $dayReportNameArray = $dayReportValueArray = [];
            foreach($dayStoreReport as $item){
            $dayReportNameArray[] = ($item->dayname == null) ? 'NA' : $item->dayname;
            $dayReportValueArray[]=($item->count == null) ? 0 : $item->count;
            }
			$monthReportNameArray = $monthReportValueArray = [];
            foreach($monthStoreReport as $item){
            $monthReportNameArray[] = ($item->monthname == null) ? 'NA' : $item->monthname;
            $monthReportValueArray[]=($item->count == null) ? 0 : $item->count;
            }
	        $stateReportNameArray = $stateReportValueArray = [];
            foreach($stateWiseReport as $item){
            $stateReportNameArray[] = ($item->name == null) ? 'NA' : $item->name;
            $stateReportValueArray[]=($item->count == null) ? 0 : $item->count;
            }
        @endphp
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <canvas id="dailyReportDiv" width="600" height="300"></canvas>
                    </div>
                </div>
            </div>
			<div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <canvas id="monthReportDiv" width="600" height="300"></canvas>
                    </div>
                </div>
            </div>
		</div>
	
		  <div class="row mt-4">
				<div class="col-md-6">
					<div class="card h-100">
						<div class="card-body">
							<a href="{{ url('dashboard/store/export/csv') }}" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
							<canvas id="myChart" width="600" height="600"></canvas>
						</div>
					</div>
				</div>
				<div class="col-md-6">
                <div class="card h-100" id="distributorCard" style="max-height: 680px;overflow:hidden">
                    <div class="card-body">
						
                        <h5 class="card-title">ASE wise retailer/store report</h5>
						<a href="{{ url('ase/store/export/csv',['keyword' =>$request->keyword]) }}" class="btn btn-sm btn-danger text-end" data-bs-toggle="tooltip" title="Export data in CSV">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            </a>
						<form class="row align-items-end justify-content-end" action="{{url('home')}}">
						
                        <div class="col-auto">
                            <input type="search" name="keyword" id="keyword" class="form-control form-control-sm" placeholder="Search by name" value="{{request()->input('keyword')}}" autocomplete="off">
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Filter
                                </button>

                               
                            </div>
                        </div>
                    </form>
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ASE Name</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($aseWiseReport as $aseKey => $item)
							   
                                    <tr>

                                        <td>

                                            <a href="{{url('stores',['ase'=>$item->id])}}"> {{ ($item->name == null) ? 'NA' : $item->name }} ({{$item->state_name}})</a>
                                        </td>
                                        <td> {{number_format($item->count)}}</td>
                                    </tr>
                                    @if($aseKey == 10)
                                    <tr>
                                        <td colspan="100%" class="text-end">
                                            <a href="javascript: void(0)" id="distributorShowMore">Show more</a>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <a href="{{url('stores')}}" class="btn btn-sm btn-danger float-right">View complete report</a>
                    </div>
                </div>
            </div>
			</div>
			
			<div class="row mt-4">
			    
                     
                        
                        <h5 class="card-title">Secondary Order Details</h5>
            			    @foreach($data->monthly_secondary as $month)
                            <div class="col-sm-6 col-lg-3">
                                <div class="card home__card">
                                    <div class="card-body">
                                        <h4>{{ $month->month_name }}</h4>
                                        <h2><a href="{{ url('order/filter/report/data', ['month' => $month->month_name,'year'=>$month->year_name]) }}">
                                            {{ $month->total_qty }}
                                        </a></h2>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            
                     
                    
                
			</div>
	<div class="row mt-4">
		<div class="col-md-12">
                <div class="card h-100" id="aseCard" style="max-height: 530px;overflow:hidden">
                    <div class="card-body">
                        <h5 class="card-title">Today's Inactive ASE report</h5>
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>ASE Name</th>
                                    <th>Contact</th>
									<th>Area</th>
									<th>State</th>
									<th>ASM</th>
									<th>RSM</th>
									<th>VP</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($inactiveASE as $aseKey => $item)
							     @php
								   $salesTeam=\App\Models\Team::select('vp_id','rsm_id','asm_id')->where('ase_id', $item->id)->with('vp','rsm','asm','ase')->first();
								@endphp
                                    <tr>

                                        <td>

                                            <a href="{{url('employees',['ase'=>$item->name])}}"> {{ ($item->name == null) ? 'NA' : $item->name }}</a>
                                        </td>
                                        <td> {{$item->mobile ?? ''}}</td>
										 <td> {{$item->area->name ?? ''}}</td>
										 <td> {{$item->stateDetail->name ?? ''}}</td>
										<td> {{$salesTeam->asm->name ?? ''}}</td>
										<td> {{$salesTeam->rsm->name ?? ''}}</td>
										<td> {{$salesTeam->vp->name ?? ''}}</td>
                                    </tr>
                                    @if($aseKey == 5)
                                    <tr>
                                        <td colspan="100%" class="text-end">
                                            <a href="javascript: void(0)" id="aseShowMore">Show more</a>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
	</div>
    @endcan
</div>
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>

    <script>

        // daily report
        var labelValues0 = [];
        var dataValues0 = [];
        var barColors = ["red", "green","blue","orange","brown","orange","grey"];
        labelValues0 = <?php echo json_encode($dayReportNameArray); ?>;
       // labelValues0 = ["Monday", "Tuesday", "Wednesday","Thursday", "Friday", "Saturday","Sunday"];
        dataValues0 = <?php echo json_encode($dayReportValueArray); ?>;

        // console.log(labelValues0);

        /*const ctx0 = document.getElementById('dailyReportDiv').getContext('2d');
        const dailyReportDiv = new Chart(ctx0, {
            type: 'bar',
            data: {
                labels: labelValues0,
                datasets: [{
                    label: 'Monday',
                    data: dataValues0,
                    backgroundColor: barColors,
                    // backgroundColor: [
                    //     'rgba(255, 99, 132, 0.2)',
                    //     'rgba(54, 162, 235, 0.2)',
                    //     'rgba(255, 206, 86, 0.2)',
                    //     'rgba(75, 192, 192, 0.2)'
                    // ],
                    borderWidth: 1
                }]
            },
            options: {
               
                
				title: {
			  display: true,
			  text: "Weekly Retailer/Store add report"
			}
            }
        });*/
		new Chart("dailyReportDiv", {
		  type: "bar",
		  data: {
			labels: labelValues0,
			datasets: [{
			  backgroundColor: barColors,
			  data: dataValues0
			}]
		  },
		  options: {
			legend: {display: false},
			title: {
			  display: true,
			  text: "Weekly Retailer/Store add report"
			}

		  }
		});
		</script>
       <script>
		//monthly report
        var labelValues0 = [];
        var dataValues0 = [];
        var barColors = ["red", "green","blue","orange","brown","orange","grey"];
        labelValues0 = <?php echo json_encode($monthReportNameArray); ?>;
        dataValues0 = <?php echo json_encode($monthReportValueArray); ?>;

        // console.log(labelValues0);

       /* const ctx1 = document.getElementById('monthReportDiv').getContext('2d');
        const monthReportDiv = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: labelValues0,
                datasets: [{
                    label: 'January',
                    data: dataValues0,
                    backgroundColor: barColors,
                    // backgroundColor: [
                    //     'rgba(255, 99, 132, 0.2)',
                    //     'rgba(54, 162, 235, 0.2)',
                    //     'rgba(255, 206, 86, 0.2)',
                    //     'rgba(75, 192, 192, 0.2)'
                    // ],
                    borderWidth: 1
                }]
            },
            options: {
               
                
				title: {
			  display: true,
			  text: "Monthly Retailer/Store add report"
			}
            }
        });*/
  		new Chart("monthReportDiv", {
		  type: "line",
		  data: {
			labels: labelValues0,
			datasets: [{
			  backgroundColor: barColors,
			  data: dataValues0
			}]
		  },
		  options: {
			legend: {display: false},
			title: {
			  display: true,
			  text: "Monthly Retailer/Store add report"
			}

		  }
		});
      
    </script>
	<script>
		var xValues = [];
		var yValues = [];
		var barColors = [
		  "#b91d47",
		  "#00aba9",
		  "#2b5797",
		  "#e8c3b9",
		  "#1e7145",
		  "#ADD8E6",
		  "#FFA500",
		  "#52595D",
		  "#C9C0BB",
		  "#C9C0BB",
		  "#838996",
		  "#566D7E",
		  "#151B54",
		  "#0000CD",
		  "#2554C7",
		  "#357EC7",
		  "#3090C7",
		  "#3BB9FF",
		  "#B7CEEC",
		  "#C6DEFF",
		  "#E3E4FA",
		  "#EBF4FA",
		  "#00FFFF",
		  "#81D8D0",
		  "#48D1CC",
		  "#3EA99F",
		  "#808000",
		  "#4E5B31",
		  "#347235",
		 "#004225",
		 "#3F9B0B",
		 "#9DC209",
		"#DAEE01",
		 "#FFFACD"
		];
		xValues = <?php echo json_encode($stateReportNameArray); ?>;
        yValues = <?php echo json_encode($stateReportValueArray); ?>;
		console.log(yValues);
		new Chart("myChart", {
		  type: "pie",
		  data: {
			labels: xValues,
			datasets: [{
			  backgroundColor: barColors,
			  data: yValues
			}]
		  },
		  options: {
			title: {
			  display: true,
			  text: "State wise retailer/store report"
			}
		  }
		});
	</script>
<script>
	  $('#distributorShowMore').on('click', function() {
            $(this).parent().parent().hide();
            $('#distributorCard').css('maxHeight', '100%');
        });
	 $('#aseShowMore').on('click', function() {
            $(this).parent().parent().hide();
            $('#aseCard').css('maxHeight', '100%');
        });
</script>
@endsection --}}