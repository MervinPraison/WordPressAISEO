/**
 * Playwright Test: Extract All Admin UI Elements
 * 
 * This script discovers all available UI elements across AISEO admin pages
 * Use this to understand what elements are available for testing
 */

const { test } = require('@playwright/test');
const path = require('path');
const fs = require('fs');
require('dotenv').config({ path: path.resolve(__dirname, '../../.env') });

const WP_URL = process.env.WP_URL || 'https://wordpress.test';
const USERNAME = process.env.WP_USERNAME || 'praison';
const PASSWORD = process.env.WP_PASSWORD || 'leicester';

const discoveredElements = {};

test.describe('AISEO Admin - Element Discovery', () => {
  let page;
  let context;

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext({
      ignoreHTTPSErrors: true,
      extraHTTPHeaders: {
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      }
    });
    page = await context.newPage();

    // Login
    console.log('\n🔐 Logging in to WordPress...');
    await page.goto(`${WP_URL}/wp-admin`);
    await page.fill('#user_login', USERNAME);
    await page.fill('#user_pass', PASSWORD);
    await page.click('#wp-submit');
    await page.waitForLoadState('networkidle');
    console.log('✅ Login successful\n');
  });

  test.afterAll(async () => {
    const logsDir = path.resolve(__dirname, '../logs');
    if (!fs.existsSync(logsDir)) {
      fs.mkdirSync(logsDir, { recursive: true });
    }
    
    const reportPath = path.join(logsDir, 'discovered-elements.json');
    fs.writeFileSync(reportPath, JSON.stringify(discoveredElements, null, 2));
    
    console.log('\n' + '='.repeat(70));
    console.log('📋 ELEMENT DISCOVERY SUMMARY');
    console.log('='.repeat(70));
    
    Object.keys(discoveredElements).forEach(tab => {
      console.log(`\n${tab}:`);
      const elements = discoveredElements[tab];
      console.log(`  Buttons: ${elements.buttons.length}`);
      console.log(`  Inputs: ${elements.inputs.length}`);
      console.log(`  Selects: ${elements.selects.length}`);
      console.log(`  Textareas: ${elements.textareas.length}`);
      console.log(`  Forms: ${elements.forms.length}`);
    });
    
    console.log('\n' + '='.repeat(70));
    console.log(`📝 Full report saved to: ${reportPath}`);
    console.log('='.repeat(70) + '\n');
    
    await page.close();
    await context.close();
  });

  async function discoverElements(tabName, tabUrl) {
    console.log(`\n${'='.repeat(70)}`);
    console.log(`🔍 DISCOVERING: ${tabName}`);
    console.log('='.repeat(70));
    
    await page.goto(tabUrl);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(1000);
    
    const elements = {
      buttons: [],
      inputs: [],
      selects: [],
      textareas: [],
      forms: [],
      links: []
    };
    
    // Discover buttons
    const buttons = await page.$$('button, input[type="button"], input[type="submit"]');
    for (const button of buttons) {
      try {
        const text = await button.innerText();
        const id = await button.getAttribute('id');
        const className = await button.getAttribute('class');
        const dataField = await button.getAttribute('data-field');
        const type = await button.getAttribute('type');
        
        elements.buttons.push({
          text: text?.trim(),
          id,
          class: className,
          dataField,
          type,
          selector: id ? `#${id}` : className ? `.${className.split(' ')[0]}` : null
        });
      } catch (e) {
        // Skip if element is stale
      }
    }
    
    // Discover inputs
    const inputs = await page.$$('input[type="text"], input[type="email"], input[type="url"], input[type="number"]');
    for (const input of inputs) {
      try {
        const id = await input.getAttribute('id');
        const name = await input.getAttribute('name');
        const placeholder = await input.getAttribute('placeholder');
        const className = await input.getAttribute('class');
        
        elements.inputs.push({
          id,
          name,
          placeholder,
          class: className,
          selector: id ? `#${id}` : name ? `input[name="${name}"]` : null
        });
      } catch (e) {
        // Skip if element is stale
      }
    }
    
    // Discover selects
    const selects = await page.$$('select');
    for (const select of selects) {
      try {
        const id = await select.getAttribute('id');
        const name = await select.getAttribute('name');
        const className = await select.getAttribute('class');
        const options = await select.$$('option');
        const optionCount = options.length;
        
        elements.selects.push({
          id,
          name,
          class: className,
          optionCount,
          selector: id ? `#${id}` : name ? `select[name="${name}"]` : null
        });
      } catch (e) {
        // Skip if element is stale
      }
    }
    
    // Discover textareas
    const textareas = await page.$$('textarea');
    for (const textarea of textareas) {
      try {
        const id = await textarea.getAttribute('id');
        const name = await textarea.getAttribute('name');
        const placeholder = await textarea.getAttribute('placeholder');
        const className = await textarea.getAttribute('class');
        
        elements.textareas.push({
          id,
          name,
          placeholder,
          class: className,
          selector: id ? `#${id}` : name ? `textarea[name="${name}"]` : null
        });
      } catch (e) {
        // Skip if element is stale
      }
    }
    
    // Discover forms
    const forms = await page.$$('form');
    for (const form of forms) {
      try {
        const id = await form.getAttribute('id');
        const action = await form.getAttribute('action');
        const method = await form.getAttribute('method');
        const className = await form.getAttribute('class');
        
        elements.forms.push({
          id,
          action,
          method,
          class: className,
          selector: id ? `#${id}` : null
        });
      } catch (e) {
        // Skip if element is stale
      }
    }
    
    // Print summary
    console.log(`\n📊 Found:`);
    console.log(`   Buttons: ${elements.buttons.length}`);
    console.log(`   Inputs: ${elements.inputs.length}`);
    console.log(`   Selects: ${elements.selects.length}`);
    console.log(`   Textareas: ${elements.textareas.length}`);
    console.log(`   Forms: ${elements.forms.length}`);
    
    // Print key elements
    if (elements.buttons.length > 0) {
      console.log(`\n   Key Buttons:`);
      elements.buttons.slice(0, 5).forEach(btn => {
        if (btn.text) console.log(`     - "${btn.text}" ${btn.selector || ''}`);
      });
    }
    
    if (elements.selects.length > 0) {
      console.log(`\n   Key Selects:`);
      elements.selects.forEach(sel => {
        console.log(`     - ${sel.selector || sel.name} (${sel.optionCount} options)`);
      });
    }
    
    discoveredElements[tabName] = elements;
  }

  test('1. Dashboard', async () => {
    await discoverElements('Dashboard', `${WP_URL}/wp-admin/admin.php?page=aiseo&tab=dashboard`);
  });

  test('2. SEO Tools', async () => {
    await discoverElements('SEO Tools', `${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
  });

  test('3. AI Content', async () => {
    await discoverElements('AI Content', `${WP_URL}/wp-admin/admin.php?page=aiseo&tab=ai-content`);
  });

  test('4. Bulk Operations', async () => {
    await discoverElements('Bulk Operations', `${WP_URL}/wp-admin/admin.php?page=aiseo&tab=bulk-operations`);
  });

  test('5. Technical SEO', async () => {
    await discoverElements('Technical SEO', `${WP_URL}/wp-admin/admin.php?page=aiseo&tab=technical-seo`);
  });

  test('6. Advanced', async () => {
    await discoverElements('Advanced', `${WP_URL}/wp-admin/admin.php?page=aiseo&tab=advanced`);
  });

  test('7. Monitoring', async () => {
    await discoverElements('Monitoring', `${WP_URL}/wp-admin/admin.php?page=aiseo&tab=monitoring`);
  });

  test('8. Settings', async () => {
    await discoverElements('Settings', `${WP_URL}/wp-admin/admin.php?page=aiseo&tab=settings`);
  });
});
