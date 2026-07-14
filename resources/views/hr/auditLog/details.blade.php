

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}">
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css"> -->
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('assets/css/AdminLTE.min.css') }}">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <!-- <link rel="stylesheet" href="{{ asset('assets/css/skin-green-light.min.css') }}"> -->
  <link rel="stylesheet" href="{{ asset('assets/css/_all-skins.min.css') }}">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition skin-green sidebar-mini">






  <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                    
                       



    <table width="1122" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
          <td colspan="3"><div align="center">
      <h2>ISALU HOSPITAL LIMITED PAYROLL<br />
   
</h2>


<h3>
MINISTRY/DEPARTMENT: ISALU HOSPITAL LIMITED
</h3>

        
      </div></td>
    </tr>    
    </table><br>
  
<table class="table table-hover table-bordered  width="1122" border="0" align="center" cellpadding="0" cellspacing="0"">
              <tr>
                  <th>USERID</th>
                  <th>FULLNAME</th>
                  <th>USERNAME</th>
                  <th>OPERATION</th>

              
                  <th>DIVISION</th>

                  <th>DATE/TIME</th>
                  <th>REFERER</th>
            
            </tr>
      
                 @foreach ($audit_detail as $reports)
                 
                 <tr>
                 <td>{{ $reports->id }}</td>
                 <td>{{  $reports->name }}</td>
                 <td>{{  $reports->username}}</td>
                 <td>{{ $reports->operation }}</td>
              
                
              
                 <td>{{ $reports->division }}</td>
                 <td>{{ $reports->date }}</td>
            
                 <td>{{ $reports->referer }}</td>
               
               
               </tr>
                 
                 @endforeach

             </table>
           </div>
                    
                </div>
            </div>
        </div>
<!-- jQuery 2.2.0 -->
<script src="{{ asset('assets/js/jQuery-2.2.0.min.js') }}"></script>
<!-- Bootstrap 3.3.6 -->
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<!-- SlimScroll -->
<!-- <script src="../../plugins/slimScroll/jquery.slimscroll.min.js"></script> -->
<!-- FastClick -->
<!-- <script src="../../plugins/fastclick/fastclick.js"></script> -->
<!-- AdminLTE App -->
<script src="{{ asset('assets/js/app.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
</body>
</html>