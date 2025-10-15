<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('backend/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css" rel="stylesheet">
    <link href="{{ asset('backend/css/style.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('backend/images/logo.png') }}" type="image/x-icon">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/fontawesome.min.css" integrity="sha512-cHxvm20nkjOUySu7jdwiUxgGy11vuVPE9YeK89geLMLMMEOcKFyS2i+8wo0FOwyQO/bL8Bvq1KMsqK4bbOsPnA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
    <title>Pynk & Onn Admin </title>
	
	<style>
		.page-item.active .page-link {
			background-color: #dc3545;
			border-color: #dc3545;
		}
		.page-link, .page-link:hover, .page-link:focus {
			color: #dc3545;
			box-shadow: none;
		}
	</style>
</head>

<body>
    <aside class="side__bar shadow-sm">
        <div class="admin__logo">
            <div class="logo">
                {{-- <svg width="322" height="322" viewBox="0 0 322 322" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="231.711" y="47.8629" width="60" height="260" rx="30" transform="rotate(45 231.711 47.8629)" fill="#c10909" />
                    <rect x="236.66" y="137.665" width="60" height="180" rx="30" transform="rotate(45 236.66 137.665)" fill="#c10909" />
                    <rect x="141.908" y="42.9132" width="60" height="180" rx="30" transform="rotate(45 141.908 42.9132)" fill="#c10909" />
                </svg> --}}
                <img src="{{ asset('backend/images/logo.png') }}">
            </div>
            <div class="admin__info" style="width: 100% ; overflow : hidden" >
                <h1>{{ Auth::user()->name }}</h1>
                <p style="overflow : hidden;whitespace: narrow font-size:12px;font-size: 12px;" >{{ Auth::user()->email }}</p>
            </div>
        </div>

        <nav class="main__nav">
            <ul>
                <li class="{{ ( request()->is('home*') ) ? 'active' : '' }}"><a href="{{ route('home') }}"><i class="fi fi-br-home"></i> <span>Dashboard</span></a></li>
                @can('view user')
                <li class="{{ ( request()->is('users*') ) ? 'active' : '' }}"><a href="{{ route('users.index') }}"><i class="fi fi-br-user"></i> <span>Admin User Management</span></a></li>
                @endcan
                @can('view role')
                <li class="{{ ( request()->is('roles*') ) ? 'active' : '' }}"><a href="{{ route('roles.index') }}"><i class="fi fi-br-users-alt"></i> <span>Role Management</span></a></li>
                @endcan
                @can('view permission')
                <li class="{{ ( request()->is('permissions*') ) ? 'active' : '' }}"><a href="{{ route('permissions.index') }}"><i class="fi fi-br-chart-user"></i> <span>Permission Management</span></a></li>
                @endcan
                @can('view state')
                        <li class="{{ ( request()->is('states*') ) ? 'active' : '' }}"><a href="{{ route('states.index') }}"><i class="fi fi-br-database"></i> <span>State Management</span></a></li>
                @endcan
                @can('view area')
                        <li class="{{ ( request()->is('areas*') ) ? 'active' : '' }}"><a href="{{route('areas.index')}}"><i class="fi fi-br-database"></i> <span>Area Management</span></a></li>
                @endcan
                
                
               
                
               @can('view employee')
                <li class="@if(request()->is('employees*')||request()->is('employees/hierarchy*')||request()->is('activities*')||request()->is('employees/notifications*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Employee</span></a>
                    <ul>
                        
                        
                        @can('view employee')
                        <li class="{{ ( request()->is('employees*') ) ? 'active' : '' }}"><a href="{{ route('employees.index') }}"><i class="fi fi-br-user"></i> <span>Employee Management</span></a></li>
                        @endcan
                        @can('view employee hierarchy')
                        <li class="{{ ( request()->is('employees/hierarchy*') ) ? 'active' : '' }}"><a href="{{route('employees.hierarchy')}}"><i class="fi fi-br-database"></i> <span>Employee Hierarchy</span></a></li>
                        @endcan
                        @can('view employee activities')
                        <li class="{{ ( request()->is('activities*') ) ? 'active' : '' }}"><a href="{{route('activities.index')}}"><i class="fi fi-br-book"></i> <span>Activity Management</span></a></li>
                        @endcan
                        @can('view notifications')
                        <li class="{{ ( request()->is('employees/notifications*') ) ? 'active' : '' }}"><a href="{{route('notifications.index')}}"><i class="fi fi-br-book"></i> <span>Notification</span></a></li>
                        @endcan
                        
                    </ul>
                </li>
                @endcan

                @can('view distributor')
                <li class="@if(request()->is('distributors*')||request()->is('distributors/note*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Distributor</span></a>
                    <ul>
                        
                        
                        @can('view distributor')
                        <li class="{{ ( request()->is('distributors*') ) ? 'active' : '' }}"><a href="{{ route('distributors.index') }}"><i class="fi fi-br-user"></i> <span>Distributor Management</span></a></li>
                        @endcan
                        @can('view distributor mom')
                        <li class="{{ ( request()->is('distributors/note*') ) ? 'active' : '' }}"><a href="{{route('distributors.note')}}"><i class="fi fi-br-database"></i> <span>Note</span></a></li>
                        @endcan
                        
                        
                    </ul>
                </li>
                @endcan
                
                @can('view collection')
                <li class="@if(request()->is('collections*')||request()->is('categories*')||request()->is('colors*')||request()->is('sizes*')||request()->is('products*')||request()->is('catalogues*')||request()->is('schemes*')||request()->is('news*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Product Master</span></a>
                    <ul>
                        
                        
                        @can('view collection')
                        <li class="{{ ( request()->is('collections*') ) ? 'active' : '' }}"><a href="{{ route('collections.index') }}"><i class="fi fi-br-user"></i> <span>Collection</span></a></li>
                        @endcan
                        @can('view category')
                        <li class="{{ ( request()->is('categories*') ) ? 'active' : '' }}"><a href="{{route('categories.index')}}"><i class="fi fi-br-database"></i> <span>Category</span></a></li>
                        @endcan
                        @can('view color')
                        <li class="{{ ( request()->is('colors*') ) ? 'active' : '' }}"><a href="{{route('colors.index')}}"><i class="fi fi-br-book"></i> <span>Color</span></a></li>
                        @endcan
                        @can('view size')
                        <li class="{{ ( request()->is('sizes*') ) ? 'active' : '' }}"><a href="{{route('sizes.index')}}"><i class="fi fi-br-book"></i> <span>Size</span></a></li>
                        @endcan
                       
                        @can('view product')
                        <li class="{{ ( request()->is('products*') ) ? 'active' : '' }}"><a href="{{route('products.index')}}"><i class="fi fi-br-book"></i> <span>Product</span></a></li>
                        @endcan
                        @can('view catalogue')
                        <li class="{{ ( request()->is('catalogues*') ) ? 'active' : '' }}"><a href="{{route('catalogues.index')}}"><i class="fi fi-br-book"></i> <span>Catalogue</span></a></li>
                        @endcan
                        @can('view scheme')
                        <li class="{{ ( request()->is('schemes*') ) ? 'active' : '' }}"><a href="{{route('schemes.index')}}"><i class="fi fi-br-book"></i> <span>Scheme</span></a></li>
                        @endcan
                         @can('view news')
                        <li class="{{ ( request()->is('news*') ) ? 'active' : '' }}"><a href="{{route('news.index')}}"><i class="fi fi-br-book"></i> <span>News</span></a></li>
                        @endcan
                    </ul>
                </li>
                @endcan
                @can('view store')
                <li class="@if(request()->is('stores*')||request()->is('stores/noorderreason*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Store</span></a>
                    <ul>
                        
                        
                        @can('view store')
                        <li class="{{ ( request()->is('stores*') ) ? 'active' : '' }}"><a href="{{ route('stores.index') }}"><i class="fi fi-br-box"></i> <span>Management</span></a></li>
                        @endcan
                        @can('view no order reason')
                        <li class="{{ ( request()->is('stores/noorderreason*') ) ? 'active' : '' }}"><a href="{{route('stores.noorderreason')}}"><i class="fi fi-br-database"></i> <span>No Order Reason</span></a></li>
                        @endcan
                        
                        
                    </ul>
                </li>
                @endcan
                @can('view primary order')
                <li class="@if(request()->is('primary/order*')||request()->is('secondary/order*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Order</span></a>
                    <ul>
                        
                        
                        @can('view primary order')
                        <li class="{{ ( request()->is('primary/order*') ) ? 'active' : '' }}"><a href="{{ route('primary.orders.index') }}"><i class="fi fi-br-box"></i> <span>Primary Order</span></a></li>
                        @endcan
                        @can('view secondary order')
                        <li class="{{ ( request()->is('secondary/order*') ) ? 'active' : '' }}"><a href="{{route('secondary.orders.index')}}"><i class="fi fi-br-database"></i> <span>Secondary Order</span></a></li>
                        @endcan
                        
                        
                    </ul>
                </li>
                @endcan

                @can('view primary order report')
                <li class="@if(request()->is('primary/order/report*')||request()->is('secondary/order/report*')||request()->is('attendance/report*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Report</span></a>
                    <ul>
                        
                        
                        @can('view primary order report')
                        <li class="{{ ( request()->is('primary/order/report*') ) ? 'active' : '' }}"><a href="{{ route('primary.order.report') }}"><i class="fi fi-br-box"></i> <span>Primary Order Report</span></a></li>
                        @endcan
                        @can('view secondary order report')
                        <li class="{{ ( request()->is('secondary/order/report*') ) ? 'active' : '' }}"><a href="{{route('secondary.order.report')}}"><i class="fi fi-br-database"></i> <span>Secondary Order Report</span></a></li>
                        @endcan
                        @can('view attendance report')
                        <li class="{{ ( request()->is('attendance/report*') ) ? 'active' : '' }}"><a href="{{route('attendance.report')}}"><i class="fi fi-br-database"></i> <span>Attendance Report</span></a></li>
                        @endcan
                        
                    </ul>
                </li>
                @endcan

                @can('view primary order report')
                <li class="@if(request()->is('primary/order/report*')||request()->is('secondary/order/report*')||request()->is('attendance/report*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Report</span></a>
                    <ul>
                        
                        
                        @can('view primary order report')
                        <li class="{{ ( request()->is('primary/order/report*') ) ? 'active' : '' }}"><a href="{{ route('primary.order.report') }}"><i class="fi fi-br-box"></i> <span>Primary Order Report</span></a></li>
                        @endcan
                        @can('view secondary order report')
                        <li class="{{ ( request()->is('secondary/order/report*') ) ? 'active' : '' }}"><a href="{{route('secondary.order.report')}}"><i class="fi fi-br-database"></i> <span>Secondary Order Report</span></a></li>
                        @endcan
                        @can('view attendance report')
                        <li class="{{ ( request()->is('attendance/report*') ) ? 'active' : '' }}"><a href="{{route('attendance.report')}}"><i class="fi fi-br-database"></i> <span>Attendance Report</span></a></li>
                        @endcan
                        
                    </ul>
                </li>
                @endcan
                 <li class="{{ request()->is('reward/product*') ? 'active' : '' }}">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Reward App</span></a>
                    <ul>
                        <li class="{{ request()->is('reward/product*') ? 'active' : '' }}">
                            <a href="{{ route('reward.retailer.product.index') }}"><i class="fi fi-br-box"></i> <span>Product</span></a>
                        </li>
                    </ul>
                </li>
                
            </ul>
        </nav>
         <div class="nav__footer">
            <a href="javascript:void(0)" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="fi fi-br-cube"></i> <span>Log Out</span></a>
        </div>
    </aside>
    <main class="admin">
       <header>
            <div class="row align-items-center">
                <div class="col-auto ms-auto">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </button>
                        <ul class="dropdown-menu test" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="{{route('profile.edit')}}">Profile</a></li>
                            <li> <a class="dropdown-item" href="javascript:void(0)" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
								<i class="fi fi-br-sign-out"></i> 
								<span>Logout</span>
								</a>
							</li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
        <section class="admin__title">
            <h1>@yield('page')</h1>
        </section>

        @yield('content')

        <footer>
            <div class="row">
                <div class="col-12 text-end">Pynk & Onn-{{date('Y')}}</div>
            </div>
        </footer>
    </main>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('backend/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/30.0.0/classic/ckeditor.js"></script>
    <script type="text/javascript" src="{{ asset('backend/js/custom.js') }}"></script>

   
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <script>
            $(document).ready(function() {
                $('.select2').select2();
            });
    </script>
    <script>
		// tooltip
		var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
		var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
		  return new bootstrap.Tooltip(tooltipTriggerEl)
		})

        // click to select all checkbox
        function headerCheckFunc() {
            if ($('#flexCheckDefault').is(':checked')) {
                $('.tap-to-delete').prop('checked', true);
                clickToRemove();
            } else {
                $('.tap-to-delete').prop('checked', false);
                clickToRemove();
            }
        }

        // sweetalert fires | type = success, error, warning, info, question
        function toastFire(type = 'success', title, body = '') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                showCloseButton: true,
                timer: 2000,
                timerProgressBar: false,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            Toast.fire({
                icon: type,
                title: title,
                // text: body
            })
        }

        // on session toast fires
        @if (Session::get('success'))
            toastFire('success', '{{ Session::get('success') }}');
        @elseif (Session::get('failure'))
            toastFire('warning', '{{ Session::get('failure') }}');
        @endif
    </script>
    
    @yield('script')
</body>
</html>
