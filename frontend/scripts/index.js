import 'babel-polyfill';  // 各entrypointの先頭で読み込むが、サイズがデカイので場合によっては丸ごと外して必要なpolyfillのみ読み込む

class Main {
  constructor() {
    console.log(`Run: ${new Date().toISOString()}`);
    this.onDOMContentLoaded = this.onDOMContentLoaded.bind(this);
  }

  onDOMContentLoaded() {
    console.log(`onDOMContentLoaded: ${new Date().toISOString()}`);
  }
}

const main = new Main();
window.addEventListener('DOMContentLoaded', main.onDOMContentLoaded);
