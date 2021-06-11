# QdmailをPHP7.0に対応させる

- コンストラクタ名を__constructorに修正
- newの前の&を削除
- QdmailComponent内のfunction & smtpObjectの引数をスーパークラスと合わせて$null = falseに修正
- HTMLメールでのマルチパート順序をhtml, plain, OMITからplain, html, OMITへ変更
- iPhone用アドレスをi.softbank.ne.jpからi.softbank.jpに修正