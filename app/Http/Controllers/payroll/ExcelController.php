<?php
namespace App\Http\Controllers\payroll;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use File;
use Session;
use DB;
use PHPMailerAutoload; 
use PHPMailer;
use Mail;

class ExcelController extends Controller
{
    
    public function export()
    {
         $data['courts'] = DB::table('tbl_court')->get();
         return view('excel/export',$data);
    }

    public function create()
    {
       return view('excel.excelupload');
    }

    public function store(Request $request)
    {
        
      $filename = $request->file('upload');       
      if($request->hasFile('upload'))
      {

        $file = fopen($filename, "r");
        //$i = 0;
        $affected_records = 0;
        $headers = fgetcsv($file, 10000, ",");
         while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE)
         {
            $string = $emapData[0];
            $arr = explode("/", $string, 2);
  
            $checkuser = DB::table('users')->where('fileNo', '=', $emapData[0])->count();
            $check = DB::table('tblper')->where('fileNo', '=', $emapData[0])->count();
            $getCourt   = DB::table('tbl_court')->where('id', '=', $emapData[1])->get();
            $court   = DB::table('tbl_court')->where('id', '=', $emapData[1])->first();
            $ifDuplicate = DB::table('tblper')->where('fileNo', '=', $emapData[0])->get();
            //dd($ifDuplicate);

            $clent=4;
           
            //$row = DB::table('users')->where('courtID','=',$emapData[1])->orderBy('id', 'desc')->first();
            
           /* if($row){
            $data = $row->username;
            $intc=strlen($data);
            $data=substr($data, $clent, ($intc-$clent));
            $count=$data+1;}
            else{$count=1;}
            $tempdata=$arr[0];
            $newcode=$tempdata.$count;
            if(strlen($tempdata) ==3)
            {
                $totalLength = 9;
            }
            elseif(strlen($tempdata) ==4)
            {
                $totalLength = 10;
            }

            elseif(strlen($tempdata) ==5)
            {
                $totalLength = 11;
            }

            elseif(strlen($tempdata) ==2)
            {
                $totalLength = 8;
            */

            /*while(strlen($newcode)<$totalLength)
            {$tempdata=$tempdata."0";
            $newcode=$tempdata.$count;}*/
            //echo $newcode;
        //dd($court->file_abbr );

           //dd( $emapData[14]);
            if($checkuser >=1)
            {
              $lastid = "";
            }
            else
            {
             if($court->file_abbr == '')
            {
                $fileNo = "$court->courtAbbr/$emapData[0]";
            }
            else
            {
                 $fileNo = "$court->courtAbbr/$court->file_abbr/$emapData[0]";
            }
            
            $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') , 0 , 6 );
            $p = bcrypt($pass);
            //if($checkuser ==0){
            $lastid = DB::table('users')->insertGetId(array( 
                'fileNo'              => $fileNo,
                'name'                => $emapData[1].' '.$emapData[2],
                'email'               => $emapData[14],
                'username'            => $fileNo,
                'password'            => $p, 
                'temp_pass'           => $pass,
                'courtID'             => $emapData[1],
                //'first_login'         => 0,
                'user_type'           => 'NONTECHNICAL',
                ));

               
             //}
        }
        if($lastid)
        {

            /*foreach($getCourt as $d )
            {
                if($d->court_name === $emapData[9])
                {
                    $id = $d->id ;

                }*/
   //dd($id);
            if($check >=1)
            {
                /*$recordSaved = DB::table('tblper')->where('fileNo', '=', $emapData[0])->update(array( 
                'fileNo'              => $emapData[0],
                'surname'             => $emapData[1],
                'first_name'          => $emapData[2],
                'othernames'          => $emapData[3],
                'OfficialPhoneNo'     => $emapData[4],
                'OfficialEmail'       => $emapData[5],
                'departmentID'        => $id,
                'userID'              => 1,
                //'court'               => $emapData[7],
                ));*/

            foreach ($ifDuplicate as $value) {
               if($value->fileNo == $emapData[0])
               {
                 $recordSaved = DB::table('existing_record')->insert(array( 
                'fileNo'              => $fileNo,
                'surname'             => $emapData[10],
                'first_name'          => $emapData[11],
                'phone'               => $emapData[13],
                'courtID'             => $row->courtID,
                'created_at'          => date('Y-m-d'),
                
                ));
               }
             }

              //$affected_records .= $emapData[0];
                 
            }      
            else
            {
              $recordSaved = DB::table('tblper')->insert(array( 
                'fileNo'                    => $fileNo,
                'courtID'                   => $emapData[1],
                'divisionID'                => $emapData[2],
                'employee_type'             => $emapData[3],
                'grade'                     => $emapData[4],
                'step'                      => $emapData[5],
                'department'                => $emapData[6],
                'appointment_date'          => $emapData[7],
                'date_present_appointment'    => $emapData[8],
                'dob'                       => $emapData[9],
                 'surname'                  => $emapData[10],
                'first_name'                => $emapData[11],
                'othernames'                => $emapData[12],
                'phone'                     => $emapData[13], 
                'email'                     => $emapData[14],
                'staff_status'              => $emapData[15], 
                'userID'                    => $lastid,
                'created_at'           => date('Y-m-d'),
                'updated_at'           => date('Y-m-d'),
                ));


                DB::table('assign_user_role')->insert(array( 
                'userID'               => $lastid,
                'roleID'               => 2,
                'created_at'           => date('Y-m-d'),
                ));

            }
              }
        }
           
         //}
         fclose($file);
         return redirect('/profile/upload')->with('msg', 'Records successfully uploaded');
     }
    else
    {
       return redirect('/profile/upload')->with('err', 'Error Uploading');
    }
    }

   
    public function ExportLoginDetails(Request $request)
    {
       $this->validate($request, [
        'court'            => 'required|numeric',
        ]);
        $court             = trim($request['court']);
        //dd($court);
        $results = DB::table('users')
        ->join('tblper','tblper.userID','=','users.id')
        ->where('tblper.courtID','=',$court)
        ->selectRaw('users.fileNo, users.name, users.username, users.temp_pass')
        ->get();
       //dd($results);

        $filename = "loginDetails.csv";
        
        $fp = fopen($filename,"w");
        $seperator = "";
        $comma = "";

        $seperator .= 'FileNo, Name, Username, Password';
        $seperator .= "\n";
        fputs($fp,$seperator);

        if($results != "")
        {
        $seperator = "";
        $comma = "";

        foreach ($results as $val){
            //$name = str_replace( ',', '', $val->name );
        $value1 = array($val->fileNo, $val->name, $val->username, $val->temp_pass);
        $value = implode(",",$value1);
        //$seperator .= $comma . '' .str_replace("", '""', $value);
        $seperator .= $value."\n";

        $comma = ",";
        }

        $seperator .= "\n";
        fputs($fp, $seperator);
        }

        fclose($fp);


        header("Content-type: application/csv");
        header("Content-Disposition: attachment; filename=$filename");
        readfile($filename);
    }

     public function mail()
     {
         $data['courts'] = DB::table('tbl_court')->get();
        return view('excel.sendMail',$data);
     }
    public function sendMail(Request $request)
    {
        /*$courtID = $request['court'];
        $mails = DB::table('tblper')
        ->join('users','users.userID','=','tblper.iserID')
        ->where('tblper.courtID', '=', $courtID)
        ->get();*/

        /*foreach($mails as $list)
        {

        $data = [
                 'name'          => $list->FirstName." ".$list->LastName,
                 'username'          => $list->username,
                 'password '         => $list->temp_pass 
                ];*/

        //$data = ['name'=>'Salvation'];


         $username='save';
        $to = 'aknice4u@gmail.com';
        $subject="Password Reset";
        $from ="Salvation";
        $header = "From:".$from."\r\n";
        $header .= "MIME-Version: 1.0 \r\n";
        $header .= "Content-type: text/html \r\n";
        $message="Dear Person, <br> Your password have been reset by admin name. <br> your login details is as follow: <br> User Name: save <br> Password: pass <br> Kindly Change your password after login";
        //$retval = mail ($this->senderemail(),$to,$subject,$message,$header);  
        $retval = mail ($to,$subject,$message,$header); 


                return redirect('/profile/mail')->with('msg','Email successfully Sent');
        
        
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
