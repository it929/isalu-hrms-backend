@extends('layouts.layout')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border hidden-print">
                    <h3 class="box-title">STAFF PROFILE <span id='processing'></span></h3>
                </div>
                <form method="post" action="{{ url('/profile/details') }}">
                    <div class="box-body row">
                        <div class="form-group col-md-10">
                            {{ csrf_field() }}
                            <input id="autocomplete" name="q" class="form-control input-lg"
                                placeholder="Search By First Name, Surname or File Number">
                            <input type="hidden" id="fileNo" name="fileNo">
                            <span style="color: #999;">(If you don't see name suggestion after typing to the above text
                                field, please refresh your page.)</span>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="searchName" id="searchName" class="btn btn-default btn-lg"><i
                                    class="fa fa-search"></i> Search</button>
                        </div>
                    </div>

                </form>
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

                @if (session('msg'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Success!</strong> {{ session('msg') }}
                    </div>
                @endif
                @if (session('err'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Staff Not Available for this Division! <br></strong> {{ session('err') }}
                    </div>
                @endif
            </div>
        </div>
    </div>


@endsection
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom-style.css') }}">
@endsection
@section('styles')
    <style>
        .textbox {
            border: 1px;
            background-color: #33AD0A;
            outline: 0;
            height: 25px;
            width: 275px;
        }

        $('.autocomplete-suggestions').css({
            color: 'red'
        });

        .autocomplete-suggestions {
            color: #fff;
            font-size: 15px;
        }
    </style>
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <!-- autocomplete js-->
    <script src="{{ asset('assets/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-hr.js') }}" type="text/javascript"></script>
    <script>
        var murl = "{{ url('') }}";
    </script>
    {{-- <script type="text/javascript">
        $(function() {
            $('#searchName').attr("disabled", true);
            $("#autocomplete").autocomplete({
                serviceUrl: murl + '/profile/searchUser',
                minLength: 2,
                onSelect: function(suggestion) {
                    $('#fileNo').val(suggestion.data);
                    $('#searchName').attr("disabled", false);
                    showAll();
                }
            });
        });
    </script> --}}
    <script>
        $(function() {
            $('#searchName').attr("disabled", true);

            $("#autocomplete").autocomplete({
                serviceUrl: "{{ url('/profile/searchUser') }}",
                paramName: "query", // important: matches your controller input name
                dataType: "json",
                minChars: 2,
                onSelect: function(suggestion) {
                    $('#fileNo').val(suggestion.data);
                    $('#searchName').attr("disabled", false);
                }
            });
        });
    </script>
@endsection
