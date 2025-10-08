@extends('layouts/commonMaster' )



@section('layoutContent')
<div class="layout-wrapper layout-content-navbar">
  <div class="layout-container">




    <!-- Layout page -->
    <div class="layout-page">
      <!-- BEGIN: Navbar-->
  
      <!-- END: Navbar-->


      <!-- Content wrapper -->
      <div class="content-wrapper">

        <!-- Content -->
        
        <div class=" d-flex align-items-stretch flex-grow-1 p-0">
          
          <div class=" flex-grow-1 container-p-y">
            

            @yield('content')

          </div>
          <!-- / Content -->

          <!-- Footer -->
          
          @include('layouts/sections/footer/footer')
          
          <!-- / Footer -->
          <div class="content-backdrop fade"></div>
        </div>
        <!--/ Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
 
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
  </div>
  <!-- / Layout wrapper -->
  @endsection
