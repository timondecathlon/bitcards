<?php

namespace Bitkit\Core\Entities;

class User extends \Bitkit\Core\Entities\Unit implements \Bitkit\Core\Interfaces\UserActions
{

    use \Bitkit\Core\Traits\Test;

    public $login;
    public $password;
    public $result;


    public function setTable() : string
    {
        return 'core_users';
    }

    public function setTableId()
    {
        $table = new Table(0);
        $table->getTableByName($this->setTable());
        return $table->tableId();
    }

    public function logIn()
    {

    }

    public function logOut()
    {

    }

    public function registration()
    {

    }

    public function recover(){

    }

    public function addCards()
    {

    }

    public function saveCards(){

    }

    public function getCards(){

    }


    public function member_id()
    {
        if ($this->getField('id')) {
            return $this->getField('id');
        }
        return 0;
    }

    public function title()
    {
        if($this->firstName() || $this->lastName() || $this->fatherName()){
            return $this->fullName();
        }else{
            return $this->getField('title');
        }
    }

    public function titleShort(){
        if($this->firstName() || $this->lastName() || $this->fatherName()){
            return $this->lastName().' '.preg_split('//u',$this->firstName(),-1,PREG_SPLIT_NO_EMPTY)[0].'.';
        }else{
            return $this->getField('title');
        }
    }

    public function firstName(){
        return $this->getField('first_name');
    }

    public function lastName(){
        return $this->getField('last_name');
    }

    public function fatherName(){
        return $this->getField('father_name');
    }

    public function fullName(){
        return $this->firstName().' '.$this->lastName().' '.$this->fatherName();
    }

    public function status(){
        return $this->getField('status');
    }

    public function reputation_points(){
        return $this->getField('reputation_points');
    }

    public function photo(){
        if(unserialize($this->getField('photo'))[0] != ''){
            return unserialize($this->getField('photo'))[0];
        }else{
            return 'https://openclipart.org/image/2400px/svg_to_png/247319/abstract-user-flat-3.png';
        }
    }

    public function photos(){
        if(unserialize($this->getField('photo')) != ''){
            return unserialize($this->getField('photo'));
        }
        return false;
    }

    public function coverPhoto(){
        return $this->getField('cover_photo');
    }

    public function respect_points(){
        return $this->getField('respect_points');
    }


    public function isActive(){
        if($this->getField('activity') == 1){
            return true;
        }
        return false;
    }

    public function is_online(){
        if(time() - $this->getField('last_visit') < 60){
            return true;
        }
        return false;
    }

    public function joined(){
        return date_format_rus($this->getField('publ_time'));
    }

    public function last_was(){
        return date_format_rus($this->getField('last_visit'));
    }

    public function instagram(){
        return $this->getField('instagram');
    }

    public function avatar()
    {
        if($this->getField('avatar')){
            return $this->getField('avatar');
        }
        return '/img/avatar_default.png';
    }

    public function changeAvatar($photo)
    {

        if(file_exists(PROJECT_ROOT.'/'.$this->avatar())){
            unlink(PROJECT_ROOT.'/'.$this->avatar());
        }
        $file_sec_new_name = md5($_FILES['file']['name'].time()).'.jpg';
        $uploadfile_sec = '/uploads/'.$file_sec_new_name;
        echo $uploadfile_sec;
        echo $_SERVER['DOCUMENT_ROOT'].$uploadfile_sec;
        if(move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].$uploadfile_sec)){
            $photo = '/uploads/'.$file_sec_new_name;
            $this->updateField('photo',json_encode($photo));
            echo "Загрузили";
        }else{
            echo "не Загрузили";
        }
    }

    public function getMemberDialogs(){
        $messages = new Message(0);
        $sql = $this->pdo->prepare("SELECT * FROM ".$messages->setTable()." WHERE (destination_id=:a OR author_id=:b)  AND activity='1' ORDER BY publ_time DESC ");
        $sql->bindParam(':a', $this->member_id());
        $sql->bindParam(':b', $this->member_id());
        $sql->execute();
        $units = $sql->fetchAll();
        $authors_arr  = [];
        $messages_arr  = [];
        foreach ($units as $message) {
            if ($message['author_id'] == $this->member_id()) {
                if (!in_array($message['destination_id'], $authors_arr)) {
                    $authors_arr[] = $message['destination_id'];
                    $messages_arr[] = $message['id'];
                }
            } else {
                if (!in_array($message['author_id'], $authors_arr)) {
                    $authors_arr[]= $message['author_id'];
                    $messages_arr[]= $message['id'];
                }
            }
        }

        $msg_line = implode(',',$messages_arr);

        $sql = $this->pdo->prepare("SELECT * FROM ".$messages->setTable()." WHERE id IN($msg_line) ORDER BY publ_time DESC ");
        $sql->execute();
        $units = $sql->fetchAll();
        return $units;
    }


    public function getUserTasks(){
        $tasks = new Task(0);
        $sql = $this->pdo->prepare("SELECT * FROM ".$tasks->setTable()." WHERE agent_id='".$this->member_id()."' ORDER BY publ_time DESC ");
        $sql->execute();
        return $sql->fetchAll();

    }


    public function getAllNewMessages(){
        $messages = new Message(0);
        $sql = $this->pdo->prepare("SELECT * FROM ".$messages->setTable()." WHERE destination_id=:to_id  AND activity='1' AND is_read='0' ");
        $sql->bindParam(':to_id', $this->member_id());
        $sql->execute();
        $units = $sql->fetchAll();
        return $units;
    }

    public function getAllNewTasks(){
        $tasks = new Task(0);
        $sql = $this->pdo->prepare("SELECT * FROM ".$tasks->setTable()." WHERE agent_id=:to_id  AND activity='1' AND is_read='0' ");
        $sql->bindParam(':to_id', $this->member_id());
        $sql->execute();
        $units = $sql->fetchAll();
        return $units;
    }

    public function canWriteFeedback(int $item_id){
        $sys = new System(0);
        $good_comment_cooldown_days = $sys->getField('good_comment_cooldown');
        $lim_time = time() - $good_comment_cooldown_days*24*3600;
        $sql = $this->pdo->prepare("SELECT COUNT(*) FROM comments WHERE author=:member_id AND record_id=:item_id AND comment_group='2' AND publ_time>'$lim_time' ");
        $sql->bindParam(':member_id', $this->member_id());
        $sql->bindParam(':item_id', $item_id);
        $sql->execute();
        $res = $sql->fetch();
        if($res[0] == 0  || $this->isAdmin() ) {
            return true;
        }else{
            return false;
        }
    }



    /* check login and pass  */
    public function loginCheck($login, $password){
        $member_sql = $this->pdo->prepare("SELECT * FROM ".$this->setTable()." WHERE login=:login OR email=:email ");
        $member_sql->bindParam(':login',$login);
        $member_sql->bindParam(':email',$login);
        $member_sql->execute();
        $member = $member_sql->fetch();
        if($member_sql->rowCount()){
                if( hash_equals($member['password'], crypt($password, $member['password']))){

                    $id = $member['id'];
                    $user_hash = md5($member['id'].'%'.$member['password'].'%'.$member['email'].'%'.time()); //создаем хэш для защиты куки
                    $sql = $this->pdo->prepare("UPDATE ".$this->setTable()." SET  user_hash=:user_hash  WHERE id=:id");
                    $sql->bindParam(":user_hash", $user_hash);
                    $sql->bindParam(":id", $id);
                    $sql->execute();
                    return ['id'=>$id,'hash'=>$user_hash];
                    /*
                    $member_password_hash = $member['password'];
                    setcookie ("member_id", "$id",time()+36000,"/"); //поставить тут переменное занчение
                    $user_hash = md5($_SERVER ['HTTP_USER_AGENT'].'%'.$member_password_hash); //создаем хэш для защиты куки
                    $sql = $this->pdo->prepare("UPDATE ".$this->setTable()." SET ip_address=:ip_address , user_hash=:user_hash  WHERE id=:id");
                    $sql->bindParam(":ip_address", $_SERVER['REMOTE_ADDR']);
                    $sql->bindParam(":user_hash", $user_hash);
                    $sql->bindParam(":id", $id);
                    $sql->execute();
                    $flag =1;
                    break;
                    */
                }else{
                    return 0;
                    //setcookie ("member_id","0",time()-3600,"/");
                    //echo 'пароль неверный';
                }
        }else{
            return 0;
            //echo 'такого юзера нету';
        }
    }

    public function exists($email,$title){
        $sql = $this->pdo->prepare("SELECT COUNT(id) as num FROM ".$this->setTable()." WHERE email=:email OR title=:title ");
        $sql->bindParam(":email", $email);
        $sql->bindParam(":title", $title);
        $sql->execute();
        $member =  $sql->fetch(\PDO::FETCH_LAZY);
        if($member['num'] > 0){
            return true;
        }else{
            return false;
        }
    }

    /* validate the users id*/
    public function is_valid($hash){
        $sql = $this->pdo->prepare("SELECT * FROM ".$this->setTable()." WHERE id=:id");
        $sql->bindParam(":id", $this->id);
        $sql->execute();
        $member =  $sql->fetch(\PDO::FETCH_LAZY);
        if(!hash_equals ( $member->user_hash , $hash )){
            return false;
        }else{
            return true;
        }
    }

    /* check if the user is admin  */
    public function isAdmin(){
        if($this->getField('member_group_id') == 4){
            return true;
        }else{
            return false;
        }
    }



    /* total users amount count */
    public function users_count(){
        $sql = $this->pdo->prepare("SELECT * FROM ".$this->setTable()." ");
        $sql->execute();
        return count($sql->fetch());
    }

    /* online users amount count */
    public function online_users_count(){
        $sql = $this->pdo->prepare("SELECT * FROM core_visitors WHERE activity='1' ");
        $sql->execute();
        return count($sql->fetch());
    }

    /* today visitors count */
    public function today_visits_count(){
        $now = time();
        $today_start = strtotime(date("d-m-Y", time()));
        $sql = $this->pdo->prepare("SELECT * FROM core_visitors WHERE publ_time >'$today_start' AND publ_time <'$now' ");
        $sql->execute();
        //return $sql->fetch();
    }

    /* month visitors count */
    public function month_visits_count(){
        $now = time();
        $month_start = strtotime(date("m-Y", time()));
        $sql = $this->pdo->prepare("SELECT * FROM visitors WHERE publ_time >'$month_start' AND publ_time <'$now' ");
        $sql->execute();
        return $sql->fetch();
    }

    /* get the last visit time of a user */
    public function get_last_visit(){
        $sql = $this->pdo->prepare("UPDATE ".$this->setTable()." SET last_visit=:last_visit WHERE id=:id");
        $sql->bindParam(':last_visit', time() );
        $sql->bindParam(':id', $this->id );
        try{
            $sql->execute();
        }catch (PDOException $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
        }
    }

    /*
    public function visit_find(){
        $time = time() - 60;
        $sql = $this->mysqli->prepare("UPDATE visitors SET activity='0' WHERE publ_time< ?");
        $sql->bindParam("i", $time);
        $sql->execute();

        if($_SESSION['page_hash']){
            $page_hash = $_SESSION['page_hash'];
        }else{
            $page_hash = 0;
        }
        $sql = $this->mysqli->prepare("SELECT * FROM users WHERE ip_address=?");
        $sql->bindParam("s", $_SERVER['REMOTE_ADDR']);
        $sql->execute();
        if($sql->get_results->num_rows > 0){
            $member =  $sql->get_results->fetch_assoc();
            $name = $member['title'];
            $user_id = $member['id'];
        }else{
            $name = 'Гость';
            $user_id = 0;
        }
        try{
            $sql = $this->mysqli->prepare("SELECT * FROM visitors WHERE activity='1' AND ip_address=?");
            $sql->bindParam("s", $_SERVER['REMOTE_ADDR']);
            $sql->execute();
            if($sql->get_results->num_rows > 0){

            }else{
                if( $curl = curl_init() ) {
                curl_setopt($curl, CURLOPT_URL, 'ru.sxgeo.city/xml/'.$_SERVER['REMOTE_ADDR']);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                $out = curl_exec($curl);
                $items = new SimpleXMLElement($out);// перезаписываем items для каждой конкретной машины
                $item = $items->ip;
                $country = $item->country;
                $country = $country->name_en;
                $city = $item->city;
                $city = $city->name_en;
                if($country == ''){
                   $country = 'Australia';
                }
               curl_close($curl);
            }
            $sql = $this->mysqli->prepare("INSERT INTO visitors(title, ip_address, country, city, publ_time, user_id, page_hash, activity)"."VALUES(:title, :ip_address, :country, :city, :publ_time, :user_id, :page_hash, '1')");
            $sql->bindParam("s", $name);
            //$sql->bindParam(":title", $name);
            //$sql->bindParam(":publ_time", time());
            //$sql->bindParam(":user_id", $user_id);
            //$sql->bindParam(":page_hash", $page_hash);
            //$sql->bindParam(":ip_address", $_SERVER['REMOTE_ADDR']);
            //$sql->bindParam(":country", $country);
            //$sql->bindParam(":city", $city);
            $sql->execute();
        }

        }catch (PDOException $e) {
            echo 'Подключение не удалось: ' . $e->getMessage();
        }
    }
        */

    /*
    public function check_email($email){
        $sql = $this->mysqli->prepare("SELECT * FROM ".$this->setTable()." WHERE email=?");
        $sql->bind_param("s", $email);
        $sql->execute();
        if($sql->get_result()->num_rows > 0){
            return true;
        }else{
            return false;
        }
    }

    public function check_name($name){
        $sql = $this->mysqli->prepare("SELECT * FROM ".$this->setTable()." WHERE title=?");
        $sql->bind_param("s", $name);
        $sql->execute();
        if($sql->get_result()->num_rows > 0){
            return true;
        }else{
            return false;
        }
    }
    */

    public function check_password($password, $confirm){
        if($password === $confirm && $password !='' && $password !=' '){
            return true;
        }else{
            return false;
        }
    }

    /*
    public function change_pass($new_pass){
        $sql = $this->mysqli->prepare("UPDATE ".$this->setTable()." SET password=?	WHERE restore_hash=?");
        $sql->bind_param("s", crypt($new_pass));
        $sql->bind_param("s", $_COOKIE['restore_hash']);
        try{
            $sql->execute();
            $sql = $this->pdo->prepare("SELECT * FROM ".$this->setTable()." WHERE restore_hash=?");
            $sql->bindParam("s", $_COOKIE['restore_hash']);
            $sql->execute();
            $member = $sql->fetch();
            setcookie ("pass_restored", "1",time()+3600,"/");
            setcookie ("restore_hash","0",time()-3600,"/");
            setcookie ("member_id", $member['id'],time()+3600,"/");
            return true;
        }catch(PDOException $e){
            echo 'Подключение не удалось: ' . $e->getMessage();
        }
    }
    */


    public function hasMarker(){
        if($this->marker()->id != NULL){
            return true;
        }else{
            return false;
        }
    }

    public function marker(){
        $marker = new Marker(0);
        $sql = $this->pdo->prepare("SELECT * FROM markers_groups WHERE table_name='".$this->setTable()."'");
        $sql->execute();
        $markerGroup = $sql->fetch(\PDO::FETCH_LAZY);

        $sql = $this->pdo->prepare("SELECT * FROM core_markers WHERE element_id='".$this->member_id()."' AND marker_group='".$markerGroup->id."'");
        $sql->execute();
        $marker_line = $sql->fetch(\PDO::FETCH_LAZY);

        $marker = new Marker($marker_line->id);
        return $marker;
    }

    public function email(){
        return $this->getField('email');
    }

    public function phone(){
        return $this->getField('phone');
    }

    /*
    public function find_by_param($param_name, $param_val){
        $sql = $this->mysqli->prepare("SELECT * FROM ".$this->setTable()." WHERE ?=?");
        $sql->bind_param("ss", $param_name, $param_val);
        if($sql->execute()){
            $member = $sql->get_result()->fetch_asscoc();
            return $member['id'];
        }
    }
    */

    /*
    public function create($PAR_ARR, $global_pars, PROJECT_URL.'/'){
       $line1 = '';
       $line2 = '';
       $publ_time = time();
       $i =0;
       ($global_pars['email_verification'] == 1) ? $activity = 1 : $activity = 0;
       $member_group_id = 2;
       $reg_hash = md5(time().md5(time()));
       $arr_isset = array();
       //смотрим какие поля были получены
       foreach($PAR_ARR as $arr_item){
        if($_POST[$arr_item] != NULL){
         if(is_array($_POST[$arr_item])){ //проверяем не массив ли это
           $arr_isset[$arr_item] = serialize($_POST[$arr_item]);
         }else{
           if($arr_item == 'password'){
            $arr_isset[$arr_item] = crypt($_POST[$arr_item]); //криптуем пароль
           }else{
            $arr_isset[$arr_item] = $_POST[$arr_item];
           }
         }
        }
       }
       //создаем строку полей
       foreach($arr_isset as $arr_isset_item =>$key){
         $line1 = $line1.$arr_isset_item.', ';
       }
       //создаем строку знаений
       foreach($arr_isset as $arr_isset_item){
        $line2 = $line2."'".addslashes ($arr_isset_item)."'".', ';
        $arr_isset_values[$i] = $arr_isset_item;
        $i++;
       }

      $line1 = $line1.'activity, publ_time, reg_hash, member_group_id';
      $line2 = $line2."'$activity', "."'$publ_time', "."'$reg_hash', "."'$member_group_id'";

      try {
       $user = $this->pdo->prepare("INSERT INTO users(".$line1.")"."VALUES(".$line2.")");
       $user->execute();

       //смотрим пользователя
       $sql = $this->pdo->prepare("SELECT * FROM users WHERE email= :email");
       $sql->bindParam(":email", $_POST['email']);
       $sql->execute();
       $new_user = $sql->fetch();

       if($global_pars['email_verification'] == 1 && $new_user['id'] ){
         $ver_link = PROJECT_URL.'/'."/controllers/verification/?code=".$reg_hash;
         $msg = $new_user['title'].", благодарим Вас за регистрацию на сайте ".$global_pars['site_name'].".<br></br>  Для активации Вашего аккаунта пройдите по ссылке $ver_link";
         setcookie ("member_id", "-1",time()+3600,"/");
         mail($new_user['email'], $global_pars['site_name'].". Подтверждение E-mail адреса",$msg);
       }else{
         setcookie ("member_id", $new_user['id'],time()+3600,"/");
       }

       //Отправляем или не отправляе подтверждение на почту

      }catch (PDOException $e) {
       echo 'Подключение не удалось: ' . $e->getMessage();
      }
    }

      public function verify($code){
          $sql = $this->pdo->prepare("SELECT * FROM users WHERE reg_hash= :hash");
          $sql->bindParam(":hash", $code);
          $sql->execute();
          if($sql->rowCount() > 0 ){
              try{
                  $member = $sql->fetch();
                  $ver_sql = $this->pdo->prepare("UPDATE users SET activity='1', reg_hash=''  WHERE id= :member_id");
                  $ver_sql->bindParam(":member_id", $member['id']);
                  $ver_sql->execute();
                  $member_id = $member['id'];
                  setcookie ("member_id", "$member_id",time()+3600,"/");
                  return true;
              }
              catch (PDOException $e){
                  echo 'Подключение не удалось: ' . $e->getMessage();
                  return false;
              }
          }
      }
      */



    public function group_id(){
        if($this->getField('member_group_id')){
            return $this->getField('member_group_id');
        }else{
            return 1;
        }
    }

    public function group_name(){
        $group = new Group($this->group_id());
        return $group->title();
    }


    public function inFavourites(int $item_id){
        $favs_arr =  $this->showJsonField('favourites');
        if(in_array($item_id, $favs_arr)){
            return true;
        }else{
            return false;
        }
    }

    public function getFavourites(){
        return $this->getJsonField('favourites');
    }

    public function reviewFavourites(){
        $reviewed_favourites = [];
        $favourites = $this->getFavourites();
        foreach ($favourites as $item){
            $offer_mix = new OfferMix();
            $offer_mix->getRealId($item[0],$item[1]);
            if($offer_mix->getField('deleted') !=1){
                $reviewed_favourites[] = $item;
            }
        }
        $this->updateField('favourites',json_encode($reviewed_favourites));
    }

    public function setFavourites(array $object_id){

        $favsArray =  $this->getFavourites();
        if($favsArray == NULL){
            $favsArray =  [];
        }
        if(in_array($object_id, $favsArray)){
            unset($favsArray[array_search($object_id, $favsArray)]);
            sort($favsArray); //иначе при перегоне из json в массив будет косяк
        }else{
            $favsArray[]=$object_id;
        }
        $favs_json = json_encode($favsArray);

        //$sql = $this->pdo->prepare("UPDATE ".$this->setTable()." SET favourites='$favs_json' WHERE id='".$this->id."'");
        //if($sql->execute()){
        if($this->updateField('favourites',$favs_json)){
            return true;
        }else{
            return false;
        }

    }

    public function setPresentations(array $object_id){

        $favsArray =  $this->getJsonField('presentations');
        if($favsArray == NULL){
            $favsArray =  [];
        }
        if(in_array($object_id, $favsArray)){
            unset($favsArray[array_search($object_id, $favsArray)]);
            sort($favsArray); //иначе при перегоне из json в массив будет косяк
            echo 0;
        }else{
            $favsArray[]=$object_id;
            echo 1;
        }
        $favs_json = json_encode($favsArray);


        if($this->updateField('presentations',$favs_json)){
            return true;
        }else{
            return false;
        }

    }


    /* FRIENDS METHODS */
    public function addUserToSocialList($list_field_name, int $target_user){
        //$member = new Member($member_id);
        $list =  $this->showJsonField($list_field_name);
        if($list == NULL){
            $list =  array();
        }
        if(!in_array($target_user,$list)){
            $list[]=$target_user;
            $listJson = json_encode($list);
            $sql = $this->pdo->prepare("UPDATE ".$this->setTable()." SET $list_field_name='$listJson'  WHERE id='".$this->member_id()."'");
            $sql->execute();
        }
    }

    public function deleteUserFromSocialList($list_field_name, int $target_user){
        //$member = new Member($member_id);
        $list =  $this->showJsonField($list_field_name);
        if($list == NULL){
            $list =  array();
        }
        if(in_array($target_user,$list)){
            unset($list[array_search($target_user, $list)]);
            sort($list); //иначе при перегоне из json в массив будет косяк
            $listJson = json_encode($list);
            $sql = $this->pdo->prepare("UPDATE ".$this->setTable()." SET $list_field_name='$listJson'  WHERE id='".$this->member_id()."'");
            $sql->execute();
        }
    }



    public function subscribe(int $user_id)
    {

    }

    public function unSubscribe(int $user_id)
    {

    }

    public function subscriberAdd(int $user_id)
    {
        $this->addUserToSocialList('subscribers', $user_id);
    }

    public function subscriberDelete(int $user_id)
    {
        $this->deleteUserFromSocialList('subscribers', $user_id);
    }

    public function subscriptionAdd(int $user_id)
    {
        $this->addUserToSocialList('subscriptions', $user_id);
    }

    public function subscriptionDelete(int $user_id)
    {
        $this->deleteUserFromSocialList('subscriptions', $user_id);
    }

    public function friendAdd(int $user_id)
    {
        $this->addUserToSocialList('friends', $user_id);
    }

    public function friendDelete(int $user_id)
    {
        $this->deleteUserFromSocialList('friends', $user_id);
    }


    public function getFriends1()
    {
        return array_intersect($this->getSubscribers(), $this->getSubscriptions());
    }

    public function getFriends()
    {
        return $this->showJsonField('friends');
    }

    public function getSubscribers()
    {
        return $this->showJsonField('subscribers');
    }

    public function getSubscriptions()
    {
        return $this->showJsonField('subscriptions');
    }

    public function isSubscribedTo(int $user_id)
    {
        if(in_array($user_id,$this->getSubscriptions())){
            return true;
        }else{
            return false;
        }
    }


    public function isFriend(int $user_id)
    {
        $subscription_user = new Member($user_id);
        if($this->isSubscribedTo($subscription_user->member_id()) && $subscription_user->isSubscribedTo($this->member_id())){
            return true;
        }else{
            return false;
        }
    }


    public function friendsCount()
    {
        return count($this->getFriends());
    }

    public function subscribersCount()
    {
        return count($this->getSubscribers());
    }

    public function subscriptionsCount()
    {
        return count($this->getSubscriptions());
    }

    public function memberInfoField($field)
    {
        $sql = $this->pdo->prepare("SELECT * FROM ".$this->setTable()." WHERE id=:id");
        $sql->bindParam(':id', $this->id);
        $sql->execute();
        $unit = $sql->fetch();
        if($unit[$field]){
            $field = json_decode($unit[$field]);
            $fieldValue = $field->value;
            $fieldPermission = $field->permission;
            return $unit[$field];
        }
    }



    /*

    public function isFriendOfFriend($id)
    {
        $sql = $this->mysqli->prepare("SELECT * FROM " . $this->setTable() . " WHERE id=?");
        $sql->bind_param("i", $this->id);
        $sql->execute();
        $unit = $sql->get_result()->fetch_assoc();
        $friendsArray = json_decode($unit['friends']);
        if(in_array($id, $friendsArray)){
            return true;
        }else{
            foreach ($friendsArray as $friendId) {
                $sql = $this->mysqli->prepare("SELECT * FROM " . $this->setTable() . " WHERE id=?");
                $sql->bind_param("i", $friendId);
                $sql->execute();
                $f = $sql->get_result()->fetch_assoc();
                $friendsOfFriendArray = json_decode($unit['friends']);
                if (in_array($id, $friendsOfFriendArray)) {
                    return true;
                    break;
                } else {
                    return false;
                }
            }
        }
    }

    */
    public function getALLVisitors()
    {
        return array_reverse($this->showJsonField('visitors'));
    }

    public function getLastVisitors()
    {


    }


    public function setNewVisitor(int $user_id)
    {
        $visitorsList = $this->getALLVisitors();
        if($visitorsList == NULL){
            $visitorsList =  array();
            $new_visitor_data = array('time' => time(), 'id' => $user_id );
            $visitorsList[]= $new_visitor_data;
            $visitorsList = json_encode($visitorsList);
            $sql = $this->pdo->prepare("UPDATE ".$this->setTable()." SET visitors=' $visitorsList' WHERE id='".$this->member_id()."' ");
            $sql->execute();
        }else{
            foreach ($visitorsList as $visitor_list_item) {
                if($visitor_list_item->id == $user_id){
                    //echo $visitor_list_item->id;
                    if(time() > $visitor_list_item->time + 3600*24 ){
                        $new_visitor_data = array('time' => time(), 'id' => $user_id );
                        $visitorsList[]= $new_visitor_data;
                        $visitorsList = json_encode($visitorsList);
                        $sql = $this->pdo->prepare("UPDATE ".$this->setTable()." SET visitors=' $visitorsList' WHERE id='".$this->member_id()."' ");
                        $sql->execute();
                    }
                    break;
                }

            }
        }
    }


}



?>