import fs from 'node:fs';

const css = fs.readFileSync('assets/css/main.css', 'utf8');
const js = fs.readFileSync('assets/js/main.js', 'utf8');

const minCss = css
  .replace(/\/\*[\s\S]*?\*\//g, '')
  .replace(/\s+/g, ' ')
  .replace(/\s*([{}:;,>])\s*/g, '$1')
  .replace(/;}/g, '}')
  .trim();

const minJs = js
  .replace(/\/\*[\s\S]*?\*\//g, '')
  .replace(/^\s*\/\/.*$/gm, '')
  .trim();

fs.writeFileSync('assets/css/main.min.css', minCss);
fs.writeFileSync('assets/js/main.min.js', minJs);
