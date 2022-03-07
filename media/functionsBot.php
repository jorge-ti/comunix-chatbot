<?php
//version: 1.3
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
include_once dirname(__FILE__).'/db.php';

//--------------------------------
function botGeraLog($filename,$msg){
    //Gera log 
    
    global $id;
    $name_function = __FUNCTION__;

    $dia = date('Y-m-d');
    $hora = date('H:i:s');
    
    $log_dir = "/var/log/chatbot_log/";
    $file_log = "$log_dir/$dia/$filename";
    $log_content = "LOG=$dia-$hora;ID_CHAT:$id;$msg\n";
    
    shell_exec("mkdir -p $log_dir/$dia");

    if(is_dir("$log_dir/$dia")){

        $arquivo = fopen($file_log,"a+");

        if (flock($arquivo, LOCK_EX)) {
            fwrite($arquivo, $log_content);
            flock($arquivo, LOCK_UN);

        }else{
            echo "LOG $name_function: Arquivo nao foi travado! \n";
        }

        fclose($arquivo);

    }else{
        echo "LOG $name_function: Falha ao criar o diretório de log! \n";
    }

}

//--------------------------------
function botLog($log_dir, $filename, $msg){
    //Gera log 
    
    global $id;
    $name_function = __FUNCTION__;

    $dia = date('Y-m-d');
    $hora = date('H:i:s');
    
    $file_log = "$log_dir/$dia/$filename";
    $log_content = "LOG=$dia-$hora;ID_CHAT:$id;$msg\n";
    
    shell_exec("mkdir -p $log_dir/$dia");

    if(is_dir("$log_dir/$dia")){

        $arquivo = fopen($file_log,"a+");

        if (flock($arquivo, LOCK_EX)) {
            fwrite($arquivo, $log_content);
            flock($arquivo, LOCK_UN);

        }else{
            echo "LOG $name_function: Arquivo nao foi travado! \n";
        }

        fclose($arquivo);

    }else{
        echo "LOG $name_function: Falha ao criar o diretório de log! \n";
    }

}

//--------------------------------
function botGeraProtocolo(){
    //Gera um protocolo de atendimento

    global $id;
    $name_function = __FUNCTION__;

    $dia = date('Ymd');
    $protocolo = $dia.$id;

    echo "LOG $name_function: PROTOCOLO -> $protocolo \n";
    return $protocolo;

}


//--------------------------------
function removeAcento($msg){
    //Remove acentos e caracteres especiais de uma string

    $comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú');
    $semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U');
    $texto = str_replace($comAcentos, $semAcentos, $msg);

    return $texto;

}


//--------------------------------
function unidecode($msg){
    //Remove acentos e caracteres e deixa tudo minusculo

    $msg = removeAcento($msg);
    $msg = strtolower($msg);

    return $msg;

}



//--------------------------------
function onlyNumber($str){
    //Retorna apenas numeros de uma string

    return preg_replace("/[^0-9]/", "", $str);

}


//--------------------------------
function getInsertFile(){
    //Captura o local do insert_messages_exec.php

    $diretorio = dirname(__FILE__);
    $pos = strpos($diretorio,"/bot");

    if ($pos === false){
        $arquivo_insert_msg = "$diretorio/./insert_messages_exec.php";
    }else{
        $arquivo_insert_msg = "$diretorio/.././insert_messages_exec.php";
    }

    return $arquivo_insert_msg;

}

    
//--------------------------------
function getFinishFile(){
    //Captura o local do finish_chat_end_exec.php

    $diretorio = dirname(__FILE__);
    $pos = strpos($diretorio,"/bot");

    if ($pos === false){
        $arquivo_insert_msg = "$diretorio/./finish_chat_end_exec.php";
    }else{
        $arquivo_insert_msg = "$diretorio/.././finish_chat_end_exec.php";
    }

    return $arquivo_insert_msg;

}


//--------------------------------
function getPSQLFile(){
    //Captura o local do PSQL

    $diretorio = dirname(__FILE__);
    $pos = strpos($diretorio,"/bot");

    if ($pos === false){
        $arquivo_insert_msg = "$diretorio/./psql_exec";
        $dir_psql = ".";
    }else{
        $arquivo_insert_msg = "$diretorio/.././psql_exec";
        $dir_psql = "..";
    }

    return array($arquivo_insert_msg, $dir_psql);

}


//--------------------------------
function getURLChat(){
    $url_chat = $db->keyFile;
    $url_chat = explode("/",$url_chat);
    $url_chat = end($url_chat);
    $url_chat = str_replace(".key","",$url_chat);

    return $url_chat;

}


//--------------------------------
function getChannel(){
    $channel = "";
    foreach (debug_backtrace() as &$valor) {
        $back_file = $valor['file'];

        if (strpos($back_file,"bot_thread_chat") !== false){
            $channel = 2;
            break;
        }elseif(strpos($back_file,"bot_thread_whats") !== false){
            $channel = 0;
            break;
        }elseif(strpos($back_file,"bot_thread_sms") !== false){
            $channel = 8;
            break;
        }elseif(strpos($back_file,"bot_thread_email") !== false){
            $channel = 4;
            break;
        }elseif(strpos($back_file,"bot_thread_telegram") !== false){
            $channel = 9;
            break;
        }elseif(strpos($back_file,"bot_thread_facebook") !== false){
            $channel = 6;
            break;
        }
    }

    return $channel;   
    
}


//--------------------------------
function botFaq($dic_sophia, $duvida, $assunto=""){
    //Utiliza sophia para retorno sobre um dicionario
    //@param string  $duvida -> Texto que sera enviado para o dicionario da Sophia
    //@param string  $dic_sophia -> Caminho completo do dicionario (Arquivo ".ai") 
    //@param string  $assunto -> Caso tenha relacionamento com assuntos
    
    global $ret_botfaq_dic;
    global $ret_botfaq_duvida;
    global $ret_botfaq_assunto;
    global $ret_botfaq_palavra;
    global $ret_botfaq_score;
    global $ret_botfaq_retorno;
    
    $name_function = __FUNCTION__;

    $sophia_bin = "/home/extend/sophia/./sophia";
    $duvida = removeAcento($duvida);
    $resp=shell_exec("$sophia_bin $dic_sophia '$duvida'");
    $arrResp = explode("\n",$resp);
    $elementos = count($arrResp)-1;

    $retornoSohia = explode(';',$arrResp[$elementos]);
    $retSophia = $retornoSohia[0];
    $score = $retornoSohia[1];
    if($score < 100){
        $retSophia = 0;
    }
 
    $list_score = [];
    foreach ($arrResp as &$valor) {
        $arr_1 = explode(":",$valor);
        $score_temp = $arr_1[2];
        $number  = explode(",",$score_temp)[0];
        if( intval($number) ){
            $list_score[$number] = $arr_1[1];
        }
    }

    $chave_maior = max( array_keys($list_score));
    $chave_result = $list_score[$chave_maior];
    $palavra = explode(",",$chave_result)[0];


    $ret_b = explode(",",$arrResp[2]);
    $ret_botfaq_dic = trim($dic_sophia);
    $ret_botfaq_duvida = trim($duvida);
    $ret_botfaq_assunto = trim($assunto);
    $ret_botfaq_score = trim($score);
    $ret_botfaq_palavra = trim($palavra);
    $ret_botfaq_retorno = trim($retSophia);


    $log = "LOG $name_function: DIC:$dic_sophia -- DUVIDA:$duvida -- RETORNO:$retSophia";
    $cmd = "LOG $name_function: CMD: $sophia_bin $dic_sophia '$duvida'";
    echo "$log \n";
    echo "$cmd \n";


    if("$retSophia" == "0"){
        $retSophia = "botFac_0";
        $ret_botfaq_retorno = "botFac_0";
    }
    
    botUpdateMsgSophia();

    return $retSophia;

}


//------------------------------------------------------------------
//--------------------------------
function botStartChat(){
    //Inicia atendimento

    global $id;
    global $db;
    global $protocolo_bot;
    global $protocolo_js;
    $name_function = __FUNCTION__;

    $ano = date("Ymd");
    $protocolo_bot = $ano.$id;
    $protocolo_js = "{ \"protocolo\": \"$protocolo_bot\"}";

    botVerificaHorarioChat();

    echo "LOG $name_function: $sql \n";

    return;

}


//--------------------------------
function botFinishChat(){
    //Finaliza atendimento
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;
    
    $arquivo_finish_chat = getFinishFile();

    sleep(3);
    $cmd="$arquivo_finish_chat '$id'";
    system($cmd);
    usleep(1000);
    
    echo "LOG $name_function: $cmd \n";
    exit;

}


//--------------------------------
function botTransfer($cd_servico=0, $cifra_servico=0){
    //Transfere para atendimento humano
    //Envia para servico caso $cd_servico seja informado
    //Verifica formulario caso $cifra_servico seja informado
    //@param integer $cd_servico -> Codigo do banco 
    //@param integer $cifra_servico -> Cifra do servicco

    global $id;
    global $db;
    $name_function = __FUNCTION__;
    
    $channel = getChannel();

    if($cifra_servico !=0){
        botVerificaHorarioFormulario($channel,$cifra_servico);
    }

    $now=time();

    if($cd_servico != 0){ 
        sleep(1);
        $sql = "update chat_list set channel=$channel, id_user=NULL, cd_servico=$cd_servico, start_timestamp=$now where id=$id";
        $db->exec($sql);

        echo "LOG $name_function: $sql \n";

    }else{
        sleep(1);
        $sql = "update chat_list set channel=$channel, id_user=NULL, start_timestamp=$now where id=$id";
        $db->exec($sql);
        
        echo "LOG $name_function: $sql \n";

    }

    botFinishChat();
    return;

}


//--------------------------------
function botInsertMsg($msg){
    //Insere mensagem no bot
    //@param string $msg -> Mensagem que sera enviada

    global $id;
    global $db;
    global $id_msg_bot;
    $name_function = __FUNCTION__;

    $arquivo_insert_msg = getInsertFile();

    $back_file = debug_backtrace()[0]['file'];
    $tipo = "";

    if (strpos($back_file,"bot_thread_chat") !== false){
        $tipo = "bot";
    }elseif(strpos($back_file,"bot_thread_whats") !== false){
        $tipo = "wpp";
    }

    if($tipo == 'bot'){
        $msg=str_replace("¨¨", "¨&nbsp;", $msg);
    }elseif($tipo == 'wpp'){
        $msg=str_replace("¨¨", "¨¨", $msg);
    }

    $msg = str_replace("\n", " ",$msg);
    $cmd = "$arquivo_insert_msg '$id' '$msg' | grep RETORNO_ID_MSG | cut -d':' -f2";

    $id_msg_bot = exec($cmd);
    $id_msg_bot = trim($id_msg_bot);
    sleep(1);
    
    echo "LOG $name_function: $cmd \n";
    return;

}


//--------------------------------
function botInsertMedia($msg,$caption=""){
    //Envia midia
    //@param string $msg -> Nome do arquivo
    //local dos arquivos: /var/www/nome_cliente/bot/media/send/
    //Passar apenas o nome do arquivo na funcao: botInsertMsg($id,"arquivo.mp4")
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $arquivo_insert_msg = getInsertFile();
    $msg = str_replace(' ','\ ',$msg);
    $cmd = "$arquivo_insert_msg '$id' '$msg' '1' '$caption'";

    system($cmd);   
    sleep(1);
    
    echo "LOG $name_function: $cmd \n";
    return;
}


//--------------------------------
function botInsertPassword($msg){
    //Adiciona menu de opcoes. 
    //@param msg $msg -> Mensagem que será enviada ao usuario travando o Bubble no formato de senha
    //no caso de chatbot enviar um botInsertMsg("Ö!Ö"); para destravar o Bubble

    global $id;
    global $db;
    $name_function = __FUNCTION__;
    
    $back_file = debug_backtrace()[0]['file'];
    $tipo = "";

    if (strpos($back_file,"bot_thread_chat") !== false){
        $tipo = "bot";
    }elseif(strpos($back_file,"bot_thread_whats") !== false){
        $tipo = "wpp";
    }

    if($tipo == "bot"){
        $msg = "!!BOTOPT!! $msg ÖpasswordÖ";
    }elseif($tipo == "wpp"){
        $msg = $msg;
    }
    
    $arquivo_insert_msg = getInsertFile();
    $cmd = "$arquivo_insert_msg '$id' '$msg'";

    system($cmd);
    sleep(1);
    
    echo "LOG $name_function: $cmd \n";
    return;
   
}


//--------------------------------
function botInsertButtom($btn,$wpp_prefix=1){
    //Adiciona menu de opcoes. 
    //@param array $btn -> Array com as opcoes. O primeiro é sempre o titulo (podendo passar vazio)
    //@param integer -> Tira formatacao inicial do wpp
    //$btn = array("Que bom te ter por aqui! Escolha uma das opções abaixo:", 
    //             "1 - Suporte técnico", 
    //             "2 - Financeiro");

    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $back_file = debug_backtrace()[0]['file'];
    $tipo = "";

    if (strpos($back_file,"bot_thread_chat") !== false){
        $tipo = "bot";
    }elseif(strpos($back_file,"bot_thread_whats") !== false){
        $tipo = "wpp";
    }

    $menu = "";
    $brk = "";
    $brk_1 = "*";
    $brk_2 = " ¨";
    foreach ($btn as $i => $value) {
        if ( $i == 0 ){
            if($tipo == "wpp"){
                if($value != "" && $value != " "){
                    $value=str_replace($value, $brk_1.$value.$brk_1, $value);
                    $menu = $menu.$value.$brk_2;
                }

            }elseif($tipo == "bot"){
                if($value == ""){
                    $value = " ";
                }
                $value=str_replace("¨¨", "¨&nbsp; ", $value);
                $value=str_replace($value, "!!BOTOPT!! $value", $value);
                $menu = $menu.$value.$brk;
            }

        }else{
            if($tipo == "wpp"){
                if( preg_replace("/[^0-9]/", "", $value[0]) ){
                    $value_n = str_replace($value[0], $brk_1.$value[0].$brk_1, $value[0]);
                    $value = str_replace("$value[0] - ", "$value_n - ", $value);
                }else{
                    if($wpp_prefix == 0 ){
                    
                    }else{
                        $value = str_replace($value, $brk_1.$i.$brk_1." - ".$value, $value);
                    }
                }
                $menu = $menu.$value.$brk_2;

            }elseif($tipo == "bot"){
                $bot_split = "Ü";
                $value=str_replace($value, $bot_split.$value.$bot_split, $value);
                $menu = $menu.$value.$brk;
            }
        }

    }

    $arquivo_insert_msg = getInsertFile();
    $cmd="$arquivo_insert_msg '$id' '$menu'";

    system($cmd);
    sleep(1);
    
    echo "LOG $name_function: $cmd \n";
    return;

}


//--------------------------------
function botGetFull($repeticao_max=3, $timeout_rep=60, $repeticao_msg_rep="", $repeticao_msg_fim=""){
    //Caputura mensagem do usuario
    //@param integer ou array $timeout -> Tempo total de espera
    //@param integer $repeticao_max -> Numero maximo de repeticoes da mensagem de timeout
    //@param string ou array  $repeticao_msg_rep -> Mensagem a cada repeticao
    //@param string  $repeticao_msg_fim -> Mensagem ao final das repeticoes. Quando chegar ao numero do parametro $repeticao_max

    global $id;
    global $db;
    global $id_msg;

    global $bot_ura;
    global $bot_menu;
    global $bot_opcao;
    global $cti;

    $name_function = __FUNCTION__;
    
    $repeticao = 1;
    
    $url_chat = $db->keyFile;
    $url_chat = explode("/",$url_chat);
    $url_chat = end($url_chat);
    $url_chat = str_replace(".key","",$url_chat);

    $wpp_transcript_url = "$url_chat";
    $wpp_transcript_file_stt = "/home/extend/scripts/google_stt.py";

    
    if($repeticao_msg_rep == "" || $repeticao_msg_rep == Null ){
        $repeticao_msg_rep = "Oi você ainda está por ai? Por favor escolha uma das opções";
    }
    if($repeticao_msg_fim == "" || $repeticao_msg_fim == Null){
        $repeticao_msg_fim = "Agradecemos seu contato!";
    }


    start_get_full:
    
    if (is_array($timeout_rep)){
        $timeout = $timeout_rep[$repeticao -1];
    }else{
        $timeout = $timeout_rep;
    }

    $start = time();
    $text = "";
    $sql = "update messages set sent=1 where type=0 and id_chat=$id";
    $db->exec($sql);
    echo "LOG $name_function Update: $sql \n";

    while (1){
        $sql="select text,id from messages where id_chat=$id and sent=0 and type=0 order by id desc limit 1";
        $results = $db->query($sql);
        while ($row = pg_fetch_assoc($results)) {
            $text=$row['text'];
            $id_msg=$row['id'];
        }

        if ($text!=""){
            $sql="update messages set sent=1 where type=0 and id_chat=$id";
            $db->exec($sql);
			
			if (strpos($text, $wpp_transcript_url) !== false) {
				$separaLink = explode('/',$text);
				$arquivoWav = $separaLink[5];
				$separaWav = explode('.',$arquivoWav);
				$arquivoOgg = $separaWav[0].'.ogg';
                $local_arquivo = dirname(__FILE__)."/../media/";
				system("mv $local_arquivo/$arquivoWav $local_arquivo/$arquivoOgg");
				system("ffmpeg -i $local_arquivo/$arquivoOgg $local_arquivo/$arquivoWav");
                $cmd = "$wpp_transcript_file_stt \"$local_arquivo/$arquivoWav\"";

                echo "LOG $name_function: $cmd \n";
				$trans = system($cmd);

                system("rm $local_arquivo/$arquivoOgg");
                system("rm $local_arquivo/$arquivoWav");

				echo "LOG $name_function: ARQUIVO WAV TRANSCRICAO -->  $trans\n";
				$text = $trans;
			}
			
		    echo "LOG $name_function TEXTO: $text \n";
            $text = trim($text);

            return $text;
        }

        if ((time()-$start)>$timeout){
            botUpdateMsgBot($bot_ura,$bot_menu,"Timeout",$cti);

            if($repeticao == $repeticao_max){
                botInsertMsg($repeticao_msg_fim);
                botFinishChat();
            }else{
                if (is_array($repeticao_msg_rep)){
                    botInsertMsg($repeticao_msg_rep[$repeticao -1]);
                    $repeticao += 1;
                    goto start_get_full;    
                }else{
                    botInsertMsg($repeticao_msg_rep);
                    $repeticao += 1;
                    goto start_get_full;
                }
            }
            return '';
        }
        sleep(1);
    }

}


//--------------------------------
function botGetFirstWord(){
    //Captura a primeira mensagem enviada pelo usuario
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $sql="SELECT messages.text FROM messages WHERE messages.id_chat = $id ORDER BY messages.id LIMIT 1;";
    $results = $db->query($sql);
    while ($row = pg_fetch_assoc($results)) {
        $first_text=$row['text'];
    }


    $first_text = trim($first_text);
    echo "LOG $name_function: $first_text \n";

    return $first_text;

}


//--------------------------------
function botGetTelefone(){
    //Caputura telefone do cliente
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;
    
    $psql = getPSQLFile();
    $arquivo_psql = $psql[0]; 
    $dir_psql = $psql[1];

    $cmd = "$arquivo_psql $dir_psql 'select number from chat_users,chat_list where chat_users.id=chat_list.id_chat_user and chat_list.id=$id'";
    $telefone = system($cmd);

    if($telefone == "" || $telefone == Null){
        $telefone = 0;
    }else{
        $telefone = substr($telefone, 2);
    }

    echo "$cmd \n";
    echo "LOG $name_function: $telefone \n";

    return $telefone;

}


//--------------------------------
function botGetPosicaoFila(){
    //Captura posicao da fila
    
    $name_function = __FUNCTION__;
    
    $channel = getChannel();

    $psql = getPSQLFile();
    $arquivo_psql = $psql[0]; 
    $dir_psql = $psql[1];

    $cmd = "$arquivo_psql $dir_psql 'select count(*) from chat_list where end_timestamp is null and id_user is null and channel=$channel'";
    $totalAtendendo = system($cmd);
    $totalFila = onlyNumber(intval($totalAtendendo));
    
    echo "LOG $name_function: CMD:$cmd  --  RESULT:$totalFila \n";

    return $totalFila;
    
}


//--------------------------------
function botGetConversaInteira(){
    //Captura a conversa interia dividia por "|--"
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $msg = "";
    $sql="select chat_list.join_chat, messages.join_msg, messages.id as msg_id, messages.text as msg, messages.timestamp, messages.type, messages.channel, messages.photo, messages.name, messages.status, chat_list.refresh, chat_list.end_timestamp from  messages, chat_list, chat_users where  messages.id_chat=chat_list.id and chat_list.id_chat_user=chat_users.id and chat_list.id=$id order by messages.timestamp;";

    $results = $db->query($sql);
    while ($row = pg_fetch_assoc($results)) {
        $msg = $msg.$row['msg']."|--";
    }
    
    echo "LOG $name_function: SQL:$sql \n";

    return $msg;
    
}


//--------------------------------
function botGetRank($bot_ura, $bot_menu){
    //Retorna o assunto mais acessado quando utilizado pela Sophia
    //@param string  $bot_ura -> Nome da "URA"
    //@param string  $bot_menu -> Nome da "MENU"
        

    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $ds_assunto = "";

    $sql = "SELECT 
             tm_top_acesso.ds_bot_ura
             ,tm_top_acesso.ds_bot_menu
             ,tm_top_acesso.ds_assunto
             ,tm_top_acesso.nu_acesso
            FROM(
             SELECT 
              messages.ds_bot_ura
              ,messages.ds_bot_menu
              ,messages.js_nlp->>'ds_assunto' AS ds_assunto
              ,COUNT(0) AS nu_acesso
             FROM messages
             GROUP BY 1,2,3
            ) AS tm_top_acesso
            WHERE tm_top_acesso.ds_bot_ura = '$bot_ura'
             AND tm_top_acesso.ds_bot_menu = '$bot_menu'
            ORDER BY tm_top_acesso.nu_acesso DESC 
            LIMIT 3";

    $results = $db->query($sql);
        while($row = pg_fetch_assoc($results)){
        $ds_assunto = $ds_assunto.$row['ds_assunto']."@";
            
    }

    $ds_assunto = substr_replace($ds_assunto, "", -1);
    
    echo "botGetRank: $ds_assunto \n";
    
    return $ds_assunto;

}


//--------------------------------
function botUpdateMsgBot($bot_ura,$bot_menu,$bot_opcao,$cti){
    //Insere bilhete de navegacao
    //@param string  $bot_ura -> Nome da "URA"
    //@param string  $bot_menu -> Nome da "MENU"
    //@param string  $bot_opcao -> Nome da "OPCAO"
    //@param string  $cti -> Formato INFORMACAO_PROTOCOLO_CPF

    global $id;
    global $db;
    global $id_msg;

    $name_function = __FUNCTION__;
    
    if($bot_opcao == "i"){
        $bot_opcao = "Opção Inválida";
    }

    $sql = "UPDATE messages SET ds_bot_ura = '$bot_ura', ds_bot_menu = '$bot_menu', ds_bot_opcao = '$bot_opcao', ds_bot_cti = '$cti' WHERE messages.id = $id_msg";
    $db->exec($sql);

    $log = "CMD:$sql";
    botGeraLog($name_function,$log);
    echo "LOG $name_function: $sql \n";

    sleep(1);
    return;

}


//--------------------------------
function botUpdateMsgSophia(){
    //Insere bilhete de navegacao, incluindo dados da Sophia
    //Natural language process

    global $id;
    global $db;
    global $id_msg;
    
    global $ret_botfaq_dic;
    global $ret_botfaq_duvida;
    global $ret_botfaq_assunto;
    global $ret_botfaq_palavra;
    global $ret_botfaq_score;
    global $ret_botfaq_retorno;

    $name_function = __FUNCTION__;

    $sql = "UPDATE messages SET js_nlp='{ \"ds_dicionario\":\"$ret_botfaq_dic\", \"ds_duvida\":\"$ret_botfaq_duvida\", \"ds_assunto\":\"$ret_botfaq_assunto\", \"ds_palavra\":\"$ret_botfaq_palavra\", \"nu_score\":\"$ret_botfaq_score\", \"ds_transcricao\":\"$ret_botfaq_retorno\" }' WHERE messages.id = $id_msg";
    $db->exec($sql);

    $log = "CMD:$sql";
    botGeraLog($name_function,$log);
    echo "LOG $name_function: $sql \n";

    sleep(1);
    return;

}


//--------------------------------
function botUpdateMessages($js){
    //Atualiza tabela messages de acordo com o id da mensagem enviada pelo bot
    //@param string  $js -> JSON em string
    global $id;
    global $db;
    global $id_msg_bot;
    $name_function = __FUNCTION__;

    $sql="UPDATE messages SET js_integracao = COALESCE(js_integracao,'{}') || '$js'::JSONB WHERE id = $id_msg_bot;";
    $db->exec($sql);

    $log = "SQL:$sql";
    echo "LOG $name_function: $sql \n";
    botGeraLog($name_function,$log);

    sleep(0.5);
    return;

}


//--------------------------------
function botUpdateChatlist($js){
    //Atualiza chatlist
    //@param string  $js -> JSON em string
    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $sql="UPDATE chat_list SET js_integracao = COALESCE(js_integracao,'{}') || '$js'::JSONB WHERE id = $id;";
    $db->exec($sql);

    $log = "SQL:$sql";
    echo "LOG $name_function: $sql \n";
    botGeraLog($name_function,$log);

    sleep(0.5);
    return;


}


//--------------------------------
function botUpdateIntegracaoDB($js){
    //Atualiza chatlist
    //@param string  $js -> JSON em string
    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $sql="INSERT INTO integracao.tb_chat_integracao(id_chat, js_integracao) VALUES ($id, '$js') RETURNING cd_chat_integracao;";
    $db->exec($sql);

    $log = "SQL:$sql";
    echo "LOG $name_function: $sql \n";
    botGeraLog($name_function,$log);

    sleep(0.5);
    return;


}


//--------------------------------
function botUpdateIntegracaoHTTP($integracao_url="", $integracao_metodo="", $integracao_envio="", $integracao_retorno="", $integracao_extra_1="", $integracao_extra_2="", $integracao_extra_3=""){
    //Insere bilhete de integracao

    $js = "{ \"int_url\":\"$integracao_url\",  \"int_metodo\":\"$integracao_metodo\", \"int_envio\":\"$integracao_envio\", \"int_retorno\":\"$integracao_retorno\", \"int_extra_1\":\"$integracao_extra_1\", \"int_extra_2\":\"$integracao_extra_2\", \"int_extra_3\":\"$integracao_extra_3\" }";

    botUpdateIntegracaoDB($js);

    return;

}


//--------------------------------
function botUpdateCTI($cti_new){
    //Atualiza o campo cti do chat_list
    //@param string  $cti_new -> Formato INFORMACAO_PROTOCOLO_CPF
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $sql="UPDATE chat_list SET cti = '$cti_new' WHERE chat_list.id = $id;";
    $db->exec($sql);

    $log = "CMD:$sql";
    botGeraLog($name_function,$log);
    echo "LOG $name_function: $sql \n";

    sleep(1);
    return;

}


//--------------------------------
function botUpdateAgent($id_user){
    //Atualiza agente que ira atender o usuario
    //@param string  $id_user -> Codigo do agente que esta no banco
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $sql="UPDATE chat_list set id_user = '$id_user' WHERE chat_list.id = '$id';";    
    $db->exec($sql);
    
    $log = "CMD:$sql";
    botGeraLog($name_function,$log);
    echo "LOG $name_function: $sql \n";

    sleep(1);
    return;

}


//--------------------------------
function botRegistroPesquisa($arr_pergunta, $arr_resposta){
    //Pesquisa de satisfacao
    //@param array  $arr_pergunta -> Array com perguntas
    //@param array  $arr_resposta -> Array com respostas 

    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $p = "";
    $r = "";
    $s1 = "\"";
    $s2 = "\"";
    
    foreach ($arr_pergunta as &$i) {
        $i = str_replace(",", "\,",$i);
        $p = $p.$s1.$i.$s2.",";
    }

    foreach ($arr_resposta as &$i) {
        $i = str_replace(",", "\,",$i);
        $r = $r.$s1.$i.$s2.",";
    }

    $p = substr($p, 0, -1);
    $r = substr($r, 0, -1);

    $p = "{ $p }";
    $r = "{ $r }";

    $pesquisa = "{ $p , $r }";


    $sql = "UPDATE chat_list SET quest = 1, lt_pesquisa = '$pesquisa' WHERE id = $id";
    $db->exec($sql);

    $log = "CMD:$sql";
    botGeraLog($name_function,$log);
    echo "LOG $name_function: $sql \n";

    return;

}


//--------------------------------
function botVerificaHorarioChat(){
    //Verifica horario de entrada do Chat
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;

    $data = date('Y-m-d');
    $diasemana_numero = date('w', strtotime($data));
    $sql="select msg,start_hr,end_hr from schedule where day_number=$diasemana_numero";
    $db->exec($sql);
    $results = $db->query($sql);
    
    if($results == "" || $results == Null){
        echo "LOG $name_function: SEM FORMULARIO OU VINDO VAZIO DO BANCO";
        return;
    }

    while ($row = pg_fetch_assoc($results)) {
        $start_hr=($row['start_hr']);
        $end_hr=($row['end_hr']);
        $msg=$row['msg'];
    }

    
    $now = date('H:i:s');

    echo "LOG $name_function: DIA SEMANA -> $diasemana_numero  HORA -> $now  CHAT_HORA_INICIO -> $start_hr  CHAT_HORA_FIM -> $end_hr \n";

    if (($now>=$start_hr) && ($now<=$end_hr)) {
        return;

    }else{
        botInsertMsg($msg);
        botFinishChat();
    }

}


//--------------------------------
function botVerificaHorarioFormulario($channel,$cifra_servico){
    //Verifica horario do Servico
    
    global $id;
    global $db;
    $name_function = __FUNCTION__;


    //===========================================================================================================
    //Verifica e cria os diretorios utilizados pelos sistema
    //===========================================================================================================
    $origem = "/home/extend/servico/$cifra_servico/msg";
    $destino = "/var/lib/comunix/sounds/servico/$cifra_servico/";

    if(file_exists($destino)){
        echo "LOG $name_function: O Diretorio $destino ja existe \n";
    } else {
        shell_exec( "mkdir -p $destino");
        echo "LOG $name_function: O Diretorio $destino foi criado com exito \n";
    }
    if(file_exists($origem)){
        echo "LOG $name_function: VERBOSE \"O Diretorio $origem ja existe \n";
    } else {
        shell_exec( "mkdir -p $origem");
        echo "LOG $name_function: O Diretorio $origem foi criado com exito \n";

    }

    //=================================================================================================================
    
    $arquivo_json = "/home/extend/servico/$cifra_servico/$cifra_servico.json";
    $lista = file($arquivo_json);

    if(!file_exists($arquivo_json)){
        $log = "LOG $name_function: ARQUIVO JSON NAO ENCONTRADO --> $arquivo_json \n";
        echo "$log";
        botGeraLog($name_function,$log);
        return;
    }
    
    $result = $lista[0];

    if($result != ''){
        //$result = file_get_contents($url);
        $result = json_decode($result, true);
        $servico_ativo = $result['in_status_formulario'];
        $in_atendimento_humano = 1;
        $ds_msg_abertura = 0;
        $ds_msg_fhorario = 0;
        $in_msg_excecao = 0;
        $msg_excecao = 0;
        $in_msg_abertura = 0;
        $in_mensagem_tempo_maximo_fila = 0;
        $data_atual = date('d-m-Y');
        $datahora_atual = $data_atual.' '.date('H:i:s');
        $datahora_atual =  strtotime($datahora_atual);

        $dd = date("w");
        $dentro_horario = 0;
        $excecao = 0;

        //===========================================================================================================
        // Configuracao de excecao
        //===========================================================================================================
        $msg_excecao = 0;
        if(count($result['excecao']) > 0){
            for($i = 0; $i < count($result['excecao']); $i++) {
                $inicio =  strtotime($result['excecao'][$i]['dt_inicial']);
                $final =  strtotime($result['excecao'][$i]['dt_final']);
                if(($datahora_atual >= $inicio) && ($datahora_atual <= $final)){
                    $excecao = 1;
                    $in_atendimento_humano = $result['excecao'][$i]['in_atendimento_humano'];// Atendimento humano
                    $msg_excecao = $result['excecao'][$i]['ds_mensagem_gravacao']; //mensagem excessão
                    if($msg_excecao != ''){
                        botInsertMsg($msg_excecao);
                        sleep(3);
                    }
                    if($in_atendimento_humano == 0){
                        botFinishChat();
                    }else{
                        goto verificaHorario;
                    }

                    break;
                }
            }
        }

        //===========================================================================================================
        //Determina o horario de funcionamento
        //===========================================================================================================
        verificaHorario:
        switch($dd) {

            //Domingo
            case"0":
                if($result['in_domingo'] == 1){
                    if(($result['hr_domingo_inicial'] == '') && ($result['hr_domingo_final'] == '') ){
                        $hr_domingo_inicial = $data_atual.' 00:00:00';
                        $hr_domingo_final = $data_atual.' 00:00:00';
                    }
                    else{
                        $hr_domingo_inicial = "$data_atual $result[hr_domingo_inicial]";
                        $hr_domingo_final = $data_atual.' '.$result['hr_domingo_final'];
                    }
                    $hr_domingo_inicial = strtotime($hr_domingo_inicial);
                    $hr_domingo_final =  strtotime($hr_domingo_final);

                    if(($datahora_atual >= $hr_domingo_inicial) && ($datahora_atual <= $hr_domingo_final)){
                        $dentro_horario = 1;
                    }
                }
            break;

            //Segunda-Feira
            case"1":
                if($result['in_segunda'] == 1){
                    if(($result['hr_segunda_inicial'] == '') && ($result['hr_segunda_final'] == '') ){
                        $hr_segunda_inicial = $data_atual.' 00:00:00';
                        $hr_segunda_final = $data_atual.' 00:00:00';
                    }
                    else{
                        $hr_segunda_inicial = "$data_atual $result[hr_segunda_inicial]";
                        $hr_segunda_final = $data_atual.' '.$result['hr_segunda_final'];
                    }
                    $hr_segunda_inicial = strtotime($hr_segunda_inicial);
                    $hr_segunda_final =  strtotime($hr_segunda_final);

                    if(($datahora_atual >= $hr_segunda_inicial) && ($datahora_atual <= $hr_segunda_final)){
                        $dentro_horario = 1;
                    }
                }
            break;

            //Terca-Feira
            case"2":
                if($result['in_terca'] = 1){
                    if(($result['hr_terca_inicial'] == '') && ($result['hr_terca_final'] == '') ){
                        $hr_terca_inicial = $data_atual.' 00:00:00';
                        $hr_terca_final = $data_atual.' 00:00:00';
                    }
                    else{
                        $hr_terca_inicial = "$data_atual $result[hr_terca_inicial]";
                        $hr_terca_final = $data_atual.' '.$result['hr_terca_final'];
                    }
                    $hr_terca_inicial = strtotime($hr_terca_inicial);
                    $hr_terca_final =  strtotime($hr_terca_final);

                    if(($datahora_atual >= $hr_terca_inicial) && ($datahora_atual <= $hr_terca_final)){
                        $dentro_horario = 1;
                    }
                }
            break;

            //Quarta-Feira
            case"3":
                if($result['in_quarta'] == '1'){
                    if(($result['hr_quarta_inicial'] == '') && ($result['hr_quarta_final'] == '') ){
                        $hr_quarta_inicial = $data_atual.' 00:00:00';
                        $hr_quarta_final = $data_atual.' 00:00:00';
                    }
                    else{
                        $hr_quarta_inicial = "$data_atual $result[hr_quarta_inicial]";
                        $hr_quarta_final = $data_atual.' '.$result['hr_quarta_final'];
                    }
                    $hr_quarta_inicial = strtotime($hr_quarta_inicial);
                    $hr_quarta_final =  strtotime($hr_quarta_final);

                    if(($datahora_atual >= $hr_quarta_inicial) && ($datahora_atual <= $hr_quarta_final)){
                        $dentro_horario = 1;
                    }
                }
            break;

            //Quinta-Feira
            case"4":
                if($result['in_quinta'] == '1'){
                    if(($result['hr_quinta_inicial'] == '') && ($result['hr_quinta_final'] == '') ){
                        $hr_quinta_inicial = $data_atual.' 00:00:00';
                        $hr_quinta_final = $data_atual.' 00:00:00';
                    }
                    else{
                        $hr_quinta_inicial = "$data_atual $result[hr_quinta_inicial]";
                        $hr_quinta_final = $data_atual.' '.$result['hr_quinta_final'];
                    }
                    $hr_quinta_inicial = strtotime($hr_quinta_inicial);
                    $hr_quinta_final =  strtotime($hr_quinta_final);

                    if(($datahora_atual >= $hr_quinta_inicial) && ($datahora_atual <= $hr_quinta_final)){
                        $dentro_horario = 1;
                    }
                }
            break;

            //Sexta-Feira
            case"5":
                if($result['in_sexta'] == '1'){
                    if(($result['hr_sexta_inicial'] == '') && ($result['hr_sexta_final'] == '') ){
                        $hr_sexta_inicial = $data_atual.' 00:00:00';
                        $hr_sexta_final = $data_atual.' 00:00:00';
                    }
                    else{
                        $hr_sexta_inicial = "$data_atual $result[hr_sexta_inicial]";
                        $hr_sexta_final = $data_atual.' '.$result['hr_sexta_final'];
                    }
                    $hr_sexta_inicial = strtotime($hr_sexta_inicial);
                    $hr_sexta_final =  strtotime($hr_sexta_final);

                    if(($datahora_atual >= $hr_sexta_inicial) && ($datahora_atual <= $hr_sexta_final)){
                        $dentro_horario = 1;
                    }
                }
            break;

            //Sabado
            case"6":
                if($result['in_sabado'] == '1'){
                    if(($result['hr_sabado_inicial'] == '') && ($result['hr_sabado_final'] == '') ){
                        $hr_sabado_inicial = $data_atual.' 00:00:00';
                        $hr_sabado_final = $data_atual.' 00:00:00';
                    }
                    else{
                        $hr_sabado_inicial = "$data_atual $result[hr_sabado_inicial]";
                        $hr_sabado_final = $data_atual.' '.$result['hr_sabado_final'];
                    }
                    $hr_sabado_inicial = strtotime($hr_sabado_inicial);
                    $hr_sabado_final =  strtotime($hr_sabado_final);

                    if(($datahora_atual >= $hr_sabado_inicial) && ($datahora_atual <= $hr_sabado_final)){
                        $dentro_horario = 1;
                    }
                }
                break;
        }

        //===========================================================================================================
        //Configura frase fora de horario
        //===========================================================================================================

        if($dentro_horario == 0){
            echo "LOG $name_function: FORA DO HORARIO \n";

            if(trim($result['in_mensagem_fora_horario']) == '1'){
                $in_msg_fhorario = 1;
                $msg_fora_horario = $result['ds_mensagem_fora_horario'];
                sleep(1);
                botInsertMsg($msg_fora_horario);
                echo "LOG $name_function: FORA DO HORARIO MSG -> $msg_fora_horario \n";
            }

            botFinishChat();

        }elseif($dentro_horario == 1){
            echo "LOG $name_function: DENTRO DO HORARIO \n";
        }

        return;

    }
}


?>

