<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>{{$header_title}}</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{asset('assets/img/favicon/favicon.ico')}}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{asset('assets/vendor/fonts/boxicons.css')}}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{asset('assets/vendor/css/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/theme-default.css')}}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{asset('assets/css/demo.css')}}" />

    <link rel="stylesheet" href="{{asset('css/styles.css')}}">

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css')}}" />

    <link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}" />
    
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    @yield('_styles')
    <style>
      .position-relative {
          position: relative;
      }
      .notification-count {
          position: absolute;
          top: -7px;
          right: -7px;
          background-color: red;
          color: white;
          border-radius: 50%;
          padding: 0.25em 0.5em;
          font-size: 0.75em;
          line-height: 1;
          font-weight: bold;
      }
    </style>
    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{asset('assets/vendor/js/helpers.js')}}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{asset('assets/js/config.js')}}"></script>
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->

        @include('layout.sidebar')
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          @include('layout.navbar')
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <div class="alert-mesg">

            </div>
            <!-- Content -->
             <div class="container">
              <div id="alert-message-notification">
                
              </div>
             </div>
              @yield('content')
            <!-- / Content -->

            <!-- Footer -->
             @include('layout.footer')
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{asset('assets/vendor/libs/jquery/jquery.js')}}"></script>
    
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{asset('assets/vendor/libs/popper/popper.js')}}"></script>
    <script src="{{asset('assets/vendor/js/bootstrap.js')}}"></script>
    <script src="{{asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}"></script>

    <script src="{{asset('assets/vendor/js/menu.js')}}"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>

    <!-- Main JS -->
    <script src="{{asset('assets/js/main.js')}}"></script>

    <!-- Page JS -->
    <script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script>
      // Enable pusher logging - don't include this in production
      Pusher.logToConsole = true;

      var pusher = new Pusher('749fe8beaacad593048d', {
        cluster: 'ap1'
      });

      var channel = pusher.subscribe('my-channel');
      channel.bind('my-event', function(data) {
        // $('#alert-message-notification').html(`<div class="alert alert-success alert-message-badge" role="alert">${data}</div>`);
      });

      (function() {
        function checkCount() {
          $.ajax({
            url: "{{url('get-notifications')}}",
            method: 'POST',
            data: {
              _token: "{{csrf_token()}}",
            },
            success: function (res) {
            //   if (res.status == true) {
            //     if (res.import_notification == 1) {
            //       $('.alert-mesg').html(`
            //  <div class="alert alert-success alert-message-badge m-4" role="alert">
            //       File imported successfully
            //   </div>`);
            //     }
            //     if (res.user_sync_notification == 1) {
            //       $('.alert-mesg').html(`
            //  <div class="alert alert-success alert-message-badge m-4" role="alert">
            //       Users synchronization has been successfully completed
            //   </div>`);
            //     }
            //   }
            }
          });
        }
        // checkCount();
        $('.date_range_picker').daterangepicker({
          autoUpdateInput: false,      
          locale: {
              cancelLabel: 'Clear'
          }
        }).on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate ? picker.startDate.format('YYYY/MM/DD') : '';
            var endDate = picker.endDate ? picker.endDate.format('YYYY/MM/DD') : '';
            $(this).val(startDate + ' - ' + endDate).trigger('change');
        }).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('').trigger('change');
        });
        $('.date_range_picker').val('');
        let idleTime = 0;

        function timerIncrement() {
            idleTime++;
            if (idleTime > 1) { // 10 minutes
                // window.location.href = '{{route("logout")}}'; // Change to your logout route
            }
        }

        // Increment the idle time counter every minute
        setInterval(timerIncrement, 60000); // 1 minute

        // Zero the idle timer on mouse movement, key press, etc.
        window.onmousemove = window.onkeypress = window.onload = () => {
            idleTime = 0;
        };
        
        setTimeout(() => {
          $('.alert-message-badge').hide();
        }, 5000);
        // setInterval(function() {
        //   // checkCount();
        // }, 2000);
      8})();
    </script>
    @yield('_scripts')
  </body>
</html>