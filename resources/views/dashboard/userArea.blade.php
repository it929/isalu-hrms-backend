@extends('layouts.layout')
@section('pageTitle')
    Dashboard
@endsection

@section('content')
    <div class="box-body">
        <?php $url = $_SERVER['HTTP_HOST']; ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="text-center"><strong>ISALU HR AND PAYROLL MANAGEMENT SYSTEM</strong></h3>
                        <p class="text-red lead text-center">
                            For assistance, please contact the Isalu IT Department. Thank you.
                        </p>
                        <h4 style="text-align: center">{{ $divisionName }}</h4>

                        {{-- Show user's role info --}}
                        @if ($userRoleName)
                            <div class="text-center" style="margin-bottom: 10px;">
                                <span class="label label-primary">
                                    <i class="fa fa-user-tag"></i> Role: {{ $userRoleName }}
                                </span>
                            </div>
                        @endif

                        {{-- Show warning if user is using default password --}}
                        @if (\Illuminate\Support\Facades\Hash::check('12345', Auth::user()->password))
                            <div class="alert alert-danger alert-dismissible" style="margin: 15px auto; max-width: 800px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center;">
                                <h4><i class="icon fa fa-lock"></i> Security Warning!</h4>
                                <p style="font-size: 1.1em; margin-bottom: 15px;">You are currently logged in with the default password (<strong>12345</strong>). For security reasons, please change your password immediately.</p>
                                <a href="{{ url('user/editAccount') }}" class="btn btn-warning btn-sm" style="color: #000; font-weight: bold; border-radius: 4px; padding: 6px 15px;">
                                    <i class="fa fa-key"></i> Change Password Now
                                </a>
                            </div>
                        @endif

                        {{-- Show warning if no widgets assigned --}}
                        @if (!$hasAnyWidget)
                            <div class="alert alert-warning text-center" style="margin-top: 15px;">
                                <i class="fa fa-exclamation-triangle"></i>
                                No dashboard widgets have been assigned to your role.
                                Please contact your administrator.
                            </div>
                        @endif
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <!-- Dashboard Cards - Organized in 3 columns per row -->
                        <div class="row">
                            @php
                                // Define all possible widgets in the order you want them to appear
                                $widgetOrder = [
                                    'Total Staff' => [
                                        'color' => 'bg-blue',
                                        'icon' => 'fa-user-circle',
                                        'text' => 'Total Staff',
                                        'data' => $totalStaffCount,
                                        'description' => 'Total personnel',
                                    ],

                                    'Active Staff' => [
                                        'color' => 'bg-green',
                                        'icon' => 'fa-user-check',
                                        'text' => 'Active Staff',
                                        'data' => $activeStaffCount,
                                        'description' => 'Total active personnel',
                                    ],
                                    'Inactive Staff' => [
                                        'color' => 'bg-red',
                                        'icon' => 'fa-user-times',
                                        'text' => 'Inactive Staff',
                                        'data' => $inactiveStaffCount,
                                        'description' => 'Total inactive personnel',
                                    ],
                                    // 'Justices' => [
                                    //     'color' => 'bg-purple',
                                    //     'icon' => 'fa-balance-scale',
                                    //     'text' => 'Justices',
                                    //     'data' => $justicesCount,
                                    //     'description' => 'Total judicial officers',
                                    // ],
                                    'Contractors' => [
                                        'color' => 'bg-orange',
                                        'icon' => 'fa-file-contract',
                                        'text' => 'Contractors',
                                        'data' => $contractorsCount,
                                        'description' => 'Total contract personnel',
                                    ],
                                    'Total Vouchers Raised' => [
                                        'color' => 'bg-aqua',
                                        'icon' => 'fa-file-invoice-dollar',
                                        'text' => 'Total Vouchers <br> Raised',
                                        'data' => $totalVouchers,
                                        'description' => 'Total vouchers in system',
                                    ],
                                    'Processed Vouchers' => [
                                        'color' => 'bg-teal',
                                        'icon' => 'fa-check-circle',
                                        'text' => 'Processed <br> Vouchers',
                                        'data' => $processedVouchers,
                                        'description' => 'Total processed vouchers',
                                    ],
                                ];

                                $counter = 0;
                            @endphp

                            @foreach ($widgetOrder as $widgetName => $widgetData)
                                @if (in_array($widgetName, $assignedWidgets))
                                    @php $counter++; @endphp
                                    <div class="col-md-4 col-sm-6 col-xs-12">
                                        <div class="info-box {{ $widgetData['color'] }}">
                                            <span class="info-box-icon"><i class="fa {{ $widgetData['icon'] }}"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{!! $widgetData['text'] !!}</span>
                                                <span
                                                    class="info-box-number">{{ number_format($widgetData['data']) }}</span>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: 100%"></div>
                                                </div>
                                                <span class="progress-description">
                                                    {{ $widgetData['description'] }}
                                                </span>
                                            </div>
                                            <!-- /.info-box-content -->
                                        </div>
                                        <!-- /.info-box -->
                                    </div>
                                    <!-- /.col -->

                                    {{-- Start new row after every 3 cards --}}
                                    @if ($counter % 3 == 0)
                        </div>
                        <div class="row">
                            @endif
                            @endif
                            @endforeach

                            {{-- If no widgets at all, show empty state --}}
                            @if (empty($assignedWidgets) && $userRoleID)
                                <div class="col-md-12">
                                    <div class="alert alert-info text-center">
                                        <h4><i class="fa fa-info-circle"></i> No Widgets Assigned</h4>
                                        <p>Your role ({{ $userRoleName }}) doesn't have any dashboard widgets assigned yet.
                                        </p>
                                        <p>Please contact your administrator to assign widgets to your role.</p>
                                        @if (Auth::user()->user_type == 'Administrator')
                                            <hr>
                                            <a href="{{ route('role-widget.form') }}" class="btn btn-primary">
                                                <i class="fa fa-sliders"></i> Assign Widgets to Roles
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <!-- /.row -->

                        <!-- Charts Section -->
                        @if (in_array('Staff Distribution', $assignedWidgets) ||
                                in_array('Workforce Composition', $assignedWidgets) ||
                                in_array('Voucher Analytics', $assignedWidgets))
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-12">
                                    <div class="box box-default">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Dashboard Analytics</h3>
                                        </div>
                                        <div class="box-body">
                                            <div class="row">
                                                <!-- Staff Distribution Chart -->
                                                @if (in_array('Staff Distribution', $assignedWidgets))
                                                    <div class="col-md-6">
                                                        <div class="box box-info">
                                                            <div class="box-header with-border">
                                                                <h3 class="box-title">Staff Distribution</h3>
                                                            </div>
                                                            <div class="box-body">
                                                                <canvas id="staffChart" height="250"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Workforce Composition Chart -->
                                                @if (in_array('Workforce Composition', $assignedWidgets))
                                                    <div class="col-md-6">
                                                        <div class="box box-success">
                                                            <div class="box-header with-border">
                                                                <h3 class="box-title">Workforce Composition</h3>
                                                            </div>
                                                            <div class="box-body">
                                                                <canvas id="workforceChart" height="250"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Voucher Analytics Chart -->
                                            @if (in_array('Voucher Analytics', $assignedWidgets))
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="box box-warning">
                                                            <div class="box-header with-border">
                                                                <h3 class="box-title">Voucher Analytics</h3>
                                                            </div>
                                                            <div class="box-body">
                                                                <canvas id="voucherChart" height="150"></canvas>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.row -->
                        @endif

                        {{-- Show assigned widgets info for debugging --}}
                        @if (Auth::user()->user_type == 'Administrator' && !empty($assignedWidgets))
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <strong>Debug Info (Admin Only):</strong><br>
                                        Role ID: {{ $userRoleID }}<br>
                                        Role Name: {{ $userRoleName }}<br>
                                        Assigned Widgets: {{ implode(', ', $assignedWidgets) }}<br>
                                        Widget Count: {{ count($assignedWidgets) }}
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
        </div>
    </div>

    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add Chart.js for charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Custom styles for better spacing */
        .info-box {
            margin-bottom: 20px;
            min-height: 130px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .info-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .info-box-icon {
            border-radius: 8px 0 0 8px;
            font-size: 45px;
            padding-top: 25px;
        }

        .info-box-content {
            padding: 15px;
        }

        .info-box-text {
            font-size: 16px;
            font-weight: 600;
        }

        .info-box-number {
            font-size: 28px;
            font-weight: 700;
            margin: 10px 0;
        }

        .progress {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
            height: 6px;
            margin: 8px 0;
        }

        .progress-bar {
            border-radius: 3px;
        }

        .progress-description {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Color variants */
        .bg-green {
            background-color: #00a65a !important;
        }

        .bg-red {
            background-color: #dd4b39 !important;
        }

        .bg-purple {
            background-color: #605ca8 !important;
        }

        .bg-aqua {
            background-color: #00c0ef !important;
        }

        .bg-teal {
            background-color: #39cccc !important;
        }

        .bg-orange {
            background-color: #ff851b !important;
        }

        /* Chart box styles */
        .box {
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .box-header {
            border-bottom: 1px solid #f4f4f4;
            padding: 15px;
        }

        .box-title {
            font-weight: 600;
            color: #333;
        }

        /* Label styles */
        .label-primary {
            background-color: #3c8dbc;
            padding: 5px 10px;
            font-size: 14px;
        }

        /* Ensure equal height for cards in same row */
        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .col-md-4 {
            display: flex;
        }

        .info-box {
            flex: 1;
        }
    </style>

    <script>
        // Wait for the page to load
        document.addEventListener('DOMContentLoaded', function() {
            // Staff Distribution Chart (Pie Chart) - Only initialize if element exists
            var staffChartElement = document.getElementById('staffChart');
            if (staffChartElement) {
                var staffCtx = staffChartElement.getContext('2d');
                var staffChart = new Chart(staffCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Active Staff', 'Inactive Staff',],
                        datasets: [{
                            data: [{{ $activeStaffCount }}, {{ $inactiveStaffCount }}

                            ],
                            backgroundColor: [
                                '#00a65a', // Green for Active Staff
                                '#dd4b39', // Red for Inactive Staff
                                '#605ca8' // Purple for Justices
                            ],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.raw || 0;
                                        var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        var percentage = Math.round((value / total) * 100);
                                        return label + ': ' + value + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Workforce Composition Chart (Bar Chart) - Only initialize if element exists
            var workforceChartElement = document.getElementById('workforceChart');
            if (workforceChartElement) {
                var workforceCtx = workforceChartElement.getContext('2d');
                var workforceChart = new Chart(workforceCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Permanent Staff', 'Contract Staff'],
                        datasets: [{
                            label: 'Number of Personnel',
                            data: [{{ $totalStaff }}, {{ $contractorsCount }}],
                            backgroundColor: [
                                'rgba(0, 123, 255, 0.7)', // Blue
                                'rgba(255, 159, 64, 0.7)' // Orange
                            ],
                            borderColor: [
                                'rgba(0, 123, 255, 1)',
                                'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Personnel'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Employment Type'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.raw;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Voucher Analytics Chart (Horizontal Bar Chart) - Only initialize if element exists
            var voucherChartElement = document.getElementById('voucherChart');
            if (voucherChartElement) {
                var voucherCtx = voucherChartElement.getContext('2d');
                var voucherChart = new Chart(voucherCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Total Vouchers', 'Processed Vouchers'],
                        datasets: [{
                            label: 'Number of Vouchers',
                            data: [{{ $totalVouchers }}, {{ $processedVouchers }}],
                            backgroundColor: [
                                'rgba(0, 192, 239, 0.7)', // Aqua/Blue for Total Vouchers
                                'rgba(57, 204, 204, 0.7)' // Teal for Processed Vouchers
                            ],
                            borderColor: [
                                'rgba(0, 192, 239, 1)',
                                'rgba(57, 204, 204, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                title: {
                                    display: true,
                                    text: 'Number of Vouchers'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Voucher Type'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        var label = context.label || '';
                                        var value = context.raw || 0;
                                        var total = context.dataset.data[
                                        0]; // Total vouchers is first element
                                        var percentage = (label === 'Processed Vouchers' && total > 0) ?
                                            Math.round((value / total) * 100) :
                                            0;
                                        return label + ': ' + value + (percentage > 0 ? ' (' +
                                            percentage + '%)' : '');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Add animation to info boxes
            document.querySelectorAll('.info-box').forEach(function(box) {
                box.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.15)';
                });

                box.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                });
            });

            // Fix Bootstrap row display issues
            document.querySelectorAll('.row').forEach(function(row) {
                // Remove empty columns caused by conditional rendering
                var cols = row.querySelectorAll('.col-md-4');
                cols.forEach(function(col, index) {
                    if (!col.querySelector('.info-box')) {
                        col.style.display = 'none';
                    }
                });
            });

            // Refresh dashboard data every 5 minutes (optional)
            setTimeout(function() {
                // You can add AJAX refresh here if needed
                // console.log('Dashboard data can be refreshed here');
            }, 300000); // 5 minutes
        });
    </script>
@endsection
