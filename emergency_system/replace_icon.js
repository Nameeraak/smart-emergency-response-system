const fs = require('fs');
const path = require('path');
const svg = '<svg class="brand-icon" style="width:22px;height:22px;color:var(--red);margin-right:4px;vertical-align:text-bottom;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 18v-6a5 5 0 1 1 10 0v6"/><path d="M5 21a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-1a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2z"/><path d="M21 12h1"/><path d="M18.5 4.5 18 5"/><path d="M2 12h1"/><path d="M12 2v1"/><path d="m4.929 4.929.707.707"/><path d="M12 12v6"/></svg>';

function walk(dir) {
  let results = [];
  let list = fs.readdirSync(dir);
  list.forEach(function(file) {
    file = dir + '/' + file;
    let stat = fs.statSync(file);
    if (stat && stat.isDirectory()) { 
      results = results.concat(walk(file));
    } else if (file.endsWith('.php')) { 
      results.push(file);
    }
  });
  return results;
}

const files = walk('.');
let count = 0;
for (let file of files) {
  let content = fs.readFileSync(file, 'utf8');
  // replace <span class="siren">🚨</span> with svg
  let newContent = content.replace(/<span class="siren">.*?<\/span>/g, svg);
  
  // also replace the specific SVG in index.php, login.php, register.php, admin/dashboard.php
  const oldSvg1 = '<svg class="brand-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">\r\n        <path d="M12 2v20" />\r\n        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />\r\n      </svg>';
  const oldSvg2 = '<svg class="brand-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">\n        <path d="M12 2v20" />\n        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />\n      </svg>';
  const oldSvg3 = '<svg class="brand-icon" style="width:22px;height:22px;color:var(--red);" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>';
  const oldSvg4 = '<svg class="brand-icon" style="width:22px;height:22px;color:var(--red);" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>';
  const oldSvg5 = '<svg class="brand-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>';
  
  newContent = newContent.replace(oldSvg1, svg);
  newContent = newContent.replace(oldSvg2, svg);
  newContent = newContent.replace(oldSvg3, svg);
  newContent = newContent.replace(oldSvg4, svg);
  newContent = newContent.replace(oldSvg5, svg);

  if (content !== newContent) {
    fs.writeFileSync(file, newContent, 'utf8');
    console.log('Replaced icon in ' + file);
    count++;
  }
}
console.log('Updated ' + count + ' files.');
