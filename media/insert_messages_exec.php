#!/usr/bin/php -q
<?php
//versao 1.0
header('Content-Type: application/json');
include 'db.php';



function send_tcp($pacote) {
$ip="127.0.0.1";
$port=9999;
$address=$ip;
$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
$erro=1;
$result = socket_connect($sock, $address, $port);
if ($result === false) {
  $erro=1;
} else {
  $erro=0;
}
if ($erro==0)
{
 echo "Enviando TCP... $pacote \n";
 socket_set_option($sock,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>3, "usec"=>0));
 socket_write($sock,$pacote,strlen($pacote));
 socket_close($sock);
}

}


foreach($argv as $value){
  $data = $value;
}

$v = explode("|",$data);

//$chat_list_id=$v[0];
//$msg=$v[1];
//$file_type=$v[2];
//$type = $v[3];


$chat_list_id=$argv[1];
$msg=$argv[2];
$file_type=$argv[3];
$type = $argv[4];

$timestamp=time();



// \\\"%s\\\"

//$msg=str_replace("\\", "\\\\", $msg);
//$msg=str_replace('"', '\"', $msg);
//$msg=str_replace("'", "''", $msg);
//$msg=str_replace('&', '\&', $msg);
$msg=str_replace('\n', '', $msg);

$db->busyTimeout(15000);

$nums = implode('', range(0, 9)); // 0123456789

$alphaNumeric = $nums; // ABCDEF123456789
$string = '';
$len = 5; // numero de chars
for($i = 0; $i < $len; $i++) {
    $string .= $alphaNumeric[rand(0, strlen($alphaNumeric) - 1)];
}

$join_msg=$string; 
$join_msg=$timestamp.$join_msg;

$sql='select www from settings';
$results = $db->query($sql);
while ($row = pg_fetch_assoc($results)) {
   $www=$row['www'];
}

$sql="select channel,join_chat from chat_list where id=$chat_list_id";
$results = $db->query($sql);
while ($row = pg_fetch_assoc($results)) {
   $channel=$row['channel'];
   $join_chat=$row['join_chat'];

}
 

//-------------------------------------
function insertMsg($db, $sql){
        $sql = $sql." returning id;";
        print("SQL MSG: $sql\n");

        $id_msg = $db->exec($sql);

        while ($row = pg_fetch_assoc($id_msg)) {
                $id_msg=$row['id'];
        }

        print("\nRETORNO_ID_MSG:$id_msg\n");

}

//-------------------------------------
echo "channel:".$channel."\n";
echo "join_chat:".$join_chat."\n";


if ($channel==0){

 $sql="select id_chat_user,id_user,id_subject,cd_servico from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
   $id_subject=$row['id_subject'];
   $cd_servico=$row['cd_servico'];
 } 

 if ($id_subject=="")
              $id_subject=0;

 if ($cd_servico=="")
              $cd_servico=0;



 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   $photo=$row['photo'];
 }

 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo,id_user,id_subject,cd_servico ) values ($chat_list_id,$timestamp,'$msg',1,0,0,$channel,'$photo',$id_user,$id_subject,$cd_servico )";

 if ($file_type=="1"){
  //$msg=str_replace(" ", "_", $msg);   
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";

 }
 if ($file_type=="2"){
  // $msg=str_replace(" ", "_", $msg);
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";
 }
 
 insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',new_msg=1,refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);

 $tm_tcp=microtime(true);
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$id_user");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$id_user");


}

if ($channel==1){

 $sql="select id_chat_user,id_user from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
 }

 $sql="select id_user from chat_users where id=$id_chat_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_user_other=$row['id_user'];
 }

 $tm_tcp=microtime(true);
 $id_user_tcp=$id_user;
 $id_user_other_tcp=$id_user;


 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   $photo=$row['photo'];
 }
 

 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,join_msg,photo ) values ($chat_list_id,$timestamp,'$msg',1,0,0,$channel,$join_msg,'$photo' )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,join_msg,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,$join_msg,'$photo' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,join_msg,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,$join_msg,'$photo' )";

 insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);


 $sql="select id from chat_list where join_chat=$join_chat and id<>$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
    $id=$row['id'];

 }

 $sql="select id_chat_user,id_user from chat_list where id=$id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
 }

 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   //$photo=$row['photo'];
 }


 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,join_msg,photo ) values ($id,$timestamp,'$msg',0,0,0,$channel,$join_msg,'$photo' )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,join_msg,photo ) values ($id,$timestamp,'$www/suporte/media/send/$msg',0,0,$file_type,'$msg',$channel,$join_msg,'$photo' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,join_msg,photo ) values ($id,$timestamp,'$www/suporte/media/send/$msg',0,0,$file_type,'$msg',$channel,$join_msg,'$photo' )";
 
 insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',new_msg=1,refresh=1,refresh_msg=1 where id=$id";
 $db->exec($sql);

 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$id_user_tcp");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$id_user_other_tcp");

 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$id_user_tcp");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$id_user_other_tcp");


}


if ($channel==2){
if ($type==1)
 $type=0;
else
 $type=1;

if ($type==1){
 $sql="select id_chat_user,id_user,id_subject,cd_servico from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
   $id_subject=$row['id_subject'];
   $cd_servico=$row['cd_servico'];
 }

 if ($id_subject=="")
              $id_subject=0;

 if ($cd_servico=="")
              $cd_servico=0;


 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $name=$row['user'];
   $photo=$row['photo'];
 }

 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo,name,id_user,id_subject,cd_servico ) values ($chat_list_id,$timestamp,'$msg',$type,1,0,$channel,'$photo','$name',$id_user,$id_subject,$cd_servico )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo,name,id_user,id_subject ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',$type,0,$file_type,'$msg',$channel,'$photo','$name',$id_user,$id_subject )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo,name ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',$type,0,$file_type,'$msg',$channel,'$photo',$name )";

 insertMsg($db, $sql);


 $tm_tcp=microtime(true);
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$id_user");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$chat_list_id");

 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$id_user");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$chat_list_id");


 $sql="select join_chat from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $join_chat=$row['join_chat'];

 }  

 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',new_msg=1,refresh=1,refresh_msg=1 where join_chat=$join_chat";
 $db->exec($sql);
} 

if ($type==0){

 $sql="select id_user,id_chat_user,id_subject from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
   $id_subject=$row['id_subject'];

 }

 if ($id_subject=="")
              $id_subject=0;


 $sql="select name,photo from chat_users where id=$id_chat_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['name'];
 }

 $photo='/suporte/profile/user_with_out_img.jpg';

 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo,name,id_user,id_subject ) values ($chat_list_id,$timestamp,'$msg',$type,1,0,$channel,'/$photo','$user',$id_user,$id_subject )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo,name,id_user,id_subject ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',$type,0,$file_type,'$msg',$channel,'/$photo','$user',$id_user,$id_subject )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,name ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',$type,0,$file_type,'$msg',$channel,'$user' )";

 insertMsg($db, $sql);

 $tm_tcp=microtime(true);
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$id_user");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$chat_list_id");

 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$id_user");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$chat_list_id");

 $sql="select join_chat from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $join_chat=$row['join_chat'];

 }

 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',new_msg=1,refresh=1,refresh_msg=1 where join_chat=$join_chat";
 $db->exec($sql);
}


}

if ($channel==3){



 $sql="select id_chat_user,id_user from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
 }

 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   $photo=$row['photo'];
 }

 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,join_msg,photo ) values ($chat_list_id,$timestamp,'$msg',1,0,0,$channel,$join_msg,'$photo' )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,join_msg,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,$join_msg,'$photo' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,join_msg,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,$join_msg,'$photo' )";
        
        insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);

 $tm_tcp=microtime(true);
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$id_user");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$id_user");




 $sql="select id_user,id from chat_list where id_chat_user=$id_chat_user and id<>$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $chat_list_id=$row['id'];
   $id_user=$row['id_user'];
 
 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,name,photo,join_msg ) values ($chat_list_id,$timestamp,'$msg',0,0,0,$channel,'$user','$photo',$join_msg )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,name,photo,join_msg ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',0,0,$file_type,'$msg',$channel,'$user','$photo',$join_msg )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,name,photo,join_msg ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',0,0,$file_type,'$msg',$channel,'$user','$photo',$join_msg )";

 insertMsg($db, $sql);

 $tm_tcp=microtime(true);
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$id_user");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$id_user");


 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',new_msg=1,refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);

}

}

//e-mail
if ($channel>3){

 $sql="select id_chat_user,id_user,id_subject,cd_servico from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
   $id_subject=$row['id_subject'];
   $cd_servico=$row['cd_servico'];
 }

 if ($id_subject=="")
              $id_subject=0;

 if ($cd_servico=="")
              $cd_servico=0;

 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   $photo=$row['photo'];
 }


 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo,id_user,id_subject,cd_servico ) values ($chat_list_id,$timestamp,'$msg',1,0,0,$channel,'$photo',$id_user,$id_subject,$cd_servico )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo,id_user,id_subject ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo',$id_user,$id_subject )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";

 insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,new_msg=1,refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);


}


//twitter
if ($channel==55){

 $sql="select id_chat_user,id_user from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
 }

 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   $photo=$row['photo'];
 }


 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo ) values ($chat_list_id,$timestamp,'$msg',1,0,0,$channel,'$photo' )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";
 
 insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,new_msg=1,last_msg='$msg',refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);


}







//Facebook
if ($channel==66){

 $sql="select id_chat_user,id_user from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
 }

 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   $photo=$row['photo'];
 }


 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo ) values ($chat_list_id,$timestamp,'$msg',1,0,0,$channel,'$photo' )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";
 
 insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,new_msg=1,last_msg='$msg',refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);

 $tm_tcp=microtime(true);
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_tcp_$id_user");
 send_tcp("echo $tm_tcp > /dev/shm/suporte/tm_chat_tcp_$id_user");

}




//bot
if ($channel==77){

 $sql="select id_chat_user from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];

 }

if ($type==1){

 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo ) values ($chat_list_id,$timestamp,'$msg',$type,1,0,$channel,'/suporte/profile/user_with_out_img.jpg' )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',$type,0,$file_type,'$msg',$channel,'/amazonas/profile/user_with_out_img.jpg' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',$type,0,$file_type,'$msg',$channel,'/amazonas/profile/user_with_out_img.jpg' )";

 insertMsg($db, $sql);

 $sql="select join_chat from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $join_chat=$row['join_chat'];
 }
 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',new_msg=1,refresh=1,refresh_msg=1 where join_chat=$join_chat";
 $db->exec($sql);
}
if ($type==0){

 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo ) values ($chat_list_id,$timestamp,'$msg',$type,1,0,$channel,'/suporte/profile/bot.jpg')";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',$type,0,$file_type,'$msg',$channel,'/amazonas/profile/bot.jpg' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',$type,0,$file_type,'$msg',$channel,'/amazonas/profile/bot.jpg' )";
 
 insertMsg($db, $sql);

 $sql="select join_chat from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $join_chat=$row['join_chat'];

 }
 $sql="update chat_list set last_msg_timestamp=$timestamp,last_msg='$msg',new_msg=1,refresh=1,refresh_msg=1 where join_chat=$join_chat";
 $db->exec($sql);
}
}

//sms
if ($channel==88){

 $sql="select id_chat_user,id_user from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
 }

 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   $photo=$row['photo'];
 }


 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo ) values ($chat_list_id,$timestamp,'$msg',1,0,0,$channel,'$photo' )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";

 insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,new_msg=1,last_msg='$msg',refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);


}

//telegram
if ($channel==99){

 $sql="select id_chat_user,id_user from chat_list where id=$chat_list_id";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $id_chat_user=$row['id_chat_user'];
   $id_user=$row['id_user'];
 }

 $sql="select \"user\",photo from \"users\" where id=$id_user";
 $results = $db->query($sql);
 while ($row = pg_fetch_assoc($results)) {
   $user=$row['user'];
   $photo=$row['photo'];
 }


 if (($file_type=="0") || ($file_type==""))
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,channel,photo ) values ($chat_list_id,$timestamp,'$msg',1,0,0,$channel,'$photo' )";

 if ($file_type=="1")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";

 if ($file_type=="2")
        $sql="insert into messages (id_chat,timestamp,text,type,status,file_type,file_name,channel,photo ) values ($chat_list_id,$timestamp,'$www/suporte/media/send/$msg',1,0,$file_type,'$msg',$channel,'$photo' )";
 
 insertMsg($db, $sql);

 $sql="update chat_list set last_msg_timestamp=$timestamp,new_msg=1,last_msg='$msg',refresh=1,refresh_msg=1 where id=$chat_list_id";
 $db->exec($sql);


}

  shell_exec("curl $id->serverEventApi/events/send/$id_user --insecure");
  shell_exec("curl $db->serverEventApi/events/send-outside/$chat_list_id --insecure");
?>

