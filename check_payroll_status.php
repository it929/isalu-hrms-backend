<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$activeMonth = DB::table('tblactivemonth')->first();
echo "Active Month: " . json_encode($activeMonth) . "\n\n";

$conptCount = DB::table('payroll_conpt')->count();
echo "Total Computed Records in payroll_conpt: {$conptCount}\n\n";

if ($conptCount > 0) {
    $stages = DB::table('payroll_conpt')
        ->select('month', 'year', 'vstage', 'is_paid', DB::raw('count(*) as count'))
        ->groupBy('month', 'year', 'vstage', 'is_paid')
        ->get();
    echo "Records by Month/Year/Vstage/IsPaid:\n";
    foreach ($stages as $s) {
        echo "  Month: {$s->month}, Year: {$s->year}, VStage: {$s->vstage}, Is Paid: {$s->is_paid}, Count: {$s->count}\n";
    }
}

$staffWithEmails = DB::table('tblper')
    ->whereNotNull('email')
    ->where('email', '!=', '')
    ->select('ID', 'fileNo', 'surname', 'first_name', 'email')
    ->limit(5)
    ->get();
echo "\nStaff with Emails configured:\n";
foreach ($staffWithEmails as $st) {
    echo "  ID: {$st->ID}, FileNo: {$st->fileNo}, Name: {$st->surname} {$st->first_name}, Email: {$st->email}\n";
}
