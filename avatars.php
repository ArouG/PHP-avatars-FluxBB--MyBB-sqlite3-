<?php          
/**************************************************
* avatars.php - objectif = rajouter, à partir d'une base FluxBB vers une base myBB les avatars des membres
*      conditions préalables : les fichiers 'avatars' de la base FluxBB doivent préalablement avoir été chargés sous le répertoire ./uploads/avatars de la base MyBB
*      le préfixe de la base out.sqlite sera fourni en paramètre : ?p=<votre_prefix>
*      le nom de votre base de données MyBB devra remplacer celui du script en ligne 54 : <myBB_database.sqlite>
*      celle-ci, tout comme le script, sera uploadé au même endroit que le fichier index.php de votre forum.
*
* 1 - Sorry for my english spoken,
* 2 - The objective is to allow members of the new forum MyBB recover their avatars from the old database automatically after FluxBB Merge System
* 3 - Files of 'avatars' (base FluxBB) shall have already been charged under the ./uploads/avatars directory database directory MyBB
* 4 - the prefix of the tables of your database will be passed by parameter : ?p=<your_prefix>
* 5 - the name of your database MyBB will replace the script in line 54 : <myBB_database.sqlite>
* 6 - file database, as this script file, will be uploaded to the same location as the index.php file of your board
* Auteur : françois DANTGNY 25/09/2014
**************************************************/       
*             
    ignore_user_abort(TRUE);
    error_reporting(E_ERROR | E_WARNING | E_PARSE); 
    set_time_limit(0);    
    ini_set("memory_limit" , -1);   
     
    $pref='';                 
    if (isset($_GET['p'])) {
      $pref=$_GET['p'];
    }      
    echo 'prefixe = '.$pref."<br>";
    
    function listDir(){
        if ($rep = opendir('./uploads/avatars')){     
        $Res=array();
            while (false !== ($fichier = readdir($rep))){
                if ($fichier != "." && $fichier != ".."){    
                    $file='./uploads/avatars/'.$fichier;                            
                    $ex=explode('.',$fichier);
                    $ext=$ex[count($ex)-1];
                    $name=substr($fichier,0,strlen($fichier)-strlen($ext)-1);
                    
                    list($width, $height, $type, $attr) = getimagesize($file); 
                    
                    if (is_null($width)){   
                        $Res[count($Res)-1]=array($name,$fichier,0,0);
                    } else { 
                        $Res[count($Res)-1]=array($name,$fichier,$width,$height);
                    }
                }           
            }
            closedir($rep); 
        }
        return $Res;
    }
    
    $Dir=listDir();
    $db= NEW PDO('sqlite:myBB_database.sqlite');                            // <- name of YOUR database here 
    $SQLUpdate="UPDATE ".$pref."users SET avatar=?, avatardimensions=?, avatartype=? WHERE uid=?";
    $stmtUp=$db->prepare($SQLUpdate);
    if ($db){
        for ($i=0; $i<count($Dir); $i++){
            $IntVal=(int)$Dir[$i][0];
            $LitNam=$IntVal.'';
            echo 'nom_fichier='.$Dir[$i][0].', n='.$LitNam."<br>";
            if ($Dir[$i][0] == $LitNam){                                    // name of the file is a number ?
                $now=time();
                $par[0]='./uploads/avatars/'.$Dir[$i][1].'?dateline='.$now;  
                $par[1]=$Dir[$i][2].'|'.$Dir[$i][3];                        // 'width|heigth' <=> avatardimensions
                $par[2]='upload';
                $par[3]=$IntVal-1;                                          // because FluxBB creates a member called Guest which is not imported by Merge System
                //var_dump($par); echo "<br>"; 
                $stmtUp->execute($par);
            }
        }
        $db=null;
    } 
?>
