const puppeteer = require('puppeteer');

let browser = null;

async function getBrowser() {
    if (!browser) {
        browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu'
            ]
        });
    }
    return browser;
}

async function newPage() {
    const b = await getBrowser();
    const page = await b.newPage();
    await page.setViewport({ width: 1280, height: 720 });
    page.on('console', msg => {
        if (msg.type() === 'error') {
            // suppress non-critical console errors during tests
        }
    });
    return page;
}

async function closeBrowser() {
    if (browser) {
        await browser.close();
        browser = null;
    }
}

module.exports = { getBrowser, newPage, closeBrowser };
