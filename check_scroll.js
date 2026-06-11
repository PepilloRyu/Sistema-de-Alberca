const {execFileSync}=require('child_process');
// Use chromium remote? Instead use puppeteer? Not installed. We'll use Chrome DevTools Protocol via --headless --dump-dom? Need JS eval? Use playwright not installed likely. Try selenium? We'll use a small cdp client? Better install? no internet. Check if puppeteer installed local/global.
try { require.resolve('puppeteer'); console.log('puppeteer yes'); } catch(e) { console.log('puppeteer no'); }
try { require.resolve('playwright'); console.log('playwright yes'); } catch(e) { console.log('playwright no'); }
