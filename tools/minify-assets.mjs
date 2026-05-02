import fs from 'node:fs';
import path from 'node:path';

const cssSourceDir = 'assets/css/src';
const generatedNotice = '/* Generated from assets/css/src/*.css. Edit source modules, not this file. */';

const css = fs.existsSync(cssSourceDir)
  ? [
      generatedNotice,
      ...fs
        .readdirSync(cssSourceDir)
        .filter((file) => file.endsWith('.css'))
        .sort((a, b) => a.localeCompare(b))
        .map((file) => fs.readFileSync(path.join(cssSourceDir, file), 'utf8').trimEnd()),
    ].join('\n\n') + '\n'
  : fs.readFileSync('assets/css/main.css', 'utf8');
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

fs.writeFileSync('assets/css/main.css', css);
fs.writeFileSync('assets/css/main.min.css', minCss);
fs.writeFileSync('assets/js/main.min.js', minJs);
