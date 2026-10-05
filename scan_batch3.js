const fs = require('fs');

const dirs = [
  'designation', 'salarySheet', 'salaryParameter', 'warehouse',
  'category', 'brand', 'series', 'unit', 'color', 'size'
];

const targetFiles = [];
dirs.forEach(d => {
  ['index.vue', 'create.vue', 'view.vue'].forEach(f => {
    const p = `resources/js/views/admin/${d}/${f}`;
    if (fs.existsSync(p)) targetFiles.push(p);
  });
});

const sharedFiles = [
  'resources/js/components/Form/Checkbox.vue',
  'resources/js/components/elements/Status.vue',
  'resources/js/components/AppBreadcrumb.vue',
  'resources/js/components/AppSidebarRecursive.vue',
  'resources/js/components/Table/ViewBaseTable.vue',
  'resources/js/components/Table/Pagination.vue',
  'resources/js/components/Page/CreateForm.vue',
  'resources/js/components/Page/IndexPage.vue',
  'resources/js/components/Page/ViewPage.vue'
];
sharedFiles.forEach(f => {
  if (fs.existsSync(f)) targetFiles.push(f);
});

const bn = require('./resources/js/lang/bn.js');
const allBnKeys = new Set(Object.keys(bn).map(k => k.toLowerCase().trim()));
if (bn.default) {
  Object.keys(bn.default).forEach(k => allBnKeys.add(k.toLowerCase().trim()));
}

const report = {};

targetFiles.forEach(file => {
  const content = fs.readFileSync(file, 'utf8');
  const issues = {
    missingTableTitles: [],
    missingTKeys: [],
    rawTextInTemplate: [],
    staticAttrs: []
  };

  // 1. Table titles in script
  const colRegex = /title:\s*['"`]([^'"`]+)['"`]/g;
  let match;
  while ((match = colRegex.exec(content)) !== null) {
    const raw = match[1];
    const cleaned = raw.replace(/_/g, ' ').toLowerCase().trim();
    if (!allBnKeys.has(cleaned) && !allBnKeys.has(raw.toLowerCase().trim())) {
      issues.missingTableTitles.push(raw);
    }
  }

  // 2. $t calls
  const tRegex = /\$t\(\s*['"`]([^'"`]+)['"`]\s*\)/g;
  while ((match = tRegex.exec(content)) !== null) {
    const raw = match[1];
    const cleaned = raw.replace(/_/g, ' ').replace(/:$/, '').toLowerCase().trim();
    if (!allBnKeys.has(cleaned) && !allBnKeys.has(raw.toLowerCase().trim())) {
      issues.missingTKeys.push(raw);
    }
  }

  // 3. Template raw text & attributes
  const templateMatch = content.match(/<template>([\s\S]*?)<\/template>/);
  if (templateMatch) {
    const tpl = templateMatch[1];
    
    // Raw text between tags
    const textMatches = tpl.matchAll(/>\s*([A-Za-z][A-Za-z0-9\s\/\-_:,\.]{2,70})\s*</g);
    for (const m of textMatches) {
      const text = m[1].trim();
      if (text.startsWith('v-') || text.startsWith('@') || text.startsWith(':') || text.includes('{{') || text.includes('}}')) continue;
      if (['div', 'span', 'p', 'i', 'b', 'strong', 'td', 'th', 'tr', 'table', 'svg', 'path', 'button', 'input', 'select', 'option'].includes(text.toLowerCase())) continue;
      issues.rawTextInTemplate.push(text);
    }

    // Static attributes
    const attrMatches = tpl.matchAll(/(?<!:)(placeholder|title|label|on-label|off-label)=["']([A-Za-z][A-Za-z0-9\s\/\-_:,\.]{2,70})["']/g);
    for (const m of attrMatches) {
      const attr = m[1];
      const val = m[2].trim();
      const cleaned = val.replace(/_/g, ' ').toLowerCase().trim();
      if (!allBnKeys.has(cleaned) && !allBnKeys.has(val.toLowerCase().trim())) {
        issues.staticAttrs.push({ attr, val });
      }
    }
  }

  report[file] = issues;
});

fs.writeFileSync('scan_batch3_report.json', JSON.stringify(report, null, 2));
console.log('Batch 3 scan report generated.');
