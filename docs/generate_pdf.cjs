const puppeteer = require('puppeteer');
const { PDFDocument } = require('pdf-lib');
const fs   = require('fs');
const path = require('path');

const DOCS_DIR = __dirname;
const OUT      = path.join(DOCS_DIR, 'VMRFDU_Activity_Portal_Complete.pdf');

const HEADER = `
  <div style="font-size:8px;color:#1e3a8a;width:100%;padding:0 10mm;
              border-bottom:1px solid #e2e8f0;padding-bottom:3px;
              font-family:Calibri,Arial,sans-serif;display:flex;justify-content:space-between;">
    <span>VMRF(DU) Institutional Activity Portal — Complete Documentation</span>
    <span style="color:#64748b;font-style:italic;">Under the Guidance of Dr. A. Murugan</span>
  </div>`;

const FOOTER = `
  <div style="font-size:8px;color:#94a3b8;width:100%;padding:0 10mm;
              border-top:1px solid #e2e8f0;padding-top:3px;
              font-family:Calibri,Arial,sans-serif;display:flex;justify-content:space-between;">
    <span>Designed &amp; Developed by: VMRFDU IT Team (Dhinakaran &amp; Sanjai) &nbsp;|&nbsp; © 2026 VMRF(DU)</span>
    <span>Page <span class="pageNumber"></span> / <span class="totalPages"></span></span>
  </div>`;

const SECTIONS = [
  { file: 'flowchart.html',            label: 'System Flowchart & Architecture' },
  { file: 'screenshots_document.html', label: 'Screenshots Documentation (All Roles)' },
];

async function printToPdf(page, htmlFile) {
  const url = 'file:///' + path.join(DOCS_DIR, htmlFile).replace(/\\/g, '/');
  console.log(`  Loading: ${htmlFile}`);
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 90000 });

  // Give SVGs / lazy images a moment to fully render
  await new Promise(r => setTimeout(r, 2000));

  const buf = await page.pdf({
    format: 'A4',
    printBackground: true,
    margin: { top: '14mm', bottom: '14mm', left: '10mm', right: '10mm' },
    displayHeaderFooter: true,
    headerTemplate: HEADER,
    footerTemplate: FOOTER,
  });
  return buf;
}

(async () => {
  console.log('Launching browser…');
  const browser = await puppeteer.launch({ headless: 'new' });
  const page    = await browser.newPage();
  await page.setViewport({ width: 1280, height: 900 });

  const pdfBuffers = [];
  for (const sec of SECTIONS) {
    console.log(`\n📄 Printing: ${sec.label}`);
    const buf = await printToPdf(page, sec.file);
    pdfBuffers.push(buf);
    console.log(`   Done (${(buf.length / 1024).toFixed(0)} KB)`);
  }

  await browser.close();

  // ── Merge all PDFs ──────────────────────────────────────────────────────────
  console.log('\n🔗 Merging PDFs…');
  const merged = await PDFDocument.create();

  for (const buf of pdfBuffers) {
    const src   = await PDFDocument.load(buf);
    const pages = await merged.copyPages(src, src.getPageIndices());
    pages.forEach(p => merged.addPage(p));
  }

  // Set document metadata
  merged.setTitle('VMRF(DU) Activity Portal – Complete Documentation');
  merged.setAuthor('VMRFDU IT Team (Dhinakaran & Sanjai)');
  merged.setSubject('Flowchart, Workflow & Screenshots – All User Roles');
  merged.setKeywords(['VMRFDU', 'Activity Portal', 'Flowchart', 'Screenshots', 'Documentation']);
  merged.setCreator('VMRFDU IT Team');
  merged.setProducer('Puppeteer + pdf-lib');

  const finalBytes = await merged.save();
  fs.writeFileSync(OUT, finalBytes);

  const mb = (finalBytes.length / 1024 / 1024).toFixed(2);
  console.log(`\n✅  PDF generated successfully`);
  console.log(`    File  : ${OUT}`);
  console.log(`    Size  : ${mb} MB`);
  console.log(`    Pages : ${merged.getPageCount()}`);
})().catch(e => { console.error('\n❌ Error:', e.message); process.exit(1); });
