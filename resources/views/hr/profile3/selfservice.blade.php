@extends('layouts.layout')
@section('pageTitle')
Self Service
@endsection
@section('content')
  <style>
        table, th, td {
            border: 1px solid black;  
            /* remove the .table-borded class for this to work */
        }
        td {
            vertical-align: top;
        }
  </style>
@include('profile.editModal2')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-header with-border hidden-print">
        <h3 class="box-title"><b>@yield('pageTitle')</b> <i class="fa fa-arrow-right"></i> <span id='processing'>
                <strong><em>My Profile</em></strong> </span></h3>
    </div>
      <!--
      <form method="post" action="{{ url('/profile/details') }}">
        <div class="box-body row">
             <div class="form-group col-md-10">
                {{ csrf_field() }}
               <input id="autocomplete" name="q" class="form-control input-lg" placeholder="Search By First Name, Surname or File Number">
               <input type="hidden" id="fileNo"  name="fileNo">
            </div>
            <div class="col-md-2">
              <button type="submit" name="searchName" id="searchName" class="btn btn-default btn-lg"><i class="fa fa-search"></i> Search</button>
             
            </div>
        </div>

      </form>
      -->
      <!--
      <div style="margin-left:10px;margin-bottom:10px;">
          <a href="{{ url('/profile/details') }}"><button type="submit" name="searchName" id="searchName" class="btn btn-default btn-lg"><i class="fa fa-search"></i> Search another</button></a>
      </div>
      -->
      <br>
    </div>
  </div><!-- /.col -->
</div><!-- /.row -->

<div class="box-body">
    <div class="row">
        <div class="col-md-12">
            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                  <strong>Error!</strong> 
                  @foreach ($errors->all() as $error)
                      <p>{{ $error }}</p>
                  @endforeach
                  </div>
                  @endif                       
                        
                  @if(session('msg'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Success!</strong> {{ session('msg') }}</div>                        
                  @endif
                  @if(session('err'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Staff Not Available for this Division! <br></strong> {{ session('err') }}</div>                        
                  @endif
        </div>
    </div>
</div>

<div class="row">  
        <div class="col-md-3">

          <!--BIO-DATA-->
          <!-- Profile Image -->
          @if(isset($staffFullDetails) && $staffFullDetails)

          <div class="box box-success">
          <h3 class="profile-username text-center">{{strtoupper('Bio-Data')}}</h3>
            <div class="box-body box-profile"> 
             @php $pic="/passport/" @endphp
              <img class="profile-user-img img-responsive" 
              src="{{ $pic }}{{ $staffFullDetails->picture }}" alt="Staff profile picture">
              @if($getIDs->biodata==1)
                <div class="text-center"><a onclick="profilePicEdit('{{ $staffFullDetails->staffID }}')" title="Edit Picture">Edit Picture</a></div>
              @elseif($getIDs->biodata==0)
                    
              @endif
              <h4 class="profile-username text-center" id="fullName"></h4>

              <p class="text-muted text-center" id="decoration"></p>

              <ul class="list-group list-group-unbordered">
                <li class="list-group-item">
                  <div><b>File No:</b> <span class="pull-right">{{$staffFullDetails->fileNo}}</span></div>
                </li>
                <!--
                <li class="list-group-item">
                  <div><b>Division:</b> <span class="pull-right">{{$staffFullDetails->division}}</span></div>
                </li>
                -->
                <li class="list-group-item">
                  <div><b>Title:</b> <span class="pull-right">{{ $staffFullDetails->title }}</span></div>
                </li>
                <li class="list-group-item">
                  <div><b>Surname:</b> <span class="pull-right">{{$staffFullDetails->surname}}</span></div>
                </li>
                <li class="list-group-item">
                  <div><b>First Name:</b> <span class="pull-right">{{$staffFullDetails->first_name}}</span></div>
                </li>
                <li class="list-group-item">
                  <div><b>Other Names:</b> <span class="pull-right">{{$staffFullDetails->othernames}}</span></div>
                </li>
                <li class="list-group-item">
                  <div><b>Gender:</b> <span class="pull-right">{{$staffFullDetails->gender}}</span></div>
                </li>
                <li class="list-group-item">
                  <div><b>Home Address:</b> <div class="text-right">{{$staffFullDetails->home_address}}</div></div>
                </li>
                <li class="list-group-item">
                  <div><b>Phone:</b> <div class="pull-right">{{$staffFullDetails->phone}}</div></div>
                </li>
                 <li class="list-group-item">
                  <div><b>Current State:</b> <div class="pull-right">{{$staffFullDetails->State}}</div></div>
                </li>
                <li class="list-group-item">
                  <div><b>Nationality:</b> <div class="pull-right">{{$staffFullDetails->nationality}}</div></div>
                </li>
                <li class="list-group-item">
                  <div><b>Staff Status:</b> <div class="pull-right">{{$staffFullDetails->status}}</div></div>
                </li>
              </ul>
                <div>
                    @if($getIDs->biodata==1)
                        <a onclick="profileEdit('{{ $staffFullDetails->staffID }}','{{ $staffFullDetails->fileNo }}','{{ $staffFullDetails->divID }}','{{ $staffFullDetails->titleID }}','{{ $staffFullDetails->surname }}','{{ $staffFullDetails->first_name }}','{{ $staffFullDetails->othernames }}','{!! $staffFullDetails->home_address !!}','{{ $staffFullDetails->genderID }}','{{ $staffFullDetails->stateID }}','{{ $staffFullDetails->phone }}','{{ $staffFullDetails->nationality }}','{{ $staffFullDetails->status }}')" style="cursor:pointer;" class="pull-left" id="fileNoBioData"><i class="fa fa-edit"></i> Edit</a>
                    @elseif($getIDs->biodata==0)
                    
                    @endif
                     
                    <!--<a href="#" class="pull-right" id="fileNoBioData"><i class="fa fa-print"></i> Print</a>-->
                </div>
            </div>
          </div> 
          @endif

          <!--Details of service in the forces-->
          {{--
            <div class="box box-success">
              <div class="box-body box-profile">
                <h3 class="profile-username text-center">{{strtoupper('Details of service in the forces')}}</h3>
                    <table class="table table-condensed">
                          @php if($staffFullDetailsDetailsService != null){ @endphp
                          @foreach($staffFullDetailsDetailsService as $ds)
                           <tbody class="text-gray">
                              <tr>
                                <td>
                                  <div><b>Arm of Service:</b><span class="pull-right">{{$ds->armOfservice}}</span></div>
                                </td>
                              </tr>
                              <tr>
                                 <td>
                                    <div><b>Service No.:</b><span class="pull-right">{{$ds->serviceNumber}}</span></div>
                                  </td>
                              </tr>
                              <tr>
                                  <td>
                                    <div><b>Last Unit:</b><span class="pull-right">{{$ds->lastUnit}}</span></div>
                                  </td>
                              </tr>
                              <tr>
                                 <td>
                                    <div><b>Reason for Leaving:</b><span class="pull-right">{{$ds->reasonForLeaving}}</span></div>
                                 </td>
                              </tr>
                            </tbody>
                            @endforeach
                            @php } @endphp
                    </table>
                  <div class="text-gray-c">
                      <a href="{{url('/update/detail-service/'.$staffFullDetails->fileNo)}}"><i class="fa fa-edit"></i> Edit</a> 
                      <a href="{{url('/profile/DetailsServiceForce/report/'.$staffFullDetails->fileNo)}}" class="pull-right">
                          <i class="fa fa-print"></i> Print
                      </a> 
                      <span class="pull-right"><i lass="fa fa-count">
                        </i> Total: {{$totalDetailsService}} &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; 
                      </span> 
                  </div>
            </div>
          </div>
          --}}

          <!--Details of service in the forces-->
        </div>
          <!-- //BIO-DATA-->
       
        <!-- /.col -->

        <!--EDUCATION-->
        <div class="col-md-6">
          <div class="box box-success">
            <div class="box-body box-profile">
              
              <h3 class="profile-username text-center">{{strtoupper('Education')}} </h3>
              
                  
             
                <table class="table table-reponsive">
                 
		        <thead class="text-gray-b">
		          <tr>
		          
		            <th></th>
		            <th>Degree and Professional Qualifications:</th>
		            <th>Schools Attended</th>
		            <th>From</th>
		            <th>To</th>
		            <th>Certificates Obtained:</th>
		           
		            <th>Action</th>
		           
		            		            
		          </tr>
		        </thead>
		           
		          
		          
		         <tbody>
		        
		          @php
		          $i=1;
		          @endphp
		          @php if($staffFullDetailsEducation != null){ @endphp
		         
		            @foreach($staffFullDetailsEducation as $edu)
		            
		             
    		           <tr>
    		               <td></td>
    		               <td>{{$edu->degreequalification}}</td>
    		               <td>{{$edu->schoolattended}}</td>
    		               <td>{{date('d-m-Y', strtotime($edu->schoolfrom))}}</td>
    		               <td>{{date('d-m-Y', strtotime($edu->schoolto))}}</td>
                            <td>{{$edu->certificateheld}}</td>
    		                <td>
    		                 @if($getIDs->education==1)
    		               	    <a onclick="educationEdit('{{ $edu->id }}','{{ $edu->staffid }}','{{$edu->degreequalification}}','{{ $edu->schoolattended }}','{{ date('d-m-Y', strtotime($edu->schoolfrom)) }}','{{ date('d-m-Y', strtotime($edu->schoolto)) }}','{{$edu->certificateheld}}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
    		                 @elseif($getIDs->education==0)
    		                 
    		                 @endif
    		               </td>
    		           </tr>
    		            
		          
		            @endforeach
		           
		           @php } @endphp
		        
		            
		            </tbody>
		           
		      </table>
		     
              <div class="text-gray-c">
                
                       
                    
              </div>
            </div>
          </div>
          <!--LANGUAGES-->
          <div class="box box-success">
            <div class="box-body box-profile">
               <div class="table-responsive">
              <h3 class="profile-username text-center">{{strtoupper('Languages')}}</h3>
              
               <table class="table table-reponsive">
                  <thead class="text-gray-b">
                        <tr>
                            <th></th>
                            <td><b>Language</b></td>
                            <td><b>Spoken</b></td>
                            <td><b>Written</b></td>
                            <td><b>Exam, Qualified</b></td>
                            <td><b>Checked By</b></td>
                             <th>Action</th>
                        </tr>
                  </thead>
                  <tbody>
                    @php
		          $i=1;
		          @endphp
		           @php if($staffFullDetailsLanguage != null){ @endphp
                    @foreach($staffFullDetailsLanguage as $lan)
                        <tr>
                            
                          <td>{{ $i++ }}</td>
                          <td>{{$lan->language}}</td>
                          <td>{{$lan->spoken}}</td>
                          <td>{{$lan->written}}</td>
                          <td>{{$lan->exam_qualified}}</td>
                          <td>{{$lan->checkedby}}</td>
                          <td>
                          @if($getIDs->language==1)
		               	    <a onclick="languageEdit('{{ $lan->langid }}','{{ $lan->staffid }}','{{ $lan->language }}','{{ $lan->spoken }}','{{ $lan->written }}','{{ $lan->exam_qualified }}','{{$lan->checkedby}}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                  @elseif($getIDs->language==0)
		                  
		                  @endif
		               </td>
                        </tr>
                      @endforeach
                     @php } @endphp 
                  </tbody>
              </table>
            </div>
              
            </div>
          </div><!--//LANGUAGE-->

          <!--PARTICULARS OF CHILDREN-->
          <div class="box box-success">
            <div class="box-body box-profile">
               <div class="table-responsive">
              <h3 class="profile-username text-center">{{strtoupper('Particulars of children')}}</h3>
              <table class="table table-condensed">
                  <thead class="text-gray-b">
                        <tr>
                            <td><b></b></td>
                            <td><b>Full Name</b></td>
                            <td><b>Sex</b></td>
                            <td><b>Date of Birth</b></td>
                            <td><b>Checked By</b></td>
                            <td><b></b></td>
                        </tr>
                  </thead>
                  <tbody>
                    @php $i=1; @endphp
                    @php if($staffFullDetailsChildren != null){ @endphp
                    @foreach($staffFullDetailsChildren as $child)
                        <tr>
                          <td>{{ $i++ }}</td>
                          <td>{{$child->fullname}}</td>
                          <td>{{$child->gender}}</td>
                          <td>{{date('d-m-Y', strtotime($child->dateofbirth))}}</td>
                          <td>{{$child->checked_children_particulars}}</td>
                           <td>
                         @if($getIDs->childrenparticulars==1)
                           	<a onclick="childrenEdit('{{ $child->id }}','{{ $child->staff }}','{{ $child->fullname }}','{{ $child->gID }}','{{ date('d-m-Y', strtotime($child->dateofbirth)) }}','{{ $child->checked_children_particulars }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                 @elseif($getIDs->childrenparticulars==0)
		                 
		                 @endif
		               </td>
                        </tr>
                      @endforeach
                      @php } @endphp
                  </tbody>
              </table>
             </div>
              
            </div>
          </div><!--//PARTICULARS OF CHILDREN-->

          <!--NEXT OF KIN-->
          <div class="box box-success">
            <div class="box-body box-profile">
               <div class="table-responsive">
              <h3 class="profile-username text-center">{{strtoupper('Next of kin')}}</h3>
              <table class="table table-condensed">
                  <thead class="text-gray-b">
                        <tr>
                            <td><b></b></td>
                            <td><b>Full Name</b></td>
                            <td><b>Address</b></td>
                            <td><b>Relationship</b></td>
                            <td><b>Phone No.</b></td>
                            <td><b></b></td>
                        </tr>
                  </thead>
                  <tbody>
                    @php $i=1; @endphp
                    @php if($nextOfKin != null){ @endphp
                    @foreach($nextOfKin as $nok)
                        <tr>
                          <td>{{ $i++ }}</td>
                          <td>{{ $nok->fullname }}</td>
                          <td>{{ $nok->address }}</td>
                          <td>{{ $nok->relationship }}</td>
                          <td>{{ $nok->phoneno }}</td>
                          <td>
		               	@if($getIDs->nok==1)
		               	    <a onclick="nokEdit('{{ $nok->kinID }}','{{ $nok->staffid }}','{{ $nok->fullname }}','{!! $nok->address !!}','{{ $nok->relationship }}','{{ $nok->phoneno }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                @elseif($getIDs->nok==0)
		                
		                @endif
		               </td>
                        </tr>
                    @endforeach
                     @php } @endphp
                    
                  </tbody>
              </table>
             </div>
             
            </div>
          </div>
          <!--//NEXT OF KIN-->

        </div>
        <!--//EDUCATION-->

        <!--//PERTICULAR OF BIRTH-->
        <div class="col-md-3">
          <div class="box box-success">
            <div class="box-body box-profile">
              
              <h3 class="profile-username text-center">{{strtoupper('Particulars of Birth')}}</h3>
              <table class="table table-condensed">
                  <tr>
                    <td><strong>Date of Birth:</strong> <span class="pull-right">
                    @php if((($staffFullDetails->dob) == "0000-00-00") or (($staffFullDetails->dob) == "")){ @endphp
                      {{$staffFullDetails->dob}}
                    @php }else{ @endphp
                      {{date('d-m-Y', strtotime($staffFullDetails->dob))}}
                     @php } @endphp
                    </span></td>
                  </tr>
                  <tr>
                    <td><strong>Place of Birth:</strong> <span class="pull-right">@foreach($getState as $list) @if($list->StateID==$staffFullDetails->placeofbirth){{ $list->State }}@else @endif @endforeach</span></td>
                  </tr>
                  <tr>
                    <td><strong>Marital Status:</strong> <span class="pull-right">{{$staffFullDetails->marital_status}}</span></td>
                  </tr>
              </table>
               @if($getIDs->birthparticulars==1)
              <div class="text-gray-c">
                  <a onclick="dobEdit('{{ $staffFullDetails->staffID }}','{{ date('d-m-Y', strtotime($staffFullDetails->dob)) }}','{{ $staffFullDetails->placeofbirth }}','{{ $staffFullDetails->msID }}')" style="cursor:pointer;">
                    <i class="fa fa-edit"></i> Edit
                  </a> 
                 
              </div>
               @elseif($getIDs->birthparticulars==0)
               
               @endif
            </div>
          </div>
           <!--//PERTICULAR OF BIRTH-->

            <!--//ACCOUNT/SALARY DETAILS -->
          <div class="box box-success">
            <div class="box-body box-profile">
              
              <h3 class="profile-username text-center">{{strtoupper('Salary Details')}}</h3>
              <table class="table table-condensed">
                  <tr>
                    <td>
                      <div><b>First Appointment:</b> 
                        <div class="pull-right">
                        @php if((($staffFullDetails->appointment_date) == "0000-00-00") or (($staffFullDetails->appointment_date) == "")){ @endphp
                          {{$staffFullDetails->appointment_date}}
                        @php }else{ @endphp
                          {{date('d-m-Y', strtotime($staffFullDetails->appointment_date))}}
                        @php } @endphp
                        </div>
                      </div>
                   </td>
                </tr>
                <tr>
                    <td>
                      <div><b>Resumption Date:</b> <div class="pull-right">
                       @php if((($staffFullDetails->firstarrival_date) == "0000-00-00") or (($staffFullDetails->firstarrival_date) == "")){ @endphp
                          {{$staffFullDetails->firstarrival_date}}
                        @php }else{ @endphp
                          {{date('d-m-Y', strtotime($staffFullDetails->firstarrival_date))}}
                        @php } @endphp
                      </div></div>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Employer:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->employmentType}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Designation:</strong> 
                        <div align="right" class="pull-right">
                            <small>{{$staffFullDetails->designation}}</small>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Department:</strong> 
                        <div align="right" class="pull-right">
                            {{$staffFullDetails->department}}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Section:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->section}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Grade Level:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->grade}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Step:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->step}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Bank:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->bank}}
                        </span>
                    </td>
                </tr>
                {{-- <tr>
                    <td>
                      <strong>Bank Group:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->bankGroup}}
                        </span>
                    </td>
                </tr> --}}
                <tr>
                    <td>
                      <strong>Bank Branch:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->bank_branch}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Account No.:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->AccNo}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>NHF No.:</strong> 
                        <span class="pull-right">
                            {{$staffFullDetails->nhfNo}}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                      <strong>Incremental Date:</strong> 
                        <span class="pull-right">
                            {{ date('d-m-Y', strtotime($staffFullDetails->incremental_date))}}
                        </span>
                    </td>
                </tr>
              </table>
              @if($getIDs->salary==1)
                  <div class="text-gray"> 
                        <a onclick="sEdit('{{ $staffFullDetails->staffID }}','{{ date('d-m-Y', strtotime($staffFullDetails->appointment_date)) }}','{{ date('d-m-Y', strtotime($staffFullDetails->firstarrival_date)) }}','{{ $staffFullDetails->empID }}','{{ $staffFullDetails->Designation }}','{{ $staffFullDetails->deptID }}','{{ $staffFullDetails->section }}','{{ $staffFullDetails->grade }}','{{ $staffFullDetails->step }}','{{ $staffFullDetails->bankID }}','{{ $staffFullDetails->bankGroup }}','{{ $staffFullDetails->bank_branch }}','{{ $staffFullDetails->AccNo }}','{{ $staffFullDetails->nhfNo }}','{{ date('d-m-Y', strtotime($staffFullDetails->incremental_date)) }}')" style="cursor:pointer;">
                            <i class="fa fa-edit"></i> Edit
                        </a> 
                        
                 </div>
              @elseif($getIDs->salary==0)
              
              @endif
             </div>
          </div>
           <!--//salary details-->

          <!--//PARTICULAR OF WIFE-->
          <div class="box box-success">
            <div class="box-body box-profile">
              
             
              <div class="table-responsive">
              <h3 class="profile-username text-center">{{strtoupper('Particulars of Spouse')}}</h3>
              <table class="table table-condensed">
                  <thead class="text-gray-b">
                        <tr>
                            <td><b></b></td>
                            <td><b>Spouse Name</b></td>
                            <td><b>Date of Birth</b></td>
                            <td><b>Marriage Date</b></td>
                            
                            <td><b></b></td>
                        </tr>
                  </thead>
                  <tbody>
                    @php $i=1; @endphp
                    @php if($staffFullDetailsWife != null){ @endphp
                    @foreach($staffFullDetailsWife as $details)
                        <tr>
                          <td>{{ $i++ }}</td>
                          <td>{{ $details->wifename }}</td>
                          <td>{{ date('d-m-Y', strtotime($details->wifedateofbirth)) }}</td>
                          <td>{{ date('d-m-Y', strtotime($details->dateofmarriage)) }}</td>
                          
                           <td>
                        @if($getIDs->wifeparticulars==1)
		               	<a onclick="wifeEdit('{{ $details->particularID }}','{{ $details->staffid }}','{{ $details->wifename }}','{{ date('d-m-Y', strtotime($details->wifedateofbirth)) }}','{{ date('d-m-Y', strtotime($details->dateofmarriage)) }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                @elseif($getIDs->wifeparticulars==0)
		                
		                @endif
		               </td>
                        </tr>
                    @endforeach
                     @php } @endphp
                  </tbody>
              </table>
             </div>
              
            </div>
          </div>
          <!--//PERTICULAR OF BIRTH WIFE--> 
        </div> 
</div><!-- /main row -->


 <div class="row">    
    <!--Details of Previous service-->
    <div class="col-md-12">
          <div class="box box-success">
            <div class="box-body box-profile">
              <h3 class="profile-username text-center">{{strtoupper('Details of Previous Service')}}</h3>
              <table class="table table-condensed">
                     <thead class="text-gray-b">
                        <tr>
                            <td><b></b></td>
                            <td><b>Previous Employers</b></td>
                            <td><b>From</b></td>
                            <td><b>To</b></td>
                            <td><b>Previous Pay</b></td>
                            <td><b>File Page Ref.</b></td>
                            <td><b>Checked BY</b></td>
                            <td><b></b></td>
                        </tr>
                      </thead>
                      <tbody>
                    @php $i=1; @endphp
                       @php if($staffFullDetailsPreviousService != null){ @endphp
                           @foreach($staffFullDetailsPreviousService as $ps)
                            <tr>
                              <td>{{ $i++ }}</td>
                              <td>{{$ps->previousSchudule}}</td>
                              <td>{{date('d-m-Y', strtotime($ps->fromDate))}}</td>
                              <td>{{date('d-m-Y', strtotime($ps->toDate))}}</td>
                               <td>&#8358;{{ number_format($ps->totalPreviousPay,2) }}</td>
                              <td>{{$ps->filePageRef}}</td>
                              <td>{{$ps->checkedby}}</td>
                            <td>
                                @if($getIDs->publicservice==1)
		               	          <a onclick="previousServiceEdit('{{ $ps->doppsid }}','{{ $ps->staffid }}','{{ $ps->previousSchudule }}','{{ date('d-m-Y', strtotime($ps->fromDate)) }}','{{ date('d-m-Y', strtotime($ps->toDate)) }}','{{ $ps->totalPreviousPay }}','{{ $ps->filePageRef }}','{{ $ps->checkedby }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                        @elseif($getIDs->publicservice==0)
		                        @endif
		                    </td>
                            </tr>
                            @endforeach
                         @php } @endphp
                      </tbody>
              </table>
              <div class="text-gray-c">
                   
              </div>
            </div>
          </div>
    </div>
    <!--//Details of previous service-->

    <!--//Record of leave type-->
    <div class="col-md-6">
          <div class="box box-success">
            <div class="box-body box-profile">
              <h3 class="profile-username text-center">{{strtoupper('Record of leave type')}}</h3>
              <table class="table table-condensed">
                     <thead style="color: #33AD0A;">
                        <tr>
                            <td><b>SN</b></td>
                            <td><b>Type of Leave</b></td>
                            <td><b>From</b></td>
                            <td><b>To</b></td>
                            <td><b>No. of Days</b></td>       
                        </tr>
                      </thead>
                      <tbody>
                       @php $i=1; @endphp
                       @php if($staffFullDetailsCensure != null){ @endphp
                           @foreach($staffFullDetailsCensure as $ps)
                            <tr>
                              <td>{{ $i++ }}</td>
                              <td>{{$ps->typeleave}}</td>
                              <td>{{date('d-m-Y', strtotime($ps->leavefrom))}}</td>
                              <td>{{date('d-m-Y', strtotime($ps->leaveto))}}</td>
                              <td>{{$ps->numberday}}</td>
		                    </td>
                            </tr>
                            @endforeach
                         @php } @endphp
                      </tbody>
              </table>
              <div class="text-gray-c">
                  
                  
              </div>
            </div>
          </div>
    </div>
    <!--//end Record of leave type-->

    <!--//Record of Censures and commendations-->
    <div class="col-md-6">
      <div class="box box-success">
        <div class="box-body box-profile">
          <h3 class="profile-username text-center">{{strtoupper('Record of Censures and commendations')}}</h3>
          <table class="table table-condensed">
                 <thead class="text-gray-b">
                    <tr>
                        <td><b>Date</b></td>
                        <td><b>File Page Ref.</b></td>
                        <td><b>Summary.</b></td>
                        <td><b>Compiled By</b></td>
                        <td><b></b></td>
                    </tr>
                  </thead>
                  <tbody>
                   @php $i=1; @endphp
                   @php if($staffFullDetailsCensure != null){ @endphp
                       @foreach($staffFullDetailsCensure as $ps)
                        <tr>
                          <td>{{date('d-m-Y', strtotime($ps->commendationdate))}}</td>
                          <td>{{$ps->fileref}}</td>
                          <td>{{$ps->summary}}</td>
                          <td>{{$ps->checked_commendation }}</td>
                          <td>
                            @if($getIDs->censors_recomm==1)
                           <a onclick="censorsAndCommendationEdit('{{ $ps->id }}','{{ $ps->staffid }}','{{ $ps->typeleave }}','{{ date('d-m-Y', strtotime($ps->leavefrom)) }}','{{ date('d-m-Y', strtotime($ps->leaveto)) }}','{{ $ps->numberday }}','{{ date('d-m-Y', strtotime($ps->commendationdate)) }}','{{ $ps->fileref }}','{{ $ps->summary }}','{{ $ps->checked_commendation }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
                        @elseif($getIDs->censors_recomm==0)
                        @endif
                    </td>
                        </tr>
                        @endforeach
                     @php } @endphp
                  </tbody>
          </table>
          <div class="text-gray-c">
              
              
          </div>
        </div>
      </div>
</div>
<!--//Record of censures-->

    <!--//Record of gratuity payments-->
    <div class="col-md-12">
          <div class="box box-success">
            <div class="box-body box-profile">
              <h3 class="profile-username text-center">{{strtoupper('Record of gratuity payments')}}</h3>
              <table class="table table-condensed">
                     <thead class="text-gray-b">
                        <tr>
                          <th  rowspan="2" style="vertical-align: top;">Date of Payment</th>
                          <th  colspan="5" class="text-center">Period Covered</th>
                          <th  rowspan="2" style="vertical-align: top;">Rate of Gratuity p.a</th>
                          <th  rowspan="2" style="vertical-align: top;">Amount Paid</th>
                          <th  rowspan="2" style="vertical-align: top;">File Page Ref.</th>
                          <th  rowspan="2" style="vertical-align: top;">Checked By</th>
                        </tr>
                        <tr>
                          <th>From</th>
                          <th>To</th>
                          <th>Yrs</th>
                          <th>Months</th>
                          <th>Days</th>
                        </tr>
                      </thead>
                      <tbody>
                       @php if($staffFullDetailsGratuityPayment != null){ @endphp
                           @foreach($staffFullDetailsGratuityPayment as $gratuity)
                            <tr>
                              <td>{{date('d-m-Y', strtotime($gratuity->dateofpayment))}}</td>
                              <td>{{date('d-m-Y', strtotime($gratuity->periodfrom))}}</td>
                              <td>{{date('d-m-Y', strtotime($gratuity->periodto))}}</td>
                              <td>{{$gratuity->periodyear}}</td>
                              <td>{{$gratuity->periodmonth}}</td>
                              <td>{{$gratuity->periodday}}</td>
                              <td>{{$gratuity->rateofgratuity}}</td>
                              <td>&#8358;{{ number_format($gratuity->amountpaid,2) }}</td>
                              <td>{{$gratuity->pageref }}</td>
                              <td>{{$gratuity->gratuitycheckedby }}</td>
                              <td>
                              @if($getIDs->gratuitypayment==1)
                                <a onclick="gratuityEdit('{{ $gratuity->id }}','{{ $gratuity->staffid }}','{{ date('d-m-Y', strtotime($gratuity->dateofpayment)) }}','{{ date('d-m-Y', strtotime($gratuity->periodfrom)) }}','{{ date('d-m-Y', strtotime($gratuity->periodto)) }}','{{ $gratuity->periodyear }}','{{ $gratuity->periodmonth }}','{{ $gratuity->periodday }}','{{ $gratuity->rateofgratuity }}','{{ $gratuity->amountpaid }}','{{ $gratuity->pageref }}','{{ $gratuity->gratuitycheckedby }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                      @elseif($getIDs->gratuitypayment==0)
		                      @endif
		                      </td>
                            </tr>
                            @endforeach
                         @php } @endphp
                      </tbody>
              </table>
              <div class="text-gray-c">
                  
              </div>
            </div>
          </div>
    </div>
    <!--//Record of gratuity payments-->

    <!--//Particular of termination of service-->
    <div class="col-md-12">
          <div class="box box-success">
            <div class="box-body box-profile">
              <h3 class="profile-username text-center">{{strtoupper('Particulars of termination of service')}}</h3>
              <table class="table table-condensed ">
                     <thead class="text-gray-b">
                        <tr>
                          <th  colspan="5" class="text-center">By Resignation/Invalidity</th>
                          <th  colspan="4" class="text-center">By Death</th>
                          <th  colspan="4" class="text-center">By Transfer</th>
                        </tr>
                        <tr>
                          <th style="vertical-align: top;">Date Term.</th>
                          <th style="vertical-align: top;">Pension/Contract</th>
                          <th style="vertical-align: top;">Pension of &#8358;</th>
                          <th style="vertical-align: top;">Gratuity of &#8358;</th>
                          <th style="vertical-align: top;">Contract Gratuity of &#8358;</th>

                          <th style="vertical-align: top;">Date of Demise</th>
                          <th style="vertical-align: top;">Gratuity Paid to Estate &#8358;</th>
                          <th style="vertical-align: top;">Widows Pension &#8358; p.a from</th>
                          <th style="vertical-align: top;">Orphans Pension &#8358; p.a from</th>

                          <th style="vertical-align: top;">Date of Trans.</th>
                          <th style="vertical-align: top;">Pension/Cont.</th>
                          <th style="vertical-align: top;">Aggregate Service In Nig.</th>
                          <th style="vertical-align: top;">Aggregate Salary in Nig. &#8358;</th>
                        </tr>
                      </thead>
                      <tbody>
                       @php if($staffFullDetailsTerminationService != null){ @endphp
                          @foreach($staffFullDetailsTerminationService as $terminate)
                            <tr>
                              <td>{{date('d-m-Y', strtotime($terminate->dateTerminated))}}</td>
                              <td>{{$terminate->pension_contract_terminate}}</td>
                              <td>{{ number_format($terminate->pensionAmount,2) }} p.a. From <br /> {{$terminate->pensionperanumfrom}}</td>
                              <td>{{ number_format($terminate->gratuity,2) }}</td>
                              <td>{{ number_format($terminate->contractGratuity,2) }}</td>
                              <td>{{date('d-m-Y', strtotime($terminate->dateOfDeath))}}</td>
                              <td>{{ number_format($terminate->gratuityPaidEstate,2)}}</td>
                              <td>{{ number_format($terminate->widowsPension,2) }} p.a. From <br />{{ date('d-m-Y', strtotime($terminate->widowsPensionFrom)) }}</td>
                              <td>{{ number_format($terminate->orphanPension,2) }} p.a. From <br /> {{ date('d-m-Y', strtotime($terminate->orphanPensionFrom)) }}</td>
                              <td>{{date('d-m-Y', strtotime($terminate->dateOfTransfer))}}</td>
                              <td>{{$terminate->pension_contract_transfer}}</td>
                              <td>{{$terminate->aggregateYears." ".$terminate->aggregateMonths ." ".$terminate->aggregateDays}}</td>
                              <td>{{ number_format($terminate->aggregateSalary,2) }}</td>
                              <td>
                              @if($getIDs->terminaofservice==1)
                               <a onclick="terminateEdit('{{ $terminate->terminateID }}','{{ $terminate->staffid }}','{{ date('d-m-Y', strtotime($terminate->dateTerminated)) }}','{{ $terminate->pension_contract_terminate }}','{{ $terminate->pensionAmount }}','{{ $terminate->pensionperanumfrom }}','{{ $terminate->gratuity }}','{{ $terminate->contractGratuity }}','{{ date('d-m-Y', strtotime($terminate->dateOfDeath)) }}','{{ $terminate->gratuityPaidEstate }}','{{ $terminate->widowsPension }}','{{ date('d-m-Y', strtotime($terminate->widowsPensionFrom)) }}','{{ $terminate->orphanPension }}','{{ date('d-m-Y', strtotime($terminate->orphanPensionFrom)) }}','{{ date('d-m-Y', strtotime($terminate->dateOfTransfer)) }}','{{ $terminate->pension_contract_transfer }}','{{ $terminate->aggregateYears }}','{{ $terminate->aggregateMonths }}','{{ $terminate->aggregateDays }}','{{ $terminate->aggregateSalary }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                          @elseif($getIDs->terminaofservice==0)
		                          @endif
		                          </td>
                            </tr>
                          @endforeach
                         @php } @endphp
                      </tbody>
              </table>
              <div class="text-gray-c">
                 
              </div>
            </div>
          </div>
    </div>
    <!--//Particular of termination of service-->

    <!--//Tour and Leave Record-->
    <div class="col-md-12">
          <div class="box box-success">
            <div class="box-body box-profile">
              <h3 class="profile-username text-center">{{strtoupper('Tour and Leave Records')}}</h3>
              <table class="table table-condensed ">
                     <thead>
                        <tr>
                          <th  rowspan="2" style="vertical-align: top;">Date Tour Started</th>
                          <th  rowspan="2" style="vertical-align: top;">Gezette Notice No.</th>
                          <th  rowspan="2" style="vertical-align: top;">Length of Tour for Age</th>
                          <th  rowspan="2" style="vertical-align: top;">Date Due for Leave</th>
                          <th  rowspan="2" style="vertical-align: top;">Date Departed on Leave</th>
                          <th  rowspan="2" style="vertical-align: top;">Gazette Notice No.</th>
                          <th  rowspan="2" style="vertical-align: top;">Date Due to Return from Leave</th>
                          <th  rowspan="2" style="vertical-align: top;">Date Extension Granted to</th>
                          <th  rowspan="2" style="vertical-align: top;">Salary Rule for Ext.</th>
                          <th  rowspan="2" style="vertical-align: top;">Date Resumed Duty</th>
                          <th  colspan="2" class="text-center" style="vertical-align: top;">Passage by<br/>Sea or Air</th>
                          <th  colspan="2" class="text-center" style="vertical-align: top;">Resident</th>
                          <th  colspan="2" class="text-center" style="vertical-align: top;">Leave</th>
                        </tr>
                        <tr>
                          <th><small>To UK</small></th>
                          <th><small>Fro UK</small></th>
                          <th><small>Mnths</small></th>
                          <th><small>Days</small></th>
                          <th><small>Mnths</small></th>
                          <th><small>Days</small></th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                       @php if($staffFullDetailsTourLeaveRecord != null){ @endphp
                           @foreach($staffFullDetailsTourLeaveRecord as $tourLeave)
                            <tr>
                                
                              <td>{{date('d-m-Y', strtotime($tourLeave->dateTourStarted))}}</td>
                              <td>{{$tourLeave->tourGezetteNumber}}</td>
                              <td>{{$tourLeave->lengthOfTour}}</td>
                              <td>{{date('d-m-Y', strtotime($tourLeave->leaveDueDate))}}</td>
                              <td>{{date('d-m-Y', strtotime($tourLeave->leaveDepartDate))}}</td>
                              <td>{{$tourLeave->leaveGezetteNumber}}</td>
                              <td>{{date('d-m-Y', strtotime($tourLeave->leaveReturnDate))}}</td>
                              <td>{{date('d-m-Y', strtotime($tourLeave->dateExtensionGranted))}}</td>
                              <td>{{$tourLeave->salaryRuleForExt }}</td>
                              <td>{{date('d-m-Y', strtotime($tourLeave->dateResumedDuty))}}</td>
                              <td>{{$tourLeave->toUK}}</td>
                              <td>{{$tourLeave->fromUK}}</td>
                              <td>{{$tourLeave->residentMonths}}</td>
                              <td>{{$tourLeave->residentDays}}</td>
                              <td>{{$tourLeave->leaveMonths }}</td>
                              <td>{{$tourLeave->leaveDays }}</td>
                              <td>
                              @if($getIDs->tour_leave==1)
                                <a onclick="tourleaveEdit('{{ $tourLeave->tourLeaveID }}','{{ $tourLeave->staffid }}','{{ date('d-m-Y', strtotime($tourLeave->dateTourStarted)) }}','{{ $tourLeave->tourGezetteNumber }}','{{ $tourLeave->lengthOfTour }}','{{ date('d-m-Y', strtotime($tourLeave->leaveDueDate)) }}','{{ date('d-m-Y', strtotime($tourLeave->leaveDepartDate)) }}','{{ $tourLeave->leaveGezetteNumber }}','{{ date('d-m-Y', strtotime($tourLeave->leaveReturnDate)) }}','{{ date('d-m-Y', strtotime($tourLeave->dateExtensionGranted)) }}','{{ $tourLeave->salaryRuleForExt }}','{{ date('d-m-Y', strtotime($tourLeave->dateResumedDuty)) }}','{{ $tourLeave->toUK }}','{{ $tourLeave->fromUK }}','{{ $tourLeave->residentMonths }}','{{ $tourLeave->residentDays }}','{{ $tourLeave->leaveMonths }}','{{ $tourLeave->leaveDays }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                      @elseif($getIDs->tour_leave==0)
		                      @endif
		                      </td>
		                      
                            </tr>
                            @endforeach
                         @php } @endphp
                      </tbody>
              </table>
              <div class="text-gray-c">
                  
              </div>
            </div>
          </div>
    </div>
    <!--//Tour and Leave Record-->

     <!--//Record of service-->
    <div class="col-md-12">
          <div class="box box-success">
            <div class="box-body box-profile">
              <h3 class="profile-username text-center">{{strtoupper('Record of service')}}</h3>
              <span>
                <small class="text-center">
                  <center>
                    To include details of all secondments, transfer, posting promotion (Acting and Subs) and Changes of Appointment
                  </center>
                </small>
              </span>
              <table class="table table-condensed table-bordered">
                     <thead>
                        <tr>
                          <th width="150" rowspan="2" style="vertical-align: top;">DATE ENTRY MADE</th>
                          <th rowspan="2" style="vertical-align: top;">DETAILS</th>
                          <th colspan="2" class="text-center" style="vertical-align: top;">CERTIFIED BY</th>
                        </tr>
                        <tr>
                          <th>Signature</th>
                          <th>Name Stamp</th>
                        </tr>
                        <th></th>
                      </thead>
                      <tbody>
                       @php if($staffFullDetailsRecordService != null){ @endphp
                           @foreach($staffFullDetailsRecordService as $rs)
                            <tr>
                              <td>{{date('d-m-Y', strtotime($rs->entryDate))}}</td>
                              <td>{{$rs->detail}}</td>
                              <td>{{$rs->signature}}</td>
                              <td>{{$rs->namestamp}}</td>
                              <td>
                              @if($getIDs->record_service==1)
                                <a onclick="servicerecordEdit('{{ $rs->recID }}','{{ $rs->staffid }}','{{ date('d-m-Y', strtotime($rs->entryDate)) }}','{{ $rs->detail }}','{{ $rs->signature }}','{{ $rs->namestamp }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                      @elseif($getIDs->record_service==0)
		                      @endif
		                      </td>
                            </tr>
                            @endforeach
                         @php } @endphp
                      </tbody>
              </table>
              <div class="text-gray-c">
                 
              </div>
            </div>
          </div>
    </div>
    <!--//Record of service-->

    <!--//Record of Emolument-->
    <div class="col-md-12">
          <div class="box box-success">
            <div class="box-body box-profile">
              <h3 class="profile-username text-center">{{strtoupper('Record of Emoluments')}}</h3>
                <table class="table table-condensed ">
                     <thead>
                        <tr>
                          <th rowspan="2" style="vertical-align: top;">Date Entry Made</th>
                          <th rowspan="2" style="vertical-align: top;">Salary Scale</th>
                          <th rowspan="2" style="vertical-align: top;">Basic Salary p.a.</th>
                          <th rowspan="2" style="vertical-align: top;">Inducement Pay p.a.</th>
                          <th rowspan="2" style="vertical-align: top;">Date Paid from</th>
                          <th colspan="2" class="text-center">Incremental Date</th>
                          <th rowspan="2" style="vertical-align: top;">AUTHORITY</th>
                          <th colspan="2" class="text-center">CERTIFIED BY</th>
                        </tr>
                        <tr>
                          <th><small>M</small></th>
                          <th><small>Yr</small></th>
                          <th>Signature</th>
                        
                        </tr>
                        <th></th>
                      </thead>
                      <tbody>
                       @php if($staffFullDetailsRecordEmolument != null){ @endphp
                           @foreach($staffFullDetailsRecordEmolument as $rs)
                            <tr>
                              <td>{{date('d-m-Y', strtotime($rs->entryDateMade))}}</td>
                              <td>{{$rs->salaryScale}}</td>
                              <td>&#8358;{{ number_format($rs->basicSalaryPA,2) }}</td>
                              <td>&#8358;{{ number_format($rs->inducementPayPA,2) }}</td>
                              <td>{{date('d-m-Y', strtotime($rs->datePaidFrom))}}</td>
                              <td>{{date('M', strtotime($rs->month))}}</td>
                              <td>{{$rs->year}}</td>
                              <td>{{$rs->authority}}</td>
                              <td>{{$rs->signature}}</td>
                              <td>
                              @if($getIDs->record_emolument==1)
                                <a onclick="emolumentrecordEdit('{{ $rs->emolumentID }}','{{ $rs->staffid }}','{{ date('d-m-Y', strtotime($rs->entryDateMade)) }}','{{ $rs->salaryScale }}','{{ $rs->basicSalaryPA }}','{{ $rs->inducementPayPA }}','{{ date('d-m-Y', strtotime($rs->datePaidFrom)) }}','{{ $rs->month }}','{{ $rs->year }}','{{ $rs->authority }}','{{ $rs->signature }}')" class="btn btn-success  glyphicon glyphicon-edit btn-xs"  style="cursor:pointer;"></a>&nbsp;
		                      @elseif($getIDs->record_emolument==0)
		                      @endif
		                      </td>
                            </tr>
                            @endforeach
                         @php } @endphp
                      </tbody>
              </table>
              <div class="text-gray-c">
                  
              </div>
            </div>
          </div>
    </div>
    <!--//Record of service-->

</div><!--//main roll2-->


@endsection
@section('styles')
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/custom-style.css')}}">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.2/css/buttons.dataTables.min.css">
@endsection
@section('styles')
<style> 
  
  
  #editSALARYINFO{
    display: table;
    height: 100%;
    width: 100%;
    position:absolute;
    background-color:#FF0000;   
    margin-top:250px;
}

  .textbox { 
    border: 1px;
    background-color: #33AD0A; 
    outline:0; 
    height:25px; 
    width: 275px; 
  } 
  $('.autocomplete-suggestions').css({
    color: '#0f3'
  });

  .autocomplete-suggestions{
    color:#fff;
    font-size: 15px;
  }
</style> 
@endsection
@section('scripts')
<!--loading vuejs -->
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.js"></script>

<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- autocomplete js-->
<script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}" ></script>
<script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
<!--<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>-->

<script>
 
 $(document).ready(function() {
    $('#').DataTable();
  } );

  $(document).ready(function() {
      $('#mytable').DataTable( {
          dom: 'Bfrtip',
          //"pageLength": 1,
          buttons: [
              {
                  extend: 'print',
                  customize: function ( win ) {
                      $(win.document.body)
                          .css( 'font-size', '10pt' )
                          .prepend(
                              ''
                          );
   
                      $(win.document.body).find( 'table' )
                          .addClass( 'compact' )
                          .css( 'font-size', 'inherit' );
                  }
              }
          ]
      } );
  } );


</script>
<script type="text/javascript">
  $(function() {
      $('#searchName').attr("disabled", false);
      $("#autocomplete").autocomplete({
        serviceUrl: murl + '/profile/searchUser',
        minLength: 2,
        onSelect: function (suggestion) {
            $('#fileNo').val(suggestion.data);
            $('#searchName').attr("disabled", false);
            showAll();
        }
      });
  });
</script>
<script type="text/javascript">
    
     //process date of appointment_date
        $(document).ready(function () {
                
                $('#appointment_date').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#appointment_date').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        }); 
        
         //process date of firstarrival_date
        $(document).ready(function () {
                
                $('#firstarrival_date').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#firstarrival_date').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        }); 
        
         //process date of incrementaldate
        $(document).ready(function () {
                
                $('#incrementaldate').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#incrementaldate').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        }); 
        
        //process date of dob
        $(document).ready(function () {
                
                $('#dob').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dob').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        }); 
        
        //process date of education
        $(document).ready(function () {
                
                $('#schoolfrom').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#schoolfrom').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
        
        $(document).ready(function () {
                
                $('#schoolto').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#schoolto').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        }); 
        
        //process date of particulars of children
        $(document).ready(function () {
                
                $('#dateofbirth').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dateofbirth').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
       
       //process date of wife particulars
        $(document).ready(function () {
                
                $('#wifedob').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#wifedob').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
        
        $(document).ready(function () {
                
                $('#marriagedate').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#marriagedate').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        //process date for previous public service
        $(document).ready(function () {
                
                $('#prefrom').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#prefrom').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
        
        $(document).ready(function () {
                
                $('#preto').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#preto').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        //process date for censores and commendations
        $(document).ready(function () {
                
                $('#leavefrom').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#leavefrom').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
        
        $(document).ready(function () {
                
                $('#leaveto').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#leaveto').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#commendationdate').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#commendationdate').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        //process date for gratuity
        $(document).ready(function () {
                
                $('#dateofpayment').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dateofpayment').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
        
        $(document).ready(function () {
                
                $('#periodfrom').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#periodfrom').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#periodto').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#periodto').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
         //process date for terminate of service
        $(document).ready(function () {
                
                $('#dateTerminated').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dateTerminated').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
        
        $(document).ready(function () {
                
                $('#pensionperanumfrom').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#pensionperanumfrom').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#dateOfDeath').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dateOfDeath').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#dateOfTransfer').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dateOfTransfer').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#widowsPensionFrom').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#widowsPensionFrom').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#orphanPensionFrom').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#orphanPensionFrom').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
         //process date for tour and leave
        $(document).ready(function () {
                
                $('#dateTourStarted').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dateTourStarted').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
        
        $(document).ready(function () {
                
                $('#leaveDueDate').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#leaveDueDate').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#leaveDepartDate').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#leaveDepartDate').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#leaveReturnDate').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#leaveReturnDate').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#dateExtensionGranted').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dateExtensionGranted').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
        $(document).ready(function () {
                
                $('#dateResumedDuty').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#dateResumedDuty').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });  
        
         //process date for record of service
        $(document).ready(function () {
                
                $('#entryDate').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#entryDate').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
         //process date for record of emolument
        $(document).ready(function () {
                
                $('#eentryDate').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#eentryDate').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
         $(document).ready(function () {
                
                $('#datePaidFrom').datepicker({
                    format: "yyyy-mm-dd"
                });  
            
            });
            
        $('#datePaidFrom').datepicker({
        autoclose: true,  
        format: "yyyy-mm-dd"
        });
        
        
        
</script>
<script>

 function profilePicEdit(x){
     
      document.getElementById('PfileID').value = x;
     
       $("#editProfilePic").modal('show')
     
 }
 
</script>

<script>

 function profileEdit(x,y,z,a,b,c,d,e,f,g,h,i,j){
     
      document.getElementById('fileID').value = x;
      document.getElementById('fileNo').value = y;
      document.getElementById('divs').value = z;
      document.getElementById('titles').value = a;
      document.getElementById('surname').value = b;
      document.getElementById('firstname').value = c;
      document.getElementById('othernames').value = d;
      
      document.getElementById('address').value = e;
      document.getElementById('gender').value = f;
      document.getElementById('currentstate').value = g;
      document.getElementById('phone').value = h;
      document.getElementById('nationality').value = i;
      document.getElementById('status').value = j;

       $("#editBIO").modal('show')
     
 }
 
</script>

<script>
    function educationEdit(x,y,z,a,b,c,d){
     
       
      document.getElementById('eduID').value = x;
      document.getElementById('fID').value = y;
      document.getElementById('degreequalification').value = z;
      document.getElementById('schoolattended').value = a;
      document.getElementById('schoolfrom').value = b;
      document.getElementById('schoolto').value = c;
      document.getElementById('certificateheld').value = d;
      
      $("#editEDU").modal('show')
      
      
 }
</script>

<script>
    function languageEdit(x,y,z,a,b,c,d){
     
       
      document.getElementById('langID').value = x;
      document.getElementById('stID').value = y;
      document.getElementById('language').value = z;
      document.getElementById('spoken').value = a;
      document.getElementById('written').value = b;
      document.getElementById('exam_qualified').value = c;
      document.getElementById('checkedby').value = d;
      
      $("#editLANGUAGE").modal('show')
      
      
 }
</script>

<script>
    function childrenEdit(x,y,z,a,b,c){
     
       
      document.getElementById('recordID').value = x;
      document.getElementById('parentID').value = y;
      document.getElementById('fullname').value = z;
      document.getElementById('gender2').value = a;
      document.getElementById('dateofbirth').value = b;
      document.getElementById('checked_children_particulars').value = c;
     
      $("#editCHILDREN").modal('show')
      
      
 }
</script>

<script>
    function nokEdit(x,y,z,a,b,c){
     
       
      document.getElementById('nokID').value = x;
      document.getElementById('nokparentID').value = y;
      document.getElementById('nokfullname').value = z;
      document.getElementById('nokaddress').value = a;
      document.getElementById('nokrelationship').value = b;
      document.getElementById('nokphoneno').value = c;
     
      $("#editNOK").modal('show')
      
      
 }
</script>

<script>
    function wifeEdit(x,y,z,a,b){
     
       //var t=x;
      
      document.getElementById('wifeID').value = x;
      document.getElementById('husbandID').value = y;
      document.getElementById('wifename').value = z;
      document.getElementById('wifedob').value = a;
      document.getElementById('marriagedate').value = b;
      
      $("#editWIFE").modal('show')
       
 }
</script>

<script>
    function previousServiceEdit(x,y,z,a,b,c,d,e){
     
       //var t=x;
      
      document.getElementById('serviceID').value = x;
      document.getElementById('userID').value = y;
      document.getElementById('preemployer').value = z;
      document.getElementById('prefrom').value = a;
      document.getElementById('preto').value = b;
      document.getElementById('prepay').value = c;
      document.getElementById('prefileref').value = d;
      document.getElementById('precheckedby').value = e;
      
      $("#editPRECIOUSSERVICE").modal('show')
       
 }
</script>

<script>
    function censorsAndCommendationEdit(x,y,z,a,b,c,d,e,f,g){
     
       //var t=x;
      
      document.getElementById('censorID').value = x;
      document.getElementById('censorUserID').value = y;
      document.getElementById('leavetype').value = z;
      document.getElementById('leavefrom').value = a;
      document.getElementById('leaveto').value = b;
      document.getElementById('numberdate').value = c;
      document.getElementById('commendationdate').value = d;
      document.getElementById('censorfileref').value = e;
      document.getElementById('summary').value = f;
      document.getElementById('checked_commendation').value = g;
      
      $("#editCENSORSANDCOMMENDATION").modal('show')
       
 }
</script>

<script>
    function gratuityEdit(x,y,z,a,b,c,d,e,f,g,h,i){
     
       //var t=x;
      
      document.getElementById('gratuityID').value = x;
      document.getElementById('gratuityUserID').value = y;
      document.getElementById('dateofpayment').value = z;
      document.getElementById('periodfrom').value = a;
      document.getElementById('periodto').value = b;
      document.getElementById('periodyear').value = c;
      document.getElementById('periodmonth').value = d;
      document.getElementById('periodday').value = e;
      document.getElementById('rateofgratuity').value = f;
      document.getElementById('amountpaid').value = g;
      document.getElementById('pageref').value = h;
      document.getElementById('gratuitycheckedby').value = i;
      
      $("#editGRATUITY").modal('show')
       
 }
</script>

<script>
    function terminateEdit(x,y,z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q){
     
      document.getElementById('terminateID').value = x;
      document.getElementById('terminateUserID').value = y;
      document.getElementById('dateTerminated').value = z;
      document.getElementById('pension_contract_terminate').value = a;
      document.getElementById('pensionamount').value = b;
      document.getElementById('pensionperanumfrom').value = c;
      document.getElementById('gratuity').value = d;
      document.getElementById('contractGratuity').value = e;
      document.getElementById('dateOfDeath').value = f;
      document.getElementById('gratuityPaidEstate').value = g;
      document.getElementById('widowsPension').value = h;
      document.getElementById('widowsPensionFrom').value = i;
      document.getElementById('orphanPension').value = j;
      document.getElementById('orphanPensionFrom').value = k;
      document.getElementById('dateOfTransfer').value = l;
      document.getElementById('pension_contract_transfer').value =m;
      document.getElementById('aggregateYears').value = n;
      document.getElementById('aggregateMonths').value = o;
      document.getElementById('aggregateDays').value = p;
      document.getElementById('aggregateSalary').value = q;
      
      $("#editTERMINATE").modal('show')
       
 }
</script>

<script>
    function tourleaveEdit(x,y,z,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o){
     
      document.getElementById('tourLeaveID').value = x;
      document.getElementById('leaveUserID').value = y;
      document.getElementById('dateTourStarted').value = z;
      document.getElementById('tourGezetteNumber').value = a;
      document.getElementById('lengthOfTour').value = b;
      document.getElementById('leaveDueDate').value = c;
      document.getElementById('leaveDepartDate').value = d;
      document.getElementById('leaveGezetteNumber').value = e;
      document.getElementById('leaveReturnDate').value = f;
      document.getElementById('dateExtensionGranted').value = g;
      document.getElementById('salaryRuleForExt').value = h;
      document.getElementById('dateResumedDuty').value = i;
      document.getElementById('toUK').value = j;
      document.getElementById('fromUK').value = k;
      document.getElementById('residentMonths').value = l;
      document.getElementById('residentDays').value =m;
      document.getElementById('leaveMonths').value = n;
      document.getElementById('leaveDays').value = o;
      
      
      $("#editTOURLEAVE").modal('show')
       
 }
</script>

<script>
    function servicerecordEdit(x,y,z,a,b,c){
     
      document.getElementById('recID').value = x;
      document.getElementById('serviceUserID').value = y;
      document.getElementById('entryDate').value = z;
      document.getElementById('detail').value = a;
      document.getElementById('signature').value = b;
      document.getElementById('namestamp').value = c;
      
      $("#editSERVICERECORD").modal('show')
       
 }
</script>

<script>
    function emolumentrecordEdit(x,y,z,a,b,c,d,e,f,g,h){
     
      document.getElementById('emolumentID').value = x;
      document.getElementById('emolumentUserID').value = y;
      document.getElementById('eentryDate').value = z;
      document.getElementById('salaryScale').value = a;
      document.getElementById('basicSalaryPA').value = b;
      document.getElementById('inducementPayPA').value = c;
      document.getElementById('datePaidFrom').value = d;
      document.getElementById('month').value = e;
      document.getElementById('year').value = f;
      document.getElementById('authority').value = g;
      document.getElementById('ssignature').value = h;
      
      $("#editEMOLUMENTRECORD").modal('show')
       
 }
</script>

<script>
    function dobEdit(x,y,z,r){
     
       //var t=x;
      
      document.getElementById('fileID2').value = x;
      document.getElementById('dob').value = y;
      document.getElementById('pob').value = z;
      document.getElementById('ms').value = r;
      $("#editDOB").modal('show')
        //$.get('/get-dob-details?fileID='+t, function(data){
       // console.log(data);
       
       // $.each(data, function(index, obj){
            // $('#dob').val(obj.degreequalification);
             //$('#pob').val(obj.schoolattended);
             //$('#ms').val(obj.schoolfrom);
             
             //$("#editDOB").modal('show')
       // });
         
        
       // })
      
 }
</script>

<script>
    function sEdit(x,y,z,a,b,c,d,e,f,g,h,i,j,k,l){
     
       //var t=x;
      
      document.getElementById('ID3').value = x;
      document.getElementById('appointment_date').value = y;
      document.getElementById('firstarrival_date').value = z;
      document.getElementById('employee_type').value = a;
      document.getElementById('Designation').value = b;
      document.getElementById('department').value = c;
      document.getElementById('section').value = d;
      
      document.getElementById('grade').value = e;
      document.getElementById('step').value = f;
      document.getElementById('bank').value = g;
      document.getElementById('bankgroup').value = h;
      document.getElementById('bankbranch').value = i;
      document.getElementById('accno').value = j;
      document.getElementById('nhfno').value = k;
      document.getElementById('incrementaldate').value = l;
      
      $("#editSALARYINFO").modal('show')
        
      
 }
</script>

<script type="text/javascript">
   // When the document is ready
    $("#refreshPage").click(function(){
        
            history.pushState('Staff Page', 'Judicial Payroll', 'https://jippis.njc.gov.ng/profile/details');
         
    });
</script>
@endsection