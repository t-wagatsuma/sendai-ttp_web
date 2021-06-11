<?php
//http://www.tohoho-web.com/ex/bootstrap.html
//http://mm.tripodw.biz/sendai-tta/inquiry.php
//https://manablog.org/php-valitron/

require_once("../lib/Validator.php");
session_start();

$v = null;
$pd = array();

if (isset($_POST['mode']) && $_POST['mode'] == 'input' ) {
	$v = new Valitron\Validator($_POST, null, 'ja', '../lib/lang');
	$pd = $_POST;
	$_SESSION['pd'] = $pd;

	$v->labels(array(
		'name' => 'お名前',
		'team' => '所属',
		'email' => 'Eメール',
		'tel' => '電話番号',
		'title' => 'タイトル',
		'body' => '問い合わせ内容',
		'check' => '「ご注意事項」のご確認'
	));

	$v->rule('required', array('name', 'team', 'email', 'tel', 'title', 'body'));
	$v->rule('email', 'email');
	$v->rule('numeric', 'tel');
	$v->rule('required', 'check')->message('{field}をお願いします');

	if($v->validate()) {
     		//echo "Yay! We're all good!";
     		//print_r($v->data());
		header('Location: ./inquiry-confirm.php');
	} else {
     		// Errors
     		//print_r($v->errors());
	}
}

function psv($key) {
	global $pd;
	print $_SESSION['pd'][$key];
}
//function printFeedbackClassName($key) {
function pfc($key) {
	global $v;
	if (empty($v)) {
		print "";
		return;
	}
	$arr = $v->errors();
	if (!is_array($arr)) {
		print "";
		return;
	}
	if (isset($arr[$key])) {
		print "has-warning";
		return;
	} else {
		print "has-success";
		return;
	}
}
//function printFeedbackIcon($key) {
function pfi($key) {
	global $v;
	if (empty($v)) {
		print "";
		return;
	}
	$arr = $v->errors();
	if (!is_array($arr)) {
		print "";
		return;
	}
	if (isset($arr[$key])) {
		print '<span class="glyphicon glyphicon-warning-sign form-control-feedback"></span>';
		return;
	} else {
		print '<span class="glyphicon glyphicon-ok form-control-feedback"></span>';
		return;
	}
}
?>
<pre>
<?php
//var_dump($v);
?>
</pre>

<?php include(dirname(__FILE__) . "/header.tmpl"); ?>

<div class="row stta">
<h2 id="inquiry" class="bg-primary"><span class="glyphicon glyphicon-question-sign"></span>&nbsp;お問い合わせ</h2>
<h3 class="bg-info">お問い合わせの前に（ご注意事項）</h3>
<div class="container-fluid bs-docs-container">
<ul>
<li>
大会個別の運営に関するご質問は受け付けておりません。大会要項記載の窓口へお願い致します。
</li>
<li>
問い合わせ内容は、仙台市卓球協会内のみでの取り扱いとし、ご回答目的以外では利用致しません。
</li>
<li>
原則として電子メールでご回答させて頂きますので、ドメイン"@sendai-tta.info"および"@gmail.com"からのメール受信を許可するように設定してください。
</li>
<li>
ご回答には数日～1週間程度お時間を頂く場合があります。
</li>
</ul>
</div>

<div class="row stta">
<h3 class="bg-info">お問い合わせ内容を入力して下さい。</h3>
<div class="container-fluid bs-docs-container">
<div class="bs-docs-section stta">
<?php
$buf = null;
if (!empty($v)) { $buf = $v->errors(); }
if (is_array($buf) && count($buf) > 0 ) {
?>
<div class="alert alert-danger">
  <ul>
<?php
foreach ($v->errors() as $error) {
	foreach ($error as $value) {
		// var_dump($value);
		print '<li>' . $value . '</li>';
	}
}
?>
  </ul>
</div>
<?php
}
?>

<form id="form1" name="form1" method="post" action="inquiry.php" >
    <input type="hidden" name="mode" value="input">
    <div class="form-group has-feedback <?php pfc('name'); ?>">
      <label for="name">お名前</label>
      <input type="text" name="name" value="<?php psv('name'); ?>" class="form-control" placeholder="仙台卓郎">
      <?php pfi('name'); ?>
    </div>
    <div class="form-group has-feedback <?php pfc('team'); ?>">
      <label for="team">所属</label>
      <input type="text" name="team" value="<?php psv('team'); ?>" class="form-control" placeholder="杜の都クラブ">
      <?php pfi('team'); ?>
    </div>
    <div class="form-group has-feedback <?php pfc('email'); ?>">
      <label for="email">Eメール</label>
      <input type="text" name="email" value="<?php psv('email'); ?>" class="form-control" placeholder="takuro.sendai@gmail.com">
      <?php pfi('email'); ?>
    </div>
    <div class="form-group has-feedback <?php pfc('tel'); ?>">
      <label for="tel">電話番号</label>
      <input type="text" name="tel" value="<?php psv('tel'); ?>" class="form-control" placeholder="09012345678">
      <?php pfi('tel'); ?>
      <p id="phone-help" class="help-block">ハイフン無しで入力して下さい</p>
    </div>
    <div class="form-group has-feedback <?php pfc('title'); ?>">
      <label for="title">お問い合わせタイトル</label>
      <input type="text" name="title" value="<?php psv('title'); ?>" class="form-control">
      <?php pfi('title'); ?>
    </div>
    <div class="form-group has-feedback <?php pfc('body'); ?>">
      <label for="body">お問い合わせ内容</label>
      <textarea name="body" class="form-control" style="height:250px"><?php psv('body'); ?></textarea>
      <?php pfi('body'); ?>
    </div>
    <div class="form-group has-feedback <?php pfc('check'); ?>">
    <label class="checkbox-inline">
      <input name="check" value="1" type="checkbox"> 上記「ご注意事項」について理解しました
    </label>
    </div>
    <!-- 登録ボタンの表示 -->
    <hr>
    <div>
    <button id="btn" type="submit" class="btn btn-default">次へ(送信確認)</button>
    </div>
</form>
</div>
</div>
</div>

<?php include(dirname(__FILE__) . "/footer.tmpl"); ?>
