<?php
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
require_once 'db.php';



//--------------------------------
function banco_integracao($id){
    global $db;

    $sql="select  cd_chat_integracao,
  id_chat,
  js_integracao,
  dt_registro
FROM integracao.tb_chat_integracao
WHERE integracao.tb_chat_integracao.id_chat = $id;";

    $result = $db->exec($sql);

    while ($row = pg_fetch_assoc($result)) {
            print_r($row);
    }




}

function banco_chatlist($id){
    global $db;

    $sql="SELECT
  messages.id,
  messages.id_chat,
  messages.id_user,
  messages.js_integracao AS js_integracao_messages,
  chat_list.js_integracao
FROM messages
 INNER JOIN chat_list ON messages.id_chat = chat_list.id
WHERE messages.id_chat = $id
ORDER BY messages.id_chat, messages.id";

    $result = $db->exec($sql);

    while ($row = pg_fetch_assoc($result)) {
            print_r($row);
    }

}


banco_integracao($argv[1]);
banco_chatlist($argv[1]);


?>
