<?php
require_once('../lib/qdmail-php7/qdmail.php');
require_once('../lib/qdmail-php7/qdsmtp.php');

// シークレットキー
$secret_key = '6LfnNxUUAAAAAKbh1bqW5mCKcZ5HrhNceEaEEkHU' ;
$remoteip = $_SERVER["REMOTE_ADDR"];

session_start();

$isRecaptcha = true;
if (isset($_POST['mode'])  && $_POST['mode'] == 'confirm'  && isset($_SESSION['pd'])) {
	// エラー判定
	$recaptcha = '';
	if( !empty( $_POST['g-recaptcha-response'] ) ) {
		$recaptcha = $_POST["g-recaptcha-response"];
		// エンドポイント
		$endpoint = 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha . '&remoteip=' . $remoteip;
		// 判定結果の取得
		$curl = curl_init() ;
		curl_setopt( $curl , CURLOPT_URL , $endpoint ) ;
		curl_setopt( $curl , CURLOPT_SSL_VERIFYPEER , false ) ;// 証明書の検証を行わない
		curl_setopt( $curl , CURLOPT_RETURNTRANSFER , true ) ;// curl_execの結果を文字列で返す
		curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;// タイムアウトの秒数
		$json = curl_exec( $curl ) ;
		curl_close( $curl ) ;
		$j = json_decode($json, true);
		$ret = false;
		if ($j["success"] == true) {
			$isRecaptcha = true;
			$name = gv('name');
			$team = gv('team');
			$email = gv('email');
			$tel = gv('tel');
			$title = gv('title');
			$buf = gv('body');
			$body = createbody($name, $team, $title, $buf);
			$ret = mailsender($email, '', 'お問い合わせを受付けました[' . $title . ']', $body, '仙台市卓球協会(送信専用)', "noreply@sendai-tta.info", null);
			if ($ret) {
				$body = createbodyfortta($name, $team, $tel, $email, $title, $buf);
				$ret = mailsender('sendai.tta@gmail.com', '', $title, $body, null, "noreply@sendai-tta.info", $email);
			}
		} else {
			$isRecaptcha = false;
			$ret = false;
		}
		if ($ret) {
			session_destroy();
			header('Location: ./inquiry-submit.php?status=0');
			exit;
		} else {
			header('Location: ./inquiry-submit.php?status=1');
			exit;
			//var_dump($ret);
		}
	} else {
		$isRecaptcha = false;
	}
}


function h($str) {
	return htmlspecialchars ($str);
}
function pv($key) {
	$pd = $_SESSION['pd'];
	if (isset($pd[$key])) {
		print h($pd[$key]);
	} else {
		print "";
	}
}
function gv($key) {
	$pd = $_SESSION['pd'];
	if (isset($pd[$key])) {
		return h($pd[$key]);
	} else {
		return "";
	}
}

//メール送信関数
// $to：送信先メールアドレス
// $subject：件名（日本語OK）
// $body：本文（日本語OK）
// $fromname：送信元名（日本語OK）
// $fromaddress：送信元メールアドレス
// $replyto：返信メールアドレス
function mailsender($to,$bcc,$subject,$body,$fromname,$fromaddress,$replyto){
    //SMTP送信
    $mail = new Qdmail();
    $mail -> smtp(true);

    $setting = csvToArray("./smtp-setting.csv");
    //var_dump($param);
    $param = array(
        'host'=>'ssl://smtp.gmail.com',
        'port'=> 465 ,
        'from'=>'sendai.tta@gmail.com',
        'user' => 'sendai.tta@gmail.com',
        'pass' => 'sendai00',
        'protocol'=>'SMTP_AUTH',
    );
    $mail ->smtpServer($setting[1]);
    $mail ->to($to);
    if (!empty($bcc)) {
        $mail ->bcc($bcc);
    }
    $mail ->subject($subject);
    $mail ->from($fromaddress,$fromname);
    if (!empty($replyto)) {
        $mail ->replyto($replyto);
    }
    $mail ->text($body);
    $return_flag = $mail->send();
    return $return_flag;
}

// csvの1列目をキーにした連想配列を返す（引数：csvファイルのパス）
function csvToArray($csvPath){
  $csvArray = array();
  $firstFlg = true;
  $keys = array();
  $count = 0;
  $file = fopen($csvPath, 'r');

  while ($line = fgetcsv($file)) {
    if($firstFlg){
      for($i = 0; $i < count($line); $i++){
        array_push($keys,$line[$i]);
      }
      $firstFlg = false;
    }else{
      for($i = 0; $i < count($line); $i++){
        $csvArray[$count][$keys[$i]] = $line[$i];
      }
      $count++;
    }
  }
  fclose($file);
  return $csvArray;
}

function createbody($name, $team, $title, $body) {
	$str = <<<EOM
【本メールは自動応答として作成されております。】
【当メールの送信アドレスは送信専用となっております。このメールへの返信はできませんのでご了承ください。】

$team $name 様

仙台市卓球協会 Webサイト担当です。
お世話になっております。

下記の内容でお問い合わせを承りました。
大変恐縮ですが、回答には数日～1週間程度お時間を頂く場合があります。

今後ともよろしくお願い致します。

====
タイトル： $title

問い合わせ内容：
$body


EOM;

	return $str;
}

function createbodyfortta($name, $team, $tel, $email, $title, $body) {
	$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	$ua = mb_strtolower($_SERVER['HTTP_USER_AGENT']);
  $str = <<<EOM

問い合わせ内容：
$body

====
氏名： $name 様
所属： $team
メールアドレス： $email
電話番号： $tel


EOM;

	return $str;
}
?>
<?php include(dirname(__FILE__) . "/header.tmpl"); ?>

<div class="row stta">
<h2 class="bg-info">内容確認</h2>
<p>以下の内容でお問い合わせを送信します。よろしいですか?</p>
<div class="container-fluid bs-docs-container">
<div class="bs-docs-section stta">

<?php if ($isRecaptcha == false) {  ?>
<div class="alert alert-danger">
<p>あなたはロボットではありませんか？</p>
</div>
<?php } ?>



<form id="cf" name="cf" method="post" action="inquiry-confirm.php" >
    <input type="hidden" name="mode" value="confirm">
    <div class="form-group">
      <label class="col-sm-3 control-label">お名前</label>
      <div class="col-sm-9">
      <p class="form-control-static"><?php pv('name'); ?></p>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">所属</label>
      <div class="col-sm-9">
      <p class="form-control-static"><?php pv('team'); ?></p>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">Eメール</label>
      <div class="col-sm-9">
      <p class="form-control-static"><?php pv('email'); ?></p>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">電話番号</label>
      <div class="col-sm-9">
      <p class="form-control-static"><?php pv('tel'); ?></p>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">お問い合わせタイトル</label>
      <div class="col-sm-9">
      <p class="form-control-static"><?php pv('title'); ?></p>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">お問い合わせ内容</label>
      <div class="col-sm-9">
      <p class="form-control-static"><?php print nl2br(gv('body')); ?></p>
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-12">
      <div class="g-recaptcha" data-sitekey="6LfnNxUUAAAAAGnkAbISTkXOLdvG91lOZfDHOrGC"></div>
      </div>
    </div>
    <!-- 登録ボタンの表示 -->
    <div class="form-group">
      <div class="col-sm-12">
      <hr>
      <p>お問い合わせが送信されると、入力されたメールアドレス宛てに自動応答のメールが送られます。
      </p>
      <button id="btn" class="btn btn-default" onclick="location.href='./inquiry.php';return false;">戻る</button>
      <button id="btn" type="submit" class="btn btn-success">送信</button>
      </div>
    </div>
</form>
</div>
</div>
</div>

<?php include(dirname(__FILE__) . "/footer.tmpl"); ?>
