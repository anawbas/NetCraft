<!DOCTYPE html>
<html>
<head>
    <title>Data Scraping</title>
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "netcraft";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT id, keywords FROM keyword";
$result = $conn->query($sql);
$number=0;
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) 
    {
        $results=scraping($row["keywords"]);

        for($i=1;$i<count($results);$i++)
        {
            
            $sql2 = "INSERT INTO search_domain (keyword_id,site_url) VALUES (".$row['id'].",".$results[$i].")";
            $number++;
            if ($conn->query($sql2) === TRUE) {

                echo "<br/>".$number."- Record Created Successfully";
            } else {
                echo "<br/>".$number."- Record Not Created Successfully";
                echo "Error: " . $sql2 . "<br>" . $conn->error;
            }
        }
    }
} else {
    echo "0 results";
}
$conn->close();       
/*$result=scraping('sitepoint');
print_r('<pre>');
print_r($result);
print_r('<pre>');
for($i=1;$i<count($result);$i++)
{
    echo '<br/>'.$result[$i];
}*/

function scraping($word)
 {
        include_once('simple_html_dom.php');
        $search_keyword=$word;
		$starting_url='https://searchdns.netcraft.com/?restriction=site+contains&host='.$search_keyword.'&lookup=wait..&position=limited';
        $html = file_get_html($starting_url);
        $t_result='';
        $total_sites_url=array();
        foreach($html->find('div.blogbody') as $blogbody) 
        {

            foreach($blogbody->find('p') as $blogbody2)
            {
                
                $t_result.=$blogbody2->plaintext;
                
            }
        }
        $total_results_found=filter_var($t_result,FILTER_SANITIZE_NUMBER_INT);
       // echo 'Total Results ='.$total_results_found.'<br/>';
        $lk=0;
        $nextpage='';
        foreach($html->find('div.blogbody') as $blogbody) 
        {

            foreach($blogbody->find('p') as $blogbody2)
            {
                if($lk==0)
                {

                }
                else{
                        foreach($blogbody2->find('a') as $blogbody3)
                        {
                            if($total_results_found<21)
                            {
                                $nextpage1= 'https://searchdns.netcraft.com'.$blogbody3->href;
                                $nextpage=str_replace('site contains', 'site%20contains',$nextpage1);
                            }
                        }
              
                }
                $lk++;
                
                
            }
        }
        //echo $nextpage;

        foreach($html->find('div.blogbody') as $blogbody) 
        {

            foreach($blogbody->find('p') as $blogbody2)
            {
                $t_result.=$blogbody2->plaintext;
                
            }
        }
        $numb=0;
        foreach($html->find('table.TBtable') as $element) 
        { // echo $element->find('tr',1)->plaintext; 
            foreach($element->find('tr') as $element2)
            {   
                if($numb==0)
                {
                   // echo $numb.'-'.$element2->find('td',1)->plaintext.'</br>';
                }
                else
                {   
                   // echo '<br/>';
                   // echo $numb.'-'.$element2->find('td',1)->plaintext;
                    $total_sites_url[$numb]='"'.$element2->find('td',1)->plaintext.'"';
                }
                $numb++;
                
                
            }
        }
        
        ///////////////////////////////////////////// NEXT Pages Results Algorith Start From Here/////////////////////////
        if($total_results_found<21)
        {
            $limit = 100000; 
            set_time_limit($limit);
            $y=0;
            $condition=$total_results_found-20;
            
            while($y<$condition)
            {

               // echo '</br>Con='.$condition."Caling Back=".$y;
                if(strlen($nextpage)>3)
                {
                $html = file_get_html($nextpage);
                $t_result='';
                foreach($html->find('div.blogbody') as $blogbody) 
                {

                    foreach($blogbody->find('p') as $blogbody2)
                    {
                        
                        $t_result.=$blogbody2->plaintext;
                        
                    }
                }
               // echo 'Total Results ='.filter_var($t_result,FILTER_SANITIZE_NUMBER_INT).'<br/>';
                

                
                $yy=0;
                foreach($html->find('table.TBtable') as $element) 
                { 
                    foreach($element->find('tr') as $element2)
                    {   
                        if($yy==0)
                        {
                            //echo $y.'-'.$element2->find('th',1)->plaintext.'</br>';
                        }
                        else
                        {   
                          //  echo '<br/>';
                          //  echo $numb.'-'.$element2->find('td',1)->plaintext;
                            $total_sites_url[$numb]='"'.$element2->find('td',1)->plaintext.'"';
                            $numb++;
                        }
                        
                        $yy++;
                        
                    }
                }

                $lk=0;
                $nextpage='';
                $for_break=0;
                foreach($html->find('div.blogbody') as $blogbody) 
                {

                    foreach($blogbody->find('p') as $blogbody2)
                    {
                        if($lk==0)
                        {

                        }
                        else{
                                foreach($blogbody2->find('a') as $blogbody3)
                                {   
                                    $y=$y+20;
                                    if($y+21<$total_results_found)
                                    {
                                        $nextpage1= 'https://searchdns.netcraft.com'.$blogbody3->href;
                                        $nextpage=str_replace('site contains', 'site%20contains',$nextpage1);
                                    }
                                    else{
                                        $for_break=1;
                                    }
                                }
                      
                        }
                        $lk++;
                        
                        
                    }
                }
               // echo $nextpage;
                if($for_break==1)
                {
                    break;
                }
                }
                else{
                    break;
                }

            }
        }

        
   return $total_sites_url;

 }

                
        
        
?>
</body>

</html>
       