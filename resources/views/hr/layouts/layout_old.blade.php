<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>ISALU HOSPITAL LIMITED</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/AdminLTE.min.css') }}">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/_all-skins.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
  <style type="text/css">
    table th {
      width: auto !important;
    }
  </style>

  @yield('styles')

  <script> var murl = "{{ url('/')}}"; </script>
</head>
<body class="hold-transition skin-green sidebar-mini">

<div class="wrapper">

  <header class="main-header hidden-print">
    <!-- Logo -->
    <a href="{{url('/')}}" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini">JIPPIS</span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b>JIPPIS HR</b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <!-- Messages: style can be found in dropdown.less-->
        
          <!-- Notifications: Staff that are due for increment per division -->
        @if(count($increment) > 0)
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-bell"></i>
              <span class="label label-warning">              
              <div style="font-size: 12px;">{{ count($increment)}}</div>              
              </span>
            </a>
            <ul class="dropdown-menu">
              <li class="header alert alert-warning">{{ count($increment) }} Staff(s) Due For Increment </li>
              <li>                
                <ul class="menu">
                  @foreach($increment as $list)
                  <li>
                    <a href="{{ url('/record-variation/view/increment') }}">
                      <i class="fa fa-user text-aqua"></i>
                       @include('Layouts._alertIncrement')
                      <!--$this-><td class="hidden-print"></td> was include _alertIncrement--> 
                    </a>
                  </li>
                  @endforeach
                </ul>
              </li>
              <li class="footer"><a href="{{ url('/record-variation/view/increment') }}" class="text-success">View all</a></li>
            </ul>
          </li>
          @endif

          <!-- Notifications: New Staff Added to the system from Open Registry -->
          @if(count($NewStaff) > 0)
          <li class="dropdown notifications-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-user"></i>
              <span class="label label-warning">              
              <div style="font-size: 12px;">{{ count($NewStaff)}}</div>              
              </span>
            </a>
            <ul class="dropdown-menu">
              <li class="header alert alert-warning">You have {{ count($NewStaff) }} New Staff(s)</li>
              <li>                
                <ul class="menu">
                  @foreach($NewStaff as $transfer)
                  <li>
                      <a href="{{ url('/personal-emolument/new-staff') }}">
                        <i class="fa fa-user text-aqua"></i>
                        {{$transfer->surname. ', '.$transfer->first_name .' ('. $transfer->division .') - ' . $transfer->fileNo }}
                      </a>
                  </li>
                  @endforeach
                </ul>
              </li>
              <li class="footer"><a href="{{ url('/personal-emolument/new-staff') }}" class="text-success">View all</a></li>
            </ul>
          </li>
          @endif
          <!-- Tasks: style can be found in dropdown.less -->
          <!-- The other dropdown notification goes here -->


          <!-- End of other dropdown notification -->
          <!-- User Account: style can be found in dropdown.less -->
          <li class="user">
           @if(Entrust::hasRole('admin') or Entrust::hasRole('tax staff') or Entrust::hasRole('audit staff') or Entrust::hasRole('cpo staff') or Entrust::hasRole('super admin') or Entrust::hasRole('salary collator') or Entrust::hasRole('nhf staff') )
            <a href="{{ url('division/changeDivision')}}">
            @else  <a href="">
            @endif
              <i class="fa fa-location-arrow"></i>
              <span>
              @if(!Auth::guest())
              {{ session('division') }} <span class="hidden-xs">Division</span>
              @endif
              </span>
            </a>
           
          </li>          

          <li class="dropdown user">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
              <i class="fa fa-user"></i>
              <span class="hidden-xs">
              @if(!Auth::guest())
              {{ Auth::user()->name }}
              @endif
              </span>
            </a>
           <ul class="dropdown-menu dropdown-user">
                 <li><a href="{{url('/user/editAccount')}}"><i class="fa fa-circle-o"></i> Edit Account</a></li>
                 <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li> 
                 @role('super admin')
                  <li role="separator" class="divider"></li>
                 <li><a href="{{url('/user/register')}}"><i class="fa fa-circle-o"></i> Create New User</a></li>
                 <li><a href="{{url('/role/create')}}"><i class="fa fa-circle-o"></i> Create Role</a></li>
                 <li><a href="{{url('/permission/create')}}"><i class="fa fa-circle-o"></i> Create Permission</a></li>
                 <li><a href="{{url('permission/permRole')}}"><i class="fa fa-circle-o"></i> Assign Permission</a></li>
                 <li><a href="{{url('/role/userRole')}}"><i class="fa fa-circle-o"></i> Assign User</a></li>
                 <li><a href="{{url('/role/viewUser')}}"><i class="fa fa-circle-o"></i> View Users</a></li>
                 @endrole  
            </ul>
          </li>          
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar hidden-print">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="{{asset('Images/coat.jpg')}}" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>{{ ucwords(session('roleName')) }}</p>
          <a href="#"><i class="fa fa-circle text-success"></i>online</a>          
        </div>

      </div>
     
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">
        <li class="header">MAIN NAVIGATION</li>
        @if(Entrust::hasRole('Open Registry') or Entrust::hasRole('super admin'))
            <!--open regidtry Department-->
            <li class="treeview" id="admin" >
              <a data-target="#">           
                  <i class="fa fa-users text-aqua"></i> <span>OPEN REGISTRY</span>
                  <i class="fa fa-angle-left pull-right"></i>            
              </a>
              <ul class="treeview-menu">
               
                <li><a href="{{url('/new-staff/create')}}"><i class="fa fa-circle-o"></i> New Staff</a></li>
                <li><a href="{{url('/staff-report/view')}}"><i class="fa fa-circle-o"></i> Staff Report</a></li>
                <li><a href="{{url('/openregistry/create/')}}"><i class="fa fa-circle-o"></i> Create File Movement</a></li>
                <li><a href="{{url('/openregistry/list')}}"><i class="fa fa-circle-o"></i> View FIle Movement</a></li>
                <li><a href="{{url('/open-file-registry/incoming-letter')}}"><i class="fa fa-circle-o"></i>Incoming Letter</a></li>
                <li><a href="{{url('/open-file-registry/outgoing-letter')}}"><i class="fa fa-circle-o"></i>Outgoing Letter</a></li>
                <li><a href="{{url('/open-file-registry/create')}}"><i class="fa fa-circle-o"></i>Close Files</a></li>
                <li><a href="{{url('/open-file-registry/mail')}}"><i class="fa fa-circle-o"></i>Mail</a></li>

                <li class="treeview" id="admin" > <a a data-target="#"> <i class="fa fa-edit"></i> <span>Report</span><i class="fa fa-angle-left pull-right"></i> </a>
              <ul class="treeview-menu">
                <li><a href="{{url('/open-file-registry/view-incoming')}}"><i class="fa fa-circle-o"></i>View Incoming Letter</a></li>
                <li><a href="{{url('/open-file-registry/view-outgoing')}}"><i class="fa fa-circle-o"></i>View Outgoing Letter</a></li>
                <li><a href="{{url('/open-file-registry/view-closed-files')}}"><i class="fa fa-circle-o"></i>View Close Files</a></li>
                <li><a href="{{url('/open-file-registry/view-mails')}}"><i class="fa fa-circle-o"></i>Mail</a></li>
              </ul>
            </li>
            @endif
              </ul>
        </li> 
        <!--//open regidtry Department-->

              @if(Entrust::hasRole('estab') or Entrust::hasRole('super admin'))
        <!--ManPower Department-->
            <li class="treeview" id="admin" >
              <a data-target="#">           
                  <i class="fa fa-users text-aqua"></i> <span> ESTABLISHMENT - ADMIN</span>
                  <i class="fa fa-angle-left pull-right"></i>             
              </a>
              <ul class="treeview-menu">
            
                <li><a href="{{url('/map-power/view/central')}}"><i class="fa fa-circle-o"></i> Central Nominal Roll</a></li>
                <li><a href="{{url('/map-power/view/cadre')}}"><i class="fa fa-circle-o"></i> Nominal Roll By Cadre</a></li>
                 <!--<li><a href="#"><i class="fa fa-circle-o"></i> Nominal Roll By Division</a></li>-->
                <!--<li><a href="{{url('/manpower/budget')}}"><i class="fa fa-circle-o"></i> Budget</a></li>-->
                <li><a href="{{url('/estab/central-list')}}"><i class="fa fa-circle-o"></i> Staff Promotion </a></li>
                <li><a href="{{url('/estab/conversion')}}"><i class="fa fa-circle-o"></i> Conversion/Advancement </a></li>
                <li><a href="{{url('/estab/promotion-list')}}"><i class="fa fa-circle-o"></i> Due For Promotion </a></li>
              </ul>
           </li> 
           @endif
        <!--//ManPower Department-->

          <!--RECORDS AND VARIATION DEPARTMENT-->
          @if(Entrust::hasRole('Records and Variation') or Entrust::hasRole('super admin'))
            <li class="treeview" id="admin" >
              <a data-target="#">           
                  <i class="fa fa-users text-aqua"></i> <span>RECORDS AND VARIATION</span>
                  <i class="fa fa-angle-left pull-right"></i>             
              </a>
              <ul class="treeview-menu">
                
                <li>
                  <a href="{{url('/record-variation/view/cadre')}}"><i class="fa fa-users text-aqua"></i> <span>Master Staff Records</span></a>
                </li>

                <li>
                  <a href="{{url('/profile/details')}}"><i class="fa fa-user text-aqua"></i> 
                      <span>Staff Profile details</span>
                  </a>
                </li>
                <li>
                    <a href="{{url('/searchUser/create')}}"><i class="fa fa-search text-aqua"></i> <span>Search For Staff Record</span>
                    </a>
                </li>

                <hr />

                 <!--Variation-->
                <li>
                  <a href="{{url('/computer/variation/create')}}">
                      <i class="fa fa-user text-aqua"></i> <span>Compute Variation</span>
                  </a>
                </li>
                 <li>
                  <a href="{{url('/compute/promotion/variation')}}">
                      <i class="fa fa-user text-aqua"></i> <span>Compute advancement Variation</span>
                  </a>
                </li>

                
                <li>
                  <a href="{{url('/staff/variation/view/')}}"><i class="fa fa-user text-aqua"></i> 
                    <span>Variation Report</span>
                  </a>
                </li><!--//--><hr />

                <li><!--Emolument-->
                  <a href="{{url('/personal-emolument/create')}}"><i class="fa fa-user text-aqua"></i> 
                    <span>Emolument Record</span>
                  </a>
                </li>
                <li>
                  <a href="{{url('/staff/personal-emolument/view/')}}"><i class="fa fa-user text-aqua"></i> 
                    <span>Emolument Report</span>
                  </a>
                </li><!--//--><hr />
                
                <li>
                  <a href="{{url('/offerofappointment/createoffer')}}"><i class="fa fa-circle-o"></i>Offer Of Appointment</a>
                </li>
                <li>
                  <a href="{{url('/offerofappointment/createletter')}}"><i class="fa fa-circle-o"></i>Letter Of Appoinment</a>
                </li>
                <li>
                  <a href="{{url('/offerofappointment/medicalexam')}}"><i class="fa fa-circle-o"></i>Medical Examination</a>
                </li>
                <li>
                  <a href="{{url('/offerofappointment/acceptance')}}"><i class="fa fa-circle-o"></i>Letter Of Acceptance</a>
                </li>

                <li>
                  <a href="{{url('/forms/letter-of-application')}}"><i class="fa fa-circle-o"></i>Letter Of Application</a>
                </li>
                <li>
                  <a href="{{url('/forms/appointment-form')}}"><i class="fa fa-circle-o"></i>Appointment Form</a>
                </li>
                <li>
                  <a href="{{url('/forms/referee-form')}}"><i class="fa fa-circle-o"></i>Referee Form Statement</a>
                </li>
                <li>
                  <a href="{{url('/forms/leave-form')}}"><i class="fa fa-circle-o"></i>Leave Form</a>
                </li>

                <li class="treeview" id="admin">
                    <a data-target="#">
                      <i class="fa fa-edit"></i>  
                      <span>Report Listing</span> 
                      <i class="fa fa-angle-left pull-right"></i>
                    </a>
                  <ul class="treeview-menu">
                     <li>
                      <a href="{{url('/offerofappointment/listoffer')}}"><i class="fa fa-circle-o"></i>Offer Of Appointment Report</a>
                     </li>
                    <li>
                      <a href="{{url('/offerofappointment/listletter')}}"><i class="fa fa-circle-o"></i>Letter Of Appoinment Report</a>
                    </li>
                    <li>
                      <a href="{{url('/offerofappointment/listmedicalexam')}}"><i class="fa fa-circle-o"></i>Medical Examination Report</a>
                    </li>
                    <li>
                      <a href="{{url('/offerofappointment/listacceptance')}}"><i class="fa fa-circle-o"></i>Letter Of Acceptance Report</a>
                    </li>
                  </ul>
                </li>

                <!--<li><a href="{{url('/staff/create')}}"><i class="fa fa-user text-aqua"></i> <span>Update Staff Record</span></a></li>-->
              </ul>
           </li> 
          <!--//Records and variation Department-->
          @endif
          @if(Entrust::hasRole('Man Power') or Entrust::hasRole('super admin'))
          <!--ManPower Department-->
            <li class="treeview" id="admin" >
              <a data-target="#">           
                  <i class="fa fa-users text-aqua"></i> <span>MAN POWER</span>
                  <i class="fa fa-angle-left pull-right"></i>             
              </a>
              <ul class="treeview-menu">
               
                <li><a href="{{url('/map-power/view/central')}}"><i class="fa fa-circle-o"></i> Central Nominal Roll</a></li>
                <li><a href="{{url('/map-power/view/cadre')}}"><i class="fa fa-circle-o"></i> Nominal Roll By Cadre</a></li>
                 <!--<li><a href="#"><i class="fa fa-circle-o"></i> Nominal Roll By Division</a></li>-->
                <li><a href="{{url('/manpower/budget')}}"><i class="fa fa-circle-o"></i> Budget</a></li>
              </ul>
           </li>
           @endif 
          <!--//ManPower Department-->

          @if(Entrust::hasRole('Pension') or Entrust::hasRole('super admin'))
          <!--PENSION Department-->
            <li class="treeview" id="admin" >
              <a data-target="#">           
                  <i class="fa fa-users text-aqua"></i> <span>PENSION</span>
                  <i class="fa fa-angle-left pull-right"></i>             
              </a>
              <ul class="treeview-menu">
                <li>
                 
                  <a href="{{url('/record-variation/view/cadre')}}"><i class="fa fa-users text-aqua"></i> <span>Master Staff Records</span></a>
                </li>
                <li><a href="{{url('/pension-manager/create')}}"><i class="fa fa-circle-o"></i> Add Pension Manager</a></li>
                <li><a href="{{url('/pensionmanager/view')}}"><i class="fa fa-circle-o"></i> View Pension Manager</a></li>
                <li><a href="{{url('/pension/create')}}"><i class="fa fa-circle-o"></i> Compute Staff Pension</a></li>
                <li><a href="{{url('/pension/all-report')}}"><i class="fa fa-circle-o"></i> All Staff Pension</a></li>
                <li><a href="{{url('/pension/report')}}"><i class="fa fa-circle-o"></i> Print Report</a></li>
              </ul>
           </li> 
           @endif
          <!--//PENSION Department-->

          <!--NHF Department-->
           <!-- <li class="treeview" id="admin" >
              <a data-target="#">           
                  <i class="fa fa-users text-aqua"></i> <span>NHF</span>
                  <i class="fa fa-angle-left pull-right"></i>             
              </a>
              <ul class="treeview-menu">
                <!--<li><a href="{{url('/map-power/view')}}"><i class="fa fa-circle-o"></i> Central Nominal Roll</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Nominal Roll By Cadre</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Nominal Roll By Division</a></li>
                <li><a href="#"><i class="fa fa-circle-o"></i> Staff List</a></li>
              </ul>
           </li> -->
          <!--//NHF Department-->

        @if(Entrust::hasRole('admin') or Entrust::hasRole('super admin'))            
         <li class="treeview" id="admin" >
          <a data-target="#">           
              <i class="fa fa-edit"></i> <span>Admin</span><i class="fa fa-angle-left pull-right"></i>         
          </a>
          <ul class="treeview-menu">
            <li><a href="{{url('/banklist/create')}}"><i class="fa fa-circle-o"></i> Add new Bank</a></li>
            <li><a href="{{url('/classcode/create')}}"><i class="fa fa-circle-o"></i> Classification Code</a></li>
            <li><a href="{{url('/division/create')}}"><i class="fa fa-circle-o"></i> Division</a></li>
            <li><a href="{{url('/auditLog/create')}}"><i class="fa fa-circle-o"></i> Audit Log</a></li>
          </ul>
         </li>  
        @endif 

      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper hidden-print">
    <section class="content">

        @yield('content')
    
    </section>
  </div>

     <section class="content hidden-lg hidden-md">
        @yield('content')
    </section>
    <!-- /.content -->
  
  <!-- /.content-wrapper -->
  <footer class="main-footer hidden-print">
    <div class="pull-right hidden-xs">
      <b>Designed by</b> <a href="http://mbrcomputers.net">MBR Computers</a>
    </div>
    <strong>Copyright &copy; <?php echo date('Y') ?> .</strong> All rights
    reserved.
  </footer>
  </div>
<!-- ./wrapper -->
<script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>
@yield('scripts')
<!-- <script src="{{ asset('assets/js/jquery.cookie.js') }}"></script> -->
</body>
</html>