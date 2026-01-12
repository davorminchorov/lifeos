<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BrowserlessService
{
    protected string $apiKey;
    protected string $apiEndpoint;
    protected int $timeout;

    public function __construct()
    {
        $this->validateCredentials();

        $this->apiKey = config('browserless.api_key');
        $this->apiEndpoint = config('browserless.api_endpoint');
        $this->timeout = config('browserless.timeout');
    }

    /**
     * Validate that all required credentials are configured.
     *
     * @throws RuntimeException
     */
    protected function validateCredentials(): void
    {
        if (empty(config('browserless.api_key'))) {
            throw new RuntimeException('Browserless API key is not configured. Please set BROWSERLESS_API_KEY in your .env file.');
        }

        if (empty(config('browserless.investor_portal.username'))) {
            throw new RuntimeException('Investor portal username is not configured. Please set INVESTOR_PORTAL_USERNAME in your .env file.');
        }

        if (empty(config('browserless.investor_portal.password'))) {
            throw new RuntimeException('Investor portal password is not configured. Please set INVESTOR_PORTAL_PASSWORD in your .env file.');
        }
    }

    /**
     * Crawl the investor portal and extract fund data.
     *
     * @return array
     * @throws RuntimeException
     */
    public function crawlInvestorPortal(): array
    {
        $loginUrl = config('browserless.investor_portal.url');
        $username = config('browserless.investor_portal.username');
        $password = config('browserless.investor_portal.password');

        Log::info('Starting investor portal crawl', [
            'url' => $loginUrl,
        ]);

        // Puppeteer script to login and extract data
        $script = $this->getPuppeteerScript($loginUrl, $username, $password);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->apiEndpoint}/function?token={$this->apiKey}", [
                    'code' => $script,
                ]);

            if (!$response->successful()) {
                $errorMessage = $response->json('message') ?? $response->body();
                Log::error('Browserless API request failed', [
                    'status' => $response->status(),
                    'error' => $errorMessage,
                ]);

                throw new RuntimeException("Browserless API request failed: {$errorMessage}");
            }

            $data = $response->json();

            if (isset($data['error'])) {
                Log::error('Crawl script error', ['error' => $data['error']]);
                throw new RuntimeException("Crawl failed: {$data['error']}");
            }

            Log::info('Investor portal crawl completed successfully', [
                'funds_count' => count($data['funds'] ?? []),
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('Exception during investor portal crawl', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new RuntimeException("Failed to crawl investor portal: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Generate the Puppeteer script for crawling the investor portal.
     *
     * @param string $loginUrl
     * @param string $username
     * @param string $password
     * @return string
     */
    protected function getPuppeteerScript(string $loginUrl, string $username, string $password): string
    {
        // Safely escape credentials for JavaScript using json_encode
        $usernameEscaped = json_encode($username, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $passwordEscaped = json_encode($password, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<JS
module.exports = async ({ page }) => {
    try {
        // Navigate to login page
        await page.goto('{$loginUrl}', { waitUntil: 'networkidle2', timeout: 30000 });

        // Wait for login form to be visible
        await page.waitForSelector('input[type="text"], input[name*="korisnik"], input[name*="user"]', { timeout: 10000 });

        // Find and fill in the username field
        const usernameFields = await page.$$('input[type="text"]');
        if (usernameFields.length > 0) {
            await usernameFields[0].type({$usernameEscaped});
        }

        // Find and fill in the password field
        const passwordFields = await page.$$('input[type="password"]');
        if (passwordFields.length > 0) {
            await passwordFields[0].type({$passwordEscaped});
        }

        // Find and click the login button
        const submitButton = await page.$('input[type="submit"], button[type="submit"]');
        if (submitButton) {
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 }),
                submitButton.click()
            ]);
        }

        // Wait a bit for dashboard to load
        await page.waitForTimeout(3000);

        // Check if we're logged in by looking for common dashboard elements
        const currentUrl = page.url();

        // Extract all fund data from the dashboard
        const fondsData = await page.evaluate(() => {
            const data = {
                url: window.location.href,
                title: document.title,
                funds: [],
                tables: []
            };

            // Try to extract data from tables
            const tables = document.querySelectorAll('table');
            tables.forEach((table, tableIndex) => {
                const tableData = {
                    index: tableIndex,
                    headers: [],
                    rows: []
                };

                // Extract headers
                const headers = table.querySelectorAll('th');
                headers.forEach(header => {
                    tableData.headers.push(header.textContent.trim());
                });

                // Extract rows
                const rows = table.querySelectorAll('tr');
                rows.forEach((row, rowIndex) => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        const rowData = [];
                        cells.forEach(cell => {
                            rowData.push(cell.textContent.trim());
                        });
                        tableData.rows.push(rowData);
                    }
                });

                if (tableData.headers.length > 0 || tableData.rows.length > 0) {
                    data.tables.push(tableData);
                }
            });

            // Try to extract fund cards or divs with fund information
            const fundElements = document.querySelectorAll('.fund, .fond, [class*="fund"], [class*="fond"]');
            fundElements.forEach(element => {
                data.funds.push({
                    html: element.innerHTML,
                    text: element.textContent.trim()
                });
            });

            // If no specific fund elements, try to get all text content
            if (data.funds.length === 0 && data.tables.length === 0) {
                const bodyText = document.body.textContent;
                data.rawContent = bodyText.trim();
            }

            return data;
        });

        // Take a screenshot for debugging
        const screenshot = await page.screenshot({ encoding: 'base64', fullPage: false });

        return {
            success: true,
            url: currentUrl,
            funds: fondsData.funds,
            tables: fondsData.tables,
            title: fondsData.title,
            rawContent: fondsData.rawContent,
            screenshot: screenshot
        };

    } catch (error) {
        return {
            success: false,
            error: error.message,
            stack: error.stack
        };
    }
};
JS;
    }

    /**
     * Test the browserless connection.
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->apiEndpoint}/pressure?token={$this->apiKey}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Browserless connection test failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
