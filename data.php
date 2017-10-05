<?php
// Database details
$db_server   = 'localhost';
$db_username = 'root';
$db_password = 'rootlh';
$db_name     = 'company';

// Get job (and id)
$job = '';
$id  = '';
if (isset($_GET['job'])){
  $job = $_GET['job'];
  if ($job == 'get_companies' ||
      $job == 'get_company'   ||
      $job == 'add_company'   ||
      $job == 'edit_company'  ||
      $job == 'delete_company'){
    if (isset($_GET['id'])){
      $id = $_GET['id'];
      if (!is_numeric($id)){
        $id = '';
      }
    }
  } else {
    $job = '';
  }
}

// Prepare array
$mysql_data = array();

// Valid job found
if ($job != ''){

  // Connect to database
  
    //try {
        $dsn = "mysql:host=$db_server;dbname=$db_name;charset=utf8";
        $opt = [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false
        ];
        
        $pdo = new PDO($dsn, $db_username, $db_password, $opt);

    //} catch(PDOException $ex) {
    //    echo 'ERROR: ' . $ex->getMessage();
    //}
 
  // Execute job
  if ($job == 'get_companies'){

    // Get companies
      try{
          $stmt = $pdo->query("SELECT * FROM it_companies ORDER BY rank ");
          $result  = 'success';
          $message = 'query success';

          foreach ($stmt as $row)
          { 
              $functions  = '<div class="function_buttons"><ul>';
              $functions .= '<li class="function_edit"><a data-id="'   . $row['company_id'] . '" data-name="' . $row['company_name'] . '"><span>Edit</span></a></li>';
              $functions .= '<li class="function_delete"><a data-id="' . $row['company_id'] . '" data-name="' . $row['company_name'] . '"><span>Delete</span></a></li>';
              $functions .= '</ul></div>';
              //echo $row['rank'];
              $mysql_data[] = array(
                "rank"          => $row['rank'],
                "company_name"  => $row['company_name'],
                "industries"    => $row['industries'],
                "revenue"       => '$ ' . $row['revenue'],
                "fiscal_year"   => $row['fiscal_year'],
                "employees"     => number_format($row['employees'], 0, '.', ','),
                "market_cap"    => '$ ' . $row['market_cap'],
                "headquarters"  => $row['headquarters'],
                "functions"     => $functions
            );
          }
      }
      catch(PDOException $ex){
        $result  = 'error';
        $message = 'query error';
        //echo 'ERROR: ' . $ex->getMessage();
      }
   
  } elseif ($job == 'get_company'){

    // Get company
    if ($id == ''){
      $result  = 'error';
      $message = 'id missing';
    } else {
      
        $stmt = $pdo->prepare("SELECT * FROM it_companies WHERE company_id = ? ");
        $stmt->execute([$id]);
       
        $result  = 'success';
        $message = 'query success';
        foreach ($stmt as $row)
        { $mysql_data[] = array(
            "rank"          => $row['rank'],
            "company_name"  => $row['company_name'],
            "industries"    => $row['industries'],
            "revenue"       => $row['revenue'],
            "fiscal_year"   => $row['fiscal_year'],
            "employees"     => $row['employees'],
            "market_cap"    => $row['market_cap'],
            "headquarters"  => $row['headquarters']
           );
        }
     
    }

  } elseif ($job == 'add_company'){

    // Add company

    $stmt = $pdo->prepare("INSERT INTO it_companies (rank, company_name, industries, revenue, fiscal_year, employees, market_cap, headquarters) 
              VALUES (:rank, :company_name, :industries, :revenue, :fiscal_year, :employees, :market_cap, :headquarters)");
    
    $stmt->bindParam(':rank', $_GET['rank']);
    $stmt->bindParam(':company_name', $_GET['company_name']);
    $stmt->bindParam(':industries', $_GET['industries']);
    $stmt->bindParam(':revenue', $_GET['revenue']);
    $stmt->bindParam(':fiscal_year', $_GET['fiscal_year']);
    $stmt->bindParam(':employees', $_GET['employees']);
    $stmt->bindParam(':market_cap', $_GET['market_cap']);
    $stmt->bindParam(':headquarters', $_GET['headquarters']);
  
    $stmt->execute();
  
    $result  = 'success';
    $message = 'query success';

  } elseif ($job == 'edit_company'){

    // Edit company
    if ($id == ''){
      $result  = 'error';
      $message = 'id missing';
    } else {
      $stmt = $pdo->prepare("UPDATE it_companies SET rank = :rank, company_name = :company_name, industries = :industries
                                , revenue = :revenue, fiscal_year = :fiscal_year, employees = :employees, market_cap = :market_cap
                                , headquarters = :headquarters 
                                WHERE company_id = :id") ;
      $stmt->bindParam(':rank', $_GET['rank']);
      $stmt->bindParam(':company_name', $_GET['company_name']);
      $stmt->bindParam(':industries', $_GET['industries']);
      $stmt->bindParam(':revenue', $_GET['revenue']);
      $stmt->bindParam(':fiscal_year', $_GET['fiscal_year']);
      $stmt->bindParam(':employees', $_GET['employees']);
      $stmt->bindParam(':market_cap', $_GET['market_cap']);
      $stmt->bindParam(':headquarters', $_GET['headquarters']);
      $stmt->bindParam(':id', $id);
      $stmt->execute();
  
      $result  = 'success';
      $message = 'query success';


    }

  } elseif ($job == 'delete_company'){

    // Delete company
    if ($id == ''){
      $result  = 'error';
      $message = 'id missing';
    } else {
      $stmt = $pdo->prepare("DELETE FROM it_companies WHERE company_id = ?");
      $stmt->execute([$id]);
      $deleted = $stmt->rowCount();
      
      if($deleted > 0){
        $result  = 'success';
        $message = 'query success';
      }else{
        $result  = 'error';
        $message = 'bad DB call';
      }
      
  
    }

  }

  // Close database connection
  $pdo = null;

}

// Prepare data
$data = array(
  "result"  => $result,
  "message" => $message,
  "data"    => $mysql_data
);

// Convert PHP array to JSON array
$json_data = json_encode($data);
print $json_data;
?>
