# DB の入出力機能
## 概要
```text
ステージング環境や、本番環境のDBは
基本的にphpMyAdmin等を利用するケースが多く
SSHアカウントはもらえないケースも多い。
そのため、自作のスクリプトは利用できないことを
前提として考える。

だから、このスクリプトは下記のことができる
・LocalのDBのドメインを任意のドメインに変換してExportできる
・任意のドメインのDB情報をLocalドメインに変換してImportできる
```
## <他環境からLocalへのImport>
1. 他環境からダンプを取得
2. `make import-from-production`

## <Localから他環境へのExport>
1. `make export-to-production`  
ダンプ `settings/db/mysql.sql` が出力される
2. ダンプファイルをphpMyAdmin等でImportする


