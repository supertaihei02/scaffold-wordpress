import 'babel-polyfill';  // 各entrypointの先頭で読み込む

class Main {
  constructor() {
    console.log(`Run: ${new Date().toISOString()}`);
    this.onDOMContentLoaded = this.onDOMContentLoaded.bind(this);
  }

  onDOMContentLoaded() {
    console.log(`onDOMContentLoaded: ${new Date().toISOString()}`);

    //setInterval(() => document.body.style.backgroundColor = `hsl(${Math.floor(Math.random() * 360)}, 90%, 70%)`, 300);
    //setInterval(() => document.body.style.backgroundColor = `hsl(${Math.floor(Math.random() * 360)}, 60%, 30%)`, 300);
  }
}

const main = new Main();
window.addEventListener('DOMContentLoaded', main.onDOMContentLoaded);
