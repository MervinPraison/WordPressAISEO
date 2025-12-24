/**
 * Playwright Test: Comprehensive Tool Testing with Detailed Logging
 * 
 * Extended from test-all-pages.spec.js (working script)
 * Tests each tool individually with full response capture
 */

const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');
require('dotenv').config({ path: path.resolve(__dirname, '../../.env') });

const WP_URL = process.env.WP_URL || 'https://wordpress.test';
const USERNAME = process.env.WP_USERNAME || 'praison';
const PASSWORD = process.env.WP_PASSWORD || 'leicester';

// Store all test data
const toolResults = [];
const consoleMessages = [];
const ajaxRequests = [];
const ajaxResponses = [];

test.describe('AISEO All Tools - Detailed Testing', () => {
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
    
    // Capture console messages
    page.on('console', msg => {
      const text = msg.text();
      consoleMessages.push({
        type: msg.type(),
        text: text,
        timestamp: new Date().toISOString()
      });
      if (text.includes('AISEO') || text.includes('🔴') || text.includes('🔵') || 
          text.includes('aiseoAdmin') || text.includes('nonce') || text.includes('Nonce')) {
        console.log(`[CONSOLE ${msg.type()}] ${text}`);
      }
    });

    // Capture AJAX requests
    page.on('request', request => {
      if (request.url().includes('admin-ajax.php')) {
        const postData = request.postData();
        if (postData && postData.includes('aiseo_')) {
          const actionMatch = postData.match(/action=([^&]+)/);
          const action = actionMatch ? actionMatch[1] : 'unknown';
          
          ajaxRequests.push({
            action,
            url: request.url(),
            method: request.method(),
            postData: postData,
            timestamp: new Date().toISOString()
          });
          console.log(`[AJAX REQUEST] ${postData.substring(0, 100)}...`);
        }
      }
    });

    // Capture AJAX responses
    page.on('response', async response => {
      if (response.url().includes('admin-ajax.php')) {
        const request = response.request();
        const postData = request.postData();
        if (postData && postData.includes('aiseo_')) {
          const status = response.status();
          let body = '';
          try {
            body = await response.text();
          } catch (e) {
            body = '[Could not read response]';
          }
          
          const actionMatch = postData.match(/action=([^&]+)/);
          const action = actionMatch ? actionMatch[1] : 'unknown';
          
          ajaxResponses.push({
            action,
            status,
            body: body,
            timestamp: new Date().toISOString(),
            postData: postData
          });
          
          console.log(`[AJAX RESPONSE] Status: ${status}, Body: ${body.substring(0, 200)}...`);
          
          if (status === 403 || status === 500) {
            console.error(`❌ ERROR: ${status} response for AJAX request`);
          }
        }
      }
    });

    // Login to WordPress
    console.log('\n========================================');
    console.log('🔐 LOGGING IN TO WORDPRESS');
    console.log('========================================');
    console.log(`URL: ${WP_URL}/wp-admin`);
    console.log(`Username: ${USERNAME}`);
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin`);
    await page.fill('#user_login', USERNAME);
    await page.fill('#user_pass', PASSWORD);
    await page.click('#wp-submit');
    await page.waitForLoadState('networkidle');
    
    const cookies = await context.cookies();
    const wpCookies = cookies.filter(c => c.name.includes('wordpress') || c.name.includes('wp-'));
    console.log(`✅ Login successful - ${wpCookies.length} WordPress cookies set`);
    wpCookies.forEach(c => console.log(`   Cookie: ${c.name}`));
    console.log('');
  });

  test.afterAll(async () => {
    const logsDir = path.resolve(__dirname, '../logs');
    if (!fs.existsSync(logsDir)) {
      fs.mkdirSync(logsDir, { recursive: true });
    }
    
    const reportPath = path.join(logsDir, 'all-tools-detailed-report.json');
    const report = {
      timestamp: new Date().toISOString(),
      summary: {
        totalTools: toolResults.length,
        passed: toolResults.filter(r => r.status === 'PASSED').length,
        failed: toolResults.filter(r => r.status === 'FAILED').length,
        skipped: toolResults.filter(r => r.status === 'SKIPPED').length,
        totalAjaxRequests: ajaxRequests.length,
        totalAjaxResponses: ajaxResponses.length,
        successRate: ((toolResults.filter(r => r.status === 'PASSED').length / toolResults.filter(r => r.status !== 'SKIPPED').length) * 100).toFixed(1) + '%'
      },
      toolResults,
      ajaxRequests,
      ajaxResponses,
      consoleMessages
    };
    
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    console.log(`\n📝 Detailed report saved to: ${reportPath}`);
    
    await page.close();
    await context.close();
  });

  test('1. SEO Tools - Generate Title', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 1: SEO TOOLS - GENERATE TITLE');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.$('#aiseo-meta-post-select');
      if (postSelect) {
        await page.selectOption('#aiseo-meta-post-select', { index: 1 });
        console.log('✅ Post selected');
        
        await page.click('button[data-field="title"]');
        console.log('✅ Generate Title button clicked');
        
        await page.waitForTimeout(6000);
        
        const response = ajaxResponses.find(r => r.action === 'aiseo_generate_title');
        toolResults.push({
          tool: 'Generate Title',
          tab: 'SEO Tools',
          status: response?.status === 200 ? 'PASSED' : 'FAILED',
          httpStatus: response?.status,
          response: response?.body?.substring(0, 500),
          timestamp: new Date().toISOString()
        });
        
        console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
        console.log(`HTTP Status: ${response?.status || 'N/A'}`);
      } else {
        toolResults.push({
          tool: 'Generate Title',
          tab: 'SEO Tools',
          status: 'SKIPPED',
          reason: 'Post select not found',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  Post select not found');
      }
    } catch (error) {
      toolResults.push({
        tool: 'Generate Title',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('2. SEO Tools - Generate Description', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 2: SEO TOOLS - GENERATE DESCRIPTION');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.$('#aiseo-meta-post-select');
      if (postSelect) {
        await page.selectOption('#aiseo-meta-post-select', { index: 1 });
        console.log('✅ Post selected');
        
        const descBtn = await page.$('button[data-field="description"]');
        if (descBtn) {
          await descBtn.click();
          console.log('✅ Generate Description button clicked');
          
          await page.waitForTimeout(6000);
          
          const response = ajaxResponses.find(r => r.action === 'aiseo_generate_description');
          toolResults.push({
            tool: 'Generate Description',
            tab: 'SEO Tools',
            status: response?.status === 200 ? 'PASSED' : 'FAILED',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
          console.log(`HTTP Status: ${response?.status || 'N/A'}`);
        } else {
          toolResults.push({
            tool: 'Generate Description',
            tab: 'SEO Tools',
            status: 'SKIPPED',
            reason: 'Description button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Description button not found');
        }
      } else {
        toolResults.push({
          tool: 'Generate Description',
          tab: 'SEO Tools',
          status: 'SKIPPED',
          reason: 'Post select not found',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  Post select not found');
      }
    } catch (error) {
      toolResults.push({
        tool: 'Generate Description',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('3. Bulk Operations - Generate Titles', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 3: BULK OPERATIONS - GENERATE TITLES');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=bulk-operations`);
    await page.waitForLoadState('networkidle');
    
    try {
      const checkboxes = await page.$$('.aiseo-bulk-post');
      if (checkboxes.length >= 2) {
        await checkboxes[0].check();
        await checkboxes[1].check();
        console.log('✅ Two posts selected');
        
        const generateBtn = await page.$('#aiseo-bulk-generate-titles');
        if (generateBtn) {
          await generateBtn.click();
          console.log('✅ Generate Titles for Selected clicked');
          await page.waitForTimeout(8000);
          
          const responses = ajaxResponses.filter(r => r.action === 'aiseo_generate_title');
          const bulkResponses = responses.slice(-2);
          const allSuccess = bulkResponses.every(r => r.status === 200);
          
          toolResults.push({
            tool: 'Bulk Generate Titles',
            tab: 'Bulk Operations',
            status: allSuccess ? 'PASSED' : 'FAILED',
            postsProcessed: bulkResponses.length,
            responses: bulkResponses.map(r => ({ status: r.status, preview: r.body.substring(0, 200) })),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${allSuccess ? 'PASSED ✅' : 'FAILED ❌'}`);
          console.log(`Posts Processed: ${bulkResponses.length}`);
        } else {
          toolResults.push({
            tool: 'Bulk Generate Titles',
            tab: 'Bulk Operations',
            status: 'SKIPPED',
            reason: 'Generate button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Generate button not found');
        }
      } else {
        toolResults.push({
          tool: 'Bulk Generate Titles',
          tab: 'Bulk Operations',
          status: 'SKIPPED',
          reason: `Not enough posts (found: ${checkboxes.length})`,
          timestamp: new Date().toISOString()
        });
        console.log(`⚠️  Not enough posts (found: ${checkboxes.length})`);
      }
    } catch (error) {
      toolResults.push({
        tool: 'Bulk Generate Titles',
        tab: 'Bulk Operations',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('4. Technical SEO - List Redirects', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 4: TECHNICAL SEO - LIST REDIRECTS');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=technical-seo`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    try {
      const response = ajaxResponses.find(r => r.action === 'aiseo_list_redirects');
      
      if (response) {
        let redirectCount = 0;
        try {
          const data = JSON.parse(response.body);
          redirectCount = data.data?.length || 0;
        } catch (e) {
          // Ignore parse errors
        }
        
        toolResults.push({
          tool: 'List Redirects',
          tab: 'Technical SEO',
          status: response.status === 200 ? 'PASSED' : 'FAILED',
          httpStatus: response.status,
          redirectCount,
          response: response.body.substring(0, 500),
          timestamp: new Date().toISOString()
        });
        
        console.log(`📊 Result: ${response.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
        console.log(`HTTP Status: ${response.status}`);
        console.log(`Redirects Found: ${redirectCount}`);
      } else {
        toolResults.push({
          tool: 'List Redirects',
          tab: 'Technical SEO',
          status: 'SKIPPED',
          reason: 'No AJAX response captured',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  No AJAX response captured');
      }
    } catch (error) {
      toolResults.push({
        tool: 'List Redirects',
        tab: 'Technical SEO',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('5. SEO Tools - Generate Keyword', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 5: SEO TOOLS - GENERATE KEYWORD');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.$('#aiseo-meta-post-select');
      if (postSelect) {
        await page.selectOption('#aiseo-meta-post-select', { index: 1 });
        console.log('✅ Post selected');
        
        const keywordBtn = await page.$('button[data-field="keyword"]');
        if (keywordBtn) {
          await keywordBtn.click();
          console.log('✅ Generate Keyword button clicked');
          await page.waitForTimeout(6000);
          
          const response = ajaxResponses.find(r => r.action === 'aiseo_generate_keyword');
          toolResults.push({
            tool: 'Generate Keyword',
            tab: 'SEO Tools',
            status: response?.status === 200 ? 'PASSED' : 'FAILED',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
        } else {
          toolResults.push({
            tool: 'Generate Keyword',
            tab: 'SEO Tools',
            status: 'SKIPPED',
            reason: 'Button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Button not found');
        }
      }
    } catch (error) {
      toolResults.push({
        tool: 'Generate Keyword',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('6. SEO Tools - Analyze Content', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 6: SEO TOOLS - ANALYZE CONTENT');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.$('#aiseo-analyze-post-select');
      if (postSelect) {
        await page.selectOption('#aiseo-analyze-post-select', { index: 1 });
        console.log('✅ Post selected');
        
        const analyzeBtn = await page.$('.aiseo-analyze-content');
        if (analyzeBtn) {
          await analyzeBtn.click();
          console.log('✅ Analyze Content button clicked');
          await page.waitForTimeout(6000);
          
          const response = ajaxResponses.find(r => r.action === 'aiseo_analyze_content');
          toolResults.push({
            tool: 'Analyze Content',
            tab: 'SEO Tools',
            status: response?.status === 200 ? 'PASSED' : 'FAILED',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
        } else {
          toolResults.push({
            tool: 'Analyze Content',
            tab: 'SEO Tools',
            status: 'SKIPPED',
            reason: 'Button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Button not found');
        }
      }
    } catch (error) {
      toolResults.push({
        tool: 'Analyze Content',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('7. SEO Tools - Get Linking Suggestions', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 7: SEO TOOLS - LINKING SUGGESTIONS');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.$('#aiseo-linking-post-select');
      if (postSelect) {
        await page.selectOption('#aiseo-linking-post-select', { index: 1 });
        console.log('✅ Post selected');
        
        const linkingBtn = await page.$('.aiseo-get-linking');
        if (linkingBtn) {
          await linkingBtn.click();
          console.log('✅ Get Linking Suggestions button clicked');
          await page.waitForTimeout(6000);
          
          const response = ajaxResponses.find(r => r.action.includes('linking'));
          toolResults.push({
            tool: 'Get Linking Suggestions',
            tab: 'SEO Tools',
            status: response?.status === 200 ? 'PASSED' : 'NO_RESPONSE',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            note: 'May not trigger AJAX if no suggestions found',
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'NO RESPONSE ⚠️'}`);
        } else {
          toolResults.push({
            tool: 'Get Linking Suggestions',
            tab: 'SEO Tools',
            status: 'SKIPPED',
            reason: 'Button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Button not found');
        }
      }
    } catch (error) {
      toolResults.push({
        tool: 'Get Linking Suggestions',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('8. SEO Tools - Title Variations', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 8: SEO TOOLS - TITLE VARIATIONS');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.$('#aiseo-variations-post-select');
      if (postSelect) {
        await page.selectOption('#aiseo-variations-post-select', { index: 1 });
        console.log('✅ Post selected');
        
        const variationsBtn = await page.$('.aiseo-get-title-variations');
        if (variationsBtn) {
          // Handle potential popup
          page.once('dialog', async dialog => {
            console.log(`🔔 Popup detected: ${dialog.message()}`);
            await dialog.accept();
          });
          
          await variationsBtn.click();
          console.log('✅ Generate Title Variations button clicked');
          await page.waitForTimeout(6000);
          
          const response = ajaxResponses.find(r => r.action.includes('variation') || r.action.includes('title'));
          toolResults.push({
            tool: 'Title Variations',
            tab: 'SEO Tools',
            status: response?.status === 200 ? 'PASSED' : 'NO_RESPONSE',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'NO RESPONSE ⚠️'}`);
        } else {
          toolResults.push({
            tool: 'Title Variations',
            tab: 'SEO Tools',
            status: 'SKIPPED',
            reason: 'Button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Button not found');
        }
      }
    } catch (error) {
      toolResults.push({
        tool: 'Title Variations',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('9. SEO Tools - Description Variations', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 9: SEO TOOLS - DESCRIPTION VARIATIONS');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.$('#aiseo-variations-post-select');
      if (postSelect) {
        await page.selectOption('#aiseo-variations-post-select', { index: 1 });
        console.log('✅ Post selected');
        
        const variationsBtn = await page.$('.aiseo-get-desc-variations');
        if (variationsBtn) {
          // Handle potential popup
          page.once('dialog', async dialog => {
            console.log(`🔔 Popup detected: ${dialog.message()}`);
            await dialog.accept();
          });
          
          await variationsBtn.click();
          console.log('✅ Generate Description Variations button clicked');
          await page.waitForTimeout(6000);
          
          const response = ajaxResponses.find(r => r.action.includes('variation') || r.action.includes('description'));
          toolResults.push({
            tool: 'Description Variations',
            tab: 'SEO Tools',
            status: response?.status === 200 ? 'PASSED' : 'NO_RESPONSE',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'NO RESPONSE ⚠️'}`);
        } else {
          toolResults.push({
            tool: 'Description Variations',
            tab: 'SEO Tools',
            status: 'SKIPPED',
            reason: 'Button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Button not found');
        }
      }
    } catch (error) {
      toolResults.push({
        tool: 'Description Variations',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('10. AI Content - Generate Post', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 10: AI CONTENT - GENERATE POST');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=ai-content`);
    await page.waitForLoadState('networkidle');
    
    try {
      const topicInput = await page.$('#aiseo-content-topic');
      if (topicInput) {
        await page.fill('#aiseo-content-topic', 'Playwright Test Topic');
        console.log('✅ Topic entered');
        
        const generateBtn = await page.$('#aiseo-generate-content');
        if (generateBtn) {
          await generateBtn.click();
          console.log('✅ Generate Post button clicked');
          await page.waitForTimeout(8000);
          
          const response = ajaxResponses.find(r => r.action === 'aiseo_create_post');
          toolResults.push({
            tool: 'Generate Post',
            tab: 'AI Content',
            status: response?.status === 200 ? 'PASSED' : 'FAILED',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
        } else {
          toolResults.push({
            tool: 'Generate Post',
            tab: 'AI Content',
            status: 'SKIPPED',
            reason: 'Button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Button not found');
        }
      }
    } catch (error) {
      toolResults.push({
        tool: 'Generate Post',
        tab: 'AI Content',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('11. Technical SEO - Add Redirect', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 11: TECHNICAL SEO - ADD REDIRECT');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=technical-seo`);
    await page.waitForLoadState('networkidle');
    
    try {
      const fromInput = await page.$('input[name="redirect_from"]');
      const toInput = await page.$('input[name="redirect_to"]');
      
      if (fromInput && toInput) {
        await page.fill('input[name="redirect_from"]', `/test-playwright-${Date.now()}`);
        await page.fill('input[name="redirect_to"]', '/');
        console.log('✅ Redirect URLs entered');
        
        const addBtn = await page.$('button:has-text("Add Redirect")');
        if (addBtn) {
          await addBtn.click();
          console.log('✅ Add Redirect button clicked');
          await page.waitForTimeout(3000);
          
          const response = ajaxResponses.find(r => r.action === 'aiseo_add_redirect');
          toolResults.push({
            tool: 'Add Redirect',
            tab: 'Technical SEO',
            status: response?.status === 200 ? 'PASSED' : 'FAILED',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
        } else {
          toolResults.push({
            tool: 'Add Redirect',
            tab: 'Technical SEO',
            status: 'SKIPPED',
            reason: 'Button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Button not found');
        }
      }
    } catch (error) {
      toolResults.push({
        tool: 'Add Redirect',
        tab: 'Technical SEO',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('12. Technical SEO - Regenerate Sitemap', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 12: TECHNICAL SEO - REGENERATE SITEMAP');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=technical-seo`);
    await page.waitForLoadState('networkidle');
    
    try {
      const regenBtn = await page.$('#aiseo-regenerate-sitemap');
      if (regenBtn) {
        await regenBtn.click();
        console.log('✅ Regenerate Sitemap button clicked');
        await page.waitForTimeout(3000);
        
        const response = ajaxResponses.find(r => r.action === 'aiseo_regenerate_sitemap');
        toolResults.push({
          tool: 'Regenerate Sitemap',
          tab: 'Technical SEO',
          status: response?.status === 200 ? 'PASSED' : 'NO_RESPONSE',
          httpStatus: response?.status,
          response: response?.body?.substring(0, 500),
          timestamp: new Date().toISOString()
        });
        
        console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'NO RESPONSE ⚠️'}`);
      } else {
        toolResults.push({
          tool: 'Regenerate Sitemap',
          tab: 'Technical SEO',
          status: 'SKIPPED',
          reason: 'Button not found',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  Button not found');
      }
    } catch (error) {
      toolResults.push({
        tool: 'Regenerate Sitemap',
        tab: 'Technical SEO',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('13. Advanced - Save CPT Settings', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 13: ADVANCED - SAVE CPT SETTINGS');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=advanced`);
    await page.waitForLoadState('networkidle');
    
    try {
      const saveBtn = await page.$('button:has-text("Save Post Type Settings")');
      if (saveBtn) {
        await saveBtn.click();
        console.log('✅ Save Post Type Settings button clicked');
        await page.waitForTimeout(3000);
        
        const response = ajaxResponses.find(r => r.action === 'aiseo_save_cpt_settings');
        toolResults.push({
          tool: 'Save CPT Settings',
          tab: 'Advanced',
          status: response?.status === 200 ? 'PASSED' : 'NO_RESPONSE',
          httpStatus: response?.status,
          response: response?.body?.substring(0, 500),
          timestamp: new Date().toISOString()
        });
        
        console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'NO RESPONSE ⚠️'}`);
      } else {
        toolResults.push({
          tool: 'Save CPT Settings',
          tab: 'Advanced',
          status: 'SKIPPED',
          reason: 'Button not found',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  Button not found');
      }
    } catch (error) {
      toolResults.push({
        tool: 'Save CPT Settings',
        tab: 'Advanced',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('14. Advanced - Generate Report', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 14: ADVANCED - GENERATE REPORT');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=advanced`);
    await page.waitForLoadState('networkidle');
    
    try {
      const reportBtn = await page.$('#aiseo-generate-report');
      if (reportBtn) {
        // Handle potential popup
        page.once('dialog', async dialog => {
          console.log(`🔔 Popup detected: ${dialog.message()}`);
          await dialog.accept();
        });
        
        await reportBtn.click();
        console.log('✅ Generate Full Report button clicked');
        await page.waitForTimeout(6000);
        
        const response = ajaxResponses.find(r => r.action === 'aiseo_generate_report');
        toolResults.push({
          tool: 'Generate Report',
          tab: 'Advanced',
          status: response?.status === 200 ? 'PASSED' : 'NO_RESPONSE',
          httpStatus: response?.status,
          response: response?.body?.substring(0, 500),
          note: 'May show popup instead of AJAX',
          timestamp: new Date().toISOString()
        });
        
        console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'NO RESPONSE ⚠️'}`);
      } else {
        toolResults.push({
          tool: 'Generate Report',
          tab: 'Advanced',
          status: 'SKIPPED',
          reason: 'Button not found',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  Button not found');
      }
    } catch (error) {
      toolResults.push({
        tool: 'Generate Report',
        tab: 'Advanced',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('15. Advanced - Research Keyword', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 15: ADVANCED - RESEARCH KEYWORD');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=advanced`);
    await page.waitForLoadState('networkidle');
    
    try {
      const keywordInput = await page.$('input[placeholder*="keyword"]');
      if (keywordInput) {
        await page.fill('input[placeholder*="keyword"]', 'test keyword');
        console.log('✅ Keyword entered');
        
        const researchBtn = await page.$('button:has-text("Research Keyword")');
        if (researchBtn) {
          await researchBtn.click();
          console.log('✅ Research Keyword button clicked');
          await page.waitForTimeout(6000);
          
          const response = ajaxResponses.find(r => r.action === 'aiseo_keyword_research');
          toolResults.push({
            tool: 'Research Keyword',
            tab: 'Advanced',
            status: response?.status === 200 ? 'PASSED' : 'NO_RESPONSE',
            httpStatus: response?.status,
            response: response?.body?.substring(0, 500),
            timestamp: new Date().toISOString()
          });
          
          console.log(`📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'NO RESPONSE ⚠️'}`);
        } else {
          toolResults.push({
            tool: 'Research Keyword',
            tab: 'Advanced',
            status: 'SKIPPED',
            reason: 'Button not found',
            timestamp: new Date().toISOString()
          });
          console.log('⚠️  Button not found');
        }
      }
    } catch (error) {
      toolResults.push({
        tool: 'Research Keyword',
        tab: 'Advanced',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`❌ Error: ${error.message}`);
    }
  });

  test('99. Summary - Display Results', async () => {
    console.log('\n' + '='.repeat(70));
    console.log('📊 COMPREHENSIVE TOOL TEST SUMMARY');
    console.log('='.repeat(70));
    
    const passed = toolResults.filter(r => r.status === 'PASSED').length;
    const failed = toolResults.filter(r => r.status === 'FAILED').length;
    const skipped = toolResults.filter(r => r.status === 'SKIPPED').length;
    const tested = toolResults.length - skipped;
    
    console.log(`\n📈 STATISTICS:`);
    console.log(`   Total Tools: ${toolResults.length}`);
    console.log(`   ✅ Passed: ${passed}`);
    console.log(`   ❌ Failed: ${failed}`);
    console.log(`   ⚠️  Skipped: ${skipped}`);
    console.log(`   Success Rate: ${tested > 0 ? ((passed / tested) * 100).toFixed(1) : 0}%`);
    
    console.log(`\n📡 AJAX STATISTICS:`);
    console.log(`   Total Requests: ${ajaxRequests.length}`);
    console.log(`   Total Responses: ${ajaxResponses.length}`);
    console.log(`   Status 200: ${ajaxResponses.filter(r => r.status === 200).length}`);
    console.log(`   Status 403: ${ajaxResponses.filter(r => r.status === 403).length}`);
    console.log(`   Status 500: ${ajaxResponses.filter(r => r.status === 500).length}`);
    
    console.log(`\n💬 CONSOLE STATISTICS:`);
    console.log(`   Total Messages: ${consoleMessages.length}`);
    console.log(`   Errors: ${consoleMessages.filter(m => m.type === 'error').length}`);
    console.log(`   Warnings: ${consoleMessages.filter(m => m.type === 'warning').length}`);
    
    console.log('\n' + '-'.repeat(70));
    console.log('DETAILED RESULTS:');
    console.log('-'.repeat(70));
    
    toolResults.forEach((result, index) => {
      const icon = result.status === 'PASSED' ? '✅' : result.status === 'FAILED' ? '❌' : '⚠️';
      console.log(`\n${index + 1}. ${icon} ${result.tool} (${result.tab})`);
      console.log(`   Status: ${result.status}`);
      if (result.httpStatus) console.log(`   HTTP: ${result.httpStatus}`);
      if (result.postsProcessed) console.log(`   Posts: ${result.postsProcessed}`);
      if (result.redirectCount !== undefined) console.log(`   Redirects: ${result.redirectCount}`);
      if (result.response) console.log(`   Response: ${result.response.substring(0, 100)}...`);
      if (result.error) console.log(`   Error: ${result.error}`);
      if (result.reason) console.log(`   Reason: ${result.reason}`);
    });
    
    console.log('\n' + '='.repeat(70));
    console.log('📝 Full report: tests/logs/all-tools-detailed-report.json');
    console.log('='.repeat(70) + '\n');
  });
});
