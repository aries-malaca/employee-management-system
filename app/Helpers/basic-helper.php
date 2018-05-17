<?php
    function createLabel($string){
        switch(strtolower($string)){
            case 'approved':
            case 'present': return '<span class="badge" style="background-color:#4CAF50;">'.ucfirst($string).'</span>';
                break;
            case 'absent': 
            case 'denied': return '<span class="badge" style="background-color:#F44336;">'.ucfirst($string).'</span>';
                break;
            case 'rest-day': return '<span class="badge" style="background-color:#00B0FF;">'.ucfirst($string).'</span>';
            case 'holiday': return '<span class="badge" style="background-color:#00B0FF;">'.ucfirst($string).'</span>'; 
                break;
            case 'overtime': return '<span class="badge" style="background-color:#9C27B0;">'.ucfirst($string).'</span>';
                break;
            case 'no-timeout': return '<span class="badge" style="background-color:#9E9E9E;">'.ucfirst($string).'</span>';
                break;
            case 'leave': return '<span class="badge" style="background-color:#DA9606;">'.ucfirst($string).'</span>';
                break;
            case 'emergency': return '<span class="badge" style="background-color:#550000;">'.ucfirst($string).'</span>';
                break;
            case 'travel': return '<span class="badge" style="background-color:#EC5E8D;">'.ucfirst($string).'</span>';
                break;
            case 'offset': return '<span class="badge" style="background-color:#002EFF;">'.ucfirst($string).'</span>';
                break;
        }
    }
    
    function timeNormal($string){
        if($string == '')
            return '';
            
        return date('h:i A', strtotime($string));
    }
    
    function dateNormal($string){
        if($string == '')
            return '';
            
        return date('m/d/Y', strtotime($string));
    }
    function datetimeNormal($string){
        if($string == '')
            return '';
            
        return date('m/d/Y h:i A', strtotime($string));
    }
    function dateNice($string){
        if($string == '')
            return '';
            
        return date('F d, Y', strtotime($string));
    }
    
    
    function timeShort($string){
        if($string == '')
            return '00:00';
        
        return date('H:i', strtotime($string));
    }
    
    function dateShort($string){
        return date('Y-m-d', strtotime($string));
    }

    function dateProgress($date1, $date2 ){
        $diff_all = strtotime($date2) - strtotime($date1);
        $diff_now = time() - strtotime($date1);

        if($diff_all==0){
            $diff_all = 1;
        }
        $total = ($diff_now/$diff_all) * 100;

        if($total>=100){
            return 100;
        }
        else{
            return number_format($total, 2);
        }
    }
?>