<script type="text/javascript">
   $(document).ready(function(){

    $("#select1").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv");
        
        $.get('/update-biodata?biodataid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks').empty();
        }
        else if(recordid==0)
        {
            $('#oks').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok').empty();
            
        }
        //$.each(data, function(index, obj){
        //$('#divs2').append( '<option value="'+obj.id+'">'+obj.divname+'</option>' );
        });
        
        })
        
    $("#select2").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv2");
        
        $.get('/update-education?educationid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok2').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks2').empty();
        }
        else if(recordid==0)
        {
            $('#oks2').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok2').empty();
            
        }
        
        });
        
        })
        
        //update birth details
        $("#select3").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv3");
        
        $.get('/update-birth?birthid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok3').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks3').empty();
        }
        else if(recordid==0)
        {
            $('#oks3').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok3').empty();
            
        }
        
        });
        
        })
        
        //update language details
        $("#select4").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv4");
        
        $.get('/update-language?langid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok4').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks4').empty();
        }
        else if(recordid==0)
        {
            $('#oks4').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok4').empty();
            
        }
        
        });
        
        })
       //update children details
        $("#select5").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv5");
        
        $.get('/update-children?childid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok5').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks5').empty();
        }
        else if(recordid==0)
        {
            $('#oks5').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok5').empty();
            
        }
        
        });
        
        })
        
        //update salary details
        $("#select6").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv6");
        
        $.get('/update-salary?salaryid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok6').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks6').empty();
        }
        else if(recordid==0)
        {
            $('#oks6').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok6').empty();
            
        }
        
      });
    })
    
    //update nok details
        $("#select7").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv7");
        
        $.get('/update-nok?nokid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok7').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks7').empty();
        }
        else if(recordid==0)
        {
            $('#oks7').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok7').empty();
            
        }
        
      });
    })

    //update wife details
        $("#select8").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv8");
        
        $.get('/update-wife?wifeid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok8').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks8').empty();
        }
        else if(recordid==0)
        {
            $('#oks8').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok8').empty();
            
        }
        
      });
    })
    
    //update publicservice details
        $("#select9").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv9");
        
        $.get('/update-publicservice?publicserviceid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok9').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks9').empty();
        }
        else if(recordid==0)
        {
            $('#oks9').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok9').empty();
            
        }
        
      });
    })
    
    //update censors details
        $("#select10").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv10");
        
        $.get('/update-censors?censorsid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok10').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks10').empty();
        }
        else if(recordid==0)
        {
            $('#oks10').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok10').empty();
            
        }
        
      });
    })
    
    //update gratuity details
        $("#select11").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv11");
        
        $.get('/update-gratuity?gratuityid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok11').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks11').empty();
        }
        else if(recordid==0)
        {
            $('#oks11').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok11').empty();
            
        }
        
      });
    })
    
    //update termination details
        $("#select12").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv12");
        
        $.get('/update-termination?terminationid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok12').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks12').empty();
        }
        else if(recordid==0)
        {
            $('#oks12').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok12').empty();
            
        }
        
      });
    })
    
    //update tour details
        $("#select13").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv13");
        
        $.get('/update-tour?tourid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok13').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks13').empty();
        }
        else if(recordid==0)
        {
            $('#oks13').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok13').empty();
            
        }
        
      });
    })
    
    //update service details
        $("#select14").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv14");
        
        $.get('/update-service?serviceid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok14').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks14').empty();
        }
        else if(recordid==0)
        {
            $('#oks14').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok14').empty();
            
        }
        
      });
    })
    
    //update emolument details
        $("#select15").change(function(e){
    
        var recordid = e.target.value;
        //alert(recordid);
        
        var x = document.getElementById("hidediv15");
        
        $.get('/update-emolument?emolumentid='+recordid, function(data){
        //$('#divs2').empty();
        //console.log(data);
        if(recordid==1)
        {
            $('#ok15').append( '<i class="glyphicon glyphicon-ok" style="color:green" ></i>' );
            x.style.display = "none";
            $('#oks15').empty();
        }
        else if(recordid==0)
        {
            $('#oks15').append( '<i class="glyphicon glyphicon-remove" ></i>' );
            x.style.display = "none";
            $('#ok15').empty();
            
        }
        
      });
    })
        
        
 });
</script>