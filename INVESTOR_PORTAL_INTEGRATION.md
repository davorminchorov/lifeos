# Investor Portal Integration with Browserless.io

This document describes the integration with the Macedonian investor portal (investor.wvpfondovi.mk) using Browserless.io for web scraping.

## Overview

The integration allows you to automatically crawl and extract fund data from the investor portal dashboard. It uses Browserless.io to run Puppeteer scripts that:
1. Log in to the investor portal using your credentials
2. Navigate to the dashboard
3. Extract all fund data from tables and other elements
4. Store the data in the database for analysis
5. Take screenshots for debugging

## Setup

### 1. Configure Environment Variables

Add the following variables to your `.env` file:

```env
# Browserless.io Configuration
BROWSERLESS_API_KEY=your_browserless_api_key_here
BROWSERLESS_API_ENDPOINT=https://chrome.browserless.io
BROWSERLESS_TIMEOUT=60

# Investor Portal Credentials
INVESTOR_PORTAL_USERNAME=your_portal_username
INVESTOR_PORTAL_PASSWORD=your_portal_password
```

### 2. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `browserless_connections` - Stores connection configurations
- `investor_data` - Stores scraped data from each crawl

### 3. Get a Browserless.io API Key

1. Sign up at [https://browserless.io](https://browserless.io)
2. Get your API key from the dashboard
3. Add it to your `.env` file as `BROWSERLESS_API_KEY`

## Usage

### Via Web Interface

1. Navigate to Settings → Investor Portal
2. Click "Connect" to enable the integration
3. Click "Sync Now" to trigger a crawl
4. View the crawled data in the history

### Via CLI

```bash
# Crawl for the first user (dispatches a job)
php artisan investor:crawl

# Crawl for a specific user
php artisan investor:crawl --user-id=1

# Run synchronously (useful for testing)
php artisan investor:crawl --sync
```

### Via Code

```php
use App\Models\BrowserlessConnection;
use App\Jobs\FetchInvestorData;

// Get or create connection
$connection = auth()->user()->browserlessConnections()->firstOrCreate([
    'portal_name' => 'investor.wvpfondovi.mk',
], [
    'sync_enabled' => true,
]);

// Dispatch the crawl job
FetchInvestorData::dispatch($connection);
```

## Architecture

### Service Layer
- `App\Services\BrowserlessService` - Handles Browserless.io API communication and Puppeteer script execution

### Models
- `App\Models\BrowserlessConnection` - Stores connection state and sync status
- `App\Models\InvestorData` - Stores crawled data with timestamps

### Jobs
- `App\Jobs\FetchInvestorData` - Async job that performs the crawl and stores results

### Controller
- `App\Http\Controllers\BrowserlessController` - Handles web UI interactions

### Routes
- `GET /settings/investor-portal` - Settings page
- `POST /settings/investor-portal/connect` - Enable connection
- `POST /settings/investor-portal/disconnect` - Disable connection
- `POST /settings/investor-portal/sync` - Trigger manual sync
- `POST /settings/investor-portal/toggle-auto-sync` - Toggle automatic syncing on/off
- `POST /settings/investor-portal/test-connection` - Test Browserless API connection
- `GET /settings/investor-portal/history` - View crawl history
- `GET /settings/investor-portal/{investorData}` - View specific crawl result

## Data Structure

The scraped data is stored in JSON format with the following structure:

```json
{
  "success": true,
  "url": "https://investor.wvpfondovi.mk/...",
  "title": "Dashboard Title",
  "tables": [
    {
      "index": 0,
      "headers": ["Header 1", "Header 2", "..."],
      "rows": [
        ["Cell 1", "Cell 2", "..."],
        ["Cell 1", "Cell 2", "..."]
      ]
    }
  ],
  "funds": [
    {
      "html": "...",
      "text": "..."
    }
  ],
  "screenshot": "base64_encoded_screenshot"
}
```

## Error Handling

The integration includes comprehensive error handling:

- **Retry Logic**: Jobs retry up to 3 times with 60-second delays
- **Failure Tracking**: Consecutive failures are tracked
- **Auto-Disable**: Connections are disabled after 5 consecutive failures
- **Detailed Logging**: All operations are logged for debugging

## Troubleshooting

### Connection Test Fails

```bash
# Test the Browserless connection
curl -X GET "https://chrome.browserless.io/pressure?token=YOUR_API_KEY"
```

### Login Fails

1. Verify your credentials in `.env` are correct
2. Check if the portal's login form has changed
3. Review the screenshot stored with the crawl data
4. Check the Laravel logs for detailed error messages

### No Data Extracted

1. The portal's HTML structure may have changed
2. Review the Puppeteer script in `BrowserlessService::getPuppeteerScript()`
3. Check the raw_data field in the database to see what was returned

### Rate Limiting

Browserless.io has rate limits based on your plan. If you hit limits:
1. Reduce crawl frequency
2. Upgrade your Browserless plan
3. Use the `consecutive_failures` tracking to avoid hammering the API

## Security Considerations

- Portal credentials are stored in `.env` (not in database)
- Only encrypted values are stored in the database when needed
- Screenshots may contain sensitive data - handle with care
- API keys are never exposed to the frontend
- All requests are server-side only

## Future Enhancements

Potential improvements to consider:

1. **Scheduled Crawls**: Add scheduled jobs to crawl automatically
2. **Data Parsing**: Parse specific fund values from tables
3. **Alerts**: Notify users of significant changes in fund values
4. **Historical Analysis**: Track fund performance over time
5. **Multi-Portal Support**: Extend to support other investment portals
6. **Export Options**: Add CSV/PDF export of crawled data

## Support

For issues or questions:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Review the job queue: `php artisan queue:failed`
3. Test the Browserless connection via the web interface
4. Review this documentation

## Related Files

- Configuration: `config/browserless.php`
- Service: `app/Services/BrowserlessService.php`
- Models: `app/Models/BrowserlessConnection.php`, `app/Models/InvestorData.php`
- Job: `app/Jobs/FetchInvestorData.php`
- Controller: `app/Http/Controllers/BrowserlessController.php`
- Migrations: `database/migrations/2026_01_12_120000_*.php`
- Command: `app/Console/Commands/CrawlInvestorPortal.php`
