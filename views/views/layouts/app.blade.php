<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('backend/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://cdn-uicons.flaticon.com/uicons-bold-rounded/css/uicons-bold-rounded.css" rel="stylesheet">
    <link href="{{ asset('backend/css/style.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('backend/images/logo.png') }}" type="image/x-icon">
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/fontawesome.min.css" integrity="sha512-cHxvm20nkjOUySu7jdwiUxgGy11vuVPE9YeK89geLMLMMEOcKFyS2i+8wo0FOwyQO/bL8Bvq1KMsqK4bbOsPnA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}
    <title>Fox & Mandal </title>
	
	<style>
		.page-item.active .page-link {
			background-color: rgb(219, 110, 76);
			border-color: rgb(219, 110, 76);
		}
		.page-link, .page-link:hover, .page-link:focus {
			color: rgb(219, 110, 76);
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
                <li class="{{ ( request()->is('users*') ) ? 'active' : '' }}"><a href="{{ route('users.index') }}"><i class="fi fi-br-database"></i> <span>User</span></a></li>
                @endcan
                @can('view role')
                <li class="{{ ( request()->is('roles*') ) ? 'active' : '' }}"><a href="{{ route('roles.index') }}"><i class="fi fi-br-database"></i> <span>Role</span></a></li>
                @endcan
                @can('view permission')
                <li class="{{ ( request()->is('permissions*') ) ? 'active' : '' }}"><a href="{{ route('permissions.index') }}"><i class="fi fi-br-database"></i> <span>Permission</span></a></li>
                @endcan
                {{--@can('view office')
                <li class="@if(request()->is('offices*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Facility Management</span></a>
                    <ul>
                        @can('view office')
                        <li class="{{ ( request()->is('offices*') ) ? 'active' : '' }}"><a href="{{ route('offices.index') }}"><i class="fi fi-br-database"></i> <span>Office Management</span></a></li>
                        @endcan
                    </ul>
                </li>
                @endcan--}}
                
                @can('book category list')
                <li class="@if(request()->is('bookcategories*')||request()->is('bookshelves*')||request()->is('books*')||request()->is('offices*')||request()->is('unreturned/books*')||request()->is('members*')||request()->is('issues*')) { {{'active'}} }  @endif">
                    <a href="#"><i class="fi fi-br-cube"></i> <span>Lms Management</span></a>
                    <ul>
                        @can('view member')
                        <li class="{{ ( request()->is('members*') ) ? 'active' : '' }}"><a href="{{ route('members.index') }}"><i class="fi fi-br-database"></i> <span>Member Management</span></a></li>
                        @endcan
                        @can('view office')
                        <li class="{{ ( request()->is('offices*') ) ? 'active' : '' }}"><a href="{{ route('offices.index') }}"><i class="fi fi-br-database"></i> <span>Office Management</span></a></li>
                        @endcan
                        @can('book category list')
                        <li class="{{ ( request()->is('bookcategories*') ) ? 'active' : '' }}"><a href="{{route('bookcategories.index')}}"><i class="fi fi-br-database"></i> <span>Book Category Management</span></a></li>
                        @endcan
                        @can('bookshelve list')
                        <li class="{{ ( request()->is('bookshelves*') ) ? 'active' : '' }}"><a href="{{route('bookshelves.index')}}"><i class="fi fi-br-database"></i> <span>Bookshelves Management</span></a></li>
                        @endcan
                        @can('book list')
                        <li class="{{ ( request()->is('books*') ) ? 'active' : '' }}"><a href="{{route('books.index')}}"><i class="fi fi-br-database"></i> <span>Book Management</span></a></li>
                        @endcan
                        @can('Un-returned book list')
                        <li class="{{ ( request()->is('unreturned/books*') ) ? 'active' : '' }}"><a href="{{ url('unreturned/books/list') }}"><i class="fi fi-br-database"></i> <span>Un-returned book list</span></a></li>
                        @endcan
                        @can('view all book issue')
                        <li class="{{ ( request()->is('issues*') ) ? 'active' : '' }}"><a href="{{ route('issues.index') }}"><i class="fi fi-br-database"></i> <span>All book issue request list</span></a></li>
                        @endcan
                    </ul>
                </li>
                @endcan
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
                <div class="col-12 text-end">Fox & Mandal-{{date('Y')}}</div>
            </div>
        </footer>
    </main>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('backend/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/30.0.0/classic/ckeditor.js"></script>
    <script type="text/javascript" src="{{ asset('backend/js/custom.js') }}"></script>

   
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
