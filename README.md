# wordpress+Vue.js theme
forked by FRAME LUNCH scaffold for wordpress project

## 何

よりシンプルにcomposer、デフォルトプラグインを削除し
themeにVue starterを設置して素早くwordpressテンプレートを作成する事に特化した。  
WP REST APIとvue-routerにてページ遷移を行うよう調整し体感遷移速度も上がる。

## 動かす

1. `cp .env.sample .env`
    * 必要に応じてPortやコンテナ名を変更する
1. `make`
1. `yarn start`
1. 外観 > テーマ > vuejs-wordpress
1. `cd html/wp-content/themes/vuejs-wordpress`
1. `npm install`
1. `npm run watch`で更新検知
1. `npm run production`でデプロイファイル生成

## close

```bash
$ cd path/to/running/project
$ docker-compose down
```


## DBの状態をGit管理したいとき

DBファイルをバックアップ取ってやります。

```bash
# make export
```


